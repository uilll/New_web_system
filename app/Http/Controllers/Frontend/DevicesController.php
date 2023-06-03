<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Facades\ModalHelpers\DeviceModalHelper;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Device;

class DevicesController extends Controller
{
    public function create()
    {
        if (Auth::user()->isAdmin() || Auth::user()->isManager()) {
            $data = DeviceModalHelper::createData();

            return is_array($data) && ! $this->api ? view('front::Devices.create')->with($data) : $data;
        }
    }

    public function store()
    {
        if (Auth::user()->isAdmin() || Auth::user()->isManager()) {
            return DeviceModalHelper::create();
        }
    }

    public function edit($id = null, $admin = false)
    {
        if (Auth::user()->isAdmin() || Auth::user()->isManager()) {
            $data = DeviceModalHelper::editData();

            return is_array($data) && ! $this->api ? view('front::Devices.edit')->with(array_merge($data, ['admin' => $admin])) : $data;
        }
    }

    public function update()
    {
        if (Auth::user()->isAdmin() || Auth::user()->isManager()) {
            return DeviceModalHelper::edit();
        }
    }

    public function transfer()
    {
        //
        if (Auth::user()->isAdmin() || Auth::user()->isManager()) {
            if (array_key_exists('id', $this->data)) {
                $device_id = $this->data['id'];
            } else {
                $device_id = request()->route('id');
            }

            if (empty($device_id)) {
                $device_id = empty($this->data['device_id']) ? null : $this->data['device_id'];
            }

            $item = DeviceRepo::find($device_id);

            if (Auth::User()->isManager()) {
                $user_ = Auth::User()->id;
            } else {
                $user_ = 0;
            }

            $devices = DB::table('devices')
                        ->where('manager_id', $user_)
                        ->where('name', 'like', '%TESTE%')
                        ->select('plate_number', 'id')
                        ->get();
            $devices = collect($devices);

            return view('front::Devices.transfer')->with(compact('item', 'devices'));
        }
    }

