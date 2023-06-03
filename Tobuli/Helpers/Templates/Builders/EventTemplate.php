<?php

namespace Tobuli\Helpers\Templates\Builders;

use Tobuli\Helpers\Templates\TemplateBuilder;

class EventTemplate extends TemplateBuilder
{
    /**
     * @return array
     */
    protected function getReplaceVariables($item)
    {
        $google_preview_link = 'http://maps.google.com/maps?q='.$item->data['latitude'].','.$item->data['longitude'].'&t=m&hl='.$item->lang;

        return [
            '[event]' => $item->event_message,
            '[geofence]' => (isset($item->data['geofence']) ? $item->data['geofence'] : ''),
            '[device]' => $item->data['device_name'],
            '[address]' => getGeoAddress($item->data['latitude'], $item->data['longitude']),
            '[position]' => $item->data['latitude'].'&deg;, '.$item->data['longitude'].'&deg;',
            '[lat]' => $item->data['latitude'],
            '[lon]' => $item->data['longitude'],
            '[heading]' => $item->data['course'],
            '[preview]' => '<a href="'.$google_preview_link.'">'.trans('front.preview').'</a>',
            '[altitude]' => $item->user->unit_of_altitude == 'ft' ? round(metersToFeets($item->data['altitude'])).' '.trans('front.ft') : round($item->data['altitude']).' '.trans('front.mt'),
            '[speed]' => $item->user->unit_of_distance == 'mi' ? round(kilometersToMiles($item->data['speed'])).' '.trans('front.dis_h_mi') : round($item->data['speed']).' '.trans('front.dis_h_km'),
            '[time]' => datetime($item->data['time']),
        ];
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return [
            '[event]' => 'Event title',
            '[geofence]' => 'Geofence name',
            '[device]' => 'Device name',
            '[address]' => 'Address',
            '[position]' => 'Position/Point',
            '[lat]' => 'Latitude',
            '[lon]' => 'Longitude',
            '[heading]' => 'Heading/Course',
            '[preview]' => 'Google map link',
            '[altitude]' => 'Altitude',
            '[speed]' => 'Speed',
            '[time]' => 'Time',
        ];
    }
}
