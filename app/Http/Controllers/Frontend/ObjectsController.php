<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
//use GuzzleHttp\Cookie\CookieJar;
use App\Monitoring;
use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;
use Carbon\Carbon;
use Facades\ModalHelpers\DeviceModalHelper;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\GeofenceGroupRepo;
//use Illuminate\Support\Facades\Request; //OBS:: SE DER ALGUM ERRO POR CONTA DESTE REQUEST, RETORNAR ESTA LINHA E COMENTAR A PRÓXIMA DE REQUEST
use Facades\Repositories\MapIconRepo;
use Facades\Repositories\TimezoneRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Log;
use ModalHelpers\SendCommandModalHelper;
use Spatie\ArrayToXml\ArrayToXml;
use Tobuli\Entities\Device;
use Tobuli\Repositories\Device\EloquentDeviceRepository;
use Watson\Autologin\Facades\Autologin as Autologin;

//include_once('\vendor\Phayes\GeoPHP\geoPHP.inc');

class ObjectsController extends Controller
{
    private $section = 'objects';

    /**
     * @var Device
     */
    private $device;

    /**
     * @var TraccarDevice
     */
    private $traccarDevice;

    /**
     * @var Event
     */
    private $event;

    public function __construct(EloquentDeviceRepository $device)
    {
        parent::__construct();
        $this->device = $device;
    }

    public function index()
    {
        $version = Config::get('tobuli.version');
        $devices = [];
        if ($this->user->perm('devices', 'view')) {
            //$devices = UserRepo::getDevices($this->user->id);
            $devices = UserRepo::getDevicesWith($this->user->id, ['devices', 'devices.sensors', 'devices.traccar']);
        }
        if (! empty($devices)) {
            $devices = $devices->pluck('plate_number', 'id')->all();
        }

        /* $collection = collect($devices);
        $sortedData = $collection->sortBy('group_id');
        $devices = (object) $sortedData; */

        $history = [
            'start' => date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s')))),
            'end' => date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s')))),
            'end_time' => '23:45',
            'def_device' => null,
        ];

        $notifications = $this->user->unreadNotifications;
        $notifications->markAsRead();

        $mapIcons = MapIconRepo::all();

        $geofence_groups = ['0' => trans('front.ungrouped')] + GeofenceGroupRepo::getWhere(['user_id' => $this->user->id])->pluck('title', 'id')->all();

        return view('front::Objects.index')->with(compact('devices', 'history', 'version', 'geofence_groups', 'mapIcons', 'notifications'));
    }

     public function indexApp()
     {
         $version = Config::get('tobuli.version');
         $devices = [];
         if ($this->user->perm('devices', 'view')) {
             //$devices = UserRepo::getDevices($this->user->id);
             $devices = UserRepo::getDevicesWith($this->user->id, ['devices', 'devices.sensors', 'devices.traccar']);
         }
         if (! empty($devices)) {
             $devices = $devices->pluck('object_owner', 'object_owner')->all();
         }

         $history = [
             'start' => date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s')))),
             'end' => date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s')))),
             'end_time' => '23:45',
             'def_device' => null,
         ];

         $notifications = $this->user->unreadNotifications;
         $notifications->markAsRead();

         $mapIcons = MapIconRepo::all();

         $geofence_groups = ['0' => trans('front.ungrouped')] + GeofenceGroupRepo::getWhere(['user_id' => $this->user->id])->pluck('title', 'id')->all();
         $tudo = compact('devices', 'history', 'version', 'geofence_groups', 'mapIcons', 'notifications');
         $dev = $tudo['devices'];

         return json_encode($devices);
         // return view('front::Objects.index')->with(compact('devices', 'history', 'version', 'geofence_groups', 'mapIcons','notifications'));
     }

