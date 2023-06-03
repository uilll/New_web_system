<?php

namespace App\Providers;

use App\Policies\AlertPolicy;
use App\Policies\ChatPolicy;
use App\Policies\DeviceGroupPolicy;
use App\Policies\DeviceIconPolicy;
use App\Policies\DevicePolicy;
use App\Policies\DriverPolicy;
use App\Policies\EventCustomPolicy;
use App\Policies\EventPolicy;
use App\Policies\GeofenceGroupPolicy;
use App\Policies\GeofencePolicy;
use App\Policies\POIPolicy;
use App\Policies\ReportLogPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RoutePolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Tobuli\Entities\Alert;
use Tobuli\Entities\Chat;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceGroup;
use Tobuli\Entities\DeviceIcon;
use Tobuli\Entities\Event;
use Tobuli\Entities\EventCustom;
use Tobuli\Entities\Geofence;
use Tobuli\Entities\GeofenceGroup;
use Tobuli\Entities\Report;
use Tobuli\Entities\ReportLog;
use Tobuli\Entities\Route;
use Tobuli\Entities\Task;
use Tobuli\Entities\User;
use Tobuli\Entities\UserDriver;
use Tobuli\Entities\UserMapIcon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Alert::class => AlertPolicy::class,
        Device::class => DevicePolicy::class,
        DeviceGroup::class => DeviceGroupPolicy::class,
        DeviceIcon::class => DeviceIconPolicy::class,
        Geofence::class => GeofencePolicy::class,
        GeofenceGroup::class => GeofenceGroupPolicy::class,
        UserMapIcon::class => POIPolicy::class,
        Report::class => ReportPolicy::class,
        ReportLog::class => ReportLogPolicy::class,
        Route::class => RoutePolicy::class,
        Chat::class => ChatPolicy::class,
        Task::class => TaskPolicy::class,
        EventCustom::class => EventCustomPolicy::class,
        Event::class => EventPolicy::class,
        UserDriver::class => DriverPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
    }
}
