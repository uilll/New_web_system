<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tobuli\Entities\Event;
use Tobuli\Entities\TraccarPosition as Position;
use Facades\Repositories\UserDriverRepo;

use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;
use Tobuli\Helpers\Alerts\Checker;
use App\Monitoring;

class PositionsWriter
{
    const MIN_DISTANCE = 0.02;
    const MIN_TIME_SEC = 600;

    protected $device;

    protected $events = [];

    protected $positions = [];

    protected $position = null;

    protected $prevPosition = null;

    protected $drivers = [];

    protected $alertChecker = null;

    protected $debug;

    public function __construct($device, $debug = false)
    {
        $this->device = $device;

        $this->redis = Redis::connection();

        $this->debug = $debug;

        $this->stack = new PositionsStack();

        $this->device->load(['timezone']);
    }

    protected function line($text = '')
    {
        if ( ! $this->debug)
            return;

        echo $text . PHP_EOL;
    }

    public function runKeys($keys)
    {
        $this->line();
        $this->line('IMEI: ' . $this->device->imei);
        $this->line('Keys  ' . count($keys));

        $start = microtime(true);

        while( $key = array_shift($keys) )
        {
            $this->proccessKey($key);

            $this->redis->del($key);
        }

        $this->write();

        $end = microtime(true);
        $this->line('Time '.($end - $start));
    }

    public function runList($imei)
    {
        $key = 'positions.' . $imei;

        $this->line();
        $this->line('IMEI: ' . $imei);
        $this->line('Keys  ' . $this->stack->oneCount($key));

        $start = microtime(true);

        while( $data = $this->stack->getKeyData($key) )
        {
            $data = $this->normalizeData($data);

            if ( ! $data )
                return;

            $this->proccess($data);
        }

        $this->write();

        $end = microtime(true);
        $this->line('Time '.($end - $start));
    }

    protected function getData($key)
    {
        $value = $this->redis->get($key);

        $data = json_decode($value, true);

        if ( ! $data)
            return false;

        return $data;
    }

    protected function normalizeData($data)
    {
        if ( ! empty($data['deviceId']))
            $data['imei'] = $data['deviceId'];

        if ( ! empty($data['uniqueId']))
            $data['imei'] = $data['uniqueId'];

        if (empty($data['imei']))
            return false;


        $data = array_merge([
            'altitude'  => 0,
            'course'    => null,
            'latitude'  => null,
            'longitude' => null,
            'speed'     => 0,
            'distance'  => 0,
            'valid'     => 1,
            'protocol'  => null,

            'ack'         => empty($data['fixTime']),
            'attributes'  => [],
            'server_time' => date('Y-m-d H:i:s'),
        ], $data);

        $data['speed'] = $data['speed'] * 1.852;

        if ($data['ack']) {
            if ( ! empty($data['deviceTime'])) {
                $data['device_time'] = date('Y-m-d H:i:s', $data['deviceTime'] / 1000);
            }
            else {
                $data['device_time'] = null;
            }
        } else {
            $data['device_time'] = date('Y-m-d H:i:s', $data['fixTime'] / 1000);
        }

        if (is_null($data['device_time']))
        {
            $data['device_time'] = $this->device->getDeviceTime();
        }

        $data['time'] = $data['device_time'];

        if ($this->device->timezone)
        {
            $data['time'] = date('Y-m-d H:i:s', strtotime($this->device->timezone->zone, strtotime($data['time'])));
        }

        if ($data['time'] == $this->device->getTime() && time() - strtotime($this->device->getServerTime()) > 60)
            $data['ack'] = true;


        if ( ! $data['ack']) {
            //Outdated check for 90 days
            if (time() - strtotime($data['time']) > 7776000) {
                $this->line('Bad date - outdated: ' . $data['time']);
                return false;
            }

            //Future check for 1 day
            if (strtotime($data['time']) - time() > 86400) {
                $this->line('Bad date - future: ' . $data['time']);
                return false;
            }
        }

        if (isset($data['attributes']['sat']) && $data['attributes']['sat'] < 1)
            $data['valid'] = 0;

        $parameters = $data['attributes'];
        $parameters = is_array($parameters) ? $parameters : [];
        $parameters = array_change_key_case($parameters, CASE_LOWER);
        $parameters['valid'] = $data['valid'];
        $parameters[Position::VIRTUAL_ENGINE_HOURS_KEY] = 0;


        $merged_protocols = ['gt02', 'gt06', 'gps103', 'h02', 'eelink', 'xirgo', 'tk103', 'gl200', 'wialon', 'aquila'];

        if ( in_array($data['protocol'], $merged_protocols) && $prevPosition = $this->getPrevPosition($data['time']) ) {
            $excepts = ['alarm', 'result', 'ip', 'sat'];

            if ($data['protocol'] == 'gl200') {
                $excepts[] = 'power';
            }

            $prevParameters = array_except($prevPosition->parameters, $excepts);
            $parameters = array_merge($prevParameters, $parameters);
        }

        if ( ! empty($parameters['ip']))
            unset($parameters['ip']);

        $data['parameters'] = $parameters;

        $params = empty($this->device->parameters) ? [] : json_decode($this->device->parameters, true);
        $params = empty($params) ? [] : array_flip($params);
        $params = array_map(function($val) { return strtolower($val); }, $params);

        $merge = array_keys(array_merge($parameters, $params));
        if (count($params) != count($merge)) {
            $this->device->parameters = json_encode($merge);
        }

        return $data;
    }