    public function items($page = null, $search_item = null, $search_type = null, $veic_page = 10)
    {
        try {
            //dd('oi');
            $page = sanitization($page, 2);
            $search_item = sanitization($search_item, 1);
            $veic_page = sanitization($veic_page, 2);
            //dd('oi');

            $timezones = TimezoneRepo::getList();
            $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id], 'title')->pluck('title', 'id')->all();
            $device_groups_opened = array_flip(json_decode($this->user->open_device_groups, true));
            //dd('oi');
            $grouped = [];
            $drivers = [];
            $total_paginas = [];
            $stateandcity = [];
            $stateandcity2 = [];
            $key2 = [];
            $devices2 = [];
            $devices3 = [];
            $total_devices = 0;
            $item_encontrado = 0;
            $item_key2 = 0;
            $city_key_count = 0;
            $keys_selecionadas = [];
            $city_keys_selecionadas = [];
            if ($this->user->perm('devices', 'view')) {
                if (! isAdmin()) {
                    $devices = UserRepo::getDevicesWith($this->user->id, [
                        'devices',
                        'devices.sensors',
                        'devices.services',
                        'devices.driver',
                        'devices.traccar',
                        'devices.icon',
                    ]);
                } else {
                    //#######################################################
                    //CÓDIGO ABAIXO PARA TESTE
                    if (Auth::User()->id == 6) {
                        //dd(Auth::User());
                    }

                    //#######################################################

                    if (Cookie::has('devices_page')) {
                        $veic_page = Cookie::get('devices_page');
                    }
                    if (Cookie::has('current_page')) {
                        $page = (int) Cookie::get('current_page');
                    }
                    //dd($page);
                    if ($search_item == '' || $search_item == null) {
                        $devices = UserRepo::getDevicesWith($this->user->id, [
                            'devices',
                            'devices.sensors',
                            'devices.services',
                            'devices.driver',
                            'devices.traccar',
                            'devices.icon',
                        ])->sortBy('object_owner');
                    } else {
                        //dd($search_type);

                        //PESQUISAR PELA PROXIMIDADE DA CIDADE
                        if ($search_type == 'city') {
                            if (true) {
                                $dados = explode('-', $search_item);
                                $lat_lon_local = getlatlon($dados);
                                $devices = UserRepo::getDevicesWith($this->user->id, [
                                    'devices',
                                    'devices.sensors',
                                    'devices.services',
                                    'devices.driver',
                                    'devices.traccar',
                                    'devices.icon',
                                ])
                                    ->filter(function ($device) use ($lat_lon_local, $dados) {
                                        //dd($device);
                                        if (! is_null($device->traccar)) {
                                            if (is_numeric($device->traccar->lastValidLatitude) and is_numeric($device->traccar->lastValidLongitude)) {
                                                $distancia = getDistance($lat_lon_local[0], $lat_lon_local[1], $device->traccar->lastValidLatitude, $device->traccar->lastValidLongitude);
                                                //debugar(true,$distancia);
                                                if ($dados[0] == '') {
                                                    $dist = 250;
                                                } else {
                                                    $dist = 15;
                                                }
                                                if ($distancia < $dist) {
                                                    return $device;
                                                }
                                            }
                                        } else {
                                            //debugar(true, "2");
                                        }
                                    });
                                //->sortBy('object_owner');
                                //dd("oi");
                                if ($devices->count() == 0) {
                                    return false;
                                }
                            }
                        } else {
                            // PESQUISAR PELOS DEMAIS TERMOS
                            //dd($search_type, $search_item);
                            if ($search_type == 'protocol') {
                                $devices_ids = DB::connection('traccar_mysql')
                                                    ->table('devices')
                                                    ->where('protocol', 'LIKE', '%'.Str::lower($search_item).'%')
                                                    ->pluck('uniqueId');
                                $devices = UserRepo::getDevicesWith($this->user->id, [
                                    'devices',
                                    'devices.sensors',
                                    'devices.services',
                                    'devices.driver',
                                    'devices.traccar',
                                    'devices.icon',
                                ])
                                    ->filter(function ($device) use ($devices_ids) {
                                        if (in_array($device->imei, $devices_ids)) {
                                            return $device;
                                        }
                                    })
                                    ->sortBy('object_owner');
                            } else {
                                $devices_ids = DB::table('devices')
                                                ->select('id')
                                                ->where(Str::lower($search_type), 'LIKE', '%'.Str::lower($search_item).'%')
                                                ->pluck('id');
                                $devices = UserRepo::getDevicesWith($this->user->id, [
                                    'devices',
                                    'devices.sensors',
                                    'devices.services',
                                    'devices.driver',
                                    'devices.traccar',
                                    'devices.icon',
                                ])
                                    ->filter(function ($device) use ($devices_ids) {
                                        if (in_array($device->id, $devices_ids)) {
                                            return $device;
                                        }
                                    })
                                    ->sortBy('object_owner');
                            }

                            if ($devices->count() == 0) {
                                return false;
                            }
                        }
                    }
                    $devices = $devices->paginate($veic_page, 'page', $page);

                    $total_veiculos = $devices->count();
                    $total_paginas = ceil($total_veiculos / $veic_page);
                    $devices_orig = $devices;
                    //$devices->toArray();
                    foreach ($devices as $key => $device) {
                        $devices_[$key] = $device; //dd($device);
                    }
                    $devices = $devices_;
                }
                $card = false;
                foreach ($devices as $key => $device) {
                    //
                    if (false) {//strpos($device->registration_number,"ST940") !== FALSE){
                            //dd($device);
                        $matches3 = [];
                        $handle = @fopen('/opt/traccar/logs/tracker-server.log', 'r');
                        //fclose($handle);
                        if ($handle) {
                            while (! feof($handle)) {
                                $buffer = fgets($handle);
                                /*if(strpos($buffer, "20569") !== FALSE){

                                    $matches = $buffer;

                                }*/
                                //$text = bin2hex("ST910;Location;"+$device->imei);
                                if (strpos($buffer, '53543931303b4c6f636174696f6e3b'.bin2hex($device->imei)) !== false) {
                                    $pos_ini = strpos($buffer, '[TCP] HEX:');
                                    $pos_ini_ = $pos_ini + 11;
                                    $pos_fin = strpos($buffer, "\n");
                                    $matches2 = substr($buffer, $pos_ini_, ($pos_fin - $pos_ini_));
                                    $matches2 = hex2bin($matches2);
                                    $matches2 = explode(';', $matches2);
                                }
                            }
                            fclose($handle);
                            //dd($matches2);
                        }

                        if (isset($matches2)) {
                            $xml = simplexml_load_string($device->traccar->other);
                            $json = json_encode($xml);
                            $array = json_decode($json, true);
                            $array['sat'] = $matches2[23];
                            $array['rssi'] = str_replace("\r", '', $matches2[24]);
                            //dd($array['rssi']);
                            $array['batterylevel'] = $matches2[11];
                            $xml1 = ArrayToXml::convert($array, 'info');
                            $device->traccar->other = $xml1;
                        } else {
                            $xml = simplexml_load_string($device->traccar->other);
                            $json = json_encode($xml);
                            $array = json_decode($json, true);
                            $array['sat'] = '--';
                            $array['rssi'] = '--';
                            //dd($array['rssi']);
                            $array['batterylevel'] = '0';
                            $xml1 = ArrayToXml::convert($array, 'info');
                            $device->traccar->other = $xml1;
                        }
                    }
                    //}

                    $devices[$key]->total_devices = $total_devices;
                    $speed_ = $device->getSpeed();
                    $ignition_status = 'Ignição indefinida';
                    $teste = $device['traccar'];
                    $xml = simplexml_load_string($teste->other);
                    $json = json_encode($xml);
                    $array = json_decode($json, true);
                    $array2 = $teste->toArray();

                    // APRESENTAR STATUS DO BLOQUEIO DO RASTREADOR
                    //if (Auth::User()->id == 6) {
                    switch($device->getProtocol()) {
                        case 'gt06':
                            if ($array['blocked'] == true) {
                                $devices[$key]->status_block = true;
                            } else {
                                $devices[$key]->status_block = false;
                            }
                            break;
                        case 'suntech':
                            if ($array['out1'] == true) {
                                $devices[$key]->status_block = true;
                            } else {
                                $devices[$key]->status_block = false;
                            }
                            break;
                    }
                    //dd($devices[$key]->status_block,$array["out1"],$array["blocked"]);
                    //$devices[$key]->status_block = false;
                    //if($device->id==1893)
                    //print($devices[$key]->status_block." ".$device->getProtocol()." ".$array["out1"]."   ");
                    //}

                    if (isset($array['ignition'])) {
                        if (! stristr($device->registration_number, 'virtual') === false) { //strval(strpos($device->registration_number,"CRX3"))>="0"){
                            if ($array['ignition'] == 'true') {
                                $ignition_status = 'Ign. ligada';
                            } else {
                                $ignition_status = 'Ign. desligada';
                            }
                        } else {
                            if ($device->getSpeed() > 3) {
                                $ignition_status = 'Ign. ligada';
                            } else {
                                $ignition_status = 'Ign. desligada';
                            }
                        }
                    }

                    if ($device->getSpeed() > 3) {
                        $ignition_status = 'Ign. ligada';
                    }
                    if (isset($array['result'])) {
                        $devices[$key]->result_ = $array['result'];
                    } else {
                        $devices[$key]->result = null;
                    }
                    if (isset($array['power'])) {
                        $tensao = $array['power'];
                    } else {
                        $tensao = 0;
                    }
                    $status = $device->getStatus();
                    $icon_color = $device->getStatusColor();

                    if ($icon_color == 'yellow') {
                        $icon_color = 'black';
                    }
                    if ($icon_color == 'green' || $icon_color == 'black') {
                        $shadow_area = 'on-line';
                    } else {
                        $shadow_area = 'Em sombra';
                    }
                    $devices[$key]->shadow_area = $shadow_area;
                    if ($device->active == '0') {
                        $active_gpswox = 'Inativo';
                    } else {
                        $active_gpswox = 'Ativo';
                    }
                    $devices[$key]->active_gpswox = $active_gpswox;
                    $devices[$key]->active = 1; //$device->pivot->active;
                    $devices[$key]->speed = $speed_;
                    $devices[$key]->online = $status;
                    $devices[$key]->icon_color = $icon_color;
                    $devices[$key]->ignition = $device->ignition;

                    $data_atual = Carbon::now('-3');

                    if ($device->time == 'Não conectado') {
                        $text_collor = 'black';
                        $shadow_area = 'off-line';
                    } else {
                        $data_rast = Carbon::parse($device->time, '-3');
                        if ($data_atual->diffInHours($data_rast) > 24) {
                            $text_collor = 'orangered';
                            $shadow_area = 'off-line';
                        } else {
                            if ($data_atual->diffInMinutes($data_rast) > 30) {
                                $text_collor = 'blue';
                                $shadow_area = 'on-line';
                            } else {
                                if ($speed_ > 6) {
                                    if ($data_atual->diffInMinutes($data_rast) > 3) {
                                        $text_collor = 'blue';
                                        $shadow_area = 'Em sombra';
                                    } else {
                                        $text_collor = 'green';
                                        $shadow_area = 'on-line';
                                    }
                                } else {
                                    $text_collor = 'black';
                                    $shadow_area = 'on-line';
                                }
                            }
                        }
                    }
                    //$text_collor = "black";
                    $devices[$key]->status_collor = $text_collor;
                    $devices[$key]->shadow_area = $shadow_area;
                    if ($device->active == '0') {
                        $active_gpswox = 'Inativo';
                    } else {
                        $active_gpswox = 'Ativo';
                    }
                    $devices[$key]->active_gpswox = $active_gpswox;
                    $devices[$key]->active = 1; //$device->pivot->active;
                    $devices[$key]->speed = $speed_;
                    $devices[$key]->online = $status;
                    $devices[$key]->icon_color = $icon_color;
                    $devices[$key]->ignition = $device->ignition;
                    $devices[$key]->card = $card;
                    $devices[$key]->object_owner = $device->object_owner;
                    $devices[$key]->engine_status = $device->getEngineStatus();
                    $devices[$key]->lat = $device->lat;
                    $devices[$key]->lng = $device->lng;
                    if ($speed_ != 0) {
                        $ignition_status .= ' - Em movimento';
                    } else {
                        $ignition_status .= ' - Parado';
                    }
                    $devices[$key]->sensors_ = $ignition_status; //$device->driver['name'];
                    $devices[$key]->driver_ = $device->driver['name'];
                    $stateandcity = getGeoCity($device->lat, $device->lng);
                    if (Auth::User()->id == 6) {
                        //print_r($stateandcity);
                    }
                    $devices[$key]->state = $stateandcity[0]; // Editei para apresentar o endereço
                    $devices[$key]->city = $stateandcity[1]; //Editei, acrescentei este item
                    $devices[$key]->address_ = $stateandcity[2];
                    $devices[$key]->course = $device->course;
                    $devices[$key]->altitude = $device->altitude;
                    $devices[$key]->protocol = $device->getProtocol();
                    $devices[$key]->time = $device->time;
                    $devices[$key]->time2 = gmdate('d-m-Y H:i:s', abs(strtotime($array2['device_time']) - 10800)); //$array2['device_time'];//$device->time;
                    $devices[$key]->time3 = $device->time; //gmdate('d-m-Y H:i:s', abs( strtotime( $array2['server_time'] ) - 10800 ) );
                    $devices[$key]->timestamp = $device->timestamp;
                    $devices[$key]->acktimestamp = $device->acktimestamp;
                    $devices[$key]->formatSensors = $device->getFormatSensors();
                    $devices[$key]->formatServices = $device->getFormatServices();
                    $devices[$key]->tail = $device->tail;
                    $devices[$key]->distance_unit_hour = auth()->User()->unit_of_speed;
                    $devices[$key]->moved_timestamp = $device->moved_timestamp;
                    $devices[$key]->group_id = $device->pivot->group_id;
                    $devices[$key]->rastreador = $device->registration_number;

                    //touch device icon relationship to set device status to icon object
                    $devices[$key]->icon = $device->icon;

                    if (request()->wantsJson()) {
                        unset($devices[$key]->services, $devices[$key]->pivot);
                    }

                    $card = ! $card;
                }

                /*$finalTime = getTime();
                $execTime = $finalTime - $time__;
                $fp = fopen('/var/www/html/releases/20190129073809/public/debug.txt', "a+");
                fwrite($fp, "\r\n DEBUGER ".$execTime."\r\n");
                fclose($fp);*/

                if (request()->wantsJson()) {
                    return response()->json($devices);
                }

                if (Auth::User()->id == 6) {
                    $devices = paginate_($devices, $veic_page, $page);
                    //$devices = $devices->paginate($veic_page, 'page',$page);
                    //dd($devices->render());
                }

                $grouped = [];
                foreach ($devices as $device) {
                    $group_id = empty($device->group_id) || ! array_key_exists($device->group_id, $device_groups) ? 0 : $device->group_id;
                    $grouped[$group_id][] = $device;
                }

                $grouped = array_sort_array($grouped, array_keys($device_groups));

                //unset($devices);
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }

        return view('front::Objects.items')->with(compact('grouped', 'device_groups', 'drivers', 'timezones', 'device_groups_opened', 'devices_orig', 'total_paginas'));
    }

    /* public function sort_objects_by_total($a, $b) {
        if($a->object_owner == $b->object_owner){ return 0 ; }
        return ($a->object_owner < $b->object_owner) ? -1 : 1;
    } */

    public static function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        // Serialize the value
        $value = @serialize($value);
        // Create the cookie
        return parent::make($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
    }

    //Editei nova busca de items
    public function search_admin(Request $request)
    {
        //Verificar se está usando, se não apagar ou comentar.
        return json_encode(true);
        if (isAdmin()) {
            $devices = UserRepo::getDevicesWith($this->user->id, [
                'devices',
                'devices.sensors',
                'devices.services',
                'devices.driver',
                'devices.traccar',
                'devices.icon',
            ]); //->where($search_type, 'LIKE',"%".$search_name."%");
            if ($devices->count > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function xmlToArray($xml, $options = [])
    {
        $defaults = [
            'namespaceSeparator' => ':', //you may want this to be something other than a colon
            'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => [],   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => '$',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       //optional search and replace on tag and attribute names
            'keyReplace' => false,       //replace values for above search values (as passed to str_replace())
        ];
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) {
                    $attributeName =
                            str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                }
                $attributeKey = $options['attributePrefix']
                        .($prefix ? $prefix.$options['namespaceSeparator'] : '')
                        .$attributeName;
                $attributesArray[$attributeKey] = (string) $attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = xmlToArray($childXml, $options);
                [$childTagName, $childProperties] = each($childArray);

                //replace characters in tag name
                if ($options['keySearch']) {
                    $childTagName =
                            str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                }
                //add namespace prefix, if any
                if ($prefix) {
                    $childTagName = $prefix.$options['namespaceSeparator'].$childTagName;
                }

                if (! isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                            in_array($childTagName, $options['alwaysArray']) || ! $options['autoArray']
                            ? [$childProperties] : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = [$tagsArray[$childTagName], $childProperties];
                }
            }
        }

        //get text content of node
        $textContentArray = [];
        $plainText = trim((string) $xml);
        if ($plainText !== '') {
            $textContentArray[$options['textContent']] = $plainText;
        }

        //stick it all together
        $propertiesArray = ! $options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
                ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return [
            $xml->getName() => $propertiesArray,
        ];
    }

    public function itemsSimple()
    {
        $searchData = Request::all();
        $deviceCollection = DeviceRepo::searchAndPaginateSimple($searchData, /*editado anterior 'name'*/'plate_number', 'asc', 15, [$this->user->id]);
        //dd($deviceCollection);
        return view('front::Objects.itemsSimple')->with(compact('deviceCollection'));
    }

    public function itemsJson()
    {
        $data = DeviceModalHelper::itemsJson();

        return $data;
    }

    public function changeGroupStatus()
    {
        if (isDemoUser()) {
            return;
        }

        $device_groups_opened = array_flip(json_decode($this->user->open_device_groups, true));

        //dd("olá");

        if (isset($device_groups_opened[$this->data['id']])) {
            unset($device_groups_opened[$this->data['id']]);
            $device_groups_opened = array_flip($device_groups_opened);
        } else {
            $device_groups_opened = array_flip($device_groups_opened);
            array_push($device_groups_opened, $this->data['id']);
        }

        UserRepo::update($this->user->id, [
            'open_device_groups' => json_encode($device_groups_opened),
        ]);
    }

    public function changeAlarmStatus()
    {
        if (! array_key_exists('id', $this->data) && array_key_exists('device_id', $this->data)) {
            $this->data['id'] = $this->data['device_id'];
        }
        $item = DeviceRepo::find($this->data['id']);
        if (empty($item) || (! $item->users->contains($this->user->id) && ! isAdmin())) {
            return ['status' => 0];
        }

        $table = 'positions_'.$item->traccar_device_id;
        if (Schema::connection('traccar_mysql')->hasTable($table)) {
            $position = DB::connection('traccar_mysql')->table($table)->select('time')->orderBy('time', 'desc')->first();
        }

        $sendCommandModalHelper = new SendCommandModalHelper();
        $sendCommandModalHelper->setData([
            'device_id' => $item->id,
            'type' => $item->alarm == 0 ? 'alarmArm' : 'alarmDisarm',
        ]);
        $result = $sendCommandModalHelper->gprsCreate();

        $alarm = $item->alarm;

        if ($result['status'] == 1) {
            $tr = true;
            $times = 1;
            $val = '';
            if (isset($position)) {
                while ($tr && $times < 6) {
                    $positions = DB::connection('traccar_mysql')->table($table)->select('other')->where('time', '>', $position->time)->orderBy('time', 'asc')->get();
                    if ($times >= 5) {
                        $positions = DB::connection('traccar_mysql')->table($table)->select('other')->orderBy('time', 'desc')->limit(2)->get();
                    }
                    foreach ($positions as $pos) {
                        preg_match('/<'.preg_quote('alarm', '/').'>(.*?)<\/'.preg_quote('alarm', '/').'>/s', $pos->other, $matches);
                        if (! isset($matches['1'])) {
                            continue;
                        }

                        $val = $matches['1'];
                        if ($val == 'lt' || $val == 'mt' || $val == 'lf') {
                            $tr = false;
                            break;
                        }
                    }

                    $times++;
                    sleep(1);
                }
            }

            $status = 0;

            if (! $tr) {
                if (($item->alarm == 0 && $val == 'lt') || ($item->alarm == 1 && $val == 'mt')) {
                    $status = 1;
                    $alarm = $item->alarm == 1 ? 0 : 1;
                    DeviceRepo::update($item->id, [
                        'alarm' => $alarm,
                    ]);
                }
            }

            return ['status' => $status, 'alarm' => intval($alarm), 'error' => trans('front.unexpected_error')];
        } else {
            return ['status' => 0, 'alarm' => intval($alarm), 'error' => isset($result['error']) ? $result['error'] : ''];
        }
    }

    public function alarmPosition()
    {
        $item = DeviceRepo::find($this->data['id']);
        if (empty($item) || (! $item->users->contains($this->user->id) && ! isAdmin())) {
            return response()->json(['status' => 0]);
        }

        $sendCommandModalHelper = new SendCommandModalHelper();
        $sendCommandModalHelper->setData([
            'device_id' => $item->id,
            'type' => 'positionSingle',
        ]);
        $result = $sendCommandModalHelper->gprsCreate();

        if ($result['status'] == 1) {
            return ['status' => 1];
        } else {
            return ['status' => 0, 'error' => isset($result['error']) ? $result['error'] : ''];
        }
    }

    public function showAddress()
    {
        try {
            $location = \Facades\GeoLocation::byAddress($this->data['address']);

            return ['status' => 1, 'location' => $location->toArray()];
        } catch(\Exception $e) {
            return ['status' => 0, 'error' => $e->getMessage()];
        }
    }

    public function interaction_check($user_id)
    {
        // Uilmo fazer melhoria no quesito Gerentes_excluidos
        $gerentes_excluidos = [1085];
        if (Auth::User()->manager_id != 1085) { // verificação para excluir gerentes que não pagam o monitormento
            //debugar(true, Auth::User()->manager_id);

            //$this->log_register(2, Auth::User()->id);
            $ocorrencys = '';
            //dd('oi');
            $devices_ids = DB::table('user_device_pivot')->where('user_id', '=', $user_id)
                                                        ->select('device_id')
                                                        ->get();
            //debugar(true, $devices_ids);
            //dd($devices_ids);
            foreach ($devices_ids as $device_id) {
                $ocorrency_count = Monitoring::where('device_id', $device_id->device_id)
                                                ->where('active', 1)
                                                ->where('cause', 'offline_duration')
                                                ->where('sent_maintenance', 0)
                                                ->where('make_contact', 0)
                                                ->where('interaction_later', 0)
                                                //->get()
                                                ->count();

                //debugar(true, $device_id);
                //debugar(true, $ocorrency_count);

                //dd($ocorrency_count);
                if ($ocorrency_count > 0) {
                    // UILMO ALTEREI O ITEM ABAIXO PARA CORRIGIR O PROBLEMA NO BOOTSTRAP
                    return json_encode(true);
                    //break;
                }
            }

            return json_encode(false);
        }
    }

    public function interaction($user_id)
    {
        //UILMO ALGUMAS DAS MENSAGENS ABAIXO NÃO ESTÃO ATIVAS, AS OPÇÕES, DESATIVEI DEVIDO A DIFICULDADE DOS CLIENTES EM INTERAGIR.
        $ocorrencys = '';

        $devices_ids = DB::table('user_device_pivot')
                            ->where('user_id', $user_id)
                            ->select('device_id')
                            ->get();
        //dd($devices_ids);
        foreach ($devices_ids as $device_id) {
            $device = UserRepo::getDevice($user_id, $device_id->device_id);
            //$id = $device->traccar_device_id;
            $ocorrencys = Monitoring::orderby('modified_date', 'asc')
                                    ->orderby('gps_date', 'asc')
                                    ->where('device_id', $device_id->device_id)
                                    ->where('active', 1)
                                    ->where('sent_maintenance', 0)
                                    //->where('cause', 'offline_duration')
                                    ->where('make_contact', 0)
                                    ->where('interaction_later', 0)
                                    ->get();
            //dd($ocorrencys);
            if (! $ocorrencys->isEmpty()) {
                //dd($ocorrencys);
                foreach ($ocorrencys as $ocorrency) {
                    //dd($ocorrency);
                    //$ocorrency = Monitoring::find($ocorrency->id);
                    if ($ocorrency->cause == 'offline_duration') {
                        $ocorrency->text_title = 'Veículo sem comunicação';
                        $ocorrency->text_1 = 'sem comunicação com o servidor';
                        $ocorrency->option_1 = 'Parado em garagem/oficina';
                        $ocorrency->option_2 = 'Área de sombra (sem sinal da operadora, roça, fazenda, ...)';
                        $ocorrency->option_3 = 'Rodando/viajando normalmente';
                    } elseif ($ocorrency->cause == 'Bateria Violada') {
                        $ocorrency->text_title = 'Veículo com bateria violada';
                        $ocorrency->text_1 = '(esteve) com a bateria violada';
                        $ocorrency->option_1 = 'Parado em garagem/oficina (local seguro)';
                        $ocorrency->option_2 = 'Bateria funcionando normalmente';
                    }
                    $ocorrency->vehicle_model = $device->device_model;
                    $ocorrency->plate_number = $device->plate_number;
                    //dd($ocorrency);
                    return view('front::Objects.interaction')->with(compact('ocorrency'));
                    //break;
                }
            }
            //break;
        }
    }

    public function interaction_action(Request $request)
    {
        /*$ocorrency = Monitoring::find(2511);//$request->input('id'));
        $ocorrency->information = json_encode(Request::All());
        $ocorrency->save();*/
        try {
            $rules = ['id' => 'required|numeric',
                'cause' => 'required|numeric'];
            $this->validate($request, $rules);

            $now = date('Y-m-d H:i:s');
            $information = "\r\n Cliente informou pelo app: ";
            $deadline = $request->input('deadline');
            $next_contact = Carbon::createFromFormat('Y-m-d H:i:s', $now, -3);
            $next_contact_ = $next_contact;
            $ocorrency = Monitoring::find($request->input('id'));

            if ($request->input('cause') == 1) {
                $information .= 'Localização do veículo de acordo com o rastreador';
                $ocorrency->make_contact = true;
                $next_contact->addDays(7);
            } elseif ($request->input('cause') == 2) {
                $information .= 'Localização do veículo NÃO ESTÁ de acordo com o rastreador, ENTRAR EM CONTATO COM O CLIENTE';
                $ocorrency->make_contact = true;
                $next_contact->addDays(1);
            } else {
                $information .= 'Cliente NÃO SOUBE INFORMAR, ENTRAR EM CONTATO COM O CLIENTE';
                $ocorrency->interaction_later = false;
                $ocorrency->make_contact = false;
                $ocorrency->sent_maintenance = false;
                $next_contact->addDays(1);
            }

            $ocorrency->interaction_date = $now;
            $ocorrency->next_con = $next_contact;
            $ocorrency->information = $ocorrency->information."\r\n ".$information.' ('.$now.') ';
            $ocorrency->interaction_choice1 = $request->input('cause');
            $ocorrency->interaction_choice2 = $deadline;
            $ocorrency->interaction_date = $now;
            $ocorrency->automatic_treatment = true;

            $ocorrency->save();

            return Response::json(['status' => 1]);
        } catch (Exception $e) {
            $fp = fopen('/var/www/html/releases/20190129073809/public/debug.txt', 'a+');
            fwrite($fp, "\r\n DEBUGER ".json_encode($e)." \r\n");
            fclose($fp);
            //debugar($e);
            return true;
        }
    }

    public function interaction_later($id)
    {
        $id = sanitization($id, 2);

        $now = date('Y-m-d H:i:s');
        $next_contact = Carbon::createFromFormat('Y-m-d H:i:s', $now);
        $next_contact->addHours(2);
        $ocorrency = Monitoring::find($id);
        $ocorrency->information = $ocorrency->information."\r\n # Interagir depois. # \r\n ";
        $ocorrency->interaction_later = true;
        $ocorrency->make_contact = true;
        $ocorrency->automatic_treatment = true;
        $ocorrency->next_con = $next_contact;
        $ocorrency->save();
        /*$fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_log.txt', "a+");
        fwrite($fp, "\r\n Interação ".json_encode($ocorrency->next_con)." \r\n");
        fclose($fp);*/
    }

    public function log_register($type, $user_id)
    {
        if (false) {
            $now = Carbon::now('-3');
            $dayOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            $fp = fopen('/var/www/html/storage/logs/user_log.txt', 'a+');
            fwrite($fp, "\r\n ".$type.'; '.$user_id.'; '.$dayOfWeek[$now->dayOfWeek].'; '.$now);
            fclose($fp);
        }
    }

    public function traccer_route($id = null)
    {
        $id = strip_tags($id);
        $device = UserRepo::getDevice($this->user->id, $id);

        $coordenadas = ['lat' => $device->traccar->lastValidLatitude, 'lon' => $device->traccar->lastValidLongitude, 'juntas' => $device->traccar->lastValidLatitudet.','.$device->traccar->lastValidLongitude];

        return view('front::Objects.traccer_route')->with(compact('coordenadas'));
        //return json_encode($coordenadas);
    }

    public function anchor($id = null)
    {
        try {
            $id = sanitization($id, 2);
            debugar(true, 'Anchor_Start '.$id.', '.$this->user->id);
            $device = UserRepo::getDevice($this->user->id, $id);
            //dd($device);
            DB::table('devices')->where('id', $id)->update(['anchor' => ! $device->anchor]);
            if ($device->protocol == 'gt06') {
                $alert_name = 'Ancora1';
                $event_id = 26;
            } elseif ($device->protocol == 'suntech') {
                $alert_name = 'Ancora2';
                $event_id = 15;
            } elseif ($device->protocol == 'mxt') {
                $alert_name = 'Ancora3';
                $event_id = 27;
            } elseif ($device->protocol == 'easytrack') {
                $alert_name = 'Ancora5';
                $event_id = 28;
            } else {
                $alert_name = 'Ancora4';
                $event_id = 24;
            }

            $alert = DB::table('alerts')->where(['name' => $alert_name, 'user_id' => $this->user->id])->get();
            if (! $alert) {
                DB::table('alerts')->insert(['user_id' => $this->user->id,
                    'active' => true,
                    'name' => $alert_name,
                    'type' => 'custom',
                    'schedules' => '{"monday":["03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45","00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45"],"tuesday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"],"wednesday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"],"thursday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"],"friday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"],"saturday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"],"sunday":["00:00","00:15","00:30","00:45","01:00","01:15","01:30","01:45","02:00","02:15","02:30","02:45","03:00","03:15","03:30","03:45","04:00","04:15","04:30","04:45","05:00","05:15","05:30","05:45","06:00","06:15","06:30","06:45","07:00","07:15","07:30","07:45","08:00","08:15","08:30","08:45","09:00","09:15","09:30","09:45","10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45"]}',
                    'notifications' => '{"sound":{"active":"1"},"push":{"active":"1"},"email":{"input":""},"webhook":{"input":""}}',
                    'data' => '{"command":{"active":"0","type":"engineResume"},"schedule":"1"}']);

                $alert_id_ = DB::table('alerts')->where(['name' => $alert_name, 'user_id' => $this->user->id])->select('id')->get();
                foreach ($alert_id_ as $id) {
                    $alert_id = $id->id;
                }

                DB::table('alert_event_pivot')->insert(['alert_id' => $alert_id,
                    'event_id' => $event_id]);
            }
            $alert_id_ = DB::table('alerts')->where(['name' => $alert_name, 'user_id' => $this->user->id])->select('id')->get();

            foreach ($alert_id_ as $id) {
                $alert_id = $id->id;
            }
            if ($device->anchor) {
                DB::table('alert_device')->insert(['alert_id' => $alert_id, 'device_id' => $device->id]);
            } else {
                DB::table('alert_device')->where(['alert_id' => $alert_id, 'device_id' => $device->id])->delete();
            }
            $anchor_status = $device->anchor;
            debugar(false, 'Anchor_End ');

            return view('front::Objects.anchor')->with(compact('anchor_status'));
        } catch (Throwable $e) {
            Log::error($e);
            debugar(true, json_encode($e));

            return false;
        }
        //return json_encode($coordenadas);
    }

    public function sensores($id = null)
    {
        $id = strip_tags($id);

        $anchor_status = 1;

        return view('front::Objects.sensors')->with(compact('anchor_status'));
        //return json_encode($coordenadas);
    }

    public function share_device(int $device_id)
    {
        try {
            $users = DB::table('users')->where('email', 'like', 'c_user%')->get();
            $user_ok = null;

            foreach ($users as $user) {
                $token = DB::table('autologin_tokens')->where('user_id', $user->id)->get();
                if (empty($token)) {
                    DB::table('user_device_pivot')->where('user_id', $user->id)->delete();
                    $link = Autologin::user($user->id);
                    $user_ok = $user;

                    break;
                } else {
                    $date = Carbon::now();
                    $date->subDay();
                    $token = DB::table('autologin_tokens')->where('user_id', $user->id)->whereDate('created_at', '>=', $date)->get();
                    //print_r($token);
                    if (empty($token)) {
                        DB::table('user_device_pivot')->where('user_id', $user->id)->delete();
                        $link = Autologin::user($user->id);
                        $user_ok = $user;

                        break;
                    } else {
                        //print_r("já existe");
                    }
                }
            }
            if (empty($link)) {
                $link = 'ALGO DE ERRADO ACONTECEU, POR FAVOR ENTRE EM CONTATO COM O SUPORTE';

                return view('front::Objects.share_device')->with(compact('link'));
            //print_r("Limite de usuário, contacte o suporte");
            } else {
                $data = ['user_id' => $user_ok->id, 'device_id' => $device_id];
                DB::table('user_device_pivot')->insert($data);

                return view('front::Objects.share_device')->with(compact('link'));
                //print_r($link);
            }
        } catch (Throwable $e) {
            Log::error($e);
            debugar(true, json_encode($e));

            return false;
        }
    }

    public function pointLocation()
    {
        return true;
    }

    public function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        // Validação e filtro das entradas do usuário
        if (! is_string($point) || ! is_array($polygon) || ! is_bool($pointOnVertex)) {
            throw new InvalidArgumentException('Entradas inválidas.');
        }

        $point = filter_var($point, FILTER_SANITIZE_STRING);
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = $polygon;

        if ($this->pointOnVertex == true && $this->pointOnVertex($point, $vertices)) {
            return true;
        }

        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];

            if ($vertex1['y'] == $vertex2['y'] && $vertex1['y'] == $point['y'] && $point['x'] > min($vertex1['x'], $vertex2['x']) && $point['x'] < max($vertex1['x'], $vertex2['x'])) {
                return true;
            }

            if ($point['y'] > min($vertex1['y'], $vertex2['y']) && $point['y'] <= max($vertex1['y'], $vertex2['y']) && $point['x'] <= max($vertex1['x'], $vertex2['x']) && $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];

                if ($xinters == $point['x']) {
                    return true;
                }

                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }

        return $intersections % 2 != 0;
    }

    public function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }

    public function pointStringToCoordinates($pointString)
    {
        if (is_array($pointString)) {
            return ['x' => $pointString['0'], 'y' => $pointString['1']];
        } else {
            $coordinates = explode(',', $pointString);

            return ['x' => $coordinates[0], 'y' => $coordinates[1]];
        }
    }

    public function rayCasting($point, $polygon)
    {
        //dd("oi");
        $n = count($polygon);
        $inside = false;

        // criar uma linha a partir do ponto em uma direção qualquer
        $p1 = $point;
        $p2 = [$point[0] + 999999, $point[1]];

        // contar o número de interseções da linha com as arestas do polígono
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $intersect = false;

            // verificar se as arestas intersectam a linha
            if ((abs($polygon[$i][1]) > $point[1]) !== (abs($polygon[$j][1]) > $point[1])) {
                $slope = (abs($polygon[$j][0]) - abs($polygon[$i][0])) / (($polygon[$j][1]) - abs($polygon[$i][1]));
                $intersectX = ($point[1] - abs($polygon[$i][1])) * $slope + abs($polygon[$i][0]);

                // verificar se a interseção está à direita do ponto
                if ($intersectX > $point[0]) {
                    $intersect = true;
                }
            }

            // incrementar o número de interseções
            if ($intersect) {
                $inside = ! $inside;
            }
        }

        // retornar se o ponto está dentro ou fora do polígono
        return $inside;
    }
}
