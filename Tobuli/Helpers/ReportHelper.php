<?php

namespace Tobuli\Helpers;

use Carbon\Carbon;
use Facades\Repositories\TraccarPositionRepo;
use Illuminate\Support\Facades\Auth;

class ReportHelper
{
    private $geofences = [];

    private $engine_status = 0;

    public $data = [
        'zones_instead' => false,
        'show_addresses' => false, //Editei permiti mostrar endereço nos relatórios
        'stops' => 1,
        'speed_limit' => 0,
        'stop_speed' => 6,
        'unit_of_distance' => 'km',
        'unit_of_altitude' => 'mt',
        'timezone' => 0,
    ];

    public function __construct($data, $geofences = [])
    {
        $this->data = array_merge($this->data, $data);
        $this->geofences = $geofences;
    }

    public function generate($items, $sensors, $driver_history, $device, $date_from, $date_to, $engine_status)
    {
        if (! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        $history = new HistoryHelper();
        $history->report_type = $this->data['type'];
        $history->date_from = $date_from;
        $history->date_to = $date_to;
        $history->engine_status = $engine_status;
        if (! is_null($sensors)) {
            $history->setSensors($sensors);
        }
        if (! is_null($driver_history)) {
            $history->setDrivers($driver_history);
        }
        if (! is_null($device)) {
            $history->setEngineHoursType(['engine_hours' => $device['engine_hours'], 'detect_engine' => $device['detect_engine']]);
        }

        $history->setStopSpeed($this->data['stop_speed']);
        $history->setStopMinutes($this->data['stops']);
        $history->setUnitOfDistance($this->data['unit_of_distance']);
        $history->setUnitOfAltitude($this->data['unit_of_altitude']);
        $history->setTimezone($this->data['zone']);
        $history->speed_limit = $this->data['speed_limit'];
        $history->show_addresses = boolval($this->data['show_addresses']);
        if ($this->data['type'] == 5) {
            $history->getOverspeeds = 1;
        }
        if ($this->data['type'] == 6) {
            $history->getUnderspeeds = 1;
        }
        //if ($this->data['type'] == 11)
        $history->setMinFuelFillings($device['min_fuel_fillings']);
        if ($this->data['type'] == 12) {
            $history->setMinFuelThefts($device['min_fuel_thefts']);
        }
        $history->setGeofences($this->geofences, $this->data['zones_instead']);

        $history->parse($items);
        unset($items);

        return $history;
    }

    public function generateGeofences($items, $date_from, $date_to)
    {
        if (! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        if (empty($this->geofences)) {
            return false;
        }

        // Main list
        $arr = [];

        // Current list
        $current_arr = [];

        // Last geofences ids
        $last = [];

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);

            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $item['time'] = tdate($item['time'], $this->data['zone']);
            $current = $this->getCurrentGeofences($item);

            $entered_geofences = array_flip(array_diff($current, $last));
            $left_geofences = array_flip(array_diff($last, $current));

            foreach ($entered_geofences as $id => $value) {
                $current_arr[$id] = [
                    'start' => $item['time'],
                    'name' => $this->getGeofenceName($id),
                    'position' => [
                        'address' => ($this->data['show_addresses'] ? getGeoAddress($item['latitude'], $item['longitude'], '') : ''),
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude'],
                    ],
                    'distance' => 0,
                    'end' => '-',
                    'duration' => '-',
                ];
            }

            foreach ($left_geofences as $id => $value) {
                $current_arr[$id]['end'] = $item['time'];
                $current_arr[$id]['duration'] = secondsToTime(strtotime($current_arr[$id]['end']) - strtotime($current_arr[$id]['start']));
                $arr[] = $current_arr[$id];
                unset($current_arr[$id]);
            }

            if (! empty($last_item)) {
                foreach ($current_arr as $id => $value) {
                    if (isset($entered_geofences[$id])) {
                        continue;
                    }
                    if (isset($left_geofences[$id])) {
                        continue;
                    }

                    $current_arr[$id]['distance'] += getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);
                }
            }

            $last_item = $item;
            $last = $current;
        }

        foreach ($current_arr as &$geofence) {
            $geofence['end'] = $item['time'];
            $geofence['duration'] = secondsToTime(strtotime($geofence['end']) - strtotime($geofence['start']));
            $arr[] = $geofence;
            unset($geofence);
        }

