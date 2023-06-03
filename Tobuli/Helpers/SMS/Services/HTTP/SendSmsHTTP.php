<?php

namespace Tobuli\Helpers\SMS\Services\HTTP;

use Tobuli\Helpers\SMS\Services\SendSmsManager;

abstract class SendSmsHTTP extends SendSmsManager
{
    protected $authentication;

    protected $username;

    protected $password;

    protected $gatewayUrl;

    protected $customHeaders;

    protected $encoding;

    abstract protected function sendThroughConsole($base_url, $query_url);

    abstract protected function sendThroughCurlPHP($base_url, $query_url);

    public function __construct($gateway_args)
    {
        $this->authentication = array_get($gateway_args, 'authentication');
        $this->username = array_get($gateway_args, 'username');
        $this->password = array_get($gateway_args, 'password');
        $this->customHeaders = array_get($gateway_args, 'custom_headers');
        $this->gatewayUrl = array_get($gateway_args, 'sms_gateway_url');
        $this->encoding = array_get($gateway_args, 'encoding');
    }

    protected function sendSingle($receiver_phone, $message_body)
    {
        $complete_url = $this->insertUrlVariables($receiver_phone, $message_body);
        $url_parts = parse_url($complete_url);

        $base_url = $this->buildGatewayBaseUrl($url_parts);
        $query_url = $this->buildGatewayQueryUrl($url_parts);

        if (app()->runningInConsole()) {
            return $this->sendThroughConsole($base_url, $query_url);
        }

        return $this->sendThroughCurlPHP($base_url, $query_url);
    }

    protected function insertUrlVariables($receiver_phone, $message_body)
    {
        return strtr($this->gatewayUrl, [
            '%NUMBER%' => rawurlencode($receiver_phone),
            '%MESSAGE%' => urlencode($message_body),
        ]);
    }

    protected function userHeadersToArray()
    {
        $array = [];
        $headers = array_map('trim', array_filter(explode(';', $this->customHeaders)));

        foreach ($headers as $header) {
            [$title, $value] = array_map('trim', explode(':', $header));

            $array[$title] = $value;
        }

        return $array;
    }

    protected function headersInToCommandLine($command)
    {
        if (! empty($this->customHeaders)) {
            $user_headers = $this->userHeadersToArray();

            foreach ($user_headers as $header_title => $header_value) {
                $command .= '-H " '.$header_title.':'.$header_value.'"';
            }
        }

        return $command;
    }

    protected function buildGatewayBaseUrl($url_parts)
    {
        $url_parts['scheme'] = isset($url_parts['scheme']) ? $url_parts['scheme'] : 'http';
        $url_parts['host'] = isset($url_parts['host']) ? $url_parts['host'] : '';
        $url_parts['path'] = isset($url_parts['path']) ? $url_parts['path'] : '';

        return $url_parts['scheme'].'://'.$url_parts['host'].(isset($url_parts['port']) ? ':'.$url_parts['port'] : '').$url_parts['path'];
    }

    protected function buildGatewayQueryUrl($url_parts)
    {
        if (empty($url_parts['query'])) {
            return '';
        }

        parse_str($url_parts['query'], $url_query_parts);

        if (! count($url_query_parts)) {
            return '';
        }

        $array_for_implode = [];
        foreach ($url_query_parts as $key => $value) {
            $array_for_implode[] = $key.'='.rawurlencode($value);
        }

        $new_query_string = implode('&', $array_for_implode);

        return $new_query_string;
    }
}
