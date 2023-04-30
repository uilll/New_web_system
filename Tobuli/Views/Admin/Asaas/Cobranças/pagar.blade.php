@extends('Frontend.Layouts.modal')
{{$errors->first('erro_formulario')}}

@section('title')
    <i class="icon device"></i> {!! 'Deseja concluir o pagamento (em dinheiro)?' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'asaas.cobranças.receiveInCash', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                    <div class="form-group">                        
                        {!! Form::label('value', 'Valor pago:') !!}
                        {!! Form::text('value', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">                        
                        {!! Form::label('paymentDate', 'Data de Pagamento:') !!}
                        {!! Form::text('paymentDate', null, ['class' => 'form-control datepicker']) !!}
                    </div> 
                    <div class="form-group">                        
                        {!! Form::label('description', 'Descrição') !!}
                        {!! Form::text('description', null, ['class' => 'form-control']) !!}
                    </div> 
                    <div class="checkbox">                        
                        {!! Form::checkbox('active', 1, 'notifyCustomer') !!}
                        {!! Form::label('notifyCustomer', 'Notificar Cliente:') !!}
                    </div> 
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop