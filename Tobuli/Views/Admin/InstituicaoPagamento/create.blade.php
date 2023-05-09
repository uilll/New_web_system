@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Configurando Conta de Instituição de pagamento' !!}
@stop

@section('body')
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>

                        {!!Form::open(['route' => 'instituicao_pagamento.store', 'method' => 'POST'])!!}

                            <div class="tab-content" id="instituicao_pagamento_create_modal">
                                
                                <div id="occurence-add-form-main" class="tab-pane active">
                                    <div class="form-group row">
                                        {!! Form::label('nome_conta', 'Nome da Conta:*', ['class' => 'col-md-4 col-form-label text-md-right']) !!}

                                        <div class="col-md-6">
                                            {!! Form::text('nome_conta', null, ['class' => 'form-control', 'required', 'autocomplete' => 'nome_conta', 'autofocus']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {!! Form::label('nome_instituicao', 'Nome da Instituição:*', ['class' => 'col-md-4 col-form-label text-md-right']) !!}

                                        <div class="col-md-6">
                                            {!! Form::text('nome_instituicao', null, ['class' => 'form-control', 'required', 'autocomplete' => 'nome_instituicao', 'autofocus']) !!}
                                        </div>
                                    </div> 

                                    <div class="form-group row">
                                        {!! Form::label('usuarios_permitidos', 'Usuários Permitidos:*', ['class' => 'col-md-4 col-form-label text-md-right']) !!}

                                        <div class="col-md-6">
                                            {!! Form::select('usuarios_permitidos[]', $lista, null, ['class' => 'form-control', 'data-live-search' => true, 'multiple' => 'multiple']) !!}
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        {!! Form::label('chave_acesso', 'Chave de Acesso:*', ['class' => 'col-md-4 col-form-label text-md-right']) !!}

                                        <div class="col-md-6">
                                            {!! Form::text('chave_acesso', null, ['class' => 'form-control', 'required', 'autocomplete' => 'chave_acesso']) !!}
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        {!! Form::label('site_acesso', 'Site de Acesso:*', ['class' => 'col-md-4 col-form-label text-md-right']) !!}

                                        <div class="col-md-6">
                                            {!! Form::text('site_acesso', null, ['class' => 'form-control', 'required', 'autocomplete' => 'site_acesso', 'autofocus']) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            {!!Form::close()!!}
@stop

