@extends('Frontend.Layouts.modal_sensors')
@section('title')
    <i class="icon sensors"></i> Sensores do veículo.
@stop
@section('body')
    <div id="widgets2" style="display: block; font-size: initial !important">
        <div class="widgets-content">		
            <script> $("#widgets2").append($("#widgets .widget-sensors").html()); </script>	
        </div>
    </div>
@stop