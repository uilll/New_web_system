<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use Facades\Repositories\DeviceGroupRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\ObjectsListSettingsFormValidator;
use Illuminate\Support\Facades\Cookie;
use Tobuli\Exceptions\ValidationException;

class ObjectsListController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! settings('plugins.object_listview.status')) {
            throw new PermissionException();
        }
    }

    public function index()
    {
        if (! settings('plugins.object_listview.status')) {
            throw new PermissionException();
        }

        $this->checkException('devices', 'view');

        if (request()->ajax()) {
            return view('front::ObjectsList.modal');
        } else {
            return view('front::ObjectsList.index');
        }
    }

    public function items()
    {
        $this->checkException('devices', 'view');

        $device_groups = ['0' => trans('front.ungrouped')] + DeviceGroupRepo::getWhere(['user_id' => $this->user->id])->lists('title', 'id')->all();
        $devices = UserRepo::getDevicesWith($this->user->id, [
            'devices',
            'devices.sensors',
            'devices.services',
            'devices.driver',
            'devices.traccar',
        ]);
        if (Cookie::has('order_list_')) {
            $order_list_ = Cookie::get('order_list_');
        } elseif (! isAdmin()) {
            $order_list_ = 'object_owner';
        } else {
            $order_list_ = 'name';
            //var_dump($devices);
        }

        //$order_list_ = Cookie::get('order_list_');
        //$order_list_ ='object_owner';

        $collection = collect($devices);
        $sortedData = $collection->sortBy($order_list_);
        /*$sortedData = $collection->sortBy($order_list_)->groupBy($order_list_)->map(function (Collection $collection) {
            return $collection->sortBy('object_owner')->groupBy('object_owner');
        });*/

        $devices = (object) $sortedData;

        $settings = UserRepo::getListViewSettings($this->user->id);

        $columns = $settings['columns'];
        $groupby = $settings['groupby'];

        $grouped = [];
        foreach ($devices as $device) {
            $item = [];
            $address = null;

            $item['protocol'] = $device->protocol;
            $item['group'] = isset($device_groups[$device->pivot->group_id]) ? $device_groups[$device->pivot->group_id] : null;

            foreach ($columns as &$column) {
                if ($column['class'] == 'device') {
                    switch ($column['field']) {
                        case 'status':
                            if (empty($item['status'])) {
                                $item['status'] = $device->getStatus();
                            }
                            $item['status_color'] = $device->getStatusColor();
                            break;
                        case 'speed':
                            $item['speed'] = $device->getSpeed();
                            $item['speed'] .= ' '.$this->user->unit_of_speed;

                            break;
                        case 'position':
                            $item['lat'] = $device->lat;
                            $item['lng'] = $device->lng;
                            break;
                        case 'address':
                            if (! $address) {
                                $item['lat'] = $device->lat;
                                $item['lng'] = $device->lng;

                                if ($item['lat'] && $item['lng']) {
                                    $address = getGeoAddress($item['lat'], $item['lng']);
                                }
                            }
                            $item['address'] = $address;
                            break;
                        case 'fuel':
                            $sensor = $device->getFuelTankSensor();

                            if ($sensor) {
                                $item['fuel'] = [
                                    'col1' => $sensor->getPercentage($device->traccar->other).'%',
                                    'col2' => $sensor->getValueFormated($device->traccar->other),
                                    'col3' => $sensor->getValue($device->traccar->other) * $device->fuel_price,
                                ];
                            } else {
                                $item['fuel'] = [
                                    'col1' => '-',
                                    'col2' => '-',
                                    'col3' => '-',
                                ];
                            }
                            break;
                        case 'group':
                            break;
                        default:
                            $item[$column['field']] = $device->{$column['field']};
                    }
                } elseif ($column['class'] == 'sensor') {
                    $item[$column['field']] = null;

                    if ($device->sensors) {
                        foreach ($device->sensors as $sensor) {
                            if ($column['field'] == $sensor->hash) {
                                $column['title'] = $sensor->name;

                                $item[$column['field']] = $sensor->getValueFormated($device->traccar->other);

                                if (! empty($column['color'])) {
                                    foreach ($column['color'] as $color) {
                                        if ($sensor->value >= $color['from'] && $sensor->value <= $color['to']) {
                                            $item['color'][$column['field']] = $color['color'];
                                        }
                                    }
                                }

                                break;
                            }
                        }
                    }
                }
            }

            $grouped[$item[$groupby]][] = $item;
        }

        unset($devices);

        return view('front::ObjectsList.list')->with(compact('grouped', 'columns'));
    }

    public function edit()
    {
        $this->checkException('users', 'edit', $this->user);

        $numeric_sensors = config('tobuli.numeric_sensors');

        $settings = UserRepo::getListViewSettings($this->user->id);

        $fields = config('tobuli.listview_fields');

        listviewTrans($this->user->id, $settings, $fields);

        return view('front::ObjectsList.edit')->with(compact('fields', 'settings', 'numeric_sensors'));
    }

    public function update()
    {
        $this->checkException('users', 'update', $this->user);

        try {
            ObjectsListSettingsFormValidator::validate('update', $this->data);

            UserRepo::setListViewSettings($this->user->id, request()->only(['columns', 'groupby']));

            return ['status' => 1];
        } catch (ValidationException $e) {
            return ['status' => 0, 'errors' => $e->getErrors()];
        }
    }
}
