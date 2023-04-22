<div class="table_error"></div>
<div class="table-responsive">
    <table class="table table-list" data-toggle="multiCheckbox">
        <thead>
        <tr>
            @if( Auth::User()->perm('devices', 'remove') )
                {!! tableHeaderCheckall(['do_destroy' => trans('admin.delete_selected')]) !!}
            @endif
            {!! tableHeader('validation.attributes.active') !!}
            {!! tableHeaderSort($items->sorting, 'devices.name', 'validation.attributes.name') !!}
            {!! tableHeaderSort($items->sorting, 'devices.object_owner', 'validation.attributes.object_owner') !!}
            {!! tableHeaderSort($items->sorting, 'devices.plate_number', 'validation.attributes.plate_number') !!}
            {!! tableHeaderSort($items->sorting, 'devices.imei', 'validation.attributes.imei') !!}
            {!! tableHeader('global.online', 'style="text-align:center;"') !!}
            {!! tableHeaderSort($items->sorting, 'traccar.server_time', 'admin.last_connection') !!}
            {!! tableHeaderSort($items->sorting, 'expiration_date', 'validation.attributes.expiration_date') !!}
            {!! tableHeaderSort($items->sorting, 'excess_data', 'Excesso') !!}
            {!! tableHeader('validation.attributes.user') !!}
            {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
        </tr>
        </thead>

        <tbody>
        @if (count($collection = $items->getCollection()))
            @foreach ($collection as $item)
                <tr>
                    @if( Auth::User()->perm('devices', 'remove') )
                        <td>
                            <div class="checkbox">
                                <input type="checkbox" value="{!! $item->id !!}">
                                <label></label>
                            </div>
                        </td>
                    @endif
                    <td>
                        <span class="label label-sm label-{!! $item->active ? 'success' : 'danger' !!}">
                            {!! trans('validation.attributes.active') !!}
                        </span>
                    </td>
                    <td>
                        {{ $item->name }}
                    </td>
                    <td>
                        {{ $item->object_owner }} 
                    </td>
                    <td>
                        {{ $item->plate_number }} 
                    </td>
                    <td>
                        {{ $item->imei }}
                    </td>
                    <td style="text-align: center">
                        <span
                                class="device-status"
                                style="background-color: {{ $item->getStatusColor() }}"
                                data-toggle="tooltip"
                                title="{{ trans("global.{$item->getStatus()}") }}">
                        </span>
                    </td>
                    <td>
                        {{ $item->server_time ? datetime($item->server_time) : trans('front.not_connected') }}
                    </td>
                    <td>
                        {{ $item->expiration_date == '0000-00-00' ? trans('front.unlimited') : datetime($item->expiration_date) }}
                    </td>
                    <td>
                        {{ $item->excess_data ? "Excesso" : "Não" }}
                    </td>
                    <td class="user-list">
                        {{ $item->users->filter(function($value){ return auth()->user()->can('show', $value); })->implode('email', ', ') }}
                    </td>
                    <td class="actions">
                        @if (Auth::User()->perm('devices', 'edit') || Auth::User()->perm('devices', 'remove'))
                            <div class="btn-group dropdown droparrow dropdown-toggle" data-position="fixed">
                                <i class="btn icon edit" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="true"></i>
                                <ul class="dropdown-menu">
                                    @if( Auth::User()->perm('devices', 'edit') )
                                        <li>
                                            <a href="javascript:" data-modal="devices_edit"
                                               data-url="{{ route('devices.edit', [$item->id, 1]) }}">
                                                {{ trans('global.edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:" data-modal="devices_transfer"
                                               data-url="{{ route('devices.transfer', ['id' => $item->id]) }}">
                                                {{ "Transeferir dados" }}
                                            </a>
                                        </li>
                                    @endif
                                    @if( Auth::User()->perm('devices', 'remove') )
                                        <li>
                                            <a href="javascript:" data-modal="devices_delete"
                                               data-url="{{ route('devices.do_destroy', ['id' => $item->id]) }}">
                                                {{ trans('global.delete') }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        @else
            <tr class="">
                <td class="no-data" colspan="7">
                    {!! trans('admin.no_data') !!}
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

@include("Admin.Layouts.partials.pagination")