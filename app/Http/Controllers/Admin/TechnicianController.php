<?php

namespace App\Http\Controllers\Admin;

use App\Insta_maint;
use App\Technician;
use App\tracker;
use Carbon\Carbon;
//use Illuminate\Support\Facades\Request;
use Facades\Repositories\DeviceRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Validation\ClientFormValidator;

class TechnicianController extends BaseController
{
    /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'Technician';

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
        /*
        teste de código
        $imei_ = tracker::find('19555');

        $device = DeviceRepo::find($imei_->device_id);
        $lastlat = $device->traccar->lastValidLatitude;
        $lastlon = $device->traccar->lastValidLongitude;
        dd($lastlat, $lastlon);*/
        //Atualização de ocorrências

        $input = Request::all();
        $users = null;
        if (Auth::User()->isManager()) {
            $users = Auth::User()->subusers()->pluck('id', 'id')->all();
            $users[] = Auth::User()->id;
        }

        //Obter as ocorrências para apresentação
        if ($search_item == '') {
            $items = Technician::orderby('id', 'asc')
                            ->get();
        } else {
            $items = Technician::orderby('id', 'asc')
                             ->where('name', 'like', '%'.Str::lower($search_item).'%')
                             ->orWhere('city', 'like', '%'.Str::lower($search_item).'%')
                             ->orWhere('address', 'like', '%'.Str::lower($search_item).'%')
                             ->orWhere('obs', 'like', '%'.Str::lower($search_item).'%')
                             ->get();
        }
        foreach ($items as $item) {
            $item->services_performed = Insta_maint::where('technician_id', $item->id)->where('active', 0)->count();
            $item->services_to_performace = Insta_maint::where('technician_id', $item->id)->where('active', 1)->count();
            $item->paid_services = 0;
        }

        $technicians = $items->paginate(10, $page);
        $items = $this->device->searchAndPaginateAdmin($input, 'name', 'asc', 1, $users);

        $section = $this->section;
        //dd('teste');
        //$page = $services->currentPage();
        //$total_pages = $services->lastPage();
        //$pagination = smartPaginate($services->currentPage(), $total_pages);
        //dd($Monitorings->render());
        //dd($services);
        return View::make('admin::'.ucfirst($this->section).'.'.'table')->with(compact('items', 'section', 'technicians'));
    }

    public function create()
    {
        //dd($date_now);

        //$services = insta_maint::all();

        return View::make('admin::'.ucfirst($this->section).'.create');
    }

    public function edit($id)
    {
        $item = technician::find($id);

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('item'));
    }

    public function store(Request $request)
    {
        /*public function store(Request $request)
        {    $validatedData = $request->validate([      'product_line_id' => 'required|integer',      'description' => 'required|alpha_num',      'expiration_time' => 'required|date',      'price' =>['required',     'regex:/^\d+([.,]\d{1,X})?$]/'] ]);    $data = [      'product_line_id' => request('product_line_id'),      'description' => request('description'),      'expiration_time' => request('expiration_time'),      'price' => request('price') ];    Product::create($data);    return back();  } */

        $item = new Technician([
            'active' => true,
            'name' => $request->input('name'),
            'contact' => $request->input('contact'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'obs' => $request->input('obs'),
        ]);
        $item->save();

        return Response::json(['status' => 1]);
    }

    public function update(Request $request)
    {
        $item = Technician::find($request->input('id'));

        $item->active = true;
        $item->name = $request->input('name');
        $item->contact = $request->input('contact');
        $item->address = $request->input('address');
        $item->city = $request->input('city');
        $item->obs = $request->input('obs');

        //  $service-> = $request->input('');
        $item->save();

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
