<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Symfony\Component\Process\Process;
use Tobuli\Entities\EmailTemplate;
use Tobuli\Entities\FcmToken;
use Tobuli\Entities\SmsTemplate;
use Tobuli\Entities\User;
use Tobuli\Exceptions\ValidationException;

function isDemoUser($user = null)
{
    if (is_null($user)) {
        $user = Auth::User();
    }

    return $user->isDemo();
}

function isPublic()
{
    return config('tobuli.type') == 'public';
}

function isAllFF()
{
    return isPublic() || env('APP_ENV') === 'local' || env('APP_ALLFF');
}

function dontExist($name)
{
    return sprintf(trans('global.dont_exist'), trans($name));
}

function datetime($date, $timezone = true, $zone = null)
{
    static $format = null;

    if (is_null($format)) {
        $format = settings('main_settings.default_date_format').' '.settings('main_settings.default_time_format');
    }

    if (empty($date) || substr($date, 0, 4) == '0000') {
        return trans('front.invalid_date');
    }

    if ($timezone) {
        if (is_null($zone) && Auth::check()) {
            $zone = Auth::User()->timezone->zone;
        }

        if (is_null($zone)) {
            $zone = '+0hours';
        }

        return date($format, strtotime("$date $zone"));
    }

    return date($format, strtotime($date));
}

function tdate($date, $zone = null, $reverse = false, $format = 'Y-m-d H:i:s')
{
    if (is_null($zone)) {
        $zone = Auth::User()->timezone->zone;
    }

    if ($reverse) {
        $zone = timezoneReverse($zone);
    }

    return date($format, strtotime($zone, strtotime($date)));
}

function roundToQuarterHour($timestring)
{
    try {
        $time = \Carbon\Carbon::createMidnightDate()->parse($timestring);
    } catch (Exception $e) {
        $time = \Carbon\Carbon::createMidnightDate();
    }

    $minutes = date('i', strtotime($timestring));

    if ($sub = $minutes % 15) {
        $time->subMinutes($sub);
    }

    return $time->format('H:i');
}

function beginTransaction()
{
    DB::beginTransaction();
    DB::connection('traccar_mysql')->beginTransaction();
}

function rollbackTransaction()
{
    DB::connection('traccar_mysql')->rollback();
    DB::rollback();
}

function commitTransaction()
{
    DB::commit();
    DB::connection('traccar_mysql')->commit();
}

function modalError($message)
{
    return View::make('admin::Layouts.partials.error_modal')->with('error', trans($message));
}

function modal($message, $type = 'warning')
{
    return View::make('front::Layouts.partials.modal_warning', [
        'type' => $type,
        'message' => $message,
    ]);
}

function isAdmin()
{
    return Auth::User() && (Auth::User()->isAdmin() || Auth::User()->isManager());
}

function idExists($id, $arr)
{
    foreach ($arr as $key => $value) {
        if ($value['id'] == $id) {
            return true;
        }
    }

    return false;
}

function nauticalToKilometers($nm)
{
    return $nm * 1.852;
}

function kilometersToMiles($km)
{
    return round($km / 1.609344);
}

function milesToKilometers($ml)
{
    return round($ml * 1.609344);
}

function gallonsToLiters($gallons)
{
    if ($gallons <= 0) {
        return 0;
    }

    return $gallons * 3.78541178;
}

function litersToGallons($liters)
{
    if ($liters <= 0) {
        return 0;
    }

    return $liters / 3.78541178;
}

function metersToFeets($meters)
{
    if ($meters <= 0) {
        return 0;
    }

    return number_format($meters * 3.2808399, 2, '.', false);
}

function float($number)
{
    return number_format($number, 2, '.', false);
}

function cord($number)
{
    return number_format($number, 7, '.', false);
}

function convertFuelConsumption($type, $fuel_quantity)
{
    if ($fuel_quantity <= 0) {
        return 0;
    }
    if ($type == 1) {
        return 1 / $fuel_quantity;
    } elseif ($type == 2) {
        return gallonsToLiters(1) / milesToKilometers($fuel_quantity);
    } else {
        return 0;
    }
}

function sendTemplateEmail($to, EmailTemplate $template, $data, $attaches = [], $view = 'front::Emails.template')
{
    if (empty($to)) {
        return false;
    }

    $email = $template->buildTemplate($data);

    $to = explode(';', $to);

    return \Facades\MailHelper::send($to, $email['body'], $email['subject'], App::getLocale(), true, $attaches, $view);
}

/**
 * @throws ValidationException
 */
function sendTemplateSMS($to, SmsTemplate $template, $data, $user_id = null)
{
    if (empty($to) || empty($user_id)) {
        return;
    }

    $sms = $template->buildTemplate($data);

    sendSMS($to, $sms['body'], $user_id);

    return ['status' => 1];
}

/**
 * @throws ValidationException
 */
function sendSMS($to, $body, $user_id = null)
{
    if (is_null($user_id)) {
        $user_id = Auth::user()->id;
    }

    $sms_manager = new SMSGatewayManager();

    $sms_sender_service = $sms_manager->loadSender($user_id);

    $sms_sender_service->send($to, $body);

    return ['status' => 1];
}

function sendNotificationToTokens($tokens, $title, $body, $payloadData = null)
{
    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60 * 20);
    $option = $optionBuilder->build();

    $notification = null;

    $notificationBuilder = new PayloadNotificationBuilder($title);
    $notificationBuilder->setBody($body)->setSound('default');
    $notification = $notificationBuilder->build();

    $dataBuilder = new PayloadDataBuilder();
    if (! is_null($payloadData)) {
        $dataBuilder->addData($payloadData);
    }
    $data = $dataBuilder->build();

    $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

    if ($downstreamResponse->tokensToDelete()) {
        FcmToken::whereIn('token', $downstreamResponse->tokensToDelete())->delete();
    }

    if ($retryTokens = $downstreamResponse->tokensToRetry()) {
        sendNotificationToTokens($retryTokens, $title, $body, $payloadData);
    }

    if ($downstreamResponse->tokensToModify()) {
        foreach ($downstreamResponse->tokensToModify() as $old_token => $new_token) {
            FcmToken::where('token', $old_token)->update(['token' => $new_token]);
        }
    }
}

