@extends('Frontend.Layouts.modal_drivers')
@section('title')
    <i class="icon alert"></i> Motoristas
@stop

@section('body') 
    <div class="table-responsive user_drivers" style="height: 50vh">
        <table class="table table-list"  data-toggle="multiCheckbox">
            <div class="panel-form">
                <div class="form-group search" style="float: left">
                    {!! Form::text('search_phrase', null, ['class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                </div>
                <div class="action-block" style="float: right">
                    <a href="javascript:" class="btn btn-action" data-url="{!!route('user_drivers.create')!!}" data-modal="user_drivers_create" type="button">
                        <i class="icon add"></i> {{ trans('front.add_driver') }}
                    </a>
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

    
    <div class="nav-pagination" target="#drivers" style="text-align: right;">
        <a href="javascript:" class="btn" data-url="https://sistema.carseg.com.br/user_drivers?page={!!1!!}" data-modal="drivers">
                    <<
                    </a>
        @for ($i = 1; $i <= $drivers->lastpage(); $i++)
            <a href="javascript:" class="btn" data-url="https://sistema.carseg.com.br/user_drivers?page={!!$i!!}" data-modal="drivers">
                    {!! $i !!}
                    </a>
        @endfor         
        <a href="javascript:" class="btn" data-url="https://sistema.carseg.com.br/user_drivers?page={!!$drivers->lastpage()!!}" data-modal="drivers">
                    >>
                    </a>        
    </div>
@stop