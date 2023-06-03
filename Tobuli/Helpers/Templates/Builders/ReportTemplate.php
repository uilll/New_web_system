<?php

namespace Tobuli\Helpers\Templates\Builders;

use Tobuli\Helpers\Templates\TemplateBuilder;

class ReportTemplate extends TemplateBuilder
{
    protected function getReplaceVariables($item)
    {
        return [
            '[name]' => $item['title'],
            '[period]' => $item['date_from'].' - '.$item['date_to'],
        ];
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return [
            '[name]' => 'Report title',
            '[period]' => 'Report date range',
        ];
    }
}
