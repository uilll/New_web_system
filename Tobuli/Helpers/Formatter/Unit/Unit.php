<?php

namespace Tobuli\Helpers\Formatter\Unit;

abstract class Unit
{
    protected $ratio = 1;

    protected $unit;

    abstract public function byUnit($unit);

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    public function convert($value)
    {
        return $value * $this->ratio;
    }

    public function reverse($value)
    {
        return $value * (1 / $this->ratio);
    }

    public function format($value)
    {
        return round($value);
    }

    public function unit()
    {
        return $this->unit;
    }

    public function human($value)
    {
        $converted = $this->convert($value);

        return "{$this->format($converted)} {$this->unit()}";
    }
}
