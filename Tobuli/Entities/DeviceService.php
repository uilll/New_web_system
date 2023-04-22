<?php namespace Tobuli\Entities;

use Eloquent;

class DeviceService extends Eloquent {
	protected $table = 'device_services';

    protected $fillable = array(
        'user_id',
        'device_id',
        'name',
        'expiration_by',
        'interval',
        'last_service',
        'trigger_event_left',
        'renew_after_expiration',
        'expires',
        'expires_date',
        'remind',
        'remind_date',
        'event_sent',
        'expired',
        'email',
        'mobile_phone'
    );

    protected $sensors;

    public $timestamps = false;

    public function device() {
        return $this->hasOne('Tobuli\Entities\Device', 'id', 'device_id');
    }

    public function user() {
        return $this->hasOne('Tobuli\Entities\User', 'id', 'user_id');
    }

    public function setSensors($sensors)
    {
        $this->sensors = $sensors;
    }

    public function getLeftAttribute()
    {
        return $this->getLeft();
    }

    public function getPercentageAttribute()
    {
        return $this->getPercentage();
    }

    public function getLeft()
    {
        $sensor = $this->getSensor();

        switch ($this->expiration_by)
        {
            case 'days':
                return dateDiff($this->expires_date, date('Y-m-d'));

            case 'odometer':
            case 'engine_hours':
                if ( ! $sensor)
                    return null;

                return $this->expires - $sensor->getValueCurrent();

            default:
                return null;
        }
    }

    public function left_formated()
    {
        $left  = $this->getLeft();
        $sensor = $this->getSensor();

        if (is_null($left))
            return '-';

        if ($left < 0)
            return trans('front.expired');

        switch ($this->expiration_by)
        {
            case 'days':
                return $left . 'd.';

            case 'odometer':
            case 'engine_hours':
                return round($left) . $sensor->unit_of_measurement;

            default:
                return '-';
        }
    }

    public function expiration()
    {
        $left   = $this->getLeft();
        $sensor = $this->getSensor();

        switch ($this->expiration_by)
        {
            case 'days':
                return  $left > 0
                    ? trans('validation.attributes.days').' '.trans('front.left').' ('.$this->left_formated().')'
                    : trans('validation.attributes.days').' '.strtolower(trans('front.expired'));

            case 'odometer':
                if ( ! $sensor)
                    return dontExist('front.sensor');

                return  $left > 0
                    ? trans('front.odometer').' '.trans('front.left').' ('.$this->left_formated().')'
                    : trans('front.odometer').' '.strtolower(trans('front.expired'));

            case 'engine_hours':
                if ( ! $sensor)
                    return dontExist('front.sensor');

                return  $left > 0
                    ? trans('validation.attributes.engine_hours').' '.trans('front.left').' ('.$this->left_formated().')'
                    : trans('validation.attributes.engine_hours').' '.strtolower(trans('front.expired'));

            default:
                return null;
        }
    }

    public function isExpiring()
    {
        return $this->getLeft() <= $this->trigger_event_left;
    }

    public function isExpired()
    {
        return $this->getLeft() <= 0;
    }

    public function getPercentage()
    {
        $left = $this->getLeft();

        if (empty($left))
            return 0;

        if (empty($this->interval))
            return 0;

        $percentage = $left * 100 / $this->interval;

        if ( $percentage < 0 )
            $percentage = 0;

        if ( $percentage > 100 )
            $percentage = 100;

        return round($percentage);
    }

    private function getSensor()
    {
        if (isset($this->sensor))
            return $this->sensor;

        if ($this->sensors) {
            switch ($this->expiration_by)
            {
                case 'odometer':
                    return $this->sensor = $this->getSensorByType('odometer');
                case 'engine_hours':
                    return $this->sensor = $this->getSensorByType('engine_hours');
                default:
                    return $this->sensor = null;
            }
        } else {
            switch ($this->expiration_by)
            {
                case 'odometer':
                    return $this->sensor = $this->device->getOdometerSensor();
                case 'engine_hours':
                    return $this->sensor = $this->device->getEngineHoursSensor();
                default:
                    return $this->sensor = null;
            }
        }
    }

    private function getSensorByType($type)
    {
        if (empty($this->sensors))
            return null;

        foreach ($this->sensors as $sensor) {
            if ($sensor['type'] == $type) {
                $type_sensor = $sensor;
                break;
            }
        }

        if (empty($type_sensor))
            return null;

        return $type_sensor;
    }
}
