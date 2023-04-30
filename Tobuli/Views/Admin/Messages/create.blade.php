@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> Nova Mensagem para o cliente <span id="client-name-modal"></span>
@stop

@section('body')
    
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!! Form::open(['route' => 'messages.store', 'method' => 'POST']) !!}
        {!! Form::hidden('customer_id', null, ['id' => 'customer_id']) !!}
        <div class="form-group">
        {!! Form::label('subject', 'Assunto') !!}
        {!! Form::text('subject', null, ['class' => 'form-control', 'required']) !!}
        </div>
        <div class="form-group">
        {!! Form::label('message', 'Mensagem') !!}
        {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => '3', 'required']) !!}
        </div>
        {!! Form::close() !!}

        <script>
            $(document).ready(function() {
                $('#message_create').on('shown.bs.modal', function (event) {
                    var modal = $(this);
                    modal.find('#client-name-modal').text(clientName);
                    modal.find('#customer_id').val(clientId);
                })
            });
        </script>


                    

@stop

@section("javascript")
    
@stop

