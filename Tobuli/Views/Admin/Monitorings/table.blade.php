@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" id="table_{{ $section }}">
    
    <div class="panel-heading">
        <ul class="nav nav-tabs nav-icons pull-right">
            @if( Auth::User()->perm('monitoring', 'edit') )
            <li role="presentation" class="">
                <a href="javascript:" type="button" class="" data-modal="{{ $section }}_create" data-url="{{ route("admin.monitoring.create") }}">
                    <i class="icon plus" title="Adicionar nova ocorrência"></i>
                </a>
            </li>
            <li role="presentation" class="">
                
            </li>
            @endif
        </ul>

        <div class="panel-title"><i class="icon check"></i> Monitoramento - Ocorrências</div>

        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['id' => 'search_admin_', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">monitoring</span>
            </div>
        </div>
    </div>
        
    <div class="table_error"></div>
    <div class="table-responsive">
        <table class="table table-list" data-toggle="multiCheckbox">
            <thead>
            <tr>
                {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
                {!! tableHeader('Status') !!}
                {!! tableHeader('Cliente') !!}
                {!! tableHeader('Proprietário') !!}
                {!! tableHeader('Placa') !!}
                {!! tableHeader('Causa') !!}
                {!! tableHeader('admin.last_connection', 'style="text-align:center;"') !!}
                {!! tableHeader('Ocorrência') !!}
                {!! tableHeader('Modificação') !!}
                {!! tableHeader('Telefone') !!}
                {!! tableHeader('Realizado contato?') !!}
                {!! tableHeader('Informações') !!}
                {!! tableHeader('Próximo contato') !!}
                {!! tableHeader('Enviada para manutenção') !!}
                {!! tableHeader('Auto-tratamento?') !!}
            </tr>
            </thead>
            
            <tbody>
                <?php
                  $collor = true;  
                ?>
                @foreach ($items as $item2)
                    <?php
                        $collor = !$collor;  
                    ?>
                    <tr style="color: {{ $item2->make_contact ? 'rgb(0, 0, 128)' : ($collor ? 'black' : 'red') }}; background-color: {{$collor ? 'gray' : '#fffff'}}">
                        <td class="actions">
                            @if (Auth::User()->perm('monitoring', 'edit') || Auth::User()->perm('monitoring', 'remove'))
                                <div class="btn-group dropdown droparrow" data-position="fixed">
                                    <i class="btn icon edit" data-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="true"></i>
                                    <ul class="dropdown-menu">
                                        @if( Auth::User()->perm('monitoring', 'edit') )
                                            <li>
                                                <a href="javascript:" data-modal="monitoring_edit"
                                                   data-url="{{ route("admin.monitoring.edit", ['id' => $item2->id]) }}">
                                                    {{ trans('global.edit') }}
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <li>
                                            <a href="javascript:" data-modal="monitorings_info"
                                               data-url="{{ route("admin.monitoring.info", ['id' => $item2->device_id]) }}">
                                                Ocorrências anteriores
                                            </a>
                                        </li>

                                        <li>
                                            <a href="javascript:" data-modal="monitorings_info"
                                               data-url="{{ route("admin.monitoring.rem_add_alert", ['id' => $item2->device_id]) }}">
                                                Desabilitar/Habilitar alerta de bateria violada
                                            </a>
                                        </li>
                                        
                                        <li>
                                            <a id="segue__" onclick="showmap({{$item2->device_id}});" href="javascript:" data-url="{{ route("devices.follow_map", ['id' => $item2->device_id]) }}" data-id="{{ $item2->device_id }}" data-name="Seguindo">
                                                        <span class="icon follow"></span>
                                                        <span class="text">{{ trans('front.follow') }}</span>
                                                    </a> <!--{{ route("devices.follow_map", ['id' => $item2->device_id]) }}-->
                                            
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </td>
                        
                        <td>
                            <span class="label label-sm label-{!! $item2->active ? 'success' : 'danger' !!}">
                                {!! trans('validation.attributes.active') !!}
                            </span>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{$item2->customer}}">
                        <div class="fixed-height">
                            {{$item2->customer}}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{$item2->owner}}">
                        <div class="fixed-height">
                            {{$item2->owner}}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{$item2->plate_number}}">
                        <div class="fixed-height">
                            {{$item2->plate_number}}
                            <span id="fechar_mapa{{$item2->device_id}}" onclick="hidemap({{$item2->device_id}});" class="icon close" style="display: none"> Fechar mapa </span>
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{$item2->cause}}">
                        <div class="fixed-height">
                            {{$item2->cause}}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->gps_date ? datetime($item2->gps_date) : trans('front.not_connected') }}">
                        <div class="fixed-height">
                            {{ $item2->gps_date ? datetime($item2->gps_date) : trans('front.not_connected') }}<br>
                            ( {{ $item2->device_time ? datetime($item2->device_time) : trans('front.not_connected') }} )
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->occ_date ? $item2->occ_date : 'Não definida' }}">
                        <div class="fixed-height">
                            {{ $item2->occ_date ? $item2->occ_date : 'Não definida' }}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->modified_date ? $item2->modified_date : 'Nunca modificado' }}">
                        <div class="fixed-height">
                            {{ $item2->modified_date ? $item2->modified_date : 'Nunca modificado' }}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{$item2->contact}}">
                        <div class="fixed-height">
                            {{$item2->contact}}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->make_contact ? 'Sim' : 'Não' }}">
                        <div class="fixed-height">
                            {{ $item2->make_contact ? 'Sim' : 'Não' }}
                        </div>
                        </td>


                        <td class="d-inline-block text-truncate">
                            <div tabindex="0" class="info-cell" role="button" data-toggle="popover" data-placement="bottom" data-content="{{ $item2->information ? $item2->information : $item2->additional_notes }}">
                                {{ substr($item2->information ? $item2->information : $item2->additional_notes, 0, 50) . (strlen($item2->information ? $item2->information : $item2->additional_notes) > 50 ? '...' : '') }}
                            </div>
                        </td>





                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->next_con ? $item2->next_con : 'Sem previsão' }}">
                        <div class="fixed-height">
                            {{ $item2->next_con ? $item2->next_con : 'Sem previsão' }}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->sent_maintenance ? 'Sim' : 'Não' }}">
                        <div class="fixed-height">
                            {{ $item2->sent_maintenance ? 'Sim' : 'Não' }}
                        </div>
                        </td>
                        <td class="d-inline-block text-truncate table-list_truncate" data-toggle="tooltip" title="{{ $item2->automatic_treatment ? 'Sim' : 'Não' }}">
                        <div class="fixed-height">
                            {{ $item2->automatic_treatment ? 'Sim' : 'Não' }}
                        </div>
                        </td>
                    </tr>
                    <tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="nav-pagination">
        {!! $items->render() !!}
    </div>
    

</div>

<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="infoModalLabel">Informações</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Conteúdo será inserido aqui -->
      </div>
    </div>
  </div>
</div>



@stop

@section("javascript")
<script>
    
   $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    $(function () {
        $('[data-toggle="popover"]').popover()
    })
</script>
@stop