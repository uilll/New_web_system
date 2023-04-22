<?php

namespace Tobuli\Helpers\Alerts;


use Tobuli\Entities\Event;

class OfflineDurationAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if ( ! $this->check())
            return null;

        $event = $this->formatEvent($this->getEvent());

        return [$event];
    }

    public function check()
    {
        if ( $this->alert->offline_duration < 1 )
            return false;

        $offline_duration = $this->offlineDuration();

        if ( ! $offline_duration)
            return false;

        if ($offline_duration < $this->alert->offline_duration)
            return false;

        if (Event::where('time', '>=', $this->device->last_connect_time)
            ->where('user_id', $this->alert->user_id)
            ->where('alert_id', $this->alert->id)
            ->where('device_id', $this->device->id)
            ->where('type', 'offline_duration')
            ->first(['id']))
            return false;

        return true;
    }

    public function offlineDuration()
    {
        $last_connection = $this->device->last_connect_timestamp;

        if (empty($last_connection))
            return false;

        return round((time() - $last_connection) / 60);
    }

    public function getPosition()
    {
        $position = $this->device->positionTraccar();

        if ( ! $position)
            return null;

        $position->time = date('Y-m-d H:i:s');

        return $position;
    }

    private function getLastConnectionTime()
    {

    }

    private function formatEvent($event)
    {
        $offline_duration = $this->offlineDuration();

        $event->type = 'offline_duration';

        $event->message = json_encode([
            'offline_duration' => $offline_duration,
            'now_time'         => date('Y-m-d H:i:s')
        ]);

        $event->additionalQueueData = array_merge($event->additionalQueueData, [
            'offline_duration'  => $offline_duration,
            'device_name'       => htmlentities($this->device->name)
        ]);

        return $event;
    }
}