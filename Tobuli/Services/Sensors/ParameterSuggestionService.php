<?php

namespace Tobuli\Services\Sensors;

use Tobuli\Entities\Device;

class ParameterSuggestionService
{
    public function suggest($selected_parameter, $device_id)
    {
        $values = $this->parameterValues($selected_parameter, $device_id);

        if ( ! count($values))
            return trans('front.none');

        return $this->formatSuggestion($values);
    }

    private function parameterValues($parameter, $device_id)
    {
        $positions = Device::where('id', $device_id)
            ->first()
            ->positions()
            ->take(200)
            ->get();

        if (!count($positions))
            return null;

        return $this->valuesFromEveryPosition($positions, $parameter);
    }

    private function valuesFromEveryPosition($positions, $parameter)
    {
        $values = [];

        foreach ($positions as $position) {
            $value = $position->getParameter($parameter);

            if (empty($value))
                continue;

            if (in_array($value, $values))
                continue;

            $values[] = $value;
        }

        return array_filter($values);
    }

    private function formatSuggestion($values)
    {
        sort($values);

        $limit = 15;

        if (count($values) > $limit)
            return implode(", ", array_slice($values, 0, $limit)) . ' ...';

        return implode(", ", $values);
    }
}