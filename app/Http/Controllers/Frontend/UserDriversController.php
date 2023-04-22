<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\UserDriverModalHelper;

use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
use Facades\Repositories\UserDriverRepo;
use Tobuli\Entities\Device;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class UserDriversController extends Controller
{
    public function index() {
        $data = UserDriverModalHelper::get();
        foreach($data['drivers'] as $driver){
            if (! ($driver['cnh_expire']==null || $driver['cnh_expire']=="")){
                $driver['status'] = 'black';
                $cnh_expire_ = Carbon::createFromFormat('Y-m-d', $driver['cnh_expire'],-3);
                $now = Carbon::now('-3');
                if ($cnh_expire_->lessThan($now->addMonth())){
                    $driver['status'] = 'yellow';
                    if ($cnh_expire_->lessThan($now))
                        $driver['status'] = 'red'; 
                }
                $driver['cnh_expire'] = $this->convert_date($driver['cnh_expire'], true);
            }
            else{
                $driver['cnh_expire'] = "Adicionar a data de validade";
                $driver['status'] = 'red';
            }
        }

        return !$this->api ? view('front::UserDrivers.index2')->with($data) : ['items' => $data];
    }

    public function create()
    {
        $data = UserDriverModalHelper::createData();

        return !$this->api ? view('front::UserDrivers.create')->with($data) : $data;
    }

    public function store(Request $request)
    { 
        /*$rules = [  'cnh_expire'   => 'required|after:data'];
        $mensages = [   'after:data' => 'A data informada torna a carteira vencida.',
                        'required' => ':attribute é obrigatório'];
        
		$this->validate($request, $rules, $mensages);*/
        
        return UserDriverModalHelper::create();
    }

    public function edit()
    {
        $data = UserDriverModalHelper::editData();

        return is_array($data) && !$this->api ? view('front::UserDrivers.edit')->with($data) : $data;
    }

    public function update(Request $request)
    {
            /*$rules = [  'cnh_expire'   => 'required|before:data'];
            $mensages = [   'before:data' => 'A data informada torna a carteira vencida.',
                            'required' => ':attribute é obrigatório'];
            
            $this->validate($request, $rules, $mensages);*/
            return UserDriverModalHelper::edit();
    }

    public function doDestroy($id)
    {
        $data = UserDriverModalHelper::doDestroy($id);

        return is_array($data) ? view('front::UserDrivers.destroy')->with($data) : $data;
    }

    public function destroy()
    {
        return UserDriverModalHelper::destroy();
    }

    public function doUpdate( $id ) {
        return UserDriverModalHelper::edit($id);
    }
    
    public function change( $id ) {
        //Carbon::createFromFormat('Y-m-d H:i:s', $date,-3);
        $items = UserRepo::getDevice($this->user->id, $id);
        $drivers = UserDriverRepo::all()->where('user_id',$this->user->id)->filter(function ($driver) { 
            if (! ($driver->cnh_expire ==null || $driver->cnh_expire ==""))
                return Carbon::createFromFormat('Y-m-d', $driver->cnh_expire,-3) > Carbon::now('-3');
            });
        return view('front::UserDrivers.change_drivers')->with(compact('items', 'drivers'));
    }

    public function dochange( Request $request ) {
        /*$older_driver_id = $request->input('user_id');
        $user = $this->user;
        if(!$older_driver_id==null){
            UserDriverRepo::update($older_driver_id, ['device_id' => null]);
            
            $devices = Device::where('current_driver_id', $older_driver_id )->update(['current_driver_id' => 381]);
            $devices = Device::where('current_driver_id', $older_driver_id )->get();
            $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_log.txt', "a+");
            fwrite($fp, "\r\n DEBUGER ".json_encode($devices)." \r\n"); 
            fclose($fp);
            if(!$devices==null){
                foreach($devices as $device_){
                    $older_driver = UserDriverRepo::find(381);
                    $device = Device::find($device_->id);    
                    $device->changeDriver($older_driver);
                    DB::table('devices')->where('current_driver_id', $older_driver->id)->update(['current_driver_id' => 381]);
                    /*$device = Device::find($device_->id);
                    $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_log.txt', "a+");
                    fwrite($fp, "\r\n DEBUGER ".json_encode($device->current_driver_id)." \r\n"); 
                    fclose($fp);* //////
                }
            }
        }

        $item = UserDriverRepo::find($request->input('new_driver_id'));
        UserDriverRepo::update($item->id, ['device_id' => $request->input('device_id')]);
        
        $device = Device::whereHas('users', function($query) use ($user) {
            $query->where('id', $user->id);
        })->find($request->input('device_id'));
        
        if ($device)
        {
            $device->changeDriver($item);
        }
        //$item->device_id = $request->input('device_id');
        //DB::table('devices')->where('id', $request->input('device_id'))->update(['contact' => $request->input('contact')]);
        //dd('oi');
        return ['status' => 1];
        //return Response::json(['status' => 1]);*/
    }

    public function convert_date($date, $to_brazil){
        if ($to_brazil){
            $modified_date = Carbon::createFromFormat('Y-m-d', $date,-3);
            $modified_date = $modified_date->day.'/'.$modified_date->month.'/'.$modified_date->year;
        }
        else{
            //$modified_date = Carbon::createFromFormat('Y-m-d H:i:s', $date,-3);
            //$modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year.' '.$modified_date->hour.':'.$modified_date->minute.':'.$modified_date->second;
        }
        return $modified_date;
    }

    public function check_cnh(){
            $drivers = UserDriverRepo::all()
                                    ->where('user_id',$this->user->id)
                                    //->where('next_alert', '<', Carbon::now())
                                    ->where('seeing',1);
            foreach($drivers as $driver){
                $now = date("Y-m-d H:i:s");
                $first = Carbon::createFromFormat('Y-m-d H:i:s', $now);
                $second = Carbon::createFromFormat('Y-m-d H:i:s', $driver->next_alert);
                if($first->greaterThan($second))
                    DB::table('user_drivers')->where('id', $driver->id)->update(['seeing' => 0]);
            }
            $drivers = UserDriverRepo::all()
                                    ->where('user_id',$this->user->id)
                                    //->Where('next_alert', '<', Carbon::now())
                                    ->where('seeing',0);
            
            if($drivers->count()>0){
                
                foreach ($drivers as $driver){
                    $cnh_data = array(
                        "driver_id" => $driver->id,
                        "driver_name" => $driver->name,
                        "type" => "",
                        "mensage" => "",
                    );
                    if(!empty($driver->cnh_expire)){
                        if(!$driver->seeing){
                            $cnh_expire = Carbon::createFromFormat('Y-m-d', $driver->cnh_expire,-3);
                            $now = Carbon::now('-3');
                            if(false){
                                $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_log.txt', "a+");
                                fwrite($fp, "\r\n DEBUGER ".json_encode($cnh_expire->diffInDays($now)<30 && $cnh_expire->diffInDays($now)>1)." \r\n"); 
                                fclose($fp);
                            }
                                if ($cnh_expire->diffInDays($now)<30 && $cnh_expire->greaterThan($now) ){
                                    $cnh_data['type'] = 0;
                                    $cnh_data['driver_name'] = $driver->name;
                                    DB::table('user_drivers')->where('id', $driver->id)->update(['pre_alert' => '1']);
                                    return json_encode($cnh_data);
                                }
                                if ($now->greaterThan($cnh_expire)){
                                    $cnh_data['type'] = 1;
                                    $cnh_data['driver_name'] = $driver->name;
                                    DB::table('user_drivers')->where('id', $driver->id)->update(['alert' => '1']);
                                    return json_encode($cnh_data);
                                }
                        }
                    }
                    else{
                        $cnh_data['type'] = 1;
                        $cnh_data['driver_name'] = $driver->name;
                        DB::table('user_drivers')->where('id', $driver->id)->update(['alert' => '1']);
                        return json_encode($cnh_data);
                    }
                }
            }
            return json_encode(false);
    }

    public function interaction($driver_id=null, $type=null, $driver_name=null){
        $driver_name = str_replace("_", " ",$driver_name);
        $text_title = "Problemas na CNH do Motorista ".$driver_name;
        if($type==1)
            $mensagem = "VENCIDA ou com data de vencimento DESCADASTRADA";
        else
            $mensagem = "À VENCER em menos de 30 dias";
        $text_body = "O motorista ".$driver_name." se encontra com a carteira de motorista ".$mensagem.", por favor verifque o cadastro do motorista ou peça-o para renovar a CNH.";
        $questions = false;
        $var_id = $driver_id;
        $route = "user_drivers.interaction_action";
        $interaction_later = "driver_CNH_interaction_later(".$driver_id.")";
        return view('front::Interaction_central.interaction')->with(compact('text_title','text_body', 'route', 'var_id', 'questions', 'driver_id', 'interaction_later'));
    }

    public function interaction_action(Request $request){
        $now = date("Y-m-d H:i:s");
        $next_alert = Carbon::createFromFormat('Y-m-d H:i:s', $now);
        $next_alert->addDays($request->input('deadline'));
        DB::table('user_drivers')->where('id', $request->id)->update(['seeing' => '1', 'next_alert' => $next_alert]);
        return Response::json(['status' => 1]);
    }

    public function interaction_later($driver_id){
        $now = date("Y-m-d H:i:s");
        $next_alert = Carbon::createFromFormat('Y-m-d H:i:s', $now);
        $next_alert->addHours(2);
        DB::table('user_drivers')->where('id', $driver_id)->update(['seeing' => '1', 'next_alert' => $next_alert]);
        //$ocorrency->information = $ocorrency->information."\r\n Cliente solicitou para interagir depois. \r\n ";
	}

}
