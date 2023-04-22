@extends('Frontend.Layouts.modal_alert')
@section('title')
    <i class="icon-fa fa fa-anchor"></i> Âncora do veículo.
@stop
@section('body')
        <div id="anchor" class="container" style="width: 100%; min-width: 100%">
                    </br>
                    </br>
                        @if ($anchor_status==1)
                            <table style="width: 100%; min-width: 100%">
                                <td style="width: 100%; min-width: 100%" align="center">
                                    <span><h3>A função de âncora foi <strong>ativada </strong> para este veículo que você selecionou. <br><br> Caso o veículo se mova um alerta será enviado ao sistema e você irá visualizar </h3></span>
                                </td> 
                            </table>
                        @else
                            <table style="width: 100%; min-width: 100%">
                                <td style="width: 100%; min-width: 100%" align="center">
                                    <span><h3>A função de âncora foi <strong>desativada </strong> para este veículo que você selecionou.</h3></span>
                                </td> 
                            </table>
                        @endif    
                    </br>
        </div>
@stop