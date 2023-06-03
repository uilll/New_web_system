<?php

namespace Tobuli\Entities;

use App\Jobs\TrackerConfigWithRestart;
use Eloquent;
use Facades\Repositories\TraccarDeviceRepo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Traits\Chattable;

class Monitoring extends Eloquent
{
    use Chattable;

    protected $table = 'devices';

    protected $fillable = [
        'active',
        'event_id',
        'device_id',
        'customer',
        'owner',
        'plate_number',
        'cause',
        'timestamp',
        'gps_date',
        'lat',
        'lon',
        'occ_date',
        'modified_date',
        'tel',
        'make_contact',
        'information',
        'next_con',
        'treated_occurence',
        'sent_maintenance',
        'automatic_treatment',
        'interaction_date',
        'interaction_choice1',
        'interaction_choice2',
        'interaction_later',
        'updated_at',
        'created_at',
    ];

    protected $appends = [
        'stop_duration',
        //'lat',
        //'lng',
        //'speed',
        //'course',
        //'altitude',
        //'protocol',
        //'time'
    ];

    //protected $hidden = ['currents'];

    protected $casts = [
        'currents' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($device) {
            $traccar_item = TraccarDeviceRepo::create([
                'name' => $device->name,
                'uniqueId' => $device->imei,
            ]);

            $device->traccar_device_id = $traccar_item->id;
        });

        static::updated(function ($device) {
            TraccarDeviceRepo::update($device->traccar_device_id, [
                'name' => $device->name,
                'uniqueId' => $device->imei,
            ]);
        });

