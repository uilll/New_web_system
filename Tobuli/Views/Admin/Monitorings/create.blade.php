@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!!trans('global.add_new')!!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.monitoring.store', 'method' => 'POST'])!!}
        {!!Form::hidden('id')!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}
                        {!! Form::checkbox('active', 1, true) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>                    
                </div>
                <div class="form-group">                        
                    {!! Form::label('plate_number', trans('validation.attributes.plate_number').'*:') !!}
                    {!! Form::select('plate_number', $devices->lists('plate_number', 'id'), null, ['class' => 'form-control', 'data-live-search' => true]) !!}
                </div>                
                <div class="form-group">
                    {!!Form::label('cause', 'Causa:')!!}
                    {!!Form::text('cause', 'teste', ['class' => 'form-control'])!!}
                </div>
                <div class="form-group">
                    {!!Form::label('information', 'Observações:')!!}
                    {!!Form::textarea('information', null, ['class' => 'form-control','required'])!!}
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!!Form::label('Data_ocorrencia', 'Data da Ocorrência:')!!}
                            {!! Form::input('date', 'occorunce_date', null, ['class'=>'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!!Form::label('next_contact', 'Próximo contato:')!!}
                            {!! Form::input('date', 'next_contact', \Carbon\Carbon::create()->format('d/m/Y H:i:s'), ['class'=>'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('treated_occurence', 0) !!}
                        {!! Form::checkbox('treated_occurence', 1, false) !!}
                        {!! Form::label(null, "Ocorrência tratada?") !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('make_contact', 0) !!}
                        {!! Form::checkbox('make_contact', 1, false) !!}
                        {!! Form::label(null, "Entrou em contato com o cliente?") !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('sent_maintenance', 0) !!}
                        {!! Form::checkbox('sent_maintenance', 1, false) !!}
                        {!! Form::label(null, "Enviar para manutenção?") !!}
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

