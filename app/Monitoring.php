<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Facades\Repositories\UserRepo;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;


class Monitoring extends Model
{
    //
    //public $timestamps = false;
    protected $fillable = [
        'active',
        'event_id',
        'device_id',
        'customer', 
        'owner',
        'plate_number',
        'cause',
        'gps_date',
        'lat',
        'lon',
        'occ_date',
        'modified_date',
        'tel',
        'make_contact',
        'information',        
        'next_con',
        'treated_occurence',
        'sent_maintenance',
        'automatic_treatment',
        'interaction_date',
        'interaction_choice1',
        'interaction_choice2',
        'interaction_later',        
        'timestamp',
        'manager_id',
        'created_at',
        'updated_at'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

}