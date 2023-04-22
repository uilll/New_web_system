
@extends('Frontend.Layouts.modal_full')

@section('title')
    <i class="icon device"></i> {!! 'Ocorrências Anteriores:   ' !!} {{ $items->owner." (".$items->plate_number.")" }}
@stop

@section('body')
    <div style="paddin: 10px; margin: 10px">
        <div class="row justify-content-lg-center" style="min-height: 100px">
            @if ($situacao == 0)
            <div class="col-sm text-lg-center" style="height: 100px;" align="center">
                <span style="min-width:100%;font-size: 20px;">Não existem ocorrências anteriores para este veículo!</span>
            </div>
            @else
                <div class="col-xl" style="height: 100px;" align="center">
                    @foreach ($items as $item)
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Número da ocorrêcia: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->id }}</span>
                            </div>  
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Causa: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->cause }}</span>
                            </div>               
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Data da ocorrêcia: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->gps_date }}</span>
                            </div>  
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Realizou contato: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->make_contact ? "Sim" : "Não" }}</span>
                            </div>               
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Ocorrência tratada: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->treated_occurence ? "Sim" : "Não" }}</span>
                            </div>  
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Tipo de tratamento: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->automatic_treatment ? "Automático" : "Manual" }}</span>
                            </div>               
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Latitude: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->lat}}</span>
                            </div>  
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Longitude: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->lon }}</span>
                            </div>               
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Cidade: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->city}} {{ $item->state }}</span>
                            </div>  
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Endereço: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->address }}</span>
                            </div>                           
                        </div>
                        <div class="row">
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Place ID: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->place_id }}</span>
                            </div>
                            <div class="col-md-6" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Atualizado em: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->timestamp }}</span>
                            </div>                            
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12" align="left">                        
                                <span style="min-width:100%;font-size: 15px;">Informações: </span>
                                <span style="min-width:100%;font-size: 15px;">{{ $item->information}}</span>
                            </div>  
                        </div>
                        <HR>
                        <br>
                    @endforeach
                </div>    
            @endif
        </div >
    </div>
@stop