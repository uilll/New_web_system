@if (!empty($events))
    @foreach ($events as $item)
        <tr data-event-id="{!!$item->id!!}" class="event_item" onClick="app.events.select({!!$item->id!!});">             
            <td class="datetime">
                <span class="time">{!! \Carbon\Carbon::parse($item->time)->format('H:i:s') !!}</span>
                <span class="date">{!! \Carbon\Carbon::parse($item->time)->format('Y-m-d') !!}</span>
            </td>
            <td>{{ (isset($item->device_name) ? $item->device_name." (".$item->device->plate_number.")" : '') }}</td>
            <td>{!! $item->name !!}@if (!empty($item->detail)) ({{$item->detail}}) @endif</td>
            <td>
                <div class="btn-group dropleft droparrow"  data-position="fixed">
                    <i class="btn icon options" data-toggle="dropdown" data-position="fixed" aria-haspopup="true" aria-expanded="false"></i>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:;" data-url="{{ route('alerts.edit', $item->alert_id) }}" data-modal="alerts_edit">
                                <span class="icon alert"></span>
                                <span class="text">{{ trans('global.alert') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
            <?php
                //var_dump ($item);
                $arr = $item->toArray();
                unset($arr['geofence'], $arr['device']);
                if (isset($item->device_name) ?  : '')
                    $arr['device']['name'] = $item->device->name;
                if (isset($item->geofence->name) ?  : '')
                    $arr['geofence']['name'] = $item->geofence->name;
            ?>
            <script>app.events.add({!! json_encode($arr) !!});</script>
        </tr>
    @endforeach
    <div style="display: none;">
        @if (method_exists($events, 'render'))
            {!! $events->render() !!}
        @endif
    </div>
@else
    <tr>
        <td class="no-data">{!!trans('front.no_events')!!}</td>
    </tr>
@endif
