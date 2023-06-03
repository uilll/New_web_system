<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;

interface GeoServiceInterface
{
    public function byAddress($address);

    public function listByAddress($address);

    public function byCoordinates($lat, $lng);
}
