<?php namespace ModalHelpers;

use App\Exceptions\DeviceLimitException;
use Facades\ModalHelpers\SensorModalHelper;
use Facades\ModalHelpers\ServiceModalHelper;
use Facades\Repositories\DeviceFuelMeasurementRepo;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\DeviceIconRepo;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\DeviceSensorRepo;
use Facades\Repositories\EventRepo;
use Facades\Repositories\SensorGroupRepo;
use Facades\Repositories\SensorGroupSensorRepo;
use Facades\Repositories\TimezoneRepo;
use Facades\Repositories\TraccarDeviceRepo;
use Facades\Repositories\UserDriverRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\DeviceFormValidator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Tobuli\Entities\Device;
use Tobuli\Entities\DeviceIcon;
use Tobuli\Exceptions\ValidationException;
use App\Exceptions\ResourseNotFoundException;
use App\Exceptions\PermissionException;
use App\Monitoring;

class DeviceModalHelper extends ModalHelper
{
    private $device_fuel_measurements = [];

    public function __construct()
    {
        parent::__construct();

        $this->device_fuel_measurements = [
            [
                'id' => 1,
                'title' => trans('front.l_km'),
                'fuel_title' => strtolower(trans('front.liter')),
                'distance_title' => trans('front.kilometers'),
            ],
            [
                'id' => 2,
                'title' => trans('front.mpg'),
                'fuel_title' => strtolower(trans('front.gallon')),
                'distance_title' => trans('front.miles'),
            ]
        ];
    }

    public function createData() {

        if (request()->get('perm') == null || (request()->get('perm') != null && request()->get('perm') != 1)) {
            if (request()->get('perm') != null && request()->get('perm') != 2) {
                if ($this->checkDevicesLimit())
                    throw new DeviceLimitException();
            }

            $this->checkException('devices', 'create');
        }

        $icons_type = [
            'arrow' => trans('front.arrow'),
            'rotating' => trans('front.rotating_icon'),
            'icon' => trans('front.icon')
        ];

        $device_icon_colors = [
            'green'  => trans('front.green'),
            'yellow' => trans('front.yellow'),
            'red'    => trans('front.red'),
            'blue'   => trans('front.blue'),
            'orange' => trans('front.orange'),
            'black'  => trans('front.black'),
        ];
        $device_icons = DeviceIconRepo::getMyIcons($this->user->id);
        $device_icons_grouped = [];

        foreach ($device_icons as $dicon) {
            if ($dicon['type'] == 'arrow')
                continue;

            if (!array_key_exists($dicon['type'], $device_icons_grouped))
                $device_icons_grouped[$dicon['type']] = [];

            $device_icons_grouped[$dicon['type']][] = $dicon;
        }

        $users = UserRepo::getUsers($this->user);
        $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();
        $expiration_date_select = [
            '0000-00-00' => trans('front.unlimited'),
            '1' => trans('validation.attributes.expiration_date')
        ];
        $timezones = ['0' => trans('front.default')] + TimezoneRepo::order()->lists('title', 'id')->all();
        $timezones_arr = [];
        foreach ($timezones as $key => &$timezone) {
            $timezone = str_replace('UTC ', '', $timezone);
            if ($this->api)
                array_push($timezones_arr, ['id' => $key, 'value' => $timezone]);
        }

        $sensor_groups = [];
        if (isAdmin()) {
            $sensor_groups = SensorGroupRepo::getWhere([], 'title');
            $sensor_groups = $sensor_groups->lists('title', 'id')->all();
        }

        $sensor_groups = ['0' => trans('front.none')] + $sensor_groups;

        $device_fuel_measurements = $this->device_fuel_measurements;

        $device_fuel_measurements_select =  [];
        foreach ($device_fuel_measurements as $dfm)
            $device_fuel_measurements_select[$dfm['id']] = $dfm['title'];

        if ($this->api) {
            $timezones = $timezones_arr;
            $device_groups = apiArray($device_groups);
            $sensor_groups = apiArray($sensor_groups);
            $users = $users->toArray();
        }

        return compact('device_groups', 'sensor_groups', 'device_fuel_measurements', 'device_icons', 'users', 'timezones', 'expiration_date_select', 'device_fuel_measurements_select', 'icons_type', 'device_icons_grouped', 'device_icon_colors');
    }

