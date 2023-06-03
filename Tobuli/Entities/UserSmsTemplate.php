<?php

namespace Tobuli\Entities;

use Eloquent;

class UserSmsTemplate extends Eloquent
{
    protected $table = 'user_sms_templates';

    protected $fillable = [
        'user_id',
        'title',
        'message',
    ];
}
