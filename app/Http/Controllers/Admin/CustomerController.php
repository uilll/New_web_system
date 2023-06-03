<?php

namespace App\Http\Controllers\Admin;

use App\customer;
use App\tracker;
use Carbon\Carbon;
use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Repositories\User\UserRepositoryInterface as User;
use Tobuli\Validation\ClientFormValidator;

class CustomerController extends BaseController
{
    /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'customers';

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
        //Atualização de rastreadores
        /*
        $devices = UserRepo::getDevices($this->user->id);
        //= DB::table('devices')->where('registration_number', 'like','%st%')->where('active', 1)->get();

        $i=0;
        $name = '';
        $cpf_cnpj = '';
        $address = '';
        $city = '';
        $contact = '';
        $users_passwords = '';

        foreach ($devices as $device){
            $i = $i +1;
            $name = $device->name;
            $exist = customer::where('name', $name)->count();
            if($exist==0){
                $cpf_cnpj = $device->vin;
                if (str_contains(Str::lower($device->additional_notes), 'endereço:')){
                        $pos = strripos(Str::lower($device->additional_notes), 'endereço:');
                        $address = substr($device->additional_notes, $pos+10, 70);
                        //dd($address);
                }
                if (str_contains(Str::lower($device->additional_notes), 'cidade:')){
                        $pos = strripos(Str::lower($device->additional_notes), 'cidade:');
                        $city = substr($device->additional_notes, $pos+8, 24);
                        //dd($city);
                }
                if (str_contains(Str::lower($device->additional_notes), 'contato:')){
                        $pos = strripos(Str::lower($device->additional_notes), 'contato:');
                        $contact = substr($device->additional_notes, $pos+8, 70);
                        //dd($contact);
                }
                if (!str_contains(Str::lower($device->additional_notes), 'contato:') && (str_contains(Str::lower($device->additional_notes), 'cel:') || str_contains(Str::lower($device->additional_notes), 'tel:'))){

                    if (str_contains(Str::lower($device->additional_notes), 'cel:') && str_contains(Str::lower($device->additional_notes), 'tel:')){
                        //dd('Olá');
                        $pos_cel = strripos(Str::lower($device->additional_notes), 'cel:');
                        $pos_tel = strripos(Str::lower($device->additional_notes), 'tel:');
                        if ($pos_cel>$pos_tel)
                            $pos_init = $pos_tel;
                        else
                            $pos_init = $pos_cel;

                    }

                    if (!str_contains(Str::lower($device->additional_notes), 'cel:') && str_contains(Str::lower($device->additional_notes), 'tel:')){
                        $pos_tel = strripos(Str::lower($device->additional_notes), 'tel:');
                        $pos_init = $pos_tel;
                    }
                    if (str_contains(Str::lower($device->additional_notes), 'cel:') && !str_contains(Str::lower($device->additional_notes), 'tel:')){
                        $pos_cel = strripos(Str::lower($device->additional_notes), 'cel:');
                        $pos_init = $pos_cel;
                    }
                    //dd('Olá');
                    if (str_contains(Str::lower($device->additional_notes), 'cidade:')){
                        $pos_city = strripos(Str::lower($device->additional_notes), 'cidade:');
                        if ($pos_city>$pos_init)
                            $contact = substr($device->additional_notes, $pos_init+5, $pos_city);
                        else
                            $contact = substr($device->additional_notes, $pos_init+5, 70);
                    }
                    else
                        $contact = substr($device->additional_notes, $pos_init+5, 70);
                    //dd($contact);
                }

                if (str_contains(Str::lower($device->additional_notes), 'usuário:') && str_contains(Str::lower($device->additional_notes), 'senha:')){
                    $pos = strripos(Str::lower($device->additional_notes), 'usuário:');
                    $usuario = substr($device->additional_notes, $pos+9, 10);
                    $pos = strripos(Str::lower($device->additional_notes), 'senha:');
                    $senha = substr($device->additional_notes, $pos+7, 10);
                    $users_passwords = 'Usuário: '.$usuario.' Senha: '.$senha;
                    //dd($users_passwords);
                }

                $new_customer = new customer([
                            'active' => true,
                            'name' => $name,
                            'in_debt' => false,
                            'cpf_cnpj' => $cpf_cnpj,
                            'address' => $address,
                            'city' => $city,
                            'contact' => $contact,
                            'users_passwords' => $users_passwords
                        ]);
                        //dd($new_customer);
                        $new_customer->save();
                        //dd('olá');

                $name = '';
                $cpf_cnpj = '';
                $address = '';
                $city = '';
                $contact = '';
                $users_passwords = '';
            }
            //$contact = null;

            /*if ($device->has('traccar')){
                $i++;
                //dd($device->traccar->uniqueId);
                $tracker = Tracker::find($device->id);
                $service = Insta_maint::where('device_id',$device->id)->count();

                $in_service = false;
                if (!$service== 0)
                    $in_service = true;
                //dd($device);
                if (str_contains(Str::lower($device->additional_notes), 'data da manutenção:')){
                    $pos = strripos(Str::lower($device->additional_notes), 'data da manutenção:');
                    $maintence_date = substr($device->additional_notes, $pos+22, 10);
                    $maintence_quant = 1;
                }
                else{
                    $maintence_date = '';
                    $maintence_quant = 0;
                }

                if (str_contains(Str::lower($device->additional_notes), 'data da instalação:')){
                    $pos = strripos(Str::lower($device->additional_notes), 'data da instalação:');
                    $work_since =  preg_replace('/[^\d\/]/', '',substr(Str::lower($device->additional_notes), $pos+22, 10));
                }
                else
                    $work_since = '';
                //if($i == 174)
                //    dd($device);
                if (str_contains(Str::lower($device->additional_notes), 'iccd:')){
                    $pos = strripos(Str::lower($device->additional_notes), 'iccd:');
                    $iccd = substr($device->additional_notes, $pos+6, 41);
                    $iccd = substr(Str::lower($device->additional_notes), $pos+22, 10);
                }
                else{
                    $iccd = $device->sim_number;
                }
                //dd($iccd);
                $operator = preg_replace('/[0-9]/', '', $device->sim_number);
                if ($device->traccar->protocol== 'gt06')
                    $brand = "concox";
                elseif ($device->traccar->protocol == 'suntech')
                    $brand = "suntech";
                elseif ($device->traccar->protocol == 'mxt')
                    $brand = "mxt";
                //elseif ($device->traccar->protocol == null)
                else
                {
                    //dd($device->traccar->protocol);
                }

                if (is_null($tracker)){
                    //dd($device);
                    $new_tracker = new Tracker([
                        'active' => true,
                        'imei' => $device->traccar->uniqueId,
                        'brand' => $brand,
                        'model' => $device->registration_number,
                        'in_use' => true,
                        'device_id' => $device->id,
                        'in_service' => $in_service,
                        'maintence_date' => $maintence_date,
                        'work_since' => $work_since,
                        'history' => '',
                        'sim_number' => $device->sim_number,
                        'iccd' => $iccd,
                        'operator' => $operator
                    ]);
                    //dd($new_tracker);
                    $new_tracker->save();
                }
            }*/

