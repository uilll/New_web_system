@extends('Frontend.Reports.parse.layout')

@section('content')
<div class="panel panel-default">
    <div class="panel-heading">
        <table class="table">
            <tr>
                <th>
                    {{ rtl(trans('front.report_type'), $data) }}: {{ rtl($types[$data['type']], $data) }}
                </th>
                <th>{{ $data['date_from'] }} - {{ $data['date_to'] }}</th>
            </tr>
        </table>
    </div>
    <div class="panel-body no-padding">
        <table class="table table-striped table-speed" style="margin-bottom: 0px">
            <thead>
            <tr>
                <th>{{ rtl(trans('front.plate'), $data) }}</th>
                <th>{{ rtl(trans('validation.attributes.date'), $data) }}</th>
                <th>{{ rtl(trans('front.from'), $data) }} ({{ rtl(trans('front.hour_short'), $data) }})</th>
                <th>{{ rtl(trans('front.to'), $data) }} ({{ rtl(trans('front.hour_short'), $data) }})</th>
                <th>{{ rtl(trans('front.difference'), $data) }} ({{ rtl(trans('front.hour_short'), $data) }})</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($items as $device_id => $values)
                @if(empty($values))
                <tr>
                    <td>{{ $devices[$device_id]['plate_number'] }}</td>
                    <td colspan="4">{{ rtl(trans('front.nothing_found_request'), $data) }}</td>
                </tr>
                @else
                    @foreach ($values as $item)
                    <tr>
                        <td>{{ $devices[$device_id]['plate_number'] }}</td>
                        <td>{{ $item['date'] }}</td>
                        <td>{{ $item['from'] }}</td>
                        <td>{{ $item['to'] }}</td>
                        <td>{{ $item['diff'] }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
            <tr>
                <td>{{ rtl(trans('front.total'), $data) }} ({{ count($devices) }})</td>
                <td></td>
                <td></td>
                <td></td>
                <?php
                    $total = 0;
                    foreach ($items as $device_id => $values) {
                        if (empty($values))
                            continue;
                        
                        $total += array_sum(array_pluck($values, 'diff'));
                    }

                ?>
                <td>{{ round($total, 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
@stop