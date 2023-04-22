<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

use Tobuli\Entities\EmailTemplate;
use Tobuli\Entities\SmsTemplate;

use Tobuli\Repositories\DeviceService\DeviceServiceRepositoryInterface as DeviceService;
use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;


class CheckServiceExpireCommand extends Command
{

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'service:check_expire';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Check for service expired.';

    private $deviceService;
    private $emailTemplate;
    private $smsTemplate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DeviceService $deviceService)
    {
        parent::__construct();

        $this->deviceService = $deviceService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->smsTemplate = SmsTemplate::where('name', 'service_expired')->first();
        $this->emailTemplate = EmailTemplate::where('name', 'service_expired')->first();

        $items = DB::table('device_services as services')
            ->select('services.*', 'users.sms_gateway', 'users.sms_gateway_url', 'users.sms_gateway_params', 'devices.plate_number as device_name', 'timezones.zone', 'users.lang')
            ->join('devices', 'services.device_id', '=', 'devices.id')
            ->join('users', 'services.user_id', '=', 'users.id')
            ->join('timezones', 'users.timezone_id', '=', 'timezones.id')
            ->where([
                'services.expiration_by' => 'days',
                'services.expired' => 0
            ])
            ->whereRaw("((timezones.prefix = 'plus' && DATE(DATE_ADD('Y-m-d H:i:s', INTERVAL timezones.time HOUR_MINUTE)) >= DATE(services.expires_date)) OR (timezones.prefix = 'minus' && DATE(DATE_SUB('Y-m-d H:i:s', INTERVAL timezones.time HOUR_MINUTE)) >= DATE(services.expires_date)))")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            if ($id = $this->processItem($item))
                $ids[] = $id;
        }

        $items = DB::table('device_services as services')
            ->select(DB::raw('
            services.*,
            users.sms_gateway,
            users.sms_gateway_url,
            users.sms_gateway_params,
            devices.plate_number as device_name,
            sensors.odometer_value_by,
            sensors.odometer_value,
            sensors.odometer_value_unit,
            sensors.value,
            sensors.value_formula,
            sensors.unit_of_measurement,
            users.lang
            '))
            ->join('devices', 'services.device_id', '=', 'devices.id')
            ->join('users', 'services.user_id', '=', 'users.id')
            ->join('device_sensors as sensors', function ($query) {
                $query->on('devices.id', '=', 'sensors.device_id');
                $query->where('sensors.type', '=', 'odometer');
            })
            ->where([
                'services.expiration_by' => 'odometer',
                'services.expired' => 0
            ])
            ->whereRaw("((sensors.odometer_value_by = 'virtual_odometer' AND ((sensors.odometer_value_unit = 'km' && sensors.odometer_value >= services.expires) OR (sensors.odometer_value_unit = 'mi' && (sensors.odometer_value * 0.621371192) >= services.expires))) OR (sensors.odometer_value_by = 'connected_odometer' AND sensors.value_formula >= services.expires))")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            if ($id = $this->processItem($item))
                $ids[] = $id;
        }

        $items = DB::table('device_services as services')
            ->select('services.*', 'users.sms_gateway', 'users.sms_gateway_url', 'users.sms_gateway_params', 'devices.plate_number as device_name', 'sensors.value', 'sensors.unit_of_measurement', 'users.lang')
            ->join('devices', 'services.device_id', '=', 'devices.id')
            ->join('users', 'services.user_id', '=', 'users.id')
            ->join('device_sensors as sensors', function ($query) {
                $query->on('devices.id', '=', 'sensors.device_id');
                $query->where('sensors.type', '=', 'engine_hours');
            })
            ->where([
                'services.expiration_by' => 'engine_hours',
                'services.expired' => 0
            ])
            ->whereRaw("sensors.value >= services.expires")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            if ($id = $this->processItem($item))
                $ids[] = $id;
        }

        if ( ! empty($ids))
            $this->updateEventSent($ids);

        return 'DONE';
    }

    private function updateEventSent($ids)
    {
        DB::table('device_services')->whereIn('id', $ids)->update([
            'expired' => 1
        ]);
    }

    protected function processItem($item)
    {
        $this->setLanguage($item);

        try {
            sendTemplateEmail($item->email, $this->emailTemplate, $item);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }

        try {
            sendTemplateSMS($item->mobile_phone, $this->smsTemplate, $item, $item->user_id);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }

        if ( ! $item->renew_after_expiration)
            return $item->id;

        switch ($item->expiration_by) {
            case 'odometer':
                $values = [
                    'odometer' => $item->odometer_value_by == 'virtual_odometer' ? $item->odometer_value : $item->value_formula
                ];
                break;
            case 'engine_hours':
                $values = [
                    'engine_hours' => $item->value
                ];
                break;
            default:
                $values = [];
        }

        $item_arr = json_decode(json_encode($item), true);
        $update_arr = prepareServiceData($item_arr, $values);
        $this->deviceService->update($item->id, $update_arr);

        return null;
    }

    private function setLanguage($item)
    {
        if ($item->lang)
            return App::setLocale($item->lang);

        return App::setLocale(settings('main_settings.default_language'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}
