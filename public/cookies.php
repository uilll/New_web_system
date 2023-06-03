<?php

require '/var/www/html/releases/20190129073809/vendor/autoload.php';
use GuzzleHttp\Client;

$cookie_name = 'uilmo';
echo $cookie_name;

$client = new \GuzzleHttp\Client();
$response = $client->request('GET', 'http://sistema.carseg.com.br/cookie/get/'.$cookie_name);
$response = $response->getBody()->getContents();
echo $response;

//$response = file_get_contents('http://sistema.carseg.com.br/cookie/get/'.$cookie_name);

/* $request = new \GuzzleHttp\Psr7\Request('GET', 'http://sistema.carseg.com.br/cookie/get/'.$cookie_name);

$promise = $client->sendAsync($request)->then(function ($response) {
    echo 'I completed! ' . $response->getBody();
});

 */

// Create a client with a base URI
/* $client = new GuzzleHttp\Client(['base_uri' => 'http://sistema.carseg.com.br/cookie/get/']);
var_dump($client);
// Send a request to https://foo.com/api/test
$response = $client->request('GET', 'uilmo');

echo $response[0];
var_dump($response);
var_dump($client); */

/* $client = new \GuzzleHttp\Client();

$response = $client->request('POST', 'http://sistema.carseg.com.br/cookie/get/'.$cookie_name);
    $response = $response->getBody()->getContents(); */
//var_dump ($client);

//$name="uilmo";

// $request = Route::('set_cookie'), 'POST', $name,$value);

// var_dump $request;

// $response = app()->handle($request);

// $responseBody = $response->getContent();

// echo $responseBody;
