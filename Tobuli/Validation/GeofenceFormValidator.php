<?php namespace Tobuli\Validation;

use Illuminate\Validation\Factory as IlluminateValidator;

class GeofenceFormValidator extends Validator {

    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'name' => 'required',
            'polygon' => 'required',
            'polygon_color' => 'required|min:7|max:7'
        ],
        'update' => [
            'name' => 'required',
            'polygon' => 'required',
            'polygon_color' => 'required|min:7|max:7'
        ]
    ];

    public function __construct( IlluminateValidator $validator ) {
        $this->_validator = $validator;

        $userGeofenceGroups = auth()->user()->geofenceGroups->pluck('id')->all();
        $userGeofenceGroups[] = 0;

        $this->rules['create']['group_id'] = 'in:'.implode(',', $userGeofenceGroups);
        $this->rules['update']['group_id'] = 'in:'.implode(',', $userGeofenceGroups);
    }

}   //end of class


//EOF