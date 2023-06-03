<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    //
    protected $table = 'monitorings';

    protected $primaryKey = 'id';

    protected $fillable = [
        'customer',
        'owner',
    ];
}