function sendNotification($user_id, Tobuli\Entities\EventQueue $eventQueue)
{
    if (empty($user_id)) {
        return;
    }

    $user = User::find($user_id);

    if (! $user) {
        return;
    }

    $tokens = $user->fcm_tokens->pluck('token')->toArray();

    if (! $tokens) {
        return;
    }

    $title = $eventQueue->data['device_name'].' '.$eventQueue->event_message;
    $body = trans('front.speed').': '.$eventQueue->data['speed'];

    switch ($eventQueue->type) {
        case 'zone_out':
        case 'zone_in':
            $body .= "\n".trans('front.geofence').': '.$eventQueue->data['geofence'];
            break;
        case 'overspeed':
            break;
        case 'driver':
            break;
        case 'custom':
            break;
    }

    $payload = array_merge($eventQueue->data, ['title' => $title, 'content' => $body]);

    sendNotificationToTokens($tokens, $title, $body, $payload);
}

function sendWebhook($url, $data)
{
    $client = new \GuzzleHttp\Client();

    $client->post($url, [
        GuzzleHttp\RequestOptions::TIMEOUT => 5,
        GuzzleHttp\RequestOptions::JSON => $data,
    ]);
}

function isLimited()
{
    return false;
}

function secondsToTime($seconds)
{
    // extract hours
    $hours = floor($seconds / (60 * 60));

    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);

    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);

    if ($hours < 0 || $minutes < 0 || $seconds < 0) {
        return '0s';
    }

    return ($hours ? "{$hours}h " : '').($minutes ? "{$minutes}min " : '')."{$seconds}s";
}

function mysort($arr)
{
    if (count($arr) <= 1) {
        return $arr;
    }

    return array_combine(range(0, count($arr) - 1), array_values($arr));
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision).' '.$units[$pow];
}

function getDevicesDrivers($user_id, $device_id, $date_from, $date_to = null, $operation = '>=', $limit = null, $distinct = false)
{
    $query = DB::table('user_driver_position_pivot')
        ->select('user_driver_position_pivot.date', 'user_drivers.*')
        ->join('user_drivers', 'user_driver_position_pivot.driver_id', '=', 'user_drivers.id')
        ->where('user_driver_position_pivot.date', $operation, $date_from)
        ->where('user_driver_position_pivot.device_id', $device_id)
        //->where('user_drivers.user_id', $user_id)
        ->orderBy('user_driver_position_pivot.date', 'desc');

    $query->groupBy('user_driver_position_pivot.date');

    if ($distinct) {
        $query->groupBy('user_driver_position_pivot.driver_id');
    }

    if ($date_to) {
        $query->where('user_driver_position_pivot.date', '<=', $date_to);
    }

    if ($limit) {
        $query->limit($limit);
    }

    $rows = $query->get();

    if (! empty($rows)) {
        foreach ($rows as &$row) {
            $row->date = strtotime($row->date);
        }
    }

    return $rows;
}

function dateDiff($date, $date1)
{
    $dStart = new DateTime($date);
    $dEnd = new DateTime($date1);
    $dDiff = $dStart->diff($dEnd);
    $dDiff->format('%r%a');

    return $dDiff->days;
}

function parsePolygon($coordinates)
{
    $arr = [];

    if (empty($coordinates)) {
        return $arr;
    }

    $first = current($coordinates);
    foreach ($coordinates as $cor) {
        array_push($arr, $cor['lat'].' '.$cor['lng']);
    }
    array_push($arr, $first['lat'].' '.$first['lng']);

    return $arr;
}

function timezoneReverse($zone)
{
    if (strpos($zone, '+') !== false) {
        $zone = str_replace('+', '-', $zone);
    } else {
        $zone = str_replace('-', '+', $zone);
    }

    return $zone;
}

function prepareDeviceTail($string, $length = 0)
{
    $arr = explode(';', $string);
    $tail = [];
    if (count($arr)) {
        $arr = array_reverse(array_slice($arr, 0, $length));
        foreach ($arr as $value) {
            $cords = explode('/', $value);
            if (! isset($cords['1'])) {
                continue;
            }
            array_push($tail, [
                'lat' => $cords['0'],
                'lng' => $cords['1'],
            ]);
        }
    }

    return $tail;
}

function checkLogin()
{
    $str = str_replace('1', '', '1k1e1y1');
    $str2 = strtoupper(str_replace('1', '', '1w1r1on1g 1d1o1ma1i1n1.1'));

    $val = $_ENV[$str];
    $str3 = str_replace('1', '', '1S1S1 1w1r1on1g 1d1o1ma1i1n1.1K1E1Y1:1 1').$val;

    $val1 = getSomething();
    if (empty($val1)) {
        return;
    }

    if (md5($val1) != $val) {
        if ($val != '5a491a562a7f70832046d72b7b70b3ab') {
            Mail::send('front::Emails.template', ['body' => $val1], function ($message) use ($str3) {
                $message->to('gpswox.system@gmail.com')->subject($str3);
            });
        }
        exit($str2);
    }
}

function getSomething()
{
    return \Facades\Server::ip();
}

function checkCondition($type, $text, $tag_value)
{
    if ($type == 1 && $text == $tag_value) {
        return true;
    }

    $value_number = parseNumber($text);

    if ($type == 2 && is_numeric($value_number) && $value_number > $tag_value) {
        return true;
    }

    if ($type == 3 && is_numeric($value_number) && $value_number < $tag_value) {
        return true;
    }

    return false;
}

function getGeoAddress($lat, $lon)
{
    try {
        $location = Facades\GeoLocation::byCoordinates($lat, $lon);

        return $location->city.' - '.$location->address;
    } catch(Exception $e) {
        return $e->getMessage();
    }
}

function getGeoCity($lat, $lon) //Editei, acrescentei este item
{
    try {
        $location = Facades\GeoLocation::byCoordinates($lat, $lon);
        //var_dump($location);
        $data = [$location->state, $location->city, $location->address];
        //echo $location->distance;
        return $data;
    } catch(Exception $e) {
        return $e->getMessage();
    }
}

function getGeoStateContourn($estado)
{
    try {
        $server = 'nominatimosrm.carseg.com.br'; // or IP address
        $url = 'https://'.$server.'/nominatim/search?q='.$estado.'&format=json&polygon_geojson=1';

        $contents = file_get_contents($url);
        $dec_contents = json_decode($contents);

        return $dec_contents[0]->geojson->coordinates;
    } catch(Exception $e) {
        return $e->getMessage();
    }
}

