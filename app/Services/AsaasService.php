<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use App\instituicao_pagamento;
use Illuminate\Support\Facades\Auth;

class AsaasService
{
    protected $client;
    

    public function __construct($userId=null)
    {
        if(is_null($userId))
            // Obtém o ID do usuário logado
            $userId = Auth::id();

       // Obtém as informações da tabela instituicao_pagamento com base no usuário logado
       $instituicaoPagamento = instituicao_pagamento::where('usuarios_permitidos', 'like', '%"'.$userId.'"%')->first();

       // Verifica se a instituição de pagamento foi encontrada
       if ($instituicaoPagamento) {
           $this->baseUri = $instituicaoPagamento->site_acesso;
           $this->accessToken = $instituicaoPagamento->chave_acesso;
        } else {
            // Retorna uma mensagem de erro se a instituição de pagamento não for encontrada para o usuário logado
            throw new \Exception('Usuário não permitido para acessar a instituição de pagamento.');
        }

       $this->client = new Client([
           'base_uri' => $this->baseUri,
           'headers' => [
               'Content-Type' => 'application/json',
               'access_token' => $this->accessToken,
           ],
       ]);
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
