<div class="modal-dialog @yield('modal_class')">
    <div class="modal-content">
        <div class="modal-header">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css"> </link>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span>×</span></button>
            <h4 class="modal-title">@yield('title')</h4>
        </div>
        <div class="modal-body">
            @yield('body')
        </div>
        <div class="modal-footer">
                <div class="buttons">
                    @section('buttons') 
                    <button type="button" class="btn btn-default fa fa-waze" data-dismiss="modal">{!!trans('global.cancel')!!}</button>
                    @show
                </div>
            </div>
    </div>
</div>