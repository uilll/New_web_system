<?php

namespace Tobuli\Entities;

use Eloquent;

class SensorGroup extends Eloquent
{
    protected $table = 'sensor_groups';

    protected $fillable = [
        'title',
        'count',
    ];

    public $timestamps = false;
}
