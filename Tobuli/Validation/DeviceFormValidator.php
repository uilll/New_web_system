<?php

namespace Tobuli\Validation;

use Illuminate\Validation\Factory as IlluminateValidator;

class DeviceFormValidator extends Validator
{
    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'imei' => 'required',
            'name' => 'required',
            'icon_id' => 'required|exists:device_icons,id',
            'fuel_quantity' => 'numeric',
            'fuel_price' => 'numeric',
            'fuel_measurement_id' => 'required',
            'tail_length' => 'required|numeric|min:0|max:10',
            'min_moving_speed' => 'required|numeric|min:1|max:50',
            'min_fuel_fillings' => 'required|numeric|min:1|max:1000',
            'min_fuel_thefts' => 'required|numeric|min:1|max:1000',
            'group_id' => 'exists:device_groups,id',
            'sim_number' => 'unique:devices,sim_number',

            'forward.ip' => 'required_if:forward.active,1|regex:[0-9]+(?:\.[0-9]+){3}:[0-9]+',
            'forward.protocol' => 'required_if:forward.active,1|in:TCP,UDP',
        ],
        'update' => [
            'imei' => 'required|unique:devices,imei,%s',
            'name' => 'required',
            'icon_id' => 'required|exists:device_icons,id',
            'fuel_quantity' => 'numeric',
            'fuel_price' => 'numeric',
            'fuel_measurement_id' => 'required',
            'tail_length' => 'required|numeric|min:0|max:10',
            'min_moving_speed' => 'required|numeric|min:1|max:50',
            'min_fuel_fillings' => 'required|numeric|min:1|max:1000',
            'min_fuel_thefts' => 'required|numeric|min:1|max:1000',
            'group_id' => 'exists:device_groups,id',
            'sim_number' => 'unique:devices,sim_number,%s',

            'forward.ip' => 'required_if:forward.active,1|ip_port',
            'forward.protocol' => 'required_if:forward.active,1|in:TCP,UDP',
        ],
    ];

    public function __construct(IlluminateValidator $validator)
    {
        $this->_validator = $validator;

        $this->rules['create']['group_id'] = 'exists:device_groups,id,user_id,'.auth()->user()->id;
        $this->rules['update']['group_id'] = 'exists:device_groups,id,user_id,'.auth()->user()->id;
    }
}   //end of class

//EOF
