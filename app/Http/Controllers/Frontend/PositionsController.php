<?php

namespace App\Http\Controllers\Frontend;

use App\Console\PositionsStack;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Tobuli\Repositories\AlertDevice\AlertDeviceRepositoryInterface as AlertDevice;
use Tobuli\Repositories\Config\ConfigRepositoryInterface as Config;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\EmailTemplate\EmailTemplateRepositoryInterface as EmailTemplate;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\EventCustom\EventCustomRepositoryInterface as EventCustom;
use Tobuli\Repositories\Geofence\GeofenceRepositoryInterface as Geofence;
use Tobuli\Repositories\SmsTemplate\SmsTemplateRepositoryInterface as SmsTemplate;
use Tobuli\Repositories\Timezone\TimezoneRepositoryInterface as Timezone;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Repositories\TraccarPosition\TraccarPositionRepositoryInterface as TraccarPosition;
use Tobuli\Repositories\UserDriver\UserDriverRepositoryInterface as UserDriver;

class PositionsController extends Controller
{
    /**
     * @var Device
     */
    private $device;

    /**
     * @var TraccarDevice
     */
    private $traccarDevice;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Geofence
     */
    private $geofence;

    /**
     * @var EmailTemplate
     */
    private $emailTemplate;

    /**
     * @var SmsTemplate
     */
    private $smsTemplate;

    /**
     * @var EventCustom
     */
    private $eventCustom;

    /**
     * @var AlertDevice
     */
    private $alertDevice;

    /**
     * @var TraccarPosition
     */
    private $traccarPosition;

    /**
     * @var UserDriver
     */
    private $userDriver;

    /**
     * @var Timezone
     */
    private $timezone;

    private $address = null;

    private $lang = [];

    private $geofences = [];

    private $template;

    private $sms_template;

    public function __construct(Device $device, TraccarDevice $traccarDevice, Config $config, Event $event, Geofence $geofence, EmailTemplate $emailTemplate, SmsTemplate $smsTemplate, EventCustom $eventCustom, AlertDevice $alertDevice, TraccarPosition $traccarPosition, UserDriver $userDriver, Timezone $timezone)
    {
        $this->device = $device;
        $this->traccarDevice = $traccarDevice;
        $this->config = $config;
        $this->event = $event;
        $this->geofence = $geofence;
        $this->emailTemplate = $emailTemplate;
        $this->smsTemplate = $smsTemplate;
        $this->eventCustom = $eventCustom;
        $this->alertDevice = $alertDevice;
        $this->traccarPosition = $traccarPosition;
        $this->userDriver = $userDriver;
        $this->timezone = $timezone;

        // Load
        $this->lang = [];
        $dirs = File::directories(app_path().'/lang');
        foreach ($dirs as $dir) {
            $lg = explode('/', $dir);
            end($lg);
            $this->lang[$lg[key($lg)]] = require $dir.'/front.php';
        }
    }

    public function insert()
    {
        $input = Input::all();
        if (! isset($input['key']) || $input['key'] != 'Hdaiohaguywhga12344hdsbsdsfsd') {
            return;
        }

        $error = null;
        $required = ['uniqueId' => '', 'fixTime' => '', 'latitude' => '', 'longitude' => '', 'speed' => '', 'altitude' => '', 'course' => '', 'protocol' => ''];

        foreach ($required as $field => $value) {
            if (! isset($input[$field])) {
                $error .= $field.', ';
            }
        }

        if (! is_null($error)) {
            return Response::make(json_encode(['status' => 0, 'message' => 'Missing params: '.substr($error, 0, -2)]), 400);
        }

        $device = $this->device->findWhere(['imei' => $input['imei']]);
        if (empty($device)) {
            return Response::make(json_encode(['status' => 0, 'message' => 'IMEI not found']), 400);
        }

        $data = [
            'fixTime' => strtotime($input['date']) * 1000,
            'valid' => $input['valid'],
            'imei' => $input['uniqueId'],
            'latitude' => $input['latitude'],
            'longitude' => $input['longitude'],
            'attributes' => empty($input['attributes']) ? [] : $input['attributes'],
            'speed' => $input['speed'],
            'altitude' => $input['altitude'],
            'course' => $input['course'],
            'protocol' => $input['protocol'],
        ];

        (new PositionsStack())->add($data);
    }

    private function getGeofenceName($id)
    {
        if (isset($this->geofences[$id])) {
            return $this->geofences[$id];
        }

        $geofence = $this->geofence->find($id);
        $this->geofences[$id] = htmlentities($geofence->name);

        return $geofence->name;
    }

    private function getAddress()
    {
        if (! is_null($this->address)) {
            return $this->address;
        }

        $address = @json_decode(@file_get_contents('http://ztx.lt/app/gmaps/index.php?format=json&lat='.$this->latitude.'&lon='.$this->longitude), true);
        $this->address = isset($address['display_name']) ? $address['display_name'] : '-';

        return $this->address;
    }
}
