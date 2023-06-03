<?php

namespace App\Http\Controllers\Admin;

use App\customer;
//use Illuminate\Http\Request;
use App\Http\Requests\CsvImportRequest;
use App\Monitoring;
use Carbon\Carbon;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Spatie\ArrayToXml\ArrayToXml;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Validation\ClientFormValidator;
use ZipArchive;

class ChipsController extends BaseController
{
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

    public function __construct(ClientFormValidator $clientFormValidator, Device $device, TraccarDevice $traccarDevice, Event $event)
    {
        parent::__construct();
        $this->clientFormValidator = $clientFormValidator;
        $this->device = $device;
        $this->traccarDevice = $traccarDevice;
        $this->event = $event;
    }

    public function index()
    {
        dd(Session::get('last_login_at'), Session::all());

        //teste limpeza de BD
        if (0) {
            $devices = DB::connection('traccar_mysql')->table('devices')->select('id', 'uniqueId')->orderBy('id', 'asc')->get();
            $quant_devices = 0;
            $quant = 0;
            $ultimo = 0;
            $liberar = false;
            $data_ = Carbon::now();
            $data_->subdays(2);
            $data_ = $data_->toDateString();
            foreach ($devices as $device) {
                $quant_devices = $quant_devices + 1;
                //$uniqueID = $device;
                $device_id_ = $device->id;

                for ($hour = 0; $hour < 23; $hour++) {
                    if ($hour < 10) {
                        $data_ = $data_.' 0'.$hour.'%';
                    } else {
                        $data_ = $data_.$hour.'%';
                    }

                    //dd($data_);
                    $positions = DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('device_time', 'like', $data_)->select('id', 'speed', 'device_time')->orderBy('device_time', 'asc')->get();
                    //dd($positions);
                    if (! is_null($positions)) {
                        $threshold_time1 = null;
                        $threshold_time = null;
                        foreach ($positions as $position) {
                            $data_time = $position->device_time;
                            if (validaData($data_time)) {
                                if ($position->speed < 2) {
                                    $year = Str::substr($data_time, 0, 4);
                                    $month = Str::substr($data_time, 5, 2);
                                    $day = Str::substr($data_time, 8, 2);
                                    $hour = Str::substr($data_time, 11, 2);
                                    $minutes = Str::substr($data_time, 14, 2);
                                    $seconds = Str::substr($data_time, 17, 2);
                                    $time1 = Carbon::create($year, $month, $day, $hour, $minutes, $seconds, 'GMT');
                                    if (is_null($threshold_time1)) {
                                        $threshold_time1 = $time1;
                                    } else {
                                        $diff = $threshold_time1->diffInSeconds($time1);
                                        if ($diff < 1800) {
                                            DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('id', '=', $position->id)->delete();
                                            $quant = $quant + 1;
                                            $ultimo = $device_id_;
                                        } else {
                                            $threshold_time1 = $time1;
                                        }
                                    }
                                } else {
                                    $data_time = $position->device_time;
                                    $year = Str::substr($data_time, 0, 4);
                                    $month = Str::substr($data_time, 5, 2);
                                    $day = Str::substr($data_time, 8, 2);
                                    $hour = Str::substr($data_time, 11, 2);
                                    $minutes = Str::substr($data_time, 14, 2);
                                    $seconds = Str::substr($data_time, 17, 2);
                                    $time2 = Carbon::create($year, $month, $day, $hour, $minutes, $seconds, 'GMT');
                                    if (is_null($threshold_time)) {
                                        $threshold_time = $time2;
                                    } else {
                                        $diff = $threshold_time->diffInSeconds($time2);
                                        if ($diff < 120) {
                                            DB::connection('traccar_mysql')->table('positions_'.$device_id_)->where('id', '=', $position->id)->delete();
                                            $quant = $quant + 1;
                                            $ultimo = $device_id_;
                                            $uniqueIds = DB::connection('traccar_mysql')->table('devices')->where('id', '=', $device_id_)->select('uniqueId')->get();
                                            DB::table('trackers')->where('imei', $uniqueIds[0]->uniqueId)->update(['excess_data' => true]);
                                        } else {
                                            $threshold_time = $time2;
                                        }
                                    }
                                }
                            } else {
                                debugar(true, 'Data invalida no monitoramento :'.$data_time.' / no veículo: '.$device_id_.' / posição: '.$position->id);
                            }
                            $ultimo = $device_id_;
                        }
                    }
                }
            }
        }
        //fim teste limpeza de BD

        if (false) {
            $devices = DB::table('devices')->where('registration_number', 'LIKE', '%ST940%')->select('id', 'imei')->get();
            $imeis[] = [];
            $matches2[] = [];

            foreach ($devices as $key => $device) {
                $imeis[] .= $device->imei;
            }
            unset($imeis[0]);
            $imeis = array_values($imeis);
            //dd($imeis);

            $matches3 = [];
            $handle = @fopen('/opt/traccar/logs/tracker-server.log', 'r');
            //fclose($handle);
            if ($handle) {
                while (! feof($handle)) {
                    $buffer = fgets($handle);

                    foreach ($imeis as $key => $imei) {
                        if (strpos($buffer, '53543931303b4c6f636174696f6e3b'.bin2hex($imei)) !== false) {
                            $pos_ini = strpos($buffer, '[TCP] HEX:');
                            $pos_ini_ = $pos_ini + 11;
                            $pos_fin = strpos($buffer, "\n");
                            $matches2 = substr($buffer, $pos_ini_, ($pos_fin - $pos_ini_));
                            $matches2 = hex2bin($matches2);
                            $matches2 = explode(';', $matches2);
                            $device = DB::connection('traccar_mysql')->table('devices')->where('uniqueId', $imei)->select('other')->get();
                            $xml = simplexml_load_string($device[0]->other);
                            $json = json_encode($xml);
                            $array = json_decode($json, true);
                            $array['sat'] = $matches2[23];
                            $array['rssi'] = str_replace("\r", '', $matches2[24]);
                            $array['batterylevel'] = $matches2[11];
                            $xml1 = ArrayToXml::convert($array, 'info');
                            DB::connection('traccar_mysql')->table('devices')->where('uniqueId', $imei)->update(['other' => $xml1]);
                            $device = DB::connection('traccar_mysql')->table('devices')->where('uniqueId', $imei)->select('other')->get();
                            dd($device);
                            break;
                        }
                    }
                }
                fclose($handle);
                //dd($matches2);
            }
        }

        $devices_ = DB::table('devices')->where('traccar_device_id', 300)->get();

        foreach ($devices_ as $device_) {
            $device = $device_;
            $device->traccar = DB::connection('traccar_mysql')->table('devices')->find($device->traccar_device_id);
        }

        return view('admin::Chips.index');
    }

    public function importar()
    {
        //$request->file('file')->storePublicly($folderSrc);
        /*

        */
        dd('olá');
        $devices = UserRepo::getDevices($this->user->id);

        $total_algar = 0;
        $ativos_algar = 0;
        $suspender_algar = 0;
        $contatem1 = 0;
        $imeis_ativos_m2data = [];

        $total_vivo = 0;
        $ativos_vivo = 0;
        $suspender_vivo = 0;
        $contatem2 = 0;
        $imeis_vivo = [];
        //dd($devices->count());

        foreach ($chips_algar as $chips) {
            $total_algar = $total_algar + 1;
            if (array_key_exists('4', $chips) && $chips['4'] == 'Ativo') {
                $contatem1 = $contatem1 + 1;
            }
        }
        foreach ($chips_vivo as $chips) {
            $total_vivo = $total_vivo + 1;
            if (array_key_exists('3', $chips) && $chips['3'] == 'ACTIVATED') {
                $contatem2 = $contatem2 + 1;
            }
        }

        $chips_cancelar_m2data = $chips_algar;
        $chips_cancelar_vivo = $chips_vivo;

        $fp = fopen('/var/www/html/releases/20190129073809/public/cancelar_chips_m2data.csv', 'a+');
        fwrite($fp, "ICCID;IMEI\r\n");
        fclose($fp);
        $fp = fopen('/var/www/html/releases/20190129073809/public/cancelar_chips_vivo.csv', 'a+');
        fwrite($fp, "ICCID;IMEI\r\n");
        fclose($fp);
        foreach ($devices as $device) {
            $flag_algar = false;
            if ($device['active'] == true) {
                foreach ($chips_algar as $key => $chips) {
                    if (array_key_exists('4', $chips) && $chips['4'] == 'Ativo') {
                        if (Str::contains($chips['2'], $device->imei)) {
                            $ativos_algar = $ativos_algar + 1;
                            $flag_algar = true;
                            if (array_key_exists($key, $chips_cancelar_m2data)) {
                                unset($chips_cancelar_m2data[$key]);
                            }
                        } else {
                        }
                    } else {
                        if (array_key_exists($key, $chips_cancelar_m2data)) {
                            unset($chips_cancelar_m2data[$key]);
                        }
                    }
                }
                if (! $flag_algar) {
                    $suspender_algar = $suspender_algar + 1;
                }
                foreach ($chips_vivo as $key => $chips) {
                    if (array_key_exists('3', $chips) && $chips['3'] == 'ACTIVATED') {
                        if (Str::contains($chips['2'], $device->imei)) {
                            if (! $flag_algar) {
                                $ativos_vivo = $ativos_vivo + 1;
                                if (array_key_exists($key, $chips_cancelar_vivo)) {
                                    unset($chips_cancelar_vivo[$key]);
                                }
                            } else {
                                $suspender_vivo = $suspender_vivo + 1;
                            }
                        }
                    } else {
                        if (array_key_exists($key, $chips_cancelar_vivo)) {
                            unset($chips_cancelar_vivo[$key]);
                        }
                    }
                }
            }
        }

        foreach ($chips_cancelar_m2data as $chip) {
            $fp = fopen('/var/www/html/releases/20190129073809/public/cancelar_chips_m2data.csv', 'a+');
            fwrite($fp, $chip['1'].';'.$chip['2']."\r\n");
            fclose($fp);
        }
        foreach ($chips_cancelar_vivo as $chip) {
            $fp = fopen('/var/www/html/releases/20190129073809/public/cancelar_chips_vivo.csv', 'a+');
            fwrite($fp, $chip['0'].';'.$chip['2']."\r\n");
            fclose($fp);
        }

        dd('Total de chips M2DATA: '.$total_algar, 'Chips Ativos M2DATA: '.$contatem1, 'Chips M2DATA no sistema: '.$ativos_algar, 'Quant. para verificar/cancelar/suspender (M2DATA): '.($contatem1 - $ativos_algar), 'Total de chips Vivo: '.$total_vivo, 'Chips Ativos Vivo: '.$contatem2, 'Chips VIVO na plataforma: '.$ativos_vivo, 'Chips VIVO para cancelar: '.($contatem2 - $ativos_vivo), 'TOTAL DE VEÍCULOS COM CHIPS ATIVOS: '.($ativos_vivo + $ativos_algar), 'TOTAL DE CHIPS PARA CANCELAR/INSPECIONAR: '.($contatem1 + $contatem2 - ($ativos_algar + $ativos_vivo)), $chips_cancelar_m2data, $chips_cancelar_vivo);

        //return View::make('admin::'.ucfirst($this->section).'.import');
    }