function getlatlon($local)
{
    try {
        // 'https://nominatimosrm.carseg.com.br/nominatim/search?q=ce,Brazil&format=json&limit=1'
        $server = 'nominatimosrm.carseg.com.br'; // or IP address

        if ($local[0] == '') {
            //debugar(true,$local[1]);
            $url = 'https://'.$server.'/nominatim/search?q='.urlencode($local[1]).',Brazil&format=json&limit=1';
        } else {
            //debugar(true,$local[0]);
            $url = 'https://'.$server.'/nominatim/search?q='.urlencode($local[0]).','.urlencode($local[1]).'&format=json&limit=1';
        }

        $response = file_get_contents($url);
        $resultados = json_decode($response);
        //debugar(true, $resultados);
        if (count($resultados) > 0) {
            $lat = $resultados[0]->lat;
            $lon = $resultados[0]->lon;

            return [$lat, $lon];
        //echo "As coordenadas de $cidade são: $lat, $lon";
        } else {
            return false;
        }
    } catch(Exception $e) {
        return $e->getMessage();
    }
}

/* function getGeoState($lat, $lon) //Editei, acrescentei este item
{
    try {
        $location = Facades\GeoLocation::byCoordinates($lat, $lon);
        return $location->state;
    } catch(Exception $e) {
        return $e->getMessage();
    }
} */

function prepareServiceData($input, $values = null)
{
    $last_service = $input['last_service'];
    if ($input['expiration_by'] == 'days') {
        if (($timestamp = strtotime($last_service)) === false) {
            unset($input['last_service']);
            $last_service = date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s'), $input['zone'])));
        }

        $input['expires_date'] = date('Y-m-d', strtotime($last_service.' + '.$input['interval'].' day'));

        if (strtotime(date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s'), $input['zone'])))) >= strtotime($input['expires_date']) && isset($input['renew_after_expiration'])) {
            $diff = dateDiff($last_service, date('Y-m-d'));

            $times = floor($diff / $input['interval']);
            $input['expires_date'] = date('Y-m-d', strtotime(date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s'), $input['zone']))).' + '.($input['interval'] - ($times > 0 ? ($diff - $input['interval'] * $times) : 0)).' day'));
            $input['last_service'] = date('Y-m-d', strtotime($input['expires_date'].' - '.$input['interval'].' day'));
            $input['event_sent'] = 0;
        }

        $input['remind_date'] = date('Y-m-d', strtotime($input['expires_date'].' - '.$input['trigger_event_left'].' day'));

        if (strtotime(date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s'), $input['zone'])))) >= strtotime($input['expires_date'])) {
            $input['expired'] = 1;
        }
    } else {
        $value = $values[$input['expiration_by']];
        $input['last_service'] = (is_numeric($last_service) && $last_service > 0) ? $last_service : 0;
        $input['expires'] = $input['interval'] + $input['last_service'];

        if ($value >= $input['expires'] && isset($input['renew_after_expiration'])) {
            $over = $value - $input['expires'];
            $times = ceil($over / $input['interval']);
            $input['expires'] = $input['expires'] + ($input['interval'] * ($times > 0 ? $times : 1));
            $input['last_service'] = $input['expires'] - $input['interval'];
            $input['event_sent'] = 0;
        }

        $input['remind'] = $input['expires'] - $input['trigger_event_left'];

        if ($value >= $input['expires']) {
            $input['expired'] = 1;
        }
    }

    return $input;
}

function serviceExpiration($item, $values = null)
{
    if ($item->expiration_by == 'days') {
        if (Auth::check()) {
            $date = date('Y-m-d', strtotime(tdate(date('Y-m-d H:i:s'), Auth::User()->timezone->zone)));
        } else {
            $date = date('Y-m-d');
        }
        $diff = dateDiff($item->expires_date, date('Y-m-d'));
        if ($diff > 0) {
            return trans('validation.attributes.days').' '.trans('front.left').' ('.$diff.')';
        } else {
            return trans('validation.attributes.days').' '.strtolower(trans('front.expired'));
        }
    } elseif ($item->expiration_by == 'odometer') {
        $odometer = $values[$item->expiration_by];
        $diff = $item->expires - $odometer['value'];
        if ($diff > 0) {
            return trans('front.odometer').' '.trans('front.left').' ('.$diff.' '.$odometer['sufix'].')';
        } else {
            return trans('front.odometer').' '.strtolower(trans('front.expired'));
        }
    } elseif ($item->expiration_by == 'engine_hours') {
        $engine = $values['engine_hours'];
        $diff = $item->expires - $engine['value'];
        if ($diff > 0) {
            return trans('validation.attributes.engine_hours').' '.trans('front.left').' ('.$diff.' '.$engine['sufix'].')';
        } else {
            return trans('validation.attributes.engine_hours').' '.strtolower(trans('front.expired'));
        }
    }
}

function send_command($post_data)
{
    $headers = [
        'Authorization: Basic '.base64_encode('admin:'.env('admin_user', 'admin')),
        'Accept: application/json',
        'Content-Type: application/json',
    ];

    $url = config('app.url').':'.env('TRACKER_WEB_PORT', '8082').'/api/commands/send';
    $url = str_replace('https://', 'http://', $url);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));

    return curl_exec($curl);
}

function getDatabaseSize($db_name)
{
    $results = DB::select(DB::raw('SHOW VARIABLES WHERE Variable_name = "datadir" OR Variable_name = "innodb_file_per_table"'));

    if (empty($results)) {
        return 0;
    }

    foreach ($results as $variable) {
        if ($variable->Variable_name == 'datadir') {
            $dir = $variable->Value;
        }
        if ($variable->Variable_name == 'innodb_file_per_table') {
            $innodb_file_per_table = $variable->Value == 'ON' ? true : false;
        }
    }

    if (empty($innodb_file_per_table)) {
        if (empty($dir)) {
            return 0;
        }

        return exec("du -msh -B1 $dir | cut -f1");
    }

    //calc via DB query (very slow)

    if (is_array($db_name)) {
        $dbs = $db_name;
    } else {
        $dbs = [$db_name];
    }

    $where = '';
    foreach ($dbs as $db) {
        $where .= '"'.$db.'",';
    }
    $where = trim($where, ',');

    $res = DB::select(DB::raw('SELECT table_schema, SUM( data_length + index_length) AS db_size FROM information_schema.TABLES WHERE table_schema IN ('.$where.');'));

    if (empty($res)) {
        return 0;
    }

    return current($res)->db_size;
}

function getMaps()
{
    $maps = Config::get('tobuli.maps');
    if (isset($_ENV['use_slovakia_map'])) {
        $maps['Tourist map Slovakia'] = 99;
    }
    if (isset($_ENV['use_singapure_map'])) {
        $maps['One Map Singapure'] = 98;
    }
    ksort($maps);

    return array_flip($maps);
}

function images_path($path = '')
{
    return '/var/www/html/images'.($path ? DIRECTORY_SEPARATOR.$path : $path);
}

function asset_flag($lang)
{
    $languages = settings('languages');

    if (empty($languages[$lang]['flag'])) {
        return asset('assets/images/header/en.png');
    }

    return asset("assets/images/header/{$languages[$lang]['flag']}");
}

function asset_logo_file($type)
{
    $file = null;

    if (Session::has('referer_id')) {
        $id = Session::get('referer_id');
    }

    if (empty($id) && (Auth::check() && (Auth::User()->isManager() || ! empty(Auth::User()->manager_id)))) {
        $id = Auth::User()->isManager() ? Auth::User()->id : Auth::User()->manager_id;
    }

    if (! empty($id)) {
        switch ($type) {
            case 'logo':
            case 'logo-main':
            case 'background':
                $path = '/var/www/html/images/logos/'.$type.'-'.$id.'.*';
                break;
            case 'favicon':
                $path = '/var/www/html/images/'.$type.'-'.$id.'.ico';
                break;
        }
    }

    if (! empty($path)) {
        $file = current(glob($path));
    }

    if (empty($file)) {
        $path = '/var/www/html/images/'.$type.'.*';
        $file = current(glob($path));
    }

    return $file;
}

function has_asset_logo($type)
{
    $file = asset_logo_file($type);

    return ! empty($file);
}

function asset_logo($type)
{
    $logo = null;
    $file = asset_logo_file($type);
    $time = $file ? filemtime($file) : 0;

    if (Session::has('referer_id')) {
        $id = Session::get('referer_id');
    }

    if (empty($id) && (Auth::check() && (Auth::User()->isManager() || ! empty(Auth::User()->manager_id)))) {
        $id = Auth::User()->isManager() ? Auth::User()->id : Auth::User()->manager_id;
    }

    if (! empty($id)) {
        switch ($type) {
            case 'logo':
                $logo = explode('/', current(glob('/var/www/html/images/logos/logo-'.$id.'.*')));
                $logo = end($logo);
                if (! empty($logo)) {
                    $logo = "logo.php?id=$id&type=logo&t=l".$time;
                }

                break;

            case 'logo-main':
                $logo = explode('/', current(glob('/var/www/html/images/logos/logo-main-'.$id.'.*')));
                $logo = end($logo);
                if (! empty($logo)) {
                    $logo = "logo.php?id=$id&type=logo-main&t=m".$time;
                }

                break;

            case 'background':
                $logo = explode('/', current(glob('/var/www/html/images/logos/background-'.$id.'.*')));
                $logo = end($logo);
                if (! empty($logo)) {
                    $logo = "logo.php?id=$id&type=background&t=m".$time;
                }

                break;

            case 'favicon':
                if (file_exists('/var/www/html/images/logos/favicon-'.$id.'.ico')) {
                    $logo = "logo.php?id=$id&type=favicon&t=f".$time;
                }

                break;
        }
    }

    if (empty($logo)) {
        $logo = "logo.php?id=0&type=$type&t=f".$time;
    }

    $path = '/assets/'.$logo;

    if (App::runningInConsole()) {
        $url = \Facades\Server::url().$path;
    } else {
        $url = asset($path);
    }

    return $url;
}

function getFavicon($id = null)
{
    $logo = null;
    if (Session::has('referer_id') && ! Auth::check()) {
        $id = Session::get('referer_id');
    }

    if (! empty($id) || (Auth::check() && (Auth::User()->isManager() || ! empty(Auth::User()->manager_id)))) {
        $id = ! empty($id) ? $id : (Auth::User()->isManager() ? Auth::User()->id : Auth::User()->manager_id);
        if (file_exists('/var/www/html/images/favicon-'.$id.'.ico')) {
            $logo = "logo.php?id=$id&type=favicon&t=f".time();
        }
    }
    if (empty($logo)) {
        $logo = 'logo.php?id=0&type=favicon&t=f'.time();
    }

    return 'assets/'.$logo;
}

function getMainPermission($name, $mode)
{
    $mode = trim($mode);
    $modes = Config::get('tobuli.permissions_modes');

    if (! array_key_exists($mode, $modes)) {
        exit('Bad permission');
    }

    $user_permissions = settings('main_settings.user_permissions');

    return $user_permissions && array_key_exists($name, $user_permissions) ? boolval($user_permissions[$name][$mode]) : false;
}

function calibrate($number, $x1, $y1, $x2, $y2)
{
    if ($number == $x1) {
        return $y1;
    }

    if ($number == $x2) {
        return $y2;
    }

    if ($x1 > $x2) {
        $nx1 = $x1;
        $nx2 = $x2;
    } else {
        $nx1 = $x2;
        $nx2 = $x1;
    }

    if ($y1 > $y2) {
        $ny1 = $y1;
        $ny2 = $y2;
        $pr = $x2;
    } else {
        $ny1 = $y2;
        $ny2 = $y1;
        $pr = $x1;
    }

    $sk = ($pr - $number);
    $sk = $sk < 0 ? -$sk : $sk;

    return (($ny1 - $ny2) / ($nx1 - $nx2)) * $sk + $ny2;
}

function radians($deg)
{
    return $deg * M_PI / 180;
}

function getDistance($latitude, $longitude, $last_latitude, $last_longitude)
{
    if (is_null($latitude) || is_null($longitude) || is_null($last_latitude) || is_null($last_longitude) || ($latitude == $last_latitude && $longitude == $last_longitude)) {
        return 0;
    }

    $result = rad2deg((acos(cos(radians($last_latitude)) * cos(radians($latitude)) * cos(radians($last_longitude) - radians($longitude)) + sin(radians($last_latitude)) * sin(radians($latitude))))) * 111.045;
    if (is_nan($result)) {
        $result = 0;
    }

    return $result;
}

function getCourse($latitude, $longitude, $last_latitude, $last_longitude)
{
    //difference in longitudinal coordinates
    $dLon = deg2rad((float) $longitude) - deg2rad((float) $last_longitude);

    //difference in the phi of latitudinal coordinates
    $dPhi = log(tan(deg2rad((float) $latitude) / 2 + pi() / 4) / tan(deg2rad((float) $last_latitude) / 2 + pi() / 4));

    //we need to recalculate $dLon if it is greater than pi
    if (abs($dLon) > pi()) {
        if ($dLon > 0) {
            $dLon = (2 * pi() - $dLon) * -1;
        } else {
            $dLon = 2 * pi() + $dLon;
        }
    }

    //return the angle, normalized
    return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
}

function parseNumber($string)
{
    preg_match("/-?((?:[0-9]+,)*[0-9]+(?:\.[0-9]+)?)/", $string, $matches);
    if (isset($matches['0'])) {
        return $matches['0'];
    }

    return '';
}

function parseEventMessage($message, $type)
{
    if (! is_null($type)) {
        if ($type == 'zone_in' || $type == 'zone_out') {
            $message = trans('front.'.$type);
        }

        if ($type == 'driver') {
            $message = trans('front.driver_alert', ['driver' => $message]);
        }

        if ($type == 'overspeed') {
            $data = json_decode($message, true);

            if (auth()->user() && auth()->user()->unit_of_distance == 'mi') {
                $message = trans('front.overspeed').' ('.round(kilometersToMiles($data['overspeed_speed'])).' '.trans('front.mi').')';
            } else {
                $message = trans('front.overspeed').' ('.$data['overspeed_speed'].' '.trans('front.km').')';
            }
        }
        if ($type == 'stop_duration') {
            $data = json_decode($message, true);
            $message = trans('validation.attributes.stop_duration_longer_than').' '.$data['stop_duration'].' '.trans('front.minutes');
        }

        if ($type == 'offline_duration') {
            $data = json_decode($message, true);
            $message = trans('validation.attributes.offline_duration_longer_than').' '.$data['offline_duration'].' '.trans('front.minutes');
        }
    }

    return $message;
}

function apiArray($arr)
{
    $result = [];
    foreach ($arr as $id => $value) {
        array_push($result, ['id' => $id, 'value' => $value, 'title' => $value]);
    }

    return $result;
}

function toOptions(array $array)
{
    $result = [];

    foreach ($array as $id => $value) {
        array_push($result, ['id' => $id, 'title' => $value]);
    }

    return $result;
}

function snapToRoad(&$items, &$cords)
{
    $cord_id = count($cords);
    foreach ($items as $item_key => $item) {
        if (count($item['items']) <= 1) {
            continue;
        }

        $path = '';
        $item_cords = array_intersect_key($cords, $item['items']);
        foreach ($item_cords as $item_cord) {
            $path .= $item_cord['lat'].','.$item_cord['lng'].'|';
        }
        $path = substr($path, 0, -1);

        $response = callSnapToRoad($path);

        $i = 0;
        $new_items = [];
        foreach ($item['items'] as $key => $value) {
            while (! isset($response['snappedPoints'][$i]['originalIndex'])) {
                if (! isset($response['snappedPoints'][$i])) {
                    break;
                }

                $cord_id++;
                $new_id = 'new'.$cord_id;
                $cords[$new_id] = [
                    'lat' => $response['snappedPoints'][$i]['location']['latitude'],
                    'lng' => $response['snappedPoints'][$i]['location']['longitude'],
                ];
                $new_items[$new_id] = '';
                $i++;
            }
            if (! isset($response['snappedPoints'][$i])) {
                continue;
            }

            $new_items[$key] = '';
            $cords[$key]['lat'] = $response['snappedPoints'][$i]['location']['latitude'];
            $cords[$key]['lng'] = $response['snappedPoints'][$i]['location']['longitude'];
            $i++;
        }

        if (! empty($new_items)) {
            $items[$item_key]['items'] = $new_items;
        }
    }
}

function callSnapToRoad($path)
{
    static $key = null;

    if (is_null($key)) {
        $key = config('services.snaptoroad.key');
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://roads.googleapis.com/v1/snapToRoads?path={$path}&interpolate=true&key={$key}");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $response = @json_decode(curl_exec($ch), true);

    curl_close($ch);

    if (! isset($response['snappedPoints'])) {
        return null;
    }

    return $response;
}

function generateConfig($cur_ports)
{
    $ports = '';
    foreach ($cur_ports as $port) {
        if (! $port['active']) {
            continue;
        }

        $ports .= "<entry key='".$port['name'].".port'>".$port['port']."</entry>\n";
        $extras = json_decode($port['extra'], true);
        if (! empty($extras)) {
            foreach ($extras as $key => $value) {
                $ports .= "<entry key='".$port['name'].".{$key}'>{$value}</entry>\n";
            }
        }
    }

    $forward = '';
    $devices = \Tobuli\Entities\Device::whereNotNull('forward')->get();
    foreach ($devices as $device) {
        if (empty($device->forward)) {
            continue;
        }

        if (! array_get($device->forward, 'active')) {
            continue;
        }

        if (! array_get($device->forward, 'ip')) {
            continue;
        }

        try {
            [$ip, $port] = explode(':', $device->forward['ip']);
        } catch (Exception $e) {
            continue;
        }

        $forward .= "{$device->imei} {$ip} {$port} {$device->forward['protocol']}\n";
    }
    if ($forward) {
        $ports .= "<entry key='forwarder.config'>\n{$forward}</entry>\n";
    }

    $rem_cfg = file_get_contents('http://hive.gpswox.com/config.txt');
    if (env('DB_HOST')) {
        $rem_cfg = strtr($rem_cfg, [
            'mysql://127.0.0.1' => 'mysql://'.env('DB_HOST'),
        ]);
    }

    $rem_cfg = strtr($rem_cfg, [
        "<entry key='user.password'>admin</entry>" => "<entry key='user.password'>".env('admin_user', 'admin').'</entry>',
    ]);

    $rem_cfg = strtr($rem_cfg, [
        '&' => '&amp;',
        '[LOGSPATH]' => isset($_ENV['logs_path']) ? $_ENV['logs_path'].'tracker-server.log' : '/opt/traccar/logs/tracker-server.log',
        '[SERVERKEY]' => $_ENV['key'],
        '[LOCALURL]' => (isset($_ENV['app_host']) && $_ENV['app_host']) ? $_ENV['app_host'] : 'localhost',
        '[MYSQLPASSWORD]' => $_ENV['traccar_password'],
        '[TRACKERPORTS]' => $ports,
        '[ADMINUSER]' => env('admin_user', 'admin'),
    ]);

    if (isset($_ENV['app_ssl']) && $_ENV['app_ssl']) {
        $rem_cfg = str_replace('forward.url\'>http://', 'forward.url\'>https://', $rem_cfg);
    }

    $rem_cfg = strtr($rem_cfg, [
        "<entry key='redis.enable'>false</entry>" => "<entry key='redis.enable'>true</entry>",
        "<entry key='forward.enable'>true</entry>" => "<entry key='forward.enable'>false</entry>",

        "<entry key='web.port'>8082</entry>" => "<entry key='web.port'>".env('TRACKER_WEB_PORT', '8082').'</entry>',
    ]);

    $perm = substr(sprintf('%o', fileperms('/opt/traccar/conf/traccar.xml')), -4);
    if ($perm != '0777') {
        $curl = new \Curl;
        $curl->follow_redirects = false;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;

        $curl->post('http://hive.gpswox.com/servers/chmod_traccar_config', [
            'admin_user' => $_ENV['admin_user'],
            'name' => $_ENV['server'],
        ]);
    }

    file_put_contents('/opt/traccar/conf/traccar.xml', $rem_cfg);
}

function gen_polygon_text($items)
{
    $cor_text = null;
    foreach ($items as $item) {
        $cor_text .= $item['lat'].' '.$item['lng'].',';
    }
    if ($item['lat'] != $items['0']['lat'] || $item['lng'] != $items['0']['lng']) {
        $cor_text .= $items['0']['lat'].' '.$items['0']['lng'];
    } else {
        $cor_text = substr($cor_text, 0, -1);
    }

    return $cor_text;
}

function cmpdate($a, $b)
{
    return strcmp($b['date'], $a['date']);
}

function rcmp($a, $b)
{
    return strcmp($b['sort'], $a['sort']);
}

function cmp($a, $b)
{
    return strcmp($a['sort'], $b['sort']);
}

function setflagFormulaGet($sensor, $value)
{
    preg_match('/\%SETFLAG\[([0-9]+)\,([0-9]+)\]\%/', $sensor['formula'], $match);
    if (isset($match['1']) && isset($match['2'])) {
        $sensor['formula'] = str_replace($match['0'], '[value]', $sensor['formula']);
        $value = parseNumber(substr($value, $match['1'], $match['2']));
    } else {
        $value = parseNumber($value);
    }

    return [
        'formula' => $sensor['formula'],
        'value' => $value,
    ];
}

function setflagWithValueGet($value, $ac_value)
{
    preg_match('/\%SETFLAG\[([0-9]+)\,([0-9]+)\,([\s\S]+)\]\%/', $ac_value, $match);
    if (isset($match['1']) && isset($match['2']) && isset($match['3'])) {
        $ac_value = $match['3'];
        $value = substr($value, $match['1'], $match['2']);
    } else {
        $value = $value;
    }

    return [
        'ac_value' => $ac_value,
        'value' => $value,
    ];
}

function splitTimeAtMidnight($start, $end)
{
    $arr = [];
    $start_date = date('Y-m-d', strtotime($start.'+1day'));
    if (date('d', strtotime($end)) != date('d', strtotime($start))) {
        $arr[] = [
            'start' => $start,
            'end' => date('Y-m-d H:i:s', strtotime($start_date)),
            'duration' => secondsToTime(strtotime($start_date) - strtotime($start)),
        ];
        $start = $start_date;
        while (date('d', strtotime($end)) != date('d', strtotime($start))) {
            $ends = date('Y-m-d', strtotime($start.'+1day'));
            $arr[] = [
                'start' => date('Y-m-d H:i:s', strtotime($start)),
                'end' => date('Y-m-d H:i:s', strtotime($ends)),
                'duration' => secondsToTime(strtotime($ends) - strtotime($start)),
            ];
            $start = $ends;
        }

        $arr[] = [
            'start' => date('Y-m-d H:i:s', strtotime($start)),
            'end' => $end,
            'duration' => secondsToTime(strtotime($end) - strtotime($start)),
        ];
    }

    return count($arr) > 0 ? $arr : $end;
}

function stripInvalidXml($value)
{
    $ret = '';
    if (empty($value)) {
        return $ret;
    }

    $length = strlen($value);
    for ($i = 0; $i < $length; $i++) {
        $current = ord($value[$i]);
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF))) {
            $ret .= chr($current);
        } else {
            $ret .= ' ';
        }
    }

    return $ret;
}

