<?php

namespace ModalHelpers;

use Carbon\Carbon;
use Facades\Repositories\UserDriverRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\UserDriverFormValidator;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Device;
use Tobuli\Exceptions\ValidationException;

class UserDriverModalHelper extends ModalHelper
{
    public function get()
    {
        $drivers = UserDriverRepo::searchAndPaginate(['filter' => ['user_id' => $this->user->id]], 'id', 'desc', 15);
        $drivers->setPath(route('user_drivers.index'));

        if ($this->api) {
            $drivers = $drivers->toArray();
            $drivers['url'] = route('api.get_user_drivers');
        }

        return compact('drivers');
    }

    public function createData()
    {
        $devices = UserRepo::getDevices($this->user->id)->pluck('plate_number', 'id')->all();

        return compact('devices');
    }

    public function create()
    {
        $this->validate('create');

        $item = UserDriverRepo::create($this->data + ['user_id' => $this->user->id]);

        if (array_get($this->data, 'device_id') && array_get($this->data, 'current')) {
            $user = $this->user;
            $device = Device::whereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->find($this->data['device_id']);

            if ($device) {
                //$device->changeDriver($item);
            }
        }

        return ['status' => 1, 'item' => $item];
    }

    public function editData()
    {
        $id = array_key_exists('user_driver_id', $this->data) ? $this->data['user_driver_id'] : request()->route('user_drivers');

        $item = UserDriverRepo::find($id);

        $this->checkException('drivers', 'edit', $item);

        $devices = UserRepo::getDevices($this->user->id)->pluck('plate_number', 'id')->all();

        return compact('item', 'devices');
    }

    public function edit($passedId = null)
    {
        $id = ($passedId ? $passedId : $this->data['id']);
        $item = UserDriverRepo::find($id);

        // Verificando se a data de vencimento da CNH editada é maior que a data de hoje, se sim altera os status dos alertas
        if (array_key_exists('cnh_expire', $this->data)) {
            $now = date('Y-m-d H:i:s');
            $first = Carbon::createFromFormat('Y-m-d H:i:s', $now);
            $second = Carbon::createFromFormat('Y-m-d', $this->data['cnh_expire']);
            if ($second->greaterThan($first)) {
                DB::table('user_drivers')->where('id', $item->id)->update(['seeing' => 0, 'pre_alert' => 0, 'alert' => 0]);
                //if($first->diffDays($second)<30)
            }
        }
        //###################################################################################################################

        $this->checkException('drivers', 'update', $item);

        try {
            if (! $passedId) {
                $this->validate('silentUpdate');
            }

            UserDriverRepo::update($item->id, $this->data);

            if (array_get($this->data, 'device_id') && array_get($this->data, 'current')) {
                $user = $this->user;
                $device = Device::whereHas('users', function ($query) use ($user) {
                    $query->where('id', $user->id);
                })->find($this->data['device_id']);

                if ($device) {
                    $device->changeDriver($item);
                }
            }

            return ['status' => 1];
        } catch (ValidationException $e) {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    private function validate($type)
    {
        UserDriverFormValidator::validate($type, $this->data);
    }

    public function doDestroy($id)
    {
        $item = UserDriverRepo::find($id);

        $this->checkException('drivers', 'remove', $item);

        return compact('item');
    }

    public function destroy()
    {
        $id = array_key_exists('user_driver_id', $this->data) ? $this->data['user_driver_id'] : $this->data['id'];
        $item = UserDriverRepo::find($id);

        $this->checkException('drivers', 'remove', $item);

        UserDriverRepo::delete($id);

        return ['status' => 1];
    }
}
