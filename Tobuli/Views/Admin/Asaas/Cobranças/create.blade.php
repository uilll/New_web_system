@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Criando Cobrança Asaas' !!}
@stop

@section('body')
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'asaas.cobranças.criarCobrança', 'method' => 'POST'])!!}
        <div class="tab-content" id="cobranças_asaas_create_modal">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">                        
                    {!! Form::label('name', 'Nome:') !!}
                    {!! Form::select('customer', $id, null, ['class' => 'form-control', 'data-live-search'=>true]) !!}
                </div>       
                <div class="form-group">                        
                    {!! Form::label('billingType', 'Forma de Pagamento:') !!}
                    {!! Form::text('billingType', 'BOLETO', ['class' => 'form-control', 'readonly']) !!}
                </div>        
                <div class="form-group">                        
                    {!! Form::label('value', 'Valor:') !!}
                    {!! Form::text('value', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('dueDate', 'Data de Vencimento:') !!}
                    {!! Form::text('dueDate', null, ['class' => 'form-control datepicker']) !!}
                </div> 
                <div class="form-group">                        
                    {!! Form::label('description', 'Descrição:') !!}
                    {!! Form::text('description', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('installmentCount', 'Número de Parcelas:') !!}
                    {!! Form::text('installmentCount', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('installmentValue', 'Valor da Parcela:') !!}
                    {!! Form::text('installmentValue', null, ['class' => 'form-control']) !!}
                </div>           
                <div class="form-group">                        
                    {!! Form::label('externalReference', 'Referência:') !!}
                    {!! Form::text('externalReference', null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    
@stop