<?php namespace App\Http\Controllers\Admin;

use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Tobuli\Validation\ClientFormValidator;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;

use App\Monitoring;
use App\customer;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Facades\GeoLocation;
use App\Insta_maint;
use App\tracker;
use App\Http\Controllers\Admin\MessengersController;
use Tobuli\Repositories\User\UserRepositoryInterface as User;
//use App\Messengers;


/*###############
    PARA DEBUGAR

    if (Auth::User()->id == 6) {
            //dd($Monitoring);
        } 
#################*/

class MonitoringsController extends BaseController {
        /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'monitorings';
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

    function __construct(ClientFormValidator $clientFormValidator, Device $device, TraccarDevice $traccarDevice, Event $event)
    {
        parent::__construct();
        $this->clientFormValidator = $clientFormValidator;
        $this->device = $device;
        $this->traccarDevice = $traccarDevice;
        $this->event = $event;
    }
 
    public function index($page = 0, $search_item = "") {
        $page = strip_tags($page);
                $search_item = strip_tags($search_item);
                $exceptions = ['teste', 'pendente', 'tecnico', 'pendentes', 'cancelar', 'cancelado','retirado', 'deletar','enviados para o'];
                
                $input = Input::all();
                $users = null;
                
                if (Auth::User()->isManager()) {
                    $users = Auth::User()->subusers()->lists('id', 'id')->all();
                    $users[] = Auth::User()->id;
                }
                
                $query = Monitoring::orderBy('make_contact', 'asc')
                    ->orderBy('cause', 'desc')
                    ->orderBy('sent_maintenance', 'asc')
                    ->orderBy('occ_date', 'asc')
                    ->orderBy('modified_date', 'asc')
                    ->where('active', 1)
                    ->where('treated_occurence', 0);

                if ($search_item != "") {
                    $query = $query->where(function ($query) use ($search_item) {
                        // Consulta quando 'cause' é 'Bateria Violada'
                        $query->where('cause', 'Bateria Violada')
                            ->whereIn('device_id', function ($subQuery) use ($search_item) {
                                $subQuery->select('id')
                                    ->from('devices')
                                    ->where(function ($query) use ($search_item) {
                                        $query->where('name', 'like', '%' . $search_item . '%')
                                            ->orWhere('object_owner', 'like', '%' . $search_item . '%')
                                            ->orWhere('plate_number', 'like', '%' . $search_item . '%');
                                    });
                            });
                
                        // Consulta quando 'cause' é 'offline_duration' ou outros
                        $query->orWhere('cause', '!=', 'Bateria Violada')
                            ->whereIn('device_id', function ($subQuery) use ($search_item) {
                                $subQuery->select('traccar_device_id')
                                    ->from('devices')
                                    ->where(function ($query) use ($search_item) {
                                        $query->where('name', 'like', '%' . $search_item . '%')
                                            ->orWhere('object_owner', 'like', '%' . $search_item . '%')
                                            ->orWhere('plate_number', 'like', '%' . $search_item . '%');
                                    });
                            });
                    });
                }

                $results = $query->get();

                
                $monitorings = $query->get();
                
                $page = 0;
                $items = $monitorings->paginate(10, $page);
                
                foreach ($items as $item) {
                    if($item->cause == 'Bateria Violada') {
                        $device = DB::table('devices')->where('id', $item->device_id)->first();
                    } else {
                        $device = DB::table('devices')->where('traccar_device_id', $item->device_id)->first();
                    }
                    
                    if ($device !== null) {
                        $item = $this->processDevice($device, $item); // Supondo que processDevice() faz as modificações necessárias
                    } else {
                        Monitoring::where('id', $item->id)->delete();
                    }
                }
                
                $section = $this->section;
                return View::make('admin::'.ucfirst($this->section).'.' . 'table')->with(compact('items','section','monitorings'));
                
                // Função para processar o dispositivo
    }

    public function create() {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->lists('email', 'id')->all();
        $devices = UserRepo::getDevices($this->user->id);
        $Monitorings = Monitoring::all();        
        /*$devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) { return $devices_->traccar_device_id == 832; });
        
            foreach ($devices as $item){
                $device = array_get($item, 'updated_at');
                $device = $device->toArray();
                $device = array_get($device, 'formatted');
                $device = $item;
        }*/
        
        $devices = UserRepo::getDevices($this->user->id);
        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('managers', 'Monitorings', 'devices', 'device'));
    }
    
    public function edit($id) {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->lists('email', 'id')->all();
        $Monitoring = Monitoring::where('id', $id)->first();
        //dd($Monitoring->device_id);
        
        //$Monitoring = $Monitoring->toArray();
        $devices_ = DB::table('devices')->where('traccar_device_id', $Monitoring->device_id)->first();
        $device = UserRepo::getDevice($this->user->id, $devices_->id);
        //dd($devices_);
        
        
    /*foreach ($devices_ as $device_){
            
            $device = UserRepo::getDevice($this->user->id, $device_->id);
        }*/

        //dd($device);

            if (empty($Monitoring->information)){
                $item = $Monitoring;
                $item->additional_notes = $device->additional_notes;
                $item->information = "";
                }
            else{
                    
                $item = $Monitoring;
                $item->additional_notes = $device->additional_notes;
            }
            
            //Pegar contato em outras tabelas
            $stateandcity = getGeoCity( $device->traccar->lastValidLatitude, $device->traccar->lastValidLongitude );
            $last_address = $stateandcity[2]." - ".$stateandcity[1]." - ".$stateandcity[0];
            $item->last_address = $last_address;
            $item->city = $device->city;
            $item->name = $device->name;
            $item->plate_number = $device->plate_number;
            $item->device_id = $device->id;
            $item->device_model = $device->device_model;
            $item->vehicle_color = $device->vehicle_color;
            
            $poi_next_001 = collect([]);
            $poi_next_01 = collect([]);
            $poi_next_1 = collect([]);
            $poi_next_10 = collect([]);

        
        
        //if (Auth::User()->id == 6) {
            $pois = DB::table('user_map_icons')->where('user_id', 1025)->get();
            foreach ($pois as $poi){
                
                $coordinates = json_decode($poi->coordinates);
                $distance = round(getDistance($coordinates->lat, $coordinates->lng,$device->traccar->lastValidLatitude,$device->traccar->lastValidLongitude),2);
                
                if($distance <0.01){
                    $poi->distance = $distance;
                    //dd($distance, $poi);
                    $poi_next_001->push($poi);
                }
                elseif((0.01 < $distance) &&  ($distance <0.1) ){
                    $poi->distance = $distance;
                    //dd($distance, $poi);
                    $poi_next_01->push($poi);
                }
                elseif((0.1 < $distance) &&  ($distance <1) ){
                    $poi->distance = $distance;
                    //dd($distance, $poi);
                    $poi_next_1->push($poi);
                }
                elseif((1 < $distance) &&  ($distance <10) ){
                    $poi->distance = $distance;
                    //dd($distance, $poi);
                    $poi_next_10->push($poi);
                }
            }
            //dd($poi_next_001,$poi_next_01, $poi_next_1, $poi_next_10);    
            //}

            
            //dd('ola');
            
            if($device->name == "ASSOCIAÇÃO LÍDER" ||  $device->name == "COOPERATIVA"){
                    $item->contact = $device->contact;
            }
            else{
                if($device->cliente_id==0){
                    $customers = customer::where('name', $device->name)->get();
                    
                    if (empty($customers->contact)){
                        foreach ($customers as $customer){
                            $item->contact = $customer->contact;
                        }
                    }
                    else
                        $item->contact = $device->contact;
                    //dd($item);
                    
                }
                else{
                    $customers = customer::find($device->cliente_id);
                    if (empty($customers->contact))
                        $item->contact = $customer->contact;
                    else
                        $item->contact = $device->contact;
                }
            }
        //****
        
        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('managers', 'item', 'poi_next_001', 'poi_next_01', 'poi_next_1', 'poi_next_10'));
    }
    
    public function store(Request $request)
    {   
    
        /*public function store(Request $request)  
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
            $device_id = $request->input('plate_number');
            $device = UserRepo::getDevice($this->user->id, $device_id);
            $plate_number = $device->plate_number;
            $owner = $device->object_owner;
            $customer = $device->name;
            //$traccar_device_id = $device->traccar_device_id;
            $gps_date = $device->traccar->device_time;

            

            $active = $request->input('active');

            //debugar(true, $request->input('active'));
            
            $Monitoring = new Monitoring;
            
            $Monitoring->active = $active;
            $Monitoring->device_id = $device->id;
            $Monitoring->plate_number = $plate_number;
            $Monitoring->cause = $request->input('cause');
            $Monitoring->information = json_encode($gps_date);
            $Monitoring->gps_date = $gps_date;
            $Monitoring->occ_date = $request->input('occorunce_date');
            $Monitoring->next_con = $request->input('next_contact');
            $Monitoring->make_contact = $request->input('make_contact');
            $Monitoring->treated_occurence = $request->input('treated_occurence');
            $Monitoring->sent_maintenance = $request->input('sent_maintenance');
            $Monitoring->automatic_treatment = false;
            $Monitoring->customer = $customer;
            $Monitoring->owner = $owner;
                
            //]);
            debugar(true, $Monitoring);
            
            $Monitoring->save();
            
            //dd('oi');
            
            if($request->input('sent_maintenance')){
                $search_device = insta_maint::where('device_id',$device_id)
                                            ->where('active', true)
                                            ->get()
                                            ->count();
                if(!$search_device>0){
                    $service_count = insta_maint::all()->count();
                    $first = Carbon::now();
                    $os_number = $first->month;
                    $os_number .= $first->year;
                    $os_number .= strval($service_count+1);
                    $os_number .= '-'.strval(rand(00, 99));
                    $new_service = new insta_maint;

                    $new_service->active = true;
                    $new_service->device_id = $device_id;
                    $new_service->technician_id = 3;
                    $new_service->expected_date = '';
                    $new_service->city = 'Capim Grosso';
                    $new_service->installation_date = '';
                    $new_service->installation_location = '';
                    $new_service->installation_photo_id = 0;
                    $new_service->maintenance = true;
                    $new_service->type = 0;
                    $new_service->os_number = $os_number;
                    $new_service->obs = 'Inserção pelo monitoramento';
                    $new_service->occurrency_id = $Monitoring->id;

                    $new_service->save();
                }
            }

            //dd('oi');
            
        return Response::json(['status' => 1]);
    }
    
    public function auto_store()
    {   
    
    
        /*public function store(Request $request)  
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
        
        //return dd($request);
        if ($request->has('plate_number')) {
            $device_id = $request->input('plate_number');
            $devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) use ($device_id) { return $devices_->id == $device_id; });
            foreach ($devices as $item){
                $plate_number = array_get($item, 'plate_number');
                $owner = array_get($item, 'object_owner');
                $customer = array_get($item, 'name');
                
                $gps_date = array_get($item, 'traccar');
                $gps_date = array_get($gps_date, 'device_time');
            }
            $occorunce_date = (string)$request->input('occorunce_date');
            $occorunce_date = Carbon::createFromFormat('Y-m-d', $occorunce_date,-3);//->toDateTimeString();
            $dayOfWeek = array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado');
            
            $occorunce_date = $dayOfWeek[$occorunce_date->dayOfWeek].', '.$occorunce_date->day.'-'.$occorunce_date->month.'-'.$occorunce_date->year.' '.$occorunce_date->hour.':'.$occorunce_date->minute.':'.$occorunce_date->second;
            
            $next_contact = $request->input('next_contact');
            $next_contact = Carbon::createFromFormat('Y-m-d', $next_contact,-3);
            $next_contact = $dayOfWeek[$next_contact->dayOfWeek].', '.$next_contact->day.'-'.$next_contact->month.'-'.$next_contact->year.' '.$next_contact->hour.':'.$next_contact->minute.':'.$next_contact->second;
            
            $Monitoring = new Monitoring([
                'active' => $request->input('active'),
                'plate_number' => $plate_number,
                'cause' => $request->input('cause'),
                'information' => $request->input('information'),
                'gps_date' => $gps_date,
                'occ_date' => $occorunce_date,
                'next_con' => $request->input('next_contact'),
                'make_contact' => $request->input('make_contact'), 
                'sent_maintenance' => $request->input('sent_maintenance'),
                'automatic_treatment' => false,
                'customer' => $customer,
                'owner' => $owner
                
            ]);
            $Monitoring->save();
            return Response::json(['status' => 1]);
        }
    }
    
    public function update(Request $request)
    {   
        
        $rules = [  'id' => 'required|numeric',
                'cause' => 'required', 
                'information' => 'required', 
				'next_con' => 'required', 
				'contact'=>'required'];
		$this->validate($request, $rules);
        
        $Monitoring = Monitoring::find($request->input('id'));
        if ($request->input('treated_occurence') == 1) 
            $Monitoring->active = 0;
        else
            $Monitoring->active = $request->input('active');
        //dd($Monitoring);
        $Monitoring->cause = $request->input('cause');
        $Monitoring->information = $request->input('information');
        $Monitoring->occ_date = $request->input('occ_date');
        if ($request->input('active_contact') == 1) {
            $Monitoring->next_con = $request->input('next_con');
        }
        $Monitoring->treated_occurence = $request->input('treated_occurence');
        
        $Monitoring->make_contact = $request->input('make_contact');
        $Monitoring->modified_date = Carbon::now('-3');
        $Monitoring->sent_maintenance = $request->input('sent_maintenance');
        $Monitoring->save();
        
        DB::table('devices')->where('id', $Monitoring->device_id)->update(['contact' => $request->input('contact'), 'city' => $request->input('city'), 'additional_notes' => $request->input('additional_notes')]);

        if($request->input('name') == "ASSOCIAÇÃO LÍDER" ||  $request->input('name') == "COOPERATIVA"){
            DB::table('devices')->where('id', $request->input('device_id'))->update(['contact' => $request->input('contact')]);
        }
        else{
            if($request->input('cliente_id')==0){
                DB::table('customers')->where('name', $request->input('name'))->update(['contact' => $request->input('contact')]);
            }
            else{
                DB::table('customers')->where('id', $request->input('cliente_id'))->update(['contact' => $request->input('contact')]);
            }
        }
        
        if($request->input('sent_maintenance')){
            $search_device = insta_maint::where('device_id',$request->input('device_id'))
                                            ->where('active', true)
                                            ->get()
                                            ->count();
            if(!$search_device>0){
                $service_count = insta_maint::all()->count();
                $first = Carbon::now();
                $os_number = $first->month;
                $os_number .= $first->year;
                $os_number .= strval($service_count+1);
                $os_number .= '-'.strval(rand(00, 99));
                $new_service = new insta_maint([
                    'active' => true,
                    'device_id' => $request->input('device_id'),
                    'technician_id' => 3,
                    'expected_date' => '',
                    'city' => 'Capim Grosso',
                    'installation_date' => '',
                    'installation_location' => '',
                    'installation_photo_id' => 0,
                    'maintenance' => true,
                    'type' => 0, 
                    'os_number' => $os_number,
                    'obs' => 'Inserção pelo monitoramento'.$Monitoring->information,
                    'occurrency_id' => $Monitoring->id
                ]);
                $new_service->save();
            }
        }

        return Response::json(['status' => 1]);
    }
    public function destroy(Request $request) {
        
        if (config('tobuli.object_delete_pass') && Auth::user()->isAdmin() && request('password') != config('tobuli.object_delete_pass')) {
            return ['status' => 0, 'errors' => ['message' => trans('front.login_failed')]];
        }

        $ids = $request->input('ids');

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $Monitoring = Monitoring::find($id); 
                $Monitoring->delete();
            }
        }

        return Response::json(['status' => 1]);
    }

    public function doDestroy(Request $request)
    {
        $ids = $request->input('ids');
        dd($ids);
        return view('admin::monitoring.destroy')->with(compact('ids'));
    }
    
    public function info($id)
    {
        //debugar(true,"Inicio");
        $items = Monitoring::where('device_id', $id)->where('active',0)->get();
        $count = $items->count();
        if ($count == 0){
            debugar(true,"Não tem registro");
            $situacao = 0; //dd("Não existem ocorrências anteriores!");
            $items->owner = "";
            $items->plate_number = "";
        }
        else{
            //debugar(true,"Tem registro");
            $situacao = 1;
            $devices_ = DB::table('devices')->where('id', $id)->get();
            //dd($items->count());
            foreach($devices_ as $device_){
                $device = UserRepo::getDevice($this->user->id, $device_->id);
            }
            $items->owner = $device->object_owner;
            $items->plate_number = $device->plate_number;
            //debugar(true,"Ate aqui ok");
            foreach($items as $item){
                // PARA DEBUGAR########################################################################
                /*$fp = fopen('/var/www/html/releases/20190129073809/public/debug.txt', "a+");
                fwrite($fp, "\r\n NE:".$item." ".date("F j, Y, g:i a")); 
                fclose($fp);*/
                // FIM DEBUGAR########################################################################
                //if($item->id==4445)
                    //dd($item);
                //debugar(true,$item->id);
                $event = DB::table('events')->find($item->event_id);
                
                $data_time = $item->occ_date;
                //dd($data_time);
                $year = Str::substr($data_time,0, 4);
                $month = Str::substr($data_time,5, 2);
                $day = Str::substr($data_time,8, 2);
                $first = $year.'-'.$month.'-'.$day;
                if($first=="--"){
                    $data_time = $item->timestamp;
                    $year = Str::substr($data_time,0, 4);
                    $month = Str::substr($data_time,5, 2);
                    $day = Str::substr($data_time,8, 2);
                    $first = $year.'-'.$month.'-'.$day;
                }
                //debugar(true,$first);
                $item->timestamp = $this->convert_date($first, false);
                //debugar(true,$item->id);
                if(!is_null($event)){
                    $item->lat = $event->latitude;
                    $item->lon = $event->longitude;
                }
                //debugar(true,$item->id);
                if(!$item->lat==0 && !$item->lon==0){
                    $location = GeoLocation::byCoordinates($item->lat, $item->lon);
                    //dd($location);
                    $item->city = $location->city;
                    $item->state = ' - '.$location->state;
                    $item->address = $location->address;
                    $item->place_id = $location->place_id;
                    }
                else{
                    $item->city = "Não foi possível localizar a cidade";
                    $item->address = "Não foi possível localizar o estado";
                    
                }
            }
        }
        return View::make('admin::'.ucfirst($this->section).'.info')->with(compact('situacao', 'items'));
    }   

    public function rem_add_alert($id){
        $device = UserRepo::getDevice($this->user->id, $id);
        //dd($device);
        DB::table('devices')->where('id', $id)->update(['no_powercut' => !$device->no_powercut]);
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
        $device = UserRepo::getDevice($this->user->id, $id);
        if($device->no_powercut)
            DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $device->id])->delete();
        else
            DB::table('alert_device')->insert(['alert_id' => $alert_protocol, 'device_id' => $device->id]);
        
        return View::make('admin::'.ucfirst($this->section).'.no_powercut')->with(compact('device'));
    }
    
    
    function validaData($date, $format = 'Y-m-d H:i:s') {
        
        if (!empty($date) && $v_date = date_create_from_format($format, $date)) {
            $v_date = date_format($v_date, $format);
            return ($v_date && $v_date == $date);
        }
        return false;
    }
    public function convert_date($date, $full_date){
        $dayOfWeek = array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado');
        if (!$full_date==true){
            $modified_date = Carbon::createFromFormat('Y-m-d', $date,-3);
            $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year;
        }
        else{
            $modified_date = Carbon::createFromFormat('Y-m-d H:i:s', $date,-3);
            $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year.' '.$modified_date->hour.':'.$modified_date->minute.':'.$modified_date->second;
        }
        return $modified_date;
    }

    public function processDevice($device, $item)
    {
        $item->customer = $device->name;
        $item->owner = $device->object_owner;
        $item->plate_number = $device->plate_number;
        if(!$item->occ_date==null){
            if($this->validaData($item->occ_date)){
                $item->occ_date = Carbon::createFromFormat('Y-m-d H:i:s', $item->occ_date)
                                        ->timezone('America/Sao_Paulo')
                                        ->format('Y-m-d H:i:s');
            }
        }
    }
}
