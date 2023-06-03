<?php

namespace ModalHelpers;

use Facades\Repositories\DeviceRepo;
use Facades\Repositories\UserGprsTemplateRepo;
use Facades\Repositories\UserRepo;
use Facades\Repositories\UserSmsTemplateRepo;
use Facades\Validators\SendCommandFormValidator;
use Facades\Validators\SendCommandGprsFormValidator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\Device;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Protocols\Commands;
use Tobuli\Protocols\Manager as ProtocolsManager;
use Validator;

class SendCommandModalHelper extends ModalHelper
{
    public function createData()
    {
        $this->checkException('send_command', 'view');

        $devices = UserRepo::getDevices($this->user->id);
        $devices_gprs = $devices->lists('plate_number', 'id')->all();
        $devices_sms = UserRepo::getDevicesSms($this->user->id)->lists('plate_number', 'id')->all();

        $commands = [
            'engineStop' => trans('front.engine_stop'),
            'engineResume' => trans('front.engine_resume'),
            'alarmArm' => trans('front.alarm_arm'),
            'alarmDisarm' => trans('front.alarm_disarm'),
            'positionSingle' => trans('front.position_single'),
            'positionPeriodic' => trans('front.periodic_reporting'),
            'positionStop' => trans('front.stop_reporting'),
            'movementAlarm' => trans('front.movement_alarm'),
            'setTimezone' => trans('front.set_timezone'),
            'rebootDevice' => trans('front.reboot_device'),
            'sendSms' => trans('front.send_sms'),
            'requestPhoto' => trans('front.request_photo'),
            'custom' => trans('front.custom_command'),
        ];

        $units = [
            'second' => trans('front.second'),
            'minute' => trans('front.minute'),
            'hour' => trans('front.hour'),
        ];

        $number_index = [
            '1' => trans('front.first'),
            '2' => trans('front.second'),
            '3' => trans('front.third'),
            '0' => trans('front.three_sos_numbers'),
        ];

        $actions = [
            '1' => trans('front.on'),
            '0' => trans('front.off'),
        ];

        if ($this->api) {
            $sms_templates = [['id' => '0', 'title' => trans('front.no_template'), 'message' => null]];
            $results = UserSmsTemplateRepo::getWhere(['user_id' => $this->user->id], 'title');
            foreach ($results as $row) {
                array_push($sms_templates, ['id' => $row->id, 'title' => $row->title, 'message' => $row->message]);
            }

            $gprs_templates = [['id' => '0', 'title' => trans('front.no_template'), 'message' => null]];
            $results = UserGprsTemplateRepo::getWhere(['user_id' => $this->user->id], 'title');
            foreach ($results as $row) {
                array_push($gprs_templates, ['id' => $row->id, 'title' => $row->title, 'message' => $row->message]);
            }

            $devices_sms_arr = [];
            foreach ($devices_sms as $key => $value) {
                array_push($devices_sms_arr, ['id' => $key, 'value' => $value]);
            }
            $devices_sms = $devices_sms_arr;

            $devices_gprs_arr = [];
            foreach ($devices_gprs as $key => $value) {
                array_push($devices_gprs_arr, ['id' => $key, 'value' => $value]);
            }
            $devices_gprs = $devices_gprs_arr;

            $commands = apiArray($commands);
            $units = apiArray($units);
            $number_index = apiArray($number_index);
            $actions = apiArray($actions);
        } else {
            $sms_templates = ['0' => trans('front.no_template')] + UserSmsTemplateRepo::getWhere(['user_id' => $this->user->id], 'title')->lists('title', 'id')->all();

            $gprs_templates_only = UserGprsTemplateRepo::getWhere(['user_id' => $this->user->id], 'title')->lists('title', 'id')->all();
            $gprs_templates = ['0' => trans('front.no_template')] + $gprs_templates_only;
        }

        $device_id = request()->get('id');

        $device = UserRepo::getDevice($this->user->id, $device_id);
        $protocol = $device->protocol;
        //dd($device->protocol);

        return compact('devices_sms', 'devices_gprs', 'sms_templates', 'gprs_templates', 'commands', 'units', 'number_index', 'actions', 'device_id', 'protocol');
    }

    public function create()
    {
        $this->checkException('send_command', 'view');

        $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : '';
        $this->data['message'] = isset($this->data['message_sms']) ? $this->data['message_sms'] : $this->data['message'];

        SendCommandFormValidator::validate('create', $this->data);

        $devices = DeviceRepo::getWhereInWith($this->data['devices'], 'id', ['users']);

        foreach ($devices as $device) {
            if (! $this->user->can('show', $device)) {
                continue;
            }

            sendSMS($device->sim_number, $this->data['message']);
        }

        /* try
        {
            if (!Auth::User()->isAdmin())
                throw new ValidationException(['id' => trans('front.sms_gateway_disabled')]);

            SendCommandFormValidator::validate('create', $this->data);
                //var_dump ("teste");
            $devices = DeviceRepo::getWhereInWith($this->data['devices'], 'id', ['users']);


            foreach ($devices as $device) {
                if ( ! $this->user->can('show', $device))
                    continue;

                sendSMS($device->sim_number, $this->data['message']);
            }

            return $this->api ? ['status' => 1] : ['status' => 0, 'trigger' => 'send_command'];
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        } */
    }

