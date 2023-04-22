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
                        <th>{{ rtl(trans('front.transporter'), $data) }}</th>
                        <th>{{ rtl(trans('front.consignor_name'), $data) }}</th>
                        <th>{{ rtl(trans('front.consignee_name'), $data) }}</th>
                        <th>{{ rtl(trans('front.consignee_destination'), $data) }}</th>
                        <th>{{ rtl(trans('front.datetime_dispatch'), $data) }}</th>
                        <th>{{ rtl(trans('front.datetime_unloading'), $data) }}</th>
                        <th>{{ rtl(trans('front.duration'), $data) }}</th>
                        <th>{{ rtl(trans('front.move_duration'), $data) }}</th>
                        <th>{{ rtl(trans('front.loading_location'), $data) }}</th>
                        <th>{{ rtl(trans('front.unloading_location'), $data) }}</th>
                        <th>{{ rtl(trans('global.distance'), $data) }}</th>
                        <th>{{ rtl(trans('front.current_datetime'), $data) }}</th>
                        <th>{{ rtl(trans('front.current_location'), $data) }}</th>
                        <th>{{ rtl(trans('front.stop_duration'), $data) }}</th>
                        <th>{{ rtl(trans('front.remark'), $data) }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($items as $item)
                        @if(empty($item['journeys']))
                        <tr>
                            <td colspan="2">{{ $item['device']['name'] }}</td>								
                            <td colspan="13">{{ rtl(trans('front.nothing_found_request'), $data) }}</td>
                        </tr>
                        @else
                            @foreach ($item['journeys'] as $journey)
                            <tr>
                                <td>{{ $item['device']['plate'] }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ !empty($journey['begin']['time']) ? $journey['begin']['time'] : '' }}</td>
                                <td>{{ !empty($journey['end']['time']) ? $journey['end']['time'] : '' }}</td>
                                <td>{{ !empty($journey['duration']) ? $journey['duration'] : '' }}</td>
                                <td>{{ !empty($journey['move_duration']) ? $journey['move_duration'] : '' }}</td>
                                <td>{{ !empty($journey['begin']['address']) ? $journey['begin']['address'] : '' }}</td>
                                <td>{{ !empty($journey['end']['address']) ? $journey['end']['address'] : '' }}</td>
                                <td>{{ !empty($journey['distance']) ? $journey['distance'] : '' }}</td>
                                <td>{{ $item['device']['time'] }}</td>
                                <td>{{ $item['device']['address'] }}</td>
                                <td>{{ !empty($journey['stop_duration']) ? $journey['stop_duration'] : '' }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@stop