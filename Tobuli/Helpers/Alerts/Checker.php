<?php

namespace Tobuli\Helpers\Alerts;

class Checker
{
    protected $alerts;

    protected $device;

    public function __construct($device, $alerts)
    {
        $this->setDevice($device);
        $this->setAlerts($alerts);
    }

    public function setDevice($device)
    {
        $this->device = $device;
    }

    public function setAlerts($alerts)
    {
        $this->alerts = $alerts;
    }

    public function check($position = null, $prevPosition = null)
    {
        $events = [];

        if (empty($this->alerts)) {
            return $events;
        }

        foreach ($this->alerts as $alert) {
            if (empty($alert->type)) {
                continue;
            }

            switch($alert->type) {
                case 'overspeed':
                    $checker = new OverspeedAlertCheck($this->device, $alert);
                    break;
                case 'stop_duration':
                    $checker = new StopDurationAlertCheck($this->device, $alert);
                    break;
                case 'offline_duration':
                    $checker = new OfflineDurationAlertCheck($this->device, $alert);
                    break;
                case 'geofence_in':
                case 'geofence_out':
                case 'geofence_inout':
                    $checker = new GeofenceAlertCheck($this->device, $alert);
                    break;
                case 'driver':
                    $checker = new DriverAlertCheck($this->device, $alert);
                    break;
                case 'custom':
                    $checker = new EventCustomAlertCheck($this->device, $alert);
                    break;
                case 'sos':
                    $checker = new SosAlertCheck($this->device, $alert);
                    break;
                default:
                    //throw new \Exception('Alert type "'.$alert->type.'" doesnt have check class.');
            }

            if (empty($checker)) {
                continue;
            }

            $checker->setCurrentPosition($position);
            $checker->setPreviousPosition($prevPosition);

            if ($_events = $checker->getEvents()) {
                $events = array_merge($events, $_events);
            }
        }

        return $events;
    }
}
