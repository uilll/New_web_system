<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use App\Console\ProcessManager;
use App\Monitoring;
use Carbon\Carbon;
use Facades\Repositories\UserRepo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\User\UserRepositoryInterface as User;

class CheckMonitoringsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'check:monitorings';

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

        if (! $this->processManager->canProcess()) {
            echo "Cant process \n";

            return false;
        }

        $date = Carbon::now(-3);
        if ($date->dayOfWeek == 0 || $date->dayOfWeek == 1) {
        } else {
            //CÓDIGO PARA VERIFICAR AS OCORRÊNCIAS DE VEÍCULO PARADO A MAIS DE 24H NO MONITORAMENTO
            $events = DB::table('events')
                        ->where('user_id', '=', 3)
                        ->where('alert_id', '=', '52')
                        ->where('deleted', '=', 0)
                        ->Where('created_at', '>', Carbon::now()->subHour(24))
                        ->get();

            //$device = UserRepo::getDevice($this->user->id, 23);
            //dd("olá2");
            //Atualização de novas ocorrências
            foreach ($events as $event) {
                $response = Monitoring::where('event_id', '=', $event->id)->get()->count();
                if ($response == 0) { // Se já existir o evento no monitoramento o sistema não entrarará neste laço
                    $response2 = Monitoring::where('device_id', '=', $event->device_id)->where('active', '=', 1)->get()->count();
                    if ($response2 == 0) { // Se já existir um veículo dentro do monitoramento de 24 de forma ativa ele não entra nesse laço
                        if (! DB::table('devices')->where('traccar_device_id', $event->device_id)->count() == 0) {
                            $devices_ = DB::table('devices')->where('traccar_device_id', $event->device_id)->get();
                            foreach ($devices_ as $device_) {
                                $device = $device_;
                            }
                            if ($device->active) {
                                //dd("olá");
                                if (! $device == null) {
                                    if (! DB::connection('traccar_mysql')->table('devices')->where('id', 'like', $device->traccar_device_id)->count() == 0) {
                                        $device->traccar = DB::connection('traccar_mysql')->table('devices')->find($device->traccar_device_id);
                                        $data_time = $device->traccar->device_time ? $device->traccar->device_time : $event->created_at;
                                        $year = Str::substr($data_time, 0, 4);
                                        $month = Str::substr($data_time, 5, 2);
                                        $day = Str::substr($data_time, 8, 2);
                                        $first = Carbon::create($year, $month, $day);
                                        $second = Carbon::now();

                                        if ($second->diffInHours($first) > 24) {
                                            if ((! str_contains(Str::lower($device->name), 'teste')) || (! str_contains(Str::lower($device->name), 'cancelar')) || (! str_contains(Str::lower($device->name), 'pendente'))) {
                                                if ($device->active == 1) {
                                                    $Monitoring = new Monitoring;

                                                    $Monitoring->active = true;
                                                    $Monitoring->device_id = $event->device_id;
                                                    $Monitoring->event_id = $event->id;
                                                    $Monitoring->cause = $event->type;
                                                    $Monitoring->information = 'Inserção automática'; //$device->additional_notes,
                                                    $Monitoring->gps_date = $device->traccar->device_time ? $device->traccar->device_time : $event->created_at;
                                                    $Monitoring->lat = $event->latitude ? $event->latitude : $device->traccar->lastValidLatitude;
                                                    $Monitoring->lon = $event->longitude ? $event->longitude : $device->traccar->lastValidLongitude;
                                                    $Monitoring->occ_date = $event->created_at;
                                                    //'next_con' => $next_contact,
                                                    $Monitoring->make_contact = false;
                                                    //'treated_occurence' => $request->input('treated_occurence'),
                                                    $Monitoring->sent_maintenance = false;
                                                    $Monitoring->automatic_treatment = false;

                                                    $Monitoring->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //dd('oi');
        //Tratamento automático de ocorrências
        $Monitorings = Monitoring::where('cause', 'offline_duration')
                        ->where('active', 1)
                        ->where('treated_occurence', 0)
                        ->get();

        foreach ($Monitorings as $item) {
            if (! DB::table('devices')->where('traccar_device_id', $item->device_id)->count() == 0) {
                $devices_ = DB::table('devices')->where('traccar_device_id', $item->device_id)->get();

                foreach ($devices_ as $device_) {
                    $device = $device_;
                    $device->traccar = DB::connection('traccar_mysql')->table('devices')->find($device->traccar_device_id);
                }
                if ($device->active == 0) {
                    $item->active = false;
                    $item->automatic_treatment = true;
                    $item->treated_occurence = true;
                    $item->sent_maintenance = false;
                }

                if ($item->make_contact == 1 && $item->sent_maintenance == 0) {
                    if (! $item->next_con == '') {
                        if (mb_strlen($item->next_con) == 19) {
                            $first = Carbon::parse($item->next_con);
                            $second = Carbon::now('-3');

                            if ($first->lessThanOrEqualTo($second)) {
                                $interaction_later = 0;
                                if ($item->interaction_later == 1) {
                                    $interaction_later = 0;
                                }
                                $item->make_contact = 0;
                                $update_ = Monitoring::where('id', '=', $item->id)->get();
                                $update_ = $update_['0'];
                                $update_['make_contact'] = 0;
                                $update_['interaction_later'] = $interaction_later;
                                $update_->save();
                            }
                        }
                    }
                }

                //$device = UserRepo::getDevice($this->user->id, $item->device_id);
                //!$device==null
                if (str_contains(Str::lower($device->name), 'cancelado')) {
                    $update_ = Monitoring::where('id', '=', $item->id)->get();
                    $update_ = $update_['0'];
                    $update_['active'] = 0;
                    $update_['automatic_treatment'] = 1;
                    $update_['treated_occurence'] = 1;
                    $update_->save();
                }

                $data_time = $device->traccar->device_time ? $device->traccar->device_time : $device->traccar->server_time;
                if (! $data_time == '') {
                    if (mb_strlen($data_time) == 19) {
                        $first = Carbon::parse($data_time);
                    }
                }
                if (! $item->gps_date == '') {
                    if (mb_strlen($item->gps_date) == 19) {
                        $second = Carbon::parse($item->gps_date);
                    }
                }

                if ($first->greaterThan($second) and $item->active = 1) {
                    $item->active = 0;
                    $item->automatic_treatment = 1;
                    $update_ = Monitoring::where('id', '=', $item->id)->get();
                    $update_ = $update_['0'];
                    $update_['active'] = 0;
                    $update_['automatic_treatment'] = 1;
                    $update_['treated_occurence'] = 1;
                    $update_['information'] = 'Veículo voltou a atualizar (inserção automática)';
                    $update_->save();
                }
                $year = Str::substr($item->occ_date, 0, 4);
                $month = Str::substr($item->occ_date, 5, 2);
                $day = Str::substr($item->occ_date, 8, 2);
                $horas = $day = Str::substr($item->occ_date, 11, 2);
                $minutos = $day = Str::substr($item->occ_date, 14, 2);
                $segundos = $day = Str::substr($item->occ_date, 17, 2);
                $item->occ_date = $day.'/'.$month.'/'.$year.' '.$horas.':'.$minutos.':'.$segundos;

                $item->customer = $device->name;
                $item->owner = $device->object_owner;
                $item->plate_number = $device->plate_number;
                if ((str_contains(Str::lower($device->name), 'teste')) || (str_contains(Str::lower($device->name), 'cancelar')) || (str_contains(Str::lower($device->name), 'pendente')) || (str_contains(Str::lower($device->name), 'retirado')) || (str_contains(Str::lower($device->name), 'deletar') || (str_contains(Str::lower($device->name), 'enviados para o trackar') || (str_contains(Str::lower($device->name), 'chip enviado p/ cancelar')) || (str_contains(Str::lower($device->name), 'crx1 - teste')) || (str_contains(Str::lower($device->name), 'uilmo carneiro de oliveira')) || (str_contains(Str::lower($device->name), 'crx1 com problemas gps'))))) {
                    $item->active = false;
                    $item->make_contact = 1;
                    $update_ = Monitoring::where('id', '=', $item->id)->get();
                    $update_ = $update_['0'];
                    $update_['make_contact'] = 1;
                    $update_['active'] = false;
                    $update_->save();
                }
            }
        }
        //dd('oi2');
        $fp = fopen('/var/www/html/releases/20190129073809/public/monitorings_log.txt', 'a+');
        fwrite($fp, "\r\n CHECK MONITORAMENTO ".date('F j, Y, g:i a')." \r\n");
        fclose($fp);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
