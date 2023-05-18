<?php namespace ModalHelpers;

ini_set('memory_limit', env('REPORT_MEMORY_LIMIT', '2048M'));
set_time_limit(18000);

use Facades\Repositories\DeviceRepo;
use Facades\Repositories\EventRepo;
use Facades\Repositories\GeofenceRepo;
use Facades\Repositories\ReportRepo;
use Facades\Repositories\TimezoneRepo;
use Facades\Repositories\TraccarPositionRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\ReportFormValidator;
use Facades\Validators\ReportSaveFormValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;
use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Helpers\ReportHelper;

use Carbon\Carbon;
use TCPDF;


class ReportModalHelper extends ModalHelper
{
    private $types = [];

    function __construct()
    {
        parent::__construct();

        $this->types = [			
            '1' => trans('front.general_information'),
            '2' => trans('front.general_information_merged'),
            '16' => trans('front.general_information_merged_custom'),
			'25' => trans('front.object_history'), 
            '3' => trans('front.drives_and_stops'),
            '19' => trans('front.drives_and_stops').' / '.trans('front.drivers'),
            '18' => trans('front.drives_and_stops').' / '.trans('front.geofences'),
            //'21' => trans('front.drives_and_stops').' / '.trans('front.drivers') . ' (Business)',
            //'22' => trans('front.drives_and_stops').' / '.trans('front.drivers') . ' (Private)',
            '4' => trans('front.travel_sheet'),
            '5' => trans('front.overspeeds'),
            //'6' => trans('front.underspeeds'),
            '7' => trans('front.geofence_in_out'),
            '15' => trans('front.geofence_in_out_24_mode'),
            '20' => trans('front.geofence_in_out').' ('.trans('front.ignition_on_off').')',
            '28' => trans('front.geofence_in_out').' (Shift)',
            '8' => trans('front.events'),
            /*'9' => trans('front.service'),*/
            '10' => trans('front.fuel_level'),
            '11' => trans('front.fuel_fillings'),
            '12' => trans('front.fuel_thefts'),
            '13' => trans('front.temperature'),
            '14' => trans('front.rag'),
            '23' => trans('front.rag').' / '.trans('front.seatbelt'),
            //'24' => 'Birla ' . trans('global.custom'),            
            //'26' => trans('front.object_history'),
            //'27' => 'Automon ' . trans('global.custom'),
            '29' => trans('front.engine_hours') . ' ' . trans('validation.attributes.daily'),
            '30' => trans('front.ignition_on_off'),
        ];
    }

    public function get()
    {
        $this->checkException('reports', 'view');

        $reports = ReportRepo::searchAndPaginate(['filter' => ['user_id' => $this->user->id]], 'id', 'desc', 10);
        $types = $this->types;

        if ($this->api) {
            $reports = $reports->toArray();
            $reports['url'] = route('api.get_reports');
            foreach ($reports['data'] as &$item) {
                $item['devices'] = array_pluck($item['devices'], 'id');
                $item['geofences'] = array_pluck($item['geofences'], 'id');
            }
            $new_arr = [];
            foreach ($types as $id => $title) {
                array_push($new_arr, ['id' => $id, 'title' => $title]);
            }
            $types = $new_arr;
        }

        return compact('reports', 'types');
    }

    public function createData()
    {
        $this->checkException('reports', 'create');
        //debugar(true,"olá");
        $devices = UserRepo::getDevices($this->user->id)
            ->filter(function ($device) {
                return $device['expiration_date'] == '0000-00-00' || strtotime($device['expiration_date']) >= strtotime(date('Y-m-d'));
            })
            ->pluck('plate_number', 'id')
            ->all();


            //dd($devices);
        //debugar(true,$devices);

        if (empty($devices))
            return $this->api ? ['status' => 0, 'errors' => ['id' => trans('front.no_devices')]] : modal(trans('front.no_devices'), 'alert');

        $geofences = GeofenceRepo::getWhere(['user_id' => $this->user->id]);

        $formats = [
            'html' => trans('front.html'),
            'xls' => trans('front.xls'),
            'pdf' => trans('front.pdf'),
            'pdf_land' => trans('front.pdf_land'),
        ];
        
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobi') !== false) {
            // usuário está usando um celular, então definimos PDF como a primeira opção
            $formats = [
                'pdf' => trans('front.pdf'),
                'pdf_land' => trans('front.pdf_land'),
                'xls' => trans('front.xls'),
            ];
        }
        

