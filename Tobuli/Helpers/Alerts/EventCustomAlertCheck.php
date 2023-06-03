<?php

namespace Tobuli\Helpers\Alerts;

class EventCustomAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (! $this->checkAlertPosition($position)) {
            return null;
        }

        $events = [];

        foreach ($this->alert->events_custom as $eventCustom) {
            if (! $this->check($position, $eventCustom)) {
                continue;
            }

            if (! $eventCustom->always && $this->check($prevPosition, $eventCustom)) {
                continue;
            }

            $event = $this->getEvent();

            $event->type = 'custom';
            $event->message = $eventCustom->message;

            $event->additionalQueueData = array_merge($event->additionalQueueData, [
                'message' => $eventCustom->message,
            ]);

            $events[] = $event;
        }

        return $events;
    }

    protected function check($position, $eventCustom)
    {
        if (! $position) {
            return false;
        }

        if ($eventCustom->protocol != $position->protocol) {
            return false;
        }

        if (! $this->checkCustomEventConditions($position, $eventCustom)) {
            return false;
        }

        return true;
    }

    protected function checkCustomEventConditions($position, $customEvent)
    {
        $parameters = $position->parameters;
        $parameters['speed'] = $position->speed;

        if (empty($customEvent->conditions)) {
            return false;
        }

        foreach ($customEvent->conditions as $condition) {
            if (! array_key_exists($condition['tag'], $parameters)) {
                return false;
            }

            $value = $parameters[$condition['tag']];

            if ($condition['tag'] == 'rfid' && $position->protocol == 'meitrack') {
                $value = hexdec($value);
            }

            preg_match('/\%SETFLAG\[([0-9]+)\,([0-9]+)\,([\s\S]+)\]\%/', $condition['tag_value'], $match);
            if (isset($match['1']) && isset($match['2']) && isset($match['3'])) {
                $condition['tag_value'] = $match['3'];
                $value = substr($value, $match['1'], $match['2']);
            }

            if (! checkCondition($condition['type'], $value, $condition['tag_value'])) {
                return false;
            }
        }

        return true;
    }
}
