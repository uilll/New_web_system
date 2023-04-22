@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" ">

    <div class="panel-heading">
        <div class="panel-title"><i class="icon logs"></i> Pesquisar chips</div>
        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['id' => 'search_log', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">monitoring</span>
            </div>
            <a href="javascript:" data-modal="monitorings_info"
                data-url="{{ route("admin.chips.import_filter") }}">
                Filtrar Chips para cancelamento
            </a>
        </div>
    </div>

    

    <div class="panel-body" data-table> 
        {!!Form::open(['route' => 'admin.chips.upload', 'method' => 'POST', 'id'=>'upload_file', 'enctype'=>"multipart/form-data"])!!}
            

            {!!Form::file('userfile')!!}
            {!!Form::submit('Enviar')!!}
        {!!Form::close()!!}
         
    </div>
</div>
@stop
