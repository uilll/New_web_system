@extends('Frontend.Reports.parse.layout')
<!-- Relatório de Histórico de Posições-->
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <table class="table">
                <tr>
                    <th>
                        {{ rtl(trans('front.report_type'), $data) }}: {{ rtl($types[$data['type']], $data) }}
                    </th>
                    <th>{{ $data['date_from'] }} - {{ $data['date_to'] }}</th>
                </tr>
            </table>
        </div>
        <div class="panel-body no-padding">
            <table class="table table-striped table-speed" style="margin-bottom: 0px; ">
                <thead>
                <tr>
                    <th>{{ rtl(trans('front.plate'), $data) }}</th>
                    <!--<th>{{ rtl(trans('global.device'), $data) }} ID</th>
                    <th>{{ rtl(trans('validation.attributes.imei'), $data) }}</th>-->
                    <th>{{ rtl(trans('front.time'), $data) }}</th>                    
                    <th style="max-width: 15vw !important; white-space: nowrap; overflow: hidden; padding: 0px !important;">{{ rtl(trans('front.address'), $data) }}</th>
					<th>{{ rtl(trans('front.speed')." (km/h)", $data) }}</th>
					<th>{{ rtl(trans('front.latitude'), $data) }}</th>
                    <th>{{ rtl(trans('front.longitude'), $data) }}</th>
                    <th>{{ rtl(trans('front.altitude'), $data) }}</th>                    
                    @foreach($data['parameters'] as $parameter)
						@if ($parameter !== "status")
							@if ($parameter !== "type")
								@if ($parameter !== "versionfw")
									@if ($parameter !== "index")
										@if ($parameter !== "sequence")
											@if ($parameter !== "distance")
												@if ($parameter !== "totaldistance")
													@if ($parameter !== "motion")
														@if ($parameter !== "valid")
															@if ($parameter !== "enginehours")
																@if ($parameter !== "iccid")
																	@if ($parameter !== "charge")
																		@if($parameter == "ignition")	
																			<th>{{ rtl("Ignição", $data) }}</th>																		
																		@elseif ($parameter == "blocked")
																			<th>{{ rtl("Bloqueio", $data) }}</th>
																		@elseif ($parameter == "batterylevel")
																			<th>{{ rtl("Nível bateria (%)", $data) }}</th>
																		@elseif ($parameter == "rssi")
																			<th>{{ rtl("Nível GSM", $data) }}</th>
																		@elseif ($parameter == "odometer")
																			<th>{{ rtl("Odômetro (km)", $data) }}</th>
																		@elseif ($parameter == "power")
																			<th>{{ rtl("Bateria (V)", $data) }}</th>
																		@elseif ($parameter == "in1")
																			<th>{{ rtl("Ent.1", $data) }}</th>
																		@elseif ($parameter == "in2")
																			<th>{{ rtl("Ent.2", $data) }}</th>
																		@elseif ($parameter == "in3")
																			<th>{{ rtl("Ent.3", $data) }}</th>
																		@elseif ($parameter == "out1")
																			<th>{{ rtl("Saí.1", $data) }}</th>
																		@elseif ($parameter == "out2")
																			<th>{{ rtl("Saí.2", $data) }}</th>
																		@else
																			<th>{{ rtl($parameter, $data) }}</th>
																		@endif
																	@endif
																@endif
															@endif
														@endif
													@endif
												@endif
											@endif
										@endif
									@endif
								@endif
							@endif
						@endif
                    @endforeach
                </tr>
                </thead>

                <tbody>
                    @foreach ($items as $item)
					
                        @foreach ($item['positions'] as $position)
                        <tr>
							
                            <td>{!!$item['device']['plate_number']!!}</td>
                            <!--<td>{!!$item['device']['id']!!}</td>
                            <td>{!!$item['device']['imei']!!}</td> -->
                            <td>{!!$position['time']!!}</td>							
                            <td style="max-width: 15vw !important; white-space: nowrap; overflow: hidden; padding: 0px !important;"><a href="http://sistema.carseg.com.br/streetview.html?lat={!!$position['latitude']!!}&long={!!$position['longitude']!!}&t=m" target="_blank">{!!$position['address']!!}</a></td>
							@if (intval($position['speed'])>=2) 
								<td>{!!$position['speed']!!}</td>
							@else
								<td>{{"0"}}</td>
							@endif
							<td>{!!$position['latitude']!!}</td>
                            <td>{!!$position['longitude']!!}</td>                            
                            <td>{!!$position['altitude']!!}</td>                            
                            @foreach($data['parameters'] as $parameter)
								@if ($parameter !== "status")
									@if ($parameter !== "type")
										@if ($parameter !== "versionfw")
											@if ($parameter !== "index")
												@if ($parameter !== "sequence")
													@if ($parameter !== "distance")
														@if ($parameter !== "totaldistance")
															@if ($parameter !== "motion")
																@if ($parameter !== "valid")
																	@if ($parameter !== "enginehours")	
																		@if ($parameter !== "iccid")
																			@if ($parameter !== "charge")
																				@if ($parameter == "ignition")
																					@if (intval(strval(strpos($item['device']['rastreador'],"CRX3")))>=0)
																						@if (intval($position['speed'])>=3) 
																							<td>@if (isset($position['other'][$parameter]))
																								{{ "Ligada" }} @endif</td>
																						@else
																							<td>@if (isset($position['other'][$parameter])) {{ "Desligada" }} @endif</td>
																						@endif
																					@else
																						@if ($position['other'][$parameter]== "true") 
																							<td>@if (isset($position['other'][$parameter]))
																								{{ "Ligada" }} @endif</td>
																						@else
																							<td>@if (isset($position['other'][$parameter])) {{ "Desligada" }} @endif</td>
																						@endif
																					@endif
																				@elseif ($parameter == "blocked")
																					@if ($position['other'][$parameter]== "true") 
																						<td>@if (isset($position['other'][$parameter])) {{ "Sim" }} @endif</td>
																					@else
																						<td>@if (isset($position['other'][$parameter])) {{ "Não" }} @endif</td>
																					@endif
																				@elseif ($parameter == "rssi")
																					@if ($position['other'][$parameter]== "1") 
																						<td>@if (isset($position['other'][$parameter])) {{ "Ruim" }} @endif</td>
																					@elseif ($position['other'][$parameter]== "2") 
																						<td>@if (isset($position['other'][$parameter])) {{ "Regular" }} @endif</td>
																					@elseif ($position['other'][$parameter]== "3") 
																						<td>@if (isset($position['other'][$parameter])) {{ "Bom" }} @endif</td>
																					@else
																						<td>@if (isset($position['other'][$parameter])) {{ "Ótimo" }} @endif</td>
																					@endif
																				@else
																					<td>@if (isset($position['other'][$parameter])) {{ $position['other'][$parameter] }} @endif</td>
																				@endif
																			@endif
																		@endif
																	@endif
																@endif
															@endif
														@endif
													@endif
												@endif
											@endif
										@endif
									@endif
								@endif								
                            @endforeach
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop