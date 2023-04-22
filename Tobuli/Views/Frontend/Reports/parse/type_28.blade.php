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
                        <th>{{ rtl(trans('validation.attributes.geofence_name'), $data) }}</th>
                        <th>{{ rtl(trans('front.shift_time'), $data) }}</th>
                        <th>{{ rtl(trans('front.late_entry'), $data) }}</th>
                        <th>{{ rtl(trans('front.late_exit'), $data) }}</th>
                        <th>{{ rtl(trans('validation.attributes.excessive_exit'), $data) }}</th>
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
                                <td>{{ $item['geofence'] }}</td>
                                <td>{{ $item['shift'] }}</td>
                                <td>{{ $item['first_in'] }}</td>
                                <td>{{ $item['last_out'] }}</td>
                                <td>{{ $item['count_out'] }}</td>
                            </tr>
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@stop