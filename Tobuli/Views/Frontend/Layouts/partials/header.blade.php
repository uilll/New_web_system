<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div id="header" class="folded">
    <nav class="navbar navbar-main navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-header-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                @if ( has_asset_logo('logo') )
                    <?php
                                if($_SERVER['SERVER_NAME']=='app.onlysat.com.br'){
                    ?>
                        <a class="navbar-brand" href="/" title="{{ settings('main_settings.server_name') }}"+"/login/1085"><img src="{{ asset_logo('logo') }}"></a>
                    <?php
                                } else{
                    ?>  
                        <a class="navbar-brand" href="/" title="{{ settings('main_settings.server_name') }}"><img src="{{ asset_logo('logo') }}"></a>
                    <?php
                                }
                    ?>  
                @endif
            </div>

            <div class="collapse navbar-collapse" id="bs-header-navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    @if (isAdmin() or Auth::User()->group_id ==5)
                        <li>
                            <a href="{!!route('admin')!!}" role="button" rel="tooltip" data-placement="bottom" title="{!!trans('global.admin')!!}">
                                <span class="icon admin"></span>
                                <span class="text">{!!trans('global.admin')!!}</span>
                            </a>
                        </li>
                    @endif
                    <?php
                        $useragent=$_SERVER['HTTP_USER_AGENT'];
                        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
                    ?>
                    @if( Auth::User()->perm('reports', 'view') )
                    <li>
						<a href="javascript:" data-url="{!!route('reports.create')!!}" data-modal="reports_create" role="button" id="report_mobile">
							<span class="icon reports"></span>
							<span class="text">{!!trans('front.reports')!!}</span>
                        </a>
                    </li>
                    @endif	
                    <li>
                        <a href="javascript:" data-url="{!!route('events.disable')!!}" data-modal="disable_notifications">
                            <span class="icon icon-fa fa-bell-slash-o"></span>
                            <span class="text">Notificações</span>
                        </a>
                    </li>
                    <?php
                        }
                    ?>		
                    <li class="dropdown">
                        <a href="javascript:" class="dropdown-toggle" role="button" data-toggle="dropdown" id="dropTools" rel="tooltip" data-placement="bottom" title="{!!trans('front.tools')!!}">
                            <span class="icon tools"></span>
                            <span class="text">{!!trans('front.tools')!!}</span>
                        </a>
                        <ul id="menu_droop_tools" class="dropdown-menu dropdown-menu-left" role="menu" aria-labelledby="dropTools">
                            <li>
                                <a href="javascript:" onclick="app.openTab('alerts_tab');">
                                    <span class="icon alerts"></span>
                                    <span class="text">{!!trans('front.alerts')!!}</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:" data-url="{{ route('events.disable') }}" data-modal="disable_notifications">
                                    <span class="icon icon-fa fa-bell-slash-o"></span>
                                    <span class="text">Notificações</span>
                                </a>
                            </li>

                            <li id="ddm_geofences">
                                <a href="javascript:" onclick="app.openTab('geofencing_tab');">
                                    <span class="icon geofences"></span>
                                    <span class="text">{!!trans('front.geofencing')!!}</span>
                                </a>
                            </li>
                            <li id="ddm_routes">
                                <a href="javascript:" onclick="app.openTab('routes_tab');">
                                    <span class="icon routes"></span>
                                    <span class="text">{!!trans('front.routes')!!}</span>
                                </a>
                            </li> 
							@if ( Auth::User()->perm('reports', 'view') )
							<li>
									<a href="javascript:" data-url="{!!route('reports.create')!!}" data-modal="reports_create" role="button">
										<span class="icon reports"></span>
										<span class="text">{!!trans('front.reports')!!}</span>
									</a>
							</li>
							@endif
                            <li id="ddm_ruler">
                                <a  href="#objects_tab" data-toggle="tab" onclick="app.ruler();">
                                    <span class="icon ruler"></span>
                                    <span class="text">{!!trans('front.ruler')!!}</span>
                                </a>
                            </li>
                            <li id="ddm_poi">
                                <a href="javascript:" onClick="app.openTab('map_icons_tab');">
                                    <span class="icon poi"></span>
                                    <span class="text">{!!trans('front.poi')!!}</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:" data-toggle="modal" data-target="#showPoint">
                                    <span class="icon point"></span>
                                    <span class="text">{!!trans('front.show_point')!!}</span>

                                </a>
                            </li>
                            <li>
                                <a href="javascript:" data-toggle="modal" data-target="#showAddress">
                                    <span class="icon address"></span>
                                    <span class="text">{!! trans('front.show_address') !!}</span>
                                </a>
                            </li>
                            <li id="ddm_send_command">
                                <a href="javascript:" data-url="{{ route('send_command.create') }}" data-modal="send_command">
                                    <span class="icon send-command"></span>
                                    <span class="text">{!!trans('front.send_command')!!}</span>
                                </a>
                            </li>
                            @if ( Auth::User()->perm('camera', 'view') )
                                <li>
                                    <a href="javascript:" data-url="{{ route('device_media.create') }}" data-modal="camera_photos"  role="button">
                                        <span class="icon camera"></span>
                                        <span class="text">{!!trans('front.camera')!!}</span>
                                    </a>
                                </li>
                            @endif
                            @if ( Auth::User()->perm('tasks', 'view') )
                            <li id="ddm_tasks"> 
                                <a href="javascript:" data-url="{{ route('tasks.index') }}" data-modal="tasks"  role="button">
                                    <span class="icon task"></span>
                                    <span class="text">{!!trans('front.tasks')!!}</span>
                                </a>
                            </li>
                            @endif
                            @if ( Auth::User()->perm('maintenance', 'view') )
                            <li id="ddm_maintenance">
                                <a href="{!!route('maintenance.index')!!}" target="_blank" role="button">
                                    <span class="icon services"></span>
                                    <span class="text">{!!trans('front.maintenance')!!}</span>
                                </a>
                            </li>
                            @endif
                            <li id="ddm_drivers"> 
                                <a href="javascript:" data-url="{{ route('user_drivers.index') }}" data-modal="drivers"  role="button">
                                    <span class="icon user"></span>
                                    <span class="text">{!!trans('front.drivers')!!}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript:" data-url="{!!route('my_account_settings.edit')!!}" data-modal="my_account_settings_edit" role="button" rel="tooltip" data-placement="bottom" title="{!!trans('front.setup')!!}">
                            <span class="icon setup"></span>
                            <span class="text">{!!trans('front.setup')!!}</span>
                        </a>
                    </li>

                    @if ( Auth::User()->perm('chat', 'view') )
                    <li>
                        <a href="javascript:" data-url="{!!route('chat.index')!!}" data-modal="chat" role="button" rel="tooltip" data-placement="bottom" title="{!!trans('front.chat')!!}">
                            <span class="icon chat"></span>
                            <span class="text">{!!trans('front.chat')!!}</span>
                        </a>
                    </li>
                    @endif

                    <li class="dropdown">
                        <a href="javascript:" class="dropdown-toggle" role="button" id="dropMyAccount" data-toggle="dropdown" rel="tooltip" data-placement="bottom" title="{!!trans('front.my_account')!!}">
                            <span class="icon account"></span>
                            <span class="text">{!!trans('front.my_account')!!}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropMyAccount">
                            <li>
                                <a href="javascript:" data-url="{{ route('subscriptions.index') }}" data-modal="subscriptions_edit">
                                    <span class="icon membership"></span>
                                    <span class="text">{!!trans('front.subscriptions')!!}</span>
                                </a>
                            </li>
                            <?php
                                if($_SERVER['SERVER_NAME']=='app.onlysat.com.br' and strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "iphone")!==false){
                                //require_once('vendor/autoload.php');
                                //if()
                                //$detect = new Mobile_Detect; 
                                
                            ?>
                            
                                <li>
                                    <a href="javascript:" onclick="buy_ios_In_App_Purchase('{{Auth::User()->id}}','compra_mensal')">
                                        <span class="fa fa-credit-card"></span>
                                        <span class="text" id="buy">
                                                    COMPRAR ASSINATURA
                                        </span>
                                    </a>
                                </li>
                            
                            <?php
                                }

                            ?>
                            <li>
                                @if (isPublic())
                                <a href="{{ config('tobuli.frontend_change_password').auth()->user()->email }}">
                                    <span class="icon password"></span>
                                    <span class="text">{!!trans('front.change_password')!!}</span>
                                </a>
                                @else
                                <a href="javascript:" data-url="{{ route('my_account.edit') }}" data-modal="subscriptions_edit">
                                    <span class="icon password"></span>
                                    <span class="text">{!!trans('front.change_password')!!}</span>
                                </a>
                                @endif
                            </li>
                            <li>
                                <a href="{!!route('logout')!!}">
                                    <span class="icon logout"></span>
                                    <span class="text">{!!trans('global.log_out')!!}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="language-selection">
                        <a href="javascript:" data-url="{{ route('subscriptions.languages') }}" data-modal="language-selection">
                            <img src="{{ asset_flag(Session::has('language') ? Session::get('language') : Auth::user()->lang) }}" alt="Language" class="img-thumbnail">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
