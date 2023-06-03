<?php

namespace Tobuli\Entities;

use Eloquent;

class Route extends Eloquent
{
    protected $table = 'routes';

    protected $fillable = ['user_id', 'name', 'active', 'color'];

    protected $hidden = ['polyline'];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo('Tobuli\Entities\User', 'user_id', 'id');
    }
}
