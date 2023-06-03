@extends('Frontend.Layouts.modal_simples')
@section('title')
    <i class="icon alert"></i> Troca de motorista
@stop
@section('body') 
        <div class="action-block" style="float: right">
            <a href="javascript:" class="btn btn-action" data-url="{!!route('user_drivers.create')!!}" data-modal="user_drivers_create" type="button">
                <i class="icon add"></i> {{ trans('front.add_driver') }}
            </a>
        </div>
        {!!Form::open(['route' => 'user_drivers.update', 'method' => 'PUT'])!!}
        {!!Form::hidden('device_id',$items->id)!!}
        {!!Form::hidden('older_driver',$items->current_driver_id)!!}    
        <span id="Título"> Escolha o próximo motorista para o veículo </span> 
        </br>
        </br>
        <div class="tab-content">
            <div class="form-group">
                <div class="checkbox">
                    {!! Form::checkbox('current', 1, 1) !!}
                    {!! Form::label(null, trans('front.set_as_current')) !!}
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    {!!Form::label('device', 'Veículo:')!!}
                    {!!Form::text('device', $items->plate_number, ['class' => 'form-control', 'readonly'])!!}
                </div>
                <div class="col-md-6">
                    {!!Form::label('driver', 'Motorista:')!!}
                    {!! Form::select('id', $drivers->pluck('name', 'id'), $items->current_driver_id, ['class' => 'form-control', 'data-live-search' => true]) !!}
                </div>
            </div>
        </div>
        {!!Form::close()!!}
@stop
