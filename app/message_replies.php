<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class message_replies extends Model
{
    protected $table = 'message_replies';

    protected $fillable = [
        'message_id',
        'client_id',
        'company_id',
        'user_id',
        'sender_type',
        'body',
        'is_read',
    ];

    public function message()
    {
        return $this->belongsTo('App\Message');
    }
}
