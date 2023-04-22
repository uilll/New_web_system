<?php namespace Tobuli\Helpers;

class HistoryHelper {
    public $api = 0;
    public $date_from;
    public $date_to;
    public $history = 0;
    public $top_speed = 0;
    public $distance_sum = 0;
    public $move_duration = 0;
    public $stop_duration = 0;
    public $average_speed = 0;
    public $overspeed_count = 0;
    public $route_start;
    public $route_end;
    public $show_addresses = FALSE;
    public $stop_speed = 6;
    public $stop_km = 0.00005;
    public $speed_limit = 0;
    public $getOverspeeds = FALSE;
    public $getUnderspeeds = FALSE;
    public $overspeeds_count = 0;
    public $underspeeds_count = 0;
    public $unit_of_distance = 'km';
    public $distance_unit_hour = 'km/h';
    public $unit_of_altitude = 'mt';
    public $odometer = NULL;
    public $odometer_sensor_id = NULL;
    public $odometers;
    public $engine_hours = 0;
    public $engine_work = 0;
    public $engine_idle = 0;
    public $temperature_sensors = [];
    public $fuel_tank_sensors = [];
    public $fuel_tank_thefts = [];
    public $fuel_tank_fillings = [];
    public $fuel_consumption = [];
    public $fuel_diffs = [];
    public $engine_sensor = NULL;
    public $engine_status = 0;
    public $report_type = null;

    private $engine_hours_type = [];
    //editei alterei o tempo de 180 para 60s
    private $stop_secounds = 60;
    private $speed_items = 0;
    private $speed_sum = 0;
    private $items = [];
    private $cords = [];
    private $sensors = [];
    private $sensors_values = [];
    public $sensors_arr = [];
    private $last_sensor_value = [];
    private $drivers = NULL;
    private $drivers_arr = [];
    private $item = [];
    private $last_item = NULL;
    private $last_key = NULL;
    private $time = NULL;
    private $use_geofences = FALSE;
    private $geofences = [];
    private $min_fuel_fillings = NULL;
    private $min_fuel_thefts = NULL;
    private $timezone = 0;

    function __construct() {}

