<?php

namespace Tobuli\Helpers\Alerts;

class GeofenceAlertCheck extends AlertCheck
{
    public function checkEvents($position, $prevPosition)
    {
        if (! $position) {
            return null;
        }

        if (! $position->isValid()) {
            return null;
        }

        $prevPosition = $this->device->positionTraccar();

        if (! $this->preCheck($position, $prevPosition)) {
            return null;
        }

        $events = [];

        foreach ($this->alert->geofences as $geofence) {
            if (! $type = $this->check($position, $prevPosition, $geofence)) {
                continue;
            }

            $event = $this->getEvent();

            $type = $type == 'geofence_in' ? 'zone_in' : 'zone_out';

            $event->type = $type;
            $event->message = $type;
            $event->geofence_id = $geofence->id;

            $event->additionalQueueData = array_merge($event->additionalQueueData, [
                'geofence' => htmlentities($geofence->name),
            ]);

            $events[] = $event;
        }

        return $events;
    }

    protected function check($position, $prevPosition, $geofence)
    {
        switch ($this->alert->type) {
            case 'geofence_in':
                if (! $this->checkGeofenceWithSchedules($position, $geofence)) {
                    return false;
                }
                if ($this->checkGeofenceWithSchedules($prevPosition, $geofence)) {
                    return false;
                }

                return 'geofence_in';

            case 'geofence_out':
                //is current in
                if ($this->checkGeofence($position, $geofence)) {
                    return false;
                }

                //is previous in
                if (! $this->checkGeofence($prevPosition, $geofence)) {
                    return false;
                }

                return 'geofence_out';

            case 'geofence_inout':
                $isCurrentIn = $this->checkGeofence($position, $geofence);
                $isPreviousIn = $this->checkGeofence($prevPosition, $geofence);

                if ($isCurrentIn && ! $isPreviousIn) {
                    return 'geofence_in';
                }
                if (! $isCurrentIn && $isPreviousIn) {
                    return 'geofence_out';
                }

                return false;

            default:
                return false;
        }
    }

    protected function checkGeofence($position, $geofence)
    {
        if (! $position) {
            return false;
        }

        if (! $position->isValid()) {
            return false;
        }

        return $geofence->pointIn($position);
    }

    protected function checkGeofenceWithSchedules($position, $geofence)
    {
        if (! $this->checkSchedules($position->time)) {
            return false;
        }

        return $this->checkGeofence($position, $geofence);
    }

    protected function isPointEquel($position, $prevPosition)
    {
        if (! $position) {
            return false;
        }

        if (! $prevPosition) {
            return false;
        }

        if ($position->latitude != $prevPosition->latitude) {
            return false;
        }

        if ($position->longitude != $prevPosition->longitude) {
            return false;
        }

        return true;
    }

    protected function preCheck($position, $prevPosition)
    {
        if ($this->isPointEquel($position, $prevPosition)) {
            return false;
        }

        switch ($this->alert->type) {
            case 'geofence_out':
            case 'geofence_inout':
                if (! $this->checkSchedules($position->time)) {
                    return false;
                }
                break;
        }

        return true;
    }
}
