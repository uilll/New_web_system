<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.3.15
 * Time: 16.20
 */

namespace Tobuli\Validation;

class TasksFormValidator extends Validator
{
    public $rules = [
        'create' => [
            'title' => 'required',
            'device_id' => 'required|exists:devices,id',
            'priority' => 'required',
            'pickup_address' => 'required',
            'pickup_time_from' => 'required|date',
            'pickup_time_to' => 'required|date|after:pickup_time_from',
            'delivery_address' => 'required',
            'delivery_time_from' => 'required|date',
            'delivery_time_to' => 'required|date|after:delivery_time_from',
        ],
        'update' => [
            'title' => 'required',
            'device_id' => 'required|exists:devices,id',
            'priority' => 'required',
            'pickup_address' => 'required',
            'pickup_time_from' => 'required|date',
            'pickup_time_to' => 'required|date|after:pickup_time_from',
            'delivery_address' => 'required',
            'delivery_time_from' => 'required|date',
            'delivery_time_to' => 'required|date|after:delivery_time_from',
        ],
    ];
}
