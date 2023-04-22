<?php

$input = array_merge($_GET, $_POST);

if (!array_key_exists('attributes', $input))
    die('Wrong data');

if (!array_key_exists('uniqueId', $input))
    die('Wrong data');

if (!array_key_exists('fixTime', $input))
    die('Wrong data');

if (!is_array($input['attributes'])) {
    $input['attributes'] = json_decode($input['attributes'], true);
    if ( ! $input['attributes'] || ! is_array($input['attributes']))
        die('Wrong data');
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $input['ip'] = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $input['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $input['ip'] = $_SERVER['REMOTE_ADDR'];
}

$redis_status = TRUE;
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
}
catch (Exception $e) {
    $redis_status = FALSE;
}

if ( ! $redis_status) {
    header("Connection: Close");
    die();
}

//$key = 'position.' . (int)$input['fixTime'] . ':1.' . $input['uniqueId'];
//$redis->set($key, json_encode($input));

$key = 'positions.' . $input['uniqueId'];
$redis->lPush($key, json_encode($input));

echo json_encode(['status' => 1]);
die();