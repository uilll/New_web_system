<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class AsaasService
{
    protected $client;
    

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('asaas.api_url'),
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => config('asaas.api_key'),
            ],
        ]);

        //$this->path_asaas = config('asaas.api_url'); 
    }

    public function get($path, $params = [])
    {
        $response = $this->client->get($path, [
            'query' => $params,
        ]);
        
        return json_decode($response->getBody(), true);
    }

    public function post($path, $data = [])
    {
        $response = $this->client->post($path, [
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function put($path, $data = [])
    {
        $response = $this->client->put($path, [
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function delete($path)
    {
        $response = $this->client->delete($path);

        return json_decode($response->getBody(), true);
    }

    public function getBillingData($path, $params = []) 
    {
        $response = $this->client->get($path, [
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

}
