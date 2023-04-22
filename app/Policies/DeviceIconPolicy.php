<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class DeviceIconPolicy extends Policy
{
    use HandlesAuthorization;

    protected $permisionKey = null;
}
