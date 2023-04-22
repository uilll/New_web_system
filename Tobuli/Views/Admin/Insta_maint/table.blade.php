@extends('Admin.Layouts.default')

@section('content')
<div class="screen">
    <div class="panel panel-default" id="table_{{ $section }}">
        <input type="hidden" name="sorting[sort_by]" value="{{ $items->sorting['sort_by'] }}" data-filter>
        <input type="hidden" name="sorting[sort]" value="{{ $items->sorting['sort'] }}" data-filter>
        
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-icons pull-right">
                @if( Auth::User()->perm('devices', 'edit') )
                <li role="presentation" class="">
                    <a href="javascript:" type="button" class="" data-modal="{{ $section }}_create" data-url="{{ route("admin.insta_maint.create") }}">
                        <i class="icon plus" title="Adicionar nova ocorrência"></i>
                    </a>
                </li>
                <li role="presentation" class="">
                    
                </li>
                @endif
            </ul>

            <div class="panel-title"><i class="icon check"></i> Serviços </div>

            <div class="panel-form">
                <div class="form-group search">
                    {!! Form::text('search_phrase', null, ['id' => 'search_admin_', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                    <span id='search_menu' style="display:none">Insta_maint</span>
                </div>
            </div>
        </div>
            
        <div class="table_error"></div>
        <div class="table-responsive">
            <table class="table table-list" data-toggle="multiCheckbox">
                <thead>
                <tr>
                    {!! tableHeader('Status') !!}
                    {!! tableHeader('Ordem de serviço') !!}
                    {!! tableHeader('Cliente') !!}
                    {!! tableHeader('Proprietário') !!}
                    {!! tableHeader('Contato') !!}
                    {!! tableHeader('Placa') !!}
                    {!! tableHeader('Veículo') !!}
                    {!! tableHeader('Rastreador') !!}
                    {!! tableHeader('Causa') !!}
                    {!! tableHeader('Técnico') !!}
                    {!! tableHeader('Valor (R$)') !!}
                    {!! tableHeader('Situação') !!}
                    {!! tableHeader('Programado para') !!}
                    {!! tableHeader('Realizado em') !!}
                    {!! tableHeader('Local instalação') !!}
                    {!! tableHeader('Inst./Manut.') !!}
                    {!! tableHeader('Observação') !!}
                    {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
                </tr>
                </thead>

                <tbody>
                    @foreach ($services as $item2)
                        <tr style="color: {{ $item2->active ? 'red' : ($item2->payable ? 'green':'gray') }}">
                            <td>
                                <span class="label label-sm label-{!! $item2->active ? 'success' : 'danger' !!}">
                                    {!! trans('validation.attributes.active') !!}
                                </span>
                            </td>
                            <td> {{$item2->os_number}}</td>
                            <td>{{$item2->customer}} </td>
                            <td>
                                {{ $item2->owner }} 
                            </td>
                            <td>
                                {{ $item2->contact }} 
                            </td>
                            <td>
                            <!--iframe style="border: 0; display: none" id="segue{{$item2->device_id}}" src="{{ route("devices.follow_map", ['id' => $item2->device_id]) }}" width="600px" height="400px" frameborder="0" scrolling="no"></iframe-->
                                {{ $item2->plate_number }}
                                <span id="fechar_mapa{{$item2->device_id}}" onclick="hidemap({{$item2->device_id}});" class="icon close" style="display: none"> Fechar mapa </span>
                            </td>
                            <td>
                                {{ $item2->vehicle_model }}
                            </td>
                            <td>
                                {{ $item2->tracker }}
                            </td>
                            <td>
                                {{ $item2->cause }}
                            </td>
                            <td>
                                {{ $item2->technician }}
                            </td>
                            <td>
                                {{ $item2->valor }}
                            </td>
                            <td>
                                {{ $item2->payable ? "Pago" : "À pagar" }}
                            </td>
                            <td>
                                {{ $item2->expected_date ? $item2->expected_date : 'Sem previsão' }}
                            </td>
                            <td>
                                {{ $item2->installation_date ? $item2->installation_date : 'Não realizada'  }}
                            </td>
                            <td>
                                {{ $item2->installation_location ? $item2->installation_location : 'Não instalado' }}
                            </td>
                            <td>
                                {{ $item2->maintenance ? 'Manutenção' : 'Instalação' }}
                            </td>
                            <td>
                                {{ $item2->obs}}
                            </td>
                            <td class="actions" style="min-width: 70px">
                                <button type="button" class="btn btn-link" data-toggle="modal" data-target="#open_os{{$item2->id}}">
                                  <i class="fas fa-file-signature fa-1x"></i>
                                </button>
                                @if (Auth::User()->perm('devices', 'edit') || Auth::User()->perm('devices', 'remove'))
                                    <div class="btn-group dropdown droparrow" data-position="fixed">
                                        <i class="btn icon edit" data-toggle="dropdown" aria-haspopup="true"
                                           aria-expanded="true"></i>
                                        <ul class="dropdown-menu">
                                            @if( Auth::User()->perm('devices', 'edit') )
                                                <li>
                                                    <a href="javascript:" data-modal="devices_edit"
                                                       data-url="{{ route("admin.insta_maint.edit", ['id' => $item2->id]) }}">
                                                        {{ trans('global.edit') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:" data-modal="devices_edit"
                                                       data-url="{{ route("admin.insta_maint.cancel", ['id' => $item2->id]) }}">
                                                        {{ trans('global.cancel') }}
                                                    </a>
                                                </li>
                                            @endif
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
                        </tr>
                        <tr>
                        
                        <!--modal apresentação de OS -->
                        <div class="modal fade" id="open_os{{$item2->id}}" tabindex="-1" role="dialog" aria-labelledby="TituloModalCentralizado{{$item2->id}}" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="TituloModalCentralizado">Ordem de Serviço nº {{$item2->os_number}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body" style="border: 3px solid #000;paddin: 10px; margin: 10px">
                                
                                <div style="border: 2px solid #000;paddin: 10px; margin: -10px">
                                    <div class="panel-default h-auto d-inline-block" id="table_'.$item->id.'" style="min-width: 595px; align: center;">                       
                                        <div class="w-25 p-3 row" style="display: block; min-height: 100px; min-width: 100%; justify-items: stretch;" >
                                            <div class="col-lg-6" style="height: 100px; float: left; min-width: 50%; width: 50%" align="center">
                                                <span style="min-width:100%; width:100%;font-size: 20px;">ORDEM DE SERVIÇO</span>
                                                <br>
                                                <img src="https://sistema.carseg.com.br/public/images/logo-main.png" alt="Logo CARSEG" width=280 height=80>
                                            </div>
                                            <div class="col-lg-6" style="border: 1px solid #000;float: right;min-width: 50%; width: 50%; min-height: 100px">
                                                Nº da OS: {{$item2->os_number}}<br>
                                                Data Prevista: {{$item2->expected_date}} <br>
                                                Data da execução: {{$item2->installation_date}} <br>
                                                Status da OS: {{$item2->active ? "À executar":"Executada"}}<br>
                                                Técnico: {{$item2->technician}}
                                            </div>
                                        </div>          
                                        <br>
                                        <br>
                                        <br>
                                        <br>
                                        
                                        <div class="row col-lg-12" style="margin-left: 2px; margin-right: 2px;margin-top: 20px; width: 100%;">
                                            <div style="min-width: 100%;width: 100%;border-bottom: 1px solid #000" align="center">
                                                <span style="font-size: 20px;" align="center">DADOS DO CLIENTE</span>
                                            </div>
                                            <div style="min-width:100%;" class="col-lg-10">
                                                <span style="font-size: 15px;">Nome Completo: {{$item2->customer}}</span>
                                            </div>
                                            <div style="min-width:100%;" class="col-lg-10">
                                                <span style="font-size: 15px;">Propretário: {{$item2->owner}}</span>
                                            </div>
                                            <div style="min-width:100%;" class="col-lg-10">
                                                <span style="font-size: 15px;">Endereço do serviço: {{$item2->address}}</span>
                                            </div>
                                            <div>
                                                <div class="col-lg-6">
                                                    <span style="font-size: 15px;">Telefone: {{$item2->contact}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 20px;">
                                            <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                                <span style="font-size: 20px;align: center;">DADOS DO VEÍCULO</span>
                                            </div>
                                            <div style="width: 100%; ">
                                                <div style="max-width: 30%;width: 20%; float: left  ">
                                                    <span style="font-size: 15px;align: center;">Placa: {{$item2->plate_number}}</span>
                                                </div>
                                                <div style="max-width: 30%;width: 40%; float: left ">
                                                    <span style="font-size: 15px;align: center;">Modelo: {{$item2->vehicle_model}}</span>
                                                </div>
                                                <div style="width: 30%;width: 20%; float: left  ">
                                                    <span style="font-size: 15px;align: center;">Cor: {{$item2->vehicle_color}}</span>
                                                </div>
                                                <div style="width: 30%;width: 20%; float: left  ">
                                                    <span style="font-size: 15px;align: center;">Ano: {{$item2->model_year}}</span>
                                                </div>
                                                
                                            </div>
                                            <br>
                                            <div style="width: 100%; ">
                                                <div style="max-width: 100%;width: 100%; float: left  ">
                                                    <span style="font-size: 15px;align: center;">Local Instalação: {{$item2->insta_loc}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 30px;">
                                            <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                                <span style="font-size: 20px;align: center;">SERVIÇOS PRESTADOS</span>
                                            </div>
                                            <div style="min-width: 100%; ">
                                                <div class="col-lg-3">
                                                    <span style="font-size: 15px;width: 20%; float: left ">Código: {{$item2->maintenance_code}}</span>
                                                </div>
                                                <div class="col-lg-6">
                                                    <span style="font-size: 15px;width: 50%; float: left ">Descrição: {{$item2->maintenance}}</span>
                                                </div>
                                                <div class="col-lg-3">
                                                    <span style="font-size: 15px;width: 20%; float: left ">Valor: {{$item2->valor}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row" style="margin-left: 2px; margin-right: 2px; margin-top: 50px">
                                            <div style="min-width: 100%; border-bottom: 1px solid #000" align="center">
                                                <span style="font-size: 20px;align: center;">PRODUTOS UTILIZADOS</span>
                                            </div>
                                            <div>
                                                <div style="width: 20%; float: left">
                                                    <span style="font-size: 15px;">Código:</span>
                                                </div>
                                                <div style="width: 50%; float: left">
                                                    <span style="font-size: 15px;">Descrição:</span>
                                                </div>
                                                <div style="width: 20%; float: left">
                                                    <span style="font-size: 15px;">Quantidade:</span>
                                                </div>
                                                <br>
                                                <div style="width: 20%; float: left">
                                                    <span style="font-size: 15px;">Valor Un.:</span>
                                                </div>
                                                <div style="width: 20%; float: left">
                                                    <span style="font-size: 15px;">Total:</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="margin-top: 50px;">
                                                <div style="width: 50%; float: left">
                                                    <span style="font-size: 15px;align: center;">Valor total da OS:</span>
                                                </div>
                                                <div style="width: 50%; float: left">
                                                    <span style="font-size: 15px;align: center;">Forma de pagamento: </span>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <br>
                                        <div style="margin-top: 10px;">
                                            <div class="col-lg-12" align="center" style="border-bottom: 1px solid #000;">
                                                <span style="font-size: 20px;">LAUDO TÉCNICO</span>
                                            </div>
                                            <div class="col-lg-6" style="width: 50%; float: left">
                                                <span style="font-size: 15px;">Data:</span>
                                            </div>
                                            <div class="col-lg-6" style="width: 50%; float: left">
                                                <span style="font-size: 15px;">Técnico: {{$item2->technician}}</span>
                                            </div>
                                            <br>
                                            <br>
                                            <div class="col-lg-12" style="width: 100%; height: 100px; float: left; border: 1px solid #000;margin-left: 2px; margin-right: 2px">
                                                <span style="font-size: 15px; min-width:100%">Descreva o laudo conforme o serviço prestado</span>
                                            </div>
                                        </div>
                                        <br>
                                        <br>
                                        <div class="row" style="margin-top: 100px; margin-left: 2px; margin-right: 2px">
                                            <div class="col-lg-12">
                                                <span style="font-size: 15px;min-width:100%">DECLARO QUE OS SERVIÇOS DESCRITOS NESTE RELATÓRIO FORAM PRESTADOS E DADOS COMO  ACEITOS POR MIM NESTA DATA ___ / ___ / ______</span>
                                            </div>
                                            <br>
                                            <div class="col-lg-12" align="center" style="margin-top: 30px">
                                                <span style="font-size: 15px;" >________________________________________</span>
                                            </div>
                                            <div class="col-lg-12" align="center">
                                                <span style="font-size: 15px;">{{$item2->owner}}</span>
                                            </div>
                                            <br>
                                            <div class="col-lg-12" align="center" style="margin-top: 30px">
                                                <span style="font-size: 15px;">________________________________________</span>
                                            </div>
                                            
                                            <div class="col-lg-12" align="center">
                                                <span style="font-size: 15px;">{{$item2->technician}}</span>
                                            </div>
                                            <br>
                                        </div>
    
                                    </div>
                                    </div>
                                
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button id="to_pdf" type="button" class="btn btn-secondary" onclick="print_modal({{$item2->id}})">Imprimir OS</button>
                                <a class="btn btn-secondary btn-lg active" href="{{route("admin.insta_maint.os", ['id' => $item2->id])}}">Print PDF</a>
                              </div>
                            </div>
                          </div>
                        </div>
                    @endforeach
                </tbody>
                
            </table>
            @if($valores['table_payable'])
                <div>
                    <!--Valor total dos serviços: R$ {{--$valores['soma_valores']--}} <br> -->
                    <!--Valor total pago: R$ {{--$valores['paid']--}} <br> -->
                    Valor com o técnico: R$ {{$valores['recei_from_cli']}} <br>
                    Valor total à pagar: R$ {{$valores['payable']}} <br>
                </div>
            @endif
        </div>
        <div class="nav-pagination">
            {!! $services->render() !!}
        </div>
        

    </div>
</div>
<div class="printable"></div>

@stop

@section("javascript")
<script src="https://kit.fontawesome.com/d23a99ac0c.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
@stop