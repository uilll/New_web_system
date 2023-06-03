<?php

namespace Tobuli\Helpers\Alerts;

class DriverAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (! $this->checkAlertPosition($position)) {
            return null;
        }

        $events = [];

        foreach ($this->alert->drivers as $driver) {
            if (! $this->check($position, $driver)) {
                continue;
            }

            $event = $this->getEvent();

            $event->type = 'driver';
            $event->message = $driver->name;

            $event->additionalQueueData = array_merge($event->additionalQueueData, [
                'driver' => htmlentities($driver->name),
            ]);

            $events[] = $event;
        }

        return $events;
    }

    protected function check($position, $driver)
    {
        if ($this->device->current_driver_id == $driver->id) {
            return false;
        }

        if (! $position->isRfid($driver->rfid)) {
            return false;
        }

        return true;
    }
}
