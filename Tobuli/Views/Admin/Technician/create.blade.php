@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> Novo Técnico
@stop

@section('body')
    @if (isAdmin())
    
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.technician.store', 'method' => 'POST'])!!}
        {!!Form::hidden('id')!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
            
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('name', 'Nome:') !!}
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('contact', 'Contato') !!}
                        {!! Form::text('contact', null, ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('address', 'Endereço:')!!}
                        {!!Form::text('address', null, ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('city', 'Cidade:')!!}
                        {!!Form::text('city', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="form-group">
                    {!!Form::label('obs', 'Observações:')!!}
                    {!!Form::textarea('obs', null, ['class' => 'form-control','required'])!!}
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

