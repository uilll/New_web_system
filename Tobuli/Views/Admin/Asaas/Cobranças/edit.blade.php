@extends('Frontend.Layouts.modal')
{{$errors->first('erro_formulario')}}

@section('title')
    <i class="icon device"></i> {!! 'Editando Cobrança Asaas' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'asaas.cobranças.atualizarCobrança', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        {!!Form::hidden('customer',$item['customer'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                    <div class="form-group">                        
                        {!! Form::label('billingType', 'Forma de Pagamento:') !!}
                        {!! Form::text('billingType', 'BOLETO', ['class' => 'form-control', 'readonly']) !!}
                    </div>        
                    <div class="form-group">                        
                        {!! Form::label('value', 'Valor:') !!}
                        {!! Form::text('value', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">                        
                        {!! Form::label('dateCreated', 'Data de Criação:') !!}
                        {!! Form::text('dateCreated', $item['dateCreated'], ['class' => 'form-control', 'readonly']) !!}
                    </div> 
                    <div class="form-group">                        
                        {!! Form::label('dueDate', 'Data de Vencimento:') !!}
                        {!! Form::text('dueDate', null, ['class' => 'form-control datepicker']) !!}
                    </div> 
                    <div class="form-group">                        
                        {!! Form::label('description', 'Descrição:') !!}
                        {!! Form::text('description', $item['description'], ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">                        
                        {!! Form::label('externalReference', 'Referência:') !!}
                        {!! Form::text('externalReference', $item['externalReference'], ['class' => 'form-control']) !!}
                    </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop