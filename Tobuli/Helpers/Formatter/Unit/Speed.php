<?php

namespace Tobuli\Helpers\Formatter\Unit;

class Speed extends Unit
{
    public function byUnit($unit)
    {
        switch ($unit) {
            case 'km':
                $this->setRatio(1);
                $this->setUnit(trans('front.dis_h_km'));
                break;

            case 'mi':
                $this->setRatio(0.621371192);
                $this->setUnit(trans('front.dis_h_mi'));
                break;

            default:
                $this->setRatio(1);
                $this->setUnit(trans('front.dis_h_km'));
        }
    }
}
