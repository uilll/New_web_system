<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;

use App\Console\ProcessManager;

use Carbon\Carbon;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem;
use Storage;

class CleanPositionsCommand extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'clean:positions';

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
				//CÓDIGO PARA LIMPAR VÁRIOS VEÍCULOS POR UM DIA
		if (1){		
			$devices = DB::connection('traccar_mysql')->table('devices')->select('id')->orderBy('id', 'asc')->get();
			$quant_devices=0;
			$quant=0;
			$ultimo = 0;
			$liberar=false;
			foreach ($devices as $device){
				$quant_devices = $quant_devices +1;	
				$device_id_ = $device->id;           
				$data_ = date("Y-m-d").'%';
				
				$positions = DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('device_time', 'like',$data_)->orderBy('device_time', 'asc')->get();
				if(!is_null($positions)){
					$threshold_time1 = null;
					$threshold_time = null;                           
					foreach($positions as $position){
						if ($position->speed <2){
							$data_time = $position->device_time;
							$year = Str::substr($data_time,0, 4);
							$month = Str::substr($data_time,5, 2);
							$day = Str::substr($data_time,8, 2);
							$hour = Str::substr($data_time,11, 2);
							$minutes = Str::substr($data_time,14, 2);
							$seconds = Str::substr($data_time,17, 2);
							$time1 = Carbon::create($year, $month, $day, $hour, $minutes, $seconds, 'GMT');
							if (is_null($threshold_time1))
								$threshold_time1 = $time1;
							else{
								$diff = $threshold_time1->diffInSeconds($time1);
								if ($diff<1800){
									DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('id', '=', $position->id)->delete();
									$quant = $quant +1;
									$ultimo = $device_id_;
								}
								else
									$threshold_time1 = $time1;
							}
						}
						else{
						   $data_time = $position->device_time;
							$year = Str::substr($data_time,0, 4);
							$month = Str::substr($data_time,5, 2);
							$day = Str::substr($data_time,8, 2);
							$hour = Str::substr($data_time,11, 2);
							$minutes = Str::substr($data_time,14, 2);
							$seconds = Str::substr($data_time,17, 2);
							$time2 = Carbon::create($year, $month, $day, $hour, $minutes, $seconds, 'GMT');
							if (is_null($threshold_time))
								$threshold_time = $time2;
							else{
								$diff = $threshold_time->diffInSeconds($time2);
								if ($diff<120){
									DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('id', '=', $position->id)->delete();
									$quant = $quant +1;
									$ultimo = $device_id_;
								}
								else
									$threshold_time = $time2;
							} 
						}
						$ultimo = $device_id_;
					}	
				}
			}
		}
		$fp = fopen('/var/www/html/releases/20190129073809/public/postions.txt', "a+");
		fwrite($fp, "Posições processadas: ".$quant." \r\n Último dispositivo processado: ".$ultimo."(".$quant_devices.") ".date("Y-m-d")." \r\n");
		fclose($fp);
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
