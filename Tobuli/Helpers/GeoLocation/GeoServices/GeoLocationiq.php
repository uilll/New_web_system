<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;

use Tobuli\Helpers\GeoLocation\Location;

class GeoLocationiq implements GeoServiceInterface
{
    protected $url;

    protected $curl;

    private $requestOptions = [];

    public function __construct()
    {
        $curl = new \Curl;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
        $curl->options['CURLOPT_TIMEOUT'] = 5;

        $this->curl = $curl;
        $this->url = 'http://locationiq.org/v1/';
        $this->requestOptions = [
            'format' => 'json',
            'key' => settings('main_settings.api_key'),
            'accept-language' => config('tobuli.languages.'.config('app.locale').'.iso', 'en'),
            'addressdetails' => 1,
        ];
    }

    public function byAddress($address)
    {
        $addresses = $this->request('search', ['q' => $address]);

        return $addresses ? $this->locationObject($addresses[0]) : null;
    }

    public function listByAddress($address)
    {
        if (! $addresses = $this->request('search', ['q' => $address])) {
            return [];
        }

        $locations = [];

        foreach ($addresses as $address) {
            $locations[] = $this->locationObject($address);
        }

        return $locations;
    }

    public function byCoordinates($lat, $lng)
    {
        $address = $this->request('reverse', ['lat' => $lat, 'lon' => $lng]);

        return $address ? $this->locationObject($address) : null;
    }

    private function request($method, $options)
    {
        $response = $this->curl->get(
            $this->url.$method.'.php',
            array_merge($options, $this->requestOptions)
        );

        $response_body = json_decode($response->body, true);

        if ($response->headers['Status-Code'] != 200) {
            throw new \Exception(array_get($response_body, 'error'));
        }

        return (is_array($response_body) && ! empty($response_body)) ? $response_body : null;
    }

    private function locationObject($address)
    {
        return new Location([
            'place_id' => array_get($address, 'place_id'),
            'lat' => array_get($address, 'lat'),
            'lng' => array_get($address, 'lon'),
            'address' => array_get($address, 'display_name'),
            'type' => array_get($address, 'type'),
            'country' => array_get($address['address'], 'country'),
            'country_code' => array_get($address['address'], 'country_code'),
            'county' => array_get($address['address'], 'county'),
            'state' => array_get($address['address'], 'state'),
            'city' => array_get($address['address'], 'city'),
            'road' => array_get($address['address'], 'road'),
            'house' => array_get($address['address'], 'house_number'),
            'zip' => array_get($address['address'], 'postcode'),
        ]);
    }
}