    protected function isHistory($time = null)
    {
        if (is_null($time) && $this->position)
            $time = $this->position->time;

        return strtotime($time) < strtotime($this->device->getTime());
    }

    protected function isChanged($current, $previous)
    {
        if (empty($previous))
            return true;

        if (round($current->speed, 1) != round($previous->speed, 1))
            return true;

        if ($current->distance > self::MIN_DISTANCE)
            return true;

        if ((strtotime($current->time) - strtotime($previous->time)) >= self::MIN_TIME_SEC)
            return true;

        $escape = ['distance', 'totaldistance', 'sequence', 'power', Position::VIRTUAL_ENGINE_HOURS_KEY];

        $currentParameters  = array_except($current->parameters, $escape);
        $previousParameters = array_except($previous->parameters, $escape);

        if ($currentParameters != $previousParameters)
            return true;

        return false;
    }

    protected function getPrevPosition($time = null)
    {
        if ( ! is_null($this->prevPosition))
            return $this->prevPosition;

        if (is_null($time) && $this->position)
            $time = $this->position->time;

        if (empty($time))
            return $this->getLastPosition();

        if ($this->positions)
        {
            foreach ($this->positions as $index => $position)
            {
                if ($position->time > $time)
                    break;

                $this->prevPosition = & $this->positions[$index];

                if ($position->time < $time)
                    continue;

                break;
            }
        }

        if ($this->prevPosition && $this->isHistory($time) && (($time - $this->prevPosition->time) > 3600))
        {
            $this->line('Getting history prev with time ' . $time );

            $storedPosition = $this->device->positions()
                ->orderBy('time', 'desc')
                ->orderBy('id', 'desc')
                ->where('time', '<=', $time)
                ->first();

            if ($storedPosition && $storedPosition->time > $this->prevPosition->time)
                $this->prevPosition = $storedPosition;
        }

        if (is_null($this->prevPosition))
        {
            $this->prevPosition = $this->device->positions()
                ->orderBy('time', 'desc')
                ->orderBy('id', 'desc')
                ->where('time', '<=', $time)
                ->first();
        }

        return $this->prevPosition;
    }

    protected function getPrevValidPosition($time = null)
    {
        if (is_null($time) && $this->position)
            $time = $this->position->time;

        $prevPosition = $this->getPrevPosition($time);

        if ($prevPosition && $prevPosition->isValid())
            return $prevPosition;

        if ($this->isHistory())
            return $this->device->positions()
                ->orderBy('time', 'desc')
                ->orderBy('id', 'desc')
                ->where('time', '<=', $time)
                ->where('valid', '>', 0)
                ->first();

        return $this->getLastPosition();
    }

    protected function getLastPosition()
    {
        if ( ! $this->device->traccar)
            return null;

        if (empty($this->device->traccar->lastValidLatitude) && empty($this->device->traccar->lastValidLongitude))
            return null;

        return new Position([
            'server_time' => $this->device->traccar->server_time,
            'device_time' => $this->device->traccar->device_time,
            'time'        => $this->device->traccar->time,
            'latitude'    => $this->device->traccar->lastValidLatitude,
            'longitude'   => $this->device->traccar->lastValidLongitude,
            'speed'       => $this->device->traccar->speed,
            'course'      => $this->device->traccar->course,
            'altitude'    => $this->device->traccar->altitude,
            'protocol'    => $this->device->traccar->protocol,
            'other'       => $this->device->traccar->other,
            'valid'       => 1,
        ]);
    }

    protected function proccessKey($key)
    {
        $data = $this->getData($key);

        if ( ! $data )
            return;

        $data = $this->normalizeData($data);

        if ( ! $data )
            return;

        $this->proccess($data);
    }

