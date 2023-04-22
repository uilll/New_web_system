@foreach($notifications as $notification)
    <div class="modal-popup fade {!! $notification->data['position'] !!}" id="popup{{$notification->id}}" tabindex="-1" role="dialog" data-modaloverflow="true" data-backdrop="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    @if( ! empty($notification->data['title']) )
                        <div class="modal-title text-center">{!! $notification->data['title'] !!}</div>
                    @endif
                </div>

                <div class="modal-body text-center">
                    {!! $notification->data['content'] !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        $(window).on("load", function() {
            setTimeout(function(){
                $('#popup{{$notification->id}}').modal('show');
            }, 15000);
        });
    </script>
@endforeach

