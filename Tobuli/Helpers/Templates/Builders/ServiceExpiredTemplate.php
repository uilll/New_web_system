<?php

namespace Tobuli\Helpers\Templates\Builders;


use Tobuli\Helpers\Templates\TemplateBuilder;

class ServiceExpiredTemplate extends TemplateBuilder
{
    /**
     * @param $item
     * @return array
     */
    protected function getReplaceVariables($item)
    {
        return [
            '[device]'  => htmlentities($item->device_name),
            '[service]' => htmlentities($item->name)
        ];
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return [
            '[device]'   => 'Device name',
            '[service]'  => 'Service name',
        ];
    }
}