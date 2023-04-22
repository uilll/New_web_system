@if (!empty($grouped))
	<table id="cabecalho_objetos" width=100%>
						 <tr>
							<td><div class="header_devices_list" style="min-width: 18vw">
									<span> Nome</span>
								</div>
							</td>
							<td><div class="header_devices_list" style="min-width: 6vw">
									<span> {{ trans('front.plate') }}</span>
								</div>
							</td>
							<td><div class="header_devices_list" style="min-width: 11vw">
									<span> Veículo</span>
								</div>
							</td>
							@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
								<td><div class="header_devices_list" style="min-width: 14vw">
										<span> Proprietário</span>
									</div>
								</td>
								<td>
							</td>
							@else
								<td><div class="header_devices_list" style="min-width: 14vw">
										<span> Motorista</span>
									</div>
								</td>
							@endif
							<td><div class="header_devices_list" style="min-width: 10vw">
									<span> {{ trans('front.time') }} da última posição</span>
								</div>
							</td>
							<td><div class="header_devices_list" style="min-width: 17vw">
									<span> {{ trans('front.county') }}</span>
								</div>
							</td>
							<td id ="estado"><div class="header_devices_list" style="min-width: 4vw">
									<span> {{ trans('front.state') }}</span>
								</div>
							</td>														
							<td><div class="header_devices_list" style="min-width: 12vw">
									<span> Status</span>
								</div>
							</td>														
							<td><div class="header_devices_list" style="min-width: 8vw">
									<span> {{ trans('front.speed') }}</span>
								</div>
							</td>
							@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
								<td>
									<div class="header_devices_list" style="min-width: 10vw">
											<span> {{ trans('front.time') }} da última informação</span>
									</div>
								</td>
								<td>
									<div class="header_devices_list" style="min-width: 12vw">
										<span>Retorno</span>
									</div>
								</td>
							@endif

						</tr>
	</table>
	
    @foreach ($grouped as $id => $devices)


        <div class="group" id="{{ $id }}" data-toggle="multiCheckbox">
            <div class="group-heading">

                @if (0)
					<div class="checkbox" style="display: block">
						<input type="checkbox" id="checkbox_group" style="width: 100%">
						<label></label>
					</div>
				@else
					<div class="checkbox">
						<input type="checkbox" id="checkbox_group" data-toggle="checkbox" style="width: 100%">
						<label></label>
					</div>
				@endif

                <div class="group-title {{ isset($device_groups_opened[$id]) ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#device-group-{{ $id }}" data-parent="#objects_tab" aria-expanded="{{ isset($device_groups_opened[$id]) ? 'true' : 'false' }}" aria-controls="device-group-{{ $id }}">
                    {{ $device_groups[$id] }}
                </div>

                <div class="btn-group">
                    @if ($id)
                        <i class="btn icon options" data-url="{{ route('devices_groups.edit', $id) }}" data-modal="devices_groups_edit"></i>
                    @else
                        <i class="btn icon options" data-url="{{ route('devices_groups.create') }}" data-modal="devices_groups_create"></i>
                    @endif
                </div>

            </div>
            <div id="device-group-{{ $id }}" class="group-collapse collapse {{ !isset($device_groups_opened[$id]) ? '' : 'in' }}" data-id="{{ $id }}" role="tabpanel" aria-expanded="{{ isset($device_groups_opened[$id]) ? 'true' : 'false' }}">
                <div class="group-body">

                    <ul class="group-list" id="group-list">

					   @foreach ($devices as $key => $item)
									<!-- Menu mobile -->
									<?php
										$useragent=$_SERVER['HTTP_USER_AGENT'];
										if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
									?>
										<li data-device-id="{{ $item['id'] }}" class="borda_sombreada" style="background-color: {{$item['card'] ? '#d8ecf3' : '#fffff'}}">
											<div class="item_mini_sidebar checkbox" style="max-width: 0%"> <!--0--> 
												<input id="checkbox" type="checkbox" name="items[{{ $item['id'] }}]" value="{{ $item['id'] }}" {{ !empty($item['active']) ? 'checked="checked"' : '' }} onChange="app.devices.active('{{ $item['id'] }}', this.checked);"/>
												<label></label>
											</div>

											@if ( Auth::User()->isManager() or Auth::User()->isAdmin())
												<!-- Nome -->
												<div class="item_mini_sidebar name " onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['name'])}}>
													<span  data-device="name" " id="font_color{{ $item['id'] }}">{{ $item['name'] }}</span>
												</div>
												
											@endif

											<div style="background-color: '#fffff'; max-width: 100%; width: 100%; min-width: 100%"> 
												<table style="min-width: 100%">
													<tr>
														<td style="width:50%;min-width: 40%">
															<div id="plate{{ $item['id'] }}" class="item_mini_sidebar_mobile plate_mobile  font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
																<strong><span  data-device="plate" ">{{ $item['plate_number'] }}</span>
																		@if (true)
																			@if ($item['block'])
																					@if($item['status_block'])
																						<span style="color: red" data-device="status_block_{{ $item['id'] }}" id="status_block_{{ $item['id'] }}" title="BLOQUEADO"><i class="fas fa-lock"></i></span> 
																					@else
																						<span style="color: green" data-device="status_block_{{ $item['id'] }}" id="status_block_{{ $item['id'] }}" title="DESBLOQUEADO"><i class="fas fa-lock-open"></i></span> 
																					@endif

																			@endif
																		@endif
																</strong>
															</div>
														</td>

														<td style="width:50%;min-width: 50%; overflow: hidden">
															<div class="item_mini_sidebar_mobile device_model_mobile font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
																<strong><span id="device_model{{ $item['id'] }}"  data-device="device_model" ">{{ $item['device_model'] }}</span></strong>

															</div>	
														</td>
														<td>
															<!-- Menu DropDown-->
															<div class="item_mini_sidebar details_mobile">
																<div class="btn-group dropleft droparrow"  data-position="fixed">
																	<i class="btn icon" data-toggle="dropdown" data-position="fixed" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-file-text fa-lg" aria-hidden="true" style="color: purple"></i></i>
																	<ul class="dropdown-menu" >
																		@if ( Auth::User()->perm('history', 'view') )
																			<li class="dropdown-item">
																				<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'last_hour');">
																					<span class="icon last-hour"></span>
																					<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.last_hour')) }})</span>
																				</a>
																			</li>
																			<li class="dropdown-item">
																				<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'today');">
																					<span class="icon today"></span>
																					<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.today')) }})</span>
																				</a>
																			</li>
																			<li class="dropdown-item">
																				<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'yesterday');">
																					<span class="icon yesterday"></span>
																					<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.yesterday')) }})</span>
																				</a>
																			</li>
																		@endif
																		
																		
																			<li  class="dropdown-item">
																				<a href="javascript:" data-url="{{ route('objects.sensores', [$item['id']]) }}" data-modal="sensores" onClick="app.devices.select({{ $item['id'] }});">
																					<span class="icon sensors"></span>
																					<span class="text">Sensores</span>
																				</a>
																			</li>
																		
							
																		<li  class="dropdown-item">
																			<a href="javascript:" data-url="{{ route('devices.follow_map', [$item['id']]) }}" data-id="{{ $item['id'] }}" onClick="app.devices.follow({{ $item['id'] }});" data-name="{{ trans('front.follow').' ('.$item['name'].')' }}">
																				<span class="icon follow"></span>
																				<span class="text">{{ trans('front.follow') }}</span>
																			</a>
																		</li>

																		<li class="dropdown-item">
																			<a href="carseghttps://www.google.com/maps?q={{$item['lat']}},{{$item['lng']}}&z=17&hl=pt-BR">
																				<i class='icon icon-fa fab fa-google' style="color: lightgray"></i>
																				<span class="text">Rota</span>
																			</a>
																		</li>
																		@if (true)
																			<li>
																				<a href="javascript:" data-url="{{ route('objects.anchor', [$item['id']]) }}" data-modal="anchor">
																					<span class="icon icon-fa fa-anchor"></span>
																					<span class="text"> Âncora</span>
																				</a>
																			</li>
																		@endif

																		<li class="dropdown-item">
																			<a href="javascript:" data-url="https://sistema.carseg.com.br/services/index/{{ $item['id'] }}" data-modal="services">
																				<span class="icon tools"></span>
																				<span class="text"> {{ trans('front.services') }}</span>
																			</a>														
																		</li>
							
																		
																		@if (  Auth::User()->perm('chat', 'view') && $item->canChat())
																		<li class="dropdown-item">
																			<a href="javascript:" class="chat_device" data-url="{{ route('chat.init', [$item['id'], 'device', 1]) }}">
																				<span class="icon icon-fa fa-comments-o"></span>
																				<span class="text">{{ trans('front.chat') }}</span>
																			</a>
																		</li>
																		@endif
																		<li class="dropdown-item">
																			<a href="javascript:" data-url="{{ route('user_drivers.change', [$item['id']]) }}" data-modal="drivers_change" id="change_{{ $item['id'] }}">
																				<i class="fa fa-users" aria-hidden="true" style="color: lightgray"></i>
																				<span class="text">Trocar motoristas</span>
																			</a>
																		</li>

																		@if ( Auth::User()->perm('send_command', 'view') )
																			<li class="dropdown-item">
																				<a href="javascript:" data-url="{{ route('send_command.create') }}" data-modal="send_command" data-id="{{ $item['id'] }}">
																					<span class="icon send-command"></span>
																					<span class="text">{{ trans('front.send_command') }}</span>
																				</a>
																			</li>
																			@if ( Auth::User()->perm('share_device', 'view') )
																				<li>
																					<!-- Share device -->
																					<a href="javascript:" data-url="{{ route('objects.share', [$item['id']]) }}" data-modal="share_device">
																						<span class="fa fa-share-alt" aria-hidden="true"></span>
																						<span class="text"> {{ "Compartilhar Veículo - TESTE" }}</span>
																					</a>														
																				</li>
																			@endif
																			@if (false)
																				<li onclick="bloq_desbloq({{$item['status_block']}})">
																					<a href="javascript:" data-url="{{ route('send_command.create') }}" data-modal="send_command" data-id="{{ $item['id'] }}">
																						
																						@if ($item['status_block'])
																							
																							<span class="text" style="color:green" title="DESBLOQUEAR">
																								<i class="fas fa-lock-open"></i>	
																								&nbspDESBLOQUEAR
																							</span>
																						@else
																							<span class="text" style="color:red" title="BLOQUEAR">
																								<i class="fas fa-lock"></i>
																								&nbspBLOQUEAR
																							</span>
																						@endif
																						
																					</a>
																				</li>
																			@endif
																		@endif

																		@if ( Auth::User()->perm('devices', 'edit') )
																			<li class="dropdown-item">
																				<a href="javascript:" data-url="{{ route('devices.edit', [$item['id'], 0]) }}" data-modal="devices_edit">
																					<span class="icon edit"></span>
																					<span class="text">{{ trans('global.edit') }}</span>
																				</a>
																			</li>
																		@endif
																		
																	</ul>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</div>

											<div style="max-width: 100%; width: 100%; min-width: 100%">
												<table style="min-width: 100%">
													<tr>
														<td style="min-width: 60%; text-align: left; font-size: larger;">
															<div class="item_mini_sidebar_mobile" onClick="app.devices.select({{ $item['id'] }});" title={{$item['sensors_']}} style="overflow: auto; min-width:100% max-width:100% !important">
																<!-- IGNIÇÃO E VELOCIDADE -->
																@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
															<i class="fas fa-key" aria-hidden="true" style="color: {{strpos($item['sensors_'], 'desligada') ? 'red' : 'green'}}"></i>
																@endif
																<span class="font_color{{ $item['id'] }}" data-device="sensors_{{ $item['id'] }}" id="devicestatus{{ $item['id'] }}" " style="min-width: 100% ">{{$item['sensors_']}}</span> 
															</div>
														</td>
														<td style="min-width: 40%">
															<div class="item_mini_sidebar details font_color{{ $item['id'] }}" style="min-width: 50%">
																<span id="speed{{ $item['id'] }}"  onClick="app.devices.select({{ $item['id'] }});" data-device="speed{{ $item['id'] }}" ">{{ $item['speed'] }} {{ $item['distance_unit_hour'] }}</span>
															</div>
														</td>
													</tr>
												</table>
											</div>						
																		
											@if ( Auth::User()->isManager() or Auth::User()->isAdmin())
												<!--4 Proprietário -->
												<div class="item_mini_sidebar driver_ font_color{{ $item['id'] }}" id="driver_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['object_owner'])}}>
													<span  data-device="driver_" ">{{$item['object_owner']}}</span>
												</div>
											@else
												<!--4 Motorista -->
												<div class="item_mini_sidebar_mobile driver_mobile font_color{{ $item['id'] }}" id="driver_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['driver_']}}>
													<span  data-device="driver_" ">{{$item['driver_']}}</span>
												</div>
											@endif
											<!--5 Hora de atualização -->
											<div class="item_mini_sidebar_mobile time{{ $item['id'] }} time_mobile font_color{{ $item['id'] }}" id="time{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
												<span  data-device="time" ">{{ $item['time'] }}</span>
											</div>
											<!--6-- Cidade-->
											<div class="item_mini_sidebar_mobile city_mobile	 font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['city'])}}>
												<span  data-device="city" id="city"" ">{{ $item['city']}}</span> <img id="icon{{ $item['id'] }}" class="icon_report" align="right" onmouseover="change_gray_scalle({{ $item['id'] }}, true)" onmouseout="change_gray_scalle({{ $item['id'] }}, false)" onclick="error_report({{$item['lat']}}, {{$item['lng']}})" title="Reportar erro em: endereço, cidade ou distância" src="https://img.icons8.com/ios-glyphs/15/000000/marker-off.png"> 
											</div>
											<!--7 Estado-->
											<div class="item_mini_sidebar state font_color{{ $item['id'] }}" id="state_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['state']}}>
												<span  data-device="state" ">{{$item['state']}}</span>
											</div>								
											<!--8-->
											<div class="lat" >
													<span id="id_server">{{$item['id']}}</span>
											</div>
											<!--9-->
											<div class="lat" >
													<span id="icon_color">{{$item['status_collor']}}</span>
											</div>
											<div class="lat" onClick="app.devices.select({{ $item['id'] }});" title={{ $item['lat'] }}>
												<span id="lat{{ $item['id'] }}" data-device="lat">{{$item['lat']}}</span>
											</div>
											<div class="lat" onClick="app.devices.select({{ $item['id'] }});" title={{ $item['lng'] }}>
												<span id="lng{{ $item['id'] }}" data-device="lng">{{$item['lng']}}</span>
											</div>
											<!--Address -->
											<div class="item_mini_sidebar_mobile address_mobile font_color{{ $item['id'] }}" id="address_" onClick="app.devices.select({{ $item['id'] }});" title={{$item['address_']}}>
												<span  data-device="address_" ">{{$item['address_']}}</span>
											</div>

											<div class="lat" onClick="app.devices.select({{ $item['id'] }});">
												@if ( $item['engine_hours'] != 'gps' )
												<span  data-device="detect_engine" class="{{ $item['engine_status'] ? 'on' : 'off' }}"><i class="icon detect_engine"></i> {{ trans("global.{$item['online']}") }}</span>
												@endif
												<span  data-device="status" id="status" style="background-color: {{ $item['icon_color'] }}" title="{{ trans("global.{$item['online']}") }}">- {{ trans("global.{$item['online']}") }}</span>
												<span  data-device="status_color{{ $item['id'] }}" id="status_color{{ $item['id'] }}">{{$item['icon_color']}}</span>

											</div>
											
											@if ( Auth::User()->isManager() or Auth::User()->isAdmin())
												<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['active']}}>
													<span id="shadow_area{{ $item['id'] }}">{{$item['shadow_area']}}</span> 
												</div>
												<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['active']}}>
													<span id="shadow_area{{ $item['id'] }}">{{$item['active_gpswox']}}</span> 
												</div>
												<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['rastreador']}}>
													<span id="modelo{{ $item['id'] }}">{{$item['rastreador']}}</span> 
												</div>									
											@endif
											
											@if ( Auth::User()->isManager() or Auth::User()->isAdmin())
											<div class="item_mini_sidebar time{{ $item['id'] }} time font_color{{ $item['id'] }}" id="time2" onClick="app.devices.select({{ $item['id'] }});">
												<span  data-device="time2">{{ $item['time2'] }}</span>
											</div>
											<!--//Retorno do rastreador-->
												<div class="item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['result_'])}}>
													<span id="result_{{ $item['id'] }}">{{$item['result_']}}</span> 
												</div>
											@endif
											<p></p>
										</li>
										<br>
									<?php
										}

								else{
									// items apresentados na tela do computador
									?>
									<li data-device-id="{{ $item['id'] }}" " >
										<div class="item_mini_sidebar checkbox"> <!--0--> 
											<input id="checkbox" type="checkbox" name="items[{{ $item['id'] }}]" value="{{ $item['id'] }}" {{ !empty($item['active']) ? 'checked="checked"' : '' }} onChange="app.devices.active('{{ $item['id'] }}', this.checked);"/>
											<label></label>
										</div>
										<!--1-->
										<div class="item_mini_sidebar name font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['name'])}}>
											<span  data-device="name" " id="font_color{{ $item['id'] }}">{{ $item['name'] }}</span>
										</div>
										<!--2-->
										<div id="plate{{ $item['id'] }}" class="item_mini_sidebar plate  font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
											<span  data-device="plate" ">{{ $item['plate_number'] }}</span>
		
										</div>
										<!--3-->								
										<div class="item_mini_sidebar device_model font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
											<span id="device_model{{ $item['id'] }}"  data-device="device_model" ">{{ $item['device_model'] }}</span>
		
										</div>								
										@if ( Auth::User()->isManager() or Auth::User()->isAdmin())
											<!--4 Proprietário -->
											<div class="item_mini_sidebar driver_ font_color{{ $item['id'] }}" id="driver_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['object_owner'])}}>
												<span  data-device="driver_" ">{{$item['object_owner']}}</span>
											</div>
										@else
											<!--4 Motorista -->
											<div class="item_mini_sidebar driver_ font_color{{ $item['id'] }}" id="driver_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['driver_']}}>
												<span  data-device="driver_" ">{{$item['driver_']}}</span>
											</div>
										@endif
										<!--5-->
										<div class="item_mini_sidebar time{{ $item['id'] }} time font_color{{ $item['id'] }}" id="time{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
											<span  data-device="time" ">{{ $item['time'] }}</span>
										</div>
										<!--6-->
										<div class="item_mini_sidebar city font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['city'])}}>
											<span  data-device="city" id="city"" ">{{ $item['city']}}</span> <img id="icon{{ $item['id'] }}" class="icon_report" align="right" onmouseover="change_gray_scalle({{ $item['id'] }}, true)" onmouseout="change_gray_scalle({{ $item['id'] }}, false)" onclick="error_report({{$item['lat']}}, {{$item['lng']}})" title="Reportar erro em: endereço, cidade ou distância" src="https://img.icons8.com/ios-glyphs/15/000000/marker-off.png"> 
										</div>
										<!--7 ESTADO			-->
										<div class="item_mini_sidebar state font_color{{ $item['id'] }}" id="state_{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['state']}}>
											<span  data-device="state" ">{{$item['state']}}</span>
										</div>								
										<!--8-->
										<div class="lat" >
												<span id="id_server">{{$item['id']}}</span>
										</div>
										<!--9-->
										<div class="lat" >
												<span id="icon_color">{{$item['status_collor']}}</span>
										</div>
										<div class="lat" onClick="app.devices.select({{ $item['id'] }});" title={{ $item['lat'] }}>
											<span id="lat{{ $item['id'] }}" data-device="lat">{{$item['lat']}}</span>
										</div>
										<div class="lat" onClick="app.devices.select({{ $item['id'] }});" title={{ $item['lng'] }}>
											<span id="lng{{ $item['id'] }}" data-device="lng">{{$item['lng']}}</span>
										</div>
										<!--10-->
										<div class="item_mini_sidebar address_" id="address_" onClick="app.devices.select({{ $item['id'] }});" title={{$item['address_']}}>
											<span  data-device="address_" ">{{$item['address_']}}</span>
										</div>								
										<div class="lat" onClick="app.devices.select({{ $item['id'] }});">
											@if ( $item['engine_hours'] != 'gps' )
											<span  data-device="detect_engine" class="{{ $item['engine_status'] ? 'on' : 'off' }}"><i class="icon detect_engine"></i> {{ trans("global.{$item['online']}") }}</span>
											@endif
											<span  data-device="status" id="status" style="background-color: {{ $item['icon_color'] }}" title="{{ trans("global.{$item['online']}") }}">- {{ trans("global.{$item['online']}") }}</span>
											<span  data-device="status_color{{ $item['id'] }}" id="status_color{{ $item['id'] }}">{{$item['icon_color']}}</span>
		
										</div>
										<!--10	STATUS 			-->
										<div class="item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});">
											<span  data-device="sensors_{{ $item['id'] }}" id="devicestatus{{ $item['id'] }}" " title={{$item['sensors_']}}>{{$item['sensors_']}}</span> 
											@if (Auth::User()->isManager() || Auth::User()->isAdmin())
												@if ($item['block'])
													@if($item['status_block'])
														<span style="color: red" data-device="status_block_{{ $item['id'] }}" id="status_block_{{ $item['id'] }}" title="BLOQUEADO"><i class="fas fa-lock"></i></span> 
													@else
														<span style="color: green" data-device="status_block_{{ $item['id'] }}" id="status_block_{{ $item['id'] }}" title="DESBLOQUEADO"><i class="fas fa-lock-open"></i></span> 
													@endif
													@if($item['reverse_block'])
														<span style="color: red" data-device="reverse_block{{ $item['id'] }}" id="reverse_block{{ $item['id'] }}" title="BLOQUEIO REVERSO"><i class="fas fa-registered"></i></span> 
													@endif
												@endif
												@if($item['double_equip'])
													<span style="color: green" data-device="double_equip{{ $item['id'] }}" id="double_equip{{ $item['id'] }}" title="EQUIPAMENTO DUPLICADO"><i class="fas fa-check-double"></i></span> 
												@endif
											@else
											@endif
										</div>
										@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
											<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['active']}}>
												<span id="shadow_area{{ $item['id'] }}">{{$item['shadow_area']}}</span> 
											</div>
											<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['active']}}>
												<span id="shadow_area{{ $item['id'] }}">{{$item['active_gpswox']}}</span> 
											</div>
											<div class="lat item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{$item['rastreador']}}>
												<span id="modelo{{ $item['id'] }}">{{$item['rastreador']}}</span> 
											</div>									
										@endif
										<!--10	SPEED 			-->
										<div class="item_mini_sidebar details font_color{{ $item['id'] }}">
											<span  onClick="app.devices.select({{ $item['id'] }});" data-device="speed{{ $item['id'] }}" ">{{ $item['speed'] }} {{ $item['distance_unit_hour'] }}</span>
		
		
											<div class="btn-group dropup droparrow"  data-position="fixed">
												<i class="btn icon options" data-toggle="dropdown" data-position="fixed" aria-haspopup="true" aria-expanded="false"></i>
												<ul class="dropdown-menu" >
													@if ( Auth::User()->perm('history', 'view') )
														<li>
															<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'last_hour');">
																<span class="icon last-hour"></span>
																<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.last_hour')) }})</span>
															</a>
														</li>
														<li>
															<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'today');">
																<span class="icon today"></span>
																<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.today')) }})</span>
															</a>
														</li>
														<li>
															<a href="javascript:" class="object_show_history" onClick="app.history.device('{{ $item['id'] }}', 'yesterday');">
																<span class="icon yesterday"></span>
																<span class="text">{{ trans('front.show_history') }} ({{ mb_strtolower(trans('front.yesterday')) }})</span>
															</a>
														</li>
													@endif
		
													<li>
														<!-- seguir -->
														<a href="javascript:" data-url="{{ route('devices.follow_map', [$item['id']]) }}" data-id="{{ $item['id'] }}" onClick="app.devices.follow({{ $item['id'] }});" data-name="{{ trans('front.follow').' ('.$item['name'].')' }}">
															<span class="icon follow"></span>
															<span class="text">{{ trans('front.follow') }}</span>
														</a>
													</li>
													<li>
														<!-- Rota -->
														<a href="https://www.google.com/maps?q={{$item['lat']}},{{$item['lng']}}&z=17&hl=pt-BR" target="_blank">
															<i class='icon icon-fa fab fa-google fa-1x'></i>
															<span class="text">    Rota</span>
														</a>
													</li>
													@if (Auth::User()->isAdmin())
															<li onclick="bloq_desbloq({{$item['status_block']}})">
																<a href="javascript:" data-url="{{ route('send_command.create') }}" data-modal="send_command" data-id="{{ $item['id'] }}">
																	
																	@if ($item['status_block'])
																		
																		<span class="text" style="color:green" title="DESBLOQUEAR">
																			<i class="fas fa-lock-open"></i>	
																			&nbspDESBLOQUEAR
																		</span>
																	@else
																		<span class="text" style="color:red" title="BLOQUEAR">
																			<i class="fas fa-lock"></i>
																			&nbspBLOQUEAR
																		</span>
																	@endif
																	
																</a>
															</li>
														@endif
													@if ( Auth::User()->perm('share_device', 'view') )	
														<li>
															<!-- Share device -->
															<a href="javascript:" data-url="{{ route('objects.share', [$item['id']]) }}" data-modal="share_device">
																<span class="fa fa-share-alt" aria-hidden="true"></span>
																<span class="text"> {{ "Compartilhar Veículo - TESTE" }}</span>
															</a>														
														</li>
													@endif
													<li>
														<!-- Manutenções -->
														<a href="javascript:" data-url="https://sistema.carseg.com.br/services/index/{{ $item['id'] }}" data-modal="services">
															<span class="icon tools"></span>
															<span class="text"> {{ trans('front.services') }}</span>
														 </a>														
													</li>
													
													
													@if (  Auth::User()->perm('chat', 'view') && $item->canChat())
													<li>
														<a href="javascript:" class="chat_device" data-url="{{ route('chat.init', [$item['id'], 'device', 1]) }}">
															<span class="icon icon-fa fa-comments-o"></span>
															<span class="text">{{ trans('front.chat') }}</span>
														</a>
													</li>
													@endif
													<li>
														<a href="javascript:" data-url="{{ route('user_drivers.change', [$item['id']]) }}" data-modal="drivers_change" id="change_{{ $item['id'] }}">
															<span class="icon edit"></span>
															<span class="text">Trocar motoristas</span>
														</a>
													</li>

													<li>
														<a href="javascript:" data-url="{{ route('objects.anchor', [$item['id']]) }}" data-modal="ancora">
															<span class="icon icon-fa fa-anchor"></span>
															<span class="text"> Âncora</span>
														</a>
													</li>

													@if ( Auth::User()->perm('send_command', 'view') )
														<li>
															<a href="javascript:" data-url="{{ route('send_command.create') }}" data-modal="send_command" data-id="{{ $item['id'] }}">
																<span class="icon send-command"></span>
																<span class="text">{{ trans('front.send_command') }}</span>
															</a>
														</li>
													@endif
													@if ( Auth::User()->perm('devices', 'edit') )
														<li>
															<a href="javascript:" data-url="{{ route('devices.edit', [$item['id'], 0]) }}" data-modal="devices_edit">
																<span class="icon edit"></span>
																<span class="text">{{ trans('global.edit') }}</span>
															</a>
														</li>
													@endif
													
												</ul>
											</div>
										</div>
										@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
										<div class="item_mini_sidebar time{{ $item['id'] }} time font_color{{ $item['id'] }}" id="time2" onClick="app.devices.select({{ $item['id'] }});">
											<span  data-device="time2">{{ $item['time2'] }}</span>
										</div>
										<!--//Retorno do rastreador-->
											<div class="item_mini_sidebar status font_color{{ $item['id'] }}" onClick="app.devices.select({{ $item['id'] }});" title={{str_replace(" ","_",$item['result_'])}}>
												<span id="result_{{ $item['id'] }}">{{$item['result_']}}</span> 
											</div>
										@endif
									</li>
									<?php
										}
									?>
                            
                        @endforeach

						
                    </ul>
                </div>
            </div>
        </div>
    @endforeach

	<br>
	<br>					   
	@if ( Auth::User()->isManager() || Auth::User()->isAdmin())
		
		
	@endif
@else
    <p class="no-results">{!! trans('front.no_devices') !!}</p>
@endif
