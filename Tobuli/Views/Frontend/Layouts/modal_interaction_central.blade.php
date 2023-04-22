<div class="modal-dialog @yield('modal_class')">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span>×</span></button>
            <h4 class="modal-title">@yield('title')</h4>
        </div>
        <div class="modal-body">
            @yield('body')
        </div>
        <div class="modal-footer">
                <div class="buttons">
                    @section('buttons') 
                        {!! Form::submit( 'Ciente', ['class' => 'btn btn-action', 'data-submit'=>'modal', 'name' => 'action', 'value' => 'reply'])!!}
                        {!! Form::submit( 'Lembrar depois...', ['class' => 'btn btn-secondary', 'data-dismiss'=>'modal', 'name' => 'interaction_later', 'value' => 'later', 'onclick'=>$interaction_later ])!!}
                        </a-->
                    @show
                </div>
            </div>
    </div>
</div>