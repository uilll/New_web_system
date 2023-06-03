<?php

namespace Tobuli\Helpers\Formatter\Unit;

class Capacity extends Unit
{
    public function byUnit($unit)
    {
        switch ($unit) {
            case 'lt':
                $this->setRatio(1);
                $this->setUnit(trans('front.liters'));
                break;

            case 'gl':
                $this->setRatio(0.264172053);
                $this->setUnit(trans('front.gallons'));
                break;

            default:
                $this->setRatio(1);
                $this->setUnit(trans('front.liters'));
        }
    }
}
