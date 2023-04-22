<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;


use Tobuli\Helpers\GeoLocation\Location;

class GeoNominatim implements GeoServiceInterface
{
    protected $url;
    protected $curl;

    private $requestOptions = [];


    public function __construct()
    {
        $curl = new \Curl;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
        $curl->options['CURLOPT_TIMEOUT'] = 5;

        $lang = config('tobuli.languages.' . config('app.locale') . '.iso', 'en');

        $this->curl = $curl;
        $this->url = settings('main_settings.api_url');
        $this->requestOptions = [
            'format'          => 'json',
            'accept-language' => $lang,
            'addressdetails'  => 1,
        ];
    }


    public function byAddress($address)
    {
        $addresses = $this->request('search', ['q' => $address]);

        return $addresses ? $this->locationObject($addresses[0]) : null;
    }

    public function listByAddress($address)
    {
        if ( ! $addresses = $this->request('search', ['q' => $address])) {
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
            $this->url . $method . '.php',
            array_merge($options, $this->requestOptions)
        );

        if ( ! in_array($response->headers['Status-Code'], [200])) {
            $this->throwException($response->headers['Status-Code']);
        }

        $response_body = json_decode($response->body, true);

        if (empty($response_body))
            $this->throwException(404);

        if (array_key_exists('error', $response_body)) {
            throw new \Exception(array_get($response_body, 'error'));
        }

        return (is_array($response_body) && ! empty($response_body)) ? $response_body : null;
    }


    private function locationObject($address)
    {
        return new Location([
            'place_id'      => array_get($address, 'place_id'),
            'lat'           => array_get($address, 'lat'),
            'lng'           => array_get($address, 'lon'),
            'address'       => array_get($address, 'display_name'),
            'type'          => array_get($address, 'osm_type'),
            'country'       => array_get($address['address'], 'country'),
            'country_code'  => array_get($address['address'], 'country_code'),
            'county'        => array_get($address['address'], 'city', array_get($address['address'], 'town')),
            'state'         => array_get($address['address'], 'state'),
            'city'          => array_get($address['address'], 'city', array_get($address['address'], 'town')),
            'road'          => array_get($address['address'], 'road'),
            'house'         => array_get($address['address'], 'house_number'),
            'zip'           => array_get($address['address'], 'postcode'),
        ]);
    }


    private function throwException($status_code)
    {
        switch ($status_code) {
            case 429:
                throw new \Exception('Geocoder API request limit exceeded. OpenStreetView');
                break;
            case 404:
                throw new \Exception('Unable to geocode');
                break;
            case 401:
                throw new \Exception('API Key provided is invalid or inactive');
                break;
            default:
                throw new \Exception('Geocoder API error.');
        }
    }
}