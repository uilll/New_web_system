<div class="table-responsive">
    <style>
        .progress { margin-bottom: 0; min-width: 75px;}
    </style>
    <table class="table table-list">
        <thead>
        <tr>
            {!! tableHeaderSort($sorting, 'device.plate_number', trans('validation.attributes.plate_number')) !!}
            {!! tableHeaderSort($sorting, 'name', trans('validation.attributes.name')) !!}
            {!! tableHeaderSort($sorting, 'odometer_percentage', trans('global.distance')) !!}
            {!! tableHeaderSort($sorting, 'odometer_left', trans('global.distance') . ' ' . trans('front.left')) !!}
            {!! tableHeaderSort($sorting, 'engine_hours_percentage', trans('validation.attributes.engine_hours')) !!}
            {!! tableHeaderSort($sorting, 'engine_hours_left', trans('validation.attributes.engine_hours') . ' ' . trans('front.left')) !!}
            {!! tableHeaderSort($sorting, 'days_percentage', trans('global.days')) !!}
            {!! tableHeaderSort($sorting, 'days_left', trans('global.days') . ' ' . trans('front.left')) !!}
            <th></th>
        </tr>
        </thead>
        <tbody>
        @if (count($services))
                @foreach ($services as $service)
                    <?php
                    $bar_class = $service->percentage < 20 ? 'progress-bar-danger' : ( $service->percentage < 50 ? 'progress-bar-warning' : 'progress-bar-success');
                    ?>
                    <tr>
                        <td>{{ $service->device->plate_number }}</td>
                        <td>{{ $service->name }}</td>
                        @if($service->expiration_by == 'odometer')
                            <td>
                                <div class="progress">
                                    <div
                                            class="progress-bar progress-bar-striped {{ $bar_class }}"
                                            role="progressbar"
                                            style="width: {{ $service->percentage }}%"
                                            aria-valuenow="{{ $service->percentage }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        {{ $service->percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $service->left_formated() }}</td>
                        @else
                            <td>-</td>
                            <td>-</td>
                        @endif
                        @if($service->expiration_by == 'engine_hours')
                            <td>
                                <div class="progress">
                                    <div
                                            class="progress-bar progress-bar-striped {{ $bar_class }}"
                                            role="progressbar" style="width: {{ $service->percentage }}%"
                                            aria-valuenow="{{ $service->percentage }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        {{ $service->percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $service->left_formated() }}</td>
                        @else
                            <td>-</td>
                            <td>-</td>
                        @endif
                        @if($service->expiration_by == 'days')
                            <td>
                                <div class="progress">
                                    <div
                                            class="progress-bar progress-bar-striped {{ $bar_class }}"
                                            role="progressbar"
                                            style="width: {{ $service->percentage }}%"
                                            aria-valuenow="{{ $service->percentage }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        {{ $service->percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $service->left_formated() }}</td>
                        @else
                            <td>-</td>
                            <td>-</td>
                        @endif
                        <td class="actions">
                            <a href="javascript:" class="btn icon edit" data-url="{!!route('services.edit', $service->id)!!}" data-modal="services_edit"></a>
                            <a href="javascript:" class="btn icon delete" data-url="{!!route('services.do_destroy', $service->id)!!}" data-modal="services_destroy"></a>
                        </td>
                    </tr>
                @endforeach
        @else
            <tr>
                <td colspan="8">{!!trans('front.no_services')!!}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>