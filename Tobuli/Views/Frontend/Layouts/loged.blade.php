@if(isAdmin())
    <?php
        //require_once('vendor/autoload.php');
        //$client = new \GuzzleHttp\Client(['base_uri' => 'https://sistema.carseg.com.br/']);
        //$response = $client->request('GET', 'users/objects/app');
        //$response = json_decode(file_get_contents("https://sistema.carseg.com.br/users/objects/app"));
        
        //dd($response);
    ?>
@endif

<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>{{ settings('main_settings.server_name') }}</title>

    <base href="{{ url('/') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-control, max-age=172800">
    <!--meta name="description" content="@ if --($ _ENV['server'] == 'gpsbait') GPSBait, Law Enforcement Grade GPS Tracking Hardware and Software @ else --GPS Tracking System for Personal Use or Business @ endif"-->
    <link rel="shortcut icon" href="{{ asset_logo('favicon') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css"> </link>
    <link rel="stylesheet" href="{{ asset('assets/css/'.settings('main_settings.template_color').'.css?v='.config('tobuli.version')) }}">
    @if (file_exists(storage_path('custom/css.css')))
        <link rel="stylesheet" href="{{ asset('assets/css/custom.css?t=') . filemtime(storage_path('custom/css.css')) }}">
    @endif

    @yield('styles')

    <style>
        body {
            overflow: hidden;
        }
    </style>
    
</head>

<body>


@include('Frontend.Layouts.partials.loading')
@include('Frontend.Layouts.partials.header')


<div id="sidebar">
    <a class="btn-collapse" onclick="app.changeSetting('toggleSidebar');"><i></i></a>

    <div class="sidebar-content">
        <ul id="nav_tabs_map" class="nav nav-tabs nav-default">
            <li role="presentation" class="active">
                <a href="#objects_tab" id="devices_button" type="button" data-toggle="tab">{!!trans('front.objects')!!}</a>
            </li>
            <li role="presentation">
                <a href="#events_tab" id="events_button" type="button" data-toggle="tab">{!!trans('front.events')!!}</a>
            </li>
            <li role="presentation">
                <a href="#history_tab" id="history_button" type="button" data-toggle="tab">{!!trans('front.history')!!}</a>
            </li>
            {{-- hidden, import for correct tab work (shown, hidden evenets) --}}
            <li role="presentation" class="hidden"><a href="#alerts_tab" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#geofencing_tab" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#geofencing_create" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#geofencing_edit" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#routes_tab" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#routes_create" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#routes_edit" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#map_icons_tab" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#map_icons_create" data-toggle="tab"></a></li>
            <li role="presentation" class="hidden"><a href="#map_icons_edit" data-toggle="tab"></a></li>
        </ul>

        @yield('items')
		<!--@include('Frontend.History.bottom')-->
    </div>
</div>


<div id="mapWrap">
    <div id="map"></div>
    <button type="button" id="hidden_bnts" class="btn icon" onclick="hidden_bnts();"> <i class="icon globe" style="font-size: 1.4em;"> </button>
	<button type="button" id="streetview_bnt" class="btn icon street_button" onclick="Street_View_();"><span class="icon"> <i class="fa fa-street-view" style="font-size:24px"> </i></span></button>
    <button type="button" id="waze_bnt" class="btn icon waze_button" onclick="Waze_View_();"><span class="icon"> <i class="fa fa-road" style="font-size:24px"> </i></span></button>
    @if (!Auth::User()->isAdmin() && !Auth::User()->isManager())
        <button type="button" id="anchor_bnt" class="btn icon anchor_button" onclick="Anchor_();"><span class="icon"> <i class="fa fa-anchor" style="font-size:24px"> </i></span></button>
    @endif

    <button type="button" id="hidden_btns_cont_map" class="btn icon eye" onclick="hidden_cont_bnts();"><span class="icon"></span></button>
    <div id="map-controls">
        <div>
            <div class="btn-group-vertical" role="group">
                <button type="button" class="btn" onclick="app.mapFull();">
                    <span class="icon map-expand"></span>
                </button>
            </div>
        </div>

        <div>
            <div class="btn-group-vertical" data-position="fixed" role="group">
                <button type="button" class="btn" onClick="$('.leaflet-control-layers').toggleClass('leaflet-control-layers-expanded');">
                    <span class="icon map-change"></span>
                </button>
            </div>
        </div>

        <div>
            <div class="btn-group-vertical" role="group">
                <button type="button" class="btn" onclick="app.zoomIn();"><span class="icon zoomIn"></span></button>
                <button type="button" class="btn" onclick="app.zoomOut();"><span class="icon zoomOut"></span></button>
            </div>
        </div>

        <div id="map-controls-layers">
            <div class="btn-group-vertical" role="group" data-toggle="buttons">
                <label class="btn" >
                    <input id="clusterDevice" type="checkbox" autocomplete="off" onchange="app.changeSetting('clusterDevice', this.checked);">
                    <span class="icon group-devices"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.fit_objects')!!}">
                    <input id="fitBounds" type="checkbox" autocomplete="off" onchange="app.devices.toggleFitBounds(this.checked);">
                    <span class="icon fitBounds"></span>
                </label>
                <!--label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.objects')!!}">
                    <input id="showDevice" type="checkbox" autocomplete="off" onchange="app.changeSetting('showDevice', this.checked);">
                    <span class="icon devices"></span>
                </label-->
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.geofences')!!}">
                    <input id="showGeofences" type="checkbox" autocomplete="off" onchange="app.changeSetting('showGeofences', this.checked);">
                    <span class="icon geofences"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.routes')!!}">
                    <input id="showRoutes" type="checkbox" autocomplete="off" onchange="app.changeSetting('showRoutes', this.checked);">
                    <span class="icon routes"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.poi')!!}">
                    <input id="showPoi" type="checkbox" autocomplete="off" onchange="app.changeSetting('showPoi', this.checked);">
                    <span class="icon poi"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.show_names')!!}">
                    <input id="showNames" type="checkbox" autocomplete="off" onchange="app.changeSetting('showNames', this.checked);">
                    <span class="icon show-name"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.show_tails')!!}">
                    <input id="showTail" type="checkbox" autocomplete="off" onchange="app.changeSetting('showTail', this.checked);">
                    <span class="icon show-tail"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.live_traffic')!!}">
                    <input id="showTraffic" type="checkbox" autocomplete="off" onchange="app.changeSetting('showTraffic', this.checked);">
                    <span class="icon traffic"></span>
                </label>
            </div>
        </div>

        <div id="history-control-layers" style="display: none;">
            <div class="btn-group-vertical" role="group" data-toggle="buttons">
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.route')!!}">
                    <input id="showHistoryRoute" type="checkbox" autocomplete="off" onchange="app.changeSetting('showHistoryRoute', this.checked);">
                    <span class="icon routes"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.arrows')!!}">
                    <input id="showHistoryArrow" type="checkbox" autocomplete="off" onchange="app.changeSetting('showHistoryArrow', this.checked);">
                    <span class="icon device"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.stops')!!}">
                    <input id="showHistoryStop" type="checkbox" autocomplete="off" onchange="app.changeSetting('showHistoryStop', this.checked);">
                    <span class="icon parking"></span>
                </label>
                <label class="btn" data-toggle="tooltip" data-placement="left" title="{!!trans('front.events')!!}">
                    <input id="showHistoryEvent" type="checkbox" autocomplete="off" onchange="app.changeSetting('showHistoryEvent', this.checked);">
                    <span class="icon event"></span>
                </label>
            </div>
        </div>
    </div>
</div>

<a class="ajax-popup-link hidden"></a>
<input id="upload_file" type="file" style="display: none;" onchange=""/>

@include('Frontend.Layouts.partials.trans')

<script src="{{ asset('assets/js/core.js?v='.config('tobuli.version')) }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/app.js?v='.config('tobuli.version')) }}" type="text/javascript"></script>
@if (file_exists(storage_path('custom/js.js')))
    <script src="{{ asset('assets/js/custom.js?t=') . filemtime(storage_path('custom/js.js')) }}" type="text/javascript"></script>
