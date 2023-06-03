<?php

$hora_ = date('H');
if ((int) $hora_ % 2 == 0) {
    $google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';
} else {
    $google_key = 'AIzaSyBYs1o3hCH3BW2Fk_9Q3_maBuSeKelzZi8';
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => '',
        'secret' => '',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model' => 'App\User',
        'secret' => '',
    ],

    'streetview' => [
        'key' => 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38',
    ],

    'snaptoroad' => [
        'key' => 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38',
    ],

    'google_maps' => [
        'key' => $google_key,
    ],

    'stripe' => [
        'secret' => 'sk_test_odPohCSaa5k8MwD7ymewLC53',
    ],
];
