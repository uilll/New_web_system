<?php

namespace Tobuli\Helpers\GeoLocation\GeoServices;

use Tobuli\Helpers\GeoLocation\Location;

class GeoGoogle implements GeoServiceInterface
{
    private $curl;

    private $url;

    private $requestOptions = [];

    public function __construct()
    {
        $hora_ = date('H');
        if ((int) $hora_ % 2 == 0) {
            $google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';
        } else {
            $google_key = 'AIzaSyBYs1o3hCH3BW2Fk_9Q3_maBuSeKelzZi8';
        }
        $curl = new \Curl;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
        $curl->options['CURLOPT_TIMEOUT'] = 5;

        $this->curl = $curl;
        $this->url = 'https://maps.googleapis.com/maps/api/';
        $this->requestOptions = [
            'language' => config('tobuli.languages.'.config('app.locale').'.iso', 'en'),
            'key' => $google_key,
        ];
    }

    public function byAddress($address)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://sistema.carseg.com.br/get_add.php', [
            'form_params' => [
                'queries' => $address,
            ],
        ]);
        $response = json_decode($response->getBody()->getContents());
        if ($response != null) {
            $response = get_object_vars($response);
        }

        return new Location([
            'place_id' => $response['place_id'],
            'lat' => $response['lat'],
            'lng' => $response['lng'],
            'address' => $response['address'],
            'country' => $response['country'],
            'country_code' => $response['country_code'],
            'state' => $response['state'], //array_get($components, 'administrative_area_level_1'),
            'county' => $response['county'], //array_get($components, 'administrative_area_level_2'),
            'city' => $response['city'], //$response['city'],//array_get($components, 'city'/*'locality'*/),
            'road' => $response['road'],
            'house' => $response['house'],
            'zip' => $response['zip'],
            'type' => $response['type'],
            'dist_' => '', //$response['dist_'],
        ]);
        /* $address = $this->request('geocode', ['address' => $address]);

        return $address ? $this->locationObject($address) : null; */
    }

    public function listByAddress($address)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://sistema.carseg.com.br/get_add.php', [
            'form_params' => [
                'queries' => $address,
            ],
        ]);
        $response = json_decode($response->getBody()->getContents());
        foreach ($response as $local_) {
            $local_ = json_decode($local_);
            //$response = get_object_vars($response);
            $locations[] = new Location([
                'place_id' => $local_->place_id,
                'lat' => $local_->lat,
                'lng' => $local_->lng,
                'address' => $local_->address,
                'country' => $local_->country,
                'country_code' => $local_->country_code,
                'state' => $local_->state, //array_get($components, 'administrative_area_level_1'),
                'county' => $local_->county, //array_get($components, 'administrative_area_level_2'),
                'city' => $local_->city, //$response['city'],//array_get($components, 'city'/*'locality'*/),
                'road' => $local_->road,
                'house' => $local_->house,
                'zip' => $local_->zip,
                'type' => $local_->type,
                'dist_' => $local_->dist_,
            ]);
        }

        return $locations;
        /* //var_dump($address);
        if ( ! $addresses = $this->request('place/autocomplete', ['input' => $address])) {
            return [];
        }
        $locations = [];

        foreach ($addresses as $address) {
            $locations[] = $this->locationObject($address);
        }

        return $locations; */
    }

    public function byCoordinates($lat, $lng)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://sistema.carseg.com.br/get_add.php', [
            'form_params' => [
                'lat' => $lat,
                'lng' => $lng,
                'map' => 'nominatim',
            ],
        ]);
        /*if (Auth::User()->id == 6) {
            //dd($response);
           }*/
        $response = json_decode($response->getBody()->getContents());
        if ($response != null) {
            $response = get_object_vars($response);
        }
        //var_dump($response);

        $city_dist = $response['city'];

        return new Location([
            'place_id' => $response['place_id'],
            'lat' => $response['lat'],
            'lng' => $response['lng'],
            'address' => $response['address'],
            'country' => $response['country'],
            'country_code' => $response['country_code'],
            'state' => $response['state'], //array_get($components, 'administrative_area_level_1'),
            'county' => $response['county'], //array_get($components, 'administrative_area_level_2'),
            'city' => $city_dist, //$response['city'],//array_get($components, 'city'/*'locality'*/),
            'road' => $response['road'],
            'house' => $response['house'],
            'zip' => $response['zip'],
            'type' => $response['type'],
            'dist_' => '', //$response['dist_'],
        ]);
        //echo ('teste');
        /* $address = $this->request('geocode', $lat, $lng);//['latlng' => $lat . ',' . $lng]);

        return $address ? $this->locationObject($address) : null; */
    }

    private function request($method, $options)
    {
        $response = $this->curl->get(
            $this->url.$method.'/json',
            array_merge($options, $this->requestOptions)
        );

        $response_body = json_decode($response->body, true);

        if ($response->headers['Status-Code'] != 200 || array_key_exists('error_message', $response_body)) {
            throw new \Exception(array_get($response_body, 'error_message') ?: 'Geocoder API error.');
        }

        if ($response_body['status'] == 'ZERO_RESULTS') {
            return null;
        }

        return $method == 'geocode' ? $response_body['results'][0] : $response_body['predictions'];
    }
     /* private function request($method, $lat, $lng)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key=AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38');

        //$location = json_decode($response->getBody()->getContents());
        $response_body = json_decode($response->body, true);

        if ($response->headers['Status-Code'] != 200 || array_key_exists('error_message', $response_body)) {
            throw new \Exception(array_get($response_body, 'error_message') ?: 'Geocoder API error.');
        }

        if ($response_body['status'] == 'ZERO_RESULTS') {
            return null;
        }

        return $method == 'geocode' ? $response_body['results'][0] : $response_body['predictions'];
        //return $location;
    }  */

