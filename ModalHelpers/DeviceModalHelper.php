<?php namespace ModalHelpers;

use App\Exceptions\DeviceLimitException;
use Facades\ModalHelpers\SensorModalHelper;
use Facades\ModalHelpers\ServiceModalHelper;
use Facades\Repositories\DeviceFuelMeasurementRepo;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\DeviceIconRepo;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\DeviceSensorRepo;
use Facades\Repositories\EventRepo;
use Facades\Repositories\SensorGroupRepo;
use Facades\Repositories\SensorGroupSensorRepo;
use Facades\Repositories\TimezoneRepo;
use Facades\Repositories\TraccarDeviceRepo;
use Facades\Repositories\UserDriverRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Facades\Validators\DeviceFormValidator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceIcon;
use Tobuli\Exceptions\ValidationException;
use App\Exceptions\ResourseNotFoundException;
use App\Exceptions\PermissionException;

use App\tracker;
use App\customer;
use Illuminate\Support\Str;

class DeviceModalHelper extends ModalHelper
{
    private $device_fuel_measurements = [];

    public function __construct()
    {
        parent::__construct();

        $this->device_fuel_measurements = [
            [
                'id' => 1,
                'title' => trans('front.l_km'),
                'fuel_title' => strtolower(trans('front.liter')),
                'distance_title' => trans('front.kilometers'),
            ],
            [
                'id' => 2,
                'title' => trans('front.mpg'),
                'fuel_title' => strtolower(trans('front.gallon')),
                'distance_title' => trans('front.miles'),
            ]
        ];
    }

    public function createData() {
        //dd('ola');
    
        if (request()->get('perm') == null || (request()->get('perm') != null && request()->get('perm') != 1)) {
            if (request()->get('perm') != null && request()->get('perm') != 2) {
                if ($this->checkDevicesLimit())
                    throw new DeviceLimitException();
            }

            $this->checkException('devices', 'create');
        }

        $icons_type = [
            'arrow' => trans('front.arrow'),
            'rotating' => trans('front.rotating_icon'),
            'icon' => trans('front.icon')
        ];

        $device_icon_colors = [
            'green'  => trans('front.green'),
            'yellow' => trans('front.yellow'),
            'red'    => trans('front.red'),
            'blue'   => trans('front.blue'),
            'orange' => trans('front.orange'),
            'black'  => trans('front.black'),
        ];
        $device_icons = DeviceIconRepo::getMyIcons($this->user->id);
        $device_icons_grouped = [];

        foreach ($device_icons as $dicon) {
            if ($dicon['type'] == 'arrow')
                continue;

            if (!array_key_exists($dicon['type'], $device_icons_grouped))
                $device_icons_grouped[$dicon['type']] = [];

            $device_icons_grouped[$dicon['type']][] = $dicon;
        }

        $users = UserRepo::getUsers($this->user);

        if (Auth::user()->isManager()) {
            $trackers = tracker::where('active',1)
                ->where('manager_id',$this->user->id)
                ->where('in_use',0)
                ->get();
        }
        else{
            $trackers = tracker::where('active',1)
                ->where('manager_id',0)
                ->where('in_use',0)
                ->get();
        }
        
        

        $trackers = $trackers->filter(function ($tracker) { 
                        if (!$tracker->device_id == 0){
                            $device = DeviceRepo::find($tracker->device_id);
                            if (!is_null($device)){
                                if (Str::contains(Str::lower($device->name), "teste"))
                                    return $tracker;
                            }
                            else{
                                return $tracker;
                            }
                        }
                        else{
                            return $tracker;
                        }
                        
                    })
                    ->lists('imei', 'id');;
                    
        if (Auth::user()->isManager()) {
            $customers = customer::where('active',1)
                ->where('manager_id',$this->user->id)
                ->get()
                ->lists('name', 'id');
        }
        else{
            $customers = customer::where('active',1)
                ->where('manager_id',0)
                ->get()
                ->lists('name', 'id');
        }

        $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();
        $expiration_date_select = [
            '0000-00-00' => trans('front.unlimited'),
            '1' => trans('validation.attributes.expiration_date')
        ];
        $timezones = ['0' => trans('front.default')] + TimezoneRepo::order()->lists('title', 'id')->all();
        $timezones_arr = [];
        foreach ($timezones as $key => &$timezone) {
            $timezone = str_replace('UTC ', '', $timezone);
            if ($this->api)
                array_push($timezones_arr, ['id' => $key, 'value' => $timezone]);
        }

        $sensor_groups = [];
        if (isAdmin()) {
            $sensor_groups = SensorGroupRepo::getWhere([], 'title');
            $sensor_groups = $sensor_groups->lists('title', 'id')->all();
        }

        $sensor_groups = ['0' => trans('front.none')] + $sensor_groups;

        $device_fuel_measurements = $this->device_fuel_measurements;

        $device_fuel_measurements_select =  [];
        foreach ($device_fuel_measurements as $dfm)
            $device_fuel_measurements_select[$dfm['id']] = $dfm['title'];

        if ($this->api) {
            $timezones = $timezones_arr;
            $device_groups = apiArray($device_groups);
            $sensor_groups = apiArray($sensor_groups);
            $users = $users->toArray();
            //$trackers = $trackers->toArray();
        }

        return compact('device_groups', 'sensor_groups', 'device_fuel_measurements', 'device_icons', 'users', 'timezones', 'expiration_date_select', 'device_fuel_measurements_select', 'icons_type', 'device_icons_grouped', 'device_icon_colors', 'trackers','customers');
    }

