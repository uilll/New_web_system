<?php

namespace Facades\ModalHelpers;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\Config\Repository
 */
class SensorModalHelper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ModalHelpers\SensorModalHelper';
    }
}