/*     private function locationObject($address)
    {
        $components = [];
        $county = '';
        $city = '';
        $state = '';

        if (array_get($address, 'address_components')) {
            foreach ($address['address_components'] as $component) {
                $components[$component['types'][0]] = $component['long_name'];
                $components[$component['types'][0] . '_short'] = $component['short_name'];
                if ($component['types'][0] == 'administrative_area_level_2') {
                    $county = $component['long_name'];
                }
                if ($component['types'][0] == 'locality') {
                    $city = $component['long_name'];
                }
                if ($component['types'][0] == 'administrative_area_level_1') {
                    $state = $component['short_name'];
                }
            }
        }

        return new Location([
            'place_id'      => array_get($address, 'place_id'),
            'lat'           => array_get($address, 'geometry.location.lat'),
            'lng'           => array_get($address, 'geometry.location.lng'),
            'address'       => array_get($address, 'formatted_address', array_get($address, 'description')),
            'country'       => array_get($components, 'country'),
            'country_code'  => array_get($components, 'country_short'),
            'state'         => $state,//array_get($components, 'administrative_area_level_1'),
            'county'        => $county,//array_get($components, 'administrative_area_level_2'),
            'city'          => $city,//array_get($components, 'city'),
            'road'          => array_get($components, 'route'),
            'house'         => array_get($components, 'street_number'),
            'zip'           => array_get($components, 'postal_code'),
            'type'          => array_get($address['types'], 0),
        ]);
    } */
        private function locationObject($location)
        {
            $lat = '';
            $lng = '';
            $place_id = '';
            $address_for = '';
            $country = '';
            $country_short = '';
            $state = '';
            $county = '';
            $city = '';
            $road = '';
            $house = '';
            $zip = '';
            $type = '';
            $conta = 0;
            //var_dump($location);
            foreach ($location->results as $result) {
                foreach ($result->address_components as $address) {
                    if ($address->types[0] == 'administrative_area_level_1') {
                        $state = $address->short_name;
                    }
                    if ($address->types[0] == 'administrative_area_level_2') {
                        $county = $address->long_name;
                    }
                    if ($address->types[0] == 'locality') {
                        $city = $address->long_name;
                    }
                    if ($address->types[0] == 'country') {
                        $country = $address->long_name;
                    }
                    if ($address->types[0] == 'country') {
                        $country_short = $address->short_name;
                    }
                    if ($address->types[0] == 'route') {
                        $road = $address->long_name;
                    }
                    if ($address->types[0] == 'street_number') {
                        $house = $address->long_name;
                    }
                    if ($address->types[0] == 'postal_code') {
                        $zip = $address->long_name;
                    }
                }
                if ($conta == 0) {
                    if ($result->place_id) {
                        $place_id = $result->place_id;
                    }
                    if ($result->geometry->location->lat) {
                        $lat = $result->geometry->location->lat;
                    }
                    if ($result->geometry->location->lng) {
                        $lng = $result->geometry->location->lng;
                    }
                    if ($result->formatted_address) {
                        $address_for = $result->formatted_address;
                    }
                    if ($result->types) {
                        $type = $result->types;
                    }
                }
                $conta++;
            }

            return new Location([
                'place_id' => $place_id,
                'lat' => $lat,
                'lng' => $lng,
                'address' => $address_for,
                'country' => $country,
                'country_code' => $country_short,
                'state' => $state, //array_get($components, 'administrative_area_level_1'),
                'county' => $county, //array_get($components, 'administrative_area_level_2'),
                'city' => $city, //array_get($components, 'city'/*'locality'*/),
                'road' => $road,
                'house' => $house,
                'zip' => $zip,
                'type' => $type,
            ]);
        }
}
