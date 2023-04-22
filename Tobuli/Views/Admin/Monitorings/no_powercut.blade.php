
@extends('Frontend.Layouts.modal_alert')

@section('title')
    @if($device->no_powercut)
    <i class="icon device"></i> {!! 'Alerta desabilitado' !!}
    @else
    <i class="icon device"></i> {!! 'Alerta habilitado' !!}
    @endif
@stop

@section('body')
    <div style="paddin: 10px; margin: 10px">
        <div class="row justify-content-lg-center" style="min-height: 100px">
            @if($device->no_powercut)
            <div class="col-sm text-lg-center" style="height: 100px;" align="center">
                <span style="min-width:100%;font-size: 20px;">Alerta de "bateria violada" do veículo {{$device->platenumber}} foi desabilitado com sucesso</span>
            </div>
            @else
            <div class="col-sm text-lg-center" style="height: 100px;" align="center">
                <span style="min-width:100%;font-size: 20px;">Alerta de "bateria violada" do veículo {{$device->platenumber}} foi habilitado com sucesso</span>
            </div>
            @endif
        </div >
    </div>
@stop