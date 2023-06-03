<?php

namespace ModalHelpers;

use Facades\ModalHelpers\CustomEventModalHelper;
use Facades\ModalHelpers\SendCommandModalHelper;
use Facades\Repositories\AlertRepo;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\EventCustomRepo;
use Facades\Repositories\GeofenceRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\AlertFormValidator;
use Illuminate\Support\Facades\Validator;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Protocols\Commands;

class AlertModalHelper extends ModalHelper
{
    public function get()
    {
        try {
            $this->checkException('alerts', 'view');
        } catch (\Exception $e) {
            return ['alerts' => []];
        }

        if ($this->api) {
            $alerts = AlertRepo::getWithWhere(['devices', 'drivers', 'geofences', 'events_custom'], ['user_id' => $this->user->id]);
            $alerts = $alerts->toArray();

            foreach ($alerts as $key => $alert) {
                $drivers = [];
                foreach ($alert['drivers'] as $driver) {
                    array_push($drivers, $driver['id']);
                }

                $alerts[$key]['drivers'] = $drivers;

                $devices = [];
                foreach ($alert['devices'] as $device) {
                    array_push($devices, $device['id']);
                }

                $alerts[$key]['devices'] = $devices;

                $geofences = [];
                foreach ($alert['geofences'] as $geofence) {
                    array_push($geofences, $geofence['id']);
                }

                $alerts[$key]['geofences'] = $geofences;

                $events_custom = [];
                foreach ($alert['events_custom'] as $event) {
                    array_push($events_custom, $event['id']);
                }

                $alerts[$key]['events_custom'] = $events_custom;
            }
        } else {
            $alerts = AlertRepo::getWhere(['user_id' => $this->user->id]);
        }

        return compact('alerts');
    }

    public function createData()
    {
        $this->checkException('alerts', 'create');

        $devices = UserRepo::getDevices($this->user->id)->lists('plate_number', 'id')->all();
        $geofences = GeofenceRepo::whereUserId($this->user->id)->lists('name', 'id')->all();

        if (empty($devices)) {
            throw new ValidationException(['id' => trans('front.must_have_one_device')]);
        }

        $types = $this->getTypes();
        $schedules = $this->getSchedules();
        $notifications = $this->getNotifications();

        $alert_zones = [
            '1' => trans('front.zone_in'),
            '2' => trans('front.zone_out'),
        ];

        if ($this->api) {
            $devices = apiArray($devices);
            $geofences = apiArray($geofences);
            $alert_zones = apiArray($alert_zones);
        }

        return compact(
            'devices',
            'geofences',

            'types',
            'schedules',
            'notifications',
            'alert_zones'
        );
    }

    public function create()
    {
        $this->checkException('alerts', 'store');

        $this->validate('create');

        beginTransaction();
        try {
            $alert = $this->user->alerts()->create($this->data);

            $alert->devices()->sync(array_get($this->data, 'devices', []));
            $alert->geofences()->sync(array_get($this->data, 'geofences', []));
            $alert->drivers()->sync(array_get($this->data, 'drivers', []));
            $alert->zones()->sync(array_get($this->data, 'zones', []));

            $events_custom = array_get($this->data, 'events_custom', []);
            if ($events_custom) {
                $protocols = DeviceRepo::getProtocols($this->data['devices']);
                $events = EventCustomRepo::whereProtocols($events_custom, $protocols->pluck('protocol')->all());
                $events_custom = $events->pluck('id')->all();
            }
            $alert->events_custom()->sync($events_custom);
        } catch (\Exception $e) {
            rollbackTransaction();
            throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
        }

        commitTransaction();

        return ['status' => 1];
    }

    public function editData()
    {
        $id = array_key_exists('alert_id', $this->data) ? $this->data['alert_id'] : request()->route('alerts');

        $item = AlertRepo::findWithAttributes($id);

        $this->checkException('alerts', 'edit', $item);

        $devices = UserRepo::getDevicesWith($this->user->id, [
            'devices',
            'devices.traccar',
        ]);

        if (empty($devices)) {
            throw new ValidationException(['id' => trans('front.must_have_one_device')]);
        }

        $types = $this->getTypes($item);
        $schedules = $this->getSchedules($item);
        $notifications = $this->getNotifications($item);
        //dd('oi2');
        $commands = SendCommandModalHelper::getCommands($devices);
        //dd('oi2');
        $devices = $devices->lists('plate_number', 'id')->all();
        $geofences = GeofenceRepo::whereUserId($this->user->id)->lists('name', 'id')->all();
        //dd('oi');
        $alert_zones = [
            '1' => trans('front.zone_in'),
            '2' => trans('front.zone_out'),
        ];

        if ($this->api) {
            $devices = apiArray($devices);
            $geofences = apiArray($geofences);
            $alert_zones = apiArray($alert_zones);
        }

        return compact(
            'item',
            'devices',
            'geofences',

            'types',
            'schedules',
            'notifications',
            'alert_zones',
            'commands'
        );
    }

