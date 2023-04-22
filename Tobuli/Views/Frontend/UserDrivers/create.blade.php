@extends('Frontend.Layouts.modal_drivers_creator')

@section('title')
    {!!trans('front.add_driver')!!}
@stop

@section('body')
	<?php //echo 'console.log('. json_encode( $devices ) .')';?>
    {!!Form::open(['route' => 'user_drivers.store', 'method' => 'POST'])!!}
        {!!Form::hidden('id')!!}
        <div class="form-group">
            <div class="checkbox">
                {!! Form::checkbox('current', 1, 1) !!}
                {!! Form::label(null, trans('front.set_as_current')) !!}
            </div>
        </div>
        <div class="form-group">
            {!!Form::label('name', trans('validation.attributes.name').'*:')!!}
            {!!Form::text('name', null, ['class' => 'form-control'])!!}
        </div>
        <div class="row">
            <div class="col-md-6">
                {!!Form::label('rfid', trans('validation.attributes.rfid').':')!!}
                {!!Form::text('rfid', null, ['class' => 'form-control'])!!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {!!Form::label('phone', trans('validation.attributes.phone').':')!!}
                {!!Form::text('phone', null, ['class' => 'form-control'])!!}
            </div>
            <div class="col-md-6">
                {!!Form::label('email', trans('validation.attributes.email').':')!!}
                {!!Form::text('email', null, ['class' => 'form-control'])!!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {!!Form::label('cnh', 'CNH:')!!}
                {!!Form::text('cnh', null, ['class' => 'form-control'])!!}
            </div>
            <div class="col-md-6">
                {!!Form::label('cnh_expire', 'Validade da CNH:')!!}
                {!!Form::text('cnh_expire', null, ['class' => 'form-control datepicker'])!!}
            </div>
        </div>
        <div class="form-group">
            {!!Form::label('description', trans('validation.attributes.description').':')!!}
            {!!Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2])!!}
        </div>
    {!!Form::close()!!}
@stop