    public function create()
    {
        return null;
        /* $Monitorings = new Monitoring([
            'active' => $this->data['active'],
            'customer'=> '',
            'owner'=> '',
            'plate_number' => $this->data['plate_number'],
            'cause'=> '',
            'gps_date'=> '',
            'customer'=> '',
            'occurence_date'=> '',
            'modified_date'=> '',
            'tel'=> '',
            'made_contact'=> 'false',
            'information'=> '',
            'next_contact'=> '',
            'treated_occurence'=> 'false',
            'sent_maintenance'=> 'false',
            'automatic_treatment'=> 'false'
            ]);
        $Monitorings->save(); */
        
        /*$this->checkException('devices', 'store');

        if ($this->checkDevicesLimit())
            throw new DeviceLimitException();

        $this->data['imei'] = isset($this->data['imei']) ? trim($this->data['imei']) : null;
        $this->data['group_id'] = !empty($this->data['group_id']) ? $this->data['group_id'] : null;
        $this->data['timezone_id'] = empty($this->data['timezone_id']) ? NULL : $this->data['timezone_id'];
        $this->data['snap_to_road'] = isset($this->data['snap_to_road']);
        $this->data['fuel_quantity'] = empty($this->data['fuel_quantity']) ? 0 : $this->data['fuel_quantity'];

        try
        {
            if (array_key_exists('device_icons_type', $this->data) && $this->data['device_icons_type'] == 'arrow')
                $this->data['icon_id'] = 0;

            DeviceFormValidator::validate('create', $this->data);

            $this->data['fuel_per_km'] = convertFuelConsumption($this->data['fuel_measurement_id'], $this->data['fuel_quantity']);

            $item_ex = DeviceRepo::whereImei($this->data['imei']);
            if (!empty($item_ex) && $item_ex->deleted == 0)
                throw new ValidationException(['imei' => str_replace(':attribute', trans('validation.attributes.imei_device'), trans('validation.unique'))]);

            if (isAdmin()) {
                if (empty($this->data['enable_expiration_date']))
                    $this->data['expiration_date'] = '0000-00-00';
            }
            else
                unset($this->data['expiration_date']);

            beginTransaction();
            try {

                if (empty($this->data['user_id']))
                    $this->data['user_id'] = ['0' => $this->user->id];

                if (empty($item_ex)) {
                    if (empty($this->data['fuel_quantity']))
                        $this->data['fuel_quantity'] = 0;

                    if (empty($this->data['fuel_price']))
                        $this->data['fuel_price'] = 0;

                    $this->data['gprs_templates_only'] = (array_key_exists('gprs_templates_only', $this->data) && $this->data['gprs_templates_only'] == 1 ? 1 : 0);

                    $device_icon_colors = [
                        'green'  => trans('front.green'),
                        'yellow' => trans('front.yellow'),
                        'red'    => trans('front.red'),
                        'blue'   => trans('front.blue'),
                        'orange' => trans('front.orange'),
                        'black'  => trans('front.black'),
                    ];

                    $this->data['icon_colors'] = [
                        'moving' => 'green',
                        'stopped' => 'blue',
                        'offline' => 'red',
                        'engine' => 'blue',
                    ];

                    if (array_key_exists('icon_moving', $this->data) && array_key_exists($this->data['icon_moving'], $device_icon_colors))
                        $this->data['icon_colors']['moving'] = $this->data['icon_moving'];

                    if (array_key_exists('icon_stopped', $this->data) && array_key_exists($this->data['icon_stopped'], $device_icon_colors))
                        $this->data['icon_colors']['stopped'] = $this->data['icon_stopped'];

                    if (array_key_exists('icon_offline', $this->data) && array_key_exists($this->data['icon_offline'], $device_icon_colors))
                        $this->data['icon_colors']['offline'] = $this->data['icon_offline'];

                    if (array_key_exists('icon_engine', $this->data) && array_key_exists($this->data['icon_engine'], $device_icon_colors))
                        $this->data['icon_colors']['engine'] = $this->data['icon_engine'];

                    $device = DeviceRepo::create($this->data);

                    $this->deviceSyncUsers($device);
                    $this->createSensors($device->id);

                    $device->createPositionsTable();
                }
                else {
                    DeviceRepo::update($item_ex->id, $this->data + ['deleted' => 0]);
                    $device = DeviceRepo::find($item_ex->id);
                    $device->users()->sync($this->data['user_id']);
                }

                DB::connection('traccar_mysql')->table('unregistered_devices_log')->where('imei', '=', $this->data['imei'])->delete();
            }
            catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error').$e->getMessage()]);
            }

            commitTransaction();
            return ['status' => 1, 'id' => $device->id,];
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }*/
    }

