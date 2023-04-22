<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\EmailTemplate;
use Tobuli\Entities\SmsTemplate;

use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;

class CheckServiceCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'service:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check service expiration command.';

    private $sentIds = [];

    private $smsTemplate;

    private $emailTemplate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->smsTemplate = SmsTemplate::where('name', 'service_expiration')->first();
        $this->emailTemplate = EmailTemplate::where('name', 'service_expiration')->first();

        $items = DB::table('device_services as services')
            ->select('services.*', 'users.sms_gateway', 'users.sms_gateway_url', 'users.sms_gateway_params', 'devices.plate_number as device_name', 'users.lang') 
            ->join('devices', 'services.device_id', '=', 'devices.id')
            ->join('users', 'services.user_id', '=', 'users.id')
            ->join('timezones', 'users.timezone_id', '=', 'timezones.id')
            ->where([
                'services.expiration_by' => 'days',
                'services.expired' => 0,
                'services.event_sent' => 0,
            ])
            ->whereRaw("((timezones.prefix = 'plus' && DATE(DATE_ADD('Y-m-d H:i:s', INTERVAL timezones.time HOUR_MINUTE)) >= DATE(services.remind_date)) OR (timezones.prefix = 'minus' && DATE(DATE_SUB('Y-m-d H:i:s', INTERVAL timezones.time HOUR_MINUTE)) >= DATE(services.remind_date)))")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            $this->sendEventInfo($item);
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
                'services.expired' => 0,
                'services.event_sent' => 0,
            ])
            ->whereRaw("((sensors.odometer_value_by = 'virtual_odometer' AND ((sensors.odometer_value_unit = 'km' && sensors.odometer_value >= services.remind) OR (sensors.odometer_value_unit = 'mi' && (sensors.odometer_value * 0.621371192) >= services.remind))) OR (sensors.odometer_value_by = 'connected_odometer' AND sensors.value_formula >= services.remind))")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            $this->sendEventInfo($item);
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
                'services.expired' => 0,
                'services.event_sent' => 0,
            ])
            ->whereRaw("sensors.value >= services.remind")
            ->groupBy('services.id')
            ->get();

        foreach ($items as $item) {
            $this->sendEventInfo($item);
        }

        $this->updateEventSent();

        return 'DONE';
    }

    private function sendEventInfo($item)
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

        $this->sentIds[] = $item->id;
    }

    private function updateEventSent()
    {
        if (empty($this->sentIds))
            return;

        DB::table('device_services')->whereIn('id', $this->sentIds)->update(['event_sent' => 1]);
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
