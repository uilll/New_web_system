@extends('Admin.Layouts.default')

@section('content')
 
<div class="panel panel-default" id="table_{{ $section }}">
    <input type="hidden" name="sorting[sort_by]" value="{{ $items->sorting['sort_by'] }}" data-filter>
    <input type="hidden" name="sorting[sort]" value="{{ $items->sorting['sort'] }}" data-filter>
        
    <div class="panel-heading">
        <ul class="nav nav-tabs nav-icons pull-right">
            @if( Auth::User()->perm('devices', 'edit') )
            <li role="presentation" class="">
                <a href="javascript:" type="button" class="" data-modal="{{ $section }}_create" data-url="{{ route("admin.tracker.create") }}">
                    <i class="icon plus" title="Adicionar novo rastreador"></i>
                </a>
            </li>
            <li role="presentation" class="">
                
            </li>
            @endif
        </ul>
      
        <div class="panel-title"><i class="icon check"></i> Rastreadores </div>
        
        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['id' => 'search_admin_', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">Tracker</span>
            </div>
        </div>
    </div>
        
     <div class="table_error"></div>
    <div class="table-responsive">
        <table class="table table-list" data-toggle="multiCheckbox">
            <thead>
            <tr>
                {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
                {!! tableHeader('Status') !!}
                {!! tableHeader('IMEI') !!}
                {!! tableHeader('Marca') !!}
                {!! tableHeader('Modelo') !!}
                {!! tableHeader('Em Uso') !!}
                {!! tableHeader('Manutenção') !!}
                {!! tableHeader('Última manutenção') !!}
                {!! tableHeader('Quantidade de manutenções') !!}
                {!! tableHeader('Dados excedentes') !!}
                {!! tableHeader('Operando desde') !!}
                {!! tableHeader('Histórico') !!}
                {!! tableHeader('Número SIM') !!}
                {!! tableHeader('ICCD') !!}
                {!! tableHeader('APN') !!}
            </tr>
            </thead>

            <tbody>
                @foreach ($trackers as $item2)
                    <tr style="color: {{ $item2->active ? 'black' : 'gray' }}">
                        <td class="actions">
                            @if (Auth::User()->perm('devices', 'edit') || Auth::User()->perm('devices', 'remove'))
                                <div class="btn-group dropdown droparrow" data-position="fixed">
                                    <i class="btn icon edit" data-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="true"></i>
                                    <ul class="dropdown-menu">
                                        @if( Auth::User()->perm('devices', 'edit') )
                                            <li>
                                                <a href="javascript:" data-modal="devices_edit"
                                                   data-url="{{ route("admin.tracker.edit", ['id' => $item2->id]) }}">
                                                    {{ trans('global.edit') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="label label-sm label-{!! $item2->active ? 'success' : 'danger' !!}">
                                {!! trans('validation.attributes.active') !!}
                            </span>
                        </td>
                        <td> {{$item2->imei}}</td>
                        <td>{{$item2->brand}} </td>
                        <td>
                            {{ $item2->model }} 
                        </td>
                        <td>
                            {{ $item2->in_use }} 
                        </td>
                        <td>
                            {{ $item2->in_service }}
                        </td>
                        <td>
                            {{ $item2->maintence_date }}
                        </td>
                        <td>
                            {{ $item2->maintence_quant }}
                        </td>
                        <td>
                            {{ $item2->work_since }}
                        </td>
                        <td>
                            {!! $item2->excess_data ? 'Excesso' : 'Normal' !!}
                        </td>
                        <td>
                            {{ $item2->history }}
                        </td>
                        <td>
                            {{ $item2->sim_number }}
                        </td>
                        <td>
                            {{ $item2->iccd }}
                        </td>
                        <td>
                            {{ $item2->operator }}
                        </td>
                    </tr>
                    <tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="nav-pagination">
        {!! $trackers->render() !!}
    </div>
    

</div>

@stop

@section("javascript")
<script>
   
</script>
@stop