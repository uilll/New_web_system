<?php

namespace Tobuli\Helpers\GeoLocation;

use Illuminate\Support\Facades\Cache;

class GeoLocation
{
    private $service;
    private $cache;
    private $cacheExpiration;
    private $cacheMethods;

    public function __construct()
    {
        $class = 'Tobuli\Helpers\GeoLocation\GeoServices\Geo' . ucfirst(settings('main_settings.geocoder_api'));

        if ( ! class_exists($class, true)) {
            throw new \Exception('GeoService class not found!');
        }

        $this->service = new $class;

        $this->cacheMethods = ['byCoordinates'];
        $this->cacheExpiration = (int)settings('main_settings.geocoder_cache_days') * 24 * 60 * 60;

        $this->cacheDrive();
    }


    public function __call($method, $parameters)
    {
        $parameters = call_user_func_array([$this, $method . 'Normalize'], $parameters);

        if ($this->cache && in_array($method, $this->cacheMethods)) {
            return $this->cache->remember(
                $this->cacheKey($method, $parameters),
                $this->cacheExpiration,
                function () use ($method, $parameters) {
                    return call_user_func_array([$this->service, $method], $parameters);
                }
            );
        }

        return call_user_func_array([$this->service, $method], $parameters);
    }

    private function cacheDrive()
    {
        $this->cache = null;

        if ( ! (bool)settings('main_settings.geocoder_cache_enabled'))
            return;

        try {
            $this->cache = Cache::store(config('tobuli.geocoder_cache_driver'));
        } catch(\Exception $e) {
            $this->cache = null;
        }

        return;
    }

    private function cacheKey($method, $parameters)
    {
        if ( ! is_array($parameters))
            $parameters = [$parameters];

        $parameters[] = $method;
        $parameters[] = config('tobuli.languages.' . config('app.locale') . '.iso', 'en');

        return implode(',', $parameters);
    }

    private function byAddressNormalize($address)
    {
        return [$address];
    }

    private function listByAddressNormalize($address)
    {
        return [$address];
    }

    private function byCoordinatesNormalize($lat, $lng)
    {
        if ( ! is_numeric($lat) || ! is_numeric($lng)) {
            throw new \Exception('Bad coordinates input!');
        }

        $parameters[0] = round($lat, 11);
        $parameters[1] = round($lng, 11);

        return $parameters;
    }

    public function flushCache()
    {
        return $this->cache ? $this->cache->flush() : null;
    }
}