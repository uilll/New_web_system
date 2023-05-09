@extends('Frontend.Layouts.modal')
{{$errors->first('erro_formulario')}}

@section('title')
    <i class="icon device"></i> {!! 'Editando Cliente Asaas' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'asaas.clientes.atualizarCliente', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        {!!Form::hidden('name',$item['name'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="form-group">                        
                    {!! Form::label('personType', 'Tipo de Pessoa:') !!}
                    {!! Form::select('personType', ['FÍSICA', 'JURÍDICA'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('name', 'Nome:*') !!}
                    {!! Form::text('name', $item['name'], ['class' => 'form-control']) !!}
                </div>        
                <div class="form-group">                        
                    {!! Form::label('cpfCnpj', 'CPF/CNPJ:*') !!}
                    {!! Form::text('cpfCnpj', $item['cpfCnpj'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('email', 'Email:') !!}
                    {!! Form::text('email', $item['email'], ['class' => 'form-control']) !!}
                </div> 
                <div class="form-group">                        
                    {!! Form::label('additionalEmails', 'Email Alternativo:') !!}
                    {!! Form::text('additionalEmails', $item['additionalEmails'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('company', 'Companhia:') !!}
                    {!! Form::text('company', $item['company'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('phone', 'Telefone:') !!}
                    {!! Form::text('phone', $item['phone'], ['class' => 'form-control']) !!}
                </div>           
                <div class="form-group">                        
                    {!! Form::label('mobilePhone', 'Celular:') !!}
                    {!! Form::text('mobilePhone', $item['mobilePhone'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('address', 'Endereço:') !!}
                    {!! Form::text('address', $item['address'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('addressNumber', 'Número:') !!}
                    {!! Form::text('addressNumber', $item['addressNumber'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('complement', 'Complemento:') !!}
                    {!! Form::text('complement', $item['complement'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('province', 'Bairro:') !!}
                    {!! Form::text('province', $item['province'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('externalReference', 'Referência:') !!}
                    {!! Form::text('externalReference', $item['externalReference'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('postalCode', 'Código Postal:') !!}
                    {!! Form::text('postalCode', $item['postalCode'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('municipalInscription', 'Inscrição Municipal:') !!}
                    {!! Form::text('municipalInscription', $item['municipalInscription'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('stateInscription', 'Inscrição Estadual:') !!}
                    {!! Form::text('stateInscription', $item['stateInscription'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('city', 'Cidade:') !!}
                    {!! Form::text('city', $item['city'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('state', 'Estado:') !!}
                    {!! Form::text('state', $item['state'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('country', 'País:') !!}
                    {!! Form::text('country', $item['country'], ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">                        
                    {!! Form::label('observations', 'Observações:') !!}
                    {!! Form::text('observations', $item['observations'], ['class' => 'form-control']) !!}
                </div>
                <div class="checkbox">      
                    {!! Form::checkbox('active', 0, $item['notificationDisabled']) !!}                  
                    {!! Form::label('notificationDisabled', 'Desabilitar Notificações') !!}
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}
                        {!! Form::checkbox('active', 1, !$item['deleted'], ['readonly' => 'readonly']) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>                    
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop

