<?php

namespace Tobuli\Entities;

use Eloquent;

class Subscription extends Eloquent
{
    protected $table = 'subscriptions';

    protected $fillable = ['name', 'period_name', 'devices_limit', 'days', 'trial', 'price'];
}
