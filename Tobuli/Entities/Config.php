<?php

namespace Tobuli\Entities;

use Eloquent;

class Config extends Eloquent
{
    protected $table = 'configs';

    protected $fillable = ['title', 'value'];
}
