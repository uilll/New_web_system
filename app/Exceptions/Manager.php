<?php

namespace App\Exceptions;

use Tobuli\Entities\Alert;
use Tobuli\Entities\Chat;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceGroup;
use Tobuli\Entities\Event;
use Tobuli\Entities\EventCustom;
use Tobuli\Entities\Geofence;
use Tobuli\Entities\Report;
use Tobuli\Entities\Route;
use Tobuli\Entities\Task;
use Tobuli\Entities\User;
use Tobuli\Entities\UserDriver;
use Tobuli\Entities\UserMapIcon;

class Manager
{
    protected $user;

    protected $permissionMap;

    public function __construct($user)
    {
        $this->user = $user;

        $this->permissionMap = [
            'show'   => 'edit',
            'view'   => 'view',
            'create' => 'edit',
            'store'  => 'edit',
            'edit'   => 'edit',
            'update' => 'edit',
            'remove' => 'remove',
            'active' => 'edit',
            'clean'  => 'remove',
            'enable' => 'edit',
            'disable' => 'edit'
        ];

        $this->modelMap = [
            'alerts'         => Alert::class,
            'devices'        => Device::class,
            'devices_groups' => DeviceGroup::class,
            'custom_events'  => EventCustom::class,
            'geofences'      => Geofence::class,
            'poi'            => UserMapIcon::class,
            'events'         => Event::class,
            'reports'        => Report::class,
            'routes'         => Route::class,
            'drivers'        => UserDriver::class,
            'tasks'          => Task::class,
            'chats'          => Chat::class,
            'users'          => User::class,

            'camera'         => null,
            'history'        => null,
            'send_command'   => null,
        ];
    }

    public function check($repo, $action, $model = null)
    {
        switch ($action) {
            case 'show':
            case 'edit':
            case 'update':
            case 'remove':
            case 'active':
            case 'enable':
            case 'disable':
            case 'own':
                if (empty($model) && $this->getModelClass($repo))
                    throw new ResourseNotFoundException($this->getModelTrans($repo));
                break;
            case 'view':
            case 'create':
            case 'store':
            case 'clean':
                $model = $this->getModel($repo);
                break;
        }

        if (is_null($model)) {
            if ( ! $this->user->perm($repo, $this->permissionMap[$action]))
                throw new PermissionException();
        } else {
            if ( ! $this->user->can($action, $model))
                throw new PermissionException();
        }
    }

    protected function getModel($repo)
    {
        $class = $this->getModelClass($repo);

        if ($class)
            return new $class();

        return null;
    }

    protected function getModelClass($repo)
    {
        if ( ! array_has($this->modelMap, $repo))
            throw new \Exception('No model class declared');

        return array_get($this->modelMap, $repo);
    }

    protected function getModelTrans($repo)
    {
        switch ($repo) {
            case 'custom_events':
                return "front.event";
            case 'reports':
                return "front.report";
            case 'routes':
                return "front.routes";
            case 'poi':
                return "front.marker";
            case 'drivers':
                return "front.driver";
            default:
                $singular = str_singular($repo);

                return "global.$singular";
        }
    }
}