    protected function proccess($data)
    {
        $this->position = new Position($data);

        if ($data['ack'])
        {
            if ($this->isHistory($this->position->time))
                return;

            $this->device->traccar->speed = 0;
            $this->device->traccar->time = $this->position->time;
            $this->device->traccar->device_time = $this->position->device_time;
            $this->device->traccar->ack_time = date('Y-m-d H:i:s');
            $this->device->traccar->other = $this->position->other;
        }

        $lastValidPosition = $this->getPrevValidPosition();

        if (empty($this->position->latitude) && empty($this->position->longitude))
        {
            if ($lastValidPosition)
            {
                $this->position->latitude = $lastValidPosition->latitude;
                $this->position->longitude = $lastValidPosition->longitude;
            }
            else
            {
                $this->position->valid = 0;
            }
        }

        if ($this->position->speed > 200)
            $this->position->speed = $lastValidPosition ? $lastValidPosition->speed : 200;

        //if (is_null($this->position->course) && $lastValidPosition)
        //    $this->position->course = $lastValidPosition->course;

        if (empty($this->position->course) && $lastValidPosition)
            $this->position->course = getCourse(
                $this->position->latitude,
                $this->position->longitude,
                $lastValidPosition->latitude,
                $lastValidPosition->longitude
            );


        if ($lastValidPosition && $lastValidPosition->id > 50)
        {
            $this->position->distance = getDistance(
                $this->position->latitude,
                $this->position->longitude,
                $lastValidPosition->latitude,
                $lastValidPosition->longitude
            );

            //$checkValidProtocols = ['gt02', 'gt06', 'gps103', 'h02', 'eelink', 'osmand'];
            $skipProtocols = ['upro'];

            if (
                $this->device->valid_by_avg_speed &&
                ! in_array($this->position->protocol, $skipProtocols) &&
                $this->position->distance > 10
            )
            {
                $time = strtotime($this->position->time) - strtotime($lastValidPosition->time);

                if ($time > 0) {
                    $avg_speed = $this->position->distance / ($time / 3600);

                    if ($avg_speed > 170) {
                        $this->position->valid = 0;
                    }
                } else {
                    $this->position->valid = 0;
                }
            }
        }

        //tmp
        if ( ! $this->position->isValid())
        {
            $this->position->distance = 0;

            if ($lastValidPosition)
            {
                $this->position->latitude = $lastValidPosition->latitude;
                $this->position->longitude = $lastValidPosition->longitude;
            }
        }

        $distance = round($this->position->distance * 1000, 2);

        $this->position->setParameter('distance', $distance);


        $totalDistance = $lastValidPosition ? $lastValidPosition->getParameter('totaldistance', 0) : 0;

        if ($this->position->isValid())
        {
            $totalDistance += $distance;
        }

        $this->position->setParameter('totaldistance', $totalDistance);
        $this->position->setParameter('valid', $this->position->isValid() ? 'true' : 'false');
        $this->position->setParameter(Position::VIRTUAL_ENGINE_HOURS_KEY, $this->getVirtualEngineHours());

        $this->setSensors();

        if ( ! $this->isHistory())
        {
            $this->alerts();

            if ($this->position->isValid()) {
                $this->setTraccarDevicePosition($this->position);
            }

            $this->setTraccarDeviceData($this->position);
        }

        $this->setTraccarDeviceMovedAt($this->position);

        $this->setCurrentDriver($this->position);


        if ($this->events || $this->isChanged($this->position, $this->getPrevPosition()))
        {
            $this->addPosition($this->position);
        }

        if ($this->events || count($this->positions) > 100)
            $this->write();
    }

    protected function getEngineStatus($position)
    {
        if ( ! isset($this->engine_sensor))
            $this->engine_sensor = $this->device->getEngineSensor();

        if ($this->engine_sensor)
            return $this->engine_sensor->getValue($position->other, false, null);

        return $position->speed > 0;
    }

    protected function getVirtualEngineHours()
    {
        $engineHours = 0;
        $duration = 0;
        $prevEngineStatus = false;

        if ($prevPosition = $this->getPrevPosition())
        {
            $engineHours = $prevPosition->getVirtualEngineHours();

            $duration = strtotime($this->position->time) - strtotime($prevPosition->time);

            $prevEngineStatus = $this->getEngineStatus($prevPosition);
        }

        if ( ! $duration)
            return $engineHours;

        //skip if duration between positions is more then 5 mins
        if ($duration > 300)
            return $engineHours;

        if ( ! $prevEngineStatus)
            return $engineHours;

        return $engineHours + $duration;
    }

