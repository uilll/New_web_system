<?php

namespace Tobuli\Helpers\Alerts;

use Tobuli\Entities\Event;

class StopDurationAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (! $this->check()) {
            return null;
        }

        $event = $this->getEvent();

        $stopDuration = round($this->device->getStopDuration() / 60);

        $event->type = 'stop_duration';
        $event->message = json_encode([
            'stop_duration' => $stopDuration,
            'moved_at' => $this->device->moved_at,
            'nowTime' => date('Y-m-d H:i:s'),
        ]);

        $event->additionalQueueData = array_merge($event->additionalQueueData, [
            'stop_duration' => $stopDuration,
            'device_name' => htmlentities($this->device->name),
        ]);

        return [$event];
    }

    public function check()
    {
        if ($this->alert->stop_duration < 1) {
            return false;
        }

        $stopDuration = round($this->device->getStopDuration() / 60);

        if ($stopDuration < $this->alert->stop_duration) {
            return false;
        }

        $moved_at = $this->device->traccar->moved_at;

        if (! $moved_at) {
            return false;
        }

        $position = $this->getPosition();

        if (! $position) {
            return false;
        }

        if (! $this->checkAlertPosition($position)) {
            return false;
        }

        if (Event::where('time', '>=', $moved_at)
            ->where('user_id', $this->alert->user_id)
            ->where('alert_id', $this->alert->id)
            ->where('device_id', $this->device->id)
            ->where('type', 'stop_duration')
            ->first(['id'])) {
            return false;
        }

        return true;
    }

    public function getPosition()
    {
        $position = $this->device->positionTraccar();

        if (! $position) {
            return null;
        }

        $position->time = date('Y-m-d H:i:s');

        return $position;
    }
}
