<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;

class GeoDefault extends GeoNominatim
{
    public function __construct()
    {
        parent::__construct();

        $this->url = 'http://173.212.206.125/geo/';
    }
}