        //}

        //dd($address);
        $input = Input::all();
        $users = null;
        if (Auth::User()->isManager()) {
            $users = Auth::User()->subusers()->lists('id', 'id')->all();
            $users[] = Auth::User()->id;
        }

        //Obter as ocorrências para apresentação
        if ($search_item == '') {
            $page = 0;
            if (Auth::User()->isManager()) {
                $customers = customer::orderby('id', 'asc')
                                ->where('manager_id', Auth::User()->id)
                                ->get();
            } else {
                $customers = customer::orderby('id', 'asc')
                                ->where('manager_id', 0)
                                ->get();
            }
        } else {
            $page = 0;
            if (Auth::User()->isManager()) {
                $user_ = Auth::User()->id;
            } else {
                $user_ = 0;
            }
            $customers = customer::orderby('id', 'asc')
                     ->where('name', 'like', '%'.Str::lower($search_item).'%')
                     ->where('manager_id', $user_)
                     ->orWhere('cpf_cnpj', 'like', '%'.Str::lower($search_item).'%')
                     ->orWhere('city', 'like', '%'.Str::lower($search_item).'%')
                     ->orWhere('users_passwords', 'like', '%'.Str::lower($search_item).'%')
                     ->orWhere('obs', 'like', '%'.Str::lower($search_item).'%')
                     ->get();
        }

