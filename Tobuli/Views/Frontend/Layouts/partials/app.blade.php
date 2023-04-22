<script type="text/javascript">
    app.debug = false;
    app.user_id = '{{ auth()->user()->id }}';
    app.version = '{{ config('tobuli.version') }}';
    app.offlineTimeout = {{ settings('main_settings.default_object_online_timeout') * 60 }};
    app.checkFrequency = {{ config('tobuli.check_frequency') }};

    app.show_object_info_after = {{ settings('plugins.show_object_info_after.status') }};
    app.object_listview = {{ settings('plugins.object_listview.status') }};

    app.urls = {
        asset:                  '{{ asset('') }}',
        check:                  '{{ route('objects.items_json') }}',
        streetView:             '{{ route('streetview') }}',
        geoAddress:             '{{ route('api.geo_address') }}',

        events:                 '{{ route('events.index') }}',

        history:                '{{ route('history.index') }}',
        historyExport:          '{{ route('history.export') }}',
        historyPosition:        '{{ route('history.position') }}',
        historyPositions:       '{{ route('history.positions') }}',
        historyPositionsDelete: '{{ route('history.delete_positions') }}',

        devices:                '{{ route('objects.items', ['pagina' => session()->get('page')]) }}',
        deviceDelete:           '{{ route('objects.destroy') }}',
        deviceChangeActive:     '{{ route('devices.change_active') }}',
        deviceToggleGroup:      '{{ route('objects.change_group_status') }}',
        deviceStopTime:         '{{ route('objects.stop_time') }}/',
        deviceFollow:           '{{ route('devices.follow_map') }}/',
        devicesSensorCreate:    '{{ route('sensors.create') }}/',
        devicesServiceCreate:   '{{ route('services.create') }}/',
        devicesServices:        '{{ route('services.index') }}/',
        devicesCommands:        '{{ route('devices.commands') }}',
        deviceImages:           '{{ route('device_media.get_images') }}/',
        deviceImage:            '{{ route('device_media.get_image') }}/',
        deleteImage:            '{{ route('device_media.delete_image') }}/',
        deviceWidgetLocation:   '{{ route('device.widgets.location') }}/',

        geofences:              '{{ route('geofences.index') }}',
        geofenceChangeActive:   '{{ route('geofences.change_active') }}',
        geofenceDelete:         '{{ route('geofences.destroy') }}',
        geofencesExportType:    '{{ route('geofences.export_type') }}',
        geofencesImport:        '{{ route('geofences.import') }}',
        geofenceToggleGroup:    '{{ route('geofences_groups.change_status') }}',

        routes:                 '{{ route('routes.index') }}',
        routeChangeActive:      '{{ route('routes.change_active') }}',
        routeDelete:            '{{ route('routes.destroy') }}',


        alerts:                 '{{ route('alerts.index') }}',
        alertEdit:              '{{ route('alerts.edit') }}',
        alertChangeActive:      '{{ route('alerts.change_active') }}',
        alertDelete:            '{{ route('alerts.destroy') }}',
        alertGetEvents:         '{{ route('custom_events.get_events') }}',
        alertGetProtocols:      '{{ route('custom_events.get_protocols') }}',
        alertGetEventsByDevice: '{{ route('custom_events.get_events_by_device') }}',
        alertGetCommands:       '{{ route('alerts.commands') }}',

        mapIcons:               '{{ route('map_icons.index') }}',
        mapIconsDelete:         '{{ route('map_icons.destroy') }}',
        mapIconsChangeActive:   '{{ route('map_icons.change_active') }}',
        mapIconsList:           '{{ route('map_icons.list') }}',

        changeMap:              '{{ route('my_account.change_map') }}',
        changeMapSettings:      '{{ route('my_account_settings.change_map_settings') }}',

        clearQueue:             '{{ route('sms_gateway.clear_queue') }}',

        listView:               '{{ route('objects.listview') }}',
        listViewItems:          '{{ route('objects.listview.items') }}',

        chatMessages:           '{{ route('chat.messages') }}',
    };

    app.channels = {
        chat: '{{ md5('message_for_user_' . auth()->user()->id) }}'
    };

    app.settings.units.speed    = '{{ Auth::User()->unit_of_speed }}';
    app.settings.units.distance = '{{ Auth::User()->unit_of_distance }}';
    app.settings.units.altitude = '{{ Auth::User()->unit_of_altitude }}';
    app.settings.units.capacity = '{{ Auth::User()->unit_of_capacity }}';

    app.settings.weekStart = '{{ Auth::User()->week_start_day }}';

    app.settings.mapCenter = [parseFloat('{{ settings('main_settings.map_center_latitude') }}'), parseFloat('{{ settings('main_settings.map_center_longitude') }}')];
    app.settings.mapZoom = {{ settings('main_settings.map_zoom_level') }};
    app.settings.user_id = '{{ Auth::User()->id }}';
    app.settings.map_id = '{{ Auth::User()->map_id }}';
    app.settings.notifications = '{{ Auth::User()->push_notification }}';
    
    app.settings.availableMaps = {!! json_encode(Auth::User()->available_maps) !!};

    app.settings.toggleSidebar  = false;
    app.settings.showDevice     = {{ Auth::User()->map_controls->m_objects ? 'true' : 'false' }};
    app.settings.showGeofences  = {{ Auth::User()->map_controls->m_geofences ? 'true' : 'false' }};
    app.settings.showRoutes     = {{ Auth::User()->map_controls->m_routes ? 'true' : 'false' }};
    app.settings.showPoi        = {{ Auth::User()->map_controls->m_poi ? 'true' : 'false' }};
    app.settings.showTail       = {{ Auth::User()->map_controls->m_show_tails ? 'true' : 'false' }};
    app.settings.showNames      = {{ Auth::User()->map_controls->m_show_names ? 'true' : 'false' }};
    app.settings.showTraffic    = false;

    app.settings.showHistoryRoute = {{ Auth::User()->map_controls->history_control_route ? 'true' : 'false' }};
    app.settings.showHistoryArrow = {{ Auth::User()->map_controls->history_control_arrows ? 'true' : 'false' }};
    app.settings.showHistoryStop  = {{ Auth::User()->map_controls->history_control_stops ? 'true' : 'false' }};
    app.settings.showHistoryEvent = {{ Auth::User()->map_controls->history_control_events ? 'true' : 'false' }};

    app.settings.keys.google = '{{ config('services.google_maps.key') }}';

    app.settings.keys.here_map_id = '{{ settings('main_settings.here_map_id') }}';
    app.settings.keys.here_map_code = '{{ settings('main_settings.here_map_code') }}';
    app.settings.keys.mapbox_access_token = '{{ settings('main_settings.mapbox_access_token') }}';
    app.settings.keys.bing_maps_key = '{{ settings('main_settings.bing_maps_key') }}';

    app.settings.googleQueryParam = {
        key: '{{ config('services.google_maps.key') }}',
        @if (env('google_styled', false))
        region: 'MA',
        @endif
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ajax-bootstrap-select/1.3.8/js/ajax-bootstrap-select.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
