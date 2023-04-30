@extends('Frontend.Layouts.modal')

@section('title')
    <i class="icon device"></i> {!! 'Tem certeza que deseja deletar?' !!}
@stop

@section('body')
    @if (isAdmin())
        {!!Form::open(['route' => 'asaas.clientes.excluirCliente', 'method' => 'delete', 'id' => 'deletar_cliente'])!!}

        {!!Form::hidden('id',$id)!!}

        {!!Form::close()!!}
    @endif
@stop