    public function parse($items) {
        $this->prepareSensors();

        $last_item = NULL;

        foreach ($items as $item) {
            if (is_null($last_item))
                $item['distance'] = 0;
            else {
                $item['distance'] = getDistance($item['latitude'], $item['longitude'], $last_item['latitude'], $last_item['longitude']);
            }

            $this->item = $item;

            $this->item['color'] = 'blue';

            if ( ! empty($this->route_color_sensor) ) {
                $this->item['route_color'] = $this->route_color_sensor->getValue($this->item['other'], false, false);
                if ( $this->item['route_color'] ) {
                    $this->item['color'] = settings('plugins.route_color.options.value');
                }
            }

            if ( ! empty($this->drive_business_sensor) ) {
                $this->item['drive_busines'] = $this->drive_business_sensor->getValue($this->item['other'], false, false);
                if ( $this->item['drive_busines'] ) {
                    $this->item['color'] = settings('plugins.business_private_drive.options.business_color.value');
                }
            }

            if ( ! empty($this->drive_private_sensor) ) {
                $this->item['drive_private'] = $this->drive_private_sensor->getValue($this->item['other'], false, false);

                if ( $this->item['drive_private'] ) {
                    $this->item['color'] = settings('plugins.business_private_drive.options.private_color.value');
                }
            }


            # Overspeeds
            if ($this->speed_limit && $this->item['speed'] > $this->speed_limit)
                $this->overspeed_count++;

            # Convert speed, altitude, time etc.
            $this->convertData();

            # Sensors
            $this->itemSetSensors();

            # Set top speed
            $this->top_speed < $this->item['speed'] && $this->top_speed = $this->item['speed'];

            # Speed sum for average speed
            if ($this->item['speed'] > 0) {
                $this->speed_sum += $this->item['speed'];
                $this->speed_items++;
            }


            if ( $this->report_type == 21 ) {
                if ( empty($this->item['drive_busines']) ) {
                    if (!empty($last_item)) {
                        $last_item = $this->item;
                    }
                    $skipped = true;
                    continue;
                } else {
                    $skipped = false;
                }
            }

            if ( $this->report_type == 22 ) {
                if ( empty($this->item['drive_private']) ) {
                    if (!empty($last_item)) {
                        $last_item = $this->item;
                    }
                    $skipped = true;
                    continue;
                } else {
                    $skipped = false;
                }
            }

            if (!empty($last_item)) {
                $this->time = strtotime($this->item['raw_time']) - strtotime($last_item['raw_time']);
                $this->getLastItem();

                # Engine hours
                if (!empty($this->engine_hours_type))
                    $this->countEngineHours($last_item);

                if ($this->engine_hours_type['detect_engine'] == 'gps')
                    $this->engine_status = 1;

                # Check if device moved
                if ($this->engine_status && $this->item['speed'] > $this->stop_speed) {
                    if ($this->last_item['status'] == 1) {
                        if ( in_array($this->report_type, [21,22]) && $skipped ) {
                            $this->addNewItem(1);
                        } else {
                            $this->lastItemAdd();
                        }
                    }
                    else {
                        // If last item stood less than needed, delete it
                        if (($this->last_item['time'] + $this->time) <= $this->stop_secounds) {
                            $this->deleteLastItem(1);
                        }
                        else {
                            $this->items[$this->last_key]['time'] += $this->time;
                            if($this->item['speed']>0)
                                $this->addNewItem(1);
                            else
                                $this->addNewItem(2);
                        }
                    }
                }
                else {
                    # If object was already stoped add time
                    if ($this->last_item['status'] == 2) {
                        if ( in_array($this->report_type, [21,22]) && $skipped ) {
                            $this->addNewItem(2);
                        } else {
                            $this->lastItemAdd();
                        }
                    }
                    else {
                        # If last object didnt move distance
                        if (($this->last_item['time'] + $this->time)  < 4) {
                            $this->deleteLastItem(2);
                        }
                        else {
                            $this->items[$this->last_key]['time'] += $this->time;
                            $this->addNewItem(2);
                        }
                    }
                }
            }
            else {
                $this->addNewItem(2, TRUE);
            }

            $last_item = $this->item;
        }
        unset($items);

        $this->prepareData();
    }

    private function convertData() {
        $this->item['item_id'] = 'i'.$this->item['id'];

        # Set distance to zero if didnt move
        if ($this->item['distance'] < $this->stop_km)
            $this->item['distance'] = 0;

        # Convert speed if users unit of distance is miles
        if ($this->unit_of_distance == 'mi')
            $this->item['speed'] = kilometersToMiles($this->item['speed']);

        $this->item['speed'] = round($this->item['speed']);

        # Convert altitude if users unit of altitude is feets
        if ($this->unit_of_altitude == 'ft')
            $this->item['altitude'] = metersToFeets($this->item['altitude']);

        $this->item['altitude'] = round($this->item['altitude']);

        # Apply time zone
        //$this->item['raw_time'] = $this->item['time'];
        $this->item['raw_time'] = tdate($this->item['time'], $this->timezone);
        $this->item['time'] = datetime($this->item['raw_time'], false);
        //$this->item['time'] = datetime($this->item['time'], TRUE, $this->timezone);

        # Convert coordinates
        $this->item['lat'] = $this->item['latitude'];
        $this->item['lng'] = $this->item['longitude'];
        $this->item['other_arr'] = [];
        if ($this->api && !empty($this->item['other'])) {
            $this->item['other_arr'] = parseXML($this->item['other']);
        }

        if ($this->api) {
            $this->item['sensors_data'] = [];
            array_push($this->item['sensors_data'], ['id' => 'speed', 'value' => $this->item['speed']]);
            array_push($this->item['sensors_data'], ['id' => 'altitude', 'value' => $this->item['altitude']]);
        }
    }

    public function setStopMinutes($minutes) {
        $this->stop_secounds = $minutes * 60;
    }

    public function setStopSpeed($speed) {
        $this->stop_speed = $speed;
    }

    public function setSensors($sensors) {
        $this->sensors = $sensors;
    }

