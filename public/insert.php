<?php

$input = array_merge($_GET, $_POST);

if (! array_key_exists('attributes', $input)) {
    exit('Wrong data');
}

if (! array_key_exists('uniqueId', $input)) {
    exit('Wrong data');
}

if (! array_key_exists('fixTime', $input)) {
    exit('Wrong data');
}

if (! is_array($input['attributes'])) {
    $input['attributes'] = json_decode($input['attributes'], true);
    if (! $input['attributes'] || ! is_array($input['attributes'])) {
        exit('Wrong data');
    }
}

if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
    $input['ip'] = $_SERVER['HTTP_CLIENT_IP'];
} elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $input['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $input['ip'] = $_SERVER['REMOTE_ADDR'];
}

$redis_status = true;
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    $redis_status = false;
}

if (! $redis_status) {
    header('Connection: Close');
    exit();
}

//$key = 'position.' . (int)$input['fixTime'] . ':1.' . $input['uniqueId'];
//$redis->set($key, json_encode($input));

$key = 'positions.'.$input['uniqueId'];
$redis->lPush($key, json_encode($input));

echo json_encode(['status' => 1]);
exit();
