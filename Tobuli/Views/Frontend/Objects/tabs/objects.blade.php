<div class="tab-pane-header">
    <div class="form">
        <div class="input-group">
            @if ( Auth::User()->isManager() || Auth::User()->isAdmin())
                <div class="row">
                    @if (Auth::User()->isAdmin())
                    <span>&nbsp;&nbsp;&nbsp;&nbsp; {!!Form::select('search_type', array('name' => 'Nome', 'plate_number' => 'Placa', 'device_model' => 'Veículo', 'imei' => 'Serial', 'object_owner' => 'Proprietário', 'city' => 'Cidade', 'protocol' => 'Protocolo'),Cookie::get('search_type'),['id'=>'search_type'])!!}
                    </span>
                    @endif
                    @if (Auth::User()->isManager())
                    <span>&nbsp;&nbsp;&nbsp;&nbsp; {!!Form::select('search_type', array('object_owner' => 'Proprietário', 'plate_number' => 'Placa', 'device_model' => 'Veículo', 'city' => 'Cidade'),Cookie::get('search_type'),['id'=>'search_type'])!!}
                    </span>
                    @endif
                    @if ( Auth::User()->isManager() || Auth::User()->isAdmin())
                    <span>&nbsp;&nbsp;{!!Form::select('devices_per_page', array('10' => '10', '20' => '20', '50' => '50', '100' => '100'),Cookie::get('devices_page'),['id'=>'devices_per_page', 'onChange'=>'devices_per_page()'])!!}
                    </span>	
                    <span>&nbsp;&nbsp;{!!Form::select('ordenar_lista', array('object_owner' => 'Proprietário', 'name' => 'Nome'),'object_owner',['id'=>'order_list_', 'onChange'=>'order_list_()'])!!}
                    </span>	
                    @endif
                    @if ( Auth::User()->isManager() || Auth::User()->isAdmin())
                        <!--td><Página &nbsp;&nbsp;&nbsp;&nbsp;   
                            <span>&nbsp;&nbsp;Página: <span id="before" onClick="before_page()" style="cursor: pointer"> <<< </span> {--!!Form::select('pagina_atual', array('0' => '1', '1' => '2', '2' => '3', '3' => '4', '4' => '5',
                        '5' => '6',
                        '6' => '7',
                        '7' => '8',
                        '8' => '9',
                        '9' => '10',
                        '10' => '11',
                        '11' => '12',
                        '12' => '13',
                        '13' => '14',
                        '14' => '15',
                        '15' => '16',
                        '16' => '17',
                        '17' => '18',
                        '18' => '19',
                        '19' => '20',
                        '20' => '21',
                        '21' => '22',
                        '22' => '23',
                        '23' => '24',
                        '24' => '25',
                        '25' => '26',
                        '26' => '27',
                        '27' => '28',
                        '28' => '29',
                        '29' => '30',
                        '30' => '31',
                        '31' => '32',
                        '32' => '33',
                        '33' => '34',
                        '34' => '35',
                        '35' => '36',
                        '36' => '37',
                        '37' => '38',
                        '38' => '39',
                        '39' => '40',
                        '40' => '41',
                        '41' => '42',
                        '42' => '43',
                        '43' => '44',
                        '44' => '45',
                        '45' => '46',
                        '46' => '47',
                        '47' => '48',
                        '48' => '49',
                        '49' => '50',
                        '50' => '51',
                        '51' => '52',
                        '52' => '53',
                        '53' => '54',
                        '54' => '55',
                        '55' => '56',
                        '56' => '57',
                        '57' => '58',
                        '58' => '59',
                        '59' => '60',
                        '60' => '61',
                        '61' => '62',
                        '62' => '63',
                        '63' => '64',
                        '64' => '65',
                        '65' => '66',
                        '66' => '67'),Cookie::get('pagina_admin'),['id'=>'pagina_admin', 'onChange'=>'pagina_()'])!!} de <span id="ultima_pagina">{!!Cookie::get('total_paginas') !!}</span>
                            </span>	 <span id="next" onClick="next_page()" style="cursor: pointer"> >>> </span>
                        </td-->
                        

                    @endif
                </div>
                <div class="row">

                    </span>
                    <div class="form-group search">
                    {!!Form::text('search_admin', null, ['id' => 'search_admin', 'class' => 'form-control', 'placeholder' => trans('front.search'), 'autocomplete' => 'on'])!!}
                    
                    </div>
                </div>
                <!--div class="form-group search">
                    
                    
                </div-->                
            @else
                <div class="form-group search">
                    {!!Form::text('search', null, ['id' => 'search', 'class' => 'form-control', 'placeholder' => trans('front.search'), 'autocomplete' => 'off'])!!}
                </div>
            @endif
            <span class="input-group-btn">
                {{--
                <button class="btn btn-default" type="button">
                    <i class="icon filter"></i>
                </button>
                --}}
                @if ( settings('plugins.object_listview.status') && Auth::User()->perm('devices', 'view') )
                    <a href="{{ route('objects.listview') }}" class="btn btn-primary" target="_blank">
                        <i class="icon list"></i>
                    </a>
                @endif
                @if (Auth::User()->perm('devices', 'edit'))
                <button class="btn btn-primary" type="button"  data-url="{!!route('devices.create')!!}" data-modal="devices_create">
                    <i class="icon add"></i>
                </button>
                @endif                
            </span>
        </div>
    </div>
</div>

<div class="tab-pane-body">
    <div id="ajax-items"></div>
</div>