<?php namespace App\Http\Controllers\Admin;

use Facades\Repositories\UserRepo;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\CsvImportRequest;
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
use App\tracker;


class MontPayController extends BaseController {
        /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'chips';
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
 
    public function index() {
        //$output = shell_exec("php /var/www/html/releases/20190129073809/phpcs.phar -s /var/www/html/releases/20190129073809/config");
        //dd($output);
        if(false){

                $customer_app_ext = array();

                /*
                $curl = curl_init();
                $url = "https://api.vhsys.com/v2/contas-receber";
                $params = array('liquidado' => 'Nao', 'data_vencimento' => '2010-01-01,2021-02-15', 'offset' => '100');
                $url = $url . '?' . http_build_query($params);

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
        //            CURLOPT_POST => true,
        //            CURLOPT_POSTFIELDS => 'limit=1',
                    CURLOPT_HTTPHEADER => array(
                        "Access-Token: BbCNHOWNJTXdPKNNUBFbRLgbHCAgbT",
                        "Secret-Access-Token: joF9UxR3OPyhCS7YjqjSizKN9tsSwu",
                        "Content-Type: application/json"
                    ),
                ));

                //curl_setopt($curl, CURLOPT_POSTFIELDS, 'foo=1&bar=2&baz=3');

                $response = json_decode(curl_exec($curl));
                $erro = curl_error($curl);
                echo '<pre>';
                    dd($response);
                echo '</pre>';
                */

                $customers = customer::orderby('id', 'asc')
                                    ->where('id_app_ext','like',0)
                                    ->get();
                //dd($customers);
                $curl = curl_init();
                $url = "https://api.vhsys.com/v2/clientes";
                $params = array('lixeira' => 'Nao', 'limit' => '10');
                $url = $url . '?' . http_build_query($params);

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
        //            CURLOPT_POST => true,
        //            CURLOPT_POSTFIELDS => 'limit=1',
                    CURLOPT_HTTPHEADER => array(
                        "Access-Token: BbCNHOWNJTXdPKNNUBFbRLgbHCAgbT",
                        "Secret-Access-Token: joF9UxR3OPyhCS7YjqjSizKN9tsSwu",
                        "Content-Type: application/json"
                    ),
                ));

                //curl_setopt($curl, CURLOPT_POSTFIELDS, 'foo=1&bar=2&baz=3');

                $response_cru = curl_exec($curl);

                $pos_ini = strripos($response_cru, "total");
                $total = preg_replace('/[^\d\-]/', '',substr($response_cru, $pos_ini+7,5));
                $limit = 250;
                $offset = 0;

                $total_paginacao = intval(ceil($total/$limit));
                //dd($total_paginacao);
                for ($i=0; $i<$total_paginacao; $i++){
                    $curl = curl_init();
                    $url = "https://api.vhsys.com/v2/clientes";
                    $params = array('lixeira' => 'Nao', 'limit' => $limit, 'offset' => $offset);
                    $url = $url . '?' . http_build_query($params);

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
            //            CURLOPT_POST => true,
            //            CURLOPT_POSTFIELDS => 'limit=1',
                        CURLOPT_HTTPHEADER => array(
                            "Access-Token: BbCNHOWNJTXdPKNNUBFbRLgbHCAgbT",
                            "Secret-Access-Token: joF9UxR3OPyhCS7YjqjSizKN9tsSwu",
                            "Content-Type: application/json"
                        ),
                    ));

                    $response_cru = curl_exec($curl);
                    $response = json_decode($response_cru);
                    $erro = curl_error($curl);
                    $customers_app_ext[$i] = $response->data;
                    $offset = $offset +250;

                } 

                //dd($customers);
                //print($total."\n\n\r");
                $i=1;
                $j=0;
                //dd($customers);
                foreach ($customers as $customer){
                    /*echo '<pre>';
                        print_r($customer);
                    echo '</pre>';*/
                    $customer_finded = "";
                    $client_id = $customer->id_app_ext;
                    //dd($client_id);
                    if($client_id ==0){
                        $search = $customer->cpf_cnpj;
                        if(!empty($search)){
                            $base = "cnpj_cliente";
                        }
                        else{
                            $search = $customer->name;
                            $base = "razao_cliente";
                        }
                        /*echo '<pre>';
                            print_r($customers_app_ext);
                        echo '</pre>';*/
                        //dd($customers_app_ext);
                        $search1 = str_replace(array('.',' ','-'), '', $search);
                        $encontrado = false;
                        //dd("olá");
                        foreach($customers_app_ext as $customer_app_ext){
                            foreach($customer_app_ext as $customer_ext){
                                //dd($customer_ext);
                                $customer_ext = json_decode(json_encode($customer_ext), true);
                                //dd($customer_ext);
                                $base1 = str_replace(array('.',' ','-'), '', $customer_ext[$base]);
                                //dd($search1, $base1);
                                //dd();
                                //if($search1 == "05762300579") dd($search1, $base1);
                                
                                if( $base1 == $search1){
                                    $item = customer::find($customer->id);
                                    $item->id_app_ext = $customer_ext['id_cliente'];
                                    $item->save();
                                    $encontrado = true;
                                    $j++;
                                    echo("=>".$i."\n".$j);
                                    //dd('olá');
                                    break;
                                }
                                if ($encontrado) break;
                            }
                            if ($encontrado) break;
                        }
                        //dd($search, $customer);
                    }
                    $i++;
                }
                //SEGUNDA VERIFICAÇÃO (MANUAL)
                $customers = null;
                $customers = customer::orderby('id', 'asc')
                                    ->where('id_app_ext','like',0)
                                    ->where('name','not like','%carseg%')
                                    ->where('name','not like','%deletar%')
                                    ->where('name','not like','%retirado%')
                                    ->where('name','not like','%pendente%')
                                    ->where('name','not like','%teste%')
                                    ->where('name','not like','%tecnico%')
                                    ->where('name','not like','%crx1%')
                                    ->where('name','not like','%crx3%')
                                    ->where('name','not like','%rastreamento%')
                                    ->where('name','not like','%rastreador%')
                                    ->where('name','not like','%suntech%')
                                    ->where('name','not like','%ENVIADOS%')
                                    ->where('name','not like','%cancelamento%')
                                    ->where('name','not like','%uilmo%')
                                    ->where('name','not like','%ST210W%')
                                    ->where('name','not like','%ST340RB%')
                                    ->where('name','not like','%cancelar%')
                                    ->where('name','not like','')
                                    ->where('name','not like','DELET')
                                    ->where('name','not like','HELDER ALDERETE')
                                    ->get();
                
                if($customers){
                    $customers = $customers->lists('name', 'id')->all();
                    
                    foreach($customers_app_ext as $customer_app_ext){
                        foreach($customer_app_ext as $customer_ext){
                            //dd($customer_ext);
                            $customer_ext = json_decode(json_encode($customer_ext), true);
                            $customer_ext_list[$customer_ext['id_cliente']] = $customer_ext['razao_cliente'];
                        }
                    }
                    //dd($customer_ext_list);
                }
                //dd('olá');
                
            /*
            Integração Juno
            $clientId = "5jdiOcNBt36VRfKd";
            $clientSecret = "tQ^Ud9:Bt1V*ey>a08KlWU[j)|*lsDCQ";

            $base64 = base64_encode("{$clientId}:{$clientSecret}");
            
            $ch = curl_init("https://sandbox.boletobancario.com/authorization-server/oauth/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic {$base64}"
            ]);
            $resultado = curl_exec($ch);
            dd($resultado); 
            var_dump($resultado);*/
        /*
            $url="https://sandbox.boletobancario.com/authorization-server/oauth/token";
            $timeout = 60;
            $ch = curl_init();
            /*curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic {$base64}"
            ]);
            /*$data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data);
            //$headers = ['Authorization' => 'Basic ZXhlbXBsby1jbGllbnQtaWQ6ZXhlbXBsby1jbGllbnQtc2VjcmV0', 'Content-Type' => 'application/x-www-form-urlencoded'];
            //$body = ['Base64("exemplo-client-id:exemplo-client-secret")' => 'ZXhlbXBsby1jbGllbnQtaWQ6ZXhlbXBsby1jbGllbnQtc2VjcmV0'];
            /*$client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://sandbox.boletobancario.com/authorization-server', [
                'form_params' => [			
                'Authorization' => 'Basic ZXhlbXBsby1jbGllbnQtaWQ6ZXhlbXBsby1jbGllbnQtc2VjcmV0',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Base64("exemplo-client-id:exemplo-client-secret")' => 'ZXhlbXBsby1jbGllbnQtaWQ6ZXhlbXBsby1jbGllbnQtc2VjcmV0'
                ] 
            ]);

            //$response = $client->request('POST', 'https://sandbox.boletobancario.com/authorization-server',[$headers,$body]);
            /*dd($response);
            $response = $response->getBody()->getContents();
            $response = json_decode($response);*/

            /*
                            use GuzzleHttp\Psr7\Request;

                    // Create a PSR-7 request object to send
                    $headers = ['X-Foo' => 'Bar'];
                    $body = 'Hello!';
                    $request = new Request('HEAD', 'http://httpbin.org/head', $headers, $body);
                    $promise = $client->sendAsync($request);

                    // Or, if you don't need to pass in a request instance:
                    $promise = $client->requestAsync('GET', 'http://httpbin.org/get');

            /*$devices_ = DB::table('devices')->where('traccar_device_id', 300)->get();
                            
            foreach ($devices_ as $device_){
                $device = $device_;
                $device->traccar = DB::connection('traccar_mysql')->table('devices')->find($device->traccar_device_id);
            }*/
        }


      


        return view('admin::Chips.index');
    }

    public function importar()
    {
    }
    
    public function importar_csv(CsvImportRequest $request)
    {
        
    }

    public function create() {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->lists('email', 'id')->all();
        $devices = UserRepo::getDevices($this->user->id);
        $Monitorings = Monitoring::all();        
        $devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) { return $devices_->traccar_device_id == 832; });
        
        foreach ($devices as $item){
            $device = array_get($item, 'updated_at');
            $device = $device->toArray();
            $device = array_get($device, 'formatted');
            $device = $item;
        }
        
        $devices = UserRepo::getDevices($this->user->id);
        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('managers', 'Monitorings', 'devices', 'device'));
    }
    
    public function edit($id) {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->lists('email', 'id')->all();
        $Monitoring = Monitoring::where('id', $id)->get();
        $Monitoring = $Monitoring->toArray();
        $devices_ = DB::table('devices')->where('traccar_device_id', $Monitoring[0]['device_id'])->get();
        foreach ($devices_ as $device_){
            $device = UserRepo::getDevice($this->user->id, $device_->id);
        }
        if (empty($Monitoring[0]['information'])){
            $item = $Monitoring[0];
            $item['additional_notes'] = $device->additional_notes;
            $item['information'] = "";
            }
        else{
            $item = $Monitoring[0];
            $item['additional_notes'] = $device->additional_notes;
        }
        //Pegar contato em outras tabelas
        $item['name'] = $device->name;
        $item['device_id'] = $device->id;
        if($device->name == "ASSOCIAÇÃO LÍDER" ||  $device->name == "COOPERATIVA"){
                $item['contact'] = $device->contact;
        }
        else{
            if($device->cliente_id==0){
                $customers = customer::where('name', $device->name)->get();
                    foreach ($customers as $customer){
                        $item['contact'] = $customer->contact;
                    }
            }
            else{
                $customers = customer::find($device->cliente_id);
                $item['contact'] = $customer->contact;
            }
        }
        //****
        //dd('oi');
        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('managers', 'item'));
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
            $traccar_device_id = $device->traccar_device_id;
            $gps_date = $device->traccar->device_time;
            
            $Monitoring = new Monitoring([
                'active' => $request->input('active'),
                'device_id' => $traccar_device_id,
                'plate_number' => $plate_number,
                'cause' => $request->input('cause'),
                'information' => json_encode($gps_date),//$request->input('information'),
                //'gps_date' => $gps_date,
                'occ_date' => $request->input('occorunce_date'),
                'next_con' => $request->input('next_contact'),
                'make_contact' => $request->input('make_contact'), 
                'treated_occurence' => $request->input('treated_occurence'),
                'sent_maintenance' => $request->input('sent_maintenance'),
                'automatic_treatment' => false,
                'customer' => $customer,
                'owner' => $owner
                
            ]);
            //dd('oi');
            $Monitoring->save();
        return Response::json(['status' => 1]);
    }
    
    public function auto_store()
    {   
    
    
        /*public function store(Request $request)  
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
        
        //return dd($request);
        if ($request->has('plate_number')) {
            $device_id = $request->input('plate_number');
            $devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) use ($device_id) { return $devices_->traccar_device_id == $device_id; });
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
}