@endif

<div id="bottombar" >
    @include('Frontend.History.bottom')
    @include('Frontend.Widgets.index')
</div>

@if (!Auth::User()->isAdmin() && !Auth::User()->isManager())
<div id="interaction_dialog" class="modal fade modal-overflow in"> </div>

<div id="somediv" title="Rota" style="display:none;">
    <iframe id="thedialog" width="350" height="350"></iframe>
</div>
@endif

<div id="conversations"></div>

<script type="text/javascript">
    //alert("IMPORTANTE: Prezados(as) clientes, estamos passando por uma instabilidade nas linhas da algar, já entramos em contato com a operadora e que buscando solucionar o problema o mais rápido possível. Agradecemos a compreensão.";
    var handlers = L.drawLocal.draw.handlers;
    handlers.polygon.tooltip.start = '{{ trans('front.click_to_start_drawing_shape') }}';
    handlers.polygon.tooltip.cont = '{{ trans('front.click_to_continue_drawing_shape') }}';
    handlers.polygon.tooltip.end = '{{ trans('front.click_first_point_to_close_this_shape') }}';
    handlers.polyline.error = '{{ trans('front.shape_edges_cannot_cross') }}';
    handlers.polyline.tooltip.start = '{{ trans('front.click_to_start_drawing_line') }}';
    handlers.polyline.tooltip.cont = '{{ trans('front.click_to_continue_drawing_line') }}';
    handlers.polyline.tooltip.end = '{{ trans('front.click_last_point_to_finish_line') }}';

    var ua = null;
    // Verificar o tipo de mobile usado
    var isMobile = {
        Windows: function() {
            return /IEMobile/i.test(navigator.userAgent);
        },
        Android: function() {
            return /Android/i.test(navigator.userAgent);
        },
        BlackBerry: function() {
            return /BlackBerry/i.test(navigator.userAgent);
        },
        iOS: function() {
            ua = navigator.userAgent.toLowerCase();
            return /iPhone|iPad|iPod/i.test(navigator.userAgent);
        },
        any: function() {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
        }
    };
    /*ua = navigator.userAgent.toLowerCase();
    
    var isiOS = ua.indexOf("mac") > -1; //&& ua.indexOf("mobile");
    if (isiOS) {
        createCookie("mobile_type", "iOS", "1");
       //alert("teste");
    } */
	
    
// Creating a cookie after the document is ready




// Function to create the cookie
function createCookie(name, value, days) {
    var expires;

    if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toGMTString();
    }
    else {
    expires = "";
    }

    document.cookie = escape(name) + "=" + 
    escape(value) + expires + "; path=/";
}
    /// FIM
    
function getLocation1() {
		
		if (navigator.geolocation) {
			retorno = navigator.geolocation.getCurrentPosition(function (position) {		
			retorno = position.coords.latitude + "," + position.coords.longitude;
			console.log(retorno);
			
			
			document.cookie = "coord_phone="+retorno; 
			
		}, showError);
		} else { 
			retorno = "current+location";
		}
		
		
	}

	
	function showError(error) {
		switch(error.code) {
			case error.PERMISSION_DENIED:
				retorno =  "User denied the request for Geolocation."
				break;
			case error.POSITION_UNAVAILABLE:
				retorno =  "Location information is unavailable."
				break;
			case error.TIMEOUT:
				retorno =  "The request to get user location timed out."
				break;
			case error.UNKNOWN_ERROR:
				retorno =  "An unknown error occurred."
				break;
		}
	}
</script>

@yield('scripts')
@include('Frontend.Layouts.partials.app')

<script type="text/javascript">
    $(window).on("load", function() {
        app.init();
    });
</script>

<script type="text/javascript" src="resources/js/socket.io.js"></script>
<!--script type="text/javascript" src="{{ asset('js/socket.io.js') }}"></script-->

@include('Frontend.Popups.index')
</body>
</html>