    protected function alerts()
    {
        if ($this->alertChecker === false)
            return;

        if (is_null($this->alertChecker))
        {
            $alerts = $this->device
                ->alerts()
                ->with('user', 'geofences', 'drivers', 'events_custom', 'zones')
                ->checkByPosition()
                ->active()
                ->get();

            $count = count($alerts);

            if ($count) {
                $this->alertChecker = new Checker($this->device, $alerts);

                $this->line('Alerts: '.count($alerts));
            } else {
                $this->alertChecker = false;
                
                return;
            }
        }
        
        
        
        $start = microtime(true);

        // reset device with new proterties as lat, lng and etc.
        $this->alertChecker->setDevice($this->device);

        $this->events = $this->alertChecker->check($this->position, $this->getPrevPosition());

        $end = microtime(true);
        $this->line('Alerts check time '.round($end - $start, 5));
    }

    protected function setSensors()
    {
        $sensorsValues = [];

        if ($this->device->sensors)
        {
            foreach ($this->device->sensors as &$sensor)
            {
                if ( ! $sensor->isUpdatable())
                    continue;

                if ( ! $this->isHistory())
                {
                    $sensorValue = $sensor->getValue($this->position->other);

                    if ( ! is_null($sensorValue) && ! is_array($sensorValue))
                        $sensor->setValue($sensorValue);

                } else {
                    $prevSensorValue = null;

                    if ($prevPosition = $this->getPrevPosition())
                    {
                        $prevSensorValue = $sensor->getValue($prevPosition->other, false);
                    }

                    $sensorValue = $sensor->getValue($this->position->other, false, $prevSensorValue);
                }

                if ( ! $sensor->isUpdatable())
                    continue;

                if ( ! is_null($sensorValue))
                {
                    $sensorsValues[] = [
                        'id'  => $sensor->id,
                        'val' => $sensorValue
                    ];
                }
            }
        }

        if ($sensorsValues)
            $this->position->sensors_values = json_encode($sensorsValues);
    }

    protected function setCurrentDriver($position)
    {
        $rfids = $position->getRfids();

        if ( ! $rfids)
            return;

        $hash = md5(json_encode($rfids));

        if ( ! array_key_exists($hash, $this->drivers))
        {
            $this->drivers[$hash] = UserDriverRepo::findWhere(function($query) use ($rfids){
                $query->whereIn('rfid', $rfids);
            });
        }

        $driver = $this->drivers[$hash];

        if ( ! $driver)
            return;

        if ($this->device->current_driver_id == $driver->id)
            return;

        $this->device->current_driver_id = $driver->id;

        DB::table('user_driver_position_pivot')->insert([
            'driver_id' => $driver->id,
            'device_id' => $this->device->id,
            'date' => $position->time
        ]);
    }

    protected function setTraccarDevicePosition($position)
    {
        $this->device->traccar->lastValidLatitude = $position->latitude;
        $this->device->traccar->lastValidLongitude = $position->longitude;
        $this->device->traccar->altitude = $position->altitude;
        //$this->device->traccar->speed = $position->speed;
        $this->device->traccar->course = $position->course;


        $latest_positions = $this->device->traccar->latest_positions ? explode(';', $this->device->traccar->latest_positions) : [];

        if ( ! $latest_positions) {
            array_unshift($latest_positions, $position->latitude . '/' . $position->longitude);
        } else {
            list($lat, $lng) = explode('/', reset($latest_positions));

            $distance = getDistance($position->latitude, $position->longitude, $lat, $lng);

            if ($distance > self::MIN_DISTANCE)
                array_unshift($latest_positions, $position->latitude . '/' . $position->longitude);
        }

        $this->device->traccar->latest_positions = implode(';', array_slice($latest_positions, 0, 15));
    }

    protected function setTraccarDeviceData($position)
    {
        $this->device->traccar->time = $position->time;
        $this->device->traccar->server_time = $position->server_time;
        $this->device->traccar->device_time = $position->device_time;
        $this->device->traccar->other = $position->other;
        $this->device->traccar->protocol = $position->protocol;

        $this->device->traccar->speed = $position->speed;
    }

    protected function setTraccarDeviceMovedAt($position)
    {
        if ($position->speed < $this->device->min_moving_speed)
            return;

        if ($this->device->traccar->moved_at > $position->time)
            return;

        $this->device->traccar->moved_at = $position->time;
    }

    protected function addPosition($position)
    {
        $this->positions[] = $position;

        $this->positions = array_sort($this->positions, function($value){
            return $value->time;
        });

        $this->prevPosition = null;

        if ( ! $this->isHistory())
            $this->prevPosition = $position;
    }