        return $arr;
    }

    public function generateGeofencesEngine($items, $date_from, $date_to, $device, $sensors)
    {
        if (! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        if (empty($this->geofences)) {
            return false;
        }

        $detect_engine = $device['engine_hours'] == 'engine_hours' ? $device['detect_engine'] : $device['engine_hours'];

        if (! empty($sensors) && ! empty($detect_engine) && $detect_engine != 'gps') {
            foreach ($sensors as $isensor) {
                if ($isensor['type'] == $detect_engine) {
                    $sensor = $isensor;
                    break;
                }
            }
        }

        // Total engine on/off in geofences
        $totals = [];

        // Main list
        $arr = [];

        // Current list
        $current_arr = [];

        // Last geofences ids
        $last = [];

        $engine_status = 0;
        $engine_status_changed = false;

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);

            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $item['time'] = tdate($item['time'], $this->data['zone']);
            $current = $this->getCurrentGeofences($item);

            $entered_geofences = array_flip(array_diff($current, $last));
            $left_geofences = array_flip(array_diff($last, $current));

            if (! empty($sensor)) {
                $engine = $sensor->getValue($item['other'], false, null);
            } else {
                $engine = $item['speed'] > $device['min_moving_speed'] ? 1 : 0;
            }

            $engine_status_changed = (! is_null($engine)) && $engine_status != $engine;

            if (! is_null($engine)) {
                $engine_status = $engine;
            }

            if (empty($last_item)) {
                $last_item = $item;
            }

            foreach ($entered_geofences as $id => $value) {
                $current_arr[$id] = [
                    'start' => $item['time'],
                    'name' => $this->getGeofenceName($id),
                    'geofence_id' => $id,
                    'position' => [
                        'address' => ($this->data['show_addresses'] ? getGeoAddress($item['latitude'], $item['longitude'], '') : ''),
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude'],
                    ],
                    'end' => '-',
                    'duration' => '-',
                ];
            }

            foreach ($current_arr as $id => $value) {
                if (! isset($current_arr[$id]['duration_engine_on'])) {
                    $current_arr[$id]['duration_engine_on'] = 0;
                }
                if (! isset($current_arr[$id]['duration_engine_off'])) {
                    $current_arr[$id]['duration_engine_off'] = 0;
                }

                $duration_time = strtotime($item['time']) - strtotime($last_item['time']);

                if ($engine_status_changed) {
                    if ($engine_status) {
                        $current_arr[$id]['duration_engine_off'] += $duration_time;
                    } else {
                        $current_arr[$id]['duration_engine_on'] += $duration_time;
                    }
                } else {
                    if ($engine_status) {
                        $current_arr[$id]['duration_engine_on'] += $duration_time;
                    } else {
                        $current_arr[$id]['duration_engine_off'] += $duration_time;
                    }
                }
            }

            foreach ($left_geofences as $id => $value) {
                $current_arr[$id]['end'] = $item['time'];
                $current_arr[$id]['duration'] = secondsToTime(strtotime($current_arr[$id]['end']) - strtotime($current_arr[$id]['start']));
                $arr[] = $current_arr[$id];
                unset($current_arr[$id]);
            }

            $last = $current;
            $last_item = $item;
        }

        foreach ($current_arr as &$geofence) {
            $geofence['end'] = $last_item['time'];
            $geofence['duration'] = secondsToTime(strtotime($geofence['end']) - strtotime($geofence['start']));
            $arr[] = $geofence;
            unset($geofence);
        }

        foreach ($arr as &$geofence) {
            if (empty($totals[$geofence['geofence_id']])) {
                $totals[$geofence['geofence_id']] = [
                    'name' => $this->getGeofenceName($geofence['geofence_id']),
                    'duration_engine_on' => 0,
                    'duration_engine_off' => 0,
                ];
            }
            $totals[$geofence['geofence_id']]['duration_engine_on'] += $geofence['duration_engine_on'];
            $totals[$geofence['geofence_id']]['duration_engine_off'] += $geofence['duration_engine_off'];

            $geofence['duration_engine_on'] = secondsToTime($geofence['duration_engine_on']);
            $geofence['duration_engine_off'] = secondsToTime($geofence['duration_engine_off']);
        }

        foreach ($totals as &$geofence) {
            $geofence['duration_engine_on'] = secondsToTime($geofence['duration_engine_on']);
            $geofence['duration_engine_off'] = secondsToTime($geofence['duration_engine_off']);
        }

        return [
            'items' => $arr,
            'totals' => $totals,
        ];
    }

    public function generateGeneralCustom($items, $date_from, $date_to, $device, $sensors)
    {
        if (! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }
        $device_engine = ['engine_hours' => $device['engine_hours'], 'detect_engine' => $device['detect_engine']];
        foreach ($sensors as $key => $sensor) {
            if ($sensor['type'] == $device_engine['engine_hours']) {
                $device_engine['engine_hours_sensor'] = $sensor;
            }
            if ($sensor['type'] == $device_engine['detect_engine']) {
                $device_engine['detect_engine_sensor'] = $sensor;
            }
        }
        $stop_speed = 6;
        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);
        $last_item = null;
        $arr = [];
        $status = 2;
        $action_time = 0;
        foreach ($items as &$item) {
            $timestamp = strtotime($item['time']);
            if ($from_timestamp > $timestamp) {
                continue;
            }
            if ($to_timestamp < $timestamp) {
                break;
            }
            $item['time'] = tdate($item['time'], $this->data['zone']);
            if (is_null($last_item)) {
                $item['distance'] = 0;
            } else {
                $item['distance'] = getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);
            }

            if (! empty($last_item)) {
                $time = strtotime($item['time']) - strtotime($last_item['time']);
                if ($time <= 10 && $last_item['speed'] > 5 && $item['speed'] == 0) {
                    $item['speed'] = $last_item['speed'];
                }
                $last = end($arr);
                if (empty($last)) {
                    $arr[] = [
                        'date' => date('Y-m-d', strtotime($item['time'])),
                        'start' => null,
                        'end' => '-',
                        'distance' => 0,
                        'stop_duration' => 0,
                        'move_duration' => 0,
                        'engine_idle' => 0,
                        'engine_work' => 0,
                        'overspeed_count' => 0,
                    ];
                    $last = end($arr);
                }
                $last_key = key($arr);
                if ($last['date'] == date('Y-m-d', strtotime($item['time']))) {
                    //if (date('Y-m-d', strtotime($last_item['time'])) == date('Y-m-d', strtotime($item['time'])))
                    $this->countEngineHours($last_item, $item, $arr[$last_key], $time, $device_engine);
                    $arr[$last_key]['distance'] += $item['distance'];
                    if ($this->data['speed_limit'] && $item['speed'] > $this->data['speed_limit']) {
                        $arr[$last_key]['overspeed_count']++;
                    }

                    if ($item['speed'] < $stop_speed) {
                        if ($status == 1) {
                            $arr[$last_key]['end'] = $item['time'];
                        }
                        // If object was already stoped add time
                        if ($status == 2) {
                            $arr[$last_key]['stop_duration'] += $time;
                            $action_time += $time;
                        } else {
                            // If last object didnt move distance
                            if (($action_time + $time) < 4) {
                                $arr[$last_key]['move_duration'] -= $action_time;
                                $arr[$last_key]['stop_duration'] += $action_time + $time;
                                $action_time = 0;
                            } else {
                                $arr[$last_key]['move_duration'] += $time;
                                $action_time = 0;
                            }
                            $status = 2;
                        }
                    } else {
                        if (is_null($arr[$last_key]['start'])) {
                            $arr[$last_key]['start'] = $item['time'];
                        }
                        if ($status == 1) {
                            $arr[$last_key]['move_duration'] += $time;
                        } else {
                            // If last item stood less than needed, delete it
                            if (($action_time + $time) <= $this->data['stops'] * 60) {
                                $arr[$last_key]['stop_duration'] -= $action_time;
                                $arr[$last_key]['move_duration'] += $action_time;
                                $action_time = 0;
                            } else {
                                $arr[$last_key]['stop_duration'] += $time;
                                $action_time = 0;
                            }
                            $status = 1;
                        }
                    }
                } else {
                    if ($arr[$last_key]['end'] == '-') {
                        $arr[$last_key]['end'] = $last_item['time'];
                    }
                    $arr[] = [
                        'date' => date('Y-m-d', strtotime($item['time'])),
                        'start' => null,
                        'end' => '-',
                        'distance' => 0,
                        'stop_duration' => 0,
                        'move_duration' => 0,
                        'engine_idle' => 0,
                        'engine_work' => 0,
                        'overspeed_count' => 0,
                    ];
                }
            }
            $last_item = $item;
        }
        foreach ($arr as &$item) {
            $item['distance'] = float($item['distance']);
            $item['engine_idle'] = secondsToTime($item['engine_idle']);
            $item['engine_work'] = secondsToTime($item['engine_work']);
            $item['stop_duration'] = secondsToTime($item['stop_duration']);
            $item['move_duration'] = secondsToTime($item['move_duration']);
            if ($device_engine['engine_hours'] == 'gps') {
                $item['move_duration'] = $item['engine_work'];
            }
        }

        return $arr;
    }

    public function countEngineHours($last_item, $item, &$arr_item, $time, $engine)
    {
        if ($engine['engine_hours'] == 'gps') {
            if ($time > 300) {
                return;
            }

            $this->sumEngineWork($item['speed'], $last_item['speed'], $arr_item, $time);
        } elseif ($engine['engine_hours'] == 'engine_hours') {
            if (! isset($engine['engine_hours_sensor'])) {
                return;
            }

            //$engine_hours = getSensorValueRaw($item['other'], $engine['engine_hours_sensor']);
            /*
            $engine_hours = $engine['engine_hours_sensor']->getValueRaw($item['other']);
            if (!is_null($engine_hours))
                $arr_item['engine_hours'] += $engine_hours;
            */

            // Engine work
            if ($engine['detect_engine'] == 'gps') {
                if ($time > 300) {
                    return;
                }

                $this->sumEngineWork($item['speed'], $last_item['speed'], $arr_item, $time);
            } else {
                if (! isset($engine['detect_engine_sensor'])) {
                    return;
                }

                $engine = $engine['detect_engine_sensor']->getValue($item['other'], false, null);
                if (! is_null($engine)) {
                    $this->engine_status = $engine;
                }

                if (! $this->engine_status) {
                    return;
                }

                $this->sumEngineWork($item['speed'], $last_item['speed'], $arr_item, $time);
            }
        } else {
            if (! isset($engine['engine_hours_sensor'])) {
                return;
            }

            $engine = $engine['engine_hours_sensor']->getValue($item['other'], false, null);
            if (! is_null($engine)) {
                $this->engine_status = $engine;
            }

            if (! $this->engine_status) {
                return;
            }

            $this->sumEngineWork($item['speed'], $last_item['speed'], $arr_item, $time);
        }
    }

    private function sumEngineWork($speed, $last_speed, &$arr_item, $time)
    {
        if ($speed > 0) {
            $arr_item['engine_work'] += $time;
        }

        if ($last_speed <= 0 && $speed <= 0) {
            $arr_item['engine_idle'] += $time;
        }
    }

    public function generateGeofences24($items, $date_from, $date_to)
    {
        if (! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        if (empty($this->geofences)) {
            return false;
        }

        // Main list
        $arr = [];

        // Current list
        $current_arr = [];

        // Last geofences ids
        $last = [];

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);

            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $item['time'] = tdate($item['time'], $this->data['zone']);
            $current = $this->getCurrentGeofences($item);

            $entered_geofences = array_flip(array_diff($current, $last));
            $left_geofences = array_flip(array_diff($last, $current));

            foreach ($entered_geofences as $id => $value) {
                $current_arr[$id] = [
                    'start' => $item['time'],
                    'name' => $this->getGeofenceName($id),
                    'position' => [
                        'address' => ($this->data['show_addresses'] ? getGeoAddress($item['latitude'], $item['longitude'], '') : ''),
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude'],
                    ],
                    'end' => '-',
                    'duration' => '-',
                ];
            }

            foreach ($left_geofences as $id => $value) {
                $current_arr[$id]['end'] = splitTimeAtMidnight($current_arr[$id]['start'], $item['time']);
                $current_arr[$id]['duration'] = (! is_array($current_arr[$id]['end']) ? secondsToTime(strtotime($current_arr[$id]['end']) - strtotime($current_arr[$id]['start'])) : '');

                $arr[] = $current_arr[$id];
                unset($current_arr[$id]);
            }

            $last = $current;
        }

        foreach ($current_arr as &$geofence) {
            $geofence['end'] = splitTimeAtMidnight($geofence['start'], $item['time']);
            $geofence['duration'] = ! is_array($geofence['end']) ? secondsToTime(strtotime($geofence['end']) - strtotime($geofence['start'])) : $geofence['end'];
            $arr[] = $geofence;
            unset($geofence);
        }

        return $arr;
    }

    private function getCurrentGeofences($item)
    {
        $arr = [];

        foreach ($this->geofences as $geofence) {
            if (! $geofence->pointIn($item)) {
                continue;
            }

            array_push($arr, $geofence['id']);
        }

        return $arr;
    }

    private function getGeofenceName($geofence_id)
    {
        foreach ($this->geofences as $geofence) {
            if ($geofence_id == $geofence->id) {
                return $geofence->name;
            }
        }

        return null;
    }

    public function generateEvents($items)
    {
        $history = new HistoryHelper();
        $history->show_addresses = boolval($this->data['show_addresses']);
        $history->setGeofences($this->geofences, $this->data['zones_instead']);

        foreach ($items as &$item) {
            $item['message'] = parseEventMessage($item['message'], $item['type']);
            $item['time'] = tdate($item['time'], $this->data['zone']);
            $item['address'] = $history->getAddress([
                'lat' => $item['latitude'],
                'lng' => $item['longitude'],
                'address' => $item['address'],
            ]);
        }

        return $items;
    }

    public function generateRag($items, $driver_history, $device, $sensors, $date_from, $date_to)
    {
        $data = [];
        $last = null;
        $last_over = false;
        $current_driver = null;
        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);
        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);
            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            if (! is_null($last)) {
                $time = $timestamp - strtotime($last['time']);
                $distance = getDistance($item['latitude'], $item['longitude'], $last['latitude'], $last['longitude']);
            } else {
                $distance = 0;
            }

            if (! empty($driver_history)) {
                foreach ($driver_history as $driver) {
                    if ($timestamp <= $driver->date) {
                        continue;
                    }

                    $current_driver = $driver->name;
                }
            }

            end($data);
            $key = key($data);
            $ld = current($data);
            if ($ld === false) {
                array_push($data, [
                    'name' => $current_driver,
                    'time' => 0,
                    'hb' => 0,
                    'ha' => 0,
                    'distance' => $distance,
                ]);

                end($data);
                $key = key($data);
                $ld = current($data);
            } else {
                $data[$key]['distance'] += $distance;
            }

            if (! empty($sensors)) {
                foreach ($sensors as $sensor) {
                    preg_match('/<'.preg_quote($sensor['tag_name'], '/').'>(.*?)<\/'.preg_quote($sensor['tag_name'], '/').'>/s', $item['other'], $matches);
                    if (isset($matches['1'])) {
                        $value = $matches['1'];
                        preg_match('/\%SETFLAG\[([0-9]+)\,([0-9]+)\]\%/', $sensor['formula'], $match);
                        if (isset($match['1']) && isset($match['2'])) {
                            $value = substr($value, $match['1'], $match['2']);
                        }

                        if ($value == $sensor['on_value']) {
                            if ($sensor['type'] == 'harsh_acceleration') {
                                $data[$key]['ha']++;
                            } else {
                                $data[$key]['hb']++;
                            }
                        }
                    }
                }
            }

            if ($last_over) {
                if ($ld['name'] != $current_driver) {
                    $data[$key]['time'] += $time;
                    array_push($data, [
                        'name' => $current_driver,
                        'time' => 0,
                        'hb' => 0,
                        'ha' => 0,
                        'distance' => 0,
                    ]);
                } else {
                    $data[$key]['time'] += $time;
                }
                $last_over = false;
            }

            if ($this->data['speed_limit'] && round($item['speed']) > $this->data['speed_limit']) {
                $last_over = true;
            }

            $last = $item;
        }

        return $data;
    }

    public function generateRagSeatbelt($items, $driver_history, $device, $sensors, $date_from, $date_to)
    {
        if (! empty($item) && ! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        $data = [];
        $last = null;
        $last_over = false;
        $current_driver = null;
        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);
        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);

            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            if (! is_null($last)) {
                $time = strtotime($item['time']) - strtotime($last['time']);
                $distance = getDistance($item['latitude'], $item['longitude'], $last['latitude'], $last['longitude']);
            } else {
                $time = 0;
                $distance = 0;
            }

            if (! empty($driver_history)) {
                foreach ($driver_history as $driver) {
                    if ($timestamp <= $driver->date) {
                        continue;
                    }

                    $current_driver = $driver->name;
                }
            }

            end($data);
            $key = key($data);
            $ld = current($data);
            if ($ld === false) {
                array_push($data, [
                    'name' => $current_driver,
                    'time' => 0,
                    'hb' => 0,
                    'ha' => 0,
                    'sb0' => 0,
                    'sb1' => 0,
                    'top_speed' => 0,
                    'distance' => $distance,
                ]);

                end($data);
                $key = key($data);
                $ld = current($data);
            } else {
                $data[$key]['distance'] += $distance;
            }

            if (! empty($sensors)) {
                foreach ($sensors as $sensor) {
                    if (in_array($sensor['type'], ['harsh_acceleration', 'harsh_breaking'])) {
                        preg_match('/<'.preg_quote($sensor['tag_name'], '/').'>(.*?)<\/'.preg_quote($sensor['tag_name'], '/').'>/s', $item['other'], $matches);
                        if (isset($matches['1'])) {
                            $value = $matches['1'];

                            preg_match('/\%SETFLAG\[([0-9]+)\,([0-9]+)\]\%/', $sensor['formula'], $match);
                            if (isset($match['1']) && isset($match['2'])) {
                                $value = substr($value, $match['1'], $match['2']);
                            }

                            if ($value == $sensor['on_value']) {
                                if ($sensor['type'] == 'harsh_acceleration') {
                                    $data[$key]['ha']++;
                                } else {
                                    $data[$key]['hb']++;
                                }
                            }
                        }
                    } else {
                        $seatbelt = $sensor->getValue($item['other'], false, false);

                        if (round($item['speed']) > 0) {
                            if ($seatbelt === 1) {
                                $data[$key]['sb1'] += $time;
                            } elseif ($seatbelt === 0) {
                                $data[$key]['sb0'] += $time;
                            }
                        }
                    }
                }
            }

            if ($last_over) {
                if ($ld['name'] != $current_driver) {
                    $data[$key]['time'] += $time;
                    array_push($data, [
                        'name' => $current_driver,
                        'time' => 0,
                        'hb' => 0,
                        'ha' => 0,
                        'sb0' => 0,
                        'sb1' => 0,
                        'top_speed' => 0,
                        'distance' => 0,
                    ]);
                } else {
                    $data[$key]['time'] += $time;
                }
                $last_over = false;
            }
            if ($this->data['speed_limit'] && round($item['speed']) > $this->data['speed_limit']) {
                $last_over = true;
            }
            if (round($item['speed']) > $data[$key]['top_speed']) {
                $data[$key]['top_speed'] = round($item['speed']);
            }

            $last = $item;
        }

        return $data;
    }

    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function generateBirlaCustom($items, $date_from, $date_to, $device)
    {
        if (! empty($item) && ! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        $journeys = [];
        $journey = null;
        $current_state = null;
        $last_state = null;
        $last_item = null;
        $repeat = 0;

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);
            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $state = $this->birlaState($item['other']);
            $item = array_merge($item, $state);

            if (empty($last_item)) {
                $last_item = $item;
            }

            if (is_null($item['state'])) {
                continue;
            }

            if (! in_array($item['state'], [0, 1])) {
                continue;
            }

            if (! $journey) {
                $journey = [
                    'state' => $item['state'],
                    'distance' => 0,
                    'duration' => 0,
                    'move_duration' => 0,
                    'stop_duration' => 0,
                ];
            }

            //$distance = $item['distance'];
            $distance = getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);
            $time = strtotime($item['time']) - strtotime($last_item['time']);
            $move_duration = $item['motion'] == 'true' ? $time : 0;
            $stop_duration = $item['motion'] != 'true' ? $time : 0;

            $journey['distance'] += $distance;
            $journey['duration'] += $time;
            $journey['move_duration'] += $move_duration;
            $journey['stop_duration'] += $stop_duration;

            if ($last_item['state'] == $item['state'] && $item['motion'] == 'false') {
                $repeat++;

                if (! empty($tmp)) {
                    $tmp['distance'] += $distance;
                    $tmp['duration'] += $time;
                    $tmp['move_duration'] += $move_duration;
                    $tmp['stop_duration'] += $stop_duration;
                }
            } else {
                $repeat = 0;
                $item_changed = $item;

                $tmp = [
                    'distance' => $distance,
                    'duration' => $time,
                    'move_duration' => $move_duration,
                    'stop_duration' => $stop_duration,
                ];
            }

            $last_item = $item;

            if ($repeat < 5) {
                continue;
            }

            if ($journey['state'] == $item['state']) {
                continue;
            }

            // 1 -> 0 journy end
            if ($item_changed['state'] == 0) {
                $journey['end'] = [
                    'timestamp' => strtotime($item_changed['time']),
                    'time' => tdate($item_changed['time'], $this->data['zone']),
                    'address' => ($this->data['show_addresses'] ? getGeoAddress($item_changed['latitude'], $item_changed['longitude'], '') : ''),
                ];

                if (! empty($tmp)) {
                    $journey['distance'] -= $tmp['distance'];
                    $journey['duration'] -= $tmp['duration'];
                    $journey['move_duration'] -= $tmp['move_duration'];
                    $journey['stop_duration'] -= $tmp['stop_duration'];
                }

                $journeys[] = $journey;
                $journey = null;
            }

            // 0 -> 1 journy begin
            if ($item_changed['state'] == 1) {
                $journeys[] = $journey;

                $journey = [
                    'state' => $item_changed['state'],
                    'distance' => 0,
                    'duration' => 0,
                    'move_duration' => 0,
                    'stop_duration' => 0,
                    'begin' => [
                        'timestamp' => strtotime($item_changed['time']),
                        'time' => tdate($item_changed['time'], $this->data['zone']),
                        'address' => ($this->data['show_addresses'] ? getGeoAddress($item_changed['latitude'], $item_changed['longitude'], '') : ''),
                    ],
                ];

                if (! empty($tmp)) {
                    $journey = array_merge($journey, $tmp);
                }
            }

            $tmp = null;
        }

        if ($journey) {
            $journeys[] = $journey;
        }

        foreach ($journeys as $i => $journey) {
            $distance = $journey['distance'];

            if ($this->data['unit_of_distance'] == 'mi') {
                $distance = kilometersToMiles($journey['distance']);
            }

            $journeys[$i]['distance'] = round($distance, 2).trans('front.'.$this->data['unit_of_distance']);
            $journeys[$i]['move_duration'] = secondsToTime($journey['move_duration']);
            $journeys[$i]['stop_duration'] = secondsToTime($journey['stop_duration']);

            if (! empty($journey['begin']) && ! empty($journey['end'])) {
                $journeys[$i]['duration'] = secondsToTime($journey['duration']);
            } else {
                $journeys[$i]['duration'] = null;
            }
        }

        return [
            'device' => [
                'name' => $device->name,
                'time' => tdate(date('Y-m-d H:i:s'), $this->data['zone']),
                'address' => ($this->data['show_addresses'] ? getGeoAddress($device->lat, $device->lng, '') : ''),
            ],
            'journeys' => $journeys,
        ];
    }

    public function birlaState($other)
    {
        $motion = null;
        preg_match('/<motion>(.*?)<\/motion>/s', $other, $matches);
        if (isset($matches[1])) {
            $motion = $matches[1];
        }

        $state = null;
        preg_match('/<in2>(.*?)<\/in2>/s', $other, $matches);
        if (isset($matches[1])) {
            $state = $matches[1];
        }

        return [
            'state' => $state,
            'motion' => $motion,
        ];
    }

    public function generateAutomonCustom($items, $date_from, $date_to, $device)
    {
        if (! empty($item) && ! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        $journeys = [];
        $journey = null;
        $current_state = null;
        $last_state = null;
        $last_item = null;
        $repeat = 0;

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);
            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $state = $this->automonState($item['other']);
            $item = array_merge($item, $state);

            if (empty($last_item)) {
                $last_item = $item;
            }

            if (is_null($item['state'])) {
                continue;
            }

            if (! in_array($item['state'], [0, 1])) {
                continue;
            }

            if (! $journey) {
                $journey = [
                    'state' => $item['state'],
                    'distance' => 0,
                    'duration' => 0,
                    'move_duration' => 0,
                    'stop_duration' => 0,
                ];
            }

            //$distance = $item['distance'];
            $distance = getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);
            $time = strtotime($item['time']) - strtotime($last_item['time']);
            $move_duration = $item['motion'] == 'true' ? $time : 0;
            $stop_duration = $item['motion'] != 'true' ? $time : 0;

            $journey['distance'] += $distance;
            $journey['duration'] += $time;
            $journey['move_duration'] += $move_duration;
            $journey['stop_duration'] += $stop_duration;

            if ($last_item['state'] == $item['state']) {
                $repeat++;

                if (! empty($tmp)) {
                    $tmp['distance'] += $distance;
                    $tmp['duration'] += $time;
                    $tmp['move_duration'] += $move_duration;
                    $tmp['stop_duration'] += $stop_duration;
                }
            } else {
                $repeat = 0;
                $item_changed = $item;

                $tmp = [
                    'distance' => $distance,
                    'duration' => $time,
                    'move_duration' => $move_duration,
                    'stop_duration' => $stop_duration,
                ];
            }

            $last_item = $item;

            if ($repeat < 1) {
                continue;
            }

            if ($journey['state'] == $item['state']) {
                continue;
            }

            // 1 -> 0 journy end
            if ($item_changed['state'] == 0) {
                $journey['end'] = [
                    'timestamp' => strtotime($item_changed['time']),
                    'time' => tdate($item_changed['time'], $this->data['zone']),
                    'address' => ($this->data['show_addresses'] ? getGeoAddress($item_changed['latitude'], $item_changed['longitude'], '') : ''),
                ];

                if (! empty($tmp)) {
                    $journey['distance'] -= $tmp['distance'];
                    $journey['duration'] -= $tmp['duration'];
                    $journey['move_duration'] -= $tmp['move_duration'];
                    $journey['stop_duration'] -= $tmp['stop_duration'];
                }

                $journeys[] = $journey;
                $journey = null;
            }

            // 0 -> 1 journy begin
            if ($item_changed['state'] == 1) {
                $journeys[] = $journey;

                $journey = [
                    'state' => $item_changed['state'],
                    'distance' => 0,
                    'duration' => 0,
                    'move_duration' => 0,
                    'stop_duration' => 0,
                    'begin' => [
                        'timestamp' => strtotime($item_changed['time']),
                        'time' => tdate($item_changed['time'], $this->data['zone']),
                        'address' => ($this->data['show_addresses'] ? getGeoAddress($item_changed['latitude'], $item_changed['longitude']) : ''),
                    ],
                ];

                if (! empty($tmp)) {
                    $journey = array_merge($journey, $tmp);
                }
            }

            $tmp = null;
        }

        if ($journey) {
            $journeys[] = $journey;
        }

        foreach ($journeys as $i => $journey) {
            $distance = $journey['distance'];

            if ($this->data['unit_of_distance'] == 'mi') {
                $distance = kilometersToMiles($journey['distance']);
            }

            $journeys[$i]['distance'] = round($distance, 2).trans('front.'.$this->data['unit_of_distance']);
            $journeys[$i]['move_duration'] = secondsToTime($journey['move_duration']);
            $journeys[$i]['stop_duration'] = secondsToTime($journey['stop_duration']);

            if (! empty($journey['begin']) && ! empty($journey['end'])) {
                $journeys[$i]['duration'] = secondsToTime($journey['duration']);
            } else {
                $journeys[$i]['duration'] = null;
            }
        }

        return [
            'device' => [
                'name' => $device->name,
                'time' => tdate(date('Y-m-d H:i:s'), $this->data['zone']),
                'address' => ($this->data['show_addresses'] ? getGeoAddress($device->lat, $device->lng) : ''),
            ],
            'journeys' => $journeys,
        ];
    }

    public function automonState($other)
    {
        $motion = null;
        preg_match('/<motion>(.*?)<\/motion>/s', $other, $matches);
        if (isset($matches[1])) {
            $motion = $matches[1];
        }

        $state = null;
        preg_match('/<adc1>(.*?)<\/adc1>/s', $other, $matches);
        if (isset($matches[1])) {
            $state = $matches[1] > 10000 ? 1 : 0;
        }

        return [
            'state' => $state,
            'motion' => $motion,
        ];
    }

    public function generateObjectHistory($date_from, $date_to, $device, $user)
    {
        $timezone = $user->timezone->zone;
        $zone = timezoneReverse($timezone);

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        $positions = TraccarPositionRepo::searchWithSensors($user->id, $device->traccar_device_id, $date_from, $date_to);

        $parameters = [];

        foreach ($positions as $index => &$position) {
            $timestamp = strtotime($position['time']);

            if ($timestamp < $from_timestamp || $timestamp > $to_timestamp) {
                unset($positions[$index]);

                continue;
            }

            $position['time'] = datetime($position['time'], true, $timezone);

            if ($user->unit_of_distance == 'mi') {
                $position['speed'] = kilometersToMiles($position['speed']);
            }

            $position['speed'] = round($position['speed']);

            // Convert altitude if users unit of altitude is feets
            if ($user->unit_of_altitude == 'ft') {
                $position['altitude'] = metersToFeets($position['altitude']);
            }

            $position['altitude'] = round($position['altitude']);

            $other = parseXMLToArray($position['other']);
            $position['other'] = $other ? $other : [];

            $position['address'] = ($this->data['show_addresses'] ? getGeoAddress($position['latitude'], $position['longitude'], '') : '');

            foreach ($position['other'] as $key => $value) {
                if (empty($key)) {
                    continue;
                }

                if (in_array($key, $parameters)) {
                    continue;
                }

                $parameters[] = $key;
            }
        }

        return [
            'device' => $device,
            'positions' => $positions,
            'parameters' => $parameters,
        ];
    }

    public function generateGeofencesShift($items, $date_from, $date_to, $parameters)
    {
        if ($items && ! is_array($items[0])) {
            $items = json_decode(json_encode((array) $items), true);
        }

        if (empty($this->geofences)) {
            return false;
        }

        $out_limit = $parameters['excessive_exit'];
        $shift_start = $parameters['shift_start'];
        $shift_finish = $parameters['shift_finish'];

        $late_entry = Carbon::parse($shift_start)->addMinutes($parameters['shift_start_tolerance'])->format('H:i');
        $late_exit = Carbon::parse($shift_finish)->subMinutes($parameters['shift_finish_tolerance'])->format('H:i');

        // Main list
        $arr = [];

        // Current list
        $current_arr = [];

        // Last geofences ids
        $last = [];

        $from_timestamp = strtotime($date_from);
        $to_timestamp = strtotime($date_to);

        foreach ($items as $item) {
            $timestamp = strtotime($item['time']);

            if ($from_timestamp > $timestamp) {
                continue;
            }

            if ($to_timestamp < $timestamp) {
                break;
            }

            $item['time'] = tdate($item['time'], $this->data['zone']);
            $current = $this->getCurrentGeofences($item);

            $entered_geofences = array_flip(array_diff($current, $last));
            $left_geofences = array_flip(array_diff($last, $current));

            foreach ($entered_geofences as $id => $value) {
                $current_arr[] = [
                    'geofence_id' => $id,
                    'type' => 'in',
                    'time' => $item['time'],
                    'position' => [
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude'],
                    ],
                ];
            }

            foreach ($left_geofences as $id => $value) {
                $current_arr[] = [
                    'geofence_id' => $id,
                    'type' => 'out',
                    'time' => $item['time'],
                    'position' => [
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude'],
                    ],
                ];
            }

            $last = $current;
        }

        foreach ($current_arr as $current_item) {
            $day = date('Y-m-d', strtotime($current_item['time']));

            if (empty($arr[$day][$current_item['geofence_id']])) {
                $arr[$day][$current_item['geofence_id']] = [
                    'geofence' => $this->getGeofenceName($current_item['geofence_id']),
                    'shift' => $late_entry.' - '.$late_exit,
                    'first_in' => null,
                    'last_out' => null,
                    'count_out' => 0,
                ];
            }

            $current = $arr[$day][$current_item['geofence_id']];

            if ($current_item['type'] == 'out') {
                $current['last_out'] = $current_item['time'];
                $current['count_out']++;
            } else {
                if (empty($current['first_in'])) {
                    $current['first_in'] = $current_item['time'];
                }
            }

            $arr[$day][$current_item['geofence_id']] = $current;
        }

        $result = [];

        foreach ($arr as $day => $geofences) {
            $time_in = strtotime($day.' '.$late_entry);
            $time_out = strtotime($day.' '.$late_exit);

            foreach ($geofences as $geofence_id => $values) {
                if ($values['count_out'] >= $out_limit) {
                    $result[] = $arr[$day][$geofence_id];

                    continue;
                }

                if (strtotime($values['first_in']) > $time_in) {
                    $result[] = $arr[$day][$geofence_id];

                    continue;
                }

                if (strtotime($values['last_out']) < $time_out) {
                    $result[] = $arr[$day][$geofence_id];

                    continue;
                }
            }
        }

        return $result;
    }

    public function generateEngineHours24($items)
    {
        $result = [];

        foreach ($items as $item) {
            $time = tdate($item->time, $this->data['zone']);
            $date = date('Y-m-d', strtotime($time));

            $value = round($item->getVirtualEngineHours() / 3600, 2);

            if (! isset($result[$date]['from'])) {
                $result[$date]['date'] = $date;
                $result[$date]['from'] = $value;
            }

            $result[$date]['to'] = $value;
            $result[$date]['diff'] = round($result[$date]['to'] - $result[$date]['from'], 2);
        }

        return $result;
    }

    public function generateIgnitionOnOff($items, $date_from, $date_to, $device, $sensors, $driver_history = null)
    {
        $result = [];

        $detect_engine = $device['engine_hours'] == 'engine_hours' ? $device['detect_engine'] : $device['engine_hours'];

        if (! empty($sensors) && ! empty($detect_engine) && $detect_engine != 'gps') {
            foreach ($sensors as $isensor) {
                if ($isensor['type'] == $detect_engine) {
                    $sensor = $isensor;
                    break;
                }
            }
        }

        $speed = 0;
        $distance = 0;
        $items_count = 0;
        $duration_time = 0;
        $last_key = null;
        $engine_status = null;
        $units_of_distance = Auth::user()->unit_of_distance;

        foreach ($items as $key => $item) {
            if (! empty($sensor)) {
                $ignition = $sensor->getValue($item['other'], false, null);
            } else {
                $ignition = $item['speed'] > $device['min_moving_speed'] ? 1 : 0;
            }

            $engine_status_changed = (! is_null($engine_status)) && $engine_status != $ignition;

            if (! is_null($ignition)) {
                $engine_status = $ignition;
            }

            if (empty($last_item)) {
                $last_item = $item;
            }

            $duration_time += strtotime($item['time']) - strtotime($last_item['time']);

            if ($engine_status_changed) {
                if ($duration_time <= $this->data['ignition_off'] * 60 && $engine_status) {
                    $result[$last_key]['duration_engine_on'] += $duration_time;
                } else {
                    if (isset($result[$last_key]) && isset($result[$last_key]['duration_engine_on']) && ! $engine_status) {
                        $result[$last_key]['duration_engine_on'] += $duration_time;
                        $result[$last_key]['speed'] = round(($result[$last_key]['speed'] + $speed / $items_count) / 2);
                        $result[$last_key]['distance'] += round($distance, 2);
                        $result[$last_key]['position'] = $this->data['show_addresses'] ?
                            getGeoAddress($item['latitude'], $item['longitude'], '') : $item['latitude'].', '.$item['longitude'];

                        $key = $last_key;
                    } else {
                        $result[$key][$engine_status ? 'duration_engine_off' : 'duration_engine_on'] = $duration_time;

                        $time = strtotime(tdate($item['time'], $this->data['zone'])) - $duration_time;

                        if (! empty($driver_history)) {
                            foreach ($driver_history as $driver) {
                                if ($time <= $driver->date) {
                                    continue;
                                }

                                $current_driver = $driver->name;
                            }
                        }

                        $result[$key]['date'] = date('Y-m-d', $time);
                        $result[$key]['time'] = date('H:i:s', $time);
                        $result[$key]['speed'] = round($speed / $items_count);
                        $result[$key]['distance'] = round($distance, 2);
                        $result[$key]['driver'] = ! empty($current_driver) ? $current_driver : null;
                        $result[$key]['position'] = $this->data['show_addresses'] ?
                            getGeoAddress($item['latitude'], $item['longitude'], '') : $item['latitude'].', '.$item['longitude'];
                        $result[$key]['unit_of_distance'] = $units_of_distance;
                    }

                    $items_count = 0;
                    $speed = 0;
                    $distance = 0;
                    $duration_time = 0;
                    $last_key = $key;
                }
            }

            $items_count += 1;
            $speed += $item['speed'];
            $distance += getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);

            $last_item = $item;
        }

        return $result;
    }
}
