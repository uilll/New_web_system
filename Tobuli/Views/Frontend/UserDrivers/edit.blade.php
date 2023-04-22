@extends('Frontend.Layouts.modal')

@section('title')
    {!!trans('global.edit')!!}
@stop

@section('body')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {!!Form::open(['route' => 'user_drivers.update', 'method' => 'PUT'])!!}
    {!!Form::hidden('id', $item->id)!!}
    <div class="form-group">
        <div class="checkbox">
            {!! Form::checkbox('current', 1, 1) !!}
            {!! Form::label(null, trans('front.set_as_current')) !!}
        </div>
    </div>
    <div class="form-group">
        {!!Form::label('name', trans('validation.attributes.name').'*:')!!}
        {!!Form::text('name', $item->name, ['class' => 'form-control'])!!}
    </div>
    <div class="row">
        <div class="col-md-6">
            {!!Form::label('rfid', trans('validation.attributes.rfid').':')!!}
            {!!Form::text('rfid', $item->rfid, ['class' => 'form-control'])!!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            {!!Form::label('phone', trans('validation.attributes.phone').':')!!}
            {!!Form::text('phone', $item->phone, ['class' => 'form-control'])!!}
        </div>
        <div class="col-md-6">
            {!!Form::label('email', trans('validation.attributes.email').':')!!}
            {!!Form::text('email', $item->email, ['class' => 'form-control'])!!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            {!!Form::label('cnh', 'CNH:')!!}
            {!!Form::text('cnh', $item->cnh, ['class' => 'form-control'])!!}
        </div>
        <div class="col-md-6">
            {!!Form::label('cnh_expire', 'Validade da CNH:')!!}
            {!!Form::text('cnh_expire', $item->cnh_expire, ['class' => 'form-control datepicker'])!!}
        </div>
    </div>
    <div class="form-group">
        {!!Form::label('description', trans('validation.attributes.description').':')!!}
        {!!Form::textarea('description', $item->description, ['class' => 'form-control', 'rows' => 2])!!}
    </div>
    {!!Form::close()!!}
@stop