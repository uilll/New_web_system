<?php

namespace Tobuli\Entities;

use Eloquent;

class FcmToken extends Eloquent
{
    protected $table = 'fcm_tokens';

    protected $fillable = ['token'];

    public function user()
    {
        return $this->hasOne('Tobuli\Entities\User', 'id', 'user_id');
    }
}
