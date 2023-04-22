<?php

namespace Tobuli\Helpers;

use Curl;
use Facades\Repositories\UserRepo;

class RemoteUser
{
    public function getByHash($hash)
    {
        $response = $this->remote(config('tobuli.frontend_curl').'/get_user', [
            'hash' => $hash,
            'password' => config('tobuli.frontend_curl_password')
        ]);

        if (empty($response['status']))
            return null;

        return $this->createOrUpdate($response);
    }

    public function getByApiHash($api_hash)
    {
        $response = $this->remote(config('tobuli.frontend_curl').'/get_user', [
            'user_api_hash' => $api_hash,
            'password' => config('tobuli.frontend_curl_password')
        ]);

        if (empty($response['status']))
            return null;

        $response['user_api_hash'] = $api_hash;

        return $this->createOrUpdate($response);
    }

    public function getByCredencials($email, $password)
    {
        $response = $this->remote(config('tobuli.frontend_curl').'/login', [
            'email' => $email,
            'password' => $password
        ]);

        if (empty($response['status']))
            return null;

        return $this->getByApiHash($response['user_api_hash']);
    }

    protected function createOrUpdate($data)
    {
        $user_id = $data['id'];

        $user_data = [
            'email'                   => $data['email'],
            'devices_limit'           => $data['devices_limit'] == 'free' ? 1 : $data['devices_limit'],
            'group_id'                => $data['group_id'],
            'subscription_expiration' => $data['subscription_expiration'],
            'billing_plan_id'         => $data['billing_plan_id'],
            'open_geofence_groups'    => '["0"]',
            'open_device_groups'      => '["0"]'
        ];

        if ( ! empty($data['user_api_hash'])) {
            $user_data = $user_data + [
                'api_hash'            => $data['user_api_hash'],
                'api_hash_expire'     => date('Y-m-d H:i:s', time() + 600)
            ];
        }

        $user = UserRepo::find($user_id);

        if (empty($user)) {
            UserRepo::create($user_data + ['id' => $user_id]);
        } else {
            if ( ! empty($user['open_geofence_groups'])) {
                unset($user_data['open_geofence_groups']);
            }
            if ( ! empty($user['open_device_groups'])) {
                unset($user_data['open_geofence_groups']);
            }

            UserRepo::update($user_id, $user_data);
        }

        $user = UserRepo::find($user_id);

        return $user;
    }

    protected function remote($url, $data)
    {
        $curl = new Curl;
        $curl->follow_redirects = false;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = FALSE;
        $curl->options['CURLOPT_FRESH_CONNECT'] = 1;
        if ($_ENV['server'] == 'us')
            $curl->options['CURLOPT_PROXY'] = '185.69.52.20:3128';

        $response = $curl->post($url, $data);

        return json_decode($response,TRUE);
    }
}