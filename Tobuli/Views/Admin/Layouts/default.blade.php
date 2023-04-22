<?php $version = Config::get('tobuli.version'); ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <title>{{ settings('main_settings.server_name') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="shortcut icon" href="{{ asset_logo('favicon') }}"/>
    
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/'.settings('main_settings.template_color').'.css?v=' . config('tobuli.version')) }}" />
    <style type="text/css">
            .printable {
                display: none;
            }
            /* print styles*/
            @media print {
                
                .screen {
                      display: none;
                      visibility:hidden;
                 }
                 .printable {
                      display: block;
                      visibility:visible;
                      position: absolute;
                      top:0;
                      left:0;                                     
                 }
            }
        </style>
    @yield('styles')
</head>

<body class="admin-layout">

<div class="header">
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
                <a class="navbar-brand" href="javascript:"><img src="{{ asset_logo('logo') }}"></a>
                @endif

                <p class="navbar-text">ADMIN</p>
            </div>

            <div class="collapse navbar-collapse" id="bs-header-navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    {!! getNavigation() !!}
                </ul>
            </div>
        </div>
    </nav>
</div>

<div class="content">
    <div class="container-fluid">
        @if (Session::has('success'))
            <div class="alert alert-success">
                {!! Session::get('success') !!}
            </div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-danger">
                {!! Session::get('error') !!}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<div id="footer">
    <div class="container-fluid">
        <p>
            <span>{{ date('Y') }} &copy; {{ settings('main_settings.server_name') }}
            | {{ Facades\Server::ip() }}
            | v{{ config('tobuli.version') }}
            @if (Auth::User()->isAdmin())
                @if ( ! empty($_ENV['limit']))
                    | {{ ($_ENV['limit'] == 1 ? trans('front.limit_1') : '1-'.$_ENV['limit']).' '.strtolower(trans('front.objects')) }}
                @endif
                | {{ trans('front.last_update') }}: {{ datetime(Facades\Server::lastUpdate()) }}
                @if ( ! Facades\Server::isAutoDeploy())
                | <i style="color: red;">Automatic updates disabled</i>
                @endif
            @endif
            </span>
        </p>
    </div>
</div>

@include('Frontend.Layouts.partials.trans')

<script src="{{ asset('assets/js/core.js?v='.$version) }}"></script>
<script src="{{ asset('assets/js/app.js?v='.$version) }}"></script> 

@yield('javascript')
<script>
    function showmap(device_id) {
        //alert(device_id+"teste");
        $("#segue"+device_id).show();
        $("#fechar_mapa"+device_id).show();
        $("#segue"+device_id).attr('src', $('#segue'+device_id).attr('src'));
    }
    
    function hidemap(device_id) {
        //alert(device_id+"teste");
        $("#segue"+device_id).css("display", "none");
        $("#fechar_mapa"+device_id).css("display", "none");
        //$("#segue"+device_id).attr('src', $('#segue'+device_id).attr('src'));
    }
    
    $(document).on("keydown", "#search_admin_", function (e) {
        var tecla = (e.keyCode?e.keyCode:e.which);                        
        if(tecla == 13){
            url = "{{ asset('/admin/users')}}"+"/"+$("#search_menu").text()+"/page/1/"+$("#search_admin_").val();
            $(location).prop('href', url);
        }    
        
    });
    $(document).on("keydown", "#search_log", function (e) {
        var tecla = (e.keyCode?e.keyCode:e.which);                        
        if(tecla == 13){
            if(!$("#search_log").val()==""){
                url = "{{ asset('/admin/logs/search')}}"+"/"+$("#search_log").val();
                $(location).prop('href', url);
            }
        }    
        
    });
    $("body").on("keydown", "#contact_", function(event){
            if (event.keyCode == 13) {
                $('#contact_').val($('#contact_').val()+'\n');
            event.preventDefault();
            return false;
            }
        });
    $(document).on("click", ("#segue__"), function (){
       //var iframe = $("#segue");
       //alert($("#device_id").text())
        //$("#segue").show();
        //$("#segue").attr('src', $('#segue').attr('src')); // $('#map_iframe').attr('src', $('#map_iframe').attr('src'));
       //iframe.attr("src", "https://admin.carseg.com.br/devices/follow_map/577");
       
    });
    $.ajaxSetup({cache: false});
    window.lang = {
        nothing_selected: '{{ trans('front.nothing_selected') }}',
        color: '{{ trans('validation.attributes.color') }}',
        from: '{{ trans('front.from') }}',
        to: '{{ trans('front.to') }}',
        add: '{{ trans('global.add') }}'
    };
</script>

<div class="modal" id="modalDeleteConfirm">
    <div class="contents">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 class="modal-title thin" id="modalConfirmLabel">{{ trans('admin.delete') }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ trans('admin.do_delete') }}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-main" onclick="modal_delete.del();">{{ trans('admin.yes') }}</button>
                    <button class="btn btn-side" data-dismiss="modal" aria-hidden="true">{{ trans('global.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="js-confirm-link" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                loading
            </div>
            <div class="modal-footer" style="margin-top: 0">
                <button type="button" value="confirm" class="btn btn-main submit js-confirm-link-yes">{{ trans('admin.confirm') }}</button>
                <button type="button" value="cancel" class="btn btn-side" data-dismiss="modal">{{ trans('admin.cancel') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modalError">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 class="modal-title thin" id="modalErrorLabel">{{ trans('global.error_occurred') }}</h3>
            </div>
            <div class="modal-body">
                <p class="alert alert-danger"></p>
            </div>
            <div class="modal-footer">
                <button class="btn default" data-dismiss="modal" aria-hidden="true">{{ trans('global.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalSuccess">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 class="modal-title thin" id="modalSuccessLabel">{{ trans('global.warning') }}</h3>
            </div>
            <div class="modal-body">
                <p class="alert alert-success"></p>
            </div>
            <div class="modal-footer">
                <button class="btn default" data-dismiss="modal" aria-hidden="true">{{ trans('global.close') }}</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>

 