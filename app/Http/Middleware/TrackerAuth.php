<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.3.12
 * Time: 14.20
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Request;
use Tobuli\Entities\Device;

class TrackerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $input = Request::all();
        $user = null;
        if (! empty($input['imei'])) {
            $imei = $input['imei'];
            $device = Device::where('imei', $imei)
                ->select('devices.*')
                ->join('gpswox_traccar.devices as traccar_devices', 'devices.traccar_device_id', '=', 'traccar_devices.id')
                ->where(function ($query) {
                    $query
                        ->whereNull('traccar_devices.protocol')
                        ->orWhere('traccar_devices.protocol', '=', 'osmand');
                })
                ->first();
        }

        if (empty($device)) {
            return response()->json(['success' => false, 'message' => trans('front.login_failed')], 401);
        }

        \app()->instance(Device::class, $device);

        return $next($request);
    }
}
