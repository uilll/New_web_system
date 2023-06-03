<?php

namespace Tobuli\Entities;

use Eloquent;

class AlertGeofence extends Eloquent
{
    protected $table = 'alert_geofence';

    protected $fillable = ['alert_id', 'geofence_id', 'zone', 'time_from', 'time_to'];

    public $timestamps = false;
}
