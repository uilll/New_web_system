<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use App\Console\PositionsKeys;
use App\Console\PositionsStack;
use App\Console\ProcessManager;
use Exception;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Config;

class CheckServerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'server:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
    public function fire(Config $config)
    {
        $curl = new \Curl;
        $curl->follow_redirects = false;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
        $curl->options['CURLOPT_TIMEOUT'] = 30;

        $traccar_restart = '';
        try {
            $autodetect = ini_get('auto_detect_line_endings');
            ini_set('auto_detect_line_endings', '1');
            $lines = file('/var/spool/cron/root', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            ini_set('auto_detect_line_endings', $autodetect);
            foreach ($lines as $key => $line) {
                if (strpos($line, 'tracker:restart') !== false) {
                    [$time] = explode('php', $line);
                    $traccar_restart = trim($time);
                    break;
                }
                //$text .= $line."\r\n";
            }
        } catch(\Exception $e) {
        }

        $host = gethostname();
        $ip = gethostbyname($host);

        if (! is_numeric(substr($ip, 0, 1))) {
            $command = "/sbin/ifconfig eth0 | grep \"inet addr\" | awk -F: '{print $2}' | awk '{print $1}'";
            $ip = exec($command);
        }

        $cfg = settings('jar_version');
        if (empty($cfg)) {
            settings('jar_version', 1);
        }
        $jar_version = empty($cfg) ? 1 : $cfg;

        $cpu = exec("ps --no-heading -o pcpu -C httpd | awk '{s+=$1} END {print s}'");
        $cores = exec('nproc');
        $cpu = round(($cpu / $cores), 2);
        $ram_used = round(exec("free | awk 'FNR == 2 {print $3/1000000}'"), 2);
        $ram_all = round(exec("free | awk 'FNR == 2 {print ($3+$4)/1000000}'"), 2);
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        $disk_used = $disk_total - $disk_free;
        $traccar_status = boolval(strpos(exec('sudo service traccar status'), 'traccar is running') !== false) ? 1 : 0;

        $date = date('Y-m-d H:i:s', time() - 360);
        $devices = DB::connection('traccar_mysql')->table('devices')->select(DB::Raw('COUNT(id) as nr'))->where('server_time', '>', $date)->orWhere('ack_time', '>', $date)->first();

        try {
            $redis = new \Redis();
            $redis->connect(config('database.redis.default.host'), config('database.redis.default.port'));
        } catch (\Exception $e) {
            $redis = false;
        }

        $position_count = 0;
        if ($redis) {
            $position_count += (new PositionsStack())->count();
            $position_count += (new PositionsKeys())->count();
        }

        // Check if memcached php module loaded
        $memcached = class_exists('Memcached');

        // Check if memcached php server is up
        $memcachedServerRunning = false;
        if ($memcached) {
            try {
                $memcachedStats = Cache::store('memcached')->getMemcached()->getStats();
                $memcachedServerRunning = true;
            } catch (Exception $e) {
            }
        }

        try {
            $response = json_decode($curl->get('http://hive.gpswox.com/check_jar', [
                'version' => $jar_version,
                'app_version' => config('tobuli.version'),
                'cpu' => $cpu,
                'ram' => compact('ram_used', 'ram_all'),
                'disk' => compact('disk_total', 'disk_used'),
                'traccar_restart' => $traccar_restart,
                'traccar_status' => $traccar_status,
                'redis_status' => $redis ? 1 : 0,
                'redis_keys' => $position_count,
                'memcached_module_status' => $memcached ? 1 : 0,
                'memcached_server_status' => $memcachedServerRunning ? 1 : 0,
                'cores' => $cores,
                'devices_online' => $devices->nr,
                'admin_user' => env('admin_user', null),
                'name' => env('server', null),
                'type' => config('tobuli.type'),
                'ip' => $ip,
            ]), true);
        } catch (Exception $e) {
            $response = false;
        }

        $this->processManager = new ProcessManager($this->name, $timeout = 300, $limit = 1);

        if (! $this->processManager->canProcess()) {
            echo "Cant process \n";

            return false;
        }

        if (! is_null($response['autodeploy'])) {
            $autodeployMark = storage_path('autodeploy');

            if ($response['autodeploy'] && File::exists($autodeployMark)) {
                File::delete($autodeployMark);
            }

            if (! $response['autodeploy'] && ! File::exists($autodeployMark)) {
                File::put($autodeployMark, '');
            }
        }

        if (empty(settings('last_ports_modification'))) {
            settings('last_ports_modification', 0);
        }

        if (empty(settings('last_config_modification'))) {
            settings('last_config_modification', 0);
        }

        $last_ports_modification = settings('last_ports_modification');
        $last_config_modification = settings('last_config_modification');

        if (isset($response['ports']) && $response['ports']['last'] > $last_ports_modification) {
            $ports = $response['ports']['items'];
            parsePorts($response['ports']['items']);

            settings('last_ports_modification', $response['ports']['last']);
            settings('last_config_modification', $response['configs']['last']);
        } else {
            if (isset($response['configs']) && $response['configs']['last'] > $last_config_modification) {
                settings('last_config_modification', $response['configs']['last']);
            }
        }

        if ((isset($response['ports']) && $response['ports']['last'] > $last_ports_modification) || (isset($response['configs']) && $response['configs']['last'] > $last_config_modification)) {
            $cur_ports = json_decode(json_encode(DB::table('tracker_ports')->get()), true);
            generateConfig($cur_ports);
            exec('sudo service traccar restart', $output, $result);
        }

        if (isset($response['status']) && $response['status'] && isset($response['url']) && ! empty($response['url'])) {
            @unlink('/opt/traccar/tracker-server-back.jar');
            exec('cd /opt/traccar; cp tracker-server.jar tracker-server-back.jar', $output, $result);
            if (file_exists('/opt/traccar/tracker-server-back.jar')) {
                exec('cd /opt/traccar; wget '.$response['url'].' -O tracker-server-current.jar 2>&1', $output, $result);
                [$md5] = explode(' ', exec('md5sum /opt/traccar/tracker-server-current.jar'));
                if (filesize('/opt/traccar/tracker-server-current.jar') != $response['size'] || empty($md5) || $md5 != $response['md5']) {
                    echo 'Failed to download traccar';

                    return;
                }

                $this->call('generate:config');

                exec('cd /opt/traccar; cp tracker-server-current.jar tracker-server.jar');
                $updated = true;
                $traccar_running = false;
                $backed = 0;
                while ($backed < 2) {
                    $times = 0;
                    while (! $traccar_running && $times <= 5) {
                        $result = restartTraccar('new_jar');
                        if ($result == 'OK') {
                            $traccar_running = true;
                        }

                        $times++;
                    }

                    $backed++;

                    if (! $traccar_running && $backed == 1) {
                        exec('cd /opt/traccar; cp tracker-server-back.jar tracker-server.jar', $output, $result);
                        $updated = false;
                    }
                }

                if ($updated) {
                    settings('jar_version', $response['version']);
                }
            }
        }

        $date = date('Y-m-d H:i:s', strtotime('-1 days'));
        DB::statement("DELETE FROM sms_events_queue WHERE created_at < '{$date}'");

        dd('Ok');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
