@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Editando Ordem de Serviço' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.insta_maint.update', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        {!!Form::hidden('name',$item['name'])!!}
        {!!Form::hidden('device_id',$item['device_id'])!!}
        {!!Form::hidden('client_id',$item['client_id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('os_number', 'Nº Ordem de Serviço:') !!}
                        {!! Form::text('os_number', $item['os_number'], ['class' => 'form-control', 'readonly']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('plate_number', trans('validation.attributes.plate_number').'*:') !!}
                        {!! Form::select('plate_number', $devices->lists('plate_number', 'id'), $item['device_id'], ['class' => 'form-control', 'data-live-search' => true]) !!}
                    </div>               
                </div>
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('cause', 'Causa:')!!}
                        {!!Form::text('cause', $item['cause'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('contact', 'Contato:')!!}
                        {!!Form::text('contact', $item['contact'], ['class' => 'form-control'])!!}
                    </div>
                    @if(!$item['active'])
                        <div class="col-md-6">
                            {!!Form::label('technician_id', 'Técnico:')!!}
                            {!! Form::select('technician_id', $technician->lists('name', 'id'), $item['technician_id'], ['class' => 'form-control', 'data-live-search' => true]) !!}
                        </div>
                        <div class="col-md-6">
                            {!!Form::label('recei_from_cli', 'Técnico Recebeu do cliente (R$):')!!}
                            {!!Form::text('recei_from_cli', $item['recei_from_cli'], ['class' => 'form-control'])!!}
                        </div>
                    @else
                        <div class="col-md-6">
                            {!!Form::label('technician_id', 'Técnico:')!!}
                            {!! Form::text('technician_id', $technician[$item['technician_id']-1]->name, ['class' => 'form-control',  'readonly']) !!}
                            {!!Form::hidden('technician_id',$item['technician_id'])!!}
                        </div>
                        <div class="col-md-6">
                            {!!Form::label('recei_from_cli', 'Técnico Recebeu do cliente (R$):')!!}
                            {!!Form::text('recei_from_cli', $item['recei_from_cli'], ['class' => 'form-control',  'readonly'])!!}
                        </div>
                    @endif
                </div>

                <div class="row">
                    @if($item['active'])
                        <div class="col-md-6">
                            {!!Form::label('valor', 'Valor da OS (R$):')!!}
                            {!!Form::text('valor', $item['valor'], ['class' => 'form-control',  'readonly'])!!}
                        </div>
                    @endif
                    @if($item['payable'])
                        <div class="col-md-6">
                            {!!Form::label('amount_paid', 'Valor Pago ao Técnico (R$):')!!}
                            {!!Form::text('amount_paid', $item['payable_value'], ['class' => 'form-control', 'readonly'])!!}
                        </div>
                    @endif
                    @if(!$item['active'])
                        <div class="col-md-6">
                            {!!Form::label('valor', 'Valor da OS (R$):')!!}
                            {!!Form::text('valor', $item['valor'], ['class' => 'form-control'])!!}
                        </div>
                    @endif
                    @if(!$item['payable'])
                        <div class="col-md-6">
                            {!!Form::label('amount_paid', 'Valor Pago ao Técnico (R$):')!!}
                            {!!Form::text('amount_paid', $item['payable_value'], ['class' => 'form-control'])!!}
                        </div>
                    @endif
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"> 
                            {!!Form::label('expected_date', 'Programado para:')!!}
                            {!! Form::input('date', 'expected_date', $item['expected_date'], ['class'=>'form-control', 'required']) !!}
                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!!Form::label('installation_date', 'Realizado em:')!!}
                            {!! Form::input('date', 'installation_date', $item['installation_date'], ['class'=>'form-control', 'required']) !!}
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('city', 'Endereço completo Instalação:')!!}
                        {!!Form::text('city', $item['address'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('installation_location', 'Local Instalação:')!!}
                        {!!Form::text('installation_location', $item['installation_location'], ['class' => 'form-control'])!!}
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
                    {!!Form::textarea('obs', $item['obs'], ['class' => 'form-control','required'])!!}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            {!! Form::hidden('maintenance', 0) !!}
                            {!! Form::checkbox('maintenance', 1, $item['maintenance']) !!}
                            {!! Form::label($item['maintenance'], "Manutenção") !!}
                        </div>
                    </div>
                    @if(!$item['payable'])
                        <div class="col-md-6">
                            <div class="checkbox">
                                {!! Form::hidden('write_off_the_balance', 0) !!}
                                {!! Form::checkbox('write_off_the_balance', 1, 0) !!}
                                {!! Form::label(0, "Abater no saldo") !!}
                            </div>
                        </div>
                    @endif
                </div>
                    <div class="row">
                        {!! Form::hidden('active', $item['active']) !!}
                @if(!$item['active'])
                        <div class="col-md-6">
                            <div class="checkbox">
                                
                                {!! Form::checkbox('active', 1, $item['active']) !!}
                                {!! Form::label($item['active'], "Serviço finalizado") !!}
                            </div>
                        </div>
                @endif
                {!! Form::hidden('payable', $item['payable']) !!}
                @if(!$item['payable'])
                        <div class="col-md-6">
                            <div class="checkbox">
                                
                                {!! Form::checkbox('payable', 1, $item['payable']) !!}
                                {!! Form::label($item['payable'], "Pagamento total realizado") !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop

