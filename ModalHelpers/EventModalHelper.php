<?php

namespace ModalHelpers;

use Facades\Repositories\EventRepo;

class EventModalHelper extends ModalHelper
{
    public function search($search, $device_id = null)
    {
        $this->checkException('events', 'view');

        $events = EventRepo::whereUserIdWithAttributes($this->user->id, $search, $device_id);

        foreach ($events as &$event) {
            $event->time = tdate($event->time, $this->user->zone);

            $event->speed = round($this->user->unit_of_distance == 'mi' ? kilometersToMiles($event->speed) : $event->speed);
            $event->altitude = round($this->user->unit_of_altitude == 'ft' ? metersToFeets($event->altitude) : $event->altitude);
        }

        if ($this->api) {
            $events = $events->toArray();

            foreach ($events['data'] as &$event) {
                if (! empty($event['geofence'])) {
                    unset($event['geofence']);
                }
            }
            $events['url'] = route('api.get_events');
        }

        return $events;
    }

    public function destroy()
    {
        $this->checkException('events', 'clean');

        EventRepo::deleteWhere(['user_id' => $this->user->id, 'deleted' => 0]);
    }
}