    public function transfer_now()
    {
        try {
            //debugar(true,"Início");
            $device_id = request()->get('id');
            $new_device_id = request()->get('new_id');

            $old_device = DB::table('devices')->find($device_id);
            //debugar(true,"C1");
            $old_device_traccar = DB::connection('traccar_mysql')->table('devices')->where('id', $old_device->traccar_device_id)->get();
            //debugar(true,"C2");
            $old_positions = DB::connection('traccar_mysql')->select('select * from positions_'.$old_device->traccar_device_id);
            //debugar(true,"C3");
            $new_device = DB::table('devices')->find($new_device_id);

            $pivots = DB::table('user_device_pivot')->where('device_id', $old_device->id)->get();
            foreach ($pivots as $pivot) {
                switch ($pivot->user_id) {
                    case 2:
                        break;
                    case 3:
                        break;
                    case 6:
                        break;
                    case 950:
                        break;
                    default:
                        DB::table('user_device_pivot')
                        ->where('device_id', $old_device->id)
                        ->where('user_id', $pivot->user_id)
                        ->update([
                            'device_id' => $new_device_id,
                        ]);
                        break;
                }
            }
            //debugar(true,json_encode($pivot));
            //dd('oi');

            //debugar(true,"Fazendo mudanças");
            DB::table('devices')->where('id', $new_device_id)
            ->update([
                'current_driver_id' => $old_device->current_driver_id,
                'icon_id' => $old_device->icon_id,
                'active' => $old_device->active,
                'name' => $old_device->name,
                'fuel_measurement_id' => $old_device->fuel_measurement_id,
                'fuel_quantity' => $old_device->fuel_quantity,
                'fuel_price' => $old_device->fuel_price,
                'fuel_per_km' => $old_device->fuel_per_km,
                'device_model' => $old_device->device_model,
                'plate_number' => $old_device->plate_number,
                'vin' => $old_device->vin,
                'registration_number' => $old_device->registration_number,
                'object_owner' => $old_device->object_owner,
                'cliente_id' => $old_device->cliente_id,
                'contact' => $old_device->contact,
                'additional_notes' => $old_device->additional_notes,
                'engine_hours' => $old_device->engine_hours,
                'detect_engine' => $old_device->detect_engine,
                'insta_loc' => $old_device->insta_loc,
                'installation_date' => $old_device->installation_date,
                'maintence_date' => Carbon::now(-3),
                'chassis' => $old_device->chassis,
                'renavam' => $old_device->renavam,
                'model_year' => $old_device->model_year,
                'vehicle_color' => $old_device->vehicle_color,
                'city' => $old_device->city,
                'user_owner' => $old_device->user_owner,
                'passwor_owner' => $old_device->passwor_owner,
                'no_powercut' => $old_device->no_powercut,
                'anchor' => $old_device->anchor,
            ]);

            //debugar(true,"mudanças no traccar_web feitas");

            foreach ($old_device_traccar as $device) {
                DB::connection('traccar_mysql')->table('devices')->where('id', $new_device->traccar_device_id)
                ->update([
                    'name' => $device->name,
                ]);
            }

            //debugar(true,"inicio mudança de posições, deletando");
            DB::connection('traccar_mysql')->table('positions_'.$new_device->traccar_device_id)->truncate();
            //debugar(true,"deletar mudanças ok, iniciando cópia");
            foreach ($old_positions as $position) {
                //debugar(true, json_encode($new_device->traccar_device_id));
                DB::connection('traccar_mysql')->table('positions_'.$new_device->traccar_device_id)->insert(
                    ['device_id' => $position->device_id,
                        'altitude' => $position->altitude,
                        'course' => $position->course,
                        'latitude' => $position->latitude,
                        'longitude' => $position->longitude,
                        'other' => $position->other,
                        'power' => $position->power,
                        'speed' => $position->speed,
                        'time' => $position->time,
                        'device_time' => $position->device_time,
                        'server_time' => $position->server_time,
                        'sensors_values' => $position->sensors_values,
                        'valid' => $position->valid,
                        'distance' => $position->distance,
                        'protocol' => $position->protocol]
                );
            }

            //Alterando os dados do veículo antigo
            DB::table('devices')->where('id', $old_device->id)
            ->update([
                'name' => 'TESTE DE RASTREADOR',
                'device_model' => '',
                'plate_number' => 'CAR-'.substr($old_device->imei, -4),
                'vin' => '',
                'registration_number' => '',
                'object_owner' => 'ex-proprietário: '.$old_device->object_owner,
                'cliente_id' => '',
                'contact' => '',
                'additional_notes' => 'RASTREADOR REMOVIDO DE CLIENTE '.$old_device->additional_notes,
                'maintence_date' => Carbon::now(-3),
                'chassis' => '',
                'renavam' => '',
                'model_year' => '',
                'vehicle_color' => '',
                'city' => '',
                'user_owner' => '',
                'passwor_owner' => '',
                'no_powercut' => false,
                'anchor' => false,
            ]);

            return ['status' => 1];
        } catch (ValidationException $e) {
            debugar(true, json_encode($e->getErrors()));

            return ['status' => 0, 'errors' => $e->getErrors()];
        }

        //debugar(true,json_encode($old_device));
    }

    public function changeActive()
    {
        return DeviceModalHelper::changeActive();
    }

    public function destroy()
    {
        if (config('tobuli.object_delete_pass') && Auth::user()->isAdmin() && request('password') != config('tobuli.object_delete_pass')) {
            return ['status' => 0, 'errors' => ['message' => trans('front.login_failed')]];
        }

        return DeviceModalHelper::destroy();
    }

    public function doDestroy($id)
    {
        return view('front::Devices.destroy', compact('id'));
    }

    public function stopTime($device_id = null)
    {
        if (is_null($device_id)) {
            $device_id = request()->get('device_id');
        }

        $device = DeviceRepo::getWithFirst(['traccar', 'users', 'sensors'], ['id' => $device_id]);

        $this->checkException('devices', 'show', $device);

        return ['time' => $device->stopDuration];
    }

    public function followMap(int $device_id)
    {
        $item = UserRepo::getDevice($this->user->id, $device_id);

        $this->checkException('devices', 'show', $item);

        $item->lat = $item->lat;
        $item->lng = $item->lng;
        $item->speed = $item->speed;
        $item->course = $item->course;
        $item->altitude = $item->altitude;
        $item->protocol = $item->getProtocol();

        $item->time = $item->time;
        $item->timestamp = $item->timestamp;
        $item->acktimestamp = $item->acktimestamp;

        $item->tail = $item->tail;
        $item->online = $item->getStatus();
        //dd($item);
        return view('front::Devices.follow_map', compact('item'));
    }
}