        $stops = [
            '1' => '> 1 '.trans('front.minute_short'),
            '2' => '> 2 '.trans('front.minute_short'),
            '5' => '> 5 '.trans('front.minute_short'),
            '10' => '> 10 '.trans('front.minute_short'),
            '20' => '> 20 '.trans('front.minute_short'),
            '30' => '> 30 '.trans('front.minute_short'),
            '60' => '> 1 '.trans('front.hour_short'),
            '120' => '> 2 '.trans('front.hour_short'),
            '300' => '> 5 '.trans('front.hour_short'),
        ];

        $filters = [
            '0' => '',
            '1' => trans('front.today'),
            '2' => trans('front.yesterday'),
            '3' => trans('front.before_2_days'),
            '4' => trans('front.before_3_days'),
            '5' => trans('front.this_week'),
            '6' => trans('front.last_week'),
            '7' => trans('front.this_month'),
            '8' => trans('front.last_month'),
        ];

        $types = $this->types;
        $types_list = $this->types;

        if ( ! settings('plugins.business_private_drive.status') ) {
            unset( $types_list['21'], $types_list['22'] );
        }

        if ( ! settings('plugins.birla_report.status') ) {
            unset( $types_list['24'] );
        }

        if ( ! settings('plugins.object_history_report.status') ) {
            unset( $types_list['25'] );
        }

        if ( ! settings('plugins.automon_report.status') ) {
            unset( $types_list['27'] );
        }

        if ($this->api) {
            $formats = apiArray($formats);
            $stops = apiArray($stops);
            $filters = apiArray($filters);
            $types = apiArray($types);
        }

        $reports = ReportRepo::searchAndPaginate(['filter' => ['user_id' => $this->user->id]], 'id', 'desc', 10);
        $reports->setPath(route('reports.index'));

        if ($this->api) {
            $reports = $reports->toArray();
            $reports['url'] = route('api.get_reports');
            $geofences = $geofences->toArray();
            $devices = array_values( $devices->all() );
        }

