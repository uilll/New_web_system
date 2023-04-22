<?php

namespace Tobuli\Helpers\Formatter\Unit;

class Altitude extends Unit
{
    public function byUnit($unit)
    {
        switch ($unit) {
            case 'mt':
                $this->setRatio(1);
                $this->setUnit(trans('front.mt'));
                break;

            case 'ft':
                $this->setRatio(3.2808399);
                $this->setUnit(trans('front.ft'));
                break;

            default:
                $this->setRatio(1);
                $this->setUnit(trans('front.mt'));
        }
    }
}