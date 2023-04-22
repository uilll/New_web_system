@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> Novo Serviço
@stop

@section('body')
    @if (isAdmin())
    
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.insta_maint.store', 'method' => 'POST'])!!}
        {!!Form::hidden('id')!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
            
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('os_number', 'Nº Ordem de Serviço:') !!}
                        {!! Form::text('os_number', $os_number, ['class' => 'form-control', 'readonly']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('plate_number', trans('validation.attributes.plate_number').'*:') !!}
                        {!! Form::select('plate_number', $devices->lists('plate_number', 'id'), null, ['class' => 'form-control', 'data-live-search' => true]) !!}
                    </div>               
                </div>
               <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('cause', 'Causa:')!!}
                        {!!Form::text('cause', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('technician_id', 'Técnico:')!!}
                        {!! Form::select('technician_id', $technician->lists('name', 'id'), null, ['class' => 'form-control', 'data-live-search' => true]) !!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('recei_from_cli', 'Técnico Recebeu do cliente (R$):')!!}
                        {!!Form::text('recei_from_cli', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('valor', 'Valor (R$):')!!}
                        {!!Form::text('valor', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('amount_paid', 'Valor Pago ao Técnico (R$):')!!}
                        {!!Form::text('amount_paid', 0, ['class' => 'form-control'])!!}
                    </div>        
                </div>
                
                
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"> 
                            {!!Form::label('expected_date', 'Programado para:')!!}
                            {!! Form::input('date', 'expected_date', $date_now, ['class'=>'form-control', 'required']) !!}
                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!!Form::label('installation_date', 'Realizado em:')!!}
                            {!! Form::input('date', 'installation_date', $date_now, ['class'=>'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('city', 'Endereço completo Instalação:')!!}
                        {!!Form::text('city', 'Capim Grosso', ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('installation_location', 'Local Instalação:')!!}
                        {!!Form::text('installation_location', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('change_locale', 0) !!}
                            {!! Form::checkbox('change_locale', 1, 0) !!}
                            {!! Form::label(0, "Alterar Local de instalação") !!}
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    {!!Form::label('obs', 'Observações:')!!}
                    {!!Form::textarea('obs', null, ['class' => 'form-control','required'])!!}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('maintenance', 0) !!}
                            {!! Form::checkbox('maintenance', 1, 0) !!}
                            {!! Form::label(0, "Manutenção") !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('write_off_the_balance', 0) !!}
                            {!! Form::checkbox('write_off_the_balance', 1, 0) !!}
                            {!! Form::label(0, "Abater no saldo") !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('active', 0) !!}
                            {!! Form::checkbox('active', 1, 0) !!}
                            {!! Form::label(0, "Serviço finalizado") !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('payable', 0) !!}
                            {!! Form::checkbox('payable', 1, 0) !!}
                            {!! Form::label(0, "Pagamento total realizado") !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
        
        <script>
            $(document).ready(function() {
                console.log("teste3");
                $(document).on("change", '#plate_number_', function () { 
                    //$("#device_id option:selected").text()
                    //$($forms_protocol_).hide();
                    console.log("teste2");
                    console.log($("#plate_number_ option:selected").val());
                    $("#object_owner_").focus($("#plate_number_ option:selected").val());    
                    $( "#object_owner_" ).change();
                });     
            });     
        </script>
    @endif
@stop

@section("javascript")

@stop

