<?php

namespace Tobuli\Helpers\SMS\Services\HTTP;

use Curl;
use Tobuli\Exceptions\ValidationException;

class SendSmsPOST extends SendSmsHTTP
{
    public function sendThroughConsole($base_url, $query_url)
    {
        $command = 'curl -i ';

        if ($this->authentication) {
            $command .= '--user '.$this->username.':'.$this->password.' ';
        }

        if ($this->encoding === 'json') {
            $command .= '-H "Content-Type: application/json" ';
        }

        $command = $this->headersInToCommandLine($command);

        $command .= '-d \''.$query_url.'\' '.$base_url.' > /dev/null 2>&1 &';

        @exec($command);
    }

    public function sendThroughCurlPHP($base_url, $query_url)
    {
        try {
            $curl = new Curl();

            $curl->options = [
                'CURLOPT_TIMEOUT' => 5,
                'CURLOPT_POSTFIELDS' => $query_url,
            ];

            $curl->headers = [
                'Content-Type: application/json',
                'Content-Length: '.strlen($query_url),
            ];

            if (! empty($this->customHeaders)) {
                $curl->headers = array_merge($curl->headers, $this->userHeadersToArray());
            }

            if ($this->authentication) {
                $curl->setAuth($this->username, $this->username);
            }

            $response = $curl->request('POST', $base_url);

            return $response;
        } catch (\CurlException $e) {
            throw new ValidationException(['curl_request' => trans('validation.attributes.bad_sms_gateway_url')]);
        }
    }
}
