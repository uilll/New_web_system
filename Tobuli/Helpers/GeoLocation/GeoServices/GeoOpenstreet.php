<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;



class GeoOpenstreet extends GeoNominatim
{
    public function __construct()
    {
        parent::__construct();

        $this->url = 'https://nominatim.openstreetmap.org/';
    }
}