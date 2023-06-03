<?php

namespace App\Http\Controllers\Frontend;

use App\Console\PositionsStack;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;

class GpsDataController extends Controller
{
    /**
     * @var Device
     */
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function insert()
    {
        $input = Request::all();
        $error = null;
        $required = ['imei' => '', 'date' => '', 'lat' => '', 'lon' => '', 'speed' => '', 'altitude' => '', 'course' => '', 'protocol' => ''];

        foreach ($required as $field => $value) {
            if (! isset($input[$field])) {
                $error .= $field.', ';
            }
        }

        $other_arr = array_diff_key($input, $required);

        if (! is_null($error)) {
            return Response::make(json_encode(['status' => 0, 'message' => 'Missing params: '.substr($error, 0, -2)]), 400);
        }

        $device = $this->device->findWhere(['imei' => $input['imei']]);
        if (empty($device)) {
            return Response::make(json_encode(['status' => 0, 'message' => 'IMEI not found']), 400);
        }

        $data = [
            'fixTime' => strtotime($input['date']) * 1000,
            'valid' => 1,
            'imei' => $input['imei'],
            'latitude' => $input['lat'],
            'longitude' => $input['lon'],
            'attributes' => $other_arr,
            'speed' => $input['speed'] / 1.852,
            'altitude' => $input['altitude'],
            'course' => $input['course'],
            'protocol' => $input['protocol'],
        ];

        (new PositionsStack())->add($data);

        return Response::make(json_encode(['status' => 1, 'message' => 'OK']), 200);
    }
}
