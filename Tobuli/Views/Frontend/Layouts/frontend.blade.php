<!doctype html>
<html lang="en" class="no-js" itemscope itemtype="http://schema.org/WebSite">
<head>
    <title>{{ settings('main_settings.server_name') }}</title>

    <base href="{{ url('/') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GPS Tracking System for Personal Use or Business">
    <link rel="shortcut icon" href="{{ asset_logo('favicon') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('assets/css/'.settings('main_settings.template_color').'.css?v='.config('tobuli.version')) }}">
    @if (file_exists(storage_path('custom/css.css')))
        <link rel="stylesheet" href="{{ asset('assets/css/custom.css?t=') . filemtime(storage_path('custom/css.css')) }}">
    @endif
    @if (file_exists(storage_path('custom/js.js')))
        <script src="{{ asset('assets/js/custom.js?t=') . filemtime(storage_path('custom/js.js')) }}" type="text/javascript"></script>
    @endif

    @yield('styles')

    <style>
        @if ( settings('main_settings.login_page_background_color') )
        body.sign-in-layout { background-color: {{ settings('main_settings.login_page_background_color') }}; }
        @endif

        @if ( settings('main_settings.login_page_text_color') )
        body.sign-in-layout .sign-in-text { color: {{ settings('main_settings.login_page_text_color') }}; }
        @endif

        @if ( has_asset_logo('background') )
        body.sign-in-layout { background-image: url( {!! asset_logo('background') !!} ); }
        @endif

        @if ( settings('main_settings.login_page_panel_background_color') )
        body.sign-in-layout .panel-background { background: {{ settings('main_settings.login_page_panel_background_color') }}; }
        @endif

        @if ( settings('main_settings.login_page_panel_transparency') != null )
        <?php $opacity = 1 - (int)settings('main_settings.login_page_panel_transparency') / 100; ?>
        body.sign-in-layout .panel-background { opacity: {{ $opacity }}; }
        @endif
    </style>
</head>

<!--[if IE 8 ]><body class="ie8 sign-in-layout"> <![endif]-->
<!--[if IE 9 ]> <body class="ie9 sign-in-layout"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><body class="sign-in-layout"><!--<![endif]-->

<div class="center-vertical">
    <div class="container">
        <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
            @yield('content')
        </div>
    </div>
</div>
<script>
   function showPassword_() {
    var b = document.querySelector("#sign-in-form-password"); 
    b.setAttribute("type", "text");
}
</script>
</body>
</html>