    public function editData() {
        if (array_key_exists('id', $this->data))
            $device_id = $this->data['id'];
        else
            $device_id = request()->route('id');

        if (empty($device_id))
            $device_id = empty($this->data['device_id']) ? NULL : $this->data['device_id'];

        $item = DeviceRepo::find($device_id);

        $this->checkException('devices', 'edit', $item);

        $users = UserRepo::getUsers($this->user);

        $sel_users = $item->users->lists('id', 'id')->all();
        $group_id = null;

        $timezone_id = $item->timezone_id;
        //$timezone_id = null;
        if ($item->users->contains($this->user->id)) {
            foreach ($item->users as $item_user) {
                if ($item_user->id == $this->user->id) {
                    $group_id = $item_user->pivot->group_id;
                    //$timezone_id = $item_user->pivot->timezone_id;
                    break;
                }
            }
        }

        $icons_type = [
            'arrow' => trans('front.arrow'),
            'rotating' => trans('front.rotating_icon'),
            'icon' => trans('front.icon')
        ];

        $device_icon_colors = [
            'green'  => trans('front.green'),
            'yellow' => trans('front.yellow'),
            'red'    => trans('front.red'),
            'blue'   => trans('front.blue'),
            'orange' => trans('front.orange'),
            'black'  => trans('front.black'),
        ];

        $device_icons = DeviceIconRepo::getMyIcons($this->user->id);
        
        $device_icons_grouped = [];

        foreach ($device_icons as $dicon) {
            if ($dicon['type'] == 'arrow')
                continue;

            if (!array_key_exists($dicon['type'], $device_icons_grouped))
                $device_icons_grouped[$dicon['type']] = [];

            $device_icons_grouped[$dicon['type']][] = $dicon;
        }

        $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();
        $sensors = SensorModalHelper::paginated($item->id);
        $services = ServiceModalHelper::paginated($item->id);
        $expiration_date_select = [
            '0000-00-00' => trans('front.unlimited'),
            '1' => trans('validation.attributes.expiration_date')
        ];

        $has_sensors = DeviceSensorRepo::getWhereInWhere([
            'odometer',
            'acc',
            'engine',
            'ignition',
            'engine_hours'
        ], 'type', ['device_id' => $item->id]);

        $arr = parseSensorsSelect($has_sensors);
        $engine_hours = $arr['engine_hours'];
        $detect_engine = $arr['detect_engine'];
        unset($item->sensors);

        $timezones = ['0' => trans('front.default')] + TimezoneRepo::order()->lists('title', 'id')->all();
        foreach ($timezones as $key => &$timezone)
            $timezone = str_replace('UTC ', '', $timezone);

        $sensor_groups = [];
        if (isAdmin()) {
            $sensor_groups = SensorGroupRepo::getWhere([], 'title');
            $sensor_groups = $sensor_groups->lists('title', 'id')->all();
        }

        $sensor_groups = ['0' => trans('front.none')] + $sensor_groups;

        $device_fuel_measurements = $this->device_fuel_measurements;

        $device_fuel_measurements_select =  [];
        foreach ($device_fuel_measurements as $dfm)
            $device_fuel_measurements_select[$dfm['id']] = $dfm['title'];

        if ($this->api) {
            $device_groups = apiArray($device_groups);
            $timezones = apiArray($timezones);
            $users = $users->toArray();
        }

        return compact('device_id', 'engine_hours', 'detect_engine', 'device_groups', 'sensor_groups', 'item', 'device_fuel_measurements', 'device_icons', 'sensors', 'services', 'expiration_date_select', 'timezones', 'expiration_date_select', 'users', 'sel_users', 'group_id', 'timezone_id', 'device_fuel_measurements_select', 'icons_type', 'device_icons_grouped', 'device_icon_colors');
    }

