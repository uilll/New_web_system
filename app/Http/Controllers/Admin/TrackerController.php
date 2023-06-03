<?php

namespace App\Http\Controllers\Admin;

use App\Monitoring;
use App\tracker;
use Carbon\Carbon;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Repositories\User\UserRepositoryInterface as User;
use Tobuli\Validation\ClientFormValidator;

class TrackerController extends BaseController
{
    /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'trackers';

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
        if (false) {//Auth::User()->id == 6) {
            $items = Tracker::orderby('id', 'asc')
                ->where('in_use', 'like', false)
                ->get();
            //dd($trackers);
            foreach ($items as $tracker_) {
                if (! $tracker_->device_id == 0) {
                    //dd($tracker_->device_id);

                    $device = DB::table('devices')->where('id', $tracker_->device_id)->select('id', 'name')->count();
                    if ($device > 0) {
                        $device = DB::table('devices')->where('id', $tracker_->device_id)->select('id', 'name')->get();
                        $device = $device[0];
                        //dd($device);
                        //dd($device);
                        //debugar(true,$device->id);
                        if (! str_contains(Str::lower($device->name), 'teste de rastreador')) {
                            //dd($tracker_);
                            $tracker__ = Tracker::find($tracker_->id);
                            $tracker__->in_use = true;
                            $tracker__->save();
                        }
                    }
                    //debugar(true,$device->id);*/
                }
            }
        }

        $input = Request::all();
        $users = null;
        if (Auth::User()->isManager()) {
            $users = Auth::User()->subusers()->pluck('id', 'id')->all();
            $users[] = Auth::User()->id;
        }

        if (Auth::User()->isManager()) {
            $user_ = Auth::User()->id;
        } else {
            $user_ = 0;
        }

        //Obter as ocorrências para apresentação
        if ($search_item == '') {
            $page = 0;
            $items = tracker::orderby('id', 'asc')
                    ->where('manager_id', $user_)
                    ->get();
        } elseif ($search_item == '-onlysat') {
            if (in_array(Auth::User()->id, [3, 6])) {
                $page = 0;
                $items = tracker::orderby('id', 'asc')
                        ->where('manager_id', '1085')
                        //->where('active',true)
                        ->get();
            } else {
                $page = 0;
                $items = tracker::orderby('id', 'asc')
                        ->where('manager_id', $user_)
                        ->get();
            }
        } else {
            $page = 0;
            $items = Tracker::orderby('id', 'asc')
                    ->where('imei', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('brand', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('model', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('sim_number', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('iccd', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('operator', 'like', '%'.Str::lower($search_item).'%')
                    ->orWhere('history', 'like', '%'.Str::lower($search_item).'%')
                    ->get();

            $items = $items->filter(function ($item) use ($user_) {
                if ($item->manager_id == $user_) {
                    return $item;
                }
            });
        }

        $trackers = $items->paginate(10, $page);
        $items = $this->device->searchAndPaginateAdmin($input, 'name', 'asc', 1, $users);

        $section = $this->section;

        return View::make('admin::'.ucfirst($this->section).'.'.'table')->with(compact('items', 'section', 'trackers'));
    }

    public function create()
    {
        return View::make('admin::'.ucfirst($this->section).'.create');
    }

    public function edit($id)
    {
        $item = Tracker::find($id);
        $item->brand = Str::upper($item->brand);

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('item'));
    }

    public function store(Request $request)
    {
        /*public function store(Request $request)
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */
        if (Auth::User()->isManager()) {
            $user_ = Auth::User()->id;
        } else {
            $user_ = 0;
        }
        //debugar(true, "Início criar novo cadastro de rastreador");
        $item = new Tracker([
            'active' => $request->input('active'),
            'in_use' => 0,
            'in_service' => 0,
            'imei' => $request->input('imei'),
            'brand' => $request->input('brand'),
            'model' => $request->input('model'),
            'work_since' => $request->input('work_since'),
            'history' => $request->input('history'),
            'sim_number' => $request->input('sim_number'),
            'iccd' => $request->input('iccd'),
            'operator' => $request->input('operator'),
            'manager_id' => $user_,
        ]);
        debugar(true, $item);
        $item->save();
        debugar(true, 'Finalizou OK?');

        return Response::json(['status' => 1]);
    }

    public function update(Request $request)
    {
        $item = Tracker::find($request->input('id'));
        //dd($item);
        $prop = $item->manager_id;

        debugar(true, 'Início criar novo cadastro de rastreador');

        if (Auth::User()->isManager()) {
            $user_ = Auth::User()->id;
        } else {
            $user_ = 0;
        }

        if (in_array(Auth::User()->id, [2, 3, 6, 1025, 1026])) {
            if (! in_array($item->manager_id, [1085])) {
                $item->active = $request->input('active');
                //$item->in_use = $request->input('in_use');
                $item->in_service = $request->input('in_service');
                $item->imei = $request->input('imei');
                $item->brand = $request->input('brand');
                $item->model = $request->input('model');
                $item->work_since = $request->input('work_since');
                $item->history = $item->history."\r\n ".$request->input('history'); //"Removido de: CAR-1564 Instalado em: CAR-1564";
                $item->sim_number = $request->input('sim_number');
                $item->iccd = $request->input('iccd');
                $item->manager_id = $user_;
                $item->operator = $request->input('operator');
            } else {
                if ($item->in_use == false) {
                    $item->active = $request->input('active');
                } else {
                    echo "<script type='javascript'>alert('Não foi possível desativar o rastreador, pois ele está atribuido a um veículo');";
                }
            }
        } else {
            $item->in_service = $request->input('in_service');
            $item->imei = $request->input('imei');
            $item->brand = $request->input('brand');
            $item->model = $request->input('model');
            $item->work_since = $request->input('work_since');
            $item->history = $item->history."\r\n ".$request->input('history'); //"Removido de: CAR-1564 Instalado em: CAR-1564";
            $item->sim_number = $request->input('sim_number');
            $item->iccd = $request->input('iccd');
            $item->manager_id = $user_;
            $item->operator = $request->input('operator');
        }
        debugar(true, $item);

        //  $service-> = $request->input('');
        $item->save();

        debugar(true, 'Finalizou OK?');

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
