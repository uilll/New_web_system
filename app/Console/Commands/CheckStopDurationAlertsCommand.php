<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Alert;
use Tobuli\Entities\Event;
use Tobuli\Helpers\Alerts\Checker;

class CheckStopDurationAlertsCommand extends Command
{
    private $events = [];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'alerts:checkStopDuration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for stop duration alerts and add them';

    /**
     * Create a new command instance.
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
        $alerts = Alert::with('user', 'devices', 'devices.traccar')
            ->checkByTime()
            ->active()
            ->get();

        foreach ($alerts as $alert) {
            foreach ($alert->devices as $device) {
                $checker = new Checker($device, [$alert]);

                $events = $checker->check();

                if ($events) {
                    $this->events = array_merge($this->events, $events);
                }
            }
        }

        $this->writeEvents();

        echo "DONE\n";
    }

    protected function writeEvents()
    {
        if (! $this->events) {
            return;
        }

        $events = [];
        $queues = [];

        foreach ($this->events as $event) {
            $attributes = $event->attributesToArray();

            if ($event->getFillable()) {
                $attributes = array_intersect_key($attributes, array_flip($event->getFillable()));

                $attributes['created_at'] = date('Y-m-d H:i:s');
                $attributes['updated_at'] = date('Y-m-d H:i:s');
            }

            $events[] = $attributes;

            $queues[] = [
                'user_id' => $event->user_id,
                'device_id' => $event->device_id,
                'type' => $event->type,
                'data' => json_encode(array_merge([
                    'altitude' => $event->altitude,
                    'course' => $event->course,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'speed' => $event->speed,
                    'time' => $event->time,
                    'device_name' => htmlentities($event->device_name),
                ], $event->additionalQueueData)),
            ];
        }

        $this->events = [];

        Event::insert($events);

        DB::table('events_queue')->insert($queues);
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
