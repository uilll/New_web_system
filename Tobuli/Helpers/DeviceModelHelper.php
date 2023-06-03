<?php

namespace Tobuli\Helpers;

use Illuminate\Support\Facades\File;

class DeviceModelHelper
{
    private $url = 'http://www.gpswox.com/get_devices';

    private $text_file = 'storage/device_models_json.txt';

    private $arr = [];

    public function __construct()
    {
        $this->text_file = app_path($this->text_file);
    }

    public function get($port = false)
    {
        $resp = $this->curl();

        $this->arr = json_decode($resp, true);
        $fails = 0;
        while (empty($this->arr) || ! is_array($this->arr)) {
            $resp = null;

            if ($fails >= 5) {
                break;
            }

            if (File::exists($this->text_file)) {
                $this->arr = json_decode(File::get($this->text_file), true);
            } else {
                $resp = $this->curl();
                $this->arr = json_decode($resp, true);
            }

            $fails++;
        }

        if ($fails >= 5) {
            return [];
        }

        if (! empty($resp)) {
            File::put($this->text_file, $resp);
        }

        if (! $port) {
            $this->prepareArray();
        } else {
            $this->prepareArrayPort();
        }

        return $this->arr;
    }

    private function prepareArray()
    {
        $new_arr = [];
        foreach ($this->arr as $item) {
            $new_arr[$item['id']] = $item['title'].' ('.$item['port'].')';
        }

        $this->arr = $new_arr;
        unset($new_arr);
    }

    private function prepareArrayPort()
    {
        $new_arr = [];
        foreach ($this->arr as $item) {
            $new_arr[$item['id']] = ['title' => $item['title'], 'port' => $item['port']];
        }

        $this->arr = $new_arr;
        unset($new_arr);
    }

    private function curl()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url,
            CURLOPT_TIMEOUT => 10,
        ]);

        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;
    }
}
