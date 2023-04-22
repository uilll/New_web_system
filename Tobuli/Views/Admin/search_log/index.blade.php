@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" ">

    <div class="panel-heading">
        <div class="panel-title"><i class="icon logs"></i> Pesquisar na log</div>
        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['id' => 'search_log', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">monitoring</span>
            </div>
        </div>
    </div>

    

    <div class="panel-body" data-table>
        @include('Admin.search_log.table')
    </div>
</div>
@stop
