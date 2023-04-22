@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Editando Técnico' !!}
@stop

@section('body')
    @if (isAdmin())
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.technician.update', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
            
                <div class="row">
                    <div class="col-md-6">                        
                        {!! Form::label('name', 'Nome:') !!}
                        {!! Form::text('name', $item['name'], ['class' => 'form-control']) !!}
                    </div>  
                    <div class="col-md-6">                        
                        {!! Form::label('contact', 'Contato') !!}
                        {!! Form::text('contact', $item['contact'], ['class' => 'form-control']) !!}
                    </div>               
                    <div class="col-md-6">
                        {!!Form::label('address', 'Endereço:')!!}
                        {!!Form::text('address', $item['address'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('city', 'Cidade:')!!}
                        {!!Form::text('city', $item['city'], ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="form-group">
                    {!!Form::label('obs', 'Observações:')!!}
                    {!!Form::textarea('obs', $item['obs'], ['class' => 'form-control','required'])!!}
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop

