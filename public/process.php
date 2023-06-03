<?php

function parseEnv($file)
{
    $arr = [];
    $autodetect = ini_get('auto_detect_line_endings');
    ini_set('auto_detect_line_endings', '1');
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    ini_set('auto_detect_line_endings', $autodetect);
    foreach ($lines as $key => $line) {
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $arr[$name] = $value;
    }

    return $arr;
}

$env = parseEnv('../.env');

$db = new PDO('mysql:host=localhost;dbname='.$env['traccar_database'].';charset=utf8', $env['traccar_username'], $env['traccar_password']);

$q = $db->prepare('SELECT COUNT(*) AS total FROM `devices`');
$q->execute();
$total = $q->fetch();

$q = $db->prepare('SELECT id FROM `devices` ORDER BY id desc LIMIT 1');
$q->execute();
$max = $q->fetch();

$q = $db->prepare('SHOW FULL PROCESSLIST');
$q->execute();
$list = $q->fetchAll();

echo '<pre>';
echo 'Total: '.$total['total'].'<br>';
echo 'Last ID: '.$max['id'].'<br>';

foreach ($list as $row) {
    if ($row['db'] != 'gpswox_traccar') {
        continue;
    }

    echo 'Process: '.$row['State'].' '.$row['Time'].' '.$row['Info'].'<br>';
}

$db = null;
