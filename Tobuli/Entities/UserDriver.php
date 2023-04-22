<?php namespace Tobuli\Entities;

use Eloquent;

class UserDriver extends Eloquent {
	protected $table = 'user_drivers';

    protected $fillable = array(
        'user_id',
        'device_id',
        'name',
        'rfid',
        'phone',
        'email',
        'cnh',
        'cnh_expire',
        'pre_alert',
        'alert',
        'seeing',
        'next_alert',
        'description'
    );

    public function user() {
        return $this->belongsTo('Tobuli\Entities\User', 'user_id', 'id');
    }

    public function device() {
        return $this->hasOne('Tobuli\Entities\Device', 'id', 'device_id');
    }
}
