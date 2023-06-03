<?php

namespace Tobuli\Importers\Device;

use Facades\Repositories\DeviceRepo;
use Facades\Repositories\TimezoneRepo;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Device;
use Tobuli\Entities\User;
use Tobuli\Exceptions\ValidationException;
use Validator;

abstract class Importer
{
    protected $device_icon_colors = [
        'green', 'yellow', 'red', 'blue', 'orange', 'black',
    ];

    protected $defaults = [
        'visible' => true,
        'active' => true,
        'group_id' => null,
        'icon_id' => 0,
        'fuel_quantity' => 0,
        'fuel_price' => 0,
        'fuel_measurement_id' => 1,
        'min_moving_speed' => 6,
        'min_fuel_fillings' => 10,
        'min_fuel_thefts' => 10,
        'tail_length' => 5,
        'tail_color' => '#33cc33',
        'timezone_id' => null,
        'expiration_date' => '0000-00-00',
        'gprs_templates_only' => false,
        'snap_to_road' => false,
        'icon_colors' => [
            'moving' => 'green',
            'stopped' => 'yellow',
            'offline' => 'red',
            'engine' => 'yellow',
        ],
    ];

    abstract protected function load($file);

    abstract protected function getItems();

    abstract protected function validFormat();

    abstract protected function prepare($data);

    public function import()
    {
        if (! $this->validFormat()) {
            throw new ValidationException('Invalid content for csv device import');
        }

        $items = $this->getItems();

        foreach ($items as $data) {
            $data = $this->prepare($data);
            $data = $this->mergeDefaults($data);
            $data = $this->normalize($data);

            if (! $this->validate($data)) {
                continue;
            }

            $device = $this->getDevice($data);

            if (! $device) {
                if ($this->devicesLimit()) {
                    continue;
                }

                if ($this->usersDeviceLimit($data)) {
                    continue;
                }

                $this->create($data);
            }
        }
    }

    public function mergeDefaults($data)
    {
        foreach ($this->defaults as $key => $value) {
            if (isset($data[$key]) && $data[$key] == '') {
                unset($data[$key]);
            }
        }

        return array_merge($this->defaults, $data);
    }

    public function normalize($data)
    {
        $users = $this->getUsers($data);

        if ($users) {
            $data['user_id'] = $users->pluck('id')->all();
        } else {
            $data['user_id'] = [auth()->user()->id];
        }

        if (empty($data['icon_id'])) {
            $data['icon_id'] = 0;
        }

        $data['fuel_per_km'] = convertFuelConsumption($data['fuel_measurement_id'], $data['fuel_quantity']);

        $statuses = ['moving', 'stopped', 'offline', 'engine'];

        foreach ($statuses as $status) {
            if (isset($data['icon_'.$status]) && in_array($data['icon_'.$status], $this->device_icon_colors)) {
                $data['icon_colors'][$status] = $data['icon_'.$status];
            }
        }

        if (! empty($data['timezone'])) {
            $timezone = $this->getTimezone($data['timezone']);

            $data['timezone_id'] = $timezone ? $timezone->id : null;
        }

        return $data;
    }

    public function validate($data)
    {
        $validator = Validator::make($data, [
            'imei' => 'required',
            'name' => 'required',
            'icon_id' => 'required|exists:device_icons,id',
            'fuel_quantity' => 'numeric',
            'fuel_price' => 'numeric',
            'fuel_measurement_id' => 'required',
            'tail_length' => 'required|numeric|min:0|max:10',
            'min_moving_speed' => 'required|numeric|min:1|max:50',
            'min_fuel_fillings' => 'required|numeric|min:1|max:1000',
            'min_fuel_thefts' => 'required|numeric|min:1|max:1000',
            'group_id' => 'exists:device_groups,id',
            'sim_number' => 'unique:devices,sim_number',
            'timezone_id' => 'exists:timezones,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator->messages());
        }

        return true;
    }

    public function create($data)
    {
        beginTransaction();
        try {
            $device = DeviceRepo::create($data);

            $this->deviceSyncUsers($device, $data);

            $device->createPositionsTable();

            DB::connection('traccar_mysql')->table('unregistered_devices_log')->where('imei', '=', $data['imei'])->delete();
        } catch (\Exception $e) {
            rollbackTransaction();
            throw new ValidationException(['id' => trans('global.unexpected_db_error').$e->getMessage()]);
        }

        commitTransaction();
    }

    public function update(Device $device, $data)
    {
        var_dump('updating');
    }

    protected function defaults($defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    protected function devicesLimit()
    {
        if (isset($_ENV['limit']) && $_ENV['limit'] > 1 && DeviceRepo::countwhere(['deleted' => 0]) >= $_ENV['limit']) {
            throw new ValidationException(['id' => trans('front.devices_limit_reached')]);
        }

        return false;
    }

    protected function usersDeviceLimit($data)
    {
        $users = $this->getUsers($data);

        foreach ($users as $user) {
            if ($this->userDevicesLimit($user)) {
                throw new ValidationException(['id' => $user->email.': '.trans('front.devices_limit_reached')]);
            }
        }

        return false;
    }

    protected function userDevicesLimit($user)
    {
        if (is_null($user->devices_limit)) {
            return false;
        }

        if ($user->isManager()) {
            $user_devices_count = getManagerUsedLimit($user->id);
        } else {
            $user_devices_count = $user->devices->count();
        }

        if ($user_devices_count >= $user->devices_limit) {
            return true;
        }

        return false;
    }

    protected function getDevice($data)
    {
        return DeviceRepo::whereImei($data['imei']);
    }

    protected function getTimezone($timezone)
    {
        return TimezoneRepo::findWhere(['title' => 'UTC '.$timezone]);
    }

    protected function getUsers($data)
    {
        if (! empty($data['user_id']) && is_string($data['user_id'])) {
            $data['user_id'] = explode(',', $data['user_id']);
        }

        if (! empty($data['users'])) {
            $emails = explode(',', $data['users']);
            $emails = array_map('trim', $emails);

            $users = User::whereIn('email', $emails)->get();

            $data['user_id'] = $users ? $users->pluck('id')->all() : [];
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = [auth()->user()->id];
        }

        if (auth()->user()->isManager()) {
            $query = User::whereIn('id', $data['user_id'])->where('manager_id', auth()->user()->id);

            if (in_array(auth()->user()->id, $data['user_id'])) {
                $query->orWhere('id', auth()->user()->group_id);
            }

            return $query->get();
        }

        return User::whereIn('id', $data['user_id'])->get();
    }

    protected function deviceSyncUsers($device, $data)
    {
        $device->users()->sync($data['user_id']);

        DB::table('user_device_pivot')
            ->where('device_id', $device->id)
            ->whereIn('user_id', $data['user_id'])
            ->update([
                'group_id' => $data['group_id'],
                'active' => $data['visible'] ? true : false,
                //'timezone_id' => $data['timezone_id'] == 0 ? NULL : $data['timezone_id']
            ]);
    }
}
