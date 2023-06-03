<?php

namespace App\Http\Controllers\Admin;

use App\Monitoring;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\EventRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\Event\EventRepositoryInterface as Event;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TraccarDevice;
use Tobuli\Validation\ClientFormValidator;

class ObjectsController extends BaseController
{
    /**
     * @var ClientFormValidator
     */
    private $clientFormValidator;

    private $section = 'objects';

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
        //dd("oi");
        $input = Request::all();

        $users = null;
        if (Auth::User()->isManager()) {
            $users = Auth::User()->subusers()->pluck('id', 'id')->all();
            $users[] = Auth::User()->id;
        }

        $items = $this->device->searchAndPaginateAdmin($input, 'name', 'asc', 10, $users);
        //dd($items);
        $section = $this->section;
        $page = $items->currentPage();
        $total_pages = $items->lastPage();
        $pagination = smartPaginate($items->currentPage(), $total_pages);
        $url_path = $items->resolveCurrentPath();

        return View::make('admin::'.ucfirst($this->section).'.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items', 'input', 'section', 'page', 'total_pages', 'pagination', 'url_path'));
    }

    public function create()
    {
        $managers = ['0' => '-- '.trans('admin.select').' --'] + UserRepo::getOtherManagers(0)->pluck('email', 'id')->all();

        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('managers', 'trackers'));
    }

    public function destroy()
    {
        if (config('tobuli.object_delete_pass') && Auth::user()->isAdmin() && request('password') != config('tobuli.object_delete_pass')) {
            return ['status' => 0, 'errors' => ['message' => trans('front.login_failed')]];
        }

        $ids = Request::get('ids');

        if (is_array($ids) && count($ids)) {
            foreach ($ids as $id) {
                $item = DeviceRepo::find($id);

                if (empty($item) || (! $item->users->contains($this->user->id) && ! isAdmin())) {
                    continue;
                }

                beginTransaction();

                try {
                    $item->users()->sync([]);

                    $Monitoring = Monitoring::where('device_id', $item->traccar_device_id)->get();
                    if (! $Monitoring == null) {
                        $Monitoring->delete();
                    }

                    DB::connection('traccar_mysql')->table('devices')->where('id', '=', $item->traccar_device_id)->delete();
                    EventRepo::deleteWhere(['device_id' => $item->id]);
                    DeviceRepo::delete($item->id);

                    DB::table('user_device_pivot')->where('device_id', $item->id)->delete();
                    DB::table('device_sensors')->where('device_id', $item->id)->delete();
                    DB::table('device_services')->where('device_id', $item->id)->delete();
                    DB::table('user_drivers')->where('device_id', $item->id)->update(['device_id' => null]);

                    if (Schema::connection('traccar_mysql')->hasTable('positions_'.$item->traccar_device_id)) {
                        DB::connection('traccar_mysql')->table('positions_'.$item->traccar_device_id)->truncate();
                    }

                    Schema::connection('traccar_mysql')->dropIfExists('positions_'.$item->traccar_device_id);

                    commitTransaction();
                } catch (\Exception $e) {
                    rollbackTransaction();
                }
            }
        }

        return Response::json(['status' => 1]);
    }

    public function doDestroy()
    {
        return view('admin::Objects.destroy', ['ids' => request('id')]);
    }

    public function restartTraccar()
    {
        $status = 0;
        $res = restartTraccar('user_manual_restart');
        if ($res == 'OK') {
            return Redirect::route('admin.clients.index')->withSuccess(trans('admin.tracking_service_restarted'));
        } else {
            return Redirect::route('admin.clients.index')->withError(trans('admin.'.$res));
        }
    }

    public function import()
    {
        return View::make('admin::'.ucfirst($this->section).'.import');
    }

    public function importSet()
    {
        $file = Request::file('file');

        if (! $file->isValid()) {
            return;
        }

        try {
            $manager = new \Tobuli\Importers\Device\ImporterManager();
            $manager->import($file->getPathName());

            return Response::json(['status' => 1]);
        } catch (ValidationException $e) {
            return Response::json(['errors' => $e->getErrors()]);
        }
    }
}
