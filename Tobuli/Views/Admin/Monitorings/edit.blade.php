@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Editando Ocorrência' !!}
@stop

@section('body')
    @if (isAdmin())
        
        <ul class="nav nav-tabs nav-default" role="tablist">
            <li class="active"><a href="#device-add-form-main" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
        </ul>
        {!!Form::open(['route' => 'admin.monitoring.update', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$item['id'])!!}
        {!!Form::hidden('name',$item['name'])!!}
        {!!Form::hidden('device_id',$item['device_id'])!!}
        <div class="tab-content">
            <div id="occurence-add-form-main" class="tab-pane active">
                
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('active', 0) !!}
                        {!! Form::checkbox('active', 1, $item['active']) !!}
                        {!! Form::label(null, trans('validation.attributes.active')) !!}
                    </div>                    
                </div>
                <div class="form-group">                        
                    {!! Form::label('plate_number', trans('validation.attributes.plate_number').'*:') !!}
                    {!! Form::text('cause', $item['plate_number'], ['class' => 'form-control', 'readonly']) !!}
                </div>               
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('cause', 'Causa:')!!}
                        {!!Form::text('cause', $item['cause'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('contact', 'Contato:')!!}
                        {!!Form::text('contact', isset($item['contact']) ? $item['contact'] : '', ['class' => 'form-control'])!!}
                    </div>

                </div>
                
                <div class="form-group">
                    {!!Form::label('information', 'Observações:')!!}
                    {!!Form::textarea('information', $item['information'], ['class' => 'form-control','required'])!!}
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('car_model', 'Veículo:')!!}
                        {!!Form::text('car_model', $item['device_model'], ['class' => 'form-control','readonly'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('vehicle_color', 'Cor:')!!}
                        {!!Form::text('vehicle_color', $item['vehicle_color'], ['class' => 'form-control','readonly'])!!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        {!!Form::label('city', 'Endereço:')!!}
                        {!!Form::text('city', $item['city'], ['class' => 'form-control'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::label('last_address', 'Última loalização:')!!}
                        {!!Form::text('last_address', $item['last_address'], ['class' => 'form-control','readonly'])!!}
                    </div>
                </div>
                
                <h4>
                    Pontos de interesse próximos:
                </h4>    

                <div style="border-width: medium;   border-style: solid;   border-color: #d3d3d3; padding: 10px;">
                    <div class="row">
                        <div class="col-md-6">
                            <b>{!!Form::label('poi_next_001', 'PI < 10m:','style="font-weight: bold"')!!}</b>
                            @foreach ($poi_next_001 as $poi)
                                    <br>
                                        Nome: {!!$poi->name!!}<br>
                                        Descrição: {!!$poi->description!!}<br>
                                        Distância: {!!$poi->distance!!}<br><br>
                            @endforeach
                            
                        </div>
                        <div class="col-md-6">
                            <b>{!!Form::label('poi_next_01', '10m < PI < 100m:','style="font-weight: bold"')!!}</b>
                            @foreach ($poi_next_01 as $poi)
                                <br>
                                    Nome: {!!$poi->name!!}<br>
                                    Descrição: {!!$poi->description!!}<br>
                                    Distância: {!!$poi->distance!!}<br><br>
                            @endforeach
                            
                        </div>
                    </div>

                    <div class="row">
                            <div class="col-md-6">
                                <b>{!!Form::label('poi_next_1', '100m < PI < 1km:','style="font-weight: bold"')!!}</b>
                                @foreach ($poi_next_1 as $poi)
                                    <br>
                                    Nome: {!!$poi->name!!}<br>
                                    Descrição: {!!$poi->description!!}<br>
                                    Distância: {!!$poi->distance!!}<br><br>
                                @endforeach
                                
                            </div>
                                <div class="col-md-6">
                                <b>{!!Form::label('poi_next_10', '1km < PI < 10km:','style="font-weight: bold"')!!}</b>
                                @foreach ($poi_next_10 as $poi)
                                    <br>
                                    Nome: {!!$poi->name!!}<br>
                                    Descrição: {!!$poi->description!!}<br>
                                    Distância: {!!$poi->distance!!}<br><br>
                                @endforeach
                                
                            </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <h4>{!!Form::label('additional_notes', 'Notas adicionais: (SOMENTE INFORMAÇÕES FIXAS)')!!}</h4>
                        {!!Form::textarea('additional_notes', $item['additional_notes'], ['class' => 'form-control'])!!}
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!!Form::label('Data_ocorrencia', 'Data da Ocorrência:')!!}
                            {!! Form::text('occ_date', $item['occ_date'], ['class' => 'form-control', 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!!Form::label('next_contact', 'Próximo contato:')!!}
                            {!! Form::input('date', 'next_con', null, ['class'=>'form-control']) !!}
                            {!! Form::hidden('active_contact', 0) !!}
                            {!! Form::checkbox('active_contact', 1, false) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('treated_occurence', 0) !!}
                        {!! Form::checkbox('treated_occurence', 1, $item['treated_occurence']) !!}
                        {!! Form::label($item['treated_occurence'], "Ocorrência tratada?") !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('make_contact', 0) !!}
                        {!! Form::checkbox('make_contact', 1, $item['make_contact']) !!}
                        {!! Form::label($item['make_contact'], "Entrou em contato com o cliente?") !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::hidden('sent_maintenance', 0) !!}
                        {!! Form::checkbox('sent_maintenance', 1, $item['sent_maintenance']) !!}
                        {!! Form::label($item['sent_maintenance'], "Enviar para manutenção?") !!}
                    </div>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    @endif
    
@stop

