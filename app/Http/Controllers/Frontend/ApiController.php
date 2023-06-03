<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\DeviceModalHelper;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\SmsEventQueueRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Mockery\CountValidator\Exception;
use Tobuli\Exceptions\ValidationException;
use Validator;

class ApiController extends Controller
{
    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()], 422);
        }

        if (isPublic()) {
            if ($user = \Facades\RemoteUser::getByCredencials($this->data['email'], $this->data['password'])) {
                return [
                    'status' => 1,
                    'user_api_hash' => $user->api_hash,
                    'permissions' => $user->getPermissions(),
                ];
            }
        } else {
            if (Auth::attempt(['email' => $this->data['email'], 'password' => $this->data['password']], ['active' => '1'])) {
                if (empty(Auth::User()->api_hash)) {
                    while (! empty(UserRepo::findWhere(['api_hash' => $hash = Hash::make(Auth::User()->email.':'.$this->data['password'])])));
                    Auth::User()->api_hash = $hash;
                    Auth::User()->save();
                }

                return [
                    'status' => 1,
                    'user_api_hash' => Auth::User()->api_hash,
                    'permissions' => Auth::User()->getPermissions(),
                ];
            }
        }

        return response()->json(['status' => 0, 'message' => trans('front.login_failed')], 401);
    }

    public function loginApp($email, $password)
    {
        $array = [
            'email' => "$email",
            'password' => "$password",
        ];

        $remember_me = config('session.remember_me') && (Input::get('remember_me') == 1 ? true : false);

        if (Auth::attempt(array_merge($array, ['active' => '1']), $remember_me)) {
            $devices = UserRepo::getDevicesWith(Auth::User()->id, [
                'devices',
                'devices.sensors',
                'devices.services',
                'devices.driver',
                'devices.traccar',
                'devices.icon',
                'devices.users',
            ]);

            for ($i = 0; count($devices) > $i; $i++) {
                $devices[$i]->traccar->server_time = date('d/m/Y H:i', strtotime('-3 hour', strtotime($devices[$i]->traccar->server_time)));
                $latitude_x = $devices[$i]->traccar->lastValidLatitude;
                $longetude_x = $devices[$i]->traccar->lastValidLongitude;

                if ($latitude_x !== null && $latitude_x !== '' && $longetude_x !== null && $longetude_x !== '') {
                    $url = 'https://sistema.carseg.com.br/get_add.php';
                    $timeout = 60;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => $latitude_x, 'lng' => $longetude_x, 'map' => 'CARSEG']));
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $data = json_decode($data);

                    $devices[$i]->traccar->address = $data->road.', '.$data->city.', '.$data->state;
                } else {
                    $devices[$i]->traccar->address = '';
                }
            }

            return	$devices;
        } else {
            return 0;
        }
    }