        static::saved(function ($device) {
            if ($device->isDirty('forward')) {
                dispatch((new TrackerConfigWithRestart()));
            }
        });
    }

    public function positions()
    {
        $instance = new \Tobuli\Entities\TraccarPosition();
        $instance->setTable('positions_'.$this->traccar_device_id);

        $foreignKey = $instance->getTable().'.device_id';
        $localKey = 'traccar_device_id';

        return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    public function positionTraccar()
    {
        if (! $this->traccar) {
            return null;
        }

        return new \Tobuli\Entities\TraccarPosition([
            'id' => $this->traccar->lastestPosition_id,
            'device_id' => $this->traccar->id,
            'latitude' => $this->traccar->lastValidLatitude,
            'longitude' => $this->traccar->lastValidLongitude,
            'other' => $this->traccar->other,
            'speed' => $this->traccar->speed,
            'altitude' => $this->traccar->altitude,
            'course' => $this->traccar->course,
            'time' => $this->traccar->time,
            'device_time' => $this->traccar->device_time,
            'server_time' => $this->traccar->server_time,
            'protocol' => $this->traccar->protocol,
            'valid' => true,
        ]);
    }

    public function createPositionsTable()
    {
        if (Schema::connection('traccar_mysql')->hasTable('positions_'.$this->traccar_device_id)) {
            throw new ValidationException(['id' => trans('global.cant_create_device_database')]);
        }

        Schema::connection('traccar_mysql')->create('positions_'.$this->traccar_device_id, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('device_id')->unsigned()->index();
            $table->double('altitude')->nullable();
            $table->double('course')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->text('other')->nullable();
            $table->double('power')->nullable();
            $table->double('speed')->nullable()->index();
            $table->datetime('time')->nullable()->index();
            $table->datetime('device_time')->nullable();
            $table->datetime('server_time')->nullable()->index();
            $table->text('sensors_values')->nullable();
            $table->tinyInteger('valid')->nullable();
            $table->double('distance')->nullable();
            $table->string('protocol', 20)->nullable();
        });
    }

    public function icon()
    {
        return $this->hasOne('Tobuli\Entities\DeviceIcon', 'id', 'icon_id');
    }

    public function getIconAttribute()
    {
        $icon = $this->getRelationValue('icon');

        return $icon ? $icon->setStatus($this->getStatus()) : null;
    }

    public function traccar()
    {
        return $this->hasOne('Tobuli\Entities\TraccarDevice', 'id', 'traccar_device_id');
    }

    public function alerts()
    {
        return $this->belongsToMany('Tobuli\Entities\Alert', 'alert_device', 'device_id', 'alert_id')
            // escape deattached users devices
            ->join('user_device_pivot', function ($join) {
                $join
                    ->on('user_device_pivot.device_id', '=', 'alert_device.device_id')
                    ->on('user_device_pivot.user_id', '=', 'alerts.user_id');
            });
    }

    public function events()
    {
        return $this->hasMany('Tobuli\Entities\Event', 'device_id');
    }

    public function users()
    {
        return $this->belongsToMany('Tobuli\Entities\User', 'user_device_pivot', 'device_id', 'user_id')->withPivot('group_id', 'current_driver_id', 'current_events');
    }

    public function driver()
    {
        //return $this->belongsToMany('Tobuli\Entities\UserDriver', 'user_device_pivot', 'device_id', 'current_driver_id');
        return $this->hasOne('Tobuli\Entities\UserDriver', 'id', 'current_driver_id');
    }

    public function sensors()
    {
        return $this->hasMany('Tobuli\Entities\DeviceSensor', 'device_id');
    }

    public function services()
    {
        return $this->hasMany('Tobuli\Entities\DeviceService', 'device_id');
    }

    public function timezone()
    {
        return $this->hasOne('Tobuli\Entities\Timezone', 'id', 'timezone_id');
    }

    public function setTimezoneIdAttribute($value)
    {
        $this->attributes['timezone_id'] = empty($value) ? null : $value;
    }

    public function setIconColorsAttribute($value)
    {
        $icon_color = json_encode($value);
        if ($icon_color == 'yellow' || $icon_color == 'black') {
            $icon_color = 'black';
        }
        $this->attributes['icon_colors'] = $icon_color;
    }

    public function getIconColorsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setForwardAttribute($value)
    {
        if (! Auth::User()->perm('forward', 'edit')) {
            return;
        }

        if (array_get($value, 'active')) {
            $this->attributes['forward'] = json_encode($value);
        } else {
            $this->attributes['forward'] = null;
        }
    }

    public function getForwardAttribute($value)
    {
        return json_decode($value, true);
    }

    public function isExpired()
    {
        return $this->expiration_date != '0000-00-00' && strtotime($this->expiration_date) < strtotime(date('Y-m-d'));
    }

    public function isConnected()
    {
        return Redis::get('connected.'.$this->imei) ? true : false;
    }

    public function getSpeed()
    {
        $speed = 0;

        if (isset($this->traccar->speed)) {// && $this->getStatus() == 'online') // Editei tentativa de eliminar o bug de velocidade
            $speed = Auth::User()->unit_of_distance == 'mi' ? kilometersToMiles($this->traccar->speed) : $this->traccar->speed;
        }
        if ($speed <= 3) {
            $speed = 0;
        }

        return round($speed);
    }

    public function getTimeoutStatus()
    {
        $minutes = settings('main_settings.default_object_online_timeout') * 60;

        $status = 'offline';

        if ((time() - $minutes) < strtotime($this->getAckTime())) {
            $status = 'ack';
        }
        if ((time() - $minutes) < strtotime($this->getServerTime())) {
            $status = 'online';
        }

        return $status;
    }

    public function getStatus()
    {
        $status = $this->getTimeoutStatus();

        if ($status != 'online') {
            return $status;
        }

        $speed = isset($this->traccar->speed) ? $this->traccar->speed : null;
        $status = 'offline';
        $sensor = $this->getEngineSensor();

        if (! empty($sensor)) {
            if (! $sensor->getValueCurrent($this->other)) {
                $status = 'ack';
            } else {
                if ($speed < $this->min_moving_speed) {
                    $status = 'engine';
                } elseif ($speed > $this->min_moving_speed) {
                    $status = 'online';
                }
            }
        } else {
            if ($speed < $this->min_moving_speed) {
                $status = 'ack';
            } elseif ($speed > $this->min_moving_speed) {
                $status = 'online';
            }
        }

        return $status;
    }

    public function getStatusColor()
    {
        switch ($this->getStatus()) {
            case 'online':
                $icon_status = 'moving';
                break;
            case 'ack':
                $icon_status = 'stopped';
                break;
            case 'engine':
                $icon_status = 'engine';
                break;
            default:
                $icon_status = 'offline';
        }

        return array_get($this->icon_colors, $icon_status, 'red');
    }

    public function getSensorByType($type)
    {
        $sensors = $this->sensors;

        if (empty($sensors)) {
            return null;
        }

        foreach ($sensors as $sensor) {
            if ($sensor['type'] == $type) {
                $type_sensor = $sensor;
                break;
            }
        }

        if (empty($type_sensor)) {
            return null;
        }

        return $type_sensor;
    }

    public function getFuelTankSensor()
    {
        $sensor = $this->getSensorByType('fuel_tank');

        if ($sensor) {
            return $sensor;
        }

        return $this->getSensorByType('fuel_tank_calibration');
    }

    public function getOdometerSensor()
    {
        return $this->getSensorByType('odometer');
    }

    public function getEngineHoursSensor()
    {
        return $this->getSensorByType('engine_hours');
    }

    public function getEngineSensor()
    {
        $detect_engine = $this->engine_hours == 'engine_hours' ? $this->detect_engine : $this->engine_hours;

        if (empty($detect_engine)) {
            return null;
        }

        if ($detect_engine == 'gps') {
            return null;
        }

        return $this->getSensorByType($detect_engine);
    }

    public function getEngineStatus()
    {
        $sensor = $this->getEngineSensor();

        if (empty($sensor)) {
            return false;
        }

        if ($this->getStatus() == 'offline') {
            return false;
        }

        return $sensor->getValueCurrent($this->other);
    }

    public function getEngineStatusFrom($date_from)
    {
        $sensor = $this->getEngineSensor();

        if (empty($sensor)) {
            return false;
        }

        $position = $this->positions()->where('time', '<=', $date_from)->first();

        if (! $position) {
            return false;
        }

        return $position->getSensorValue($sensor->id);
    }

    public function getProtocol()
    {
        return ($this->protocol && Auth::User()->perm('protocol', 'view')) ? $this->protocol : null;
    }

    public function setProtocolAttribute($value)
    {
        $this->attributes['protocol'] = $value;
    }

    public function getProtocolAttribute()
    {
        if (array_key_exists('protocol', $this->attributes)) {
            return $this->attributes['protocol'];
        }

        return isset($this->traccar->protocol) ? $this->traccar->protocol : null;
    }

    public function getDeviceTime()
    {
        return $this->traccar && $this->traccar->device_time ? $this->traccar->device_time : null;
    }

    public function getTime()
    {
        return $this->traccar && $this->traccar->time ? $this->traccar->time : null;
    }

    public function getAckTime()
    {
        return $this->traccar && $this->traccar->ack_time ? $this->traccar->ack_time : null;
    }

    public function getServerTime()
    {
        return $this->traccar && $this->traccar->server_time ? $this->traccar->server_time : null;
    }

    public function getTimeAttribute()
    {
        if ($this->isExpired()) {
            return trans('front.expired');
        }

        $time = $this->getTime();
        $ack = $this->getAckTime();

        if ((empty($time) || substr($time, 0, 4) == '0000') && (empty($ack) || substr($ack, 0, 4) == '0000')) {
            return trans('front.not_connected');
        }

        if ($ack > $time) {
            return datetime($ack, true, null);
        }

        return datetime($time, true, null);
    }

    public function getOnlineAttribute()
    {
        return $this->getStatus();
    }

    public function getLatAttribute()
    {
        return cord(isset($this->traccar->lastValidLatitude) ? $this->traccar->lastValidLatitude : 0);
    }

    public function getLngAttribute()
    {
        return cord(isset($this->traccar->lastValidLongitude) ? $this->traccar->lastValidLongitude : 0);
    }

    public function getCourseAttribute()
    {
        $course = 0;

        if (isset($this->traccar->course)) {
            $course = $this->traccar->course;
        }

        return round($course);
    }

    public function getAltitudeAttribute()
    {
        $altitude = 0;

        if (isset($this->traccar->altitude)) {
            $altitude = Auth::User()->unit_of_altitude == 'ft' ? metersToFeets($this->traccar->altitude) : $this->traccar->altitude;
        }

        return round($altitude);
    }

    public function getTailAttribute()
    {
        $tail_length = $this->getStatus() ? $this->tail_length : 0;

        return prepareDeviceTail(isset($this->traccar->latest_positions) ? $this->traccar->latest_positions : '', $tail_length);
    }

    public function getLatestPositionsAttribute()
    {
        return isset($this->traccar->latest_positions) ? $this->traccar->latest_positions : null;
    }

    public function getTimestampAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return isset($this->traccar->server_time) ? strtotime($this->traccar->server_time) : 0;
    }

    public function getServerTimestampAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return isset($this->traccar->server_time) ? strtotime($this->traccar->server_time) : 0;
    }

    public function getAckTimestampAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return isset($this->traccar->ack_time) ? strtotime($this->traccar->ack_time) : 0;
    }

    public function getAckTimeAttribute()
    {
        if ($this->isExpired()) {
            return null;
        }

        return isset($this->traccar->ack_time) ? $this->traccar->ack_time : null;
    }

    public function getServerTimeAttribute()
    {
        if ($this->isExpired()) {
            return null;
        }

        return isset($this->traccar->server_time) ? $this->traccar->server_time : null;
    }

    public function getMovedAtAttribute()
    {
        if ($this->isExpired()) {
            return null;
        }

        return isset($this->traccar->moved_at) ? $this->traccar->moved_at : null;
    }

    public function getMovedTimestampAttribute()
    {
        return $this->moved_at ? strtotime($this->moved_at) : 0;
    }

    public function getLastConnectTimeAttribute()
    {
        $lastConnect = $this->getLastConnectTimestampAttribute();

        return $lastConnect ? date('Y-m-d H:i:s', $lastConnect) : null;
    }

    public function getLastConnectTimestampAttribute()
    {
        return max($this->server_timestamp, $this->ack_timestamp);
    }

    public function getOtherAttribute()
    {
        return isset($this->traccar->other) ? $this->traccar->other : null;
    }

    public function getStopDuration()
    {
        $moved_at = isset($this->traccar->moved_at) ? strtotime($this->traccar->moved_at) : 0;
        $time = isset($this->traccar->time) ? strtotime($this->traccar->time) : 0;
        $server_time = isset($this->traccar->server_time) ? strtotime($this->traccar->server_time) : 0;

        if (! $moved_at) {
            return 0;
        }

        //device send incorrcet self timestamp
        if ($server_time > $time) {
            return time() - $moved_at + ($time - $server_time);
        }

        return time() - $moved_at;
    }

    public function getStopDurationAttribute()
    {
        $duration = $this->getStopDuration();

        if ($duration < 5) {
            return '0'.trans('front.h');
        }

        return secondsToTime($duration);
    }

    public function getFormatSensors()
    {
        $result = [];

        foreach ($this->sensors as $sensor) {
            if ($sensor->type == 'harsh_acceleration' || $sensor->type == 'harsh_breaking') {
                continue;
            }

            $value = $sensor->getValue($this->other, true);

            $result[] = [
                'id' => $sensor->id,
                'type' => $sensor->type,
                'name' => $sensor->formatName(),
                'show_in_popup' => $sensor->show_in_popup,

                //'text'          => htmlentities( $sensor->formatValue($value) ),
                'value' => htmlentities($sensor->formatValue($value)),
                'val' => $value,
                'scale_value' => $sensor->getValueScale($value),
            ];
        }

        return $result;
    }

    public function getFormatServices()
    {
        $result = [];

        foreach ($this->services as $service) {
            $service->setSensors($this->sensors);

            $result[] = [
                'id' => $service->id,
                'name' => $service->name,
                'value' => $service->expiration(),
                'expiring' => $service->isExpiring(),
            ];
        }

        return $result;
    }

    public function generateTail()
    {
        $limit = 15;

        $positions = DB::connection('traccar_mysql')
            ->table('positions_'.$this->traccar_device_id)
            ->where('distance', '>', 0.02)
            ->orderBy('time', 'desc')
            ->limit($limit)
            ->get();

        $tail_positions = [];

        foreach ($positions as $position) {
            $tail_positions[] = $position->latitude.'/'.$position->longitude;
        }

        $this->traccar->update([
            'latest_positions' => implode(';', $tail_positions),
        ]);
    }

    public function isCurrentGeofence($geofence)
    {
        $currents = $this->currents ? $this->currents : [];

        if (empty($currents)) {
            return false;
        }

        if (empty($currents['geofences'])) {
            return false;
        }

        if (! in_array($geofence->id, $currents['geofences'])) {
            return false;
        }

        return true;
    }

    public function setCurrentGeofences($geofences)
    {
        $currents = $this->currents ? $this->currents : [];

        $this->currents = array_merge($currents, ['geofences' => $geofences]);
    }

    public function applyPositionsTimezone()
    {
        if (! $this->timezone) {
            $value = 'device_time';
        } elseif ($this->timezone->id == 57) {
            $value = 'device_time';
        } else {
            [$hours, $minutes] = explode(' ', $this->timezone->time);

            if ($this->timezone->prefix == 'plus') {
                $value = "DATE_ADD(device_time, INTERVAL '$hours:$minutes' HOUR_MINUTE)";
            } else {
                $value = "DATE_SUB(device_time, INTERVAL '$hours:$minutes' HOUR_MINUTE)";
            }
        }

        $this->traccar()->update(['time' => DB::raw($value)]);
        $this->positions()->update(['time' => DB::raw($value)]);
    }

    public function isCorrectUTC()
    {
        $change = 900; //15 mins

        $ack_time = strtotime($this->getAckTime());
        $server_time = strtotime($this->getServerTime());
        $device_time = strtotime($this->getDeviceTime());

        $last = max($ack_time, $server_time);

        if ($last && (abs($last - $device_time) < $change)) {
            return true;
        }

        return false;
    }

    public function canChat()
    {
        $protocol = isset($this->traccar->protocol) ? $this->traccar->protocol : null;

        return $protocol == 'osmand';
    }

    public function scopeOnline($query, $minutes = null)
    {
        $traccar_db = config('database.connections.traccar_mysql.database');

        if (is_null($minutes)) {
            $minutes = time() - (config('tobuli.device_offline_minutes') * 60);
        }

        return $query
            ->join("{$traccar_db}.devices as traccar_devices", 'devices.traccar_device_id', '=', 'traccar_devices.id')
            ->where(function ($query) use ($minutes) {
                $query->where('traccar_devices.server_time', '>', date('Y-m-d H:i:s', $minutes));
                $query->orWhere('traccar_devices.ack_time', '>', date('Y-m-d H:i:s', $minutes));
            });
    }

    public function scopeNPerGroup($query, $group, $n = 10)
    {
        // queried table
        $table = ($this->getTable());

        // initialize MySQL variables inline
        $query->from(DB::raw("(SELECT @rank:=0, @group:=0) as vars, {$table}"));

        // if no columns already selected, let's select *
        if (! $query->getQuery()->columns) {
            $query->select("{$table}.*");
        }

        // make sure column aliases are unique
        $groupAlias = 'group_'.md5(time());
        $rankAlias = 'rank_'.md5(time());

        // apply mysql variables
        $query->addSelect(DB::raw(
            "@rank := IF(@group = {$group}, @rank+1, 1) as {$rankAlias}, @group := {$group} as {$groupAlias}"
        ));

        // make sure first order clause is the group order
        $query->getQuery()->orders = (array) $query->getQuery()->orders;
        array_unshift($query->getQuery()->orders, ['column' => $group, 'direction' => 'asc']);

        // prepare subquery
        $subQuery = $query->toSql();

        // prepare new main base Query\Builder
        $newBase = $this->newQuery()
            ->from(DB::raw("({$subQuery}) as {$table}"))
            ->mergeBindings($query->getQuery())
            ->where($rankAlias, '<=', $n)
            ->getQuery();

        // replace underlying builder to get rid of previous clauses
        $query->setQuery($newBase);
    }

    public function changeDriver($driver)
    {
        $this->current_driver_id = $driver->id;
        $this->save();

        DB::table('user_driver_position_pivot')->insert([
            'device_id' => $this->id,
            'driver_id' => $driver->id,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $position = $this->positionTraccar();

        if (is_null($position)) {
            return;
        }

        $alerts = $this->alerts->filter(function ($item) {
            return $item->type == 'driver';
        });

        foreach ($alerts as $alert) {
            $this->events()->create([
                'type' => 'driver',
                'user_id' => $alert->user_id,
                'alert_id' => $alert->id,
                'device_id' => $this->id,
                'geofence_id' => null,
                'altitude' => $position->altitude,
                'course' => $position->course,
                'latitude' => $position->latitude,
                'longitude' => $position->longitude,
                'speed' => $position->speed,
                'time' => $position->time,
                'position_id' => $position->id,
                'message' => $driver->name,
            ]);

            $notifications = $alert->notifications;

            DB::table('events_queue')->insert([
                'user_id' => $alert->user_id,
                'device_id' => $this->id,
                'type' => 'driver',
                'data' => json_encode([
                    'altitude' => $position->altitude,
                    'course' => $position->course,
                    'latitude' => $position->latitude,
                    'longitude' => $position->longitude,
                    'speed' => $position->speed,
                    'time' => $position->time,
                    'device_name' => htmlentities($this->name),
                    'driver' => htmlentities($driver->name),

                    'push' => array_get($notifications, 'push.active'),
                    'email' => array_get($notifications, 'email.active') ? array_get($notifications, 'email.input') : null,
                    'mobile_phone' => array_get($notifications, 'sms.active') ? array_get($notifications, 'sms.input') : null,
                    'webhook' => array_get($notifications, 'webhook.active') ? array_get($notifications, 'webhook.input') : null,
                    'command' => array_get($alert->command, 'active') ? $alert->command : null,
                ]),
            ]);
        }
    }
}
