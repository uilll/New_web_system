@extends('Frontend.Reports.parse.layout')

@section('content')
    @foreach ($devices as $device)
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ rtl(trans('front.report_type'), $data) }}: {{ rtl($types[$data['type']], $data) }}
            </div>
            <div class="panel-body">
                <table class="table" style="margin-bottom: 0px">
                    <tbody>
                    <tr>
                        <td><strong>{{ rtl(trans('front.plate'), $data) }}:</strong></td>
                        <td>{{ rtl($device['plate_number'], $data) }}</td>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th>{!! rtl(trans('front.time_period'), $data) !!}:</th>
                        <td>{{ $data['date_from'] }} - {{ $data['date_to'] }}</td>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="panel-body no-padding">
                <table class="table table-striped table-speed" style="margin-bottom: 0px">
                    <thead>
                        <tr>
                            <th>{{ rtl(trans('front.zone_in'), $data) }}</th>
                            <th>{{ rtl(trans('front.zone_out'), $data) }}</th>
                            <th>{{ rtl(trans('front.duration'), $data) }}</th>
                            <th>{{ rtl(trans('global.distance'), $data) }}</th>
                            <th>{{ rtl(trans('validation.attributes.geofence_name'), $data) }}</th>
                            <th>{{ rtl(trans('front.position'), $data) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (empty($items[$device['id']]))
                            <tr>
                                <td colspan="20">{{ rtl(trans('front.nothing_found_request'), $data) }}</td>
                            </tr>
                        @else
                            <?php $item_total_distance = 0; ?>
                            @foreach ($items[$device['id']] as $item)
                                <?php $item_total_distance += $item['distance']; ?>
                                <tr>
                                    <td>{{ $item['start'] }}</td>
                                    <td>{{ $item['end'] }}</td>
                                    <td>{{ $item['duration'] }}</td>
                                    <td>
                                        {{ $data['unit_of_distance'] == 'mi' ? kilometersToMiles(round($item['distance'], 2)) : round($item['distance'], 2) }} {{ rtl(trans("front.{$data['unit_of_distance']}"), $data) }}
                                    </td>
                                    <td>{{ rtl($item['name'], $data) }}</td>
                                    <td><a href="http://maps.google.com/maps?q={{ $item['position']['lat'] }},{{ $item['position']['lng'] }}&t=m" target="_blank">{{ $item['position']['lat'] }} &deg;, {{ $item['position']['lng'] }} &deg;</a>
                                        @if(!empty($item['position']['address']))
                                            - {{ rtl($item['position']['address'], $data) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                    {{ $data['unit_of_distance'] == 'mi' ? kilometersToMiles(round($item_total_distance, 2)) : round($item_total_distance, 2) }} {{ rtl(trans("front.{$data['unit_of_distance']}"), $data) }}
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@stop