<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tracker extends Model
{
    //
    protected $fillable = [
        'imei',
        'brand',
        'model',
        'active',
        'in_use',
        'test',
        'device_id',
        'last_device_id',
        'in_service',
        'maintence_date',
        'maintence_quant',
        'work_since',
        'history',
        'sim_number',
        'iccd',
        'operator',
        'manager_id',
        'created_at',
        'updated_at',
    ];
}
