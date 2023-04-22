<?php namespace Tobuli\Validation;

class SendCommandFormValidator extends Validator {

    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'devices' => 'required|array|array_max:10',
            'message' => 'required',
            'gprs_template_id' => 'required_if:type,template'
        ],
        'update' => [
        ]
    ];

}   //end of class


//EOF