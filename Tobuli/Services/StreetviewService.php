<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.4.4
 * Time: 12.16
 */

namespace Tobuli\Services;

use Illuminate\Support\Facades\Cache;

class StreetviewService
{
    const DEFAULT_RADIUS = 100;

    const CBK_URL = 'http://maps.google.com/cbk?output=json&v=4&dm=0&pm=0&ll=';

    const STREETVIEW_URL = 'https://maps.googleapis.com/maps/api/streetview?pano=';

    private $metaData;

    private $panoId;

    private $gKey;

    public function __construct()
    {
        $this->gKey = config('services.streetview.key');
    }

    public function getMetaData($location, $radius = self::DEFAULT_RADIUS)
    {
        $ch = curl_init(self::CBK_URL.$location.'&radius='.$radius.'&key='.$this->gKey);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        curl_close($ch);

        $this->metaData = json_decode($response);

        return $this->metaData;
    }

    public function getPanoId($location, $radius = self::DEFAULT_RADIUS)
    {
        $this->panoId = Cache::get(md5($location));

        if ($this->panoId) {
            return $this->panoId;
        }

        if (! $this->metaData) {
            $this->getMetaData($location, $radius);
        }

        if (isset($this->metaData->Location)) {
            $this->panoId = $this->metaData->Location->panoId;
            Cache::put(md5($location), $this->panoId, 60 * 24 * 7);

            return $this->panoId;
        }

        return false;
    }

    public function getImage($location, $size, $heading, $radius = self::DEFAULT_RADIUS)
    {
        if (! $this->gKey) {
            $image = file_get_contents("http://5.189.140.114/index2.php?size=$size&location=$location&heading=$heading&pitch=-0.76&radius=$radius");

            return $image;
        }

        if (! $this->panoId) {
            $this->getPanoId($location, $radius);
        }

        $ch = curl_init(self::STREETVIEW_URL.$this->panoId.'&size='.$size.'&heading='.$heading.'&pitch=-0.76&key='.$this->gKey);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $image = curl_exec($ch);

        curl_close($ch);

        return $image;
    }
}