    public function create()
    {
        
        $this->checkException('devices', 'store');
        
        $name_ = customer::find($this->data['name']);
        $this->data['name'] = $name_->name;
        
        if ($this->checkDevicesLimit())
            throw new DeviceLimitException();
        $imei = isset($this->data['imei']) ? trim($this->data['imei']) : null;
		$id_imei = $imei;				
        $imei = tracker::find($imei);
        
        if(!$imei->device_id==0){
            $device = DeviceRepo::find($imei->device_id);
            if(!$device ==null){
                if ($device->protocol == "gt06"){
                    $alert_protocol=104;
                }
                elseif ($device->protocol == "suntech"){
                        $alert_protocol=105;
                    }
                elseif ($device->protocol == "mxt"){
                        $alert_protocol=106;
                    }
                else{
                        $alert_protocol=107;
                    }
                DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $device->id])->delete();
            }
        }
        if(!$imei->device_id==0){                        
            $old_device = DeviceRepo::find($imei->device_id);
            $lastlat = $old_device['traccar']['lastValidLatitude'];
            $lastlon = $old_device['traccar']['lastValidLongitude'];
            
            //DB::connection('traccar_mysql')->table('devices')->where('id', '=', $this->data['id'])->update(['lastValidLatitude' => $lastlat, 'lastValidLongitude' => $lastlon ]);
            DB::connection('traccar_mysql')->table('devices')->where('uniqueId', '=', $imei->imei)->update(['uniqueId' => 'removido-'.$this->data['imei'].'('.date("d m, Y, g:i a").')']); 
            //dd('oi');
            if (str_contains(Str::lower($imei->name), 'teste')){
                DB::table('devices')->where('imei', $imei->imei)->update(['imei' => 'removido-'.$imei->imei.'('.date("d m, Y, g:i a").')', 'plate_number' => 'REMOVIDO', 'sim_number' => 'removido-'.$imei->sim_number.'('.date("d m, Y, g:i a").')']);
            }
            DB::table('device_sensors')->where('device_id', $imei->device_id)->delete();
        }
        else{
            $lastlat = -11.561980911171869;
            $lastlon = -39.29491437216739;
        }
        if (str_contains(Str::lower($this->data['name']), 'teste')){
            $this->data['plate_number'] = 'CAR-'.substr(Str::lower($imei->imei), -4);
            $imei->in_use = false;
        }
        else{
            $imei->in_use = true;                                                 
        }        
        $imei->save();
        // Ajustar sensores para a marca do rastreador*************
        if (Str::upper($imei->brand) == "CONCOX")
            $sensors_group = 3;
        elseif (Str::upper($imei->brand) == "SUNTECH")
            $sensors_group = 2;
        else
            $sensors_group = 4;
        $this->data['sensor_group_id'] = $sensors_group;
        //*********************************************************                                                   
        $imei = $imei->imei;
        $this->data['imei'] = $imei;

        $this->data['group_id'] = !empty($this->data['group_id']) ? $this->data['group_id'] : null;
        $this->data['timezone_id'] = empty($this->data['timezone_id']) ? NULL : $this->data['timezone_id'];
        $this->data['snap_to_road'] = isset($this->data['snap_to_road']);
        $this->data['fuel_quantity'] = empty($this->data['fuel_quantity']) ? 0 : $this->data['fuel_quantity'];
        //dd('oi');
        try
        {
            if (array_key_exists('device_icons_type', $this->data) && $this->data['device_icons_type'] == 'arrow')
                $this->data['icon_id'] = 0;

            DeviceFormValidator::validate('create', $this->data);

            $this->data['fuel_per_km'] = convertFuelConsumption($this->data['fuel_measurement_id'], $this->data['fuel_quantity']);

            $item_ex = DeviceRepo::whereImei($this->data['imei']);
            if (!empty($item_ex) && $item_ex->deleted == 0)
                throw new ValidationException(['imei' => str_replace(':attribute', trans('validation.attributes.imei_device'), trans('validation.unique'))]);

            if (isAdmin()) {
                if (empty($this->data['enable_expiration_date']))
                    $this->data['expiration_date'] = '0000-00-00';
            }
            else
                unset($this->data['expiration_date']);

            beginTransaction();
            try {

                if (empty($this->data['user_id']))
                    $this->data['user_id'] = ['0' => $this->user->id];

                if (empty($item_ex)) {
                    if (empty($this->data['fuel_quantity']))
                        $this->data['fuel_quantity'] = 0;

                    if (empty($this->data['fuel_price']))
                        $this->data['fuel_price'] = 0;

                    $this->data['gprs_templates_only'] = (array_key_exists('gprs_templates_only', $this->data) && $this->data['gprs_templates_only'] == 1 ? 1 : 0);

                    $device_icon_colors = [
                        'green'  => trans('front.green'),
                        'yellow' => trans('front.yellow'),
                        'red'    => trans('front.red'),
                        'blue'   => trans('front.blue'),
                        'orange' => trans('front.orange'),
                        'black'  => trans('front.black'),
                    ];

                    $this->data['icon_colors'] = [
                        'moving' => 'green',
                        'stopped' => 'blue',
                        'offline' => 'red',
                        'engine' => 'blue',
                    ];
                    
                    if (array_key_exists('icon_moving', $this->data) && array_key_exists($this->data['icon_moving'], $device_icon_colors))
                        $this->data['icon_colors']['moving'] = $this->data['icon_moving'];

                    if (array_key_exists('icon_stopped', $this->data) && array_key_exists($this->data['icon_stopped'], $device_icon_colors))
                        $this->data['icon_colors']['stopped'] = $this->data['icon_stopped'];

                    if (array_key_exists('icon_offline', $this->data) && array_key_exists($this->data['icon_offline'], $device_icon_colors))
                        $this->data['icon_colors']['offline'] = $this->data['icon_offline'];

                    if (array_key_exists('icon_engine', $this->data) && array_key_exists($this->data['icon_engine'], $device_icon_colors))
                        $this->data['icon_colors']['engine'] = $this->data['icon_engine'];

                    if (Auth::User()->isManager())
                        $user_ = Auth::User()->id;
                    else
                        $user_ = 0;
                        
                    $this->data['manager_id'] = $user_;

                    $device = DeviceRepo::create($this->data);
                    
                    $this->deviceSyncUsers($device);
                    $this->createSensors($device->id);

                    $device->createPositionsTable();
                    //dd('oi'); 
                    $imei_removeds_ = tracker::where('imei',$device->imei)->get();
                    foreach ($imei_removeds_ as $imei_removed_){
                        $imei_removed = $imei_removed_;
                    }
                    // AO UTILIZAR NOVOS PROTOCOLOS ACRESCENTAR ABAIXO PARA ACRESCENTAR AOS ALERTAS AUTOMATICAMENTE
                    if(!$device->no_powercut){
                        if ($device->protocol == "gt06"){
                            $alert_protocol=104;
                        }
                        elseif ($device->protocol == "suntech"){
                            $alert_protocol=105;
                        }
                        elseif ($device->protocol == "mxt"){
                            $alert_protocol=106;
                        }
                        else{
                            $alert_protocol=107;
                        }
                        DB::table('alert_device')->insert(['alert_id' => $alert_protocol, 'device_id' => $device->id]);
                    }
                    //INSERÇÃO DO ALERTA SEM COMUNICAÇÃO POR 24H
                        DB::table('alert_device')->insert(['alert_id' => 52, 'device_id' => $device->id]);
                    // FIM DA INSERÇÃO DO VEÍCULO NOS ALERTAS    
                    DB::connection('traccar_mysql')->table('devices')->where('id', '=', $device->traccar_device_id)->update(['lastValidLatitude' => $lastlat, 'lastValidLongitude' => $lastlon ]);									   
                    $imei = tracker::find($id_imei);
                    $imei->device_id = $device->id;
                    $imei->save();                                            
                }
                else {
                    DeviceRepo::update($item_ex->id, $this->data + ['deleted' => 0]);
                    $device = DeviceRepo::find($item_ex->id);
                    $device->users()->sync($this->data['user_id']);
                }

                DB::connection('traccar_mysql')->table('unregistered_devices_log')->where('imei', '=', $this->data['imei'])->delete();
            }
            catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error').$e->getMessage()]);
            }

            commitTransaction();
            
            Log::info('O usuário: '.Auth::user()->id.', criou o veículo: '.$device->id.' do cliente: '.$device->name.'('.$device->object_owner.')');
                                                  
            return ['status' => 1, 'id' => $device->id,];
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    public function editData() {
        
        if (array_key_exists('id', $this->data))
            $device_id = $this->data['id'];
        else
            $device_id = request()->route('id');

        if (empty($device_id))
            $device_id = empty($this->data['device_id']) ? NULL : $this->data['device_id'];

        $item = DeviceRepo::find($device_id);
        
        $devices = UserRepo::getDevices($this->user->id);
        
        $this->checkException('devices', 'edit', $item);

        $users = UserRepo::getUsers($this->user);
        $tracker = array();
        if (Auth::user()->isManager()) {
            $trackers = tracker::where('active',1)
                ->where('manager_id',$this->user->id)
                ->where('in_use',0)
                ->get();
        }
        else{
            $trackers = tracker::where('active',1)
                ->where('manager_id',0)
                ->where('in_use',0)
                ->get();
        }

        $trackers_ = tracker::where('imei',$item->imei)->get();
        
        //dd($item->traccar_device_id);                 
        foreach($trackers_ as $tracker_){
                $tracker = $tracker_;
                //dd($tracker);
            }
        //dd($tracker);    
        if (str_contains(Str::lower($item->traccar->uniqueId), 'removido-')){
            $trackers_ = tracker::where('imei',18299)->get();
            foreach($trackers_ as $tracker_){
                $tracker = $tracker_;
            }
            $tracker['model'] = 'Removido';
            $tracker['sim_number'] = '00000000-'.date("Y-m-d H:i:s");
            $tracker['iccd'] = '00000000-'.date("Y-m-d H:i:s");                                      
        }
        
        //dd($tracker);
        if($tracker){ 
            if ($tracker['sim_number'] == null)
                $tracker['sim_number'] = '';
            
            if ($tracker['model'] == null)
                $tracker['model'] = '';
        }
        else{
            $tracker['sim_number'] = '';
            $tracker['model'] = '';
            $tracker['iccd'] = '';
        }
        //dd($tracker);

        if (Auth::user()->isManager()) {
            $customers = customer::where('active',1)
                ->where('manager_id',$this->user->id)
                ->get()
                ->lists('name', 'id');
        }
        else{
            $customers = customer::where('active',1)
                ->where('manager_id',0)
                ->get()
                ->lists('name', 'id');
        }
        if (Auth::User()->isManager())
            $user_ = Auth::User()->id;
        else
            $user_ = 0;

        $customers_ = customer::where('name',$item->name)
                                ->where('manager_id',$user_)
                                ->get();
        if($customers_->count()>0){
            foreach($customers_ as $customer_){
                $customer_id = $customer_->id;
            }
        }
        else{
            $customers_ = customer::where('name',"LIKE","%".$item->name."%")
                                    ->where('manager_id',$user_)
                                    ->get();
            if($customers_->count()>0){
                foreach($customers_ as $customer_){
                    $customer_id = $customer_->id;
                }
            }
            else
                $customer_id = 629;
        }
        
        
        $sel_users = $item->users->lists('id', 'id')->all();
        $group_id = null;
        $timezone_id = $item->timezone_id;
        //$timezone_id = null;
        if ($item->users->contains($this->user->id)) {
            foreach ($item->users as $item_user) {
                if ($item_user->id == $this->user->id) {
                    $group_id = $item_user->pivot->group_id;
                    //$timezone_id = $item_user->pivot->timezone_id;
                    break;
                }
            }
        }

        $icons_type = [
            'arrow' => trans('front.arrow'),
            'rotating' => trans('front.rotating_icon'),
            'icon' => trans('front.icon')
        ];

        $device_icon_colors = [
            'green'  => trans('front.green'),
            'yellow' => trans('front.yellow'),
            'red'    => trans('front.red'),
            'blue'   => trans('front.blue'),
            'orange' => trans('front.orange'),
            'black'  => trans('front.black'),
        ];

        $device_icons = DeviceIconRepo::getMyIcons($this->user->id);
        //dd('olá');
        $device_icons_grouped = [];

        foreach ($device_icons as $dicon) {
            if ($dicon['type'] == 'arrow')
                continue;

            if (!array_key_exists($dicon['type'], $device_icons_grouped))
                $device_icons_grouped[$dicon['type']] = [];

            $device_icons_grouped[$dicon['type']][] = $dicon;
        }

        $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();
        $sensors = SensorModalHelper::paginated($item->id);
        $services = ServiceModalHelper::paginated($item->id);
        $expiration_date_select = [
            '0000-00-00' => trans('front.unlimited'),
            '1' => trans('validation.attributes.expiration_date')
        ];
        //dd('olá');
        $has_sensors = DeviceSensorRepo::getWhereInWhere([
            'odometer',
            'acc',
            'engine',
            'ignition',
            'engine_hours'
        ], 'type', ['device_id' => $item->id]);

        $arr = parseSensorsSelect($has_sensors);
        $engine_hours = $arr['engine_hours'];
        $detect_engine = $arr['detect_engine'];
        unset($item->sensors);

        $timezones = ['0' => trans('front.default')] + TimezoneRepo::order()->lists('title', 'id')->all();
        foreach ($timezones as $key => &$timezone)
            $timezone = str_replace('UTC ', '', $timezone);

        $sensor_groups = [];
        if (isAdmin()) {
            $sensor_groups = SensorGroupRepo::getWhere([], 'title');
            $sensor_groups = $sensor_groups->lists('title', 'id')->all();
        }

        $sensor_groups = ['0' => trans('front.none')] + $sensor_groups;

        $device_fuel_measurements = $this->device_fuel_measurements;
        //dd('olá');
        $device_fuel_measurements_select =  [];
        foreach ($device_fuel_measurements as $dfm)
            $device_fuel_measurements_select[$dfm['id']] = $dfm['title'];

        if ($this->api) {
            $device_groups = apiArray($device_groups);
            $timezones = apiArray($timezones);
            $users = $users->toArray();
        }
        //dd('olá');
        return compact('device_id', 'engine_hours', 'detect_engine', 'device_groups', 'sensor_groups', 'item', 'device_fuel_measurements', 'device_icons', 'sensors', 'services', 'expiration_date_select', 'timezones', 'expiration_date_select', 'users', 'sel_users', 'group_id', 'timezone_id', 'device_fuel_measurements_select', 'icons_type', 'device_icons_grouped', 'device_icon_colors', 'trackers','tracker', 'customers', 'customer_id');
    }

    public function edit() {
        try
        {
        //troca do rastreador
        
        
            if($this->data['enable_imei2']==1){
                $imei_ = tracker::find(intval($this->data['imei2']));
                $item = "";
                $item = UserRepo::getDevice(3, $imei_->device_id);
                if(!$imei_->device_id==0 && (!$item==null) && $imei_->in_use==0){
                    
                    $item2 = UserRepo::getDevice(3, $this->data['id']);

                    $plate_number = $item->plate_number;
                    
                    $lastlat = $item['traccar']['lastValidLatitude'];
                    $lastlon = $item['traccar']['lastValidLongitude'];
                    $device_time = $item['traccar']['device_time'];
                    DB::connection('traccar_mysql')->table('devices')->where('id', '=', $item2->traccar_device_id)->update(['lastValidLatitude' => $lastlat, 'lastValidLongitude' => $lastlon, 'device_time' => $device_time]);
                    DB::connection('traccar_mysql')->table('devices')->where('uniqueId', '=', $imei_->imei)->update(['uniqueId' => 'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')']); 
                    //dd('oi');
                    DB::table('devices')->where('imei', $imei_->imei)->update(['imei' => 'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')', 'sim_number' => ' ' ]);
                    debugar(true, "remoção de rastreador".'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')');
                    if (str_contains(Str::lower($item->name), 'teste')){
                        DB::table('devices')->where('imei', $imei_->imei)->update(['plate_number' => 'REMOVIDO']);
                    }
                    if ($item->protocol == "gt06"){
                        $alert_protocol=104;
                    }
                    elseif ($item->protocol == "suntech"){
                            $alert_protocol=105;
                        }
                    elseif ($item->protocol == "mxt"){
                            $alert_protocol=106;
                        }
                    else{
                            $alert_protocol=107;
                        }
                    DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $item->id])->delete();
                }
                else{
                    $plate_number = "";
                    $lastlat = -11.561980911171869;
                    $lastlon = -39.29491437216739;
                    if($imei_->in_use==false)
                        DB::connection('traccar_mysql')->table('devices')->where('uniqueId', '=', $imei_->imei)->update(['uniqueId' => 'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')']); 
                }

                DB::table('devices')->where('imei', $imei_->imei)->update(['imei' => 'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')', 'sim_number' => $this->data['sim_number'].' REMOVIDO']);
                debugar(true, "remoção de rastreador".'removido-'.$this->data['imei2'].'('.date("d m, Y, g:i a").')');
                //dd('oi');
                if (!str_contains(Str::lower($this->data['imei']), 'removido-')){
                    //dd('olá');
                    $imei_removeds_ = tracker::where('imei',$this->data['imei'])->get();
                    foreach ($imei_removeds_ as $imei_removed_){
                        $imei_removed = $imei_removed_;
                    }
                    
                    $imei_removed->in_use = false;
                    //dd('olá');
                    $imei_removed->history = $imei_removed->history.' Removido de: '. $plate_number.'\n ';
                    debugar(true, $imei_removed->history.' Removido de: '. $plate_number);
                    //dd('olá');
                    $imei_removed->last_device_id = $imei_removed->device_id;
                    $imei_removed->device_id = 0;
                    $imei_removed->save();     
                    //dd($imei_removed);
                    //dd('olá');
                }
                //$imei_ = tracker::find(strval($this->data['imei2']));
                //dd('oi');
                $this->data['imei'] = $imei_->imei;
                $customer = customer::find($this->data['name']);
                if ($this->data['imei'] == $imei_->imei){
                    if (str_contains(Str::lower($customer->name), 'teste')){
                        $imei_->in_use = false;
                        $this->data['plate_number'] = 'CAR-'.substr(Str::lower($imei_->imei), -4);
                        //dd('olá');
                    }
                    else{
                        $imei_->in_use = true;
                        //dd('oi');
                    }
                    
                    $item = DeviceRepo::find($this->data['plate_number']);
                    //dd('oi');
                    $imei_->history = $imei_->history."\r\n Instalado em: ". $this->data['plate_number']."\r\n";
                    debugar(true, $imei_->history."\r\n Instalado em: ". $this->data['plate_number']);
                    //dd('oi');
                    $imei_->device_id = $this->data['id'];
                    //dd('Olá');
                    $imei_->save();
                }
            }
            //dd('oi');
            $name_ = customer::find($this->data['name']);
            $this->data['name'] = $name_->name;

            
            
            
            //remover o rastreador #############################################################
            if($this->data['remove_tracker']==1){
                if (!str_contains(Str::lower($this->data['imei']), 'removido-')){
                    $imeis = tracker::where('imei', $this->data['imei'])->get();
                    foreach ($imeis as $imei){
                        //$item = DeviceRepo::find($imei->device_id);
                        //DB::table('devices')->where('imei', $imei->imei)->update(['sim_number' => $this->data['sim_number'].' REMOVIDO']);
                        $plate_number = "";
                        $items = DB::table('devices')->where('id', $imei->device_id)->get();
                        foreach($items as $item){
                                $plate_number = $item->plate_number;
                        }
                        //$items = DeviceRepo::where('traccar_device_id',$imei->device_id)->get();
                        //dd('oi');
                        $imei_ = tracker::find($imei->id);
                        $imei_->in_use = 0;
                        $imei_->history = $imei_->history."\r\n Removido de: ". $plate_number."\r\n";
                        $imei_->last_device_id = $this->data['id'];
                        //$imei_->device_id = null;
                        //dd('olá');
                        $imei_->save();
                        $this->data['imei'] = 'removido-'.$imei_->imei.'('.date("d m, Y, g:i a").')';
                        $this->data['sim_number'] = $this->data['sim_number'].' REMOVIDO';
                    }
                }
                $this->data['sim_number'] = $this->data['sim_number'].' REMOVIDO ('.date("d m, Y, g:i a").')';   
                if (str_contains(Str::lower($this->data['name']), 'teste')){
                    $this->data['plate_number'] = 'removido';
                }
            }
            //Fim de remover Rastreador ####################################################################
        
        
        if (empty($this->data['id']))
            $this->data['id'] = empty($this->data['device_id']) ? NULL : $this->data['device_id'];

        $item = DeviceRepo::find($this->data['id']);

        $this->checkException('devices', 'update', $item);

        $this->data['group_id'] = !empty($this->data['group_id']) ? $this->data['group_id'] : null;
        $this->data['snap_to_road'] = isset($this->data['snap_to_road']);
        $this->data['fuel_quantity'] = empty($this->data['fuel_quantity']) ? 0 : $this->data['fuel_quantity'];

        if (isAdmin() && ! empty($this->data['user_id']))
        {
            $this->data['user_id'] = array_combine($this->data['user_id'], $this->data['user_id']);

            if ($this->user->isManager()) {
                $users = $this->user->subusers()->lists('id', 'id')->all() + [$this->user->id => $this->user->id];

                foreach ($item->users as $user) {
                    if (array_key_exists($user->id, $users) && !array_key_exists($user->id, $this->data['user_id']))
                        unset($this->data['user_id'][$user->id]);

                    if (!array_key_exists($user->id, $users) && !array_key_exists($user->id, $this->data['user_id']))
                        $this->data['user_id'][$user->id] = $user->id;
                }
            }
        }
        else {
            unset($this->data['user_id']);
        }

        if (isAdmin()) {
            if (empty($this->data['enable_expiration_date']))
                $this->data['expiration_date'] = '0000-00-00';
        }
        else
            unset($this->data['expiration_date']);

        $prev_timezone_id = $item->timezone_id;

        
            if ( ! empty($this->data['timezone_id']) && $this->data['timezone_id'] != 57 && $item->isCorrectUTC())
                throw new ValidationException(['timezone_id' => 'Device time is correct. Check your timezone Setup -> Main -> Timezone']);

            if (array_key_exists('device_icons_type', $this->data) && $this->data['device_icons_type'] == 'arrow')
                $this->data['icon_id'] = 0;

            DeviceFormValidator::validate('update', $this->data, $item->id);

            $this->data['fuel_per_km'] = convertFuelConsumption($this->data['fuel_measurement_id'], $this->data['fuel_quantity']);

            beginTransaction();
            try {
                $this->data['gprs_templates_only'] = (array_key_exists('gprs_templates_only', $this->data) && $this->data['gprs_templates_only'] == 1 ? 1 : 0);

                $device_icon_colors = [
                    'green'  => trans('front.green'),
                    'yellow' => trans('front.yellow'),
                    'red'    => trans('front.red'),
                    'blue'   => trans('front.blue'),
                    'orange' => trans('front.orange'),
                    'black'  => trans('front.black'),
                ];

                $this->data['icon_colors'] = [
                    'moving' => 'green',
                    'stopped' => 'blue',
                    'offline' => 'red',
                    'engine' => 'blue',
                ];

                if (array_key_exists('icon_moving', $this->data) && array_key_exists($this->data['icon_moving'], $device_icon_colors))
                    $this->data['icon_colors']['moving'] = $this->data['icon_moving'];

                if (array_key_exists('icon_stopped', $this->data) && array_key_exists($this->data['icon_stopped'], $device_icon_colors))
                    $this->data['icon_colors']['stopped'] = $this->data['icon_stopped'];

                if (array_key_exists('icon_offline', $this->data) && array_key_exists($this->data['icon_offline'], $device_icon_colors))
                    $this->data['icon_colors']['offline'] = $this->data['icon_offline'];

                if (array_key_exists('icon_engine', $this->data) && array_key_exists($this->data['icon_engine'], $device_icon_colors))
                    $this->data['icon_colors']['engine'] = $this->data['icon_engine'];

                //DTRefactor
                //DeviceRepo::update($item->id, $this->data);
                // REMOVER O VEÍCULO DOS ALERTAS
                
                if ($item->protocol == "gt06"){
                        $alert_protocol=104;
                    }
                elseif ($item->protocol == "suntech"){
                        $alert_protocol=105;
                    }
                elseif ($item->protocol == "mxt"){
                        $alert_protocol=106;
                    }
                else{
                        $alert_protocol=107;
                    }
                DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $item->id])->delete();
                // FIM DE VEÍCULO DOS ALERTAS
                
                $item->update($this->data);
                
                // AO UTILIZAR NOVOS PROTOCOLOS ACRESCENTAR ABAIXO PARA ACRESCENTAR AOS ALERTAS AUTOMATICAMENTE
                    if(!$item->no_powercut){    
                        if ($item->protocol == "gt06"){
                            $alert_protocol=104;
                        }
                        elseif ($item->protocol == "suntech"){
                            $alert_protocol=105;
                        }
                        elseif ($item->protocol == "mxt"){
                            $alert_protocol=106;
                        }
                        else{
                            $alert_protocol=107;
                        }
                        DB::table('alert_device')->insert(['alert_id' => $alert_protocol, 'device_id' => $item->id]);
                    }
                    else{
                        DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $item->id])->delete();
                    }
                    //INSERÇÃO DO ALERTA SEM COMUNICAÇÃO POR 24H
                    $alerts_ = DB::table('alert_device')->where(['alert_id' => 52, 'device_id' => $item->id])->get();
                    if(empty($alerts_))
                        DB::table('alert_device')->insert(['alert_id' => 52, 'device_id' => $item->id]);

                /////////////////////////////////// FIM DA INSERÇÃO DO VEÍCULO NOS ALERTAS                                          
                DB::connection('traccar_mysql')->table('unregistered_devices_log')->where('imei', '=', $this->data['imei'])->delete();
                 
                // UILMO VER BUG DE TROCA DE NOMES SEM ALTERAR O USO DO RASTREADOR
                
                $imei_ = tracker::where('imei',$item->imei)->get();
                
                foreach ($imei_ as $imei){
                    /*debugar(true, $imei->id);
                    $imei_2 = tracker::find($imei->id);
                    if (str_contains(Str::lower($item->name), 'teste')){
                        $imei_->in_use = false;
                        $imei_->save();
                    }
                    else{
                        $imei_->in_use = true;
                        $imei_->save();
                    }*/
                }

                $this->deviceSyncUsers($item);
                $this->createSensors($item->id);
            }
            catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error').$e->getMessage()]);
            }

            commitTransaction();

            if ($prev_timezone_id != $item->timezone_id){
                $item->applyPositionsTimezone();
            }

            debugar(true, 'O usuário: '.Auth::user()->id.', alterou o veículo: '.$item->id.' do cliente: '.$item->name.'('.$item->object_owner.')');

            return ['status' => 1, 'id' => $item->id];
        }
        catch (ValidationException $e)
        {
            debugar(true, $e);
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    public function destroy()
    {
        $device_id = array_key_exists('id', $this->data) ? $this->data['id'] : (empty($this->data['device_id']) ? NULL : $this->data['device_id']);

        $item = DeviceRepo::find($device_id);

        $this->checkException('devices', 'remove', $item);

        beginTransaction();
        $imei_removeds_ = tracker::where('imei',$item->imei)->get();
        foreach ($imei_removeds_ as $imei_removed_){
            $imei_ = tracker::find($imei_removed_->id);
            if (!$imei_->in_use == 0){
                $imei_->in_use = 0;
                $imei_->history = $imei_->history." Veículo de placa ".$item->plate_number." deletado (".$item->id.")";
                $imei_->save();
            }
        }

        try {
            Log::info('O usuário: '.Auth::user()->id.', deletou o veículo: '.$item->id.' do cliente: '.$item->name.'('.$item->object_owner.')');
            //Log::info('O usuário: '.Auth::user()->id.', deletou o veículo: '.$item->id.' do cliente: '.$item->name.'('.$item->object_owner.')');
            $item->users()->sync([]);

            DB::connection('traccar_mysql')->table('devices')->where('id', '=', $item->traccar_device_id)->delete();
            EventRepo::deleteWhere(['device_id' => $item->id]);
            DeviceRepo::delete($item->id);

            DB::table('user_device_pivot')->where('device_id', $item->id)->delete();
            DB::table('device_sensors')->where('device_id', $item->id)->delete();
            DB::table('device_services')->where('device_id', $item->id)->delete();
            DB::table('user_drivers')->where('device_id', $item->id)->update(['device_id' => null]);

            if (Schema::connection('traccar_mysql')->hasTable('positions_'.$item->traccar_device_id))
                DB::connection('traccar_mysql')->table('positions_'.$item->traccar_device_id)->truncate();

            Schema::connection('traccar_mysql')->dropIfExists('positions_'.$item->traccar_device_id);

            // REMOVER O VEÍCULO DOS ALERTAS
                if ($item->protocol == "gt06"){
                        $alert_protocol=104;
                    }
                    elseif ($item->protocol == "suntech"){
                        $alert_protocol=105;
                    }
                    elseif ($item->protocol == "mxt"){
                        $alert_protocol=106;
                    }
                    else{
                        $alert_protocol=107;
                    }
                    DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $item->id])->delete();
            // FIM DE VEÍCULO DOS ALERTAS
            

            commitTransaction();
        }
        catch (\Exception $e) {
            rollbackTransaction();
        }

        return ['status' => 1, 'id' => $item->id, 'deleted' => 1];
    }

    public function changeActive()
    {
        if ( ! array_key_exists('id', $this->data))
            throw new ValidationException(['id' => 'No id provided']);

        if (is_array($this->data['id']))
            $devices = DeviceRepo::getWhereIn($this->data['id']);
        else
            $devices = DeviceRepo::getWhereIn([$this->data['id']]);

        $filtered = $devices->filter(function($device) {
            return $this->user->can('active', $device);
        });

        if ( ! empty($filtered)) {
            DB::table('user_device_pivot')
                ->where('user_id', $this->user->id)
                ->whereIn('device_id', $filtered->pluck('id')->all())
                ->update([
                    'active' => (isset($this->data['active']) && $this->data['active'] != 'false') ? 1 : 0
                ]);
        }

        return ['status' => 1];
    }

    public function itemsJson()
    {
        $this->checkException('devices', 'view');

        $time = time();
        if ( empty($this->data['time']) ) {
            $this->data['time'] = $time - 5;
        }

        $this->data['time'] = intval($this->data['time']);

        $devices = UserRepo::getDevicesHigherTime($this->user, $this->data['time']);

        $items = array();
        if (!empty($devices)) {
            foreach ($devices as $item) {
                $items[] = $this->generateJson($item, TRUE, TRUE);
            }
        }

        $events = EventRepo::getHigherTime($this->user->id, $this->data['time']);
        !empty($events) && $events = $events->toArray();

        foreach ($events as $key => $event) {
            $events[$key]['time'] = tdate($event['time']);
            $events[$key]['speed'] = round($this->user->unit_of_distance == 'mi' ? kilometersToMiles($event['speed']) : $event['speed']);
            $events[$key]['altitude'] = round($this->user->unit_of_altitude == 'ft' ? metersToFeets($event['altitude']) : $event['altitude']);
            $events[$key]['message'] = parseEventMessage($events[$key]['message'], $events[$key]['type']);
            $events[$key]['device_name'] = empty($events[$key]['device']['name']) ? null : $events[$key]['device']['name'];
            $events[$key]['sound'] = array_get($event, 'alert.notifications.sound.active', false) ? asset('https://sistema.carseg.com.br/assets/audio/hint.mp3') : null;

            if (empty($event['geofence']))
                continue;

            $name = htmlentities($events[$key]['geofence']['name']);
            $events[$key]['geofence'] = [
                'id' => $events[$key]['geofence']['id'],
                'name' => $name
            ];

            $name = htmlentities($events[$key]['device']['name']);
            $events[$key]['device'] = [
                'id' => $events[$key]['device']['id'],
                'name' => $name
            ];
        }

        return ['items' => $items, 'events' => $events, 'time' => $time, 'version' => Config::get('tobuli.version')];
    }

    public function generateJson($device, $json = TRUE, $device_info = FALSE) {
        $status = $device->getStatus();

        $data = [];

        if ($this->api && $device_info) {
            $device_data = $device->toArray();

            if (isset($device_data['users'])) {
                $filtered_users = $device->users->filter(function ($user) {
                    return $this->user->can('show', $user);
                });

                $device_data['users'] = $this->formatUserList($filtered_users);
            }

            $device_data['lastValidLatitude']  = floatval($device->lat);
            $device_data['lastValidLongitude'] = floatval($device->lng);
            $device_data['latest_positions']   = $device->latest_positions;
            $device_data['icon_type'] = $device->icon->type;

            $device_data['active'] = intval($device->pivot->active);
            $device_data['group_id'] = intval($device->pivot->group_id);

            //$device_data['user_timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['user_timezone_id'] = null;
            //$device_data['timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['timezone_id'] = is_null($device->timezone_id) ? null : intval($device->timezone_id);

            $device_data['id'] = intval($device->id);
            $device_data['user_id'] = intval($device->pivot->user_id);
            $device_data['traccar_device_id'] = intval($device->traccar_device_id);
            $device_data['icon_id'] = intval($device->icon_id);
            $device_data['deleted'] = intval($device->deleted);
            $device_data['fuel_measurement_id'] = intval($device->fuel_measurement_id);
            $device_data['tail_length'] = intval($device->tail_length);
            $device_data['min_moving_speed'] = intval($device->min_moving_speed);
            $device_data['min_fuel_fillings'] = intval($device->min_fuel_fillings);
            $device_data['min_fuel_thefts'] = intval($device->min_fuel_thefts);
            $device_data['snap_to_road'] = intval($device->snap_to_road);
            $device_data['gprs_templates_only'] = intval($device->gprs_templates_only);
            $device_data['group_id'] = intval($device->pivot->group_id);
            $device_data['current_driver_id'] = is_null($device->current_driver_id) ? null : intval($device->current_driver_id);
            $device_data['pivot']['user_id'] = intval($device->pivot->user_id);
            $device_data['pivot']['device_id'] = intval($device->id);
            $device_data['pivot']['group_id'] = intval($device->pivot->group_id);
            $device_data['pivot']['current_driver_id'] = is_null($device->current_driver_id) ? null : intval($device->current_driver_id);
            //$device_data['pivot']['timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['pivot']['timezone_id'] = null;
            $device_data['pivot']['active'] = intval($device->pivot->active);
            
            $device_data['time'] = $device->getTime();
            $device_data['course'] = isset($device->course) ? $device->course : null;
            $device_data['speed'] = $device->getSpeed();

            $data = [
                'device_data' => $device_data
            ];
        }

        $driver = $device->driver;

        return [
                'id'            => intval($device->id),
                'alarm'         => is_null($this->user->alarm) ? 0 : $this->user->alarm,
                'name'          => $device->name,
                'online'        => $status,
                'time'          => $device->time,
                'timestamp'     => $device->timestamp,
                'acktimestamp'  => $device->ack_timestamp,
                'lat'           => floatval($device->lat),
                'lng'           => floatval($device->lng),
                'course'        => (isset($device->course) ? $device->course : '-'),
                'speed'         => $device->getSpeed(),
                'altitude'      => $device->altitude,
                'icon_type'     => $device->icon->type,
                'icon_color'    => $device->getStatusColor(),
                'icon_colors'   => $device->icon_colors,
                'icon'          => $device->icon->toArray(),
                'power'         => '-',
                'address'       => '-',
                'protocol'      => $device->getProtocol() ? $device->getProtocol() : '-',
                'driver'        => ($driver ? $driver->name : '-'),
                'driver_data'   => $driver ? $driver : [
                    'id' => NULL,
                    'user_id' => NULL,
                    'device_id' => NULL,
                    'name' => NULL,
                    'rfid' => NULL,
                    'phone' => NULL,
                    'email' => NULL,
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ],
                'sensors'            => $json ? json_encode($device->getFormatSensors()) : $device->getFormatSensors(),
                'services'           => $json ? json_encode($device->getFormatServices()) : $device->getFormatServices(),
                'tail'               => $json ? json_encode($device->tail) : $device->tail,
                'distance_unit_hour' => $this->user->unit_of_speed,
                'unit_of_distance'   => $this->user->unit_of_distance,
                'unit_of_altitude'   => $this->user->unit_of_altitude,
                'unit_of_capacity'   => $this->user->unit_of_capacity,
                'stop_duration'      => $device->stop_duration,
                'moved_timestamp'    => $device->moved_timestamp,
                'engine_status'      => $device->getEngineSensor() ? $device->getEngineStatus() : null,
                'detect_engine'      => $device->detect_engine,
                'engine_hours'       => $device->engine_hours,
            ] + $data;
    }

    private function checkDevicesLimit($user = NULL) {
        if (is_null($user))
            $user = $this->user;

        if (isset($_ENV['limit']) && $_ENV['limit'] > 1)
        {
            $devices_count = DeviceRepo::countwhere(['deleted' => 0]);

            if ($devices_count >= $_ENV['limit'])
                return false;
        }

        if ( ! is_null($user->devices_limit))
        {
            $user_devices_count = $user->isManager() ? getManagerUsedLimit($user->id) : $user->devices->count();

            if ($user_devices_count >= $user->devices_limit)
                return true;
        }

        return false;
    }

     # Sensor groups
    private function createSensors($device_id) {
        if ( ! isAdmin())
            return;
        if ( ! isset($this->data['sensor_group_id']))
            return;

        $group_sensors = SensorGroupSensorRepo::getWhere(['group_id' => $this->data['sensor_group_id']]);

        if (empty($group_sensors))
            return;

        foreach ($group_sensors as $sensor)
        {
            $sensor = $sensor->toArray();

            if ( ! $sensor['show_in_popup'])
                unset($sensor['show_in_popup']);

            if (in_array($sensor['type'], ['harsh_acceleration', 'harsh_breaking']))
                $sensor['parameter_value'] = $sensor['on_value'];

            SensorModalHelper::setData(array_merge([
                'user_id' => $this->user->id,
                'device_id' => $device_id,
                'sensor_type' => $sensor['type'],
                'sensor_name' => $sensor['name'],
            ], $sensor));

            SensorModalHelper::create();
        }
    }

    private function deviceSyncUsers($device) {
        if (isset($this->data['user_id']))
        {
            if ( ! $this->user->isGod()) {
                $admin_user = DB::table('users')
                    ->select('users.id')
                    ->join('user_device_pivot', 'users.id', '=', 'user_device_pivot.user_id')
                    ->where(['users.email' => 'admin@gpswox.com'])
                    ->where(['user_device_pivot.device_id' => $device->id])
                    ->first();

                if ($admin_user)
                    $this->data['user_id'][$admin_user->id] = $admin_user->id;
            }

            $device->users()->sync($this->data['user_id']);
        }

        DB::table('user_device_pivot')
            ->where([
                'device_id' => $device->id,
                'user_id' => $this->user->id
            ])
            ->update([
                'group_id' => $this->data['group_id'],
                //'timezone_id' => $this->data['timezone_id'] == 0 ? NULL : $this->data['timezone_id']
            ]);
    }

    private function formatUserList($users)
    {
        if (! count($users))
            return [];

        foreach ($users as $user)
            $users_array[] = ['id' => intval($user['id']), 'email' => $user['email']];

        return $users_array;
    }
}