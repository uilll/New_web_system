<div class="table-responsive">
    <table class="table table-list"  data-toggle="multiCheckbox">
        <div class="panel-form">
            <div class="form-group search">
                {!! Form::text('search_phrase', null, ['class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
            </div>
        </div>
        <thead>
        {!! tableHeaderSort($drivers->sorting, 'drivers.name', 'validation.attributes.name') !!}
        {!! tableHeader('validation.attributes.plate_number') !!}
        {!! tableHeader('validation.attributes.rfid') !!}
        {!! tableHeader('validation.attributes.phone') !!}
        {!! tableHeader('validation.attributes.email') !!}
        {!! tableHeader('CNH') !!}
        {!! tableHeader('Data de validade CNH') !!}
        {!! tableHeader('validation.attributes.description') !!}
        <th></th>
        </thead>
        <tbody>
        @if (count($drivers))
            @foreach ($drivers as $driver)
                <tr style="color: {{ $driver->status}}">
                    <td>{{$driver->name}}</td>
                    <td>{{empty($driver->device) ? '' : $driver->device->plate_number}}</td>
                    <td data-editable-field="rfid" data-submit-url="{!! route('user_drivers.do_update',$driver->id) !!}">{{$driver->rfid}}</td>
                    <td>{{$driver->phone}}</td>
                    <td>{{$driver->email}}</td>
                    <td>{{$driver->cnh}}</td>
                    <td>{{$driver->cnh_expire}}</td>
                    <td>{{$driver->description}}</td>
                    <td class="actions">
                        <a href="javascript:" class="btn icon edit" data-url="{!!route('user_drivers.edit', $driver->id)!!}" data-modal="user_drivers_edit"></a>
                        <a href="javascript:" class="btn icon delete" data-url="{!!route('user_drivers.do_destroy', $driver->id)!!}" data-modal="user_drivers_destroy"></a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="8">{!!trans('front.no_drivers')!!}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<div class="nav-pagination">
    {!! $drivers->setPath(route('user_drivers.index'))->render() !!}
</div>