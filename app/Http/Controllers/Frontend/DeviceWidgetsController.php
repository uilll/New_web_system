<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Facades\GeoLocation;
use Facades\Repositories\DeviceRepo;
use Tobuli\Helpers\GeoLocation\Location;

class DeviceWidgetsController extends Controller
{

    public function location($device_id)
    {
        $device = DeviceRepo::find($device_id);

        $this->checkException('devices', 'show', $device);
		/* Controla o endereço do widget location*/
        try {
            $location = GeoLocation::byCoordinates($device->lat, $device->lng);
        } catch (\Exception $e) {
            $location = null;
        }

        return view('front::Widgets.location')->with([
            'location' => $location ? $location->toArray() : null
        ]);
    }
}
