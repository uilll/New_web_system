<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'client_id',
        'company_id',
        'user_id',
        'is_to_client',
        'subject',
        'body',
        'is_read',
        'sender_type',
    ];

    protected $casts = [
        'is_to_client' => 'boolean',
        'is_read' => 'boolean',
        'sender_type' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function replies()
    {
        return $this->hasMany(\App\message_replies::class);
    }
}