    protected function updatePosition($position)
    {
        $this->line('Updating last position...');

        // skip if new position
        if ( ! $position->id)
            return;

        // skip if position already in list
        if(array_filter($this->positions, function($value) use ($position) { return $position->id == $value->id; }))
            return;

        $this->addPosition($position);
    }

    protected function write()
    {
        $this->line('Writing:');
        $this->line('Positions '.count($this->positions));
        $this->line('Events '.count($this->events));

        $start = microtime(true);

        $this->writePositions();
        $this->writeEvents();

        foreach ($this->device->sensors as $sensor)
            $sensor->save();

        $this->device->traccar->save();

        $this->device->save();

        $end = microtime(true);

        $this->line('Time '.($end - $start));
    }

    protected function writePositions()
    {
        if ( ! $this->positions)
            return;

        $data = [];

        foreach ($this->positions as $position)
        {
            if ($position->id)
            {
                $this->line('Saving updated position...');
                $position->save();
                continue;
            }

            $attributes = $position->attributesToArray();

            if ($position->getFillable()) {
                $attributes = array_intersect_key($attributes, array_flip($position->getFillable()));
            }

            if (empty($attributes['power']))
                $attributes['power'] = null;
            if (empty($attributes['sensors_values'])) {
                $attributes['sensors_values'] = null;
            } elseif (is_array($attributes['sensors_values'])) {
                $attributes['sensors_values'] = json_encode($attributes['sensors_values']);
            }

            $attributes['device_id'] = $this->device->traccar_device_id;

            $data[] = $attributes;
        }

        $this->positions = [];

        $count = count($data);

        if ( ! $count)
            return;

        try {
            $this->writePositionData($data, $count > 1);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            if ($e->getCode() == '42S02') {
                $this->device->createPositionsTable();
                $this->writePositionData($data, $count > 1);
            }
        }
    }

    protected function writePositionData($data, $multi) {
        if ($multi)
        {
            $this->device->positions()->insert($data);
            $lastPosition = $this->device->positions()->orderBy('time', 'desc')->first();
            $this->device->traccar->latestPosition_id = $lastPosition->id;

        } else
        {
            $position = $this->device->positions()->create($data[0]);

            if ( ! $this->isHistory())
                $this->device->traccar->latestPosition_id = $position->id;
        }
    }

    protected function writeEvents()
    {
        if ( ! $this->events)
            return;

        $events = [];
        $queues = [];

        $insertedPosition = $this->device->positions()->orderBy('time', 'desc')->first();

        if ( ! $insertedPosition) {
            $this->events = [];
            return;
        }

        foreach ($this->events as $event)
        {
            $attributes = $event->attributesToArray();

            if ($event->getFillable()) {
                $attributes = array_intersect_key($attributes, array_flip($event->getFillable()));
                $attributes = array_diff_key($attributes, array_flip([
                    'zone',
                    'schedule',
                    'overspeed',
                    'stop_duration',
                ]));

                $attributes['position_id'] = $insertedPosition->id;
                $attributes['created_at']  = date('Y-m-d H:i:s');
                $attributes['updated_at']  = date('Y-m-d H:i:s');
            }

            $events[] = $attributes;
            
            //Editei tentativa de gravar o nome ou id do motorista no banco de dados
            /* if ($event->driver_id)
                $driver = $event->driver_id
            else
                $driver = $event->altitude */
            $queues[] = [
                'user_id'   => $event->user_id,
                'device_id' => $event->device_id,
                'type'      => $event->type,
                'data' => json_encode(array_merge([
                    'altitude'      => $event->speed,
                    'course'        => $event->course,
                    'latitude'      => $event->latitude,
                    'longitude'     => $event->longitude,
                    'speed'         => $event->speed,
                    'time'          => $event->time,
                    'device_name'   => htmlentities($this->device->name),
                ], $event->additionalQueueData))
            ];
            
            /* Uma possível utilização em futuro 
            if ($event->alert_id == 52 || $event->alert_id == 17){
                $Monitoring = new Monitoring([
                    'active' => true,
                    'device_id' => $event->device_id,
                    'cause' => $event->type,
                    'gps_date' => $event->time,
                    //'occ_date' => $occorunce_date,
                    'information' => print_r($event),
                    'make_contact' => false, 
                    //'treated_occurence' => $request->input('treated_occurence'),
                    'sent_maintenance' => false,
                    'automatic_treatment' => false
                ]);
                $Monitoring->save();
            }*/
        }

        $this->events = [];

        $this->device->events()->insert($events);

        DB::table('events_queue')->insert($queues);

    }
}