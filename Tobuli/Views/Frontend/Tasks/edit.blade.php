@extends('Frontend.Layouts.modal')

@section('title', trans('global.edit'))

@section('body')

    {!!Form::open(['route' => 'tasks.update', 'method' => 'PUT', 'class' => 'task-form'])!!}
    {!!Form::hidden('id', $item->id)!!}

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                {!!Form::label('device_id', trans('validation.attributes.device_id').':')!!}
                {!!Form::select('device_id', $devices, $item->device_id, ['class' => 'form-control',  'data-live-search' => true])!!}
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!!Form::label('title', trans('validation.attributes.title').':')!!}
                {!!Form::text('title',  $item->title, ['class' => 'form-control'])!!}
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!!Form::label('status', trans('validation.attributes.status').':')!!}
                {!!Form::select('status', $statuses, $item->status, ['class' => 'form-control'])!!}
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!!Form::label('priority', trans('validation.attributes.priority').':')!!}
                {!!Form::select('priority', $priorities, $item->priority, ['class' => 'form-control'])!!}
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('pickup_address', trans('validation.attributes.pickup_address').':')!!}
                {!! Form::hidden('pickup_address_id') !!}
                {!! Form::hidden('pickup_address_lat', $item->pickup_address_lat) !!}
                {!! Form::hidden('pickup_address_lng', $item->pickup_address_lng) !!}
                {!! Form::select('pickup_address',[ $item->pickup_address => $item->pickup_address ],  $item->pickup_address, ['class' => 'form-control selectpicker with-ajax', 'data-live-search' => true, 'data-icon' => 'icon address'])!!}
            </div>
            <div class=" form-horizontal">
                <div class="form-group">
                    {!! Form::label('pickup_time_from', trans('global.from'), ['class' => 'col-xs-3 control-label'])!!}
                    <div class="col-xs-9">
                        <div class="input-group">
                            <div class="has-feedback">
                                <i class="icon calendar form-control-feedback"></i>
                                {!!Form::text('pickup_time_from', $item->pickup_time_from, ['class' => 'datetimepicker form-control'])!!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('pickup_time_to', trans('global.to'), ['class' => 'col-xs-3 control-label'])!!}
                    <div class="col-xs-9">
                        <div class="input-group">
                            <div class="has-feedback">
                                <i class="icon calendar form-control-feedback"></i>
                                {!!Form::text('pickup_time_to', $item->pickup_time_to, ['class' => 'datetimepicker form-control'])!!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('delivery_address', trans('validation.attributes.delivery_address').':')!!}
                {!! Form::hidden('delivery_address_id') !!}
                {!! Form::hidden('delivery_address_lat',  $item->delivery_address_lat) !!}
                {!! Form::hidden('delivery_address_lng',  $item->delivery_address_lng) !!}
                {!! Form::select('delivery_address',[$item->delivery_address => $item->delivery_address],  $item->delivery_address, ['class' => 'form-control selectpicker with-ajax', 'data-live-search' => true, 'data-icon' => 'icon address'])!!}
            </div>
            <div class=" form-horizontal">
                <div class="form-group">
                    {!! Form::label('delivery_time_from', trans('global.from'), ['class' => 'col-xs-3 control-label'])!!}
                    <div class="col-xs-9">
                        <div class="input-group">
                            <div class="has-feedback">
                                <i class="icon calendar form-control-feedback"></i>
                                {!!Form::text('delivery_time_from', $item->delivery_time_from, ['class' => 'datetimepicker form-control'])!!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('delivery_time_to', trans('global.to'), ['class' => 'col-xs-3 control-label'])!!}
                    <div class="col-xs-9">
                        <div class="input-group">
                            <div class="has-feedback">
                                <i class="icon calendar form-control-feedback"></i>
                                {!!Form::text('delivery_time_to', $item->delivery_time_to, ['class' => 'datetimepicker form-control'])!!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="form-group">
        {!!Form::label('comment', trans('front.comment').':')!!}
        {!!Form::textarea('comment',  $item->comment, ['class' => 'form-control'])!!}
    </div>

    {!!Form::close()!!}
    <script>
        $(".selectpicker")
            .selectpicker()
            .filter(".with-ajax")
            .ajaxSelectPicker(options);

        //$("select").trigger("change");
    </script>
@stop

@section('buttons')
    <button type="button" class="btn btn-action update">{!!trans('global.save')!!}</button>
    <button class="btn btn-default" data-target="#deleteTask" data-toggle="modal">{!!trans('global.delete')!!}</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('global.cancel')!!}</button>
@stop