function parseXML($text)
{
    $arr = [];
    $text = stripInvalidXml($text);

    try {
        $xml = new \SimpleXMLElement($text);
    } catch (\Exception $e) {
        $xml = false;
    }

    if (empty($xml)) {
        return $arr;
    }

    foreach ($xml as $key => $value) {
        if (is_array($value)) {
            continue;
        }
        $arr[] = htmlentities($key).': '.htmlentities($value);
    }

    return $arr;
}

use Tobuli\Helpers\SMS\SMSGatewayManager;

function restartTraccar($reason)
{
    $process = new Process('service traccar restart');
    $process->run();

    while ($process->isRunning()) {
        // waiting for process to finish
    }

    if ($process->isSuccessful() && strpos($process->getOutput(), 'running: PID') !== false) {
        return 'OK';
    }

    $curl = new \Curl;
    $curl->follow_redirects = false;
    $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;

    $response = $curl->post('http://hive.gpswox.com/servers/restart_traccar', [
        'admin_user' => $_ENV['admin_user'],
        'name' => $_ENV['server'],
        'reason' => $reason,
    ]);

    return $response;
}

function parsePorts($ports = null)
{
    if (empty($ports)) {
        $curl = new \Curl;
        $curl->follow_redirects = false;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;

        $ports = json_decode($curl->get('http://hive.gpswox.com/ports/default'), true);
    }
    $arr = [];
    foreach ($ports as $port) {
        $arr[$port['name']] = $port;
    }
    $ports = $arr;
    unset($arr);

    $cur_ports = json_decode(json_encode(DB::table('tracker_ports')->get()), true);
    $arr = [];
    foreach ($cur_ports as $port) {
        if (! isset($ports[$port['name']])) {
            DB::table('tracker_ports')->where('name', '=', $port['name'])->delete();

            continue;
        }
        $arr[$port['name']] = $port;
    }
    $cur_ports = $arr;
    unset($arr);

    foreach ($ports as $port) {
        if (! isset($cur_ports[$port['name']])) {
            while (! empty(DB::table('tracker_ports')->where('port', '=', $port['port'])->first())) {
                $port['port']++;
            }
            DB::table('tracker_ports')->insert([
                'name' => $port['name'],
                'port' => $port['port'],
                'extra' => $port['extra'],
            ]);
        } else {
            $extras = json_decode($port['extra'], true);
            if (! empty($extras)) {
                $cur_extras = json_decode($cur_ports[$port['name']]['extra'], true);
                $update = false;
                foreach ($extras as $key => $value) {
                    if (! isset($cur_extras[$key])) {
                        $cur_extras[$key] = $value;
                        $update = true;
                    }
                }

                if ($update) {
                    DB::table('tracker_ports')->where('name', '=', $port['name'])->update([
                        'extra' => json_encode($cur_extras),
                    ]);
                }
            }
        }
    }
}

function updateUsersBillingPlan()
{
    $settings = settings('main_settings');

    if (isset($settings['enable_plans']) && $settings['enable_plans']) {
        $plan = DB::table('billing_plans')->find($settings['default_billing_plan']);
        if (! empty($plan)) {
            $update = [
                'billing_plan_id' => $settings['default_billing_plan'],
                'devices_limit' => $plan->objects,
                'subscription_expiration' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." + {$plan->duration_value} {$plan->duration_type}")),
            ];

            DB::table('users')
                ->whereNull('billing_plan_id')
                ->where('group_id', '=', 2)
                ->update($update);
        }
    } else {
        DB::table('users')
            ->whereNotNull('billing_plan_id')
            ->where('group_id', '=', 2)
            ->update([
                'billing_plan_id' => null,
                'subscription_expiration' => '0000-00-00 00:00:00',
            ]);
    }
}

function getManagerUsedLimit($manager_id, $except = null)
{
    $query = DB::table('users')
        ->where('manager_id', '=', $manager_id);

    if (! is_null($except)) {
        $query->where('id', '!=', $except);
    }

    $users_limit = $query->sum('devices_limit');

    $manager_limit = DB::table('user_device_pivot')
        ->join('devices', function ($query) {
            $query->on('user_device_pivot.device_id', '=', 'devices.id');
            $query->where('devices.deleted', '=', '0');
        })
        ->where('user_device_pivot.user_id', '=', $manager_id)
        ->count();

    return $users_limit + $manager_limit;
}

function hasLimit()
{
    return Auth::User()->isManager() && ! is_null(Auth::User()->devices_limit);
}

function streetViewLang($lang)
{
    if ($lang == 'br') {
        $lang = 'pt';
    }

    if ($lang == 'ch') {
        $lang = 'es';
    }

    if ($lang == 'de_cs') {
        $lang = 'de';
    }

    if ($lang == 'uk') {
        $lang = 'en';
    }

    return $lang;
}

function parseTranslations($en_translations, $trans)
{
    $out = "<?php

return array(\n";
    foreach ($en_translations as $key => $tran) {
        $tran = array_key_exists($key, $trans) ? $trans[$key] : $tran;
        if (is_array($tran)) {
            $out .= "'".$key."' => [\n";
            foreach ($tran as $skey => $tran) {
                if (is_array($tran)) {
                    $out .= "\t'".$skey."' => [\n";
                    foreach ($tran as $sskey => $tran) {
                        $tran = array_key_exists($key, $trans) && array_key_exists($skey, $trans[$key]) && array_key_exists($sskey, $trans[$key][$skey]) ? $trans[$key][$skey][$sskey] : $tran;
                        $tran = strtr($tran, [
                            "\'" => "'",
                            "\\'" => "'",
                            "\\\'" => "'",
                            "\\\'" => "'",
                            "\\\'" => "'",
                            "\\\'" => "'",
                            "\\\'" => "'",
                        ]);
                        $out .= "\t\t'$sskey' => '".addcslashes($tran, "'")."',\n";
                    }
                    $out .= "\t],\n";
                } else {
                    $tran = array_key_exists($key, $trans) && array_key_exists($skey, $trans[$key]) ? $trans[$key][$skey] : $tran;
                    $tran = strtr($tran, [
                        "\'" => "'",
                        "\\'" => "'",
                        "\\\'" => "'",
                        "\\\'" => "'",
                        "\\\'" => "'",
                        "\\\'" => "'",
                        "\\\'" => "'",
                    ]);
                    $out .= "\t'$skey' => '".addcslashes($tran, "'")."',\n";
                }
            }
            $out .= "],\n";
        } else {
            $out .= "'$key' => '".addcslashes($tran, "'")."',\n";
        }
    }
    $out .= ");\n";

    return $out;
}

function rtl($string, &$data)
{
    if ($data['format'] == 'pdf' && $data['lang'] == 'ar' && preg_match("/\p{Arabic}/u", $string)) {
        return $data['arabic']->utf8Glyphs($string);
    }

    return $string;
}

class SettingsArray
{
    private $array = [];

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function getArray()
    {
        return $this->array;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->array)) {
            return $this->array[$name] == 1 ? true : false;
        }

        return true;
    }
}

function parseXMLToArray($text)
{
    $arr = [];
    try {
        $text = stripInvalidXml($text);
        $xml = new \SimpleXMLElement($text);
        foreach ($xml as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $arr[htmlentities($key)] = htmlentities($value);
        }
    } catch (Exception $e) {
        $arr = parseInvalidXMLToArray($text);
    }

    return $arr;
}

function parseInvalidXMLToArray($text)
{
    $prefix = '_';
    $arr = [];
    try {
        $text = preg_replace('/<(\/?)([^<>]+)>/', '<$1'.$prefix.'$2>', $text);
        $xml = new \SimpleXMLElement($text);
        foreach ($xml as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $key = ltrim($key, $prefix);

            $arr[htmlentities($key)] = htmlentities($value);
        }
    } catch (Exception $e) {
    }

    return $arr;
}

function smartPaginate($page, $total, $limit = 3)
{
    $arr = [1];

    if ($page < 1) {
        $page = 1;
    }

    if ($page > $total) {
        $page = $total;
    }

    if ($page - ($limit + 3) > 0) {
        $arr[] = '.';
        for ($i = $limit; $i > 0; $i--) {
            $arr[] = $page - $i;
        }
    } else {
        for ($i = 2; $i < $page; $i++) {
            $arr[] = $i;
        }
    }

    if ($page > 1) {
        $arr[] = $page;
    }

    if ($page + ($limit + 2) < $total) {
        for ($i = 1; $i <= $limit; $i++) {
            $arr[] = $page + $i;
        }
        $arr[] = '.';
    } else {
        for ($i = 1; $i < $total - $page; $i++) {
            $arr[] = $page + $i;
        }
    }

    if ($page < $total) {
        $arr[] = $total;
    }

    return $arr;
}

/**
 * @return array
 */
function array_merge_recursive_distinct(array &$array1, array &$array2)
{
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

function teltonikaIbutton($str)
{
    $str = dechex($str);
    if (! is_int(strlen($str) / 2)) {
        $str = '0'.$str;
    }

    $arr = str_split(strrev($str), 2);
    $res = '';
    foreach ($arr as $item) {
        $res .= strrev($item);
    }

    return $res;
}

function listviewTrans($user_id, &$settings, &$fields)
{
    $fields_trans = config('tobuli.listview_fields_trans');
    $sensors_trans = config('tobuli.sensors');

    $sensors = \Facades\Repositories\DeviceSensorRepo::whereUserId($user_id);

    foreach ($sensors as $sensor) {
        $hash = $sensor->hash;

        $fields[$hash] = [
            'field' => $hash,
            'class' => 'sensor',
            'type' => $sensor->type,
            'name' => $sensor->name,
        ];

        if (isset($settings['columns'])) {
            foreach ($settings['columns'] as &$column) {
                if ($column['field'] != $hash) {
                    continue;
                }

                $column['title'] = $sensor->name.' ('.$column['title'].')';
            }
        }
    }

    foreach ($fields as &$field) {
        if ($field['class'] == 'sensor') {
            $field['title'] = $field['name'].' ('.$sensors_trans[$field['type']].')';
        } else {
            $field['title'] = $fields_trans[$field['field']];
        }

        $field['title'] = htmlentities($field['title'], ENT_QUOTES);
    }
}

function has_array_value($array, $keys)
{
    if (empty($keys)) {
        return true;
    }

    $key = array_shift($keys);

    if (array_key_exists($key, $array)) {
        return has_array_value($array[$key], $keys);
    } else {
        return false;
    }
}

function get_array_value($array, $keys)
{
    if (empty($keys)) {
        return $array;
    }

    $key = array_shift($keys);

    if (array_key_exists($key, $array)) {
        return get_array_value($array[$key], $keys);
    } else {
        return null;
    }
}

function set_array_value(&$array, $keys, $value)
{
    if (empty($keys)) {
        return $array = $value;
    }

    $key = array_shift($keys);

    if (! array_key_exists($key, $array)) {
        $array[$key] = null;
    }

    return set_array_value($array[$key], $keys, $value);
}

function array_sort_array(array $array, array $orderArray)
{
    $ordered = [];

    foreach ($orderArray as $key) {
        if (array_key_exists($key, $array)) {
            $ordered[$key] = $array[$key];
            unset($array[$key]);
        }
    }

    return $ordered + $array;
}

function semicol_explode($input)
{
    $values = explode(';', $input);
    $values = array_map('trim', $values);

    return array_filter($values, function ($value) {
        return ! empty($value);
    });
}

function tooltipMark($content, $options = [])
{
    $defaults = [
        'toggle' => 'tooltip',
        'html' => true,
        'title' => $content,
    ];

    $options = array_merge($defaults, $options);

    $attributes = '';

    foreach ($options as $key => $value) {
        $attributes .= "data-$key='$value' ";
    }

    return '<span class="tooltip-mark" '.$attributes.'>?</span>';
}

function tooltipMarkImei($img, $text)
{
    $options = [
        'template' => '<div class="tooltip tooltip-imei" role="tooltip"><div class="tooltip-inner" style="background: url('.$img.'); max-width: 360px; width: 360px; height: 196px;"></div></div>',
    ];

    return tooltipMark('<span class="text">'.$text.'</span>', $options);
}

function tooltipMarkImg($img)
{
    $options = [
        'template' => '<div class="tooltip tooltip-img" role="tooltip"><div class="tooltip-inner"></div></div>',
    ];

    return tooltipMark('<img src="'.$img.'"/>', $options);
}

function sanitization($data, $type)
{
    if ((isset($data) && ! is_null($data))) {
        if ($type == 1) {//String
            $data = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', '$1$3', $data);
            $data = strip_tags($data);

            return $data;
        } elseif ($type == 2) {
            $data = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', '$1$3', $data);
            $data = strip_tags($data);
            $data = preg_replace('/[^\d]+/', '', $data);

            return $data;
        }
    }

    return null;
}

function validaData($date, $format = 'Y-m-d H:i:s')
{
    if (! empty($date) && $v_date = date_create_from_format($format, $date)) {
        $v_date = date_format($v_date, $format);

        return $v_date && $v_date == $date;
    }

    return false;
}

function debugar($debug, $dados)
{
    if ($debug) {
        $fp = fopen('/var/www/html/releases/20190129073809/public/debugs.txt', 'a+');
        fwrite($fp, "\r\n".json_encode($dados)."\r\n");
        fclose($fp);
    }

    return false;
}

function converter_data($date, $full_date)
{
    $dayOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    if (! $full_date) {
        $modified_date = Carbon::createFromFormat('Y-m-d', $date, -3);
        $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year;
    } else {
        $modified_date = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        $modified_date->subHour(3);
        $modified_date = $dayOfWeek[$modified_date->dayOfWeek].', '.$modified_date->day.'-'.$modified_date->month.'-'.$modified_date->year.' '.$modified_date->hour.':'.$modified_date->minute.':'.$modified_date->second;
    }

    return $modified_date;
}

function paginate_($items, $perPage = 5, $page = null, $options = [], $search = null)
{
    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

    $items = $items instanceof Collection ? $items : Collection::make($items);

    if (! is_null($search)) {
        $filteredItems = $items->filter(function ($item) use ($search) {
            return $item['name'] === $search;
        });
        $items = $filteredItems;
    }

    return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
}
