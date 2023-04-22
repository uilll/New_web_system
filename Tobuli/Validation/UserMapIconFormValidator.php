<?php namespace Tobuli\Validation;

class UserMapIconFormValidator extends Validator {

    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'name' => 'required',
            'map_icon_id' => 'required',
            'coordinates' => 'required'
        ],
        'update' => [
            'name' => 'required',
            'map_icon_id' => 'required',
            'coordinates' => 'required'
        ]
    ];

}   //end of class


//EOF