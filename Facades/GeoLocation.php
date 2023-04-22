<?php

namespace Facades;

use Illuminate\Support\Facades\Facade;

class GeoLocation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
		/* Função para buscar os dados do Array Location*/
        return 'Tobuli\Helpers\GeoLocation\GeoLocation';
    }
}