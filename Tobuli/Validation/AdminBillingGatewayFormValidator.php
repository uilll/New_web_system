<?php

namespace Tobuli\Validation;

class AdminBillingGatewayFormValidator extends Validator
{
    /**
     * @var array Validation rules for the test form, they can contain in-built Laravel rules or our custom rules
     */
    public $rules = [
        'update' => [
            'payment_type' => 'required|in:paypal,stripe',
            'paypal_client_id' => 'required_if:payment_type,paypal',
            'paypal_secret' => 'required_if:payment_type,paypal',
            'paypal_currency' => 'required_if:payment_type,paypal',
            'paypal_payment_name' => 'required_if:payment_type,paypal',
            'stripe_currency' => 'required_if:payment_type,stripe',
            'stripe_public_key' => 'required_if:payment_type,stripe',
            'stripe_secret_key' => 'required_if:payment_type,stripe',
        ],
    ];
}   //end of class

//EOF
