<?php

namespace App\Console;

use Illuminate\Support\Facades\Redis;

class PositionsKeys
{
    const PREFIX = 'position';

    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function add($data)
    {
        $this->redis->set(self::PREFIX.'.'.$data['fixTime'].'.'.$data['imei'], json_encode($data));
    }

    public function getImeis()
    {
        $keys = $this->getKeys();

        $imeis = array_map(function ($key) {
            [$_prefix, $_time, $_imei] = explode('.', $key, 3);

            return $_imei;
        }, $keys);

        return array_unique($imeis);
    }

    public function getKeys()
    {
        return $this->redis->keys(self::PREFIX.'.*');
    }

    public function count($imei = null)
    {
        if (is_null($imei)) {
            return $this->countAll();
        }

        return $this->countImeiKeys($imei);
    }

    public function getImeiKeys($imei)
    {
        $keys = $this->redis->keys(self::PREFIX.'.*.'.$imei);

        asort($keys);

        return $keys;
    }

    public function getKeyData($key, $remove = true)
    {
        $data = $this->redis->get($key);

        if ($remove) {
            $this->redis->del($key);
        }

        if (! $data) {
            return null;
        }

        $data = json_decode($data, true);

        if (! empty($data['deviceId'])) {
            $data['imei'] = $data['deviceId'];
        }

        if (! empty($data['uniqueId'])) {
            $data['imei'] = $data['uniqueId'];
        }

        if (empty($data['imei'])) {
            return false;
        }

        $data['protocol'] = isset($data['protocol']) ? $data['protocol'] : null;

        return $data;
    }

    public function countImeiKeys($imei)
    {
        $keys = $this->getImeiKeys($imei);

        return $keys ? count($keys) : 0;
    }

    public function countAll()
    {
        $keys = $this->getKeys();

        return $keys ? count($keys) : 0;
    }
}
