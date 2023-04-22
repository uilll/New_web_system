<?php

namespace App\Console;


use Illuminate\Support\Facades\Redis;

class PositionsStack
{
    const PREFIX = 'positions';

    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function add($data)
    {
        $this->redis->lPush(self::PREFIX . '.' . $data['imei'], json_encode($data));
    }

    public function getImeis()
    {
        $keys = $this->getKeys();

        $imeis = array_map(function($key) { return  str_replace(self::PREFIX . '.','', $key); }, $keys);

        shuffle($imeis);

        return $imeis;
    }

    public function getKeys()
    {
        return $this->redis->keys(self::PREFIX . '.*');
    }

    public function count($imei = null)
    {
        if (is_null($imei))
            return $this->allCount();

        return $this->oneCount(self::PREFIX . '.' . $imei);
    }

    public function getData($imei, $remove = true)
    {
        return $this->getKeyData(self::PREFIX . '.' . $imei, $remove);
    }

    public function getKeyData($key, $remove = true)
    {
        if ($remove)
            $data = $this->redis->rPop($key);
        else
            $data = $this->redis->lIndex($key, -1);

        if ( ! $data)
            return null;

        $data = json_decode($data, true);

        if ( ! empty($data['deviceId']))
            $data['imei'] = $data['deviceId'];

        if ( ! empty($data['uniqueId']))
            $data['imei'] = $data['uniqueId'];

        if (empty($data['imei']))
            return false;

        $data['protocol'] = isset($data['protocol']) ? $data['protocol'] : null;

        return $data;
    }

    public function oneCount($key)
    {
        return $this->redis->lLen($key);
    }

    public function allCount() {
        $count = 0;

        $keys = $this->getKeys();

        if ( ! $keys)
            return $count;

        foreach ($keys as $key)
            $count += $this->oneCount($key);

        return $count;
    }

    public function deleteImei($imei)
    {
        $this->redis->del(self::PREFIX . '.' . $imei);
    }
}