    public function gprsCreate()
    {
        $this->checkException('send_command', 'view');

        $device_id = empty($this->data['device_id']) ? 0 : $this->data['device_id'];

        $device = DeviceRepo::find($device_id);

        $this->checkException('devices', 'own', $device);

        try {
            if (false) {// ! $device->isConnected())
                throw new ValidationException(['device_id' => trans('front.no_gprs_connection')]);
            }

            if ($device->gprs_templates_only && ! starts_with($this->data['type'], 'template')) {
                $this->data['type'] = 'template';

                if (empty($this->data['gprs_template_id']) && ! empty($this->data['gprs_template_only_id'])) {
                    $this->data['gprs_template_id'] = $this->data['gprs_template_only_id'];
                }
            }

            SendCommandGprsFormValidator::validate('create', $this->data);

            $commands = $this->getCommands($device);

            $validator = Validator::make($this->data, Commands::validationRules($this->data['type'], $commands));
            if ($validator->fails()) {
                throw new ValidationException($validator->messages());
            }

            $protocolsManager = new ProtocolsManager();
            $data = $protocolsManager->protocol($device->protocol)->buildCommand($device, $this->data);
            //debugar(true, $data);
            $result = send_command($data);

            $res_arr = json_decode($result, true);

            if (is_null($res_arr)) {
                return ['status' => 0, 'trigger' => 'send_command', 'error' => "Failed ($result)", 'result' => $result];
            }

            if (array_key_exists('message', $res_arr)) {
                $message = is_null($res_arr['message']) ? $res_arr['details'] : $res_arr['message'];
                if ($this->api) {
                    throw new ValidationException(['id' => $message]);
                } else {
                    return ['status' => 0, 'trigger' => 'send_command', 'error' => $message, 'result' => $result];
                }
            }

            /*$fp = fopen('/var/www/html/releases/20190129073809/public/debug.txt', "a+");
            fwrite($fp, "\r\n DEBUGER ".json_encode($this->data)." \r\n");
            fclose($fp);*/
            if (str_contains($data['type'], 'engineResume')) {
                DB::table('devices')->where('id', $device->id)->update(['status_block' => false]);
            }
            if (str_contains($data['type'], 'engineStop')) {
                DB::table('devices')->where('id', $device->id)->update(['status_block' => true]);
            }

            return $this->api ? ['status' => 1, 'message' => trans('front.command_sent')] : ['status' => 0, 'trigger' => 'send_command', 'result' => $result];
        } catch (ValidationException $e) {
            return ['status' => 0, 'trigger' => 'send_command', 'errors' => $e->getErrors()];
        }
    }

    public function getDeviceSimNumber()
    {
        $id = array_key_exists('device_id', $this->data) ? $this->data['device_id'] : $this->data['id'];

        $item = DeviceRepo::find($id);

        $this->checkException('devices', 'own', $item);

        return ['sim_number' => $item->sim_number];
    }

    public function getDeviceCommands()
    {
        SendCommandGprsFormValidator::validate('commands', $this->data);

        $id = array_key_exists('device_id', $this->data) ? $this->data['device_id'] : null;

        $device = DeviceRepo::find($id);

        return $this->getCommands($device);
    }

    public function getCommands($devices)
    {
        switch(true) {
            case $devices instanceof Device:
                $devices = new Collection([$devices]);
                break;
            case $devices instanceof Collection:
                break;
            case is_array($devices):
                $devices = new Collection($devices);
                break;
        }

        $devices->load(['traccar', 'users']);

        $filtered = $devices->filter(function ($device) {
            return $this->user->can('own', $device);
        });

        $commands = [];

        $protocolsManager = new ProtocolsManager();

        $gprs_templates = UserGprsTemplateRepo::getUserTemplatesByProtocol($this->user->id, $filtered->pluck('protocol')->all());

        foreach ($filtered as $device) {
            $protocol = $protocolsManager->protocol($device->protocol);

            $deviceCommands = $protocol->getTemplateCommands($gprs_templates, ! $device->gprs_templates_only);

            if (! $device->gprs_templates_only) {
                $deviceCommands = array_merge($protocol->getCommands(), $deviceCommands);
            }

            foreach ($deviceCommands as $deviceCommand) {
                $commands[$deviceCommand['type']] = $deviceCommand;
            }
        }

        return array_values(array_sort($commands, function ($value) {
            return $value['title'];
        }));
    }

    public function validate(Collection $devices, $data)
    {
    }
}