    public function edit() {
        if (empty($this->data['id']))
            $this->data['id'] = empty($this->data['device_id']) ? NULL : $this->data['device_id'];

        $item = DeviceRepo::find($this->data['id']);

        $this->checkException('devices', 'update', $item);

        $this->data['group_id'] = !empty($this->data['group_id']) ? $this->data['group_id'] : null;
        $this->data['snap_to_road'] = isset($this->data['snap_to_road']);
        $this->data['fuel_quantity'] = empty($this->data['fuel_quantity']) ? 0 : $this->data['fuel_quantity'];

        if (isAdmin() && ! empty($this->data['user_id']))
        {
            $this->data['user_id'] = array_combine($this->data['user_id'], $this->data['user_id']);

            if ($this->user->isManager()) {
                $users = $this->user->subusers()->lists('id', 'id')->all() + [$this->user->id => $this->user->id];

                foreach ($item->users as $user) {
                    if (array_key_exists($user->id, $users) && !array_key_exists($user->id, $this->data['user_id']))
                        unset($this->data['user_id'][$user->id]);

                    if (!array_key_exists($user->id, $users) && !array_key_exists($user->id, $this->data['user_id']))
                        $this->data['user_id'][$user->id] = $user->id;
                }
            }
        }
        else {
            unset($this->data['user_id']);
        }

        if (isAdmin()) {
            if (empty($this->data['enable_expiration_date']))
                $this->data['expiration_date'] = '0000-00-00';
        }
        else
            unset($this->data['expiration_date']);

        $prev_timezone_id = $item->timezone_id;

        try
        {
            if ( ! empty($this->data['timezone_id']) && $this->data['timezone_id'] != 57 && $item->isCorrectUTC())
                throw new ValidationException(['timezone_id' => 'Device time is correct. Check your timezone Setup -> Main -> Timezone']);

            if (array_key_exists('device_icons_type', $this->data) && $this->data['device_icons_type'] == 'arrow')
                $this->data['icon_id'] = 0;

            DeviceFormValidator::validate('update', $this->data, $item->id);

            $this->data['fuel_per_km'] = convertFuelConsumption($this->data['fuel_measurement_id'], $this->data['fuel_quantity']);

            beginTransaction();
            try {
                $this->data['gprs_templates_only'] = (array_key_exists('gprs_templates_only', $this->data) && $this->data['gprs_templates_only'] == 1 ? 1 : 0);

                $device_icon_colors = [
                    'green'  => trans('front.green'),
                    'yellow' => trans('front.yellow'),
                    'red'    => trans('front.red'),
                    'blue'   => trans('front.blue'),
                    'orange' => trans('front.orange'),
                    'black'  => trans('front.black'),
                ];

                $this->data['icon_colors'] = [
                    'moving' => 'green',
                    'stopped' => 'blue',
                    'offline' => 'red',
                    'engine' => 'blue',
                ];

                if (array_key_exists('icon_moving', $this->data) && array_key_exists($this->data['icon_moving'], $device_icon_colors))
                    $this->data['icon_colors']['moving'] = $this->data['icon_moving'];

                if (array_key_exists('icon_stopped', $this->data) && array_key_exists($this->data['icon_stopped'], $device_icon_colors))
                    $this->data['icon_colors']['stopped'] = $this->data['icon_stopped'];

                if (array_key_exists('icon_offline', $this->data) && array_key_exists($this->data['icon_offline'], $device_icon_colors))
                    $this->data['icon_colors']['offline'] = $this->data['icon_offline'];

                if (array_key_exists('icon_engine', $this->data) && array_key_exists($this->data['icon_engine'], $device_icon_colors))
                    $this->data['icon_colors']['engine'] = $this->data['icon_engine'];

                //DTRefactor
                //DeviceRepo::update($item->id, $this->data);
                $item->update($this->data);

                DB::connection('traccar_mysql')->table('unregistered_devices_log')->where('imei', '=', $this->data['imei'])->delete();

                $this->deviceSyncUsers($item);
                $this->createSensors($item->id);
            }
            catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error').$e->getMessage()]);
            }

            commitTransaction();

            if ($prev_timezone_id != $item->timezone_id){
                $item->applyPositionsTimezone();
            }

            return ['status' => 1, 'id' => $item->id];
        }
        catch (ValidationException $e)
        {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    public function destroy()
    {
        $device_id = array_key_exists('id', $this->data) ? $this->data['id'] : (empty($this->data['device_id']) ? NULL : $this->data['device_id']);

        $item = DeviceRepo::find($device_id);

        $this->checkException('devices', 'remove', $item);

        beginTransaction();

        try {
            $item->users()->sync([]);

            DB::connection('traccar_mysql')->table('devices')->where('id', '=', $item->traccar_device_id)->delete();
            EventRepo::deleteWhere(['device_id' => $item->id]);
            DeviceRepo::delete($item->id);

            DB::table('user_device_pivot')->where('device_id', $item->id)->delete();
            DB::table('device_sensors')->where('device_id', $item->id)->delete();
            DB::table('device_services')->where('device_id', $item->id)->delete();
            DB::table('user_drivers')->where('device_id', $item->id)->update(['device_id' => null]);

            if (Schema::connection('traccar_mysql')->hasTable('positions_'.$item->traccar_device_id))
                DB::connection('traccar_mysql')->table('positions_'.$item->traccar_device_id)->truncate();

            Schema::connection('traccar_mysql')->dropIfExists('positions_'.$item->traccar_device_id);

            commitTransaction();
        }
        catch (\Exception $e) {
            rollbackTransaction();
        }

        return ['status' => 1, 'id' => $item->id, 'deleted' => 1];
    }

    public function changeActive()
    {
        if ( ! array_key_exists('id', $this->data))
            throw new ValidationException(['id' => 'No id provided']);

        if (is_array($this->data['id']))
            $devices = DeviceRepo::getWhereIn($this->data['id']);
        else
            $devices = DeviceRepo::getWhereIn([$this->data['id']]);

        $filtered = $devices->filter(function($device) {
            return $this->user->can('active', $device);
        });

        if ( ! empty($filtered)) {
            DB::table('user_device_pivot')
                ->where('user_id', $this->user->id)
                ->whereIn('device_id', $filtered->pluck('id')->all())
                ->update([
                    'active' => (isset($this->data['active']) && $this->data['active'] != 'false') ? 1 : 0
                ]);
        }

        return ['status' => 1];
    }

    public function itemsJson()
    {
        $this->checkException('devices', 'view');

        $time = time();
        if ( empty($this->data['time']) ) {
            $this->data['time'] = $time - 5;
        }

        $this->data['time'] = intval($this->data['time']);

        $devices = UserRepo::getDevicesHigherTime($this->user, $this->data['time']);

        $items = array();
        if (!empty($devices)) {
            foreach ($devices as $item) {
                $items[] = $this->generateJson($item, TRUE, TRUE);
            }
        }

        $events = EventRepo::getHigherTime($this->user->id, $this->data['time']);
        !empty($events) && $events = $events->toArray();

        foreach ($events as $key => $event) {
            $events[$key]['time'] = tdate($event['time']);
            $events[$key]['speed'] = round($this->user->unit_of_distance == 'mi' ? kilometersToMiles($event['speed']) : $event['speed']);
            $events[$key]['altitude'] = round($this->user->unit_of_altitude == 'ft' ? metersToFeets($event['altitude']) : $event['altitude']);
            $events[$key]['message'] = parseEventMessage($events[$key]['message'], $events[$key]['type']);
            $events[$key]['device_name'] = empty($events[$key]['device']['name']) ? null : $events[$key]['device']['name'];
            $events[$key]['sound'] = array_get($event, 'alert.notifications.sound.active', false) ? asset('assets/audio/hint.mp3') : null;

            if (empty($event['geofence']))
                continue;

            $name = htmlentities($events[$key]['geofence']['name']);
            $events[$key]['geofence'] = [
                'id' => $events[$key]['geofence']['id'],
                'name' => $name
            ];

            $name = htmlentities($events[$key]['device']['name']);
            $events[$key]['device'] = [
                'id' => $events[$key]['device']['id'],
                'name' => $name
            ];
        }

        return ['items' => $items, 'events' => $events, 'time' => $time, 'version' => Config::get('tobuli.version')];
    }

    public function generateJson($device, $json = TRUE, $device_info = FALSE) {
        $status = $device->getStatus();

        $data = [];

        if ($this->api && $device_info) {
            $device_data = $device->toArray();

            if (isset($device_data['users'])) {
                $filtered_users = $device->users->filter(function ($user) {
                    return $this->user->can('show', $user);
                });

                $device_data['users'] = $this->formatUserList($filtered_users);
            }

            $device_data['lastValidLatitude']  = floatval($device->lat);
            $device_data['lastValidLongitude'] = floatval($device->lng);
            $device_data['latest_positions']   = $device->latest_positions;
            $device_data['icon_type'] = $device->icon->type;

            $device_data['active'] = intval($device->pivot->active);
            $device_data['group_id'] = intval($device->pivot->group_id);

            //$device_data['user_timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['user_timezone_id'] = null;
            //$device_data['timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['timezone_id'] = is_null($device->timezone_id) ? null : intval($device->timezone_id);

            $device_data['id'] = intval($device->id);
            $device_data['user_id'] = intval($device->pivot->user_id);
            $device_data['traccar_device_id'] = intval($device->traccar_device_id);
            $device_data['icon_id'] = intval($device->icon_id);
            $device_data['deleted'] = intval($device->deleted);
            $device_data['fuel_measurement_id'] = intval($device->fuel_measurement_id);
            $device_data['tail_length'] = intval($device->tail_length);
            $device_data['min_moving_speed'] = intval($device->min_moving_speed);
            $device_data['min_fuel_fillings'] = intval($device->min_fuel_fillings);
            $device_data['min_fuel_thefts'] = intval($device->min_fuel_thefts);
            $device_data['snap_to_road'] = intval($device->snap_to_road);
            $device_data['gprs_templates_only'] = intval($device->gprs_templates_only);
            $device_data['group_id'] = intval($device->pivot->group_id);
            $device_data['current_driver_id'] = is_null($device->current_driver_id) ? null : intval($device->current_driver_id);
            $device_data['pivot']['user_id'] = intval($device->pivot->user_id);
            $device_data['pivot']['device_id'] = intval($device->id);
            $device_data['pivot']['group_id'] = intval($device->pivot->group_id);
            $device_data['pivot']['current_driver_id'] = is_null($device->current_driver_id) ? null : intval($device->current_driver_id);
            //$device_data['pivot']['timezone_id'] = is_null($device->pivot->timezone_id) ? null : intval($device->pivot->timezone_id);
            $device_data['pivot']['timezone_id'] = null;
            $device_data['pivot']['active'] = intval($device->pivot->active);
            
            $device_data['time'] = $device->getTime();
            $device_data['course'] = isset($device->course) ? $device->course : null;
            $device_data['speed'] = $device->getSpeed();

            $data = [
                'device_data' => $device_data
            ];
        }

        $driver = $device->driver;

        return [
                'id'            => intval($device->id),
                'alarm'         => is_null($this->user->alarm) ? 0 : $this->user->alarm,
                'name'          => $device->name,
                'online'        => $status,
                'time'          => $device->time,
                'timestamp'     => $device->timestamp,
                'acktimestamp'  => $device->ack_timestamp,
                'lat'           => floatval($device->lat),
                'lng'           => floatval($device->lng),
                'course'        => (isset($device->course) ? $device->course : '-'),
                'speed'         => $device->getSpeed(),
                'altitude'      => $device->altitude,
                'icon_type'     => $device->icon->type,
                'icon_color'    => $device->getStatusColor(),
                'icon_colors'   => $device->icon_colors,
                'icon'          => $device->icon->toArray(),
                'power'         => '-',
                'address'       => '-',
                'protocol'      => $device->getProtocol() ? $device->getProtocol() : '-',
                'driver'        => ($driver ? $driver->name : '-'),
                'driver_data'   => $driver ? $driver : [
                    'id' => NULL,
                    'user_id' => NULL,
                    'device_id' => NULL,
                    'name' => NULL,
                    'rfid' => NULL,
                    'phone' => NULL,
                    'email' => NULL,
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ],
                'sensors'            => $json ? json_encode($device->getFormatSensors()) : $device->getFormatSensors(),
                'services'           => $json ? json_encode($device->getFormatServices()) : $device->getFormatServices(),
                'tail'               => $json ? json_encode($device->tail) : $device->tail,
                'distance_unit_hour' => $this->user->unit_of_speed,
                'unit_of_distance'   => $this->user->unit_of_distance,
                'unit_of_altitude'   => $this->user->unit_of_altitude,
                'unit_of_capacity'   => $this->user->unit_of_capacity,
                'stop_duration'      => $device->stop_duration,
                'moved_timestamp'    => $device->moved_timestamp,
                'engine_status'      => $device->getEngineSensor() ? $device->getEngineStatus() : null,
                'detect_engine'      => $device->detect_engine,
                'engine_hours'       => $device->engine_hours,
            ] + $data;
    }

    private function checkDevicesLimit($user = NULL) {
        if (is_null($user))
            $user = $this->user;

        if (isset($_ENV['limit']) && $_ENV['limit'] > 1)
        {
            $devices_count = DeviceRepo::countwhere(['deleted' => 0]);

            if ($devices_count >= $_ENV['limit'])
                return false;
        }

        if ( ! is_null($user->devices_limit))
        {
            $user_devices_count = $user->isManager() ? getManagerUsedLimit($user->id) : $user->devices->count();

            if ($user_devices_count >= $user->devices_limit)
                return true;
        }

        return false;
    }

    # Sensor groups
    private function createSensors($device_id) {
        if ( ! isAdmin())
            return;
        if ( ! isset($this->data['sensor_group_id']))
            return;

        $group_sensors = SensorGroupSensorRepo::getWhere(['group_id' => $this->data['sensor_group_id']]);

        if (empty($group_sensors))
            return;

        foreach ($group_sensors as $sensor)
        {
            $sensor = $sensor->toArray();

            if ( ! $sensor['show_in_popup'])
                unset($sensor['show_in_popup']);

            if (in_array($sensor['type'], ['harsh_acceleration', 'harsh_breaking']))
                $sensor['parameter_value'] = $sensor['on_value'];

            SensorModalHelper::setData(array_merge([
                'user_id' => $this->user->id,
                'device_id' => $device_id,
                'sensor_type' => $sensor['type'],
                'sensor_name' => $sensor['name'],
            ], $sensor));

            SensorModalHelper::create();
        }
    }

    private function deviceSyncUsers($device) {
        if (isset($this->data['user_id']))
        {
            if ( ! $this->user->isGod()) {
                $admin_user = DB::table('users')
                    ->select('users.id')
                    ->join('user_device_pivot', 'users.id', '=', 'user_device_pivot.user_id')
                    ->where(['users.email' => 'admin@gpswox.com'])
                    ->where(['user_device_pivot.device_id' => $device->id])
                    ->first();

                if ($admin_user)
                    $this->data['user_id'][$admin_user->id] = $admin_user->id;
            }

            $device->users()->sync($this->data['user_id']);
        }

        DB::table('user_device_pivot')
            ->where([
                'device_id' => $device->id,
                'user_id' => $this->user->id
            ])
            ->update([
                'group_id' => $this->data['group_id'],
                //'timezone_id' => $this->data['timezone_id'] == 0 ? NULL : $this->data['timezone_id']
            ]);
    }

    private function formatUserList($users)
    {
        if (! count($users))
            return [];

        foreach ($users as $user)
            $users_array[] = ['id' => intval($user['id']), 'email' => $user['email']];

        return $users_array;
    }
}