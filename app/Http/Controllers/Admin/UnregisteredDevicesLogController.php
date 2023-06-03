<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class UnregisteredDevicesLogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $items = DB::connection('traccar_mysql')->table('unregistered_devices_log')->orderBy('date', 'desc')->limit(50)->get();

        return view('admin::UnregisteredDevicesLog.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items'));
    }

    public function destroy()
    {
        $id = Input::get('id');

        $ids = is_array($id) ? $id : [$id];

        DB::connection('traccar_mysql')->table('unregistered_devices_log')->whereIn('imei', $ids)->delete();

        return ['status' => 1];
    }
}
