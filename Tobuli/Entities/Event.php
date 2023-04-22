<?php namespace Tobuli\Entities;

use Eloquent;

class Event extends Eloquent {
	protected $table = 'events';

    protected $fillable = array(
        'user_id',
        'geofence_id',
        'position_id',
        'alert_id',
        'device_id',
        'type',
        'message',
        'latitude',
        'longitude',
        'time',
        'speed',
        'altitude',
        'power',
        'address',
        'deleted'
    );

    protected $appends = [
        'name',
        'detail'
    ];

    public function geofence() {
        return $this->hasOne('Tobuli\Entities\Geofence', 'id', 'geofence_id');
    }

    public function alert() {
        return $this->hasOne('Tobuli\Entities\Alert', 'id', 'alert_id');
    }

    public function device() {
        return $this->hasOne('Tobuli\Entities\Device', 'id', 'device_id');
    }

    public function getDetailAttribute() {
        $detail = null;

        switch($this->type) {
            case 'zone_in':
            case 'zone_out':
                $detail = $this->geofence ? $this->geofence->name : null;
                break;
            case 'driver':
                $detail = $this->message;
                break;
            case 'overspeed':
                $data = json_decode($this->message, true);
                if (auth()->user() && auth()->user()->unit_of_distance == 'mi')
                    $detail = round(kilometersToMiles($data['overspeed_speed'])).' '.trans('front.mi');
                else
                    $detail = $data['overspeed_speed'].' '.trans('front.km');
                break;
            case 'stop_duration':
                $data = json_decode($this->message, true);
                $detail = $data['stop_duration'].' '. trans('front.minutes');
                break;
            case 'offline_duration':
                $data = json_decode($this->message, true);
                $detail = $data['offline_duration'].' '. trans('front.minutes');
                break;
        }

        return $detail;
    }

    public function getNameAttribute()
    {
        switch($this->type) {
            case 'zone_in':
            case 'zone_out':
                $name = trans('front.'.$this->type);
                break;
            case 'driver':
                $name = trans('front.driver');
                break;
            case 'overspeed':
                $name = trans('front.overspeed');
                break;
            case 'stop_duration':
                $name = trans('validation.attributes.stop_duration_longer_than');
                break;
            case 'offline_duration':
                $name = trans('validation.attributes.offline_duration_longer_than');
                break;
            default:
                $name = $this->message;
        }

        return $name;
    }

    public function formatMessage()
    {
        $detail = $this->detail;

        return $this->name . ($detail ? " ($detail)" : "");
    }

}
