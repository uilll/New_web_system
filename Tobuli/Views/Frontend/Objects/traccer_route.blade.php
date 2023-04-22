@extends('Frontend.Layouts.modal_traccer_route')
@section('title')
    <i class="fa fa-road"></i> Traçar rota para o veículo.
@stop
@section('body')
        <div id="traccer_router" class="container" style="width: 100%; min-width: 100%">
                    <span id="Título"> Você será redirecionado para outro sistema de rota de sua escolha, WAZE ou GOOGLE MAPS</span> 
                    </br>
                    </br>
                    <table style="width: 100%; min-width: 100%">
                        <td style="width: 50%; min-width: 50%" align="center">
                            <a href="https://www.waze.com/ul?ll={{$coordenadas['lat']}},{{$coordenadas['lon']}}&navigate=yes&zoom=1"> <i class='fab fa-waze fa-5x'></i> </a>
                        </td> 
                        <td style="width: 50%; min-width: 50%" align="center">
                            <a href="carseghttps://www.google.com/maps?q={{$coordenadas['lat']}},{{$coordenadas['lon']}}&z=17&hl=pt-BR"><i class='fab fa-google fa-5x'></i> </a>
                        </td>
                    </table>
                    </br>
        </div>
@stop