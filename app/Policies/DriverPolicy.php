<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class DriverPolicy extends Policy
{
    use HandlesAuthorization;

    protected $permisionKey = null;
}
