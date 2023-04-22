@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Editando Rastreador' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.tracker.update', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}   
                        {!! Form::checkbox('active', 1, $item['active']) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>                    
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::checkbox('in_use', 1, $item['in_use'], array('disabled')) !!}
                        {!! Form::label(null, 'Em uso') !!}
                    </div>                    
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('in_service', 0) !!}
                        {!! Form::checkbox('in_service', 1, $item['in_service']) !!}
                        {!! Form::label(null, 'Em Manutenção') !!}
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('imei', 'IMEI:') !!}
                        {!! Form::text('imei', $item['imei'], ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('brand', 'Marca') !!}
                        {!! Form::text('brand', $item['brand'], ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('model', 'Modelo:')!!}
                        {!!Form::text('model', $item['model'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('work_since', 'Funcionando desde:')!!}
                        {!!Form::text('work_since', $item['work_since'], ['class' => 'form-control', 'readonly'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('history', 'Histórico:')!!}
                        {!!Form::text('history', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('sim_number', 'Número do SIM:')!!}
                        {!!Form::text('sim_number', $item['sim_number'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('iccd', 'ICCD:')!!}
                        {!!Form::text('iccd', $item['iccd'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('operator', 'APN:')!!}
                        {!!Form::text('operator', $item['operator'], ['class' => 'form-control'])!!}
                    </div>
                    
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop

