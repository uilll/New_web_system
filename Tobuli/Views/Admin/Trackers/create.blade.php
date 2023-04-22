@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> Novo Rastreador
@stop

@section('body')
    @if (isAdmin())
    
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.tracker.store', 'method' => 'POST'])!!}
        {!!Form::hidden('id')!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}
                        {!! Form::checkbox('active', 1, 1) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('imei', 'IMEI:') !!}
                        {!! Form::text('imei', null, ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('brand', 'Marca') !!}
                        {!! Form::text('brand', null, ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('model', 'Modelo:')!!}
                        {!!Form::text('model', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('work_since', 'Funcionando desde:')!!}
                        {!! Form::input('date', 'work_since', null, ['class'=>'form-control']) !!} 
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('history', 'Histórico:')!!}
                        {!!Form::text('history', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('sim_number', 'Número do SIM:')!!}
                        {!!Form::text('sim_number', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('iccd', 'ICCID:')!!}
                        {!!Form::text('iccd', null, ['class' => 'form-control'])!!}
                    </div>
                    @if (Auth::User()->id == ["3", "2", "6", "1025", "1026"])
                        <div class="col-md-6">
                            {!!Form::label('operator', 'APN:')!!}        
                            {!!Form::text('operator', 'm2data.algar.br', ['class' => 'form-control'])!!}
                        </div>
                    @else
                        <div class="col-md-6">
                            {!!Form::label('operator', 'APN:')!!}        
                            {!!Form::text('operator', null, ['class' => 'form-control'])!!}
                        </div>                            
                    @endif
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
@stop

@section("javascript")

@stop

