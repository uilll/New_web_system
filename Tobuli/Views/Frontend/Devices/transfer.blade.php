@extends('Frontend.Layouts.modal')

@section('title')
    {{ "TRANSFERIR DADOS" }}
@stop

@section('body')
   <!-- Parei aqui -->
    {!!Form::open(['route' => 'devices.transfer_now'])!!}
    {!!Form::hidden('id', $item->id)!!}

    <div class="form-group">
        {!! "Tranferência total de dados" !!}

        @if (isAdmin())
            <div class="form-group">
                {!! Form::label(null, "Transferido de: ".$item->plate_number) !!}
            </div>
            
            <div class="form-group">
                {!! Form::label(null, "Tranferir para: ") !!}
                {!! Form::select('new_id', $devices->pluck('plate_number', 'id'), $item->id, ['class' => 'form-control', 'data-live-search' => true]) !!}
            </div>
        @endif
    </div>

    {!!Form::close()!!}
@stop

@section('buttons')
    <a type="button" class="btn btn-action" data-submit="modal">{{ trans('admin.confirm') }}</a>
    <a type="button" class="btn btn-default" data-dismiss="modal">{{ trans('admin.cancel') }}</a>
@stop