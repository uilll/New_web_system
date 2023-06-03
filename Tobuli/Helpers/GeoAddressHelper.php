<?php

namespace Tobuli\Helpers;

use Illuminate\Support\Facades\Cache;
use Stanley\Geocodio\Client;

class GeoAddressHelper
{
    //completed
    public function getAddressLocation($address)
    {
        $curl = new \Curl;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;

        $location = null;

        try {
            switch (settings('main_settings.geocoder_api')) {
                case 'default':
                    $data = $curl->get('http://173.212.206.125/geo/address.php', [
                        'q' => $address,
                        'format' => 'json',
                        'lang' => config('app.locale'),
                    ]);
                    $response = @json_decode($data->body, true);
                    if (! empty($response) && is_array($response)) {
                        $first = current($response);

                        $location = [
                            'lat' => $first['lat'],
                            'lng' => $first['lon'],
                        ];
                    }

                    break;
                case 'google':
                    $data = $curl->get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'address' => $address,
                        'key' => settings('main_settings.api_key'),
                    ]);
                    $response = @json_decode($data->body, true);

                    if (! empty($response['results'])) {
                        $first = current($response['results']);

                        $location = [
                            'lat' => $first['geometry']['location']['lat'],
                            'lng' => $first['geometry']['location']['lng'],
                        ];
                    }
                    break;
                case 'geocodio':
                    $client = new Client(settings('main_settings.api_key'));
                    $response = $client->geocode($address)->response;

                    if (isset($response->results['0'])) {
                        $location = [
                            'lat' => $response->results['0']->location->lat,
                            'lng' => $response->results['0']->location->lng,
                        ];
                    }
                    break;
                case 'openstreet':
                    $data = $curl->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $address,
                        'format' => 'json',
                        'lang' => config('app.locale'),
                    ]);

                    $response = @json_decode($data->body, true);
                    if (! empty($response) && is_array($response)) {
                        $first = current($response);

                        $location = [
                            'lat' => $first['lat'],
                            'lng' => $first['lon'],
                        ];
                    }
                    break;
                case 'locationiq':
                    $data = $curl->get('http://locationiq.org/v1/search.php', [
                        'format' => 'json',
                        'key' => settings('main_settings.api_key'),
                        'q' => $address,
                    ]);

                    $response = @json_decode($data->body, true);

                    if (! empty($response) && is_array($response)) {
                        $first = current($response);

                        $location = [
                            'lat' => $first['lat'],
                            'lng' => $first['lon'],
                        ];
                    }
                    break;
                case 'nominatim':
                    $data = $curl->get(settings('main_settings.api_url'), [
                        'q' => $address,
                        'format' => 'json',
                        'accept-language' => config('app.locale'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && ! empty($arr)) {
                        foreach ($arr as $key => $prediction) {
                            $addressCollection[$key]['description'] = $prediction['display_name'];
                            $addressCollection[$key]['id'] = $prediction['place_id'];
                            $addressCollection[$key]['lat'] = $prediction['lat'];
                            $addressCollection[$key]['lng'] = $prediction['lon'];
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            $location = $e->getMessage();
        }

        return $location;
    }

    private function getAddressLatLngFromApi($placeId)
    {
    }

    private function autocompleteAddressFromApi($address)
    {
        $curl = new \Curl;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
        $addressCollection = [];

        try {
            switch (settings('main_settings.geocoder_api')) {
                case 'default':
                    $data = $curl->get('http://173.212.206.125/geo/address.php', [
                        'q' => $address,
                        'format' => 'json',
                        'lang' => config('app.locale'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && ! empty($arr)) {
                        foreach ($arr as $key => $prediction) {
                            $addressCollection[$key]['id'] = $prediction['place_id'];
                            $addressCollection[$key]['description'] = $prediction['display_name'];
                            $addressCollection[$key]['lat'] = $prediction['lat'];
                            $addressCollection[$key]['lng'] = $prediction['lon'];
                        }
                    }

                    break;
                case 'google':
                    $data = $curl->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                        'input' => $address,
                        'key' => settings('main_settings.api_key'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr)) {
                        if (array_key_exists('predictions', $arr) && ! empty($arr['predictions'])) {
                            foreach ($arr['predictions'] as $key => $prediction) {
                                $addressCollection[$key]['description'] = $prediction['description'];
                                $addressCollection[$key]['id'] = $prediction['id'];
                            }
                            foreach ($arr['predictions'] as $key => $prediction) {
                                $data = $curl->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                                    'placeid' => $addressCollection[$key]['id'],
                                    'key' => settings('main_settings.api_key'),
                                ]);
                            }
                            $arr = @json_decode($data->body, true);
                            if (array_key_exists('predictions', $arr) && ! empty($arr['predictions'])) {
                                foreach ($arr['predictions'] as $key => $prediction) {
                                    $addressCollection[$key]['description'] = $prediction['description'];
                                    $addressCollection[$key]['id'] = $prediction['id'];
                                }
                            } else {
                                if (array_key_exists('error_message', $arr)) {
                                    return $arr['error_message'];
                                }
                            }
                        } else {
                            if (array_key_exists('error_message', $arr)) {
                                return $arr['error_message'];
                            }
                        }
                    }
                    break;
                case 'geocodio':
                    $client = new Client(settings('main_settings.api_key'));
                    $address = $client->geocode($address)->response;
                    if (isset($address->results['0'])) {
                        $addressCollection[0]['description'] = $address->results['0']->formatted_address.', '.$address->results['0']->address_components->county;
                    }
                    $addressCollection[0]['id'] = md5($address->results['0']->formatted_address.', '.$address->results['0']->address_components->county);

                    break;
                case 'openstreet':
                    $data = $curl->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $address,
                        'addressdetails' => 1,
                        'format' => 'json',
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && ! empty($arr)) {
                        foreach ($arr as $key => $prediction) {
                            $addressCollection[$key]['description'] = $prediction['display_name'];
                            $addressCollection[$key]['id'] = $prediction['place_id'];
                            $addressCollection[$key]['lat'] = $prediction['lat'];
                            $addressCollection[$key]['lng'] = $prediction['lon'];
                        }
                    }
                    break;
                case 'locationiq':
                    $data = $curl->get('http://locationiq.org/v1/search.php', [
                        'format' => 'json',
                        'key' => settings('main_settings.api_key'),
                        'q' => $address,
                    ]);

                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && ! empty($arr)) {
                        foreach ($arr as $key => $prediction) {
                            $addressCollection[$key]['description'] = $prediction['display_name'];
                            $addressCollection[$key]['id'] = $prediction['place_id'];
                            $addressCollection[$key]['lat'] = $prediction['lat'];
                            $addressCollection[$key]['lng'] = $prediction['lon'];
                        }
                    }
                    break;
                case 'nominatim':
                    $data = $curl->get(settings('main_settings.api_url'), [
                        'q' => $address,
                        'format' => 'json',
                        'accept-language' => config('app.locale'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && ! empty($arr)) {
                        foreach ($arr as $key => $prediction) {
                            $addressCollection[$key]['description'] = $prediction['display_name'];
                            $addressCollection[$key]['id'] = $prediction['place_id'];
                            $addressCollection[$key]['lat'] = $prediction['lat'];
                            $addressCollection[$key]['lng'] = $prediction['lon'];
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            $addressCollection = [];
        }

        return $addressCollection;
    }

    private function getGeoAddressFromApi($lat, $lon, $address = null)
    {
        $curl = new \Curl;
        $curl->follow_redirects = false;
        $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;

        try {
            switch (settings('main_settings.geocoder_api')) {
                case 'default':
                    $data = $curl->get('http://173.212.206.125/geo/', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'format' => 'json',
                        'lang' => config('app.locale'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && array_key_exists('display_name', $arr) && ! empty($arr['display_name'])) {
                        $address = $arr['display_name'];
                    }
                    break;
                case 'google':
                    $data = $curl->get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'latlng' => $lat.','.$lon,
                        'key' => settings('main_settings.api_key'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr)) {
                        if (array_key_exists('results', $arr) && ! empty($arr['results'])) {
                            $address = current($arr['results'])['formatted_address'];
                        } else {
                            if (array_key_exists('error_message', $arr)) {
                                return $arr['error_message'];
                            }
                        }
                    }
                    break;
                case 'geocodio':
                    $client = new Client(settings('main_settings.api_key'));
                    $address = $client->reverse($lat.','.$lon)->response;
                    if (isset($address->results['0'])) {
                        $address = $address->results['0']->formatted_address.', '.$address->results['0']->address_components->county;
                    } else {
                        $address = '';
                    }

                    break;
                case 'openstreet':
                    $data = $curl->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'addressdetails' => 1,
                        'format' => 'json',
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && array_key_exists('display_name', $arr) && ! empty($arr['display_name'])) {
                        $address = $arr['display_name'];
                    }

                    break;
                case 'locationiq':
                    $data = $curl->get('http://locationiq.org/v1/reverse.php', [
                        'format' => 'json',
                        'key' => settings('main_settings.api_key'),
                        'lat' => $lat,
                        'lon' => $lon,
                        'zoom' => 18,
                    ]);

                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && array_key_exists('display_name', $arr) && ! empty($arr['display_name'])) {
                        $address = $arr['display_name'];
                    }
                    break;
                case 'nominatim':
                    $data = $curl->get(settings('main_settings.api_url'), [
                        'lat' => $lat,
                        'lon' => $lon,
                        'format' => 'json',
                        'accept-language' => config('app.locale'),
                    ]);
                    $arr = @json_decode($data->body, true);
                    if (is_array($arr) && array_key_exists('display_name', $arr) && ! empty($arr['display_name'])) {
                        $address = $arr['display_name'];
                    }

                    break;
            }
        } catch (\Exception $e) {
            $address = '';
        }

        return $address;
    }

    // by address
    // get from cache or empty method???
    public function getAddressLatLng($placeId)
    {
        $cacheEnabled = (bool) settings('main_settings.geocoder_cache_enabled');

        if ($cacheEnabled) {
            $latLng = $this->getAddressLatLngFromCache($placeId);
            if (! empty($latLng)) {
                return $latLng;
            }
        }

        $latLng = $this->getAddressLatLngFromApi($placeId);

        if ($cacheEnabled && ! empty($latLng)) {
            $this->saveAddressLatLngToCache($latLng, $placeId);
        }

        return $latLng;
    }

    // list by address
    // from cache or api
    public function autocompleteAddress($address)
    {
        $cacheEnabled = false; //(bool)settings('main_settings.geocoder_cache_enabled');
        if ($cacheEnabled) {
            $addressCollection = $this->autocompleteAddressFromCache($address);
            if (! empty($addressCollection)) {
                return $addressCollection;
            }
        }
        $addressCollection = $this->autocompleteAddressFromApi($address);
        if ($cacheEnabled && ! empty($addressCollection)) {
            $this->saveAutocompleteAddressToCache($addressCollection, $address);
        }

        return $addressCollection;
    }

    private function autocompleteAddressFromCache($address)
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return '';
        }

        $cacheKey = $this->addressToCacheKey($address);
        // Memcached server may be down
        try {
            $server = Cache::store('memcached')->getMemcached();
            $value = $server->get($cacheKey);
            if (! $value) {
                return '';
            }
        } catch (\Exception $e) {
            return '';
        }

        return $value;
    }

    private function saveAutocompleteAddressToCache($addressCollection, $address)
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return false;
        }

        $cacheKey = $this->addressToCacheKey($address);
        $cacheDays = (int) settings('main_settings.geocoder_cache_days'); // How long to keep cache

        $expiration = $cacheDays * 24 * 60 * 60;
        $maxExpiration = 60 * 60 * 24 * 30; // 30 days
        if ($expiration > $maxExpiration) { // memcached limitation
            $expiration = 0; // "unlimited" expiration
        }
        // Memcached server may be down
        try {
            $server = Cache::store('memcached')->getMemcached();
            $server->set($cacheKey, $addressCollection, $expiration);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    // adress from coords
    public function getGeoAddress($latOrig, $lonOrig)
    {
        $lat = $this->normalizeGeoValue($latOrig);
        $lon = $this->normalizeGeoValue($lonOrig);

        // Check first if geo address caching enabled in settings
        $cacheEnabled = (bool) settings('main_settings.geocoder_cache_enabled');
        if ($cacheEnabled) {
            $address = $this->findGeoAddressFromCache($lat, $lon);
            if (! empty($address)) {
                return $address;
            }
        }

        // geoAddress not found in cache - get it from API
        $address = $this->getGeoAddressFromApi($lat, $lon);
        if ($cacheEnabled && ! empty($address)) {
            $this->saveGeoAddressToCache($lat, $lon, $address);
        }

        return $address;
    }

    private function addressToCacheKey($address)
    {
        return md5($address);
    }

    /**
     * Deletes all cache items
     *
     * @return bool
     */
    public function flushAllCache()
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return false;
        }

        try {
            Cache::store('memcached')->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns geocoder cache memory usage in bytes
     *
     * @return int|bool
     */
    public function getCacheMemoryUsed()
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return false;
        }

        try {
            $serversStats = Cache::store('memcached')->getMemcached()->getStats();
            $stats = reset($serversStats);
            $bytesUsed = $stats['bytes'];

            return $bytesUsed;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns memcached server uptime in seconds
     *
     * @return int|bool
     */
    public function getCacheServerUptime()
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return false;
        }

        try {
            $serversStats = Cache::store('memcached')->getMemcached()->getStats();
            $stats = reset($serversStats);
            $uptime = $stats['uptime'];

            return $uptime;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    private function findGeoAddressFromCache($lat, $lon)
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return '';
        }

        $cacheKey = $this->latLonToCacheKey($lat, $lon);
        // Memcached server may be down
        try {
            $server = Cache::store('memcached')->getMemcached();
            $value = $server->get($cacheKey);
            if (! $value) {
                return '';
            }
        } catch (\Exception $e) {
            return '';
        }

        return $value;
    }

    /**
     * Saves geo address to cache
     *
     * @return bool
     */
    private function saveGeoAddressToCache($lat, $lon, $address)
    {
        // Protection if Memcached not installed
        if (! class_exists('Memcached')) {
            return false;
        }

        $cacheKey = $this->latLonToCacheKey($lat, $lon);
        $cacheDays = (int) settings('main_settings.geocoder_cache_days'); // How long to keep cache

        $expiration = $cacheDays * 24 * 60 * 60;
        $maxExpiration = 60 * 60 * 24 * 30; // 30 days
        if ($expiration > $maxExpiration) { // memcached limitation
            $expiration = 0; // "unlimited" expiration
        }
        // Memcached server may be down
        try {
            $server = Cache::store('memcached')->getMemcached();
            $server->set($cacheKey, $address, $expiration);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    private function latLonToCacheKey($lat, $lon)
    {
        $key = $lat.':'.$lon;

        return $key;
    }

    /**
     * Normalizes(rounds fraction part) of latitude|longitude
     *
     * @return int
     */
    private function normalizeGeoValue($value)
    {
        return round($value, 11);
    }
}
