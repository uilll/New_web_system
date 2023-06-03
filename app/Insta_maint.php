<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class insta_maint extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'active',
        'created_at',
        'updated_at',
        'device_id',
        'technician_id',
        'recei_from_cli',
        'valor',
        'playable',
        'payable_value',
        'recei_from_cli',
        'expected_date',
        'city',
        'installation_date',
        'installation_location',
        'installation_photo_id',
        'maintenance',
        'type',
        'cause',
        'os_number',
        'manager_id',
        'obs',
        'occurrency_id',
    ];
}
