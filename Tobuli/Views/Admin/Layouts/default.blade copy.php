<?php $version = Config::get('tobuli.version'); ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->

<head>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
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

            .arrow::before {
                content: '';
                display: inline-block;
                border-top: 6px solid transparent;
                border-right: 9px solid #555;
                border-bottom: 6px solid transparent;
                margin-right: 10px;
                transform: rotate(90deg);
            }

            .client-messages {
                display: none;
            }

            .message-reply {
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 20px;
                width: 75%;
            }

            .message-reply.client {
                background-color: #f5f5f5;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 10px;
                width: 75%;
                float: left;
            }

            .message-reply.admin {
                background-color: #e0e0e0;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 10px;
                width: 75%;
                float: right;
            }

            .message-reply.client .viewed-message {
                font-size: 0.7em;
                float: left;
                margin-top: 5px;
                color: #999999;
            }

            .message-reply.admin .viewed-message {
                font-size: 0.7em;
                float: right;
                margin-top: 5px;
                color: #999999;
            }

            .scrollable {
                max-height: calc(100vh - 200px);
                overflow-y: auto;
            }

            .scrollable-messages {
                max-height: calc(100vh - 400px);
            }

        </style>

    <style scoped>
        .scan-confirmation {
        position: absolute;
        width: 100%;
        height: 100%;

        background-color: rgba(255, 255, 255, .8);

        display: flex;
        flex-flow: row nowrap;
        justify-content: center;
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
    var shouldReload = false;
    var clientName = "";
    var clientId = "";

    $(document).ready(function() {
            var navbarHeight = $('#bs-header-navbar-collapse').outerHeight(true) - 50;
            //console.log(navbarHeight);
            $('.content').css('margin-top', navbarHeight + 'px');

            //Pesquisa dos clientes nas mensagens
            $('#search').on('input', function() {
            var searchValue = $(this).val().toLowerCase();
            $('.client-item').each(function() {
                    var clientName = $(this).find('.client-name').text().toLowerCase();
                    if (clientName.indexOf(searchValue) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        
            //Criando nova mensagem para um determinado cliente:
            // Show add-message button when a client name is clicked
            $('.client-name').on('click', function() {
                // Remove active class from all client names
                $('.client-name').removeClass('active');
                // Add active class to clicked client name
                $(this).addClass('active');
                // Toggle arrow direction
                $('.arrow_').removeClass('fa-chevron-down').addClass('fa-chevron-right');
                // Hide all client messages
                $('.client-messages').slideUp();
                // Show client messages for clicked client
                $(this).siblings('.client-messages').slideDown();
                // Toggle arrow direction for clicked client
                $(this).find('.arrow_').toggleClass('fa-chevron-down fa-chevron-right');
                // Show client messages
                $('.client-messages').hide();
                $(this).next('.client-messages').show();
                // Show add-message button
                $('.add-message').removeClass('hide').addClass('d-block');
                // Get client name
                clientName = $(this).text();
                // Set client name in the modal
                $('#client-name').text(clientName);
                clientId = $(this).attr('data-customer-id');

                console.log(clientId);
                // Update data-customer-id and data-customer-name attributes
                $('.add-message').attr('data-customer-id', $('.client-name').attr('data-customer-id'));
                $('.add-message').attr('data-customer-name', clientName);
            });

            $('.message-subject').on('click', function(e) {
                e.preventDefault();
                var message = $(this).text();
                var client_id = $(this).data('client-id');
                var company_id = $(this).data('company-id');
                var user_id = $(this).data('user-id');
                var subject = $(this).data('subject-id');

                // Atualizar os campos hidden
                $('input[name="client_id"]').val(client_id);
                $('input[name="company_id"]').val(company_id);
                $('input[name="user_id"]').val(user_id);

                //console.log(message,client_id,subject);

                $.ajax({
                    url: '{{ route("messages.get_messages", ["client_id" => ":client_id", "subject" => ":subject"]) }}'.replace(':client_id', client_id).replace(':subject', subject),
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        var messagesDiv = $('#mensagens');
                        messagesDiv.html(response);
                        messagesDiv.scrollTop(messagesDiv.prop("scrollHeight"));
                        
                        scrollToBottom();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });

                // Obter o ID do subject clicado
                var subjectId = $(this).data('subject-id');
                
                // Atualizar o atributo action do formulário
                var newAction = "{{ route('message_replies.store', 'ID') }}".replace('ID', subjectId);
                $('#create-reply-form').attr('action', newAction);
            });

            $(document).on('keydown', '#create-reply-form textarea', function(e) {
                if (e.keyCode == 13 && !e.shiftKey) {
                    e.preventDefault();
                    $(this.form).submit();
                }
            });

            $('#create-reply-form').on('submit', function(e) {
                 e.preventDefault();

                var form = $(this);
                var token = $('input[name="_token"]').val(); // Adicione esta linha para obter o token CSRF

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    dataType: 'json',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': token // Adicione esta linha para incluir o token CSRF no cabeçalho da solicitação
                    },
                    success: function(response) {
                        //console.log("entrou1?");
                        //console.log("response:", response);
                        if (response.success) {
                            //console.log("entrou2?");
                            var message = $('.message-subject[data-subject-id="' + response.message_id + '"]').text();
                            var client_id = response.client_id;
                            var subject = encodeURIComponent(message);

                            var ajaxUrl = '{{ route("messages.get_messages", ["client" => ":client_id", "subject" => ":subject"]) }}'.replace(':client_id', client_id).replace(':subject', subject);
                            //console.log("ajaxUrl:", ajaxUrl); // Adicione esta linha para verificar a URL AJAX
                            
                            $.ajax({
                                url: ajaxUrl,
                                type: 'GET',
                                dataType: 'html',
                                success: function(response) {
                                    //console.log("entrou3?");
                                    $('#mensagens').html(response);

                                    // Limpe o conteúdo do textarea
                                    $("textarea[name='body']").val("");

                                    // Role a barra de rolagem do elemento "mensagens" para baixo
                                    $("#mensagens").scrollTop($("#mensagens")[0].scrollHeight);

                                },
                                error: function(xhr, status, error) {
                                    console.error(xhr.responseText);
                                }
                            });
                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            
    });

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
            url= "https://bd.carseg.com.br/admin/users/"+$("#search_menu").text()+"/page/1/"+$("#search_admin_").val();
            $(location).prop('href', url);
        }    
        
    });
    $(document).on("keydown", "#search_log", function (e) {
        var tecla = (e.keyCode?e.keyCode:e.which);                        
        if(tecla == 13){
            if(!$("#search_log").val()==""){
                url= "https://bd.carseg.com.br/admin/logs/search/"+$("#search_log").val();
                $(location).prop('href', url);
            }
        }    
        
    });

    const searchInput = document.getElementById('search_asaas');
    searchInput.addEventListener('keydown', function(event) {
        if (event.keyCode === 13) { // 13 é o código da tecla "Enter"
        event.preventDefault(); // previne o comportamento padrão de enviar o formulário
        this.form.submit(); // envia o formulário
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
       //iframe.attr("src", "https://bd.carseg.com.br/devices/follow_map/577");
       
    });
    $.ajaxSetup({cache: false});
    window.lang = {
        nothing_selected: '{{ trans('front.nothing_selected') }}',
        color: '{{ trans('validation.attributes.color') }}',
        from: '{{ trans('front.from') }}',
        to: '{{ trans('front.to') }}',
        add: '{{ trans('global.add') }}'
    };
    $(document).on('hidden.bs.modal', '.modal', function () {
            location.reload();
    });
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

 