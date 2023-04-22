<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Tobuli\Repositories\Config\ConfigRepositoryInterface as Config;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;

class CheckAlertsCommand extends Command {
    /**
     * @var Config
     */
    private $config;
    /**
     * @var TraccarDevice
     */
    private $traccarDevice;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'alerts:check';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';
    /**
     * Create a new command instance.
     *
     * @param Config $config
     * @param TraccarDevice $traccarDevice
     */
    public function __construct(Config $config, TraccarDevice $traccarDevice)
    {
        parent::__construct();
        $this->config = $config;
        $this->traccarDevice = $traccarDevice;
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $config = $this->config->whereTitle('alerts_last_check');
        $this->config->update($config->id, ['value' => time()]);
        $time = date('Y-m-d H:i:s', $config->value);
        $devices = DB::connection('traccar_mysql')
            ->table('devices')
            ->select('devices.*', 'device_sensors.odometer_value', 'web_devices.id as device_id')
            ->join('gpswox_web.devices as web_devices', 'devices.id', '=', 'web_devices.traccar_device_id')
            ->join('gpswox_web.device_sensors', function($query) {
                $query->on('web_devices.id', '=', 'device_sensors.device_id');
                $query->where('device_sensors.type', '=', 'odometer');
                $query->where('device_sensors.odometer_value_by', '=', 'virtual_odometer');
            })
            ->where('devices.server_time', '>', $time)
            ->groupBy('devices.id')
            ->get();

        foreach ($devices as $device) {
            $distance = $device->odometer_value;
            $table_name = 'positions_'.$device->id;

            $items = DB::connection('traccar_mysql')->select(
                DB::raw("(SELECT id, distance, latitude, longitude, 1 AS outter FROM $table_name WHERE server_time <= '$time' ORDER BY time desc LIMIT 1) UNION (SELECT id, distance, latitude, longitude, 0 AS outter FROM $table_name WHERE server_time > '$time' ORDER BY time desc)")
            );

            $lastItem = null;

            foreach ($items as $item)
            {
                if (is_null($lastItem)) {
                    $lastItem = $item;
                    continue;
                }

                $cur_distance = getDistance($item->latitude, $item->longitude, $lastItem->latitude, $lastItem->longitude);
                $distance += $cur_distance;

                if (round($cur_distance, 5) != round($item->distance, 5)) {
                    DB::connection('traccar_mysql')
                        ->table($table_name)
                        ->where('id', '=', $item->id)
                        ->update(['distance' => $cur_distance]);
                }

                $lastItem = $item;
            }

            if ($device->odometer_value != $distance)
            {
                DB::table('device_sensors')
                    ->where('device_id', '=', $device->device_id)
                    ->where('type', '=', 'odometer')
                    ->where('odometer_value_by', '=', 'virtual_odometer')
                    ->update(['odometer_value' => $distance]);
            }
        }

        $this->call('alerts:checkStopDuration');

        echo "DONE\n";
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