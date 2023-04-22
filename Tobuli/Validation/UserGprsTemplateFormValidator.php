<?php namespace Tobuli\Validation;

use Facades\Repositories\TrackerPortRepo;
use Illuminate\Validation\Factory as IlluminateValidator;

class UserGprsTemplateFormValidator extends Validator {

    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'create' => [
            'title' => 'required',
            'message' => 'required'
        ],
        'update' => [
            'title' => 'required',
            'message' => 'required'
        ]
    ];

    public function __construct( IlluminateValidator $validator ) {
        $this->_validator = $validator;

        $protocols = TrackerPortRepo::getProtocolList();

        $this->rules['create']['protocol'] = 'in:0,,' . implode(',', array_keys($protocols));
        $this->rules['update']['protocol'] = 'in:0,,' . implode(',', array_keys($protocols));
    }

}   //end of class


//EOF