<?php

namespace Tobuli\Validation;

class AlertFormValidator extends Validator
{
    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'name' => 'required',
            'type' => 'required',
            'devices' => 'required|array',

            'drivers' => 'required_if:type,driver|array',
            'events_custom' => 'required_if:type,custom|array',
            'geofences' => 'required_if:type,geofence_in,geofence_out,geofence_inout|array',

            'zone' => 'in:0,1,2',
            'zones' => 'required_if:zone,1,2|array',

            'schedule' => 'in:0,1',
            'schedules' => 'required_if:schedule,1',

            'overspeed' => 'required_if:type,overspeed|numeric',
            'stop_duration' => 'required_if:type,stop_duration|numeric',

            'command.active' => 'in:0,1',
            'command.type' => 'required_if:command.active,1',
        ],
        'update' => [
            'name' => 'required',
            'type' => 'required',
            'devices' => 'required|array',

            'drivers' => 'required_if:type,driver|array',
            'events_custom' => 'required_if:type,custom|array',
            'geofences' => 'required_if:type,geofence_in,geofence_out,geofence_inout|array',

            'zone' => 'in:0,1,2',
            'zones' => 'required_if:zone,1,2|array',

            'overspeed' => 'required_if:type,overspeed|numeric',
            'stop_duration' => 'required_if:type,stop_duration|numeric',

            'command.active' => 'in:0,1',
            'command.type' => 'required_if:command.active,1',
        ],
        'commands' => [
            'devices' => 'required|array',
        ],
        'devices' => [
            'devices' => 'required|array',
        ],
        /*
        'create' => [
            'name' => 'required',
            //'email' => 'required',
            'devices' => 'required|array',
            'geofences' => 'array',
            'overspeed.speed' => 'numeric',
            'overspeed.distance' => 'in:1,2',
            'stop_duration' => 'numeric',
        ],

        'update' => [
            'name' => 'required',
            //'email' => 'required',
            'devices' => 'required|array',
            'geofences' => 'array',
            'overspeed.speed' => 'numeric',
            'overspeed.distance' => 'in:1,2',
            'stop_duration' => 'numeric',
        ]
        */
    ];
}   //end of class

//EOF
