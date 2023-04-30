@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Criando Cliente Asaas' !!}
@stop

@section('body')
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'asaas.clientes.cadastrarCliente', 'method' => 'POST'])!!}
        <div class="tab-content" id="clientes_asaas_create_modal">
            
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">                        
                    {!! Form::label('personType', 'Tipo de Pessoa:') !!}
                    {!! Form::select('personType', ['FÍSICA', 'JURÍDICA'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('name', 'Nome:*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                </div>        
                <div class="form-group">                        
                    {!! Form::label('cpfCnpj', 'CPF/CNPJ:*') !!}
                    {!! Form::text('cpfCnpj', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('email', 'Email:') !!}
                    {!! Form::text('email', null, ['class' => 'form-control']) !!}
                </div> 
                <div class="form-group">                        
                    {!! Form::label('additionalEmails', 'Email Alternativo:') !!}
                    {!! Form::text('additionalEmails', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('company', 'Companhia:') !!}
                    {!! Form::text('company', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('phone', 'Telefone:') !!}
                    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                </div>           
                <div class="form-group">                        
                    {!! Form::label('mobilePhone', 'Celular:') !!}
                    {!! Form::text('mobilePhone', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('address', 'Endereço:') !!}
                    {!! Form::text('address', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('addressNumber', 'Número:') !!}
                    {!! Form::text('addressNumber', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('complement', 'Complemento:') !!}
                    {!! Form::text('complement', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('province', 'Bairro:') !!}
                    {!! Form::text('province', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('externalReference', 'Referência:') !!}
                    {!! Form::text('externalReference', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('postalCode', 'Código Postal:') !!}
                    {!! Form::text('postalCode', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('municipalInscription', 'Inscrição Municipal:') !!}
                    {!! Form::text('municipalInscription', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('stateInscription', 'Inscrição Estadual:') !!}
                    {!! Form::text('stateInscription', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('city', 'Cidade:') !!}
                    {!! Form::text('city', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('state', 'Estado:') !!}
                    {!! Form::text('state', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('country', 'País:') !!}
                    {!! Form::text('country', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('observations', 'Observações:') !!}
                    {!! Form::text('observations', null, ['class' => 'form-control']) !!}
                </div>
                <div class="checkbox">      
                    {!! Form::checkbox('active', 1, 'notificationDisabled') !!}                  
                    {!! Form::label('notificationDisabled', 'Desabilitar Notificações') !!}
                </div>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    
@stop

