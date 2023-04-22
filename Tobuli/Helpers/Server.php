<?php

namespace Tobuli\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Server {

    public function ip()
    {
        $ip = null;

        try {
            $prefix = php_sapi_name() . '.server.';

            $ip = Cache::get($prefix.'ip');

            if ($ip)
                return $ip;

            //$ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;

            if (!$ip || $this->isPrivateIP($ip))
                $ip = @exec('curl -s ipinfo.io/ip');

            $ip = trim($ip);

            if (ip2long($ip) && !$this->isPrivateIP($ip))
                Cache::put($prefix.'ip', $ip, 15);

        } catch (\Exception $e){};

        return $ip;
    }

    public function isPrivateIP($value) {
        if ($value == '127.0.0.1')
            return true;

        if (strpos($value, '192.168.') === 0)
            return true;

        if (strpos($value, '10.') === 0)
            return true;

        return false;
    }

    public function hostname()
    {
        $hostname = null;

        try {
            $prefix = php_sapi_name() . '.server.';

            $hostname = Cache::get($prefix.'hostname');

            if ($hostname)
                return $hostname;

            $hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;

            if (empty($hostname))
                $hostname = gethostname();

            if ($hostname && !$this->isPrivateIP($hostname))
                Cache::put($prefix.'hostname', $hostname, 5);

        } catch (\Exception $e){};

        return $hostname;
    }

    public function url()
    {
        $url = config('app.url');

        if ( !empty($url) && $url != 'http://localhost' )
            return $url;

        $hostname = $this->hostname();

        if (!$hostname)
            $hostname = $this->ip();

        $protocol = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';

        return $protocol . $hostname;
    }

    public function lastUpdate()
    {
        return date('Y-m-d H:i:s', File::lastModified(base_path('server.php')));
    }

    public function isAutoDeploy()
    {
        return ! File::exists(storage_path('autodeploy'));
    }

    public function isDisabled()
    {
        return file_exists('/var/www/html/disabled.txt');
    }

    public function isApiDisabled()
    {
        return file_exists('/var/www/html/apidisabled');
    }
}