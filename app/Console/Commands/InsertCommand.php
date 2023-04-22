<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\InputArgument;
use Facades\Repositories\DeviceRepo;
use App\Console\PositionsKeys;
use App\Console\PositionsStack;
use App\Console\ProcessManager;
use App\Console\PositionsWriter;

class InsertCommand extends Command
{

    protected $redis;

    protected $processManager;

    protected $debug = false;

    protected $positionsKeys;
    protected $positionsStack;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'insert:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';


    public function __construct()
    {
        parent::__construct();

        try {
            $this->redis = Redis::connection();
        } catch (\Exception $e) {
            $this->redis = FALSE;
        }

        $this->positionsKeys = new PositionsKeys();
        $this->positionsStack = new PositionsStack();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->debug = ! empty($this->argument('debug'));

        if (!$this->redis) {
            echo "Redis not running";
            return;
        }

        DB::disableQueryLog();

        $timeout = config('tobuli.process.insert_timeout');
        $limit = config('tobuli.process.insert_limit');

        $this->processManager = new ProcessManager($this->name, $timeout, $limit);

        if ( ! $this->processManager->canProcess()) {
            echo "Cant process.";
            return;
        }

        while ($this->processManager->canProcess())
        {
            $imei = $this->process();

            if ($imei)
            {
                $this->processManager->unlock($imei);
                continue;
            }

            sleep(1);
        }
    }

    private function process()
    {
        $imei = $this->processByKeys();

        if ($imei)
            return $imei;

        $imei = $this->processByList();

        if ($imei && ! $this->positionsStack->count($imei))
            $this->positionsStack->deleteImei($imei);

        return $imei;
    }

    private function processByList()
    {
        $imei = $this->getListUnlockedImei();

        if ( ! $imei)
            return false;

        $data = $this->positionsStack->getData($imei, false);

        if ( ! $data) {
            $this->positionsStack->deleteImei($imei);
            return $imei;
        }

        $device = DeviceRepo::getByImeiProtocol($data['imei'], $data['protocol']);

        if ( ! $device) {
            DeviceRepo::setUnregisterdDevice($data, $this->positionsStack->count($imei));

            $this->positionsStack->deleteImei($imei);

        } elseif ($device->active) {
            $writer = new PositionsWriter($device, $this->debug);
            $writer->runList($imei);

            if (! $this->positionsStack->count($imei))
                $this->positionsStack->deleteImei($imei);

        } else {
            $this->positionsStack->deleteImei($imei);
        }

        return $imei;
    }

    private function processByKeys()
    {
        $imei = $this->getKeysUnlockedImei();

        if (!$imei)
            return false;

        $keys = $this->positionsKeys->getImeiKeys($imei);

        if ( ! $keys)
            return $imei;

        $first = $this->positionsKeys->getKeyData(reset($keys), false);

        if (!$first)
            return $imei;

        $device = DeviceRepo::getByImeiProtocol($first['imei'], $first['protocol']);

        if ( ! $device) {
            DeviceRepo::setUnregisterdDevice($first, count($keys));
            $this->redis->del($keys);
        } elseif ( ! $device->active) {
            $this->redis->del($keys);
        } else {
            $writer = new PositionsWriter($device, $this->debug);
            $writer->runKeys($keys);
        }

        return $imei;
    }

    private function getKeysUnlockedImei()
    {
        $keys = $this->positionsKeys->getKeys();

        if (!is_array($keys)) {
            $keys = [];
        }

        if (count($keys) < 1000)
            asort($keys);

        $locked = [];

        foreach ($keys as $key) {
            list($_prefix, $_time, $_imei) = explode('.', $key, 3);

            if (empty($_imei)) {
                $this->redis->del($key);
                continue;
            }

            if (!$this->isValidImei($_imei)) {
                $this->redis->del($key);
                continue;
            }

            if (in_array($_imei, $locked))
                continue;

            $locked[] = $_imei;

            if ( ! $this->processManager->lock($_imei))
                continue;

            $imei = $_imei;

            break;
        }

        if (empty($imei))
            return null;

        return $imei;
    }

    private function getListUnlockedImei()
    {
        $imeis = $this->positionsStack->getImeis();

        if (empty($imeis))
            return null;

        foreach ($imeis as $_imei)
        {
            //for key "positions."
            if (empty($_imei)) {
                $this->positionsStack->deleteImei($_imei);
                continue;
            }
            
            if ( ! $this->isValidImei($_imei)) {
                $this->positionsStack->deleteImei($_imei);
                continue;
            }

            if ( ! $this->processManager->lock($_imei))
                continue;

            $imei = $_imei;

            break;
        }

        if (empty($imei))
            return null;

        return $imei;
    }

    private function isValidImei($imei)
    {
        return !preg_match('/[^A-Za-z0-9.#\\-$]/', $imei);
    }

    protected function getArguments()
    {
        return array(
            array('debug', InputArgument::OPTIONAL, 'Debug')
        );
    }
}
