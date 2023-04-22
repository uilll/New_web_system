<?php

namespace Tobuli\Helpers\Formatter;

use Tobuli\Entities\User;

class Formatter
{
    public $speed;
    public $distance;
    public $altitude;
    public $capacity;

    public function __construct()
    {
        $this->speed = new Unit\Speed();
        $this->distance = new Unit\Distance();
        $this->altitude = new Unit\Altitude();
        $this->capacity = new Unit\Capacity();
    }

    public function byUser(User $user)
    {
        $this->speed->byUnit($user->unit_of_distance);
        $this->distance->byUnit($user->unit_of_distance);
        $this->altitude->byUnit($user->unit_of_altitude);
        $this->capacity->byUnit($user->unit_of_capacity);
    }
}