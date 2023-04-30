@extends('Admin.Layouts.default')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Clientes e assuntos</h4>
                </div>
                <div id="Lista_clientes" class="panel-body scrollable">
                    <div class="form-group">
                        <input type="text" class="form-control" id="search" placeholder="Pesquisar cliente...">
                    </div>
                    <ul class="list-group" id="client-list" style="margin-top: 40px;">
                        @foreach($customers as $client)
                            <li class="list-group-item client-item">
                                <a href="#" class="client-name" data-customer-id="{{ $client->id }}"><i class="arrow_ fa fa-chevron-right"></i>{{ $client->name }}</a>
                                <ul class="list-group client-messages">
                                    @foreach($messages->where('client_id', $client->id) as $message)
                                        <li class="list-group-item">
                                            <a href="#" class="message-subject" data-subject-id="{{$message->id}}" data-client-id="{{$message->client_id}}" data-company-id="{{$message->company_id}}" data-user-id="{{$message->user_id}}">{{ $message->subject }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>

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
                        <div id="mensagens" class="panel-body scrollable scrollable-messages">
                            <!-- List of messages and replies -->
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
                            <form id="create-reply-form" action="{{ route('message_replies.store', "id") }}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="client_id" value="{{ $message->client_id }}">
                                <input type="hidden" name="company_id" value="{{ $message->company_id }}">
                                <input type="hidden" name="user_id" value="{{ $message->user_id }}">
                                <input type="hidden" name="sender_type" value="admin">
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
</div>

@endsection
