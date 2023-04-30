

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                @if (Auth::User()->isAdmin())
                    <div class="message-body" style="background-color: #e0e0e0; border-radius: 10px; padding: 10px; margin-bottom: 10px; float: {{ $message->is_to_client == 1 ? 'right' : 'left' }}; width: 75%;">
                            {{ $message->body }}
                    </div>
                @else
                    <div class="message-body" style="background-color: #e0e0e0; border-radius: 10px; padding: 10px; margin-bottom: 10px; float: {{ $message->is_to_client == 1 ? 'left' : 'right' }}; width: 75%;">
                            {{ $message->body }}
                    </div>
                @endif
                
                @foreach ($messages as $reply)
                    <?php //dd($reply->is_read); ?>
                    <div class="message-reply {{ $reply->sender_type == 1 ? 'client' : 'admin' }}" data-reply-id="{{ $reply->id }}" data-is-read="{{ $reply->is_read }}">
                        <div>
                            {{ $reply->body }}
                        </div>
                        @if($reply->is_read == 1)
                            <span class="viewed-message" style="display: block; text-align: right;">Visto</span>
                        @endif
                    </div>
                @endforeach


            </div>
        </div>
    </div>
</div>
