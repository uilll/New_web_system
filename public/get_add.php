<?php 
require '/var/www/html/releases/20190129073809/vendor/autoload.php';
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
//use League\Flysystem\Filesystem;

$lat = filter_input(INPUT_POST, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$lng = filter_input(INPUT_POST, 'lng', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$map = filter_input(INPUT_POST, 'map', FILTER_SANITIZE_STRING);
$queries = filter_input(INPUT_POST, 'queries', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);

if (intval(date("H")) % 2 == 0) {
    $google_key = 'AIzaSyBAsveXBuLWfeEgNH_HfjchUfD699YzZ0A';
} else {
    $google_key = 'AIzaSyDeFzM5Mt0iwEOogWUPwg78twLD4Lm70qs';
}

if (!empty($lat) && !empty($lng)) {
    if ($map == 'google') {
        $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . $google_key);
        $location = json_decode($json);
        echo json_encode(process_google($location));
    } else {
        $server = 'nominatimosrm.carseg.com.br';
        $url = 'https://' . $server . '/nominatim/reverse?format=json&lat=' . urlencode($lat) . '&lon=' . urlencode($lng);
        $contents = file_get_contents($url);
        $location = get_object_vars(json_decode($contents));
        $address = get_object_vars($location['address']);
        $address_ = array_get($address, 'road');
        if (!is_null(array_get($address, 'house_number'))) $address_ .= ", " . array_get($address, 'house_number');
        $city = array_get($address, 'city', array_get($address, 'town', array_get($address, 'village')));
        //dd($city);
        if (is_null($city)) {
            $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . $google_key);
            $location = process_google(json_decode($json));
            $city = array_get($location, 'county');
        }
        $state = array_get($address, 'state');
        $city_decoded = html_entity_decode($city, ENT_QUOTES, 'UTF-8');
        //dd($city,$city_decoded);

        $distance = number_format(get_distance($lat, $lng, $city_decoded, $state), 2, ',', '.');
        if ($city == "SP") $city = 'São Paulo';
        if ($city == "SSA") $city = 'Salvador';
        if ($city == "LEM") $city = 'Luis Eduardo Magalhães';
        if ($city == "VCA") $city = 'Vitória da conquista';
        $city .= ' - ' . $distance . ' km';
        $fulladdress = $address_ . ', ' . $city . '-' . array_get($address, 'state');


		
		$Location = array(
					'place_id'      => array_get($location, 'place_id'),
					'lat'           => array_get($location, 'lat'),
					'lng'           => array_get($location, 'lon'),
					'address'     	=> $address_,
					'type'          => array_get($location, 'osm_type'),
					'country'       => array_get($address, 'country'),
					'country_code'  => array_get($address, 'country_code'),
					'county'        => $city,
					'state'         => $state,
					'city'          => $city,
					'road'          => array_get($address, 'road'),
					'house'         => array_get($address, 'house_number'),
					'zip'           => array_get($address, 'postcode'),
					'dist_'			=> $distance,
				);		
		echo json_encode($Location);
	}
	
}

if (!empty($queries)) {
    if (!is_string($queries)) {
        throw new InvalidArgumentException('Entradas inválidas.');
    }
    $queries = filter_var($queries, FILTER_SANITIZE_STRING);

    $client = new \GuzzleHttp\Client();
    $server = 'nominatimosrm.carseg.com.br';
    $url = 'https://' . $server . '/nominatim/search?format=json&countrycodes=br&q=' . urlencode($queries);

    $contents = file_get_contents($url);
    $dec_contents = json_decode($contents);

    if (empty($dec_contents)) {
        $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $queries . '&key=' . $google_key);
        $response = process_google(json_decode($json));
    } else {
        $response = array();
        foreach ($dec_contents as $data1) {
            $response[] = $client->request('POST', 'http://172.31.43.100/get_add.php', [
                'form_params' => [
                    'lat' => $data1->lat,
                    'lng' => $data1->lon,
                    'map' => 'nominatim'
                ]
            ])->getBody()->getContents();
        }
    }

    echo json_encode($response);
}

function get_distance($lat_local, $lon_local, $city, $state)
{
    //echo($lat_local, $lon_local);
    if (!is_numeric($lat_local) || !is_numeric($lon_local) || !is_string($city) || !is_string($state)) {
        throw new InvalidArgumentException('Entradas inválidas.');
    }
    //$city = filter_var($city, FILTER_CALLBACK, array('options' => 'custom_filter')); //Não usar mais o filter_var, está dando erro em caracteres especiais
    //$state = filter_var($state, FILTER_CALLBACK, array('options' => 'custom_filter')); //Não usar mais o filter_var, está dando erro em caracteres especiais

    $city = custom_filter($city);
    $state = custom_filter($state);


    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP'
            ],
            'timeout' => 5
        ]
    ]);

    //dd()
    $url = "https://nominatimosrm.carseg.com.br/nominatim/search?format=json&city=" . urlencode($city) . "&state=" . urlencode($state);
    //dd($url);
    $contents = @file_get_contents($url, false, $context);
    //dd($contents);
    if ($contents === false) {
        throw new Exception('Erro ao obter dados do servidor.');
    }

    $dec_contents = json_decode($contents);

    $lat_city = null;
    $lon_city = null;
    //dd($lat_city,$lon_city);
    foreach ($dec_contents as $data1) {
        $importance = $data1->importance;
        if (empty($lat_city) && empty($lon_city)) {
            $lat_city = $data1->lat;
            $lon_city = $data1->lon;
        } elseif ($data1->importance > $importance) {
            $lat_city = $data1->lat;
            $lon_city = $data1->lon;
        }
    }
    //dd($lat_city,$lon_city);

    if (empty($lat_city) || empty($lon_city)) {
        //throw new Exception('Não foi possível obter as coordenadas da cidade.');
        //debugar(true,'Não foi possível obter as coordenadas da cidade.');
        $dist = 0;
    }
    else{

        $contents = @file_get_contents("https://nominatimosrm.carseg.com.br/osrm/route/v1/driving/" . urlencode($lon_city) . "," . urlencode($lat_city) . ";" . urlencode($lon_local) . "," . urlencode($lat_local) . "?generate_hints=false&overview=false", false, $context);

        if ($contents === false) {
            throw new Exception('Erro ao obter dados do servidor.');
        }

        $dec_contents = json_decode($contents);

        $dist = null;

        foreach ($dec_contents->routes as $data2) {
            $dist = round(($data2->legs[0]->distance / 1000), 3);
        }

        if (empty($dist)) {
            throw new Exception('Não foi possível obter a distância.');
        }
    }

    return $dist;
}

function process_google($response)
{
    $location = new stdClass();

    $location->place_id = null;
    $location->lat = null;
    $location->lng = null;
    $location->address = null;
    $location->country = null;
    $location->country_code = null;
    $location->state = null;
    $location->county = null;
    $location->city = null;
    $location->road = null;
    $location->house = null;
    $location->zip = null;
    $location->type = null;

    try {
        foreach ($response->results as $result) {
            foreach ($result->address_components as $address) {
                switch ($address->types[0]) {
                    case 'administrative_area_level_1':
                        $location->state = str_replace('State of ', '', $address->long_name);
                        break;
                    case 'administrative_area_level_2':
                        $location->county = $address->long_name;
                        $location->lat_city = $result->geometry->location->lat;
                        $location->lng_city = $result->geometry->location->lng;
                        break;
                    case 'locality':
                        $location->city = $address->long_name;
                        break;
                    case 'country':
                        $location->country = $address->long_name;
                        $location->country_code = $address->short_name;
                        break;
                    case 'route':
                        $location->road = $address->long_name;
                        break;
                    case 'street_number':
                        $location->house = $address->long_name;
                        break;
                    case 'postal_code':
                        $location->zip = $address->long_name;
                        break;
                }
            }

            if (isset($result->place_id)) {
                $location->place_id = $result->place_id;
            }
            if (isset($result->geometry->location->lat)) {
                $location->lat = $result->geometry->location->lat;
            }
            if (isset($result->geometry->location->lng)) {
                $location->lng = $result->geometry->location->lng;
            }
            if (isset($result->formatted_address)) {
                $location->address = $result->formatted_address;
            }
            if (isset($result->types)) {
                $location->type = $result->types;
            }
        }
    } catch (Exception $e) {
        // Adicione um log de erro ou mensagem personalizada aqui
        error_log("Erro ao processar a resposta do Google Maps API: " . $e->getMessage());
    }

    return $location;
}


function custom_filter($input) {
    // Adicione ou remova caracteres que você deseja permitir ou negar na regex abaixo
    return preg_replace('/[^a-zA-Z0-9\s\'\p{L}]/u', '', $input);
}


?>