    public function setDrivers($drivers) {
        if (empty($drivers))
            return;

        $this->drivers_arr = $drivers;

        $drivers_string = '';
        foreach ($drivers as $driver)
            $drivers_string .= $driver->name.', ';

        $this->drivers = substr($drivers_string, 0, -2);
    }

    public function setGeofences($geofences, $use_geofences = FALSE) {
        $this->use_geofences = boolval($use_geofences);
        $this->geofences = $geofences;
    }

    public function setSpeedLimit($speed_limit) {
        if (is_numeric($speed_limit))
            $this->speed_limit = $speed_limit;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    public function getItems() {
        return $this->items;
    }

    public function getCords() {
        return $this->cords;
    }

    public function getSensors() {
        return $this->sensors_arr;
    }

    public function getSensorsValues() {
        return $this->sensors_values;
    }

    public function getDrivers() {
        return $this->drivers;
    }

    public function setUnitOfDistance($unit) {
        if ($unit == 'km') {
            $this->unit_of_distance = 'km';
            $this->distance_unit_hour = 'km/h';
        }
        if ($unit == 'mi') {
            $this->unit_of_distance = 'mi';
            $this->distance_unit_hour = 'mph';
        }
    }

    public function setUnitOfAltitude($unit) {
        if ($unit == 'mt' || $unit == 'ft')
            $this->unit_of_altitude = $unit;
    }

    public function setEngineHoursType($arr) {
        $this->engine_hours_type = $arr;
    }

    public function setMinFuelFillings($int) {
        $this->min_fuel_fillings = $int;
    }

    public function setMinFuelThefts($int) {
        $this->min_fuel_thefts = $int;
    }

    private function getLastItem() {
        $this->last_item = end($this->items);
        $this->last_key = key($this->items);
    }

    private function deleteLastItem($status) {
        $last_item = $this->items[$this->last_key];
        unset($this->items[$this->last_key]);
        $this->getLastItem();

        # Check if deleted item wasnt the last one or delete new one
        if (isset($this->last_item['status'])) {
            $first_key = key($last_item['items']);
            end($last_item['items']);
            $last_key = key($last_item['items']);

            $this->items[$this->last_key]['items'][$last_key] = $last_item['items'][$last_key];
            $this->items[$this->last_key]['time'] += $last_item['time'] + $this->time;
            $this->items[$this->last_key]['distance'] += $last_item['distance'] + $this->item['distance'];
            $this->items[$this->last_key]['engine_work'] += $last_item['engine_work'];
            $this->items[$this->last_key]['engine_idle'] += $last_item['engine_idle'];
            $this->items[$this->last_key]['items'][$this->item['item_id']] = '';
            $this->cords[$this->item['item_id']] = $this->item;

            unset($last_item, $last_key);
        }
        else
            $this->addNewItem($status);
    }

    private function addNewItem($status, $first = FALSE) {
        $items = [];
        if (isset($this->last_item['status'])) {
            end($this->last_item['items']);
            $item_key = key($this->last_item['items']);
            $items += [$item_key => ''];

            //$this->items[$this->last_key]['time'] += $this->time;
            $this->items[$this->last_key]['left'] = $this->item['time'];
        }

        $items += [$this->item['item_id'] => ''];
        $this->items[] = [
            'status' => $status,
            'time' => 0,
            'distance' => $first ? 0 : $this->item['distance'],
            'show' => $this->item['time'],
            'raw_time' => $this->item['raw_time'],
            'items' => $items,
            'fuel_consumption' => 0,
            'engine_work' => 0,
            'engine_idle' => 0,
        ];

        $this->cords[$this->item['item_id']] = $this->item;
    }

    private function lastItemAdd() {
        $this->items[$this->last_key]['time'] += $this->time;
        $this->items[$this->last_key]['distance'] += $this->item['distance'];
        $this->items[$this->last_key]['items'][$this->item['item_id']] = '';
        $this->cords[$this->item['item_id']] = $this->item;
    }

    private function prepareSensors() {
        if (empty($this->sensors))
            return FALSE;

        foreach ($this->sensors as $key => $sensor) {
            if (isset($this->engine_hours_type['engine_hours']) && $sensor['type'] == $this->engine_hours_type['engine_hours']) {
                $this->engine_hours_type['engine_hours_sensor'] = $sensor;
                if (isset($this->engine_hours_type['detect_engine']) == 'gps') {
                    $this->engine_hours_type['detect_engine'] = $sensor['type'];
                    $this->engine_hours_type['detect_engine_sensor'] = $sensor;
                }
            }

            if (isset($this->engine_hours_type['detect_engine']) && $sensor['type'] == $this->engine_hours_type['detect_engine'])
                $this->engine_hours_type['detect_engine_sensor'] = $sensor;

            if ( settings('plugins.business_private_drive.status') ) {
                if (in_array($sensor['type'], ['drive_business', 'drive_private'])) {
                    $this->{$sensor['type'] . '_sensor'} = $sensor;
                }
            }

            if ( settings('plugins.route_color.status') ) {
                if (in_array($sensor['type'], ['route_color'])) {
                    $this->{$sensor['type'] . '_sensor'} = $sensor;
                }
            }

            if ( !in_array($sensor['type'], ['fuel_tank','fuel_tank_calibration','temperature', 'temperature_calibration','odometer', 'tachometer', 'gsm', 'battery']) ) {
                unset($this->sensors[$key]);
                continue;
            }

            if ( ! $this->odometer_sensor_id && $sensor['type'] == 'odometer') {
                $this->odometer_sensor_id = $key;
            }

            if ($sensor['type'] == 'fuel_tank' || $sensor['type'] == 'fuel_tank_calibration')
                $this->fuel_tank_sensors[] = 'sensor_'.$sensor['id'];

            if ($sensor['type'] == 'temperature' || $sensor['type'] == 'temperature_calibration')
                $this->temperature_sensors[] = 'sensor_'.$sensor['id'];

            if ($this->api) {
                array_push($this->sensors_arr, [
                    'id' => 'sensor_'.$sensor['id'],
                    'name' => getSensorName($sensor),
                    'sufix' => $sensor['unit_of_measurement']
                ]);
            }
            else {
                $this->sensors_arr['sensor_'.$sensor['id']] = [
                    'name' => getSensorName($sensor),
                    'sufix' => $sensor['unit_of_measurement']
                ];
            }
        }
    }

    private function itemSetSensors() {
        $this->sensors_values['speed'][] = ['t' => $this->item['raw_time'], 'v' => $this->item['speed'], 'i' => $this->item['id']];
        $this->sensors_values['altitude'][] = ['t' => $this->item['raw_time'], 'v' => $this->item['altitude'], 'i' => $this->item['id']];

        if ((is_array($this->sensors) && empty($this->sensors)) || (is_object($this->sensors) && $this->sensors->isEmpty())) {
            return;
        }

        if (empty($this->item['other']))
            return;

        foreach ($this->sensors as $sensor) {

            $valid = true;

            $sensor_id = 'sensor_'.$sensor['id'];

            //$value = getSensorValue($this->item['other'], $sensor, FALSE, FALSE, FALSE);
            $value = $sensor->getValue($this->item['other'], false);

            if (is_null($value) && $sensor->type == 'tachometer')
                $value = 0;

            if (is_null($value))
                $value = isset($this->last_sensor_value[$sensor_id]) ? $this->last_sensor_value[$sensor_id] : 0;

            if ($this->api) {
                array_push($this->item['sensors_data'], ['id' => $sensor_id, 'value' => (float) $value]);
            }
            else {
                $this->sensors_values[$sensor_id][] = ['t' => $this->item['raw_time'], 'v' => $value, 'i' => $this->item['id']];
            }

            if (($sensor['type'] == 'fuel_tank' || $sensor['type'] == 'fuel_tank_calibration') && isset($this->last_sensor_value[$sensor_id]))
            {
                if ( ! isset($this->fuel_consumption[$sensor_id]))
                    $this->fuel_consumption[$sensor_id] = 0;

                if ( ! isset($this->fuel_diffs[$sensor_id]))
                    $this->fuel_diffs[$sensor_id] = [];

                //tmp fuel Filling/Thefts fix
                //$valid = !empty($this->item['other_arr']) && ! in_array('valid: false', $this->item['other_arr']);

                # Thefts
                if (!is_null($this->min_fuel_thefts) && $this->last_sensor_value[$sensor_id] > $value && $value > 0) {
                    $diff = $this->last_sensor_value[$sensor_id] - $value;
                    if ($diff >= $this->min_fuel_thefts && $this->item['speed'] < $this->stop_speed && $valid) {
                        # Add to thefts
                        $this->fuel_tank_thefts[$sensor_id][] = [
                            'time' => $this->item['time'],
                            'last' => $this->last_sensor_value[$sensor_id],
                            'diff' => $diff,
                            'current' => $value,
                            'lat' => $this->item['latitude'],
                            'lng' => $this->item['longitude'],
                            'address' => $this->getAddress($this->item)
                        ];
                    }
                }

                $valid = true;

                $diffs = & $this->fuel_diffs[$sensor_id];
                $diff = $value - $this->last_sensor_value[$sensor_id];
                $increasing = $diff > 0;
                $decreasing = $diff < 0;

                $this->getLastItem();
                $this->items[$this->last_key]['fuel_consumption'] -= $diff;
                $this->fuel_consumption[$sensor_id] -= $diff;

                $count_diffs = count($diffs);

                if ($count_diffs > 2)
                {
                    $diff_sum = array_sum($diffs);

                    $diffs = array_filter($diffs, function($v, $k) use ($diff_sum, $count_diffs) {
                        if (empty($v))
                            return false;

                        return abs($diff_sum / $v) >= 0.1;
                    }, ARRAY_FILTER_USE_BOTH);

                    $count_diffs = count($diffs);
                }

                $filling = 0;
                //condition
                if ( ! $increasing && $count_diffs > 4 ) {
                    $filling = $this->getFillingFromDiffs($diffs);
                    $diffs = array_slice($diffs, -20, 20);
                }

                $diffs[] = $diff;


                # Filling
                if (!is_null($this->min_fuel_fillings) && $filling > $this->min_fuel_fillings) {

                    # Add to fillings
                    $this->fuel_tank_fillings[$sensor_id][] = [
                        'time' => $this->item['time'],
                        'last' => $value + $diff - $filling,
                        'diff' => $filling,
                        'current' => $value + $diff,
                        'lat' => $this->item['latitude'],
                        'lng' => $this->item['longitude'],
                        'address' => $this->getAddress($this->item)
                    ];

                    # clean diffs
                    $diffs = array_slice($diffs, -1, 1);

                    $this->items[$this->last_key]['fuel_consumption'] += $filling;
                    $this->fuel_consumption[$sensor_id] += $filling;
                }
            }

            if ($valid)
                $this->last_sensor_value[$sensor_id] = $value;
        }
    }

    private function getFillingFromDiffs($diffs)
    {
        $filling = 0;
        $increasing = false;

        foreach ($diffs as $diff) {
            if ( ! $increasing && $diff < 0)
                continue;

            $increasing = true;

            $filling += $diff;
        }

        return $filling;
    }

    private function getGeofencesName($item) {
        $name = '';

        if ($this->use_geofences) {
            $geofences = $this->getGeofencesNameArr($item);

            $name = implode(',', $geofences);
        }

        return $name;
    }

    private function getGeofencesNameArr($item) {
        $arr = [];

        if (empty($this->geofences))
            return $arr;

        $point = $item['lat'].' '.$item['lng'];

        foreach ($this->geofences as $geofence) {
            if ( ! $geofence->pointIn($point))
                continue;

            $arr[$geofence['id']] = $geofence['name'];
        }

        return $arr;
    }

    public function getAddress($item) {
        $address = '';
        if (!isset($item['latitude']) && isset($item['lat'])) {
            $item['latitude'] = $item['lat'];
            $item['longitude'] = $item['lng'];
        }
        if ($this->show_addresses)
			//$data_ = getGeoCity($item['latitude'], $item['longitude']);
			//$address = $data_[1]." - ".$data_[2];
            $address = $this->history == 1 ? '' : getGeoAddress($item['latitude'], $item['longitude']);//$data_[1]
			//$address = $this->history == 1 ? '' : $data_[1];
        if ($this->use_geofences) {
            $geofences_name = $this->getGeofencesName($item);
            if (!empty($geofences_name))
                $address = $geofences_name;
        }

        return $address;
    }

    private function getItemUnderOverspeeds($type, &$speeds, $key, $last_item) {
        $cord_item = $this->cords[$key];
        $speed = $cord_item['speed'];
        $speed_item = end($speeds);
        $speed_item_key = key($speeds);
        if (!is_null($last_item))
            $time = strtotime($cord_item['raw_time']) - strtotime($last_item['raw_time']);

        if (($type == 'over' && $speed > $this->speed_limit) || ($type == 'under' && $speed < $this->speed_limit)) {
            if (empty($speed_item) || isset($speed_item['end'])) {
                $speeds[] = [
                    'position' => [
                        'address' => $this->getAddress($cord_item),
                        'lat' => $cord_item['lat'],
                        'lng' => $cord_item['lng']
                    ],
                    'start' => $cord_item['time'],
                    'time' => 0,
                    'top_speed' => $speed,
                    'speed_items' => 1,
                    'average_speed' => $speed,
                    'speed_sum' => $speed
                ];
            }
            else {
                if (!isset($speed_item['end'])) {
                    $speeds[$speed_item_key]['time'] += $time;
                    $speed > $speeds[$speed_item_key]['top_speed'] && $speeds[$speed_item_key]['top_speed'] = $speed;
                    $speeds[$speed_item_key]['speed_items']++;
                    $speeds[$speed_item_key]['speed_sum'] += $speed;
                    $speeds[$speed_item_key]['average_speed'] = round($speeds[$speed_item_key]['speed_sum'] / $speeds[$speed_item_key]['speed_items']);
                }
            }
        }
        else {
            if (!empty($speed_item) && !isset($speed_item['end'])) {
                $speeds[$speed_item_key]['time'] += $time;
                $speeds[$speed_item_key]['end'] = $cord_item['time'];
                $speeds[$speed_item_key]['average_speed'] = round($speeds[$speed_item_key]['speed_sum'] / $speeds[$speed_item_key]['speed_items']);
            }
        }
    }

    private function prepareData() {
        $cur_geofences = [];
        $items_count = count($this->items);
        $item_nr = 0;
        $this->drivers_arr = array_reverse($this->drivers_arr);
        foreach ($this->items as &$item) {
            $item_nr++;
            reset($item['items']);
            $first_key = key($item['items']);
            end($item['items']);
            $last_key = key($item['items']);

            if ($item['fuel_consumption'] < 0)
                $item['fuel_consumption'] = 0;

            $item['driver'] = NULL;
            if (!empty($this->drivers_arr)) {
                if (isset($this->cords[$last_key]))
                    $it_time = $this->cords[$last_key]['raw_time'];
                else
                    $it_time = $item['raw_time'];

                foreach ($this->drivers_arr as $driver) {
                    if (strtotime($it_time) < strtotime(tdate(date('Y-m-d H:i:s', $driver->date), $this->timezone)))
                        break;

                    $item['driver'] = $this->api ? $driver : $driver->name;
                }

                if (empty($item['driver']))
                    $item['driver'] = $this->api ? $driver : $driver->name;
            }

            $this->distance_sum += $item['distance'];
            if ($item['status'] == 1) {
                # Moving duration, distance, top speed, average speed
                $this->move_duration += $item['time'];
                $item['top_speed'] = 0;
                $item['overspeeds'] = [];
                $item['underspeeds'] = [];
                $speed_sum = 0;
                $speed_items = 0;
                $last_item = NULL;
                foreach ($item['items'] as $key => $value) {
                    # Moving duration top speed
                    $cord_item = $this->cords[$key];
                    $speed = $cord_item['speed'];
                    $speed > $item['top_speed'] && $item['top_speed'] = $speed;
                    if ($speed > 0) {
                        $speed_sum += $speed;
                        $speed_items++;
                    }

                    if ($this->getOverspeeds)
                        $this->getItemUnderOverspeeds('over', $item['overspeeds'], $key, $last_item);

                    if ($this->getUnderspeeds)
                        $this->getItemUnderOverspeeds('under', $item['underspeeds'], $key, $last_item);

                    $last_item = $cord_item;
                }

                $this->overspeeds_count += count($item['overspeeds']);
                $this->underspeeds_count += count($item['underspeeds']);

                $item['average_speed'] = $speed_sum > 0 ? round($speed_sum / $speed_items) : 0;

                # Start position
                $item['start_position'] = [
                    'address' => $this->getAddress($this->cords[$first_key]),
                    'lat' => $this->cords[$first_key]['lat'],
                    'lng' => $this->cords[$first_key]['lng']
                ];

                # Stop position
                $item['stop_position'] = [
                    'address' => $this->getAddress($this->cords[$last_key]),
                    'lat' => $this->cords[$last_key]['lat'],
                    'lng' => $this->cords[$last_key]['lng']
                ];

            }
            if ($item['status'] == 2) {
                # Stop duration
                $this->stop_duration += $item['time'];

                # Delete not needed item cords, keep first and last cords for routing
                foreach ($item['items'] as $item_id => $value) {
                    if ($item_id == $last_key || $item_id == $first_key)
                        continue;

                    //unset($item['items'][$item_id]);
                }

                # Stop position
                $item['stop_position'] = [
                    'address' => $this->getAddress($this->cords[$first_key]),
                    'lat' => $this->cords[$first_key]['lat'],
                    'lng' => $this->cords[$first_key]['lng']
                ];
            }

            if ($this->report_type == 18) {
                foreach ($item['items'] as $sitem => $val) {
                    $cord = $this->cords[$sitem];
                    $arr = $this->getGeofencesNameArr($cord);
                    $in = array_diff_key($arr, $cur_geofences);
                    $out = array_diff_assoc($cur_geofences, $arr);
                    if (!empty($out)) {
                        $item['zones'][] = [
                            'type' => 'out',
                            'time' => $cord['time'],
                            'lat' => $cord['lat'],
                            'lng' => $cord['lng'],
                            'zones' => $out
                        ];
                    }
                    if (!empty($in)) {
                        $item['zones'][] = [
                            'type' => 'in',
                            'time' => $cord['time'],
                            'lat' => $cord['lat'],
                            'lng' => $cord['lng'],
                            'zones' => $in
                        ];
                    }
                    $cur_geofences = $arr;
                }
            }

            if ($item_nr == $items_count)
                $item['left'] = $this->cords[$last_key]['time'];

            $item['distance'] = float($item['distance']);
            $item['time_seconds'] = $item['time'];
            $item['time'] = secondsToTime($item['time']);
            if ($this->api && isset($item['items'])) {
                $items_arr = [];
                foreach ($item['items'] as $key => $value) {
                    if (isset($this->cords[$key])) {
                        $this->cords[$key]['speed'] = intval($this->cords[$key]['speed']);
                        array_push($items_arr, $this->cords[$key]);
                    }
                }

                $item['items'] = $items_arr;
            }

            if ($this->use_geofences && ($this->report_type == 3 || $this->report_type == 18)) {
                $item['geofences'] = [];
                foreach ($item['items'] as $skey => $sitem) {
                    $item['geofences'] = array_replace($item['geofences'], $this->getGeofencesNameArr($this->cords[$skey]));
                }

                $item['geofences'] = count($item['geofences']) > 1 ? substr(implode(', ', $item['geofences']), 0, -2) : current($item['geofences']);
            }
        }

        if (!is_null($this->odometer_sensor_id)) {
            $od_sensor = $this->sensors[$this->odometer_sensor_id];
            if ($od_sensor['odometer_value_by'] == 'virtual_odometer') {
                $this->odometer = $od_sensor['odometer_value'].' '.$od_sensor['unit_of_measurement'];
            }
            else {
                $sensor_id = 'sensor_'.$od_sensor['id'];
                $this->odometer = (isset($this->last_sensor_value[$sensor_id]) ? $this->last_sensor_value[$sensor_id] : 0).' '.$od_sensor['unit_of_measurement'];
            }
        }

        if (!empty($this->sensors))
        {
            foreach ($this->sensors as $key => $sensor) {
                $sensor_id = 'sensor_'.$sensor['id'];

                if ($sensor['type'] != 'odometer')
                    continue;

                if (empty($this->sensors_values[$sensor_id]))
                    continue;

                $first_value = reset($this->sensors_values[$sensor_id]);
                $end_value   = end($this->sensors_values[$sensor_id]);

                $this->odometers[] = [
                    'name'  => $sensor['name'],
                    'value' => round($end_value['v'] - $first_value['v']) . ' ' . $sensor['unit_of_measurement'],
                ];
            }
        }

        reset($this->cords);
        $this->route_start = current($this->cords)['time'];
        $this->route_end = end($this->cords)['time'];

        foreach($this->fuel_consumption as &$fuel_consumption)
        {
            if ($fuel_consumption < 0)
                $fuel_consumption = 0;
        }

        $this->average_speed = $this->speed_sum > 0 ? round($this->speed_sum / $this->speed_items) : 0;
        $this->top_speed = round($this->top_speed);
        $this->distance_sum = float($this->distance_sum);
        $this->move_duration = secondsToTime($this->move_duration);
        $this->stop_duration = secondsToTime($this->stop_duration);
        if (!empty($this->engine_hours_type)) {
            if ($this->engine_hours_type['engine_hours'] == 'engine_hours') {
                $this->engine_idle = secondsToTime($this->engine_hours - $this->engine_work);
                $this->engine_hours = secondsToTime($this->engine_hours);
                $this->engine_work = secondsToTime($this->engine_work);
            }
            else {
                $this->engine_hours = secondsToTime($this->engine_work + $this->engine_idle);
                $this->engine_work = secondsToTime($this->engine_work);
                $this->engine_idle = secondsToTime($this->engine_idle);
            }
        }
    }

    private function countEngineHours($last_item) {
        $last_engine_status = $this->engine_status;
        if ($this->engine_hours_type['engine_hours'] == 'gps') {
            if ($this->time > 300)
                return;

            $this->sumEngineWork($this->item['speed'], $last_item['speed']);
        }
        elseif ($this->engine_hours_type['engine_hours'] == 'engine_hours') {
            if (!isset($this->engine_hours_type['engine_hours_sensor']))
                return;

            //$engine_hours = getSensorValueRaw($this->item['other'], $this->engine_hours_type['engine_hours_sensor']);
            $engine_hours = $this->engine_hours_type['engine_hours_sensor']->getValueRaw($this->item['other']);
            if (!is_null($engine_hours));
            $this->engine_hours = $engine_hours;

            # Engine work
            if ($this->engine_hours_type['detect_engine'] == 'gps') {
                if ($this->time > 300)
                    return;

                $this->sumEngineWork($this->item['speed'], $last_item['speed']);
            }
            else {
                if (!isset($this->engine_hours_type['detect_engine_sensor']))
                    return;

                $engine = $this->engine_hours_type['detect_engine_sensor']->getValue($this->item['other'], false, null);
                if (!is_null($engine))
                    $this->engine_status = $engine;

                if (!$last_engine_status)
                    return;

                $this->sumEngineWork($this->item['speed'], $last_item['speed']);

            }
        }
        else {
            if (!isset($this->engine_hours_type['engine_hours_sensor']))
                return;

            $engine = $this->engine_hours_type['engine_hours_sensor']->getValue($this->item['other'], false, null);
            if (!is_null($engine))
                $this->engine_status = $engine;

            if (!$last_engine_status)
                return;

            $this->sumEngineWork($this->item['speed'], $last_item['speed']);
        }
    }

    private function sumEngineWork($speed, $last_speed) {
        $this->getLastItem();

        if ($speed > 0) {
            $this->engine_work += $this->time;
            $this->items[$this->last_key]['engine_work'] += $this->time;
        }

        if ($last_speed <= 0 && $speed <= 0) {
            $this->engine_idle += $this->time;
            $this->items[$this->last_key]['engine_idle'] += $this->time;
        }
    }
}