public function obtemdispositivosapp($iduser, $latitudes, $longitudes)
{
    //var_dump($latitudes);
    //var_dump($longitudes);
    $array_latitudes = explode(',', $latitudes);
    $array_longitudes = explode(',', $longitudes);

    $devices = UserRepo::getDevicesWith($iduser, [
        'devices',
        'devices.sensors',
        'devices.services',
        'devices.driver',
        'devices.traccar',
        'devices.icon',
        'devices.users',
    ]);

    //	echo '<pre>';
    //var_dump($devices[1]);

    for ($i = 0; count($devices) > $i; $i++) {
        $devices[$i]->traccar->server_time = date('d/m/Y H:i', strtotime('-3 hour', strtotime($devices[$i]->traccar->server_time)));
        $latitude_x = $devices[$i]->traccar->lastValidLatitude;
        $longetude_x = $devices[$i]->traccar->lastValidLongitude;
        $latitude_x_atual = $array_latitudes[$i];
        $longetude_x_atual = $array_longitudes[$i];
        //echo $latitude_x;echo $longetude_x;

        if ($latitude_x !== null && $latitude_x !== '' && $longetude_x !== null && $longetude_x !== '') {
            //echo $latitude_x_atual;echo $latitude_x ;echo is_numeric($latitude_x) ;echo is_numeric($latitude_x_atual) ;
            if ($devices[$i]->traccar->lastValidLatitude == $array_latitudes[$i] && $devices[$i]->traccar->lastValidLongitude == $array_longitudes[$i]) {
                //echo "nada !!";
            } else {
                $url = 'https://sistema.carseg.com.br/get_add.php';
                $timeout = 60;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => $devices[$i]->traccar->lastValidLatitude, 'lng' => $devices[$i]->traccar->lastValidLongitude, 'map' => 'CARSEG']));
                $data = curl_exec($ch);
                curl_close($ch);
                $data = json_decode($data);

                $devices[$i]->traccar->address = $data->road.', '.$data->city.', '.$data->state;
            }
        } else {
            $devices[$i]->traccar->address = '';
        }
    }
    if (count($devices) == 0) {
        return 0;
    }

    return	$devices;
}

    public function save_token($id = null, $token = null, $code_security = null)
    {
        //dd($code_security);
        if ($code_security == '5RPr8pP2Q8neSa3S0oU4G6OTAjDB0IMhEwzYcGVvO') {
            if ((isset($id) && ! is_null($id)) && (isset($token) && ! is_null($token))) {
                $id = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', '$1$3', $id);
                $id = strip_tags($id);
                $id = preg_replace('/[^\d]+/', '', $id);
                $token = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', '$1$3', $token);
                $token = strip_tags($token);

                $user = UserRepo::find($id);
                if (! empty($user)) {
                    DB::table('users')->where('id', $id)->update(['api_hash' => $token]);
                    //$token_ = DB::table('users')->where('id', $id)->update(['api_hash' => $token]);
                    return 'Token Salvo: '.$token;
                } else {
                    return 'Erro1';
                }
            } else {
                return 'Erro2';
            }
        } else {
            return 'Erro3';
        }
    }

    public function getSmsEvents()
    {
        UserRepo::updateWhere(['id' => $this->user->id], ['sms_gateway_app_date' => date('Y-m-d H:i:s')]);
        $items = SmsEventQueueRepo::getWhereSelect(['user_id' => $this->user->id], ['id', 'phone', 'message'], 'created_at')->toArray();

        if (! empty($items)) {
            SmsEventQueueRepo::deleteWhereIn(array_pluck($items, 'id'));
        }

        return [
            'status' => 1,
            'items' => $items,
        ];
    }

    //
    // Devices
    //

    public function getDevices(\ModalHelpers\DeviceModalHelper $deviceModalHelper)
    {
        $grouped = [];

        if ($this->user->perm('devices', 'view')) {
            $devices = UserRepo::getDevicesWith($this->user->id, [
                'devices',
                'devices.sensors',
                'devices.services',
                'devices.driver',
                'devices.traccar',
                'devices.icon',
                'devices.users',
            ]);

            $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();

            $grouped = [];

            foreach ($devices as $device) {
                $group_id = empty($device->pivot->group_id) ? 0 : $device->pivot->group_id;
                if (! isset($grouped[$group_id])) {
                    $grouped[$group_id] = [
                        'title' => $device_groups[$group_id],
                        'items' => [],
                    ];
                }

                $grouped[$group_id]['items'][] = $deviceModalHelper->generateJson($device, false, true);
            }

            unset($devices);

            $grouped = array_values($grouped);
        }

        return $grouped;
    }

    public function getDevicesJson()
    {
        $data = DeviceModalHelper::itemsJson();

        return $data;
    }

    public function getUserData()
    {
        $dStart = new \DateTime(date('Y-m-d H:i'));
        $dEnd = new \DateTime($this->user->subscription_expiration);
        $dDiff = $dStart->diff($dEnd);
        $days_left = $dDiff->days;

        $plan = Config::get('tobuli.plans.'.$this->user->devices_limit);
        if (empty($plan)) {
            $plan = isset($this->user->billing_plan->title) ? $this->user->billing_plan->title : null;
            if (empty($plan)) {
                $plan = trans('admin.group_'.$this->user->group_id);
            }
        }

        return [
            'email' => $this->user->email,
            'expiration_date' => $this->user->subscription_expiration != '0000-00-00 00:00:00' ? datetime($this->user->subscription_expiration) : null,
            'days_left' => $this->user->subscription_expiration != '0000-00-00 00:00:00' ? $days_left : null,
            'plan' => $plan,
            'devices_limit' => intval($this->user->devices_limit),
            'group_id' => $this->user->group_id,
        ];
    }

    public function setDeviceExpiration()
    {
        if (! isAdmin()) {
            return response()->json(['status' => 0, 'error' => trans('front.dont_have_permission')], 403);
        }

        $validator = Validator::make(request()->all(), [
            'imei' => 'required',
            'expiration_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()], 400);
        }

        $device = \Tobuli\Entities\Device::where('imei', request()->get('imei'))->first();

        if (! $device) {
            return response()->json(['status' => 0, 'errors' => ['imei' => dontExist('global.device')]], 400);
        }

        $device->expiration_date = request()->get('expiration_date');
        $device->save();

        return response()->json(['status' => 1], 200);
    }

    public function enableDeviceActive()
    {
        $validator = Validator::make(request()->all(), ['id' => 'required']);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $device = \Tobuli\Entities\Device::find(request('id'));

        $this->checkException('devices', 'enable', $device);

        if (! $device->active) {
            $device->update(['active' => true]);
        }

        return response()->json(['status' => 1], 200);
    }

    public function disableDeviceActive()
    {
        $validator = Validator::make(request()->all(), ['id' => 'required']);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $device = \Tobuli\Entities\Device::find(request('id'));

        $this->checkException('devices', 'disable', $device);

        if ($device->active) {
            $device->update(['active' => false]);
        }

        return response()->json(['status' => 1], 200);
    }

    public function geoAddress()
    {
        if (empty($this->data['lat']) || empty($this->data['lon'])) {
            return '-';
        }

        return getGeoAddress($this->data['lat'], $this->data['lon']);
    }

    public function setFcmToken()
    {
        $token = $this->user->fcm_tokens()->firstOrNew([
            'token' => $this->data['token'],
        ]);
        $token->save();

        return response()->json(['status' => 1], 200);
    }

    public function getServicesKeys()
    {
        $services = [];

        $services['maps']['google']['key'] = config('services.google_maps.key');

        return response()->json(['status' => 1, 'items' => $services], 200);
    }

    public function __call($name, $arguments)
    {
        [$class, $method] = explode('#', $name);

        try {
            try {
                $class = App::make("App\Http\Controllers\Frontend\\".$class);
                $response = App::call([$class, $method]);
            } catch (\ReflectionException $e) {
                return response()->json(['status' => 0, 'message' => 'Method does not exist!'], 500);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Server error: '.$e->getMessage().' ('.$e->getFile().':'.$e->getLine().')'], 500);
        }

        if (! array_key_exists('status', $response)) {
            $response['status'] = 1;
        }

        $status_code = 200;
        if ($response['status'] == 0) {
            $status_code = 400;
        }

        if (array_key_exists('perm', $response)) {
            $status_code = 403;
        }

        return response()->json($response, $status_code);
    }

    public function obtemposicaoatualapp($user_id, $device_id)
    {
        $item = UserRepo::getDevice($user_id, $device_id);
        $item->lat = $item->lat;
        $item->lng = $item->lng;

        if ($item->lat !== null && $item->lat !== '' && $item->lng !== null && $item->lng !== '') {
            $url = 'https://sistema.carseg.com.br/get_add.php';
            $timeout = 60;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => $item->lat, 'lng' => $item->lng, 'map' => 'CARSEG']));
            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data);

            $item->traccar->address = $data->road.', '.$data->city.', '.$data->state;
        }

        $item->traccar->server_time = date('d/m/Y H:i', strtotime('-3 hour', strtotime($item->traccar->server_time)));

        return $item;
    }

    public function obtemposicaoatualrefreshapp($user_id, $device_id, $latitude, $longitude)
    {
        $item = UserRepo::getDevice($user_id, $device_id);
        $item->lat = $item->lat;
        $item->lng = $item->lng;

        if ($item->lat !== null && $item->lat !== '' && $item->lng !== null && $item->lng !== '') {
            //echo $latitude_x_atual;echo $latitude_x ;echo is_numeric($latitude_x) ;echo is_numeric($latitude_x_atual) ;
            if ($item->lat == $latitude && $item->lng == $longitude) {
                //echo "nada !!";
            } else {
                $url = 'https://sistema.carseg.com.br/get_add.php';
                $timeout = 60;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => $item->lat, 'lng' => $item->lng, 'map' => 'CARSEG']));
                $data = curl_exec($ch);
                curl_close($ch);
                $data = json_decode($data);

                $item->traccar->address = $data->road.', '.$data->city.', '.$data->state;
            }
        } else {
            $item->traccar->address = '';
        }
        $item->traccar->server_time = date('d/m/Y H:i', strtotime('-3 hour', strtotime($item->traccar->server_time)));

        return $item;
    }

    public function listarDispositivos($iduser)
    {
        $devices = UserRepo::getDevicesWith($iduser, [
            'devices',
            'devices.sensors',
            'devices.services',
            'devices.driver',
            'devices.traccar',
            'devices.icon',
            'devices.users',
        ]);

        for ($i = 0; count($devices) > $i; $i++) {
            $latitude_x = $devices[$i]->traccar->lastValidLatitude;
            $longetude_x = $devices[$i]->traccar->lastValidLongitude;
            $devices[$i]->traccar->server_time = date('d/m/Y H:i', strtotime('-3 hour', strtotime($devices[$i]->traccar->server_time)));
            if ($latitude_x !== null && $latitude_x !== '' && $longetude_x !== null && $longetude_x !== '') {
                $url = 'https://sistema.carseg.com.br/get_add.php';
                $timeout = 60;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => $latitude_x, 'lng' => $longetude_x, 'map' => 'CARSEG']));
                $data = curl_exec($ch);
                curl_close($ch);
                $data = json_decode($data);

                $devices[$i]->traccar->address = $data->road.', '.$data->city.', '.$data->state;
            } else {
                $devices[$i]->traccar->address = '';
            }
        }

        return	$devices;
    }

    public function testemapa()
    {
        $url = 'https://sistema.carseg.com.br/get_add.php';
        $timeout = 60;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['lat' => '-9.383721', 'lng' => '-40.520584', 'map' => 'CARSEG']));
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data);

        var_dump($data->road.','.$data->city.','.$data->state);
    }
}
