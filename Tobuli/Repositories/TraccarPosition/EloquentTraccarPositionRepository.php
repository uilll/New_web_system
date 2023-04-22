<?php namespace Tobuli\Repositories\TraccarPosition;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tobuli\Entities\Device;

class EloquentTraccarPositionRepository implements TraccarPositionRepositoryInterface {

    public function searchObj($device_id, $date_from, $date_to, $sort = 'asc')
    {
        $device = Device::where('traccar_device_id', $device_id)->first();

        if ( ! $device)
            return [];

        return $device->positions()
            ->whereBetween('time', [$date_from, $date_to])
            ->orderBy('time', $sort)
            ->get();
    }

    public function searchWithSensors($user_id, $device_id, $date_from, $date_to, $sort = 'asc')
    {
        DB::connection('traccar_mysql')->setFetchMode(\PDO::FETCH_ASSOC);

        $columns = [
            'id', 'altitude', 'course', 'latitude', 'longitude', 'other', 'speed', 'time', 'server_time', 'valid'
        ];

        $result = DB::connection('traccar_mysql')
            ->table('positions_'.$device_id )
            //->select($columns)
            ->whereBetween('time', [$date_from, $date_to])
            ->orderBy('time', $sort)
            ->get();

        //$result = json_decode(json_encode($result), true);

        DB::connection('traccar_mysql')->setFetchMode(\PDO::FETCH_OBJ);

        return $result;
    }

    public function search($user_id, $data, $paginate = FALSE, $limit = 50, $sort = 'asc')
    {
        $query = DB::connection('traccar_mysql')
            ->table('positions_' . $data['device_id'] . ' AS positions')
            ->select('positions.*')
            ->whereBetween('positions.time', [$data['date_from'], $data['date_to']])
            ->orderBy('positions.time', $sort);

        return ($paginate ? $query->paginate($limit) : $query->get());
    }

    public function sumDistance($device_id, $range)
    {
        return DB::connection('traccar_mysql')
            ->table('positions_'.$device_id)
            ->select(DB::raw('SUM(distance) as sum'))
            ->whereBetween(DB::raw('DATE(positions_'.$device_id.'.time)'), [$range[0], $range[1]])
            ->first();
    }

    public function sumDistanceHigher($device_id, $date_to)
    {
        return DB::connection('traccar_mysql')
            ->table('positions_'.$device_id)
            ->select(DB::raw('SUM(distance) as sum'))
            ->where('time', '>', $date_to)
            ->first();
    }

    public function getOldest($device_id) {
        return DB::connection('traccar_mysql')
            ->table("positions_{$device_id}")
            ->select(DB::raw('*, latitude as lastValidLatitude, longitude as lastValidLongitude'))
            ->orderBy('id', 'asc')
            ->first();
    }

    public function getNewer($device_id, $position_id = 0) {
        return DB::connection('traccar_mysql')
            ->table("positions_{$device_id}")
            ->select(DB::raw('*, latitude as lastValidLatitude, longitude as lastValidLongitude'))
            ->where('id', '>', $position_id)
            ->first();
    }

    public function getOlder($deviceId, $positionId = 0, $limit = 5) {
        return DB::connection('traccar_mysql')
            ->table("positions_{$deviceId}")
            ->select(DB::raw('*'))
            ->where('id', '<', $positionId)
            ->where('distance', '>', 0.02)
            ->take($limit)
            ->get();

    }

    public function getBetween($device_id, $from, $to) {
        return DB::connection('traccar_mysql')
            ->table("positions_{$device_id} as positions")
            ->select(DB::raw("positions.*, sensors.time as sensor_time, sensors.other as sensor_other, DATE(positions.time) as date"))
            ->whereRaw("(positions.time BETWEEN '{$from}' AND '{$to}' OR sensors.time BETWEEN '{$from}' AND '{$to}')")
            //->whereBetween('positions.time', [$from, $to])
            //->groupBy('positions.id')
            ->orderBy('sensors.time', 'asc')
            ->get();
    }

    public function getPosition($device_id, $position_id) {
        $result = DB::connection('traccar_mysql')
            ->table('positions_'.$device_id )
            ->find($position_id);

        return $result;
    }

}