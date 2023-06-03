<?php

namespace App\Console\Commands;

set_time_limit(0);

use Facades\Repositories\DeviceRepo;
use Illuminate\Console\Command;

class ResetDevicesTimezoneCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'devicetimezone:reset';

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
    public function fire()
    {
        $devices = DeviceRepo::all();

        foreach ($devices as $device) {
            if ($device->timezone_id == null) {
                continue;
            }

            if ($device->timezone_id == 57) {
                continue;
            }

            $this->line($device->imei);

            $device->timezone_id = null;
            $device->save();

            $device->applyPositionsTimezone();
        }
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
