<?php

namespace Tobuli\Entities;


use Eloquent;


class EventQueue extends Eloquent
{
    protected $table = 'events_queue';

    protected $fillable = [
        'user_id',
        'device_id',
        'data',
        'type'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected $appends = [
        'event_message'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function device()
    {
        return $this->hasOne(Device::class, 'id', 'device_id');
    }

    public function getEventMessageAttribute()
    {
        return $this->eventMessage();
    }

    public function eventMessage()
    {
        switch ($this->type) {
            case 'zone_out':
            case 'zone_in':
                $message = trans('front.' . $this->type);
                break;
            case 'overspeed':
                if (auth()->user() && auth()->user()->unit_of_distance == 'mi')
                    $message = trans('front.' . $this->type) . ' ' . round(kilometersToMiles($this->data['overspeed_speed'])).' '.trans('front.mi');
                else
                    $message = trans('front.' . $this->type) . ' ' . $this->data['overspeed_speed'].' '.trans('front.km');
                break;
            case 'driver':
                $message = sprintf(trans('front.driver_alert'), $this->data['driver']);
                break;
            case 'stop_duration':
                $message = trans('front.stop_duration') . '(' . $this->data['stop_duration'] . trans('front.minutes') . ')';
                break;
            case 'offline_duration':
                $message = trans('front.offline_duration') . '('. $this->data['offline_duration'] . trans('front.minutes').')';
                break;

            default:
                $message = $this->data['message'];
        }

        return $message;
    }
}
