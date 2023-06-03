<?php

namespace Tobuli\Helpers\Templates\Builders;

use Tobuli\Helpers\Templates\TemplateBuilder;

class ServiceExpirationTemplate extends TemplateBuilder
{
    /**
     * @return array
     */
    protected function getReplaceVariables($item)
    {
        return [
            '[device]' => htmlentities($item->device_name),
            '[service]' => htmlentities($item->name),
            '[left]' => $item->trigger_event_left.' '.($item->expiration_by == 'days' ? 'd.' : $item->unit_of_measurement),
        ];
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return [
            '[device]' => 'Device name',
            '[service]' => 'Service name',
            '[left]' => 'Left quantity',
        ];
    }
}
