<?php

namespace Tobuli\Entities;

use Eloquent;

class TrackerPort extends Eloquent
{
    protected $table = 'tracker_ports';

    protected $fillable = ['active', 'port', 'name', 'extra'];
}
