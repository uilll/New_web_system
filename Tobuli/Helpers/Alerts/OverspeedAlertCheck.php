<?php

namespace Tobuli\Helpers\Alerts;


class OverspeedAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (empty($this->alert->overspeed))
            return null;

        if ( ! $position->isValid())
            return null;

        if ( ! $this->check($position))
            return null;

        if ($this->check($prevPosition))
            return null;

        $event = $this->getEvent();

        $event->type = 'overspeed';
        $event->message = json_encode([
            'overspeed_speed'    => $this->alert->overspeed,
            'overspeed_distance' => $this->alert->user->unit_of_speed == 'km/h' ? 1 : 2,
        ]);

        $event->additionalQueueData = array_merge($event->additionalQueueData, [
            'overspeed_speed' => $this->alert->overspeed,
            'overspeed_distance' => $this->alert->user->unit_of_speed == 'km/h' ? 1 : 2
        ]);

        return [$event];
    }

    protected function check($position)
    {
        if ( ! $position)
            return false;

        if ( ! $this->checkAlertPosition($position))
            return false;

        if (round($position->speed) <= round($this->alert->overspeed))
            return false;

        return true;
    }
}