        $customers = $customers->paginate(10, $page);
        $items = $this->device->searchAndPaginateAdmin($input, 'name', 'asc', 1, $users);

        $section = $this->section;

        return View::make('admin::'.ucfirst($this->section).'.'.'table')->with(compact('items', 'section', 'customers'));
    }

    public function create()
    {
        $lista = DB::table('users')->where('attach_custumer', false)->where('active', 1)->lists('email', 'id');

        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('lista'));
    }

    public function edit($id)
    {
        if (Auth::User()->isManager()) {
            $user_ = Auth::User()->id;
        } else {
            $user_ = 0;
        }
        $item = customer::where('id', $id)
            ->where(function ($query) use ($user_) {
                $query->where('manager_id', $user_)
                      ->orWhereNull('manager_id');
            })
            ->first();
        $lista1 = DB::table('users')->where('attach_custumer', false)->where('active', 1)->lists('email', 'id');
        $lista = DB::table('users')->where('active', 1)->whereIn('id', json_decode($item->all_users, true))->lists('email', 'id');
        $item->lista = $lista;

        if (! empty($lista)) {
            $item->users = array_replace($lista1, $lista);
        } else {
            $item->users = $lista1;
        }

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('item'));
    }

    public function store(Request $request)
    {
        /*public function store(Request $request)
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
        if (Auth::user()->isAdmin()) {
            $manager_id = 0;
        } else {
            $manager_id = Auth::User()->id;
        }

        $item = new customer([
            'active' => true,
            'name' => $request->input('name'),
            'cpf_cnpj' => $request->input('cpf_cnpj'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'contact' => $request->input('contact'),
            'users_passwords' => $request->input('users_passwords'),
            'manager_id' => $manager_id,
            'all_users' => json_encode($request->input('all_users')),
            'obs' => $request->input('obs'),
        ]);
        $item->save();

        $userIds = is_array($request->input('all_users')) ? $request->input('all_users') : [];

        foreach ($userIds as $userId) {
            DB::table('users')->where('id', $userId)->update(['attach_custumer' => true]);
        }

        return Response::json(['status' => 1]);
    }

    public function update(Request $request)
    {
        $customerId = $request->input('id');
        $customer = Customer::find($customerId);
        if (! $customer) {
            return Response::json(['status' => 0, 'message' => 'Cliente não encontrado']);
        }
        $oldUserIds = json_decode($customer->all_users);
        if ($oldUserIds === null) {
            $oldUserIds = [];
        }

        // Atualiza os dados do cliente
        if ($customer->in_debt) {
            $customer->active = 0;
        } else {
            $customer->active = $request->input('active') ? 1 : 0;
        }
        //$customer->in_debt = $request->input('in_debt') ? 1 : 0;
        $customer->name = $request->input('name');
        $customer->cpf_cnpj = $request->input('cpf_cnpj');
        $customer->address = $request->input('address');
        $customer->city = $request->input('city');
        $customer->contact = $request->input('contact');
        $customer->users_passwords = $request->input('users_passwords');
        $customer->all_users = json_encode($request->input('all_users'));
        $customer->obs = $request->input('obs');
        $customer->save();

        // Obtém as IDs dos usuários antes e depois da atualização

        $newUserIds = is_array($request->input('all_users')) ? $request->input('all_users') : [];

        // IDs dos usuários removidos
        $removedUserIds = array_diff($oldUserIds, $newUserIds);
        foreach ($removedUserIds as $userId) {
            DB::table('users')->where('id', $userId)->update(['attach_custumer' => false]);
        }

        // IDs dos usuários adicionados
        $addedUserIds = array_diff($newUserIds, $oldUserIds);
        debugar(true, json_encode($newUserIds));
        debugar(true, json_encode($oldUserIds));
        debugar(true, json_encode($addedUserIds));
        debugar(true, json_encode($removedUserIds));
        foreach ($addedUserIds as $userId) {
            DB::table('users')->where('id', $userId)->update(['attach_custumer' => true]);
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

    public function convert_date($date, $show_date)
    {
        if ($show_date == true) {
            $dayOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

            $modified_date = Carbon::createFromFormat('Y-m-d', $date, -3);
            $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year;
            //dd($modified_date);
            return $modified_date;
        } else {
        }
    }
}
