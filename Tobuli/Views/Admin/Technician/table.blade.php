@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" id="table_{{ $section }}">
    <input type="hidden" name="sorting[sort_by]" value="{{ $items->sorting['sort_by'] }}" data-filter>
    <input type="hidden" name="sorting[sort]" value="{{ $items->sorting['sort'] }}" data-filter>
    
    <div class="panel-heading">
        <ul class="nav nav-tabs nav-icons pull-right">
            @if( Auth::User()->perm('devices', 'edit') )
            <li role="presentation" class="">
                <a href="javascript:" type="button" class="" data-modal="{{ $section }}_create" data-url="{{ route("admin.technician.create") }}">
                    <i class="icon plus" title="Adicionar nova ocorrência"></i>
                </a>
            </li>
            <li role="presentation" class="">
                
            </li>
            @endif
        </ul>

        <div class="panel-title"><i class="icon check"></i> Técnicos </div>

        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['id' => 'search_admin_', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">Technician</span>
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
                {!! tableHeader('Nome') !!}
                {!! tableHeader('Contato') !!}
                {!! tableHeader('Endereço') !!}
                {!! tableHeader('Cidade') !!}
                {!! tableHeader('Serviços a realizar') !!}
                {!! tableHeader('Serviços realizados') !!}
                {!! tableHeader('Serviços pagos') !!}
                {!! tableHeader('Observação') !!}
            </tr>
            </thead>

            <tbody>
                @foreach ($technicians as $item2)
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
                                                   data-url="{{ route("admin.technician.edit", ['id' => $item2->id]) }}">
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
                        <td> {{$item2->name}}</td>
                        <td>{{$item2->contact}} </td>
                        <td>
                            {{ $item2->address }} 
                        </td>
                        <td>
                            {{ $item2->city }} 
                        </td>
                        <td>
                            {{ $item2->services_to_performace }}
                        </td>
                        <td>
                            {{ $item2->services_performed }}
                        </td>
                        <td>
                            {{ $item2->paid_services }}
                        </td>
                        <td>
                            {{ $item2->obs }}
                        </td>
                    </tr>
                    <tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="nav-pagination">
        {!! $technicians->render() !!}
    </div>
    

</div>

@stop

@section("javascript")
<script>
   
</script>
@stop