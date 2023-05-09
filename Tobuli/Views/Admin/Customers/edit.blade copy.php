@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Editando Cliente' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.customer.update', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}
                        {!! Form::checkbox('active', 1, $item->active) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>                    
                    <div class="checkbox">
                        {!! Form::hidden('in_debt', 0) !!}
                        {!! Form::checkbox('in_debt', 1, $item->in_debt) !!}
                        {!! Form::label(null, "Em débito?") !!}
                    </div>  
                </div>
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('name', 'Nome:') !!}
                        {!! Form::text('name', $item->name, ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('cpf_cnpj', 'CPF/CNPJ:') !!}
                        {!! Form::text('cpf_cnpj', $item->cpf_cnpj, ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('address', 'Endereço:')!!}
                        {!!Form::text('address', $item->address, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('city', 'Cidade:')!!}
                        {!!Form::text('city', $item->city, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('contact', 'Contato:')!!}
                        {!!Form::text('contact', $item->contact, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('users_passwords', 'Usuários e senhas:')!!}
                        {!!Form::text('users_passwords', $item->users_passwords, ['class' => 'form-control'])!!}
                    </div>                    
                </div>
                <div class="form-group">         
                    {!! Form::label('all_users', 'Usuários:') !!}
                    {!! Form::select('all_users[]', $item->users, array_keys($item->lista), ['class' => 'form-control', 'data-live-search' => true, 'multiple' => 'multiple']) !!}
                </div>     
                <div class="form-group">
                    {!!Form::label('obs', 'Observação:')!!}
                    {!!Form::textarea('obs', $item->obs, ['class' => 'form-control'])!!}
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop