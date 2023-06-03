<?php

namespace Tobuli\Helpers\GeoLocation;

class Location
{
    private $values = [];

    private $attributes = [
        'id',
        'place_id',
        'lat',
        'lng',
        'address',
        'country',
        'country_code',
        'county',
        'state',
        'city',
        'road',
        'house',
        'zip',
        'type',
    ];

    public function __construct($location_attributes = [])
    {
        foreach ($this->attributes as $attribute) {
            if (! array_key_exists($attribute, $location_attributes)) {
                $this->values[$attribute] = null;

                continue;
            }

            $this->values[$attribute] = $location_attributes[$attribute];
        }

        $this->values['id'] = md5(strtolower(
            $this->values['country_code']
            .$this->values['state']
            .$this->values['house']
            .$this->values['zip']
        ));
    }

    public function __get($key)
    {
        return $this->values[$key];
    }

    public function toArray()
    {
        return $this->values;
    }
}
