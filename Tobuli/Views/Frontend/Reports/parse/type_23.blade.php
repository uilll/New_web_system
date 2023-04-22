@extends('Frontend.Reports.parse.layout')

@section('content')
    <?php $line = 0; ?>
    @foreach ($devices as $device)
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ rtl(trans('front.report_type'), $data) }}: {{ rtl($types[$data['type']], $data) }}
            </div>
            <div class="panel-body">
                <table class="table" style="margin-bottom: 0px">
                    <tbody>
                    <?php $line += 1; ?>
                    <tr>
                        <th>{{ rtl(trans('front.plate'), $data) }}:</th>
                        <th>{{ rtl($device['plate_number'], $data) }}</th>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    <?php $line += 1; ?>
                    <tr>
                        <th>{!! rtl(trans('front.time_period'), $data) !!}:</th>
                        <th>{{ $data['date_from'] }} - {{ $data['date_to'] }}</th>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <?php $line += 1; ?>
            <div class="panel-body no-padding">
                <table class="table table-striped table-speed" style="margin-bottom: 0px">
                    <thead>
                    <?php $line += 1; ?>
                    <tr>
                        <th>{{ rtl(trans('front.driver'), $data) }}</th>
                        <th>{{ rtl(trans('front.distance_driver'), $data) }}</th>
                        <th>{{ rtl(trans('front.top_speed'), $data) }}</th>
                        <th>{{ rtl(trans('front.overspeed_duration'), $data) }}</th>
                        <th>{{ rtl(trans('front.overspeed_score'), $data) }}</th>
                        <th>{{ rtl(trans('front.harsh_acceleration_count'), $data) }}</th>
                        <th>{{ rtl(trans('front.harsh_acceleration_score'), $data) }}(/100kms)</th>
                        <th>{{ rtl(trans('front.harsh_braking_count'), $data) }}</th>
                        <th>{{ rtl(trans('front.harsh_braking_score'), $data) }}(/100kms)</th>
                        <th>{{ rtl(trans('front.seatbelt_off_duration'), $data) }}</th>
                        <th>{{ rtl(trans('front.seatbelt_off_score'), $data) }}</th>
                        <th>RAG</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (!isset($items[$device['id']]) || empty($items[$device['id']]))
                        <?php $line += 1; ?>
                        <tr>
                            <td colspan="12">{{ rtl(trans('front.nothing_found_request'), $data) }}</td>
                        </tr>
                    @else
                        <?php
                        $device_items = $items[$device['id']];
                        ?>
                        @foreach ($device_items as $item)
                            <?php $line += 1; ?>
                            <?php
                            $distance = float($item['distance']);

                            $overspeed_score = $item['time'] > 0 && $distance > 0 ? float($item['time']/10/$distance*100) : 0;
                            $harsh_accl_score = $item['ha'] > 0 && $distance > 0 ? float($item['ha']/$distance*100) : 0;
                            $harsh_braking_score = $item['hb'] > 0 && $distance > 0 ? float($item['hb']/$distance*100) : 0;
                            $seatbelt_score = $item['sb0'] > 0 && $distance > 0 ? float($item['sb0']/10/$distance*100) : 0;
                            $rag = $overspeed_score + $harsh_accl_score + $harsh_braking_score + $seatbelt_score;
                            ?>
                            @if ($data['format'] == 'xls')
                                <tr style="background-color: {{ $rag > 3 ? '#FF0000' : ($rag < 1 ? '#00d400' : '#FFFF00') }}; color: #000000;">
                                    <td style="text-align: center;">{{ !empty($item['name']) ? $item['name'] : '-' }}</td>
                                    <td style="text-align: center;">{{ $distance }}</td>
                                    <td style="text-align: center;">{{ $item['top_speed'] }}</td>
                                    <td style="text-align: center;">{{ $item['time'] }}</td>
                                    <td style="text-align: center;">=D{{ $line }}/10/B{{ $line }}*100</td>
                                    <td style="text-align: center;">{{ $item['ha'] }}</td>
                                    <td style="text-align: center;">=F{{ $line }}/B{{ $line }}*100</td>
                                    <td style="text-align: center;">{{ $item['hb'] }}</td>
                                    <td style="text-align: center;">=H{{ $line }}/B{{ $line }}*100</td>
                                    <td style="text-align: center;">{{ $item['sb0'] }}</td>
                                    <td style="text-align: center;">=J{{ $line }}/10/B{{ $line }}*100</td>
                                    <td style="text-align: center;">=E{{ $line }}+G{{ $line }}+I{{ $line }}+K{{ $line }}</td>
                                </tr>
                            @else
                                <tr style="background-color: {{ $rag > 3 ? '#FF0000' : ($rag < 1 ? '#00d400' : '#FFFF00') }}; color: #000000;">
                                    <td style="text-align: center;">{{ !empty($item['name']) ? rtl($item['name'], $data) : '-' }}</td>
                                    <td style="text-align: center;">{{ $distance }}</td>
                                    <td style="text-align: center;">{{ $item['top_speed'] }}</td>
                                    <td style="text-align: center;">{{ $item['time'] }}</td>
                                    <td style="text-align: center;">{{ $overspeed_score }}</td>
                                    <td style="text-align: center;">{{ $item['ha'] }}</td>
                                    <td style="text-align: center;">{{ $harsh_accl_score }}</td>
                                    <td style="text-align: center;">{{ $item['hb'] }}</td>
                                    <td style="text-align: center;">{{ $harsh_braking_score }}</td>
                                    <td style="text-align: center;">{{ $item['sb0'] }}</td>
                                    <td style="text-align: center;">{{ $seatbelt_score }}</td>
                                    <td style="text-align: center;">{{ $rag }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
        <?php $line += 1; ?>
    @endforeach
    <div class="panel panel-default">
        <div class="panel-body no-padding" style="padding: 0px;">
            <table class="table " style="color: #000000; margin-bottom: 0px">
                <tbody>
                <tr>
                    <td style="background-color: #FF0000;">{{ rtl(strtoupper(trans('front.above')), $data) }} 3</td>
                </tr>
                <tr>
                    <td style="background-color: #FFFF00;">{{ rtl(strtoupper(trans('front.between')), $data) }} 1 {{ rtl(strtoupper(trans('front.and')), $data) }} 3</td>
                </tr>
                <tr>
                    <td style="background-color: #00d400;">{{ rtl(strtoupper(trans('front.less_than')), $data) }} 1</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if ($data['format'] != 'xls')
    <div class="panel panel-default">
        <div class="panel-body no-padding" style="padding: 0px;">
            <table class="table" style="table-layout: auto; margin-bottom: 0px;">
                <tbody>
                <tr>
                    <td style="width: 150px;">D</td>
                    <td>{{ rtl(trans('front.distance_driver'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">OD</td>
                    <td>{{ rtl(trans('front.overspeed_duration'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">AC</td>
                    <td>{{ rtl(trans('front.harsh_acceleration_count'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">AS = AC / D * 100</td>
                    <td>{{ rtl(trans('front.harsh_acceleration_score'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">BC</td>
                    <td>{{ rtl(trans('front.harsh_braking_count'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">BS = BC / D * 100</td>
                    <td>{{  rtl(trans('front.harsh_braking_score'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">OS = OD / 10 / D * 100</td>
                    <td>{{ rtl(trans('front.overspeed_score'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">SD</td>
                    <td>{{ rtl(trans('front.seatblet_off_duration'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">SS = SD / 10 / D * 100</td>
                    <td>{{ rtl(trans('front.seatblet_off_score'), $data) }}</td>
                </tr>
                <tr>
                    <td style="width: 150px;">R = OS + AS + BS + SS</td>
                    <td>RAG</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
@stop