        return compact('devices', 'geofences', 'formats', 'stops', 'filters', 'types', 'types_list', 'reports');
    }

    public function create()
    {
        if (empty($this->data['id']))
            $this->checkException('reports', 'store');
        else
            $this->checkException('reports', 'update', ReportRepo::find($this->data['id']));

        try
        {

            if ($this->api) {
                if (isset($this->data['devices']) && !is_array($this->data['devices']))
                    $this->data['devices'] = json_decode($this->data['devices'], TRUE);

                if (isset($this->data['geofences']) && !is_array($this->data['geofences']))
                    $this->data['geofences'] = json_decode($this->data['geofences'], TRUE);
            }

            $this->validate($this->data);

            ReportSaveFormValidator::validate('create', $this->data);

            $now = Carbon::parse( tdate(date('d-m-Y H:i:s'), NULL, FALSE, 'd-m-Y') );
            $days = $now->diffInDays( Carbon::parse( $this->data['date_from'] ) , false);
            $this->data['from_format'] = $days . ' days ' . (empty($this->data['from_time']) ? '00:00' : $this->data['from_time']);
            $days = $now->diffInDays( Carbon::parse( $this->data['date_to'] ) , false);
            $this->data['to_format'] = $days . ' days ' . (empty($this->data['to_time']) ? '00:00' : $this->data['to_time']);

            if ( ! $this->api ) {
                $this->data['date_from'] .= ' ' . (empty($this->data['from_time']) ? '00:00' : $this->data['from_time']);
                $this->data['date_to']   .= ' ' . (empty($this->data['to_time']) ? '00:00' : $this->data['to_time']);
            }

            $this->data['email'] = $this->data['send_to_email'];

            $daily_time = '00:00';
            if (isset($this->data['daily_time']) && preg_match("/(2[0-4]|[01][1-9]|10):([0-5][0-9])/", $this->data['daily_time']))
                $daily_time = $this->data['daily_time'];

            $this->data['daily_time'] = $daily_time;

            $weekly_time = '00:00';
            if (isset($this->data['weekly_time']) && preg_match("/(2[0-4]|[01][1-9]|10):([0-5][0-9])/", $this->data['weekly_time']))
                $weekly_time = $this->data['weekly_time'];

            $this->data['weekly_time'] = $weekly_time;

            if ( !empty($this->data['id']) && empty(ReportRepo::find($this->data['id'])) ) {
                unset($this->data['id']);
            }

            if (empty($this->data['id']))
                $item = ReportRepo::create($this->data + [
                        'user_id' => $this->user->id,
                        'daily_email_sent' => date('d-m-Y', strtotime('-1 day')),
                        'weekly_email_sent' => date("d-m-Y",strtotime('monday this week'))
                    ]);
            else {
                $item = ReportRepo::findWhere(['id' => $this->data['id'], 'user_id' => $this->user->id]);
                if (!empty($item))
                    ReportRepo::update($item->id, $this->data);
            }

            if (!empty($item)) {
                if (isset($this->data['devices']) && is_array($this->data['devices']) && !empty($this->data['devices']))
                    $item->devices()->sync($this->data['devices']);

                if (isset($this->data['geofences']) && is_array($this->data['geofences']) && !empty($this->data['geofences']))
                    $item->geofences()->sync($this->data['geofences']);
            }
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }

        return ['status' => $this->api ? 1 : 2];
    }

    public function generate($data = NULL)
    {
        $this->checkException('reports', 'view');

        if (is_null($data))
            $data = $this->data;

        try
        {
            ReportFormValidator::validate('create', $this->data);

            $data['date_from'] .= ( empty($data['from_time']) ? '' : ' ' . $data['from_time']);
            $data['date_to']   .= ( empty($data['to_time']) ? '' : ' ' . $data['to_time']);

            $this->validate($data);

            if (!isset($data['generate'])) {
                unset($data['_token']);
                unset($data['from_time']);
                unset($data['to_time']);
                return ['status' => 3, 'url' => route($this->api ? 'api.generate_report' : 'reports.update').'?'.http_build_query($data + ['generate' => 1], '', '&')];
            }

            $timezones = TimezoneRepo::getList();
            $items = [];

            $data['unit_of_distance'] = $this->user->unit_of_distance;
            $data['unit_of_altitude'] = $this->user->unit_of_altitude;
            $data['user_id'] = $this->user->id;
            $data['logo'] = 1;
            $data['lang'] = $this->user->lang;
            require(base_path('Tobuli/Helpers/Arabic.php'));
            $data['arabic'] = new \I18N_Arabic('Glyphs');

            $report_name = mb_convert_encoding($this->types[$data['type']].'_'.$data['date_from'].'_'.$data['date_to'].'_'.$data['user_id'].'_'.time(), 'ASCII');
            $report_name = strtr($report_name, [
                ' ' => '_',
                '-' => '_',
                ':' => '_',
                '/' => '_',
                "\n" => '',
                "\r" => '',
            ]);

            # Devices
            $devices = DeviceRepo::getWhereInWith($data['devices'], 'id', ['sensors', 'users']);

            # User geofences
            if ($data['type'] != 7 && $data['type'] != 15 && $data['type'] != 20 && $data['type'] != 28)
                $geofences = GeofenceRepo::getWhere(['user_id' => $data['user_id']]);
            else
                $geofences = GeofenceRepo::getWhereIn($data['geofences']);

            $reportHelper = new ReportHelper($data, $geofences);

            foreach ($devices as $device)
            {
                $timezone = $this->user->timezone->zone;
                $reportHelper->setData([
                    'zone' => $timezone
                ]);
                $date_from = tdate($data['date_from'], timezoneReverse($timezone));
                $date_to = tdate($data['date_to'], timezoneReverse($timezone));


                $reportHelper->data['stop_speed'] = $device->min_moving_speed;
                if ($data['type'] == 7) { # Geofence in/out
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateGeofences($items_result, $date_from, $date_to);

                    unset($items_result);
                }
                elseif ($data['type'] == 8) { # Events
                    $items_result = EventRepo::getBetween($this->user->id, $device->id, $date_from, $date_to);
                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateEvents($items_result->toArray());

                    unset($items_result);
                }
                elseif ($data['type'] == 14) { # RAG
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $driver_history = getDevicesDrivers($data['user_id'], $device->id, $date_from, $date_to, '>=', NULL, TRUE);
                    $last_dr = getDevicesDrivers($data['user_id'], $device->id, $date_from, NULL, '<=', 1);
                    if (!empty($last_dr)) {
                        if (!is_array($driver_history))
                            $driver_history = [];

                        $last_dr = end($last_dr);
                        $driver_history[] = $last_dr;
                    }

                    $rag_sensors = $device->sensors->filter(function ($sensor) {
                        return in_array($sensor->type, ['harsh_acceleration', 'harsh_breaking']);
                    });

                    $items[$device->id] = $reportHelper->generateRag($items_result, $driver_history, $device, $rag_sensors, $date_from, $date_to);
                }
                elseif ($data['type'] == 23) { # RAG Seatbelt
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $driver_history = getDevicesDrivers($data['user_id'], $device->id, $date_from, $date_to, '>=', NULL, TRUE);
                    $last_dr = getDevicesDrivers($data['user_id'], $device->id, $date_from, NULL, '<=', 1);
                    if (!empty($last_dr)) {
                        if (!is_array($driver_history))
                            $driver_history = [];

                        $last_dr = end($last_dr);
                        $driver_history[] = $last_dr;
                    }

                    $rag_sensors = $device->sensors->filter(function ($sensor) {
                        return in_array($sensor->type, ['harsh_acceleration', 'harsh_breaking', 'seatbelt']);
                    });

                    $items[$device->id] = $reportHelper->generateRagSeatBelt($items_result, $driver_history, $device, $rag_sensors, $date_from, $date_to);
                }
                elseif ($data['type'] == 15) { # Geofence in/out 24 mode
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateGeofences24($items_result, $date_from, $date_to);

                    unset($items_result);
                }
                elseif ($data['type'] == 16) {
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateGeneralCustom($items_result, $date_from, $date_to, $device, $device->sensors);
                    unset($items_result);
                }
                elseif ($data['type'] == 20) { # Geofence in/out engine on/off
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateGeofencesEngine($items_result, $date_from, $date_to, $device, $device->sensors);

                    unset($items_result);
                }
                elseif ($data['type'] == 24) { # Birla Custom
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $items[$device->id] = $reportHelper->generateBirlaCustom($items_result, $date_from, $date_to, $device);

                    unset($items_result);
                }
                elseif ($data['type'] == 25) { # Object history

                    $items[$device->id] = $reportHelper->generateObjectHistory($date_from, $date_to, $device, $this->user);

                    if (empty($data['parameters']))
                        $data['parameters'] = [];

                    $data['parameters'] = array_unique($data['parameters'] + $items[$device->id]['parameters']);
                }
                elseif ($data['type'] == 27) { # Automon Custom
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $items[$device->id] = $reportHelper->generateAutomonCustom($items_result, $date_from, $date_to, $device);

                    unset($items_result);
                }
                elseif ($data['type'] == 28) { # Geofence Shift
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $items[$device->id] = $reportHelper->generateGeofencesShift($items_result, $date_from, $date_to, $data['parameters']);

                    unset($items_result);
                }
                elseif ($data['type'] == 29) { # Engine hours 24
                    $items_result = TraccarPositionRepo::searchObj($device->traccar_device_id, $date_from, $date_to);

                    $items[$device->id] = $reportHelper->generateEngineHours24($items_result);

                    unset($items_result);
                }
                elseif ($data['type'] == 30) { # Ignition on/off
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    $driver_history = getDevicesDrivers($data['user_id'], $device->id, $date_from, $date_to, '>=', NULL, TRUE);
                    $last_dr = getDevicesDrivers($data['user_id'], $device->id, $date_from, NULL, '<=', 1);
                    if (!empty($last_dr)) {
                        if (!is_array($driver_history))
                            $driver_history = [];

                        $last_dr = end($last_dr);
                        $driver_history[] = $last_dr;
                    }

                    if (!empty($items_result))
                        $items[$device->id] = $reportHelper->generateIgnitionOnOff(
                            $items_result, $date_from, $date_to, $device, $device->sensors, $driver_history);

                    unset($items_result);
                }
                else {
                    $items_result = TraccarPositionRepo::searchWithSensors($this->user->id, $device->traccar_device_id, $date_from, $date_to);

                    if (!empty($items_result)) {
                        $engine_status = $device->getEngineStatusFrom($date_from);

                        $sensors = NULL;
                        $driver_history = NULL;

                        if (in_array($data['type'], [1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 18, 19, 21, 22])) {
                            # Odometer
                            if (count($device->sensors)) {
                                foreach ($device->sensors as $key => $sensor) {
                                    if ($sensor['type'] == 'odometer') {
                                        if ($sensor['odometer_value_by'] == 'virtual_odometer') {
                                            $result = TraccarPositionRepo::sumDistanceHigher($device->traccar_device_id, $date_to)->sum;
                                            $sensor['odometer_value'] = round($sensor['odometer_value'] - $result);
                                        }
                                    }
                                    $sensors[] = $sensor;
                                }
                            }
                        }

                        if (in_array($data['type'], [1, 2, 3, 4, 10, 11, 12, 13, 14, 18, 19, 21, 22])) {
                            $driver_history = getDevicesDrivers($data['user_id'], $device->id, $date_from, $date_to, '>=', NULL, TRUE);
                            $last_dr = getDevicesDrivers($data['user_id'], $device->id, $date_from, NULL, '<=', 1);
                            if (!empty($last_dr)) {
                                if (!is_array($driver_history))
                                    $driver_history = [];

                                $last_dr = end($last_dr);
                                $driver_history[] = $last_dr;
                            }
                        }

                        $items[$device->id] = $reportHelper->generate($items_result, $sensors, $driver_history, $device, $date_from, $date_to, $engine_status);

                        if (in_array($data['type'], [3, 18]))
                            $items[$device->id]->events = EventRepo::getBetweenCount($data['user_id'], $device->id, $date_from, $date_to);
                    }

                    unset($items_result);
                }
            }

            unset($reportHelper);

            $arr = [];
            foreach ($devices as $device) {
                $arr[$device->id] = $device->toArray();
            }
            $devices = $arr;
            unset($arr);


            if (in_array($data['type'], [19, 21, 22])) {
                $arr = [
                    'items' => [],
                    'devices' => $devices,
                    'data' => $data
                ];

                foreach ($items as $device_id => $item) {
                    foreach ($item->getItems() as $it) {
                        $arr['items'][$it['driver']]['items'][strtotime($it['raw_time'])] = $it + ['device' => $device_id];
                        if (!array_key_exists('total', $arr['items'][$it['driver']])) {
                            $arr['items'][$it['driver']]['total'] = [
                                'drive' => 0,
                                'stop' => 0,
                                'distance' => 0,
                                'fuel' => 0,
                                'engine_work' => 0,
                                'engine_idle' => 0
                            ];
                        }
                        $arr['items'][$it['driver']]['total']['distance'] += $it['distance'];
                        $arr['items'][$it['driver']]['total']['fuel'] += $it['fuel_consumption'];
                        $arr['items'][$it['driver']]['total']['engine_work'] += $it['engine_work'];
                        $arr['items'][$it['driver']]['total']['engine_idle'] += $it['engine_idle'];
                        if ($it['status'] == 1) {
                            $arr['items'][$it['driver']]['total']['drive'] += $it['time_seconds'];
                        }
                        elseif ($it['status'] == 2) {
                            $arr['items'][$it['driver']]['total']['stop'] += $it['time_seconds'];
                        }

                        if ( empty($arr['items'][$it['driver']]['total']['fuel_sensor']) ) {
                            $fuel_sensor_id = null;

                            if (isset($item->fuel_consumption) && is_array($item->fuel_consumption)) {
                                reset($item->fuel_consumption);
                                $fuel_sensor_id = key($item->fuel_consumption);
                            }

                            if ( isset($item->sensors_arr[$fuel_sensor_id]) ) {
                                $arr['items'][$it['driver']]['total']['fuel_sensor'] = $item->sensors_arr[$fuel_sensor_id];
                            }
                        }
                    }


                }
                $items = $arr;
            }

            $types = $this->types;


            if ($data['format'] == 'html') {
                $type = $data['type'] == 13 ? 10 : $data['type'];
                if ($data['type'] == 13 || $data['type'] == 10)
                    $data['sensors_var'] = $data['type'] == 13 ? 'temperature_sensors' : 'fuel_tank_sensors';

                $html = view('front::Reports.parse.type_'.$type)->with(compact('devices', 'items', 'types', 'data'))->render();
                header('Content-disposition: attachment; filename="'.utf8_encode($report_name).'.html"');
                header('Content-type: text/html');
                echo $html;
            }
            elseif ($data['format'] == 'pdf' || $data['format'] == 'pdf_land') {
                //UILMO FAZER MELHORIA NA QUESTÃO DE GERAR O PDF,PODE ATÉ ENVIAR OS DADOS PARA O SERVIDOR bd E FAZER A GERAÇÃO POR LÁ
                $stop = false;
                $change_page_size = ($data['format'] == 'pdf_land');
                $tries = 1;
                while (!$stop) {
                    try {
                        if ($change_page_size)
                            $pdf = PDF::loadView('front::Reports.parse.type_'.$data['type'], compact('devices', 'items', 'types', 'data'))->setPaper(array(0,0,950,950), 'landscape');
                        else
                            $pdf = PDF::loadView('front::Reports.parse.type_'.$data['type'], compact('devices', 'items', 'types', 'data'));

                        return $pdf->stream($report_name.'.pdf');
                    }
                    catch (\Exception $e) {
                        if ($e instanceof \DOMPDF_Exception && $e->getMessage() == 'Frame not found in cellmap') {
                            $change_page_size = TRUE;
                        } else {
                            Bugsnag::notifyException($e);
                        }

                        $tries++;
                        if ($tries > 2)
                            $stop = TRUE;
                        sleep(1);
                    }
                }
                return 'Desculpe não poder gerar o RELATÓRIO!';
            }
            elseif ($data['format'] == 'xls') {
                try {
                    Excel::create($report_name, function($excel) use ($items, $devices, $types, $data) {
                        $excel->sheet('Report', function($sheet) use ($items, $devices, $types, $data) {
                            $sheet->loadView('front::Reports.parse.type_'.$data['type'], compact('devices', 'items', 'types', 'data'));
                        });
                    })->export('xls');
                }
                catch(\Exception $e) {
                    Bugsnag::notifyException($e);
                    return 'Sorry can\'t generate, too mutch data.';
                }
            }
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    public function doDestroy($id)
    {
        $item = ReportRepo::find($id);

        $this->checkException('reports', 'remove', $item);

        return compact('item');
    }

    public function destroy()
    {
        $id = array_key_exists('report_id', $this->data) ? $this->data['report_id'] : $this->data['id'];

        $item = ReportRepo::find($id);

        $this->checkException('reports', 'remove', $item);

        ReportRepo::delete($id);

        return ['status' => 1];
    }

    public function getType($type)
    {
        $types = $this->getTypes();

        $filtered = array_filter($types, function($value) use ($type){
            return $value['type'] == $type;
        });

        if (empty($filtered))
            throw new \Exception('Not found');

        return reset($filtered);
    }

    public function getTypes()
    {
        $fields = ['geofences', 'speed_limit', 'stops', 'show_addresses', 'zones_instead'];

        $types = [];

        foreach ($this->types as $type => $name)
        {
            $types[$type] = [
                'type' => $type,
                'name' => $name,
                'formats' => [
                    'html', 'xls', 'pdf', 'pdf_land'
                ],
                'fields' => $fields
            ];

            switch ($type) {
                case "1":
                case "2":
                case "16":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'show_addresses', 'zones_instead']);
                    break;
                case "3":
                case "4":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'speed_limit']);
                    break;
                case "5":
                case "6":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'stops']);
                    break;
                case "7":
                    $types[$type]['fields'] = array_diff($fields, ['speed_limit', 'stops']);
                    break;
                case "8":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'speed_limit', 'stops']);
                    break;
                case "9":
                case "29":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'speed_limit', 'stops', 'show_addresses', 'zones_instead']);
                    break;
                case "10":
                case "11":
                case "12":
                case "13":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'speed_limit', 'stops']);
                    if (in_array($type, [10, 13]))
                        $types[$type]['formats'] = ['html'];

                    break;
                case "14":
                case "23":
                    $types[$type]['fields'] = array_diff($fields, ['geofences', 'stops', 'show_addresses', 'zones_instead']);
                    break;
                case "18":
                    $types[$type]['fields'] = array_diff($fields, ['stops']);
                    break;
				case "19":
				$types[$type]['fields'] = array_diff($fields, ['geofences']);
				break;
                case "28":
                    $types[$type]['fields'] = array_diff($fields, ['speed_limit', 'stops', 'show_addresses', 'zones_instead']);

                    $types[$type]['parameters'] = [
                        [
                            'title' => trans('validation.attributes.shift_start'),
                            'name'  => 'shift_start',
                            'type'  => 'select',
                            'default' => '08:00',
                            'options' => toOptions(config('tobuli.history_time')),
                            'validation' => 'required|date_format:H:i'
                        ],
                        [
                            'title' => trans('validation.attributes.shift_finish'),
                            'name'  => 'shift_finish',
                            'type'  => 'select',
                            'default' => '17:00',
                            'options' => toOptions(config('tobuli.history_time')),
                            'validation' => 'required|date_format:H:i'
                        ],
                        [
                            'title' => trans('validation.attributes.shift_start_tolerance'),
                            'name'  => 'shift_start_tolerance',
                            'type'  => 'select',
                            'options' => toOptions(config('tobuli.minutes')),
                            'validation' => 'required|integer'
                        ],
                        [
                            'title' => trans('validation.attributes.shift_finish_tolerance'),
                            'name'  => 'shift_finish_tolerance',
                            'type'  => 'select',
                            'options' => toOptions(config('tobuli.minutes')),
                            'validation' => 'required|integer'
                        ],
                        [
                            'title' => trans('validation.attributes.excessive_exit'),
                            'name'  => 'excessive_exit',
                            'type'  => 'integer',
                            'default' => 10,
                            'validation' => 'required|integer'
                        ],
                    ];

                    break;
                case "30":
                    $types[$type]['fields'] = array_diff($fields, ['speed_limit', 'stops', 'zones_instead', 'geofences']);

                    $types[$type]['parameters'] = [
                        [
                            'title' => trans('front.ignition_off'),
                            'name'  => 'ignition_off',
                            'type'  => 'select',
                            'default' => 1,
                            'options' => toOptions([
                                '1' => '> 1 '.trans('front.minute_short'),
                                '2' => '> 2 '.trans('front.minute_short'),
                                '5' => '> 5 '.trans('front.minute_short'),
                                '10' => '> 10 '.trans('front.minute_short'),
                                '20' => '> 20 '.trans('front.minute_short'),
                                '30' => '> 30 '.trans('front.minute_short'),
                                '60' => '> 1 '.trans('front.hour_short'),
                                '120' => '> 2 '.trans('front.hour_short'),
                                '300' => '> 5 '.trans('front.hour_short'),
                            ]),
                            'validation' => 'required|integer'
                        ],
                    ];
                    break;
            }

            $types[$type]['fields'] = array_values($types[$type]['fields']);
        }

        return array_values($types);
    }

    public function validate( & $data)
    {
        $validator = Validator::make($data, ['type' => 'required']);
        if ($validator->fails())
            throw new ValidationException(['type' => $validator->errors()->first()]);

        if (empty($data['send_to_email']))
            $data['send_to_email'] = '';
        $arr['send_to_email'] = array_flip(explode(';', $data['send_to_email']));
        unset($arr['send_to_email']['']);
        $arr['send_to_email'] = array_flip($arr['send_to_email']);
        $arr['send_to_email'] = array_map('trim', $arr['send_to_email']);

        # Regenerate string
        $data['send_to_email'] = implode(';', $arr['send_to_email']);

        $validator = Validator::make($arr, ['send_to_email' => 'array_max:5']);
        $validator->each('send_to_email', ['email']);
        if ($validator->fails())
            throw new ValidationException(['send_to_email' => $validator->errors()->first()]);

        if (isset($data['daily']) || isset($data['weekly'])) {
            $validator = Validator::make($arr, ['send_to_email' => 'required']);
            if ($validator->fails())
                throw new ValidationException(['send_to_email' => $validator->errors()->first()]);
        }

        if (strtotime($data['date_from']) > strtotime($data['date_to'])) {
            $message = str_replace(':attribute', trans('validation.attributes.date_to'), trans('validation.after'));
            $message = str_replace(':date', trans('validation.attributes.date_from'), $message);
            throw new ValidationException(['date_to' => $message]);
        }

        if (in_array($data['type'], ['7', '15', '20', '28'])) {
            $validator = Validator::make($data, ['geofences' => 'required']);
            if ($validator->fails())
                throw new ValidationException(['geofences' => $validator->errors()->first()]);
        }

        if (in_array($data['type'], ['5', '6'])) {
            $validator = Validator::make($data, ['speed_limit' => 'required']);
            if ($validator->fails())
                throw new ValidationException(['speed_limit' => $validator->errors()->first()]);
        }

        if ($data['type'] == '25') {
            $validator = Validator::make($data, ['devices' => 'same_protocol']);
            if ($validator->fails())
                throw new ValidationException(['devices' => $validator->errors()->first()]);
        }

        $type = $this->getType($data['type']);

        if ( ! empty($type['parameters'])) {
            $parameters = [];
            $rules = [];
            foreach ($type['parameters'] as $parameter)
            {
                $parameters[] = $parameter['name'];

                if (empty($parameter['validation']))
                    continue;

                //html attribute name to validation name
                $name = preg_replace(['/\[\]/', '/\[([^\[\]]+)\]/'], ['.*', '.$1'], $parameter['name']);

                $rules[$name] = $parameter['validation'];
            }

            if ($rules) {
                $validator = Validator::make($data, $rules);
                if ($validator->fails())
                    throw new ValidationException($validator->errors());
            }

            $data['parameters'] = array_only($data, $parameters);
        }
    }
}