<?php

namespace Tobuli\Entities;

use Eloquent;

class EventCustomTag extends Eloquent
{
    protected $table = 'event_custom_tags';

    protected $fillable = [
        'event_custom_id',
        'tag',
    ];

    public $timestamps = false;
}
