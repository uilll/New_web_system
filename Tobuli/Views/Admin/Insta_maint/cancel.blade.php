@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Cancelando OS nº'.$service->os_number !!}
@stop

@section('body')
    @if (isAdmin())
        @if(!$service->payable)
            {!!Form::open(['route' => 'admin.insta_maint.canceled', 'method' => 'POST'])!!}
            {!!Form::hidden('id',$service->id)!!}
            <div class="tab-content">
                <div id="insta-maint-cancel-form-main" class="tab-pane active">
                    <div class="row">
                        <div class="col-md-12">
                            {!!Form::label('motivo', 'Motivo do cancelamento:')!!}
                            {!!Form::textarea('motivo', null, ['class' => 'form-control','required'])!!}
                        </div>               
                    </div>
                </div>
            </div>
            {!!Form::close()!!}
        @else
        {!!Form::open(['route' => 'admin.insta_maint.canceled', 'method' => 'POST'])!!}
        {!!Form::hidden('id',$service->id)!!}
        <div class="tab-content">
            <div id="insta-maint-cancel-form-main" class="tab-pane active">
                <div class="row">
                    <div class="col-md-12">
                        <h3> Não foi possível fazer o cancelamento desta OS, pois o pagamento já foi realizado</h3>        
                    </div>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
        @endif
    @endif
    
@stop

