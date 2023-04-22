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
                        <th>{{ rtl(trans('front.ignition_on_off'), $data) }}</th>
                        <th>{{ rtl(trans('front.time'), $data) }}</th>
                        <th>{{ rtl(trans('front.average_speed'), $data) }}</th>
                        <th>{{ rtl(trans('front.route_length'), $data) }}</th>
                        <th>{{ rtl(trans('front.engine_work'), $data) }}</th>
                        <th>{{ rtl(trans('front.engine_idle'), $data) }}</th>
                        <th>{{ rtl(trans('front.driver'), $data) }}</th>
                        <th>{{ rtl(trans('front.position'), $data) }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (!isset($items[$device['id']]) || empty($items[$device['id']]))
                        <tr>
                            <td colspan="20">{{ rtl(trans('front.nothing_found_request'), $data) }}</td>
                        </tr>
                    @else
                        <?php
                            reset($items[$device['id']]);
                            current($items[$device['id']]);
                            $last_date = current($items[$device['id']])['date'];
                        ?>
                        @foreach ($items[$device['id']] as $item)
                            @if($last_date != $item['date'])
                                <tr>
                                    <td><strong>{{ $last_date = $item['date'] }}</strong></td>
                                    <td colspan="7"></td>
                                </tr>
                            @endif

                            @if(isset($item['duration_engine_on']))
                                <tr>
                                    <td>{{ rtl(trans('front.on'), $data) }}</td>
                                    <td>{{ $item['time'] }}</td>
                                    <td>{{ $item['speed'] }} {{ rtl(trans("front.dis_h_{$item['unit_of_distance']}"), $data) }}</td>
                                    <td>
                                        {{ $item['unit_of_distance'] == 'mi' ? kilometersToMiles($item['distance']) : $item['distance'] }}
                                        {{ rtl(trans('front.'.$item['unit_of_distance']), $data) }}
                                    </td>
                                    <td>{{ secondsToTime($item['duration_engine_on']) }}</td>
                                    <td></td>
                                    <td>{{ $item['driver'] }}</td>
                                    <td>{{ $item['position'] }}</td>
                                </tr>
                            @elseif(isset($item['duration_engine_off']))
                                <tr>
                                    <td>{{ rtl(trans('front.off'), $data) }}</td>
                                    <td>{{ $item['time'] }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ secondsToTime($item['duration_engine_off']) }}</td>
                                    <td>{{ $item['driver'] }}</td>
                                    <td>{{ $item['position'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@stop