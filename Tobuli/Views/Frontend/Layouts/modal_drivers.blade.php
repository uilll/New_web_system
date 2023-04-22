<div class="modal-lg @yield('modal_class')" style="position: fixed; left: 25%; top: 10%">
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
                    <button type="button" class="btn btn-action" data-dismiss="modal">Ok</button>
                @show
            </div>
        </div>
    </div>
</div>