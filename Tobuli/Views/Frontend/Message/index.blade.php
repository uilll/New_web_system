@extends('Frontend.Layouts.modal')
@section('modal_class', 'modal-lg')

@section('title')
    <i class="icon icon-fa fa-comments-o"></i> Central de Mensagens
@stop
@section('body')
    <div class="row no-padding">
            <style>
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
                    float: right;
                }

                .message-reply.admin {
                    background-color: #e0e0e0;
                    border-radius: 10px;
                    padding: 10px;
                    margin-bottom: 10px;
                    width: 75%;
                    float: left;
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
        <div class="col-md-4">
                <div class="panel panel-default" id="setup-form-itemsSimple" >
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Assuntos</h4>
                            </div>
                            <div id="mensagens" class="panel-body scrollable scrollable-messages">
                                <div data-table>
                                @foreach($messages as $message)
                                    <div class="message" data-message-id="{{ $message->id }}" data-client-id="{{ $message->client_id }}">
                                        <p><strong>{{ $message->subject }}</strong></p>
                                    </div>
                                @endforeach
                            </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Mensagens e respostas</h4>
                        </div>
                        <div id="replies" class="panel-body scrollable scrollable-messages">
                            <!-- aqui as mensagens e respostas serão apresentadas -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Nova mensagem 
                                <a href="javascript:" type="button" class="btn btn-primary btn-sm add-message hide" 
                                            data-modal="message_create" 
                                            data-url="{{ route("messages.create") }}"
                                            data-customer-id=""
                                            data-customer-name=""
                                >

                                    <i class="fa fa-plus" title="{{ trans('admin.add_new_user') }}"></i>
                                </a>
                            
                            </h4>
                            
                        </div>
                        <div class="panel-body">
                            <form id="create-reply-form" data-client-id="{{ $message->client_id }}" data-message-id="{{ $message->id }}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="client_id" value="{{ $message->client_id }}">
                                <input type="hidden" name="company_id" value="{{ $message->company_id }}">
                                <input type="hidden" name="user_id" value="{{ $message->user_id }}">
                                <input type="hidden" name="sender_type" value="1">
                                <input type="hidden" name="is_to_client" value=true>
                                <div class="form-group">
                                    <textarea name="body" class="form-control" rows="3" placeholder="Digite sua resposta"></textarea>
                                </div>
                                
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        //var updateInterval = setInterval(updateActiveReply, 3000);

        $('#messages').on('hidden.bs.modal', function () {
            clearInterval(updateInterval);
        });

        $('#messages').on('shown.bs.modal', function () {
            updateInterval = setInterval(updateActiveReply, 3000);
        });


        
        

        $(document).ready(function() {
            $('.message').click(function() {
                 // Remover a classe active das outras mensagens
                $('.message').removeClass('active');

                // Adicionar a classe active à mensagem selecionada
                $(this).addClass('active');

                //console.log("ola");
                var message = $(this).text();
                const messageId = $(this).data('message-id');
                const clientId = $(this).data('client-id');
                var subject = encodeURIComponent(message);

                // Remover o conteúdo atual de #replies
                $('#replies').html('');

                // Atualize o atributo data-client-id do formulário create-reply-form
                $('#create-reply-form').attr('data-message-id', messageId);

                var ajaxUrl = '{{ route("messages.get_messages", ["client_id" => ":client_id", "subject" => ":subject"]) }}'.replace(':client_id', clientId).replace(':subject', messageId);
                //console.log("URL da chamada AJAX: " + ajaxUrl);
                // Carregar as mensagens do assunto correspondente
                $.ajax({
                    url: ajaxUrl,
                    type: 'GET',
                    dataType: 'html',
                    beforeSend: function() {
                        //console.log("Iniciando chamada AJAX...");
                    },
                    success: function(response) {
                        //console.log("entrou3?");
                        $('#replies').html(response);

                        // Limpe o conteúdo do textarea
                        $("textarea[name='body']").val("");

                        // Role a barra de rolagem do elemento "mensagens" para baixo
                        $("#replies").scrollTop($("#replies")[0].scrollHeight);

                        //Atualizar o status de leitura das mensagens
                        var respons = updateMessageIsRead(messageId);
                        console.log(respons);
                    },
                    complete: function(xhr, textStatus) {
                            // Exibir a mensagem "Vista" para as respostas do admin que não estão marcadas como lidas
                            $('.message-reply.admin:not([data-is-read="1"]) .viewed-message').show();

                            // Atualizar o campo is_read via AJAX
                            var unreadAdminReplies = $('.message-reply.admin:not([data-is-read="1"])');
                            unreadAdminReplies.each(function() {
                                var replyId = $(this).data('reply-id');
                                var updateIsReadUrl = '{{ route("message_replies.update_is_read", ["message_id" => ":id"]) }}'.replace(':id', replyId); // Atualize com a rota correta

                                $.ajax({
                                    url: updateIsReadUrl,
                                    type: 'PUT',
                                    data: { is_read: 1 },
                                    success: function(response) {
                                        // Atualizar o atributo data-is-read para 1
                                        $('.message-reply.admin[data-reply-id="' + replyId + '"]').attr('data-is-read', '1');
                                    },
                                    error: function(xhr, textStatus, errorThrown) {
                                        console.log("Erro ao atualizar o campo is_read: " + textStatus);
                                        console.log("Detalhes do erro: " + errorThrown);
                                        console.log(xhr);
                                    }
                                });
                            });

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log("Erro na chamada AJAX: " + textStatus);
                        console.log("Detalhes do erro: " + errorThrown);
                        console.log(xhr);
                    }
                });



            });

            $('#create-reply-form').on('submit', function(event) {
                event.preventDefault();

                const activeMessage = $('.message.active');
                const clientId = activeMessage.data('client-id');
                const messageId = activeMessage.data('message-id');
                

                // Obtenha o ID da mensagem atualmente selecionada
                var url = '{{ route("message_replies.store", "id") }}'.replace('id', messageId);

                var formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Atualize o conteúdo de $('#replies') com a nova mensagem
                        // Você pode personalizar isso de acordo com a estrutura do seu HTML e a resposta do servidor
                        $('#replies').append(response);

                        // Limpe o conteúdo do textarea
                        $("textarea[name='body']").val("");

                        // Role a barra de rolagem do elemento "mensagens" para baixo
                        $("#replies").scrollTop($("#replies")[0].scrollHeight);
                    },
                    complete: function(xhr, textStatus) {
                        //console.log(clientId, messageId);
                        updateMessages(clientId, messageId);
                        //console.log("Chamada AJAX concluída com status: " + textStatus);
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        // Manipule os erros aqui
                        console.log("Erro na chamada AJAX: " + textStatus);
                        console.log("Detalhes do erro: " + errorThrown);
                        console.log(xhr);
                    }
                });
            });

            // Enviar o formulário ao pressionar Enter no textarea
            $("textarea[name='body']").on('keydown', function(event) {
                //console.log("Tecla pressionada: " + event.keyCode);
                if (event.keyCode === 13 && !event.shiftKey) {
                    //console.log("Enter pressionado (sem Shift)");
                    event.preventDefault();
                    $('#create-reply-form').submit();
                }
            });

            
        });


    function updateMessages(clientId, messageId) {
        var ajaxUrl = '{{ route("messages.get_messages", ["client_id" => ":client_id", "subject" => ":subject"]) }}'.replace(':client_id', clientId).replace(':subject', messageId);

        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                $('#replies').html(response);
                $("#replies").scrollTop($("#replies")[0].scrollHeight);

            },
            error: function(xhr, textStatus, errorThrown) {
                console.log("Erro ao atualizar mensagens: " + textStatus);
                console.log("Detalhes do erro: " + errorThrown);
                console.log(xhr);
            }
        });
    }

    function updateActiveReply() {
        
        //const activeMessage = $('.message.active');
        var activeReplyId = $('.message.active').data('message-id');
        const clientId = $('.message.active').data('client-id');
        //console.log(activeReplyId);
        if (activeReplyId) {
            //console.log("oi2");
            updateMessages(clientId, activeReplyId);
            updateMessageIsRead(activeReplyId);
        }
    }

    function updateMessageIsRead(messageId) {
        var url = '{{ route("message_replies.update_is_read", "id") }}'.replace('id', messageId);

        $.ajax({
            url: url,
            type: 'PUT',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    console.log('Status de leitura da mensagem atualizado com sucesso');
                } else {
                    console.error(response.error);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }


    </script>
@stop

@section('buttons')
@stop

