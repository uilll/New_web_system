<?php

namespace Tobuli\Helpers\Alerts;

class SosAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (! $this->check($position)) {
            return null;
        }

        $event = $this->getEvent();

        $event->type = 'sos';
        $event->message = 'SOS';

        $event->additionalQueueData = array_merge($event->additionalQueueData, [
            'message' => 'SOS',
        ]);

        return [$event];
    }

    protected function check($position)
    {
        if (! $position) {
            return false;
        }

        if (! $this->checkAlertPosition($position)) {
            return false;
        }

        if ($position->getParameter('alarm') != 'sos') {
            return false;
        }

        return true;
    }
}
