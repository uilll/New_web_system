<?php

require '/var/www/html/releases/20190129073809/vendor/autoload.php';

//  CÓDIIGO ABAIXO FUNCIONAL COMO GET.PHP ##################################################################################
// Recebe o parâmetro via POST
$lat = strip_tags((isset($_POST['lat'])) ? $_POST['lat'] : '');
$lng = strip_tags((isset($_POST['lng'])) ? $_POST['lng'] : '');
$map = strip_tags((isset($_POST['map'])) ? $_POST['map'] : '');
$queries = strip_tags((isset($_POST['queries'])) ? $_POST['queries'] : '');
$city = strip_tags((isset($_POST['city'])) ? $_POST['city'] : '');
$stret = strip_tags((isset($_POST['street'])) ? $_POST['street'] : '');
/* $hora_ = date("H");
if((int)$hora_ % 2 == 0){
    $google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';
} else {
    $google_key = 'AIzaSyBYs1o3hCH3BW2Fk_9Q3_maBuSeKelzZi8';
} */
if (intval(date(H)) % 2 == 0) {
    $google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';
} else {
    $google_key = 'AIzaSyBYs1o3hCH3BW2Fk_9Q3_maBuSeKelzZi8';
}
//$google_key = 'AIzaSyBYs1o3hCH3BW2Fk_9Q3_maBuSeKelzZi8';
//$google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';

if (! empty($lat) && ! empty($lng)) {
    if ($map == 'google') {
        $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key='.$google_key);
        $location = json_decode($json);
        echo json_encode(process_google($location));
    } else {
        $server = 'nominatimosrm.carseg.com.br'; // or IP address https://nominatim.carseg.com.br/nominatim/status.php
        $url = 'https://'.$server.'/nominatim/reverse?format=json&lat='.urlencode($lat).'&lon='.urlencode($lng);
        $contents = file_get_contents($url);
        $location = get_object_vars(json_decode($contents));
        $address = get_object_vars($location['address']);
        $address_ = array_get($address, 'road');
        if (! is_null(array_get($address, 'house_number'))) {
            $address_ .= ', '.array_get($address, 'house_number');
        }
        $city = array_get($address, 'city', array_get($address, 'town', array_get($address, 'village')));
        if (is_null($city)) {
            $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key='.$google_key);
            $location = process_google(json_decode($json));
            $city = array_get($location, 'county');

            if (is_null($city)) {
                $msg = $lat.','.$lng.' ['.date('d.m.y')."] (auto-city) \r\n";
                $fp = fopen('/var/www/html/releases/20190129073809/public/report001.txt', 'a+');
                fwrite($fp, $msg);
                fclose($fp);
            }
            //$address_ = array_get($location, 'address');
            //$state = array_get($location, 'state');
        }
        if (false) {
            $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key='.$google_key);
            $location2 = process_google(json_decode($json));
            $address_ = array_get($location2, 'address');
            //$state = array_get($location, 'state');
            if (is_null($address_)) {
                $msg = $lat.','.$lng."(auto-address) \r\n";
                $fp = fopen('/var/www/html/releases/20190129073809/public/report001.txt', 'a+');
                fwrite($fp, $msg);
                fclose($fp);
            }
        }
        $state = array_get($address, 'state');
        $distance = number_format(get_distance($lat, $lng, $city, $state), 2, ',', '.');
        if ($city == 'SP') {
            $city = 'São Paulo';
        }
        if ($city == 'SSA') {
            $city = 'Salvador';
        }
        if ($city == 'LEM') {
            $city = 'Luis Eduardo Magalhães';
        }
        if ($city == 'VCA') {
            $city = 'Vitória da conquista';
        }
        $city .= ' - '.$distance.' km';
        $fulladdress = $address_.', '.$city.'-'.array_get($address, 'state');

        $Location = [
            'place_id' => array_get($location, 'place_id'),
            'lat' => array_get($location, 'lat'),
            'lng' => array_get($location, 'lon'),
            'address' => $address_,
            'type' => array_get($location, 'osm_type'),
            'country' => array_get($address, 'country'),
            'country_code' => array_get($address, 'country_code'),
            'county' => $city,
            'state' => $state,
            'city' => $city,
            'road' => array_get($address, 'road'),
            'house' => array_get($address, 'house_number'),
            'zip' => array_get($address, 'postcode'),
            'dist_' => $distance,
        ];
        echo json_encode($Location);
    }
}

if (! empty($queries)) {
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
    $response_ = [];
    $responselat[] = null;
    $responselng[] = null;

    $server = 'nominatimosrm.carseg.com.br'; // or IP address
    $url = 'https://'.$server.'/nominatim/search?format=json&countrycodes=br&q='.urlencode($queries);
    //echo $url;
    $contents = file_get_contents($url);
    $dec_contents = json_decode($contents);

    if (empty($dec_contents)) {
        //echo "teste";
        $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$queries.'&key='.$google_key);
        $response_ = process_google(json_decode($json));
    } else {
        $client = new \GuzzleHttp\Client();
        if (count($dec_contents) == 1) {
            $lat_city = $dec_contents[0]->lat;
            $lon_city = $dec_contents[0]->lon;
            $response[] = $client->request('POST', 'https://sistema.carseg.com.br/get_add.php', [
                'form_params' => [
                    'lat' => $lat_city,
                    'lng' => $lon_city,
                    'map' => 'nominatim',
                ],
            ]);
        } else {
            foreach ($dec_contents as $data1) {
                $data2 = json_decode(json_encode($data1), true);
                $response = $client->request('POST', 'https://sistema.carseg.com.br/get_add.php', [
                    'form_params' => [
                        'lat' => $data1->lat,
                        'lng' => $data1->lon,
                        'map' => 'nominatim',
                    ],
                ]);
                $response_[] = $response->getBody()->getContents();
            }
        }

        echo json_encode($response_);
    }

    //var_dump ($response);
    //echo ( $response);
}

function get_distance($lat_local, $lon_local, $city, $state)
{
    $server = 'nominatimosrm.carseg.com.br'; // or IP address
    $url = 'https://'.$server.'/nominatim/search?format=json&city='.urlencode($city).'&state='.urlencode($state);
    $contents = file_get_contents($url);
    $dec_contents = json_decode($contents);

    if (count($dec_contents) == 1) {
        $lat_city = $dec_contents[0]->lat;
        $lon_city = $dec_contents[0]->lon;
    } else {
        foreach ($dec_contents as $data1) {
            if (is_null($importance2)) {
                $importance2 = 0;
            }
            $importance = $data1->importance;
            if ($data1->importance > $importance2) {
                $lat_city = $data1->lat;
                $lon_city = $data1->lon;
                $importance2 = $data1->importance;
            }
        }
    }
    $console = $lat_city.', '.$lon_city.'; '.$lat_local.', '.$lon_local;

    //Get distance from routes_server
    // http://18.228.124.149:5000/route/v1/driving/13.388860,52.517037;13.385983,52.496891?steps=true
    $server = 'nominatimosrm.carseg.com.br/osrm'; // or IP address
    $url = 'https://'.$server.'/route/v1/driving/'.urlencode($lon_city).','.urlencode($lat_city).';'.urlencode($lon_local).','.urlencode($lat_local).'?generate_hints=false&overview=false';
    $contents = file_get_contents($url);
    $dec_contents = json_decode($contents);

    foreach ($dec_contents->routes as $data2) {
        $dist = round(($data2->legs[0]->distance / 1000), 3);
    }

    return $dist;
}

function process_google($response)
{
    $conta = 0;
    foreach ($response->results as $result) {
        foreach ($result->address_components as $address) {
            if ($address->types[0] == 'administrative_area_level_1') {
                $state = str_replace('State of ', '', $address->long_name);
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
                $address2 = $result->formatted_address;
            }
            if ($result->types) {
                $type = $result->types;
            }
        }
        if ($result->types[0] == 'administrative_area_level_2') {
            $lat_city = $result->geometry->location->lat;
            $lng_city = $result->geometry->location->lng;
        }
        $conta++;
    }

    /* $origem = $lat.",".$lng;//"-12.209793333333,-38.969271111111";
    $destination = $lat_city.",".$lng_city;
    $api = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metrics&origins=".$origem."&destinations=".$destination."&key=AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38");
    $data = json_decode($api);
    $distance = $data->rows[0]->elements[0]->distance->text; */
    //$county .= " - ".$distance;

    $Location = [
        'place_id' => $place_id,
        'lat' => $lat,
        'lng' => $lng,
        'address' => $address2,
        'country' => $country,
        'country_code' => $country_short,
        'state' => $state,
        'county' => $county,
        'city' => $city,
        'road' => $road,
        'house' => $house,
        'zip' => $zip,
        'type' => $type,
        'distance' => $distance,
    ];

    return $Location;
}
