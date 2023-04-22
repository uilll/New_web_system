@extends('Frontend.Layouts.modal_alert')
@section('title')
    <i class="icon-fa fa fa-share-alt"></i> Compartilhar o veículo.
@stop
@section('body')
        <div id="share_device">
                    </br>
                        <h3 class="text-justify">Muito cuidado ao compartilhar a localização do seu veículo, você é responsável por eventuais problemas ao compartilhar. <br><br> Clique/toque no ícone abaixo para copiar o endereço para a área de transferência.<br> </h3>
                        <a href="javascript:" id="link_compartilhado" onClick="para_area_transferencia({{ json_encode($link) }});">
                            {{ $link }}
                        </a>    
                    </br>
        </div>
@stop