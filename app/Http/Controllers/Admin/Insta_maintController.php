<?php

namespace App\Http\Controllers\Admin;

use App;
use App\customer;
use App\Insta_maint;
use App\Monitoring;
use App\Technician;
use App\tracker;
use Carbon\Carbon;
use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Validation\ClientFormValidator;

class Insta_maintController extends BaseController
{
    /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'insta_maint';

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

    public function index($page = 0, $search_item = '')
    {
        $soma_valores = 0;
        $payable = 0;
        $paid = 0;
        $recei_from_cli = 0;
        $money_with_hands = 0;

        /*
        $monitorings = Monitoring::orderby('gps_date', 'asc')
                            ->where('active', true)
                            ->where('sent_maintenance', 1)
                            ->get();
        foreach($monitorings as $occurrency){

            $service = insta_maint::where('occurrency_id',$occurrency->id)->get()->count();
            if ($service==0 ){

                $service_count = insta_maint::all()->count();
                $first = Carbon::now();
                $os_number = $first->month;
                $os_number .= $first->year;
                $os_number .= strval($service_count+1);
                $os_number .= '-'.strval(rand(00, 99));
                $new_service = new insta_maint([
                                'active' => true,
                                'device_id' => $occurrency->device_id,
                                'technician_id' => 3,
                                'expected_date' => '',
                                'city' => 'Capim Grosso',
                                'installation_date' => '',
                                'installation_location' => '',
                                'installation_photo_id' => 0,
                                'maintenance' => true,
                                'type' => 0,
                                'os_number' => $os_number,
                                'obs' => 'Auto-inserção',
                                'occurrency_id' => $occurrency->id
                            ]);
                $new_service->save();
            }
        }
        */
        //FIm da atualização de ocorrências

        $input = Input::all();
        $users = null;
        if (Auth::User()->isManager()) {
            $users = Auth::User()->subusers()->pluck('id', 'id')->all();
            $users[] = Auth::User()->id;
        }

        //Obter as ocorrências para apresentação ou através de pesquisa
        if ($search_item == '') {
            $page = 0;
            $services = insta_maint::where('active', true)
                            ->orderby('active', 'desc')
                            ->orderby('id', 'asc')
                            ->get();

            $valores = [
                'soma_valores' => 0,
                'paid' => 0,
                'payable' => 0,
                'table_payable' => false,
            ];
        } else {
            if (! Str::contains(Str::lower($search_item), 'tec-')) { // Se não tiver Tec- entra neste if
                $page = 0;
                $soma_valores = 0;

                $services = insta_maint::orderby('active', 'desc')
                    ->orderby('id', 'asc')
                    ->orderby('payable', 'asc')
                    ->get()
                    ->filter(function ($service) use ($search_item) {
                        $device = UserRepo::getDevice($this->user->id, $service->device_id);
                        if ($device) {
                            if (Str::contains(Str::lower($device->name), Str::lower($search_item)) || Str::contains(Str::lower($device->object_owner), Str::lower($search_item)) || Str::contains(Str::lower($device->plate_number), Str::lower($search_item)) || Str::contains(Str::lower($service->city), Str::lower($search_item)) || Str::contains(Str::lower($service->os_number), Str::lower($search_item))) {
                                return $service;
                            }
                        }
                    });

                $valores = [
                    'soma_valores' => 0,
                    'paid' => 0,
                    'payable' => 0,
                    'table_payable' => false,
                ];
            } else {
                $page = 0;
                $search_item = Str::substr($search_item, 4, Str::length($search_item));
                $technicians = Technician::where('name', 'LIKE', '%'.Str::lower($search_item).'%')->get();
                if (! $technicians->count() == 0) {
                    foreach ($technicians as $technician) {
                        $services = insta_maint::where('technician_id', $technician->id)
                            ->orderby('active', 'desc')
                            ->orderby('payable', 'asc')
                            ->orderby('id', 'asc')
                            ->get();
                        foreach ($services as $service) {
                            if ($service->active == 0) {
                                $soma_valores = $soma_valores + floatval($service->valor);
                                if ($service->payable == 0) {
                                    $payable = $payable + floatval($service->valor) - floatval($service->payable_value);
                                } else {
                                    $paid = $paid + floatval($service->payable_value);
                                }
                            }
                        }
                        $money_with_hands = $technician->money_with_hands;

                        if (! $payable <= 0) {
                            $payable = $payable - $recei_from_cli;
                        }

                        setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
                        $valores = [
                            'soma_valores' => str_replace('BRL', '', ''.money_format('%i', floatval($soma_valores))),
                            'paid' => str_replace('BRL', '', ''.money_format('%i', floatval($paid))),
                            'payable' => str_replace('BRL', '', ''.money_format('%i', floatval($payable))),
                            'recei_from_cli' => str_replace('BRL', '', ''.money_format('%i', floatval($money_with_hands))),
                            'table_payable' => true,
                        ];
                    }
                    // Technician received from customer
                }
            }
        }
        //dd('Olá3');
        //Add campos de outras tabelas
        //dd($services->table_payable);

        //dd('teste');

        $services = $services->paginate(10, $page);

        foreach ($services as $item) {
            $technician = Technician::find($item->technician_id);
            $item->technician = $technician->name;

            $device = UserRepo::getDevice($this->user->id, $item->device_id);
            if ($device) {
                $item->vehicle_model = $device->device_model;
                $trackers = Tracker::where('imei', $device->imei)->get();
                foreach ($trackers as $tracker) {
                    $item->tracker = $tracker->model;
                }
                setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
                $item->valor = str_replace('BRL', '', ''.money_format('%i', floatval($item->valor)));
                //$item->value = $item->value.',00';

                if (! $item->expected_date == '') {
                    $item->expected_date = $this->convert_date($item->expected_date, false);
                }
                if (! $item->installation_date == '') {
                    $item->installation_date = $this->convert_date($item->installation_date, false);
                }

                if ($device->name == 'ASSOCIAÇÃO LÍDER' || $device->name == 'COOPERATIVA') {
                    $item->contact = $device->contact;
                } else {
                    if ($device->cliente_id == 0) {
                        $customers = customer::where('name', $device->name)->get();
                        foreach ($customers as $customer) {
                            $item->contact = $customer->contact;
                        }
                    } else {
                        $customers = customer::find($device->cliente_id);
                        $item->contact = $customer->contact;
                    }
                }
                $item->customer = $device->name;
                $item->owner = $device->object_owner;
                $item->plate_number = $device->plate_number;
            } else {
                $item->vehicle_model = 'Veículo não encontrado';
                $item->tracker = 'Não encontrado';
                setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
                $item->valor = str_replace('BRL', '', ''.money_format('%i', floatval($item->valor)));
                //$item->value = $item->value.',00';

                if (! $item->expected_date == '') {
                    $item->expected_date = $this->convert_date($item->expected_date, false);
                }
                if (! $item->installation_date == '') {
                    $item->installation_date = $this->convert_date($item->installation_date, false);
                }

                $item->contact = 'Não encontrado';
                $item->customer = 'Não encontrado';
                $item->owner = 'Não encontrado';
                $item->plate_number = 'Não encontrado';
            }
        }

        //dd($services);
        $items = $this->device->searchAndPaginateAdmin($input, 'name', 'asc', 1, $users);

        $section = $this->section;

        $page = $services->currentPage();
        $total_pages = $services->lastPage();
        $pagination = smartPaginate($services->currentPage(), $total_pages);
        //dd($Monitorings->render());
        //dd($services);
        return View::make('admin::'.ucfirst($this->section).'.'.'table')->with(compact('items', 'section', 'services', 'page', 'total_pages', 'pagination', 'valores'));
    }

    public function create()
    {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->pluck('email', 'id')->all();

        $devices = UserRepo::getDevices($this->user->id);
        $technician = Technician::all();

        $service_count = insta_maint::all()->count();
        $first = Carbon::now();
        $os_number = $first->month;
        $os_number .= $first->year;
        $os_number .= strval($service_count + 1);
        $os_number .= '-'.strval(rand(00, 99));
        $os_number = 'Em breve';
        $date_now = $first->year.'-'.$first->month.'-'.$first->day;

        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('managers', 'os_number', 'date_now', 'devices', 'technician'));
    }

    public function edit($id)
    {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->pluck('email', 'id')->all();
        $service = insta_maint::find($id);

        $device = UserRepo::getDevice($this->user->id, $service->device_id);
        $technician = Technician::find($service->technician_id);
        $service->money_with_hands = $technician->money_with_hands;
        $technician = Technician::all();

        $service->customer = $device->name;
        $service->owner = $device->object_owner;
        $service->plate_number = $device->plate_number;
        $service->active = ! $service->active;
        $item = $service;

        $item->name = $device->name;
        $item->device_id = $device->id;
        //dd($item->city);
        $item->address = $item->city;
        if ($device->name == 'ASSOCIAÇÃO LÍDER' || $device->name == 'COOPERATIVA') {
            $item->contact = $device->contact;
            if (empty($item->city)) {
                $item->address = $device->city;
            }
        } else {
            if ($device->cliente_id == 0) {
                $customers = customer::where('name', $device->name)->get();
                foreach ($customers as $customer) {
                    $item->contact = $customer->contact;
                    if (empty($item->city)) {
                        $item->address = $customer->address.' - '.$customer->city;
                    }
                }
            } else {
                $customers = customer::find($device->cliente_id);
                $item->contact = $customer->contact;
                if (empty($item->city)) {
                    $item->address = $customer->address.' - '.$customer->city;
                }
            }
        }

        $devices = UserRepo::getDevicesWith($this->user->id, ['devices']);

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('managers', 'item', 'devices', 'technician'));
    }

    public function store(Request $request)
    {
        /*public function store(Request $request)
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
        $service_count = insta_maint::all()->count();
        $first = Carbon::now();
        //dd($first->year);
        $os_number = $first->month;
        $os_number .= $first->year;
        $os_number .= strval($service_count + 1);
        $os_number .= '-'.strval(rand(00, 99));

        setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
        $valor = str_replace('BRL', '', ''.money_format('%i', floatval($request->input('valor'))));
        $recei_from_cli = str_replace('BRL', '', ''.money_format('%i', floatval($request->input('recei_from_cli'))));
        $technician = Technician::find($request->input('technician_id'));
        $technician->money_with_hands = floatval($technician->money_with_hands) + floatval($recei_from_cli);
        $technician->save();
        if ($request->input('write_off_the_balance')) {
            if (floatval($technician->money_with_hands) + floatval($request->input('amount_paid')) >= floatval($valor)) {
                $amount_paid = $valor;
                $technician->money_with_hands = floatval($technician->money_with_hands) + floatval($request->input('amount_paid')) - floatval($valor);
                $technician->save();
                $payable = true; //$request->input('payable')
            } else {
                $amount_paid = floatval($technician->money_with_hands) + floatval($request->input('amount_paid'));
                $technician->money_with_hands = floatval($technician->money_with_hands) - $amount_paid;
                $technician->save();
                $payable = $request->input('payable');
            }
        } else {
            $payable = $request->input('payable');
            $amount_paid = $request->input('amount_paid');
        }

        $service = new insta_maint([
            'active' => ! $request->input('active'),  //true,
            'device_id' => $request->input('plate_number'),
            'os_number' => $os_number,
            'type' => $request->input('cause'),
            'cause' => $request->input('cause'),
            'technician_id' => $request->input('technician_id'),
            'valor' => $valor,
            'payable_value' => $amount_paid,
            'payable' => $payable,
            'recei_from_cli' => $recei_from_cli,
            'expected_date' => $request->input('expected_date'),
            'installation_date' => $request->input('installation_date'),
            'installation_location' => $request->input('installation_location'),
            'city' => $request->input('city'),
            'obs' => $request->input('obs').' - '.$payable,
            'maintenance' => $request->input('maintenance'),
        ]);
        $service->save();
        if ($request->input('change_locale')) {
            DB::table('devices')->where('id', $request->input('plate_number'))->update(['insta_loc' => $request->input('installation_location')]);
        }
        DB::table('insta_maints')->where('os_number', $os_number)->update(['payable' => $payable]);

        return Response::json(['status' => 1]);
    }

    public function update(Request $request)
    {
        $service = insta_maint::find($request->input('id'));
        $technician = Technician::find($request->input('technician_id'));

        if ($request->input('name') == 'ASSOCIAÇÃO LÍDER' || $request->input('name') == 'COOPERATIVA') {
            DB::table('devices')->where('id', $request->input('device_id'))->update(['contact' => $request->input('contact')]);
        } else {
            if ($request->input('cliente_id') == 0) {
                DB::table('customers')->where('name', $request->input('name'))->update(['contact' => $request->input('contact')]);
            } else {
                DB::table('customers')->where('id', $request->input('cliente_id'))->update(['contact' => $request->input('contact')]);
            }
        }
        setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
        $valor = str_replace('BRL', '', ''.money_format('%i', floatval($request->input('valor'))));
        $recei_from_cli = str_replace('BRL', '', ''.money_format('%i', floatval($request->input('recei_from_cli'))));

        $technician->money_with_hands = floatval($technician->money_with_hands) + (floatval($recei_from_cli) - floatval($service->recei_from_cli));
        $technician->save();
        if ($request->input('write_off_the_balance')) {
            if (floatval($technician->money_with_hands) + floatval($request->input('amount_paid')) >= floatval($valor)) {
                $amount_paid = $valor;
                $technician->money_with_hands = floatval($technician->money_with_hands) + floatval($request->input('amount_paid')) - floatval($valor);
                $technician->save();
                $payable = true; //$request->input('payable')
            } else {
                $amount_paid = floatval($technician->money_with_hands) + floatval($request->input('amount_paid'));
                $technician->money_with_hands = floatval($technician->money_with_hands) - $amount_paid;
                $technician->save();
                $payable = $request->input('payable');
            }
        } else {
            $payable = $request->input('payable');
            $amount_paid = $request->input('amount_paid');
        }

        $service->active = ! $request->input('active');
        $service->device_id = $request->input('plate_number');
        $service->technician_id = $request->input('technician_id');
        $service->valor = $valor;
        $service->payable = $payable;
        $service->payable_value = $amount_paid;
        $service->recei_from_cli = $request->input('recei_from_cli');
        $service->expected_date = $request->input('expected_date');
        $service->city = $request->input('city'); //leia-se city como endereço completo
        $service->installation_date = $request->input('installation_date');
        $service->installation_location = $request->input('installation_location');
        $service->maintenance = $request->input('maintenance');
        $service->type = $request->input('type');
        $service->cause = $request->input('cause');
        $service->obs = $request->input('obs');
        $service->save();
        if ($request->input('change_locale')) {
            DB::table('devices')->where('id', $request->input('plate_number'))->update(['insta_loc' => $request->input('installation_location')]);
        }

        return Response::json(['status' => 1]);
    }

    public function cancel($id)
    {
        $service = insta_maint::find($id);

        return view('admin::'.ucfirst($this->section).'.cancel')->with(compact('service'));
    }

    public function canceled(Request $request)
    {
        $rules = ['id' => 'required|numeric',
            'motivo' => 'required'];
        $this->validate($request, $rules);
        $service = insta_maint::find($request->input('id'));
        if (! $service->payable) {
            $date_now = Carbon::now(-3);
            $service->obs = $service->obs."\r\n OS cancelada, valor R$ ".$service->valor.'. Motivo: '.$request->input('motivo').'. Data de cancelamento:'.$this->convert_date($date_now, true);
            $service->valor = 0;
            $service->active = 0;
            $service->payable_value = 0;
            $service->payable = true;
            $service->save();
        }

        return Response::json(['status' => 1]);
    }

    public function os($id)
    {
        $item = insta_maint::find($id);
        $technician = Technician::find($item->technician_id);
        $item->technician = $technician->name;

        $device = UserRepo::getDevice($this->user->id, $item->device_id);
        $item->vehicle_model = $device->device_model;
        $item->plate_number = $device->plate_number;
        $item->vehicle_color = $device->vehicle_color;
        $item->model_year = $device->model_year;

        $trackers = Tracker::where('imei', $device->imei)->get();
        foreach ($trackers as $tracker) {
            $item->tracker = $tracker->model;
        }
        setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');
        $item->valor = str_replace('BRL', '', ''.money_format('%i', floatval($item->valor)));
        //$item->value = $item->value.',00';

        if (! $item->expected_date == '') {
            $item->expected_date = $this->convert_date($item->expected_date, false);
        }
        if (! $item->installation_date == '') {
            $item->installation_date = $this->convert_date($item->installation_date, false);
        }

        $item->address = $item->city;
        if ($device->name == 'ASSOCIAÇÃO LÍDER' || $device->name == 'COOPERATIVA') {
            $item->contact = $device->contact;
            if (empty($item->city)) {
                $item->address = $device->city;
            }
        } else {
            if ($device->cliente_id == 0) {
                $customers = customer::where('name', $device->name)->get();
                foreach ($customers as $customer) {
                    $item->contact = $customer->contact;
                    if (empty($item->city)) {
                        $item->address = $customer->address.' - '.$customer->city;
                    }
                }
            } else {
                $customers = customer::find($device->cliente_id);
                $item->contact = $customer->contact;
                if (empty($item->city)) {
                    $item->address = $customer->address.' - '.$customer->city;
                }
            }
        }
        $item->customer = $device->name;
        $item->owner = $device->object_owner;
        $item->plate_number = $device->plate_number;
        $item->insta_loc = $device->insta_loc;

        //Tratamento dos dados ##################################
        if (! $item->expected_date) {
            $item->expected_date = 'Sem previsão';
        }

        if (! $item->installation_date) {
            $item->installation_date = 'Sem previsão';
        }

        if ($item->active) {
            $item->active = 'A executar';
        } else {
            $item->active = 'Executado';
        }

        if (! $item->installation_date) {
            $item->installation_date = 'Sem previsão';
        }

        if (! $item->technician) {
            $item->technician = 'Sem informação';
        }

        if ($item->maintenance) {
            $item->maintenance = 'Manutenção';
            $item->maintenance_code = 2;
        } else {
            $item->maintenance = 'Instalação';
            $item->maintenance_code = 1;
        }
        //######################################################

        //dd($item);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML('
            <!DOCTYPE html>
            <html lang="en" class="no-js">

            <head>
                <meta charset="utf-8"/>
                <title>Ordem de Serviço</title>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
            </head>

            <body class="admin-layout">
        <div style="border: 2px solid #000;paddin: 10px; margin: -10px">
        <div class="panel-default h-auto d-inline-block" id="table_'.$item->id.'" style="min-width: 595px; align: center;">
                                    
                                    <div class="w-25 p-3 row" style="display: block; min-height: 100px; min-width: 100%; justify-items: stretch;" >
                                        <div class="col-lg-6" style="height: 100px; float: left; min-width: 50%; width: 50%" align="center">
                                            <span style="min-width:100%; width:100%;font-size: 20px;">ORDEM DE SERVIÇO</span>
                                            <br>
                                            <img src="https://sistema.carseg.com.br/images/logo-main.png" alt="Logo CARSEG" width=280 height=80>
                                        </div>
                                        <div class="col-lg-6" style="border: 1px solid #000;float: right;min-width: 50%; width: 50%; min-height: 100px">
                                            Nº da OS: '.$item->os_number.'<br>
                                            Data Prevista: '.$item->expected_date.' <br>
                                            Data da execução: '.$item->installation_date.' <br>
                                            Status da OS: '.$item->active.'<br>
                                            Técnico: '.$item->technician.'<br>
                                            Causa: '.$item->cause.'
                                        </div>
                                    </div>          
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    
                                    <div class="row col-lg-12" style="margin-left: 2px; margin-right: 2px;margin-top: 20px; width: 100%;">
                                        <div style="min-width: 100%;width: 100%;border-bottom: 1px solid #000" align="center">
                                            <span style="font-size: 20px;" align="center">DADOS DO CLIENTE</span>
                                        </div>
                                        <div style="width: 100%; float: left">
                                            <span style="font-size: 15px;">Nome Completo: '.$item->customer.'</span>
                                        </div>
                                        <br>
                                        <div style="width: 100%; float: left">
                                            <span style="font-size: 15px;">Propretário: '.$item->owner.'</span>
                                        </div>
                                        <br>
                                        <div style="width: 100%; float: left">
                                            <span style="font-size: 15px;">Endereço do serviço: '.$item->address.'</span>
                                        </div>
                                        <br>
                                        <div style="width: 100%; float: left"> 
                                            <div class="col-lg-6">
                                                <span style="font-size: 15px;">Telefone: '.$item->contact.'</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 30px;">
                                        <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                            <span style="font-size: 20px;align: center;">DADOS DO VEÍCULO</span>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-left: 2px; margin-right: 2px; min-width: 100%; width:100%">
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Placa: '.$item->plate_number.'</span>
                                            </div>
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Modelo: '.$item->vehicle_model.'</span>
                                            </div>
                                    </div>
                                    <br>
                                    <div class="row" style="margin-left: 2px; margin-right: 2px;">
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Cor: '.$item->vehicle_color.'</span>
                                            </div>
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Ano: '.$item->model_year.'</span>
                                            </div>
                                            
                                    </div>
                                    <br>
                                    <div class="row" style="margin-left: 2px; margin-right: 2px;">
                                            <div style="width: 100%; float: left">
                                                <span style="font-size: 15px;align: center;">Local Instalação: '.$item->insta_loc.'</span>
                                            </div>
                                    </div>
                                    
                                    <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 40px;">
                                        <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                            <span style="font-size: 20px;align: center;">SERVIÇOS PRESTADOS</span>
                                        </div>
                                        <div style="min-width: 100%; ">
                                            <div class="col-lg-3">
                                                <span style="font-size: 15px;width: 20%; float: left ">Código: '.$item->maintenance_code.'</span>
                                            </div>
                                            <div class="col-lg-6">
                                                <span style="font-size: 15px;width: 50%; float: left ">Descrição: '.$item->maintenance.'</span>
                                            </div>
                                            <div class="col-lg-3">
                                                <span style="font-size: 15px;width: 20%; float: left ">Valor: '.$item->valor.'</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 50px">
                                        <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                            <span style="font-size: 20px;align: center;">PRODUTOS UTILIZADOS</span>
                                        </div>
                                        <div>
                                            <div style="width: 20%; float: left">
                                                <span style="font-size: 15px;">Código:</span>
                                            </div>
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;">Descrição:</span>
                                            </div>
                                            <div style="width: 20%; float: left">
                                                <span style="font-size: 15px;">Quantidade:</span>
                                            </div>
                                            <br>
                                            <div style="width: 20%; float: left">
                                                <span style="font-size: 15px;">Valor Un.:</span>
                                            </div>
                                            <div style="width: 20%; float: left">
                                                <span style="font-size: 15px;">Total:</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="margin-top: 25px;">
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Valor total da OS:</span>
                                            </div>
                                            <div style="width: 50%; float: left">
                                                <span style="font-size: 15px;align: center;">Forma de pagamento: </span>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <br>
                                    <div style="margin-top: 30px;">
                                        <div class="col-lg-12" align="center" style="border-bottom: 1px solid #000;">
                                            <span style="font-size: 20px;">LAUDO TÉCNICO</span>
                                        </div>
                                        <div class="col-lg-6" style="width: 50%; float: left">
                                            <span style="font-size: 15px;">Data:</span>
                                        </div>
                                        <div class="col-lg-6" style="width: 50%; float: left">
                                            <span style="font-size: 15px;">Técnico: '.$item->technician.'</span>
                                        </div>
                                        <br>
                                        <br>
                                        <div class="col-lg-12" style="width: 100%; height: 100px; float: left; border: 1px solid #000;margin-left: 2px; margin-right: 2px">
                                            <span style="font-size: 15px; min-width:100%">Descreva o laudo conforme o serviço prestado</span>
                                        </div>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="row" style="margin-top: 100px; margin-left: 2px; margin-right: 2px">
                                        <div class="col-lg-12">
                                            <span style="font-size: 15px;min-width:100%">DECLARO QUE OS SERVIÇOS DESCRITOS NESTE RELATÓRIO FORAM PRESTADOS E DADOS COMO  ACEITOS POR MIM NESTA DATA ___ / ___ / ______</span>
                                        </div>
                                        <div class="col-lg-6" align="center" style="margin-top: 50px">
                                            <span style="font-size: 15px;" >________________________________________</span>
                                        </div>
                                        <div class="col-lg-6" align="center">
                                            <span style="font-size: 15px;">'.$item->owner.'</span>
                                        </div>
                                        <br>
                                        <div class="col-lg-6" align="center" style="margin-top: 30px">
                                            <span style="font-size: 15px;">________________________________________</span>
                                        </div>
                                        
                                        <div class="col-lg-6" align="center">
                                            <span style="font-size: 15px;">'.$item->technician.'</span>
                                        </div>
                                        <br>
                                    </div>

                                </div>
                                </div>
                                </body>');

        return $pdf->stream();

        //        return $pdf->stream($report_name.'.pdf');
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

                /*catch (\Exception $e) {
                    rollbackTransaction();
                }*/
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
}
