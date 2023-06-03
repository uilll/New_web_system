<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\DeviceGroupFormValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevicesGroupsController extends Controller
{
    public function create()
    {
        $this->checkException('devices_groups', 'create');

        $data = [
            'devices' => UserRepo::getDevices($this->user->id),
        ];

        return view('front::DevicesGroups.create')->with($data);
    }

    public function store(Request $request)
    {
        $this->checkException('devices_groups', 'store');

        $data = array_merge($request->all(), ['user_id' => $this->user->id]);

        DeviceGroupFormValidator::validate('create', $data);

        $item = DeviceGroupRepo::create($data);

        if ($device = $request->input('devices', [])) {
            DB::table('user_device_pivot')
                ->where([
                    'user_id' => $this->user->id,
                ])
                ->whereIn('device_id', $device)
                ->update([
                    'group_id' => $item->id,
                ]);
        }

        return response()->json(['status' => 1, 'id' => $item->id]);
    }

    public function edit($id)
    {
        $item = DeviceGroupRepo::find($id);

        $this->checkException('devices_groups', 'edit', $item);

        $data = [
            'item' => $item,
            'devices' => UserRepo::getDevices($this->user->id),
        ];

        return view('front::DevicesGroups.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $item = DeviceGroupRepo::find($id);

        $this->checkException('devices_groups', 'update', $item);

        DeviceGroupFormValidator::validate('update', $request->all());

        $item->update($request->all());

        DB::table('user_device_pivot')
            ->where([
                'user_id' => $this->user->id,
                'group_id' => $item->id,
            ])
            ->update([
                'group_id' => null,
            ]);

        if ($device = $request->input('devices', [])) {
            DB::table('user_device_pivot')
                ->where([
                    'user_id' => $this->user->id,
                ])
                ->whereIn('device_id', $device)
                ->update([
                    'group_id' => $item->id,
                ]);
        }

        return response()->json(['status' => 1, 'id' => $item->id]);
    }
}
