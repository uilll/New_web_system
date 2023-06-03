<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    //
    //public $timestamps = false;
    protected $fillable = [
        'active',
        'name',
        'contact',
        'address',
        'city',
        'active',
        'obs',
        'money_with_hands',
        'manager_id',
        'created_at',
        'updated_at',
    ];
}