    public function importar_csv(CsvImportRequest $request)
    {
    }

    public function create()
    {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->pluck('email', 'id')->all();
        $devices = UserRepo::getDevices($this->user->id);
        $Monitorings = Monitoring::all();
        $devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) {
            return $devices_->traccar_device_id == 832;
        });

        foreach ($devices as $item) {
            $device = array_get($item, 'updated_at');
            $device = $device->toArray();
            $device = array_get($device, 'formatted');
            $device = $item;
        }

        $devices = UserRepo::getDevices($this->user->id);

        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('managers', 'Monitorings', 'devices', 'device'));
    }

    public function edit($id)
    {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->pluck('email', 'id')->all();
        $Monitoring = Monitoring::where('id', $id)->get();
        $Monitoring = $Monitoring->toArray();
        $devices_ = DB::table('devices')->where('traccar_device_id', $Monitoring[0]['device_id'])->get();
        foreach ($devices_ as $device_) {
            $device = UserRepo::getDevice($this->user->id, $device_->id);
        }
        if (empty($Monitoring[0]['information'])) {
            $item = $Monitoring[0];
            $item['additional_notes'] = $device->additional_notes;
            $item['information'] = '';
        } else {
            $item = $Monitoring[0];
            $item['additional_notes'] = $device->additional_notes;
        }
        //Pegar contato em outras tabelas
        $item['name'] = $device->name;
        $item['device_id'] = $device->id;
        if ($device->name == 'ASSOCIAÇÃO LÍDER' || $device->name == 'COOPERATIVA') {
            $item['contact'] = $device->contact;
        } else {
            if ($device->cliente_id == 0) {
                $customers = customer::where('name', $device->name)->get();
                foreach ($customers as $customer) {
                    $item['contact'] = $customer->contact;
                }
            } else {
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
            'information' => json_encode($gps_date), //$request->input('information'),
            //'gps_date' => $gps_date,
            'occ_date' => $request->input('occorunce_date'),
            'next_con' => $request->input('next_contact'),
            'make_contact' => $request->input('make_contact'),
            'treated_occurence' => $request->input('treated_occurence'),
            'sent_maintenance' => $request->input('sent_maintenance'),
            'automatic_treatment' => false,
            'customer' => $customer,
            'owner' => $owner,

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
            $devices = UserRepo::getDevices($this->user->id)->filter(function ($devices_) use ($device_id) {
                return $devices_->traccar_device_id == $device_id;
            });
            foreach ($devices as $item) {
                $plate_number = array_get($item, 'plate_number');
                $owner = array_get($item, 'object_owner');
                $customer = array_get($item, 'name');

                $gps_date = array_get($item, 'traccar');
                $gps_date = array_get($gps_date, 'device_time');
            }
            $occorunce_date = (string) $request->input('occorunce_date');
            $occorunce_date = Carbon::createFromFormat('Y-m-d', $occorunce_date, -3); //->toDateTimeString();
            $dayOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

            $occorunce_date = $dayOfWeek[$occorunce_date->dayOfWeek].', '.$occorunce_date->day.'-'.$occorunce_date->month.'-'.$occorunce_date->year.' '.$occorunce_date->hour.':'.$occorunce_date->minute.':'.$occorunce_date->second;

            $next_contact = $request->input('next_contact');
            $next_contact = Carbon::createFromFormat('Y-m-d', $next_contact, -3);
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
                'owner' => $owner,

            ]);
            $Monitoring->save();

            return Response::json(['status' => 1]);
        }
    }

    public function update(Request $request)
    {
        $rules = ['id' => 'required|numeric',
            'cause' => 'required',
            'information' => 'required',
            'next_con' => 'required',
            'contact' => 'required'];
        $this->validate($request, $rules);

        $Monitoring = Monitoring::find($request->input('id'));
        if ($request->input('treated_occurence') == 1) {
            $Monitoring->active = 0;
        } else {
            $Monitoring->active = $request->input('active');
        }
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

        if ($request->input('name') == 'ASSOCIAÇÃO LÍDER' || $request->input('name') == 'COOPERATIVA') {
            DB::table('devices')->where('id', $request->input('device_id'))->update(['contact' => $request->input('contact')]);
        } else {
            if ($request->input('cliente_id') == 0) {
                DB::table('customers')->where('name', $request->input('name'))->update(['contact' => $request->input('contact')]);
            } else {
                DB::table('customers')->where('id', $request->input('cliente_id'))->update(['contact' => $request->input('contact')]);
            }
        }

        return Response::json(['status' => 1]);
    }

    public function destroy(Request $request)
    {
        if (config('tobuli.object_delete_pass') && Auth::user()->isAdmin() && request('password') != config('tobuli.object_delete_pass')) {
            return ['status' => 0, 'errors' => ['message' => trans('front.login_failed')]];
        }

        $ids = $request->input('ids');

        if (is_array($ids) && count($ids)) {
            foreach ($ids as $id) {
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

    public function rem_add_alert($id)
    {
        $device = UserRepo::getDevice($this->user->id, $id);
        //dd($device);
        DB::table('devices')->where('id', $id)->update(['no_powercut' => ! $device->no_powercut]);
        if ($device->protocol == 'gt06') {
            $alert_protocol = 104;
        } elseif ($device->protocol == 'suntech') {
            $alert_protocol = 105;
        } elseif ($device->protocol == 'mxt') {
            $alert_protocol = 106;
        } else {
            $alert_protocol = 107;
        }
        $device = UserRepo::getDevice($this->user->id, $id);
        if ($device->no_powercut) {
            DB::table('alert_device')->where(['alert_id' => $alert_protocol, 'device_id' => $device->id])->delete();
        } else {
            DB::table('alert_device')->insert(['alert_id' => $alert_protocol, 'device_id' => $device->id]);
        }

        return View::make('admin::'.ucfirst($this->section).'.no_powercut')->with(compact('device'));
    }

    public function validaData($date, $format = 'Y-m-d H:i:s')
    {
        if (! empty($date) && $v_date = date_create_from_format($format, $date)) {
            $v_date = date_format($v_date, $format);

            return $v_date && $v_date == $date;
        }

        return false;
    }

    public function convert_date($date, $full_date)
    {
        $dayOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        if (! $full_date == true) {
            $modified_date = Carbon::createFromFormat('Y-m-d', $date, -3);
            $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year;
        } else {
            $modified_date = Carbon::createFromFormat('Y-m-d H:i:s', $date, -3);
            $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year.' '.$modified_date->hour.':'.$modified_date->minute.':'.$modified_date->second;
        }

        return $modified_date;
    }

    public function upload(Request $request)
    {
        $file = Request::file('userfile');
        $file->move('/var/www/html/releases/20190129073809/public/chips', 'chips.csv');
        $handle = fopen('/var/www/html/releases/20190129073809/public/chips/chips.csv', 'r');
        $data = fgetcsv($handle);
        $chips[] = [];
        $chips_algar[] = [];
        $devices = UserRepo::getDevices($this->user->id);
        $chips_encontrados[] = [];
        $chips_para_cancelar[] = [];
        $chips_veiculos_desativados[] = [];
        $chips_sem_realacao[] = [];
        $chips_ativos = 0;

        if ($data[0] == '#;Operadora;Usuario;Simcard;Mensalidade;Data Ativacao;IP;ICCID;Saldo;Consumo;Upload;Download;Ultima Conexao;Atacado;Contrato;IMEI Remoto;Operadora;Substatus;Status') {
            $chips_algar[0] = explode(';', $data[0]);
            $i = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $chips_algar[$i] = $data;
                $i = $i + 1;
            }

            for ($i = 1; $i < count($chips_algar); $i++) {
                $chips_algar[$i] = explode(';', $chips_algar[$i][0]);
            }

            for ($i = 1; $i < count($chips_algar); $i++) {
                $chips[$i][0] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_algar[$i][3]);
                $chips[$i][1] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_algar[$i][15]);
                $chips[$i][2] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_algar[$i][7]);
                //$chips_algar[$i][15] = preg_replace("/[^a-zA-Z0-9]+/", "", $chips_algar[$i][15]);
            }
        } else {//if(true){
            if ($data[0] = 'id;icc;imsi;msisdn;alias;customField_1;customField_2;customField_3;customField_4;simModel;simType;simProfile;gprsStatus_status;gprsStatus_lastConnStart;gprsStatus_lastConnStop;ipStatus_status;ipStatus_lastConnStart;ipStatus_lastConnStop;advancedSupervision;consumptionDaily_voice_limit;consumptionDaily_voice_value;consumptionDaily_voice_thrReached;consumptionDaily_voice_enabled;consumptionDaily_sms_limit;consumptionDaily_sms_value;consumptionDaily_sms_thrReached;consumptionDaily_sms_enabled;consumptionDaily_data_limit;consumptionDaily_data_value;consumptionDaily_data_thrReached;consumptionDaily_data_enabled;consumptionMonthly_voice_limit;consumptionMonthly_voice_value;consumptionMonthly_voice_thrReached;consumptionMonthly_voice_enabled;consumptionMonthly_sms_limit;consumptionMonthly_sms_value;consumptionMonthly_sms_thrReached;consumptionMonthly_sms_enabled;consumptionMonthly_data_limit;consumptionMonthly_data_value;consumptionMonthly_data_thrReached;consumptionMonthly_data_enabled;expenseMonthly_voiceOver_limit;expenseMonthly_voiceOver_value;expenseMonthly_voiceOver_thrReached;expenseMonthly_voiceOver_enabled;expenseMonthly_smsOver_limit;expenseMonthly_smsOver_value;expenseMonthly_smsOver_thrReached;expenseMonthly_smsOver_enabled;expenseMonthly_dataOver_limit;expenseMonthly_dataOver_value;expenseMonthly_dataOver_thrReached;expenseMonthly_dataOver_enabled;expenseMonthly_totalOver_limit;expenseMonthly_totalOver_value;expenseMonthly_totalOver_thrReached;expenseMonthly_totalOver_enabled;expenseMonthly_voiceFee;expenseMonthly_smsFee;expenseMonthly_dataFee;expenseMonthly_totalFee;expenseMonthly_other;expenseMonthly_total;expenseMonthly_voiceGroupWarning;expenseMonthly_smsGroupWarning;expenseMonthly_dataGroupWarning;expenseMonthly_totalGroupWarning;provisionDate;shippingDate;activationDate;commercialGroup;supervisionGroup;imei;imeiChangeDate;currentApn;currentIp;apn_apn1;apn_apn2;apn_apn3;apn_apn4;apn_apn5;apn_apn6;apn_apn7;apn_apn8;apn_apn9;apn_apn10;staticIpApnIndex;staticIpAddress;manufacturerOrderNumber;extraOrderNumber;servicePack_id;servicePack_name;restrictions_voiceMoHome;restrictions_voiceMoRoaming;restrictions_voiceMoInternational;restrictions_voiceMtHome;restrictions_voiceMtRoaming;restrictions_smsMoHome;restrictions_smsMoRoaming;restrictions_smsMoInternational;restrictions_smsMtHome;restrictions_smsMtRoaming;restrictions_dataHome;restrictions_dataRoaming;supplementaryServices_location;supplementaryServices_devicemgt;supplementaryServices_dim;supplementaryServices_vpn;supplementaryServices_AdvancedSupervision;locationAuto_latitude;locationAuto_longitude;locationManual_latitude;locationManual_longitude;postalCode;customerId;customerName;endCustomerId;endCustomerName;communicationModuleModel;communicationModuleManufacturer;lifeCycleStatus;region;alarms_expense_criticalCount;alarms_expense_urgentCount;alarms_expense_informativeCount;alarms_administrative_criticalCount;alarms_administrative_urgentCount;alarms_administrative_informativeCount;alarms_supervision_criticalCount;alarms_supervision_urgentCount;alarms_supervision_informativeCount;sgsnIp;ggsnIp;country;operator;endDateOfCommitment;mbtPenalty;agpPenalty;lastAccessTechnologyDetected;lastStateChangeDate;lastTrafficDate;blockReason_1;blockReason_2;blockReason_3;staticIpAddress1;staticIpAddress2;staticIpAddress3;staticIpAddress4;staticIpAddress5;staticIpAddress6;staticIpAddress7;staticIpAddress8;staticIpAddress9;staticIpAddress10;billingAccountCrm_Id;currentAdditionalIp;additionalStaticIpAddress1;additionalStaticIpAddress2;additionalStaticIpAddress3;additionalStaticIpAddress4;additionalStaticIpAddress5;additionalStaticIpAddress6;additionalStaticIpAddress7;additionalStaticIpAddress8;additionalStaticIpAddress9;additionalStaticIpAddress10;suspensionNextDate;batch;whitechipActivationDate') {
                $chips_vivo[0] = explode(';', $data[0]);
                $i = 1;
                while (($data = fgetcsv($handle)) !== false) {
                    $chips_vivo[$i] = $data;
                    $i = $i + 1;
                }
                //dd($chips_vivo);
                for ($i = 1; $i < count($chips_vivo); $i++) {
                    $chips_vivo[$i] = explode(';', $chips_vivo[$i][0]);
                }

                for ($i = 1; $i < count($chips_vivo); $i++) {
                    $chips[$i][0] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_vivo[$i][3]); //msisdn
                    $chips[$i][1] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_vivo[$i][74]); //imei
                    $chips[$i][2] = preg_replace('/[^a-zA-Z0-9]+/', '', $chips_vivo[$i][1]); //icc
                }
            }

            if (false) {
                $operadora = 'vivo';

                $uploaddir = '/var/www/html/releases/20190129073809/public/chips';

                $csv = array_map('str_getcsv', file($uploaddir.'/chips.txt'));

                if ($operadora == 'vivo') {
                    for ($i = 1; $i < count($csv); $i++) {
                        $csv[$i][0] = explode(';', $csv[$i][0]);
                    }
                }
                //dd($csv);
                //$devices = UserRepo::getDevices($this->user->id);

                $chips_encontrados[] = [];
                $chips_veiculos_desativados[] = [];
                $chips_sem_realacao[] = [];
                $chips_ativos = 0;
                //dd($chips_encontrados);

                foreach ($devices as $device) {
                    if ($device['active'] != true) {
                        for ($i = 1; $i < count($csv); $i++) {
                            if (str_contains($csv[$i][0][74], $device->imei)) {
                                $chips_encontrados[$i][0] = $csv[$i][0][74];
                                $chips_encontrados[$i][1] = $csv[$i][0][1];
                                //break;
                            }
                        }
                    } else {
                        for ($i = 1; $i < count($csv); $i++) {
                            if (str_contains($csv[$i][0][74], $device->imei)) {
                                $chips_ativos++;
                                $chips_veiculos_desativados[$i][0] = $csv[$i][0][74];
                                $chips_veiculos_desativados[$i][1] = $csv[$i][0][1];
                            }
                        }
                    }
                }
                unset($chips_encontrados[0]);
                $chips_encontrados = array_values($chips_encontrados);
                unset($chips_veiculos_desativados[0]);
                $chips_veiculos_desativados = array_values($chips_veiculos_desativados);

                for ($i = 1; $i < count($csv); $i++) {
                    $encontrado = false;
                    for ($j = 0; $j < count($chips_encontrados); $j++) {
                        if (str_contains($csv[$i][0][74], $chips_encontrados[$j][0])) {
                            $encontrado = true;
                        }
                    }
                    for ($j = 0; $j < count($chips_veiculos_desativados); $j++) {
                        if (str_contains($csv[$i][0][74], $chips_veiculos_desativados[$j][0])) {
                            $encontrado = true;
                        }
                    }
                    if (! $encontrado) {
                        $chips_sem_relacao[$i] = $csv[$i][0][74];
                    }
                }
                $chips_sem_relacao = array_values($chips_sem_relacao);

                $fp = fopen('/var/www/html/releases/20190129073809/public/chips/resumo.txt', 'a+');
                fwrite($fp, "\r\n Total de chips da vivo: ".count($csv)." \r\n");
                fwrite($fp, "\r\n Total de chips  em veículos desativados: ".count($chips_encontrados)." \r\n");
                fwrite($fp, "\r\n Total de chips em veículos ativos: ".$chips_ativos." \r\n");
                fwrite($fp, "\r\n Total de chips sem relação alguma: ".(count($csv) - count($chips_encontrados) - $chips_ativos)." \r\n");
                fclose($fp);

                $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_encontrados.txt', 'a+');
                fwrite($fp, "imei;iccid \r\n");
                foreach ($chips_encontrados as $chip) {
                    fwrite($fp, $chip[0].';'.$chip[1]."\r\n");
                }
                fclose($fp);

                $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_veiculos_desativados.txt', 'a+');
                fwrite($fp, "imei;iccid \r\n");
                foreach ($chips_veiculos_desativados as $chip) {
                    fwrite($fp, $chip[0].';'.$chip[1]." \r\n");
                }
                fclose($fp);

                $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_sem_relacao.txt', 'a+');
                fwrite($fp, "imei;iccid \r\n");
                foreach ($chips_sem_relacao as $chip) {
                    fwrite($fp, $chip);
                    fwrite($fp, " \r\n");
                }
                fclose($fp);

                dd('Total de chips da vivo: '.count($csv), 'Total de chips  em veículos desativados: '.count($chips_encontrados), 'Total de chips em veículos ativos: '.$chips_ativos, 'Total de chips sem relação alguma: '.(count($csv) - count($chips_encontrados) - $chips_ativos), $chips_encontrados, $chips_veiculos_desativados, $chips_sem_relacao);
            }
        }
        //print_r($chips);
        //print_r(count($devices)."\n");
        $j = 0;
        foreach ($devices as $device) {
            $j = $j + 1;
            //print_r($j."\n");
            if ($device['active'] == true) {
                for ($i = 1; $i < count($chips); $i++) {
                    $imei_6 = substr($chips[$i][1], -7, 6);

                    if (Str::contains($device->imei, $imei_6)) {
                        if (str_contains($device->name, 'CANCELAR')) {
                            $chips_para_cancelar[$i] = $chips[$i];
                            $chips_para_cancelar[$i][3] = $device->imei;
                            $chips_para_cancelar[$i][4] = $device->name;
                        //break;
                        } else {
                            $chips_encontrados[$i] = $chips[$i];
                        }
                    }
                }
            } else {
                for ($i = 1; $i < count($chips); $i++) {
                    if (str_contains($chips[$i][1], $device->imei)) {
                        $chips_ativos++;
                        $chips_veiculos_desativados[$i][0] = $chips[$i][0];
                        $chips_veiculos_desativados[$i][1] = $chips[$i][1];
                        $chips_veiculos_desativados[$i][2] = $chips[$i][2];
                        $chips_veiculos_desativados[$i][3] = $device->imei;
                        $chips_veiculos_desativados[$i][4] = $device->name;
                        //break;
                    }
                }
            }
        }
        unset($chips_encontrados[0]);
        $chips_encontrados = array_values($chips_encontrados);
        unset($chips_veiculos_desativados[0]);
        $chips_veiculos_desativados = array_values($chips_veiculos_desativados);
        unset($chips_para_cancelar[0]);
        $chips_para_cancelar = array_values($chips_para_cancelar);

        $cabecalho = ['simcard(msisdn)', 'imei', 'iccid(icc)', 'serial', 'nome'];
        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_para_cancelar.csv', 'w');
        fputcsv($fp, $cabecalho);
        foreach ($chips_para_cancelar as $linha) {
            fputcsv($fp, $linha);
        }
        fclose($fp);
        //$file = file_get_contents('/var/www/html/releases/20190129073809/public/chips/chips_para_cancelar.csv');
        //print_r($file);

        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_veiculos_desativados.csv', 'w');
        fputcsv($fp, $cabecalho);
        foreach ($chips_veiculos_desativados as $linha) {
            fputcsv($fp, $linha);
        }
        fclose($fp);

        //Create an object from the ZipArchive class.
        $zipArchive = new ZipArchive();
        //The full path to where we want to save the zip file.
        $zipFilePath = '/var/www/html/releases/20190129073809/public/chips/test.zip';
        //Call the open function.
        $status = $zipArchive->open($zipFilePath, ZipArchive::CREATE);
        //An array of files that we want to add to our zip archive.
        //You should list the full path to each file.
        $filesToAdd = [
            '/var/www/html/releases/20190129073809/public/chips/chips_para_cancelar.csv',
            '/var/www/html/releases/20190129073809/public/chips/chips_veiculos_desativados.csv',
        ];
        //Add our files to the archive by using the addFile function.
        foreach ($filesToAdd as $fileToAdd) {
            //Add the file in question using the addFile function.
            $zipArchive->addFile($fileToAdd);
        }
        //Finally, close the active archive.
        $zipArchive->close();

        $output = shell_exec('zip /var/www/html/releases/20190129073809/public/chips/chips.zip /var/www/html/releases/20190129073809/public/chips/*.csv');

        /*
        $zip = new ZipArchive;
        $zip->open('/var/www/html/releases/20190129073809/public/chips/test.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $filePath = "/var/www/html/releases/20190129073809/public/chips/chips_para_cancelar.csv";
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $zip->addFile($filePath, $relativePath);
        //$zip->addFile('/var/www/html/releases/20190129073809/public/chips/chips_para_cancelar.csv', '/var/www/html/releases/20190129073809/public/chips/chips_veiculos_desativados.csv');
        $zip->close();*/

        return response()->download('/var/www/html/releases/20190129073809/public/chips/chips.zip');

        dd($chips_veiculos_desativados, $chips_para_cancelar);

        for ($i = 1; $i < count($csv); $i++) {
            $encontrado = false;
            for ($j = 0; $j < count($chips_encontrados); $j++) {
                if (str_contains($csv[$i][0][74], $chips_encontrados[$j][0])) {
                    $encontrado = true;
                }
            }
            for ($j = 0; $j < count($chips_veiculos_desativados); $j++) {
                if (str_contains($csv[$i][0][74], $chips_veiculos_desativados[$j][0])) {
                    $encontrado = true;
                }
            }
            if (! $encontrado) {
                $chips_sem_relacao[$i] = $csv[$i][0][74];
            }
        }
        $chips_sem_relacao = array_values($chips_sem_relacao);

        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/resumo.txt', 'a+');
        fwrite($fp, "\r\n Total de chips da vivo: ".count($csv)." \r\n");
        fwrite($fp, "\r\n Total de chips  em veículos desativados: ".count($chips_encontrados)." \r\n");
        fwrite($fp, "\r\n Total de chips em veículos ativos: ".$chips_ativos." \r\n");
        fwrite($fp, "\r\n Total de chips sem relação alguma: ".(count($csv) - count($chips_encontrados) - $chips_ativos)." \r\n");
        fclose($fp);

        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_encontrados.txt', 'a+');
        fwrite($fp, "imei;iccid \r\n");
        foreach ($chips_encontrados as $chip) {
            fwrite($fp, $chip[0].';'.$chip[1]."\r\n");
        }
        fclose($fp);

        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_veiculos_desativados.txt', 'a+');
        fwrite($fp, "imei;iccid \r\n");
        foreach ($chips_veiculos_desativados as $chip) {
            fwrite($fp, $chip[0].';'.$chip[1]." \r\n");
        }
        fclose($fp);

        $fp = fopen('/var/www/html/releases/20190129073809/public/chips/chips_sem_relacao.txt', 'a+');
        fwrite($fp, "imei;iccid \r\n");
        foreach ($chips_sem_relacao as $chip) {
            fwrite($fp, $chip);
            fwrite($fp, " \r\n");
        }
        fclose($fp);

        dd('Total de chips da vivo: '.count($csv), 'Total de chips  em veículos desativados: '.count($chips_encontrados), 'Total de chips em veículos ativos: '.$chips_ativos, 'Total de chips sem relação alguma: '.(count($csv) - count($chips_encontrados) - $chips_ativos), $chips_encontrados, $chips_veiculos_desativados, $chips_sem_relacao);
    }
}
