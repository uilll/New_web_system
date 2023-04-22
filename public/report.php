<?php namespace Report;

use Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
 
/* Recebe os dados do cliente ajax via POST */
$lat = strip_tags((isset($_POST['lat'])) ? $_POST['lat'] : '');
$lon = strip_tags((isset($_POST['lon'])) ? $_POST['lon'] : '');
$msg = $lat.",".$lon."\r\n";

$fp = fopen('/var/www/html/releases/20190129073809/public/report001.txt', "a+");
fwrite($fp, $msg);
fclose($fp);
echo 'Erro reportado com sucesso, obrigado pela informação.';


?>