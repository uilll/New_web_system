<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Console\ProcessManager;
use Facades\Repositories\UserRepo;
use App\Monitoring;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CheckPowercutCommand extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'check:powercut';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';


	public function __construct()
	{
		parent::__construct();
	}
	
	public function fire()
	{
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */	
        $this->processManager = new ProcessManager($this->name, $timeout = 3600, $limit = 1);

        if (!$this->processManager->canProcess())
        {
			
            echo "Cant process \n";
            return false;
        }       
        //CÓDIGO PARA VERIFICAR AS OCORRÊNCIAS DE VEÍCULO COM BATERIA VIOLADA NO MONITORAMENTO
        if(true){
            $events = DB::table('events')            
            ->where('user_id', '=', 3)
            ->whereIn('alert_id', ['104','105','106','107'])
            ->Where('created_at', '>', Carbon::now()->subMinutes(5))
            //->where('deleted', '=',0)
            ->get();

            //Verificar se existe novo evento de bateria violada
            $novoseventos = 0;
            foreach($events as $event){
                //dd($events);
                $response = Monitoring::where('event_id', '=', $event->id)->get()->count();
                if($response == 0){ // Se já existir o evento no monitoramento o sistema não entrarará neste laço
                    if(!DB::table('devices')->where('id', $event->device_id)->count() == 0){//Verifica se veículo já foi deletado
                        $device = UserRepo::getDevice(3, $event->device_id);
                        $response2 = Monitoring::where('device_id', '=', $device->traccar_device_id)->where('active', '=', 1)->get()->count();
                        if ($response2 == 0) {                           
                            if(!$device->no_powercut){
                                //dd($events);
                                $novoseventos = $novoseventos +1;
                                if ((!str_contains(Str::lower($device->name), 'teste')) || (!str_contains(Str::lower($device->name), 'cancelar')) || (!str_contains(Str::lower($device->name), 'pendente'))){
                                        if ($device->active == 1){
                                            //dd($event->created_at);
                                            $Monitoring = new Monitoring([
                                                'active' => true,
                                                'device_id' => $device->traccar_device_id,
                                                'event_id' => $event->id,
                                                'cause' => "Bateria Violada",
                                                'information' => "",//$device->additional_notes,
                                                //'gps_date' => $device->traccar->device_time ? $device->traccar->device_time : $event->created_at, //Possível erro ao carregar
                                                'lat' => $event->latitude,
                                                'lon' => $event->longitude,
                                                'occ_date' => $event->created_at,
                                                //'next_con' => $next_contact,
                                                'make_contact' => false, 
                                                //'treated_occurence' => $request->input('treated_occurence'),
                                                'sent_maintenance' => false,
                                                'automatic_treatment' => false
                                            ]);
                                            
                                            $Monitoring->save();
                                        }
                                }
                            }
                        }
                    }
                }
                    
            } 
            $Monitorings = Monitoring::orderby('make_contact','asc')
            ->where('cause','Bateria Violada')  
            ->where('treated_occurence', 0)
            ->get();
            //dd($Monitorings);
            //Verificar se rastreador já voltou a tensão normal

            foreach($Monitorings as $ocorrency){      
                $device = "";
                $devices_count = DB::table('devices')->where('traccar_device_id', $ocorrency->device_id)->count();
                if($devices_count>0){
                    $devices_ = DB::table('devices')->where('traccar_device_id', $ocorrency->device_id)->get();
                    foreach($devices_ as $device_)
                        $device = $device_;
                    $device = UserRepo::getDevice(3, $device->id);
                    $events = DB::table('events')            
                                    ->where('user_id', '=', 3)
                                    ->where('device_id', '=', $device->id)
                                    ->whereIn('alert_id', ['104','105','106','107'])
                                    //->Where('created_at', '>', Carbon::now()->subMinutes(60))
                                    //->where('deleted', '=',0)
                                    ->get();
                    //dd($events);
                    $ja_tratado =false;
                    foreach ($events as $event){
                        if(!$ja_tratado){
                            $array = (array)simplexml_load_string($device->traccar->other);
                            if($event->alert_id == 104){
                                if(array_key_exists('power',$array)){
                                    if((int)$array['power']>5){
                                        $update_ = Monitoring::find($ocorrency->id);
                                        $update_->active = 0;
                                        $update_->automatic_treatment = 1;
                                        $update_->treated_occurence = 1;
                                        $update_->save();
                                        $ja_tratado = true;
                                        $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                        fwrite($fp, "\r\n Rastreador Concox(tratou o evento) - ".$device->plate_number." ".$ocorrency->id." ".$event->id." \r\n".json_encode($array['power'])); 
                                        fclose($fp);
                                    }
                                }
                                elseif(array_key_exists('batterylevel',$array) && $array['batterylevel']>=99){ 
                                    $update_ = Monitoring::find($ocorrency->id);
                                    $update_->active = 0;
                                    $update_->automatic_treatment = 1;
                                    $update_->treated_occurence = 1;
                                    $update_->save();
                                    $ja_tratado = true;
                                    $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                    fwrite($fp, "\r\n Rastreador Concox(tratou o evento) - ".$device->plate_number." ".$ocorrency->id." ".$event->id." \r\n".json_encode($array['batterylevel'])); 
                                    fclose($fp);
                                }
                                else{
                                    $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                    fwrite($fp, "\r\n Rastreador Concox(não fez nada)  - ".$device->plate_number." ".$ocorrency->id." ".$event->id."\r\n".json_encode($array)); 
                                    fclose($fp);
                                }
                            }
                            
                            if($event->alert_id == 105){
                                if(array_key_exists('power',$array)){
                                    if((int)$array['power']>5){
                                        //dd($array);
                                        $update_ = Monitoring::find($ocorrency->id);
                                        $update_->active = 0;
                                        $update_->automatic_treatment = 1;
                                        $update_->treated_occurence = 1;
                                        $update_->save();
                                        $ja_tratado = true;
                                        $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                        fwrite($fp, "\r\n Rastreador Suntech(tratou o evento)  - ".$device->plate_number."\r\n".json_encode($array['power'])); 
                                        fclose($fp);
                                    }
                                }
                                elseif(array_key_exists('batterylevel',$array) && $array['batterylevel']>=95){
                                        //dd($array);
                                        $update_ = Monitoring::find($ocorrency->id);
                                        $update_->active = 0;
                                        $update_->automatic_treatment = 1;
                                        $update_->treated_occurence = 1;
                                        $update_->save();
                                        $ja_tratado = true;
                                        $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                        fwrite($fp, "\r\n Rastreador Suntech(tratou o evento)  - ".$device->plate_number."\r\n".json_encode($array['batterylevel'])); 
                                        fclose($fp);
                                }
                                else{
                                    $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                    fwrite($fp, "\r\n Rastreador Suntech (Não fez nada)  - ".$device->plate_number."\r\n".json_encode($array)); 
                                    fclose($fp);
                                }    
                            }
                            if($event->alert_id == 106){
                                $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                fwrite($fp, "\r\n Rastreador Maxtrack  - ".$device->plate_number." \r\n".json_encode($array)); 
                                fclose($fp);
                            }
                            if($event->alert_id == 107){
                                $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
                                fwrite($fp, "\r\n Rastreador Yuntrack   - ".$device->plate_number."\r\n".json_encode($array)); 
                                fclose($fp);
                            }
                        }
                    }
                }
                
            }

            $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_powercut_log.txt', "a+");
            fwrite($fp, "\r\n NE:".$novoseventos." ".date("F j, Y, g:i a")); 
            fclose($fp);
        }
	}
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}
}
