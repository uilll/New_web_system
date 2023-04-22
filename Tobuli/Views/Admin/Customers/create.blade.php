@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> Novo Cliente
@stop

@section('body')
    @if (isAdmin())
    
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.customer.store', 'method' => 'POST'])!!}
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
                        {!! Form::label('name', 'Nome:') !!}
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('cpf_cnpj', 'CPF/CNPJ:') !!}
                        {!! Form::text('cpf_cnpj', null, ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('address', 'Endereço:')!!}
                        {!!Form::text('address', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('city', 'Cidade:')!!}
                        {!!Form::text('city', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('contact', 'Contato:')!!}
                        {!!Form::text('contact', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('users_passwords', 'Usuários e senhas:')!!}
                        {!!Form::text('users_passwords', 'Usuário:      Senha:', ['class' => 'form-control'])!!}
                    </div>                    
                </div>
                <div class="form-group">
                    {!!Form::label('obs', 'Observação:')!!}
                    {!!Form::textarea('obs', null, ['class' => 'form-control'])!!}
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
@stop

@section("javascript")

@stop