    public function edit()
    {
        $alert = AlertRepo::findWithAttributes($this->data['id']);

        $this->checkException('alerts', 'update', $alert);

        $this->validate('update');

        beginTransaction();
        try {
            AlertRepo::update($alert->id, $this->data);

            $alert->devices()->sync(array_get($this->data, 'devices', []));
            $alert->geofences()->sync(array_get($this->data, 'geofences', []));
            $alert->drivers()->sync(array_get($this->data, 'drivers', []));
            $alert->zones()->sync(array_get($this->data, 'zones', []));

            $events_custom = array_get($this->data, 'events_custom', []);
            if ($events_custom) {
                $protocols = DeviceRepo::getProtocols($this->data['devices']);
                $events = EventCustomRepo::whereProtocols($events_custom, $protocols->pluck('protocol')->all());
                $events_custom = $events->pluck('id')->all();
            }
            $alert->events_custom()->sync($events_custom);
        } catch (\Exception $e) {
            rollbackTransaction();
            throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
        }

        commitTransaction();

        return ['status' => 1];
    }

    private function validate($type)
    {
        $alert_id = array_get($this->data, 'id');

        AlertFormValidator::validate($type, $this->data, $alert_id);

        foreach (array_get($this->data, 'schedules', []) as $weekday => $schedule) {
            $validator = null;

            switch ($weekday) {
                case 'monday':
                case 'tuesday':
                case 'wednesday':
                case 'thursday':
                case 'friday':
                case 'saturday':
                case 'sunday':
                    $validator = Validator::make($this->data, ["schedules.$weekday" => 'required|array']);
                    $validator->each("schedules.$weekday", ['in:'.implode(',', array_keys(config('tobuli.history_time')))]);
                    break;
                default:
                    throw new ValidationException(["schedules.$weekday" => 'Wrong week day.']);
            }

            if ($validator && $validator->fails()) {
                throw new ValidationException(['schedule' => $validator->errors()->first()]);
            }
        }

        foreach (array_get($this->data, 'notifications', []) as $name => $notification) {
            $validator = null;
            $active = array_get($notification, 'active', false) ? true : false;

            switch ($name) {
                case 'sound':
                case 'push':
                    break;
                case 'email':
                    if ($active) {
                        $notification['input'] = semicol_explode(array_get($notification, 'input'));
                        $validator = Validator::make($notification, ['input' => 'required|array_max:'.config('tobuli.limits.alert_emails')]);
                        $validator->each('input', ['email']);
                    }

                    break;
                case 'webhook':
                    if ($active) {
                        $notification['input'] = semicol_explode(array_get($notification, 'input'));
                        $validator = Validator::make($notification, ['input' => 'required|array_max:'.config('tobuli.limits.alert_webhooks')]);
                        $validator->each('input', ['url']);
                    }

                    break;
                case 'sms':
                    if ($active) {
                        $notification['input'] = semicol_explode(array_get($notification, 'input'));
                        $validator = Validator::make($notification, ['input' => 'required|array_max:'.config('tobuli.limits.alert_phones')]);
                    }
                    break;
                default:
                    throw new ValidationException(["notifications.$name" => 'Notification type not supported.']);
            }

            if ($validator && $validator->fails()) {
                throw new ValidationException(["notifications.$name.input" => $validator->errors()->first()]);
            }

            $this->data['notifications'][$name] = array_only($this->data['notifications'][$name], ['active', 'input']);
        }

        if (array_get($this->data, 'command.active')) {
            $devices = DeviceRepo::getWhereIn($this->data['devices']);
            $commands = SendCommandModalHelper::getCommands($devices);
            $rules = Commands::validationRules(array_get($this->data, 'command.type'), $commands);
            $validator = Validator::make($this->data, $rules);
            if ($validator->fails()) {
                throw new ValidationException($validator->messages());
            }

            if ($rules) {
                $this->data['command'] = array_merge(
                    array_only($this->data, array_keys($rules)),
                    $this->data['command']
                );
            }
        }
    }

    public function changeActive()
    {
        $item = AlertRepo::find($this->data['id']);

        $this->checkException('alerts', 'active', $item);

        AlertRepo::update($item->id, ['active' => ($this->data['active'] == 'true')]);

        return ['status' => 1];
    }

    public function doDestroy($id)
    {
        $item = AlertRepo::find($id);

        $this->checkException('alerts', 'remove', $item);

        return compact('item');
    }

    public function destroy()
    {
        $id = array_key_exists('alert_id', $this->data) ? $this->data['alert_id'] : $this->data['id'];

        $item = AlertRepo::findWithAttributes($id);

        $this->checkException('alerts', 'remove', $item);

        AlertRepo::delete($id);

        return ['status' => 1];
    }

    public function getTypes($alert = null)
    {
        $drivers = UserRepo::getDrivers($this->user->id);
        $drivers->map(function ($item) {
            $item['title'] = $item['name'];

            return $item;
        })->only('id', 'title')->all();

        $geofences = GeofenceRepo::whereUserId($this->user->id);
        $geofences->map(function ($item) {
            $item['title'] = $item['name'];

            return $item;
        })->only('id', 'title')->all();

        if ($alert) {
            $events_custom = CustomEventModalHelper::getGroupedEvents($alert->devices->pluck('id')->all());
        } else {
            $events_custom = [];
        }

        return [
            [
                'type' => 'overspeed',
                'title' => trans('front.overspeed'),
                'attributes' => [
                    [
                        'name' => 'overspeed',
                        'title' => trans('validation.attributes.overspeed').'('.$this->user->unit_of_speed.')',
                        'type' => 'integer',
                        'default' => $alert ? $alert->overspeed : '',
                    ],
                ],
            ],
            [
                'type' => 'stop_duration',
                'title' => trans('front.stop_duration'),
                'attributes' => [
                    [
                        'name' => 'stop_duration',
                        'title' => trans('validation.attributes.stop_duration_longer_than').'('.trans('front.minutes').')',
                        'type' => 'integer',
                        'default' => $alert ? $alert->stop_duration : '',
                    ],
                ],
            ],
            [
                'type' => 'offline_duration',
                'title' => trans('front.offline_duration'),
                'attributes' => [
                    [
                        'name' => 'offline_duration',
                        'title' => trans('validation.attributes.offline_duration_longer_than').'('.trans('front.minutes').')',
                        'type' => 'integer',
                        'default' => $alert ? $alert->offline_duration : '',
                    ],
                ],
            ],
            [
                'type' => 'driver',
                'title' => trans('front.driver_change'),
                'attributes' => [
                    [
                        'name' => 'drivers',
                        'title' => trans('front.drivers').':',
                        'type' => 'multiselect',
                        'options' => $drivers,
                        'default' => $alert ? $alert->drivers->pluck('id')->all() : [],
                    ],
                ],
            ],

            [
                'type' => 'geofence_in',
                'title' => trans('front.geofence').' '.trans('global.in'),
                'attributes' => [
                    [
                        'name' => 'geofences',
                        'title' => trans('validation.attributes.geofences'),
                        'type' => 'multiselect',
                        'options' => $geofences,
                        'default' => $alert ? $alert->geofences->pluck('id')->all() : [],
                    ],
                ],
            ],
            [
                'type' => 'geofence_out',
                'title' => trans('front.geofence').' '.trans('global.out'),
                'attributes' => [
                    [
                        'name' => 'geofences',
                        'title' => trans('validation.attributes.geofences'),
                        'type' => 'multiselect',
                        'options' => $geofences,
                        'default' => $alert ? $alert->geofences->pluck('id')->all() : [],
                    ],
                ],
            ],
            [
                'type' => 'geofence_inout',
                'title' => trans('front.geofence').' '.trans('global.in').'/'.trans('global.out'),
                'attributes' => [
                    [
                        'name' => 'geofences',
                        'title' => trans('validation.attributes.geofences'),
                        'type' => 'multiselect',
                        'options' => $geofences,
                        'default' => $alert ? $alert->geofences->pluck('id')->all() : [],
                    ],
                ],
            ],
            [
                'type' => 'custom',
                'title' => trans('front.custom_events'),
                'attributes' => [
                    [
                        'name' => 'events_custom',
                        'title' => trans('validation.attributes.event'),
                        'type' => 'multiselect',
                        'options' => $events_custom,
                        'default' => $alert ? $alert->events_custom->pluck('id')->all() : [],
                        'description' => trans('front.alert_events_tip'),
                    ],
                ],
            ],
            [
                'type' => 'sos',
                'title' => 'SOS',
            ],
            [
                'type' => 'outdated_gps',
                'title' => 'GPS desatualizado',

            ],
        ];
    }

    public function getSchedules($alert = null)
    {
        $weekdays = [
            'monday' => trans('front.monday'),
            'tuesday' => trans('front.tuesday'),
            'wednesday' => trans('front.wednesday'),
            'thursday' => trans('front.thursday'),
            'friday' => trans('front.friday'),
            'saturday' => trans('front.saturday'),
            'sunday' => trans('front.sunday'),
        ];

        $schedules = [];

        foreach ($weekdays as $weekday => $title) {
            $items = [];
            $actives = $alert ? array_get($alert->schedules, $weekday, []) : [];

            foreach (config('tobuli.history_time') as $time => $displayTime) {
                $items[] = [
                    'id' => $time,
                    'title' => $displayTime,
                    'active' => $alert ? in_array($time, $actives) : false,
                ];
            }

            $schedules[] = [
                'id' => $weekday,
                'title' => $title,
                'items' => $items,
            ];
        }

        $invert = $this->user ? (8 - $this->user->week_start_day) % 7 : 0;
        while ($invert-- > 0) {
            array_unshift($schedules, array_pop($schedules));
        }

        return $schedules;
    }

    public function getNotifications($alert = null)
    {
        $notifications = [
            [
                'active' => $alert ? array_get($alert, 'notifications.sound.active', true) : true,
                'name' => 'sound',
                'title' => trans('validation.attributes.sound_notification'),
            ],
            [
                'active' => $alert ? array_get($alert, 'notifications.push.active', true) : true,
                'name' => 'push',
                'title' => trans('validation.attributes.push_notification'),
            ],
            [
                'active' => $alert ? array_get($alert, 'notifications.email.active', false) : false,
                'name' => 'email',
                'title' => trans('validation.attributes.email_notification'),
                'input' => $alert ? array_get($alert, 'notifications.email.input', '') : '',
                'description' => trans('front.email_semicolon'),
            ],
            [
                'active' => $alert ? array_get($alert, 'notifications.sms.active', false) : false,
                'name' => 'sms',
                'title' => trans('validation.attributes.sms_notification'),
                'input' => $alert ? array_get($alert, 'notifications.sms.input', '') : '',
                'description' => trans('front.sms_semicolon'),
            ],
            [
                'active' => $alert ? array_get($alert, 'notifications.webhook.active', false) : false,
                'name' => 'webhook',
                'title' => trans('validation.attributes.webhook_notification'),
                'input' => $alert ? array_get($alert, 'notifications.webhook.input', '') : '',
                'description' => trans('front.webhook'),
            ],
        ];

        if (! auth()->user()->sms_gateway) {
            $notifications = array_where($notifications, function ($index, $notification) {
                return $notification['name'] != 'sms';
            });
        }

        // indexes reset with array_values
        return array_values($notifications);
    }

    public function getCommands()
    {
        AlertFormValidator::validate('commands', $this->data);

        $devices = DeviceRepo::getWhereIn($this->data['devices']);

        $commands = SendCommandModalHelper::getCommands($devices);
        /*
                foreach ($commands as &$command) {
                    if (empty($command['attributes']))
                        continue;

                    foreach($command['attributes'] as &$attribute) {
                        $attribute['name'] = 'command[' .$attribute['name'] . ']';
                    }
                }
        */
        return $commands;
    }

    public function syncDevices()
    {
        $alert = AlertRepo::findWithAttributes($this->data['alert_id']);

        $this->checkException('alerts', 'update', $alert);

        AlertFormValidator::validate('devices', $this->data);

        $alert->devices()->sync(array_get($this->data, 'devices', []));

        return ['status' => 1];
    }
}
