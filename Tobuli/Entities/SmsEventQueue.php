<?php

namespace Tobuli\Entities;

use Eloquent;

class SmsEventQueue extends Eloquent
{
    protected $table = 'sms_events_queue';

    protected $fillable = ['user_id', 'phone', 'message'];
}
