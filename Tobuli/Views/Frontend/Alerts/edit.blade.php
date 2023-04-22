@extends('Frontend.Layouts.modal')

@section('modal_class', 'modal-md')

@section('title')
    <i class="icon alerts"></i> {!! trans('global.edit') !!}
@stop

@section('body')
    <ul class="nav nav-tabs nav-default" role="tablist">
        <li class="active"><a href="#alerts-form-user" role="tab" data-toggle="tab">{!!trans('front.devices')!!}</a></li>
        <li><a href="#alerts-form-type" role="tab" data-toggle="tab">{!!trans('validation.attributes.type')!!}</a></li>
        <li><a href="#alerts-form-geofences" role="tab" data-toggle="tab">{!!trans('front.geofencing')!!}</a></li>
        <li><a href="#alerts-form-schedule" role="tab" data-toggle="tab">{!!trans('front.schedule')!!}</a></li>
        <li><a href="#alerts-form-notifications" role="tab" data-toggle="tab">{!!trans('front.notifications')!!}</a></li>
        <li><a href="#alerts-form-command" role="tab" data-toggle="tab">{!!trans('front.command')!!}</a></li>
    </ul>
    
    {!!Form::open(['route' => 'alerts.update', 'method' => 'PUT', 'class' => 'alert-form'])!!}
    {!!Form::hidden('id', $item->id)!!}
        <div class="tab-content">

            <div id="alerts-form-user" class="tab-pane active">

                <div class="form-group">
                    {!!Form::label('name', trans('validation.attributes.name').'*:')!!}
                    {!!Form::text('name', $item->name, ['class' => 'form-control'])!!}
                </div>

                <div class="form-group">
                    {!! Form::label('devices', trans('validation.attributes.devices').'*:') !!}
                    {!! Form::select('devices[]', $devices , $item->devices->lists('id', 'id')->all(), ['class' => 'form-control multiexpand', 'multiple' => 'multiple', 'data-live-search' => true, 'data-actions-box' => true]) !!}
                </div>

            </div>

            <div id="alerts-form-type" class="tab-pane">
                <div class="form-group">
                    {!! Form::label('type', trans('validation.attributes.type').':') !!}
                    {!! Form::select('type', array_pluck($types, 'title', 'type'), $item->type, ['class' => 'form-control']) !!}
                </div>

                @foreach($types as $type)
                    <div class="types type-{{ $type['type'] }}">
                        @if ( ! empty($type['attributes']))
                            @foreach($type['attributes'] as $attribute)
                                <div class="form-group">
                                    {!!Form::label($attribute['name'], $attribute['title'])!!}
                                    @if ($type['type'] == 'custom' && $attribute['type'] == 'multiselect')
                                        {!! Form::select($attribute['name'].'[]', array_pluck($attribute['options'], 'items', 'name'), $attribute['default'], ['class' => 'form-control multiexpand half', 'multiple' => 'multiple', 'data-live-search' => true, 'data-actions-box' => true]) !!}
                                    @elseif ($attribute['type'] == 'multiselect')
                                        {!! Form::select($attribute['name'].'[]', array_pluck($attribute['options'], 'title', 'id'), $attribute['default'], ['class' => 'form-control multiexpand', 'multiple' => 'multiple', 'data-live-search' => true, 'data-actions-box' => true]) !!}
                                    @elseif ($attribute['type'] == 'select')
                                        {!! Form::select($attribute['name'], array_pluck($attribute['options'], 'title', 'id'), $attribute['default'], ['class' => 'form-control', 'data-live-search' => true]) !!}
                                    @else
                                        {!! Form::text($attribute['name'], $attribute['default'], ['class' => 'form-control']) !!}
                                    @endif

                                    @if ( ! empty($attribute['description']))
                                        <div>
                                            {!! $attribute['description'] !!}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>

            <div id="alerts-form-geofences" class="tab-pane">
                @if (!empty($geofences))
                    <div class="form-group">
                        {!! Form::hidden('zone', 0) !!}
                        <div class="checkbox-inline">
                            {!! Form::checkbox('zone', 1, $item->zone == 1) !!}
                            {!! Form::label(null, "Dentro") !!}
                        </div>
                        <div class="checkbox-inline">
                            {!! Form::checkbox('zone', 2, $item->zone == 2) !!}
                            {!! Form::label(null, "Fora") !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!!Form::select('zones[]', $geofences, $item->zones->lists('id', 'id')->all(), ['class' => 'form-control multiexpand disabled', 'multiple' => 'multiple', 'data-live-search' => true, 'data-actions-box' => true])!!}
                    </div>
                @else
                    <div class="alert alert-warning" role="alert">{!!trans('front.no_geofences')!!}</div>
                @endif
            </div>

            <div id="alerts-form-schedule" class="tab-pane">
                <div class="form-group">
                    {!! Form::hidden('schedule', 0) !!}
                    <div class="checkbox">
                        {!! Form::checkbox('schedule', 1, $item->schedule) !!}
                        {!! Form::label(null, trans('validation.attributes.schedule')) !!}
                    </div>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-weektime" id="weektime-selectarea">
                        <thead>
                        <tr>
                            <th></th>
                            <?php $chunks = array_chunk(config('tobuli.history_time'), 12);?>
                            @foreach($chunks as $chunk)
                                <th colspan="{{ count($chunk) }}" class="text-center">{{ reset($chunk) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedules as $schedule)
                            <tr>
                                <th>
                                    <a href="javascript:" class="btn btn-sm btn-action" onClick="app.alerts.draggerSet('{{ $schedule['id'] }}');" title="{{ $schedule['title'] }}">
                                        {{ utf8_strtoupper( utf8_substr($schedule['title'], 0,1) ) }}
                                    </a>
                                </th>
                                @foreach($schedule['items'] as $index => $time)
                                    <td
                                            title="{{ $time['title'] }}"
                                            data-day="{{ $schedule['id'] }}"
                                            data-index="{{ $index }}"
                                            class="item {{ ($index % 4) == 0 ? 'hour ' : '' }}{{ ($index % 12) == 0 ? 'quarter ' : '' }}">
                                        {!! Form::checkbox("schedules[{$schedule['id']}][]", $time['id'], $time['active'], ['class' => 'hidden']) !!}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="form-group text-right">
                    <a href="javascript:" class="btn btn-sm btn-action" onClick="app.alerts.draggerSet('workdays');">{{ trans('global.workdays') }}</a>
                    <a href="javascript:" class="btn btn-sm btn-action" onClick="app.alerts.draggerSet('weekend');">{{ trans('global.weekend') }}</a>
                    <a href="javascript:" class="btn btn-sm btn-action" onClick="app.alerts.draggerSet('always');">{{ trans('global.always') }}</a>
                </div>
            </div>

            <div id="alerts-form-notifications" class="tab-pane form-horizontal">
                @foreach($notifications as $notification)
                    <div class="form-group">
                        <div class="col-xs-1">
                            <div class="checkbox">
                                {!! Form::checkbox('notifications['.$notification['name'].'][active]', 1, array_get($item->notifications, "{$notification['name']}.active")) !!}
                                {!! Form::label(null, null) !!}
                            </div>
                        </div>
                        <div class="col-xs-11">
                            {!! Form::label(null, $notification['title']) !!}
                            @if (array_has($notification, 'input'))
                                {!! Form::text('notifications['.$notification['name'].'][input]', array_get($item->notifications, "{$notification['name']}.input"), ['class' => 'form-control']) !!}
                            @endif
                            @if (array_has($notification, 'description'))
                                <small>{!! $notification['description'] !!}</small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="alerts-form-command" class="tab-pane">

                <div class="form-group">
                    {!! Form::hidden('command[active]', 0) !!}
                    <div class="checkbox">
                        {!! Form::checkbox('command[active]', 1, array_get($item->command, 'active')) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('command[type]', trans('validation.attributes.type').':') !!}
                    {!! Form::select('command[type]', [], array_get($item->command, 'type'), ['class' => 'form-control', 'data-live-search' => true]) !!}
                </div>

                <div class="row command_attributes"></div>
            </div>
        </div>
    {!!Form::close()!!}
    <script>
        $(document).ready(function() {
            app.alerts.draggerInt();
            app.alerts.draggerSet('checked');
            app.alerts.setCommandData({!! json_encode($item->command) !!});

            $('.alert-form select[name="type"]').trigger('change');
            $('.alert-form input[name="schedule"]').trigger('change');
            $('.alert-form select[name="devices[]"]').trigger('change');
        });
    </script>
@stop

@section('buttons')
    <button type="button" class="btn btn-action update">{!!trans('global.save')!!}</button>
    <button class="btn btn-default" data-target="#deleteAlert" data-toggle="modal">{!!trans('global.delete')!!}</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('global.cancel')!!}</button>
@stop