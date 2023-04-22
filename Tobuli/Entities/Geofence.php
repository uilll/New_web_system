<?php namespace Tobuli\Entities;

use Eloquent;

use Tobuli\Helpers\PolygonHelper;

class Geofence extends Eloquent {
	protected $table = 'geofences';

    protected $fillable = array('user_id', 'group_id', 'name', 'active', 'polygon_color');

    protected $hidden = array('polygon');

    protected $polygonHelpers = [];

    public function user() {
        return $this->belongsTo('Tobuli\Entities\User', 'user_id', 'id');
    }

    public function getGroupIdAttribute($value)
    {
        if (is_null($value))
            return 0;

        return $value;
    }

    public function setGroupIdAttribute($value)
    {
        if (empty($value))
            $value = null;

        $this->attributes['group_id'] = $value;
    }

    public function pointIn($data)
    {
        if (is_string($data))
        {
            $point = $data;
        }
        elseif (is_object($data))
        {
            $point = $data->latitude . ' ' . $data->longitude;
        }
        elseif (is_array($data))
        {
            $point = $data['latitude'] . ' ' . $data['longitude'];
        }
        else
        {
            return null;
        }

        return $this->pointInPolygon($point);
    }

    public function pointOut($data)
    {
        return ! $this->pointIn($data);
    }

    private function pointInPolygon($point)
    {
        if ( ! isset($this->polygonHelpers[$this->id]))
        {
            $this->polygonHelpers[$this->id] = new PolygonHelper( parsePolygon(json_decode($this->coordinates, TRUE)) );
        }

        return false !== $this->polygonHelpers[$this->id]->pointInPolygon($point);
    }
}
