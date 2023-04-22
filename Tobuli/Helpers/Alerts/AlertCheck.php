<?php

namespace Tobuli\Helpers\Alerts;


use Tobuli\Entities\Alert;
use Tobuli\Entities\Device;
use Tobuli\Entities\Event;


abstract class AlertCheck
{
    protected $device;
    protected $alert;
    protected $position;
    protected $prevPosition;

    protected $checkPrevious = false;
    protected $checkIsPrevious = false;
    protected $checkIsHistory = false;

    abstract public function checkEvents($position, $prevPosition);

    public function __construct(Device $device, Alert $alert)
    {
        $this->setDevice($device);
        $this->setAlert($alert);
    }

    public function setDevice(Device $device)
    {
        $this->device = $device;
    }

    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function setCurrentPosition($position)
    {
        $this->position = $position;
    }

    public function setPreviousPosition($position)
    {
        $this->prevPosition = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getEvents()
    {
        return $this->checkEvents($this->position, $this->prevPosition);
    }

    protected function checkAlertPosition($position)
    {
        if ( ! $this->checkSchedules($position->time))
            return false;

        if ( ! $this->checkZones($position->latitude, $position->longitude))
            return false;

        return true;
    }

    protected function checkSchedules($time)
    {
        if ( ! $this->alert->schedule)
            return true;

        $schedules = $this->alert->getSchedulesUTC();

        if ( ! $schedules)
            return false;

        $_time = roundToQuarterHour($time);
        $_time = strtotime($_time);
        $_time = date('l H:i', $_time);

        list($_weekday, $_time) = explode(' ', $_time);

        return in_array($_time, array_get($schedules, strtolower($_weekday), []));
    }

    protected function checkZones($latitude, $longitude)
    {
        if (in_array($this->alert->type, ['geofence_in','geofence_out','geofence_inout']))
            return true;

        if ( ! $this->alert->zone)
            return true;

        if (empty($this->alert->zones))
            return true;

        if ($this->alert->zone == 1)
            return $this->checkZonesIn($latitude, $longitude);

        if ($this->alert->zone == 2)
            return $this->checkZonesOut($latitude, $longitude);

        return false;
    }

    protected function checkZonesIn($latitude, $longitude)
    {
        foreach ($this->alert->zones as $zone) {
            if ($zone->pointIn(['latitude' => $latitude, 'longitude' => $longitude]))
                return true;
        }

        return false;
    }

    protected function checkZonesOut($latitude, $longitude)
    {
        foreach ($this->alert->zones as $zone) {
            if ($zone->pointIn(['latitude' => $latitude, 'longitude' => $longitude]))
                return false;
        }

        return true;
    }

    protected function getZone($latitude, $longitude)
    {
        if (in_array($this->alert->type, ['geofence_in','geofence_out','geofence_inout']))
            return null;

        if ( ! $this->alert->zone)
            return null;

        if (empty($this->alert->zones))
            return null;

        if ($this->alert->zone == 1)
            return $this->getZoneIn($latitude, $longitude);

        if ($this->alert->zone == 2)
            return $this->getZoneOut($latitude, $longitude);

        return null;
    }

    protected function getZoneIn($latitude, $longitude)
    {
        foreach ($this->alert->zones as $zone) {
            if ($zone->pointIn(['latitude' => $latitude, 'longitude' => $longitude]))
                return $zone;
        }

        return null;
    }

    protected function getZoneOut($latitude, $longitude)
    {
        foreach ($this->alert->zones as $zone) {
            if ($zone->pointOut(['latitude' => $latitude, 'longitude' => $longitude]))
                return $zone;
        }

        return null;
    }

    protected function getZones($latitude, $longitude)
    {
        if (in_array($this->alert->type, ['geofence_in','geofence_out','geofence_inout']))
            return null;

        if ( ! $this->alert->zone)
            return null;

        if (empty($this->alert->zones))
            return null;

        if ($this->alert->zone == 1)
            return $this->getZonesIn($latitude, $longitude);

        if ($this->alert->zone == 2)
            return $this->getZonesOut($latitude, $longitude);

        return null;
    }

    protected function getZonesIn($latitude, $longitude)
    {
        $zones = [];

        foreach ($this->alert->zones as $zone) {
            if ($zone->pointIn(['latitude' => $latitude, 'longitude' => $longitude]))
                $zones[] = $zone;
        }

        return $zones;
    }

    protected function getZonesOut($latitude, $longitude)
    {
        $zones = [];

        foreach ($this->alert->zones as $zone) {
            if ($zone->pointOut(['latitude' => $latitude, 'longitude' => $longitude]))
                $zones[] = $zone;
        }

        return $zones;
    }

    protected function getEvent()
    {
        $position = $this->getPosition();

        $zone = $this->getZone($position->latitude, $position->longitude);

        $event = new Event([
            'user_id'      => $this->alert->user_id,
            'alert_id'     => $this->alert->id,
            'device_id'    => $this->device->id,
            'geofence_id'  => $zone ? $zone->id : null,
            'altitude'     => $position->altitude,
            'course'       => $position->course,
            'latitude'     => $position->latitude,
            'longitude'    => $position->longitude,
            'speed'        => $position->speed,
            'time'         => $position->time,
        ]);


        $notifications = $this->alert->notifications;

        $event->additionalQueueData = [
            'push'         => array_get($notifications, 'push.active'),
            'email'        => array_get($notifications, 'email.active') ? array_get($notifications, 'email.input') : null,
            'mobile_phone' => array_get($notifications, 'sms.active') ? array_get($notifications, 'sms.input') : null,
            'webhook'      => array_get($notifications, 'webhook.active') ? array_get($notifications, 'webhook.input') : null,
            'command'      => array_get($this->alert->command, 'active') ? $this->alert->command : null,

            'geofence'     => $zone ? $zone->name : null,
        ];

        return $event;
    }
}