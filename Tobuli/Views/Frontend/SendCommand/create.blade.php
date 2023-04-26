@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon send-command"></i> {!!trans('front.send_command')!!}
@stop

@section('body')
    <ul class="nav nav-tabs nav-default" role="tablist">
        <li class="active"><a href="#command-form-gprs" role="tab" data-toggle="tab">{!!trans('front.gprs')!!}</a></li>
        <li><a href="#command-form-sms" role="tab" data-toggle="tab">{!!trans('front.sms')!!}</a></li>
    </ul>

    {!!Form::open(['route' => 'send_command.store', 'method' => 'POST'])!!}
    {!!Form::hidden('id')!!}
    <div class="alert alert-success" role="alert" style="display: none;">{!!trans('front.command_sent')!!}</div>
    <div class="alert alert-danger main-alert" role="alert" style="display: none;"></div>
    
    <div class="tab-content">

        <div id="command-form-gprs" class="tab-pane active" data-url="{!!route('send_command.gprs')!!}">
            @if (!Auth::User()->perm('send_command', 'view'))
                <div class="alert alert-danger" role="alert">{{ trans('front.dont_have_permission') }}</div>
            @else
                <div class="form-group">
                    {!!Form::label('device_id', trans('validation.attributes.device_id').':')!!}
                    {!!Form::select('device_id', $devices_gprs, $device_id, ['class' => 'form-control', 'data-live-search' => true])!!}
                </div>
                
                @if (Auth::User()->isAdmin() or Auth::User()->isManager())
                    <div class="form-group send-command-type">
                        {!!Form::label('type', trans('validation.attributes.type').':')!!}
                        {!!Form::select('type', $commands, "custom", ['class' => 'form-control', 'id' =>'tipo'])!!}
                        
                    </div>
                    
                    @if ($protocol == 	'gt06')
                        <div id="gt06_commands" style="display: block">
                            {!!Form::label('person_', 'Comandos rápidos: ')!!}
                            {!!Form::select('person__', ['0'=> '', 
                                'Tempo de transmissão'=>'1 - Tempo de transmissão',
                                'Status posição'=>'2 - Status posição',
                                'Definir o filtro de dados de desvio estático'=>'3 - Definir o filtro de dados de desvio estático',
                                'Resetar'=>'4 - Resetar', 
                                'Status'=>'5 - Status', 
                                'Configurar Servidor'=>'6 - Configurar Servidor', 
                                'Configurar GMT'=>'7 - Configurar GMT', 
                                'Restaurar padrão de fábrica'=>'8 - Restaurar padrão de fábrica', 
                                'Requerer localiação via LBS'=> '9 - Requerer localiação via LBS', 
                                'Parâmetros'=>'10 - Parâmetros', 
                                'Status movimento'=>'11 - Status movimento',
                                'Configurar APN'=>'12 - Configurar APN', 
                                'automatic_apn' => '13 - APN automática',
                                'GPRS'=>'14 - GPRS', 
                                'Versão Firmware'=>'15 - Versão Firmware',  
                                'Intervalo de HeartBeat (ativo)'=>'16 - Intervalo de HeartBeat (ativo)', 
                                'Intervalo de envio GPS (Ign. Ligada e desligada)'=>'17 - Intervalo de envio GPS (Ign. Ligada e desligada)',
                                'Intervalo de distância para envio de dados GPS'=>'18 - Intervalo de distância para envio de dados GPS',
                                'Atualização de mudança de ângulo no deslocamento do veículo'=>'19 - Atualização de mudança de ângulo no deslocamento do veículo',
                                'Checar mudança da ignição (ACC)'=>'20 - Checar mudança da ignição (ACC)',
                                'Definir lotes de envio de dados do GPS'=>'21 - Definir lotes de envio de dados do GPS',
                                'Definir atraso da defesa'=>'22 - Definir atraso da defesa', 
                                'Tempo de detecção do sensor de vibração'=>'23 - Tempo de detecção do sensor de vibração', 
                                'Definir tempo de controle do GPS através do sensor de vibração'=>'24 - Definir tempo de controle do GPS através do sensor de vibração', 
                                'Configurar o alarme da vibração'=>'25 - Configurar o alarme da vibração',
                                'Configurar o alarme de bateria violada'=>'26 - Configurar o alarme de bateria violada',
                                'Configurar o alarme da tensão baixa na bateria interna'=>'27 - Configurar o alarme da tensão baixa na bateria interna', 
                                'Configurar o alarme da tensão baixa na bateria externa'=> '28 - Configurar o alarme da tensão baixa na bateria externa', 
                                'Configurar movimento indevido'=> '29 - Configurar movimento indevido', 
                                'Configurar excesso de velocidade'=> '30 - Configurar excesso de velocidade', 
                                'Configurar sensibilidade do sensor'=> '31 - Configurar sensibilidade do sensor', 
                                'Configurar estatísticas de quilometragem (Hodômetro)'=> '32 - Configurar estatísticas de quilometragem (Hodômetro)', 
                                'Configurar atualização da tensão da bateria externa'=>'33 - Configurar atualização da tensão da bateria externa', 
                                'Configurar proteção de baixa tensão para bateria externa (Modo avião)'=>'34 - Configurar proteção de baixa tensão para bateria externa (Modo avião)',
                                '[J16] - Ignicao_virtual'=>'35 - [ J16 ] - Ignição virtual[0], real[1]',
                                '[J16] - Solicitar_localizacao'=>'36 - [ J16 ] - Solicitar localização',
                                '[J16] - Mensagem_angulo'=>'37 - [ J16 ] - Mensagem por ângulo',
                                '[J16] - Sensibilidade / Níveis 1-6'=>'38 - [J16] - Sensibilidade / Níveis 1-6',

                                ], 
                                
                                null, 
                                ['id'=>'comando_personalisado','class' => 'form-control'])!!} 
                    </div>
                    @elseif($protocol == 'suntech')
                        <div id="suntech_commands" style="display: block">
                            {!!Form::label('person_', 'Comandos rápidos: ')!!}
                            {!!Form::select('person__', ['0'=> '', 
                                'Rede' =>'1- Parâmetros da rede',
                                'Requesitar status'=>'2 - Requesitar status',
                                'Tempo de envio'=>'3 - Tempo de envio',
                                'Requisitar posição'=>'4 - SUNTECH - Requisitar posição',
                                'Limpar relatórios e disabilitar saídas' => '5 - Limpar relatórios (posições na memória)',
                                'Parâmetos de eventos' => '6 - Ajustar parâmetros de eventos',
                                'Parâmetos de serviço' => '7 - Ajustar parâmetros de serviços',
                                'Parâmetos novos' => '8 - Ajustar parâmetros novos',
                                'SUNTECH - Iniciar contagem de pulso do  odômetro'=>'9 - SUNTECH - Iniciar contagem de pulso do  odômetro', 
                                'SUNTECH - Finalizar contagem de pulso do  odômetro'=>'10 - SUNTECH - Finalizar contagem de pulso do  odômetro',
                                'Desvio estático'=>'11 - Desvio estático'
                                ]
                                
                                , null, ['id'=>'comando_personalisado_2','class' => 'form-control'])!!} 
                        </div>
                    @elseif($protocol == 'easytrack')
                        <div id="easytrack_commands" style="display: block">
                            {!!Form::label('person_', 'Comandos rápidos: ')!!}
                            {!!Form::select('person__', ['Reiniciar'=> '1- Reiniciar',
                                    'Posição' =>'2- Requisitar posição',
                                    'Bloquear'=>'3 - Bloquear',
                                    'Desbloquear'=>'4 - Desbloquear',
                                    'Bloqueio progressivo'=>'5 - Bloqueio progressivo',
                                    'Bloqueio geral'=>'6 - Bloqueio geral',
                                    'APN'=>'7 - APN',
                                    'Tempo de transmissão'=>'5 - Tempo de transmissão',
                                    'tracking_and_monitoring'=>'6 - IP/PORTA e tempo Rastreamento',
                                    'Sensibilidade'=>'7 - Sensibilidade do sensor Motion',
                                    'Sleep_mode'=>'8 - Modo Sleep',
                                    'Hodometro'=>'9 - Hodometro',
                                    'sms_content_iccid'=>'10 - Requesitar ICCID',
                                ]
                                , null, ['id'=>'comando_personalisado_3','class' => 'form-control'])!!} 
                        </div>
                    @endif
                    
                    <!-- FORMULÁRIO TEMPO DE TRANSMISSÃO-->
                    <div id="form_tempo_transmissao" style="display:  none">
                        {!!Form::label('form_tempo_transmissao_T1', 'Tempo de transmissão com ignição ligada: ')!!}
                        {!!Form::text('response_', 120, ['class' => 'form-control', 'id' => 'tempo_transmissao_T1', 'placeholder'=>'120'])!!}
                        {!!Form::label('form_tempo_transmissao_T2', 'Tempo de transmissão com ignição desligada: ')!!}
                        {!!Form::text('response_', 1800, ['class' => 'form-control', 'id' => 'tempo_transmissao_T2', 'placeholder'=>'1800'])!!}
                        </br>
                        <button type="button" class="btn btn-action" id="check_transmition_time">Checar tempo de transmissão</button> 
                    </div>
                    
                    <!-- FORMULÁRIO GPRS-->
                    <div id="form_gprs" style="display:  none">
                        Instalação do recurso posteriormente pois não tem muita necessidade para tal recurso.
                        <!-- Instalação posterior, sem muita necessidade para tal recurso.
                        
                        {!!Form::label('form_tempo_transmissao_T1', 'Tempo de transmissão com ignição ligada: ')!!}
                        {!!Form::text('response_', null, ['class' => 'form-control', 'id' => 'tempo_transmissao_T1', 'placeholder'=>'120'])!!}
                        {!!Form::label('form_tempo_transmissao_T2', 'Tempo de transmissão com ignição desligada: ')!!}
                        {!!Form::text('response_', null, ['class' => 'form-control', 'id' => 'tempo_transmissao_T2', 'placeholder'=>'1800'])!!}
                        </br>
                        <button type="button" class="btn btn-action" id="check_transmition_time">Checar tempo de transmissão</button> -->
                    </div>
                    
                    <!-- FORMULÁRIO APN-->
                    <div id="form_apn" style="display:  none">   
                        {!!Form::label('form_apn_apnname', 'Nome da APN: ')!!}
                        {!!Form::text('response_', 'm2data.algar.br', ['class' => 'form-control', 'id' => 'form_apn_apnname', 'placeholder'=>'Nome da APN'])!!}
                        {!!Form::label('form_apn_user', 'Usuário da APN: ')!!}
                        {!!Form::text('response_', 'algar', ['class' => 'form-control', 'id' => 'form_apn_user', 'placeholder'=>'Usuário da APN'])!!}
                        {!!Form::label('form_apn_pwd', 'Senha da APN: ')!!}
                        {!!Form::text('response_', 'algar', ['class' => 'form-control', 'id' => 'form_apn_pwd', 'placeholder'=>'Senha da APN'])!!}
                        </br>
                        <button type="button" class="btn btn-action" id="check_apn_parameters">Checar parâmetros da APN</button>
                    </div>
                    <!-- FORMULÁRIO SERVER-->
                    <div id="form_server" style="display:  none">   
                        {!!Form::label('form_server_mode', 'Tipo de endereçamento: ')!!}
                        {!!Form::select('form_server_mode_', array('1' => 'Nome do domínio', '0' => 'Endereço IP'), '0',['class' => 'form-control', 'id' => 'form_server_mode_'])!!}
                        {!!Form::label('form_server_ip_domain', 'IP ou nome do domínio: ')!!}
                        {!!Form::text('form_server_ip_domain_', null, ['class' => 'form-control', 'id' => 'form_server_ip_domain', 'placeholder'=>'IP ou nome do domínio'])!!}
                        {!!Form::label('form_server_port', 'Porta: ')!!}
                        {!!Form::text('form_server_port_', null, ['class' => 'form-control', 'id' => 'form_server_port', 'placeholder'=>'Porta'])!!}
                        {!!Form::label('form_server_protocol', 'Tipo de endereçamento: ')!!}
                        {!!Form::select('form_server_protocol_', array('1' => 'UDP', '0' => 'TCP'), '0',['class' => 'form-control', 'id' => 'form_server_protocol_'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_server_parameters">Checar parâmetros do servidor</button>
                    </div>
                    
                    <!-- FORMULÁRIO GMT-->
                    <div id="form_GMT" style="display:  none">   
                        {!!Form::label('form_GMT_fuso', 'Tipo de endereçamento: ')!!}
                        {!!Form::select('form_GMT_fuso_', array('E' => 'E', 'W' => 'W'), 'W',['class' => 'form-control', 'id' => 'form_GMT_fuso_'])!!}
                        {!!Form::label('form_GMT_zona', 'Zona   : ')!!}
                        {!!Form::select('form_GMT_zona_', array('0' => '0', '1' => '1', '2' => '2', '3' => '3', '5' => '5', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12'), '0',['class' => 'form-control', 'id' => 'form_GMT_zona_'])!!}
                        {!!Form::label('form_GMT_half_time', 'Zona de Meio tempo: ')!!}
                        {!!Form::select('form_GMT_half_time_', array('0' => '0', '15' => '15', '30' => '30', '45' => '45'), '0',['class' => 'form-control', 'id' => 'form_GMT_half_time_'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_GMT_parameters">Checar parâmetros GMT</button>
                    </div>
                    
                    <!-- FORMULÁRIO factory reset-->
                    <div id="form_factory" style="display:  none">                           
                        {!!Form::label('form_factory', 'A restauração irá setar todos os parâmetros de fábrica exceto o nome do domínio, APN e nome de domínio bloqueado.')!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="restore_factory_parameters">Restaurar todos os parâmetros para o padrão de fábrica</button>
                    </div>

                    <!-- FORMULÁRIO APN automática-->
                    <div id="form_automatic_apn" style="display:  none">                           
                        {!!Form::label('automatic_apn', 'A restauração irá setar todos os parâmetros de fábrica exceto o nome do domínio, APN e nome de domínio bloqueado.')!!}
                        {!!Form::select('automatic_apn', array('ON' => 'ON', 'OFF' => 'OFF'), 'OFF',['class' => 'form-control', 'id' => 'automatic_apn'])!!} 
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_automatic_apn_parameters">Consulta de status da APN automática</button>
                    </div>
                    
                    <!-- FORMULÁRIO HeartBeat-->
                    <div id="form_HeartBeat" style="display:  none">   
                        {!!Form::label('form_HeartBeat_T1', 'Tempo para informar que está ativo: ')!!}
                        {!!Form::text('form_HeartBeat_T1_', 300, ['class' => 'form-control', 'id' => 'form_HeartBeat_T1', 'placeholder'=>'Com a ignição ligada (1 ~19 minutos)'])!!}
                        {!!Form::label('form_HeartBeat_T2', 'Tempo para informar que está ativo: ')!!}
                        {!!Form::text('form_HeartBeat_T2_', 300, ['class' => 'form-control', 'id' => 'form_HeartBeat_T2', 'placeholder'=>'Com a ignição desligada (1 ~19 minutos)'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_HeartBeat">Checar parâmetros HeartBeat</button>
                    </div>
                    
                    <!-- FORMULÁRIO Envio GPS-->
                    <div id="form_envio_GPS" style="display:  none">   
                        {!!Form::label('form_envio_GPS_T1', 'Tempo com ignição ligada: ')!!}
                        {!!Form::text('form_envio_GPS_T1_', null, ['class' => 'form-control', 'id' => 'form_envio_GPS_T1', 'placeholder'=>'Com a ignição ligada (5 ~ 18.000 segundos, 0 para não atualizar)'])!!}
                        {!!Form::label('form_envio_GPS_T2', 'Tempo com ignição desligada: ')!!}
                        {!!Form::text('form_envio_GPS_T2', null, ['class' => 'form-control', 'id' => 'form_envio_GPS_T2', 'placeholder'=>'Com a ignição desligada (5 ~ 18.000 segundos)'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_envio_GPS">Checar parâmetros HeartBeat</button>
                    </div>
                    
                    <!-- FORMULÁRIO Distância GPS-->
                    <div id="form_dist_GPS" style="display:  none">   
                        {!!Form::label('form_dist_GPS_D', 'Intervalo da distância para o envio de dados do GPS: ')!!}
                        {!!Form::text('form_dist_GPS_D', null, ['class' => 'form-control', 'id' => 'form_dist_GPS_D', 'placeholder'=>'50 ~ 10.000 m ou 0 metros, padrão é 300'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_dist_GPS">Checar intervalo de distância</button>
                    </div>
                    
                    <!--FORMULÁRIO Atualização do ângulo de deslocamento-->
                    <div id="form_mudan_angu" style="display:  none">   
                        {!!Form::label('form_mudan_angu_x', 'Ativar/desativar a atualização de direção: ')!!}
                        {!!Form::select('form_mudan_angu_x', array('ON' => 'ON', 'OFF' => 'OFF'), 'ON',['class' => 'form-control', 'id' => 'form_mudan_angu_x'])!!} 
                        {!!Form::label('form_mudan_angu_A', 'Intervalo do ângulo: ')!!}
                        {!!Form::text('form_mudan_angu_A', null, ['class' => 'form-control', 'id' => 'form_mudan_angu_A', 'placeholder'=>'5 ~ 180 graus, padrão é 30 graus'])!!}
                        {!!Form::label('form_mudan_angu_B', 'Tempo de detecção: ')!!}
                        {!!Form::text('form_mudan_angu_B', null, ['class' => 'form-control', 'id' => 'form_mudan_angu_B', 'placeholder'=>'2 ~ 5 segundos, padrão é 3 segundos'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_mudan_angu">Checar intervalo de distância</button>
                    </div>
                    
                    <!-- Intervalo de distância -->
                    <div id="form_mudan_ign" style="display:  none">   
                        {!!Form::label('form_mudan_ign_A', 'Intervalo da distância para o envio de dados do GPS: ')!!}
                        {!!Form::text('form_mudan_ign_A', "ON", ['class' => 'form-control', 'id' => 'form_mudan_ign_A'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_mudan_ign">Checar mudança ignição</button>
                    </div>
                    
                    <!-- Lote de mensagens-->
                    <div id="form_lotes_GPS" style="display:  none">   
                        {!!Form::label('form_lotes_GPS_A', 'Ativar ou desativar a função lote: ')!!}
                        {!!Form::text('form_lotes_GPS_A', "OFF", ['class' => 'form-control', 'id' => 'form_lotes_GPS_A', 'placeholder'=>'padrão "OFF"'])!!}
                        {!!Form::label('form_lotes_GPS_N', 'Número de mensagens em lote: ')!!}
                        {!!Form::text('form_lotes_GPS_N', "10", ['class' => 'form-control', 'id' => 'form_lotes_GPS_N', 'placeholder'=>'De 1 ~ 50 mensagens por lote'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_lotes_GPS">Checar quantidade mensagens por lote </button>
                    </div>
                    
                    <!-- Atraso da defesa -->
                    <div id="form_atraso_defesa" style="display:  none">   
                        {!!Form::label('form_atraso_defesa_A', 'Tempo de atraso na defesa: ')!!}
                        {!!Form::text('form_atraso_defesa_A', "10", ['class' => 'form-control', 'id' => 'form_atraso_defesa_A', 'placeholder'=>'1 ~ 60 minutos, padrão 10 minutos'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_atraso_defesa">Checar atraso de defesa </button>
                    </div>
                    
                    <!-- Tempo de detecção do sensor de vibração -->
                    <div id="form_tempo_vibracao" style="display:  none">   
                        {!!Form::label('form_tempo_vibracao_A', 'Tempo para detecção: ')!!}
                        {!!Form::text('form_tempo_vibracao_A', "10", ['class' => 'form-control', 'id' => 'form_tempo_vibracao_A', 'placeholder'=>'10 ~ 300 segundos, padrão 10 segundos'])!!}
                        {!!Form::label('form_tempo_vibracao_B', 'Tempo do atraso do alerta: ')!!}
                        {!!Form::text('form_tempo_vibracao_B', "30", ['class' => 'form-control', 'id' => 'form_tempo_vibracao_B', 'placeholder'=>'10 ~ 300 segundos, padrão 30 segundos'])!!}
                        {!!Form::label('form_tempo_vibracao_C', 'Intervalo do alerta: ')!!}
                        {!!Form::text('form_tempo_vibracao_C', "1", ['class' => 'form-control', 'id' => 'form_tempo_vibracao_C', 'placeholder'=>'1 ~ 3000 minutos, padrão 1 minuto'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_tempo_vibracao">Checar tempo de detecação da vibração </button>
                    </div>
                    
                    <!-- Tempo de controle do GPS pelo sensor de vibração -->
                    <div id="form_controle_GPS_vibracao" style="display:  none">   
                        {!!Form::label('form_controle_GPS_vibracao_A', 'Tempo para detecção: ')!!}
                        {!!Form::text('form_controle_GPS_vibracao_A', "5", ['class' => 'form-control', 'id' => 'form_controle_GPS_vibracao_A', 'placeholder'=>'0 ~ 300 minutos, 0 significa GPS sempre trabalhando, padrão 5 minutos.'])!!}
                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_controle_GPS_vibracao">Checar parâmetros</button>
                    </div>
                    
                    <!-- Filtro de desvio de dados estáticos -->
                    <div id="form_filtro_desvio_estatico" style="display:  none">   
                        {!!Form::label('form_filtro_desvio_estatico_A', 'Ativar/desativar o filtro: ')!!}
                        {!!Form::text('form_filtro_desvio_estatico_A', "ON", ['class' => 'form-control', 'id' => 'form_filtro_desvio_estatico_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_filtro_desvio_estatico_B', 'Máxima distância para filtrar: ')!!}
                        {!!Form::text('form_filtro_desvio_estatico_B', "10", ['class' => 'form-control', 'id' => 'form_filtro_desvio_estatico_B', 'placeholder'=>'10 ~ 1000 m, padrão 100 m'])!!}
                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_filtro_desvio_estatico">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme do sensor de vibração -->
                    <div id="form_alarme_vibracao" style="display:  none">   
                        {!!Form::label('form_alarme_vibracao_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_alarme_vibracao_A', "ON", ['class' => 'form-control', 'id' => 'form_alarme_vibracao_A', 'placeholder'=>'ON ou OFF, padrão: "OFF"'])!!}
                        {!!Form::label('form_alarme_vibracao_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_alarme_vibracao_M', "2", ['class' => 'form-control', 'id' => 'form_alarme_vibracao_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}
                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_alarme_vibracao">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme bateria externa violada -->
                    <div id="form_alarme_bat_ext_vio" style="display:  none">   
                        {!!Form::label('form_alarme_bat_ext_vio_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_alarme_bat_ext_vio_A', "ON", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_vio_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_alarme_bat_ext_vio_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_alarme_bat_ext_vio_M', "2", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_vio_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}
                        {!!Form::label('form_alarme_bat_ext_vio_T1', 'Tempo de detecção: ')!!}
                        {!!Form::text('form_alarme_bat_ext_vio_T1', "2", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_vio_T1', 'placeholder'=>'2 ~ 60 segundos, padrão 5 segundos'])!!}
                        {!!Form::label('form_alarme_bat_ext_vio_T2', 'Tempo mínimo de carregamento: ')!!}
                        {!!Form::text('form_alarme_bat_ext_vio_T2', "2", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_vio_T2', 'placeholder'=>'1 ~ 3600 segundos, padrão 1 segundos'])!!}
                        {!!Form::label('form_alarme_bat_ext_vio_T3', 'Tempo de proibição na transição de ignição de ON para OFF: ')!!}
                        {!!Form::text('form_alarme_bat_ext_vio_T3', "2", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_vio_T3', 'placeholder'=>'0 ~ 3600 segundos, padrão 0 segundos'])!!}
                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_alarme_bat_ext_vio">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme bateria interna com tensão baixa -->
                    <div id="form_alarme_bat_int_baixa_tensao" style="display:  none">   
                        {!!Form::label('form_alarme_bat_int_baixa_tensao_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_alarme_bat_int_baixa_tensao_A', "ON", ['class' => 'form-control', 'id' => 'form_alarme_bat_int_baixa_tensao_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_alarme_bat_int_baixa_tensao_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_alarme_bat_int_baixa_tensao_M', "0", ['class' => 'form-control', 'id' => 'form_alarme_bat_int_baixa_tensao_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}   
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_alarme_bat_int_baixa_tensao">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme bateria externa com tensão baixa -->
                    <div id="form_alarme_bat_ext_baixa_tensao" style="display:  none">   
                        {!!Form::label('form_alarme_bat_ext_baixa_tensao_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_alarme_bat_ext_baixa_tensao_A', "ON", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_baixa_tensao_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_alarme_bat_ext_baixa_tensao_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_alarme_bat_ext_baixa_tensao_M', "0", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_baixa_tensao_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}
                        {!!Form::label('form_alarme_bat_ext_baixa_tensao_N1', 'Tensão mínima: ')!!}
                        {!!Form::text('form_alarme_bat_ext_baixa_tensao_N1', "128", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_baixa_tensao_N1', 'placeholder'=>'10 ~ 360 V, padrão "128"'])!!}
                        {!!Form::label('form_alarme_bat_ext_baixa_tensao_N2', 'Tensão máxima: ')!!}
                        {!!Form::text('form_alarme_bat_ext_baixa_tensao_N2', "128", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_baixa_tensao_N2', 'placeholder'=>'10 ~ 360 V, padrão "138"'])!!}
                        {!!Form::label('form_alarme_bat_ext_baixa_tensao_T', 'Tempo de verificação: ')!!}
                        {!!Form::text('form_alarme_bat_ext_baixa_tensao_T', "10", ['class' => 'form-control', 'id' => 'form_alarme_bat_ext_baixa_tensao_T', 'placeholder'=>'1 ~ 300  segundos, padrão "10"'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_alarme_bat_ext_baixa_tensao">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme movimento indevido -->
                    <div id="form_mov_ind" style="display:  none">   
                        {!!Form::label('form_mov_ind_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_mov_ind_A', "ON", ['class' => 'form-control', 'id' => 'form_mov_ind_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_mov_ind_R', 'Raio: ')!!}
                        {!!Form::text('form_mov_ind_R', "300", ['class' => 'form-control', 'id' => 'form_mov_ind_R', 'placeholder'=>'100 ~ 1000 m, padrão "300"'])!!}
                        {!!Form::label('form_mov_ind_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_mov_ind_M', "0", ['class' => 'form-control', 'id' => 'form_mov_ind_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_mov_ind">Checar parâmetros</button>
                    </div>
                    
                    <!-- Alarme excesso de velocidade -->
                    <div id="form_exc_vel" style="display:  none">   
                        {!!Form::label('form_exc_vel_A', 'Ativar/desativar o alarme: ')!!}
                        {!!Form::text('form_exc_vel_A', "ON", ['class' => 'form-control', 'id' => 'form_exc_vel_A', 'placeholder'=>'ON ou OFF, padrão: "ON"'])!!}
                        {!!Form::label('form_exc_vel_B', 'Intervalo de detecção: ')!!}
                        {!!Form::text('form_exc_vel_B', "5", ['class' => 'form-control', 'id' => 'form_exc_vel_B', 'placeholder'=>'5 ~ 600 segundos, padrão "20"'])!!}
                        {!!Form::label('form_exc_vel_C', 'Limite de velocidade: ')!!}
                        {!!Form::text('form_exc_vel_C', "80", ['class' => 'form-control', 'id' => 'form_exc_vel_C', 'placeholder'=>'1 ~ 255 km/h, padrão "50 km/h"'])!!}
                        {!!Form::label('form_exc_vel_M', 'Forma de envio do alarme: ')!!}
                        {!!Form::text('form_exc_vel_M', "0", ['class' => 'form-control', 'id' => 'form_exc_vel_M', 'placeholder'=>'0: GPRS, 1: SMS+GPRS, 2: GPRS+SMS+phone call, 3: GPRS+call'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_exc_vel">Checar parâmetros</button>
                    </div>
                    
                    <!-- Sensibilidade do sensor de vibração -->
                    <div id="form_sen_sen" style="display:  none">   
                        {!!Form::label('form_sen_sen_A', 'Sensibilidade: ')!!}
                        {!!Form::text('form_sen_sen_A', "2", ['class' => 'form-control', 'id' => 'form_sen_sen_A', 'placeholder'=>'1 ~5, padrão 2'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_sen_sen">Checar parâmetros</button>
                    </div>
                    
                    <!-- Configurar Odômetro -->
                    <div id="form_odom" style="display:  none">   
                        {!!Form::label('form_odom_A', 'Ativar/desativar o Hodômetro: ')!!}
                        {!!Form::text('form_odom_A', "ON", ['class' => 'form-control', 'id' => 'form_odom_A', 'placeholder'=>'ON ou OFF, padrão: "OFF"'])!!}
                        {!!Form::label('form_odom_B', 'Valor do Hodômetro: ')!!}
                        {!!Form::text('form_odom_B', "0", ['class' => 'form-control', 'id' => 'form_odom_B', 'placeholder'=>'0 ~ 999999 km, padrão "0"'])!!}
                        {!!Form::label('form_odom_K', 'Coeficiente de prporcionalidade: ')!!}
                        {!!Form::text('form_odom_K', "1000", ['class' => 'form-control', 'id' => 'form_odom_K', 'placeholder'=>'1000 ~ 1200, padrão não tem'])!!}                        
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_odom">Checar parâmetros</button>
                    </div>
                    
                    <!-- Configurar checar tensão da bateria externa -->
                    <div id="form_bat_ext_upd" style="display:  none">   
                        {!!Form::label('form_bat_ext_upd_A', 'Ativar/desativar atualização da tensão: ')!!}
                        {!!Form::text('form_bat_ext_upd_A', "ON", ['class' => 'form-control', 'id' => 'form_bat_ext_upd_A', 'placeholder'=>'ON ou OFF, padrão: "OFF"'])!!}
                        {!!Form::label('form_bat_ext_upd_T', 'Intervalo de atualização: ')!!}
                        {!!Form::text('form_bat_ext_upd_T', "600", ['class' => 'form-control', 'id' => 'form_bat_ext_upd_T', 'placeholder'=>'5 ~ 3600 segundos, padrão "600"'])!!}
                        {!!Form::label('form_odom_K', 'Coeficiente de prporcionalidade: ')!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_bat_ext_upd">Checar parâmetros</button>
                    </div>
                    
                    <!-- Configurar checar tensão da bateria externa -->
                    <div id="form_prot_bat_ext" style="display:  none">   
                        {!!Form::label('form_prot_bat_ext_A', 'Ativar/desativar o modo avião: ')!!}
                        {!!Form::text('form_prot_bat_ext_A', "ON", ['class' => 'form-control', 'id' => 'form_prot_bat_ext_A', 'placeholder'=>'ON ou OFF, padrão: "OFF"'])!!}
                        {!!Form::label('form_prot_bat_ext_M', 'Meio de envio do alerta: ')!!}
                        {!!Form::text('form_prot_bat_ext_M', "0", ['class' => 'form-control', 'id' => 'form_prot_bat_ext_M', 'placeholder'=>'0 ～ 1 0：GPRS 1：SMS+GPRS, padrão "0"'])!!}
                        {!!Form::label('form_prot_bat_ext_N', 'Tensão de referência: ')!!}
                        {!!Form::text('form_prot_bat_ext_N', "125", ['class' => 'form-control', 'id' => 'form_prot_bat_ext_N', 'placeholder'=>'10 ~ 360 V, padrão "125"'])!!}
                        {!!Form::label('form_prot_bat_ext_T', 'Tensão de referência: ')!!}
                        {!!Form::text('form_prot_bat_ext_T', "10", ['class' => 'form-control', 'id' => 'form_prot_bat_ext_T', 'placeholder'=>'1 ~ 300 segundos, padrão "10"'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_prot_bat_ext">Checar parâmetros</button>
                    </div>
                    
                    <!-- Tempo de transmissão da Suntech -->
                    <div id="form_2_tem_trans" style="display:  none">   
                        {!!Form::label('form_2_tem_trans_T1', 'Tempo de transmissão veículo parado: ')!!}
                        {!!Form::text('form_2_tem_trans_T1', "1800", ['class' => 'form-control', 'id' => 'form_2_tem_trans_T1', 'placeholder'=>'0 ~ 86400 segundos'])!!}
                        {!!Form::label('form_2_tem_trans_T2', 'Tempo de transmissão veículo em movimento: ')!!}
                        {!!Form::text('form_2_tem_trans_T2', "120", ['class' => 'form-control', 'id' => 'form_2_tem_trans_T2', 'placeholder'=>'0 ~ 60000 segundos'])!!}
                        {!!Form::label('form_2_tem_trans_T3', 'Tempo de transmissão emergência: ')!!}
                        {!!Form::text('form_2_tem_trans_T3', "60", ['class' => 'form-control', 'id' => 'form_2_tem_trans_T3', 'placeholder'=>'0 ~ 9999 segundos'])!!}
                        {!!Form::label('form_2_tem_trans_A1', 'Tentativas de envio antes do reconhecimento do servidor: ')!!}
                        {!!Form::text('form_2_tem_trans_A1', "3", ['class' => 'form-control', 'id' => 'form_2_tem_trans_A1', 'placeholder'=>'0 ~ 9999 segundos'])!!}
                        {!!Form::label('form_2_tem_trans_SND_DIST', 'Intervalo de distância para o envio do relatório de status: ')!!}
                        {!!Form::text('form_2_tem_trans_SND_DIST', "0", ['class' => 'form-control', 'id' => 'form_2_tem_trans_SND_DIST', 'placeholder'=>'0~60000 m, 0 - desabilita função'])!!}
                        {!!Form::label('form_2_tem_trans_T4', 'Tempo de envio para o sinal de funcionamento (keep Alive): ')!!}
                        {!!Form::text('form_2_tem_trans_T4', "0", ['class' => 'form-control', 'id' => 'form_2_tem_trans_T4', 'placeholder'=>'0~60000 m, 0 - desabilita função'])!!}
                        {!!Form::label('form_2_tem_trans_SMS_T1', 'Tempo de envio por SMS veículo parado: ')!!}
                        {!!Form::text('form_2_tem_trans_SMS_T1', "0", ['class' => 'form-control', 'id' => 'form_2_tem_trans_SMS_T1', 'placeholder'=>'0~86400 segundos, 0 - desabilita função', 'readonly'=>'true'])!!}
                        {!!Form::label('form_2_tem_trans_SMS_T2', 'Tempo de envio por SMS veículo em movimento: ')!!}
                        {!!Form::text('form_2_tem_trans_SMS_T2', "0", ['class' => 'form-control', 'id' => 'form_2_tem_trans_SMS_T2', 'placeholder'=>'0~60000 segundos, 0 - desabilita função', 'readonly'=>'true'])!!}
                        {!!Form::label('form_2_tem_trans_SMS_PACK_NO', 'Relatório em pacote: ')!!}
                        {!!Form::text('form_2_tem_trans_SMS_PACK_NO', "0", ['class' => 'form-control', 'id' => 'form_2_tem_trans_SMS_PACK_NO', 'placeholder'=>'0~60000 segundos, 0 - desabilita função', 'readonly'=>'true'])!!}
                        </br>
                        </br>
                        <button type="button" class="btn btn-action" id="check_prot_bat_ext">Checar parâmetros</button>
                    </div>
                    
                    <div id="comandos_" class="row attributes">                
                    </div>     
                @else
                    @if($protocol == 'easytrack')
                    <div class="form-group send-command-type" style="display: none">
                        {!!Form::label('type', trans('validation.attributes.type').':')!!}
                        {!!Form::select('type', $commands, null, ['class' => 'form-control'])!!}
                        
                    </div>
                    <div id="easytrack_commands" style="display: block">
                            {!!Form::label('person_', 'Comandos rápidos: ')!!}
                            {!!Form::select('person__', ['0'=>'Selecione',
                                    'Bloquear'=>'1- Bloquear',
                                    'Desbloquear'=>'2 - Desbloquear',
                                ]
                                , null, ['id'=>'comando_personalisado_3','class' => 'form-control'])!!} 
                    </div>
                    <div id="comandos_" class="row attributes" style="display: none">                
                    </div>
                    @else
                    <div class="form-group send-command-type">
                        {!!Form::label('type', trans('validation.attributes.type').':')!!}
                        {!!Form::select('type', $commands, null, ['class' => 'form-control'])!!}
                        
                    </div>
                    @endif
                @endif
                @if (Auth::User()->isAdmin() or Auth::User()->isManager())
                    <div>
                        {!!Form::label('response_', 'Resposta: ')!!}
                        {!!Form::textarea('response_', null, ['class' => 'form-control', 'id' => 'response_', 'style'=> 'height: 60px ', 'readonly'])!!}
                    </div>
                @endif
            @endif
        </div>
        
        <div id="command-form-sms" class="tab-pane" data-url="{!!route('send_command.store')!!}">
            @if (!Auth::User()->isAdmin())
                <div class="alert alert-danger" role="alert">{!!trans('front.sms_gateway_disabled')!!}</div>
            @else
                @if (empty($devices_sms))
                    <div class="alert alert-danger" role="alert">{!!trans('front.no_devices_with_sim_number')!!}</div>
                @endif

                <div class="form-group">
                    {!!Form::label('devices', trans('validation.attributes.devices').'*:')!!}
                    @if (empty($devices_sms))
                        {!!Form::text('devices[]', null, ['class' => 'form-control', 'disabled' => 'disabled'])!!}
                    @else
                        {!!Form::select('devices[]', $devices_sms, null, ['class' => 'form-control', 'multiple' => 'multiple', 'data-live-search' => true])!!}
                    @endif
                    {!!Form::hidden('devices_fake')!!}
                    <small>{!!trans('front.add_sim_number_info')!!}</small>
                </div>

                <div class="form-group">
                    {!!Form::label('sms_template_id', trans('validation.attributes.sms_template_id').':')!!}
                    {!!Form::select('sms_template_id', $sms_templates, null, ['class' => 'form-control', 'data-url' => route('user_sms_templates.get_message')])!!}
                    <small>{!!trans('front.add_sms_template_info')!!}</small>
                </div>

                <div class="form-group">
                    {!!Form::label('message', trans('validation.attributes.message').'*:')!!}
                    {!!Form::textarea('message_sms', null, ['class' => 'form-control', 'rows' => 3])!!}
                    {!!Form::hidden('message_fake')!!}
                </div>

                <div class="send_command_result" style="display: none;">
                    <div>
                        <p>{!!trans('front.get_request')!!}:</p>
                        <p class="get_request result_parse"></p>
                    </div>
                    <div>
                        <p>{!!trans('front.response')!!}:</p>
                        <p class="get_result result_parse"></p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    {!!Form::close()!!}
    <script>
        $(document).ready(function() {
            $('#send_command select[name="type"]').trigger('change');
            $('#send_command select[name="device_id"]').trigger('change');
        });

        if ( typeof _static_send_command === "undefined" ) {
            var _static_send_command = true;

            var sendCommands = new Commands();

            $(document).on('change', '#send_command select[name="type"]', function() {
                var type = $(this).val();

                sendCommands.buildAttributes(type, $('#send_command .attributes'));
            });

            $(document).on('change', '#sms_template_id', function() {
                var url = $(this).data('url');
                var val = $(this).val();
                if (val == 0) {
                    $('#command-form-sms input[name="message_sms"]').val('');
                    return;
                }
                $.ajax({
                    type: 'POST',
                    dataType: "html",
                    data: {
                        id: val
                    },
                    url: url,
                    beforeSend: function() {
                        $('#sms_template_id, #command-form-sms textarea[name="message_sms"]').attr('disabled', 'disabled');
                    },
                    success: function (res) {
                        $('#command-form-sms textarea[name="message_sms"]').val(res);
                    },
                    complete: function() {
                        $('#sms_template_id, #command-form-sms textarea[name="message_sms"]').removeAttr('disabled').selectpicker('refresh');
                    }
                });
            });

            $(document).on('change', '#send_command select[name="device_id"]', function()
            {
                sendCommands.getDeviceCommands(
                        $(this).val(),
                        function(){
                            $(this).attr('disabled', 'disabled');
                            loader.add( $('#send_command .send-command-type') );
                        },
                        function(){
                            sendCommands.buildTypesSelect( $('#send_command .send-command-type select') );
                            $(this).removeAttr('disabled');
                            loader.remove( $('#send_command .send-command-type') );
                        }
                );
            });

            $(document).on('click', '#send_command button.btn.command-save', function() {
                
                //console.log($('#command_ .modal-body ').innerHTML());
                var message_text= $('#message_sms .tab-pane.active').data('url');
                var url = $('#send_command .tab-pane.active').data('url');
                $('#send_command form').attr('action', url);
                $('#send_command button.update_hidden').trigger('click');
                $('#send_command .alert-success').css('display', 'none');
            });

            $(document).on('send_command', function(e, res) {
                if (res.errors) {
                    $('#send_command .alert-success').css('display', 'none');
                    $('#send_command .alert-danger.main-alert').css('display', 'none');
                }
                else if (res.error) {
                    $('#send_command .alert-success').css('display', 'none');
                    $('#send_command .alert-danger.main-alert').css('display', 'block').html(res.error);
                }
                else {
                    $('#send_command .alert-danger.main-alert').css('display', 'none');
                    $('#send_command .alert-success').css('display', 'block');
                }
            });
        }
    </script>
@stop

@section('buttons')
    <button type="button" class="update_hidden" style="display: none;"></button>
    <button type="button" class="btn btn-action command-save">{!!trans('front.send')!!}</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('global.cancel')!!}</button>
@stop