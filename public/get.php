<?php
require '/var/www/html/releases/20190129073809/vendor/autoload.php';
$lat = strip_tags((isset($_POST['lat'])) ? $_POST['lat'] : '');
$lng = strip_tags((isset($_POST['lng'])) ? $_POST['lng'] : '');

/* 	$hora_ = date("H");
    if((int)$hora_ % 2 == 0){
        $google_key = 'AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38';
    } else {
        $google_key = 'ímpar';
    }
    print_r ($google_key); */
//phpinfo()

// Armazena o tempo atual
$start_time = microtime(true);

$client = new \GuzzleHttp\Client();
$response = $client->request('POST', 'http://172.31.43.100/get_add.php', [
    'form_params' => [
        'lat' => '-12.6171420',
        'lng' => '-38.3050830',
        'map' => 'nominatim',
    ],
]);
$response = $response->getBody()->getContents();
print_r($response);

// Armazena o tempo atual novamente
$end_time = microtime(true);

// Calcula o tempo de execução em segundos
$execution_time = ($end_time - $start_time);

// Exibe o tempo de execução
echo "\n\n\nTempo de execução: ".$execution_time.' segundos';

/*-8.2115,-38.7247
-11.542417,-39.30573
-11.561969, -39.294936
    -6.087109°, -49.746657°
curl "http://54.232.122.234/route/v1/driving/-9.4684489,-40.4816756;-6.087109,-49.746657?steps=true&alternatives=true"
*/

//####################################################
//					Pegar dados pelo endereço		//
//####################################################
/* 	$client = new \GuzzleHttp\Client();
    $response = $client->request('POST', 'https://sistema.carseg.com.br/get_add2.php', [
        'form_params' => [
            'queries' => 'Rua rural'
           ]
       ]);

    //print_r($response[0]);

    $response = json_decode($response->getBody()->getContents()); */
/* echo '<pre>';
echo var_dump ($response);
echo '</pre>';
foreach ($response as $local_) {
    //print_r($local_);//; json_decode($response)
    var_dump (json_decode($local_)->city);
}
print_r($response);
//echo ($response);*/

?>	