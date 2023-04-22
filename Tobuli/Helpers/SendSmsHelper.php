<?php

use Plivo\RestAPI;
use Facades\Repositories\SmsEventQueueRepo;
use Tobuli\Exceptions\ValidationException;

function send_sms_template($sms_template, $sms_gateway, $user_id, $result = FALSE, $replace = NULL) {
    $results = [];

    $body = $sms_template->note;
    if (!empty($replace))
        $body = strtr($body, $replace);

    $body = strtr($body, [
        '<br>' => "\n",
        '\r\n' => "\n",
        '&deg;' => '',
    ]);

    /*
    if ($_ENV['server'] == 'conceptnova')
        $body = preg_replace('/[^A-Za-z0-9\.\,\?\!\\\ -]/', '', $body);
    */

    $body_encoded = urlencode($body);

    if (!is_null($sms_gateway) && $sms_gateway['status']) {
        $mobile_phones = explode(';', $sms_gateway['mobile_phone']);
        if (!empty($mobile_phones)) {
            if (!isset($sms_gateway['params']['request_method']))
                $sms_gateway['params']['request_method'] = 'get';

            foreach ($mobile_phones as $key => $mobile_phone) {
                if (empty($mobile_phone))
                    continue;

                if ($sms_gateway['params']['request_method'] == 'app') {
                    SmsEventQueueRepo::create([
                        'user_id' => $user_id,
                        'phone' => $mobile_phone,
                        'message' => $body
                    ]);
                }
                else {
                    $url = strtr($sms_gateway['url'], [
                        '%NUMBER%' => rawurlencode($mobile_phone),
                        '%MESSAGE%' => $body_encoded
                    ]);

                    $sms_gateway['params']['message'] = [
                        'body' => $body,
                        'phone' => $mobile_phone
                    ];

                    $results[] = send_sms($url, $sms_gateway['params'], $result);
                }
            }


        }
    }

    return $results;
}

function send_sms($url, $params, $result = FALSE) {
    if (isset($_ENV['server']) && function_exists('send_sms_async_'.$_ENV['server'])) {
        return call_user_func('send_sms_async_'.$_ENV['server'], $url, $params, $result);
    }
    else {
        if ($params['request_method'] == 'plivo') {
            return send_sms_plivo($params, $result);
        }
        else {
            return send_sms_async($url, $params, $result);
        }
    }
}

function send_sms_plivo($params, $result = FALSE)
{
    $p = new RestAPI($params['auth_id'], $params['auth_token']);

    // Set message parameters
    $parameters = array(
        'src' => $params['senders_phone'], // Sender's phone number with country code
        'dst' => $params['message']['phone'], // Receiver's phone number with country code
        'text' => $params['message']['body'], // Your SMS text message
    );
    // Send message
    $response = $p->send_message($parameters);
    if ($result && isset($response['response']['error']))
        throw new ValidationException(['request_method' => $response['response']['error']]);
}

function send_sms_async($url, $params, $result = FALSE)
{
    $response = TRUE;
    $parts = parse_url($url);
    $parts['scheme'] = isset($parts['scheme']) ? $parts['scheme'] : 'http';
    $parts['host'] = isset($parts['host']) ? $parts['host'] : '';
    $parts['path'] = isset($parts['path']) ? $parts['path'] : '';
    $parts['query'] = (isset($parts['query']) ? $parts['query'] : '');
    if (substr($parts['query'], -2) == '__')
        $parts['query'] = substr($parts['query'], 0, -2);

    $post_string = $parts['query'];
    $url = $parts['scheme'] . '://' . $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$parts['path'];

    //$post_string  = str_replace('[%plus%]', '+', $post_string);
    parse_str($post_string, $arr);
    $new_post_string = '';
    if (count($arr)) {
        foreach ($arr as $key => $value) {
            $new_post_string .= $key . '=' . rawurlencode($value) . '&';
        }
        $new_post_string = substr($new_post_string, 0, -1);
        //$new_post_string = str_replace('%20', '%2B', $new_post_string);
    }

    //$new_post_string  = str_replace('[%plus%]', '+', $new_post_string );
    if (isset($params['encoding']) && $params['encoding'] == 'json') {
        parse_str($new_post_string, $arr);
        $new_post_string = json_encode($arr);
    }

    if ($result) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url.($params['request_method'] == 'get' ? '?'.$new_post_string : ''));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5 );
        $postLength = 0;
        if ($params['request_method'] == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $new_post_string);
            $postLength = strlen($new_post_string);
        }
        else
            curl_setopt($ch, CURLOPT_HTTPGET, 1);

        if (isset($params['authentication']) && $params['authentication'])
            curl_setopt($ch, CURLOPT_USERPWD, $params['username'].':'.$params['password']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (isset($params['encoding']) && $params['encoding'] == 'json')
            curl_setopt($ch, CURLOPT_HTTPHEADER ,array('Content-Type: application/json', 'Content-Length: ' . strlen($new_post_string)));
        else
            curl_setopt($ch, CURLOPT_HTTPHEADER ,array('Content-Length: ' . $postLength));

        $response = [];
        $response['data']  = curl_exec($ch);
        $response['error'] = curl_error($ch);
        curl_close($ch);
    }
    else {
        $command = 'curl -i ';

        if (isset($params['authentication']) && $params['authentication'])
            $command .= '--user '.$params['username'].':'.$params['password'].' ';

        if (isset($params['encoding']) && $params['encoding'] == 'json')
            $command .= '-H "Content-Type: application/json" ';

        if ($params['request_method'] == 'post')
            $command .= '-d \''.$new_post_string.'\' '.$url.' > /dev/null 2>&1 &';
        else
            $command .= '"'.$url.'?'.$new_post_string.'" > /dev/null 2>&1 &';

        @exec($command);
    }

    return $response;
}