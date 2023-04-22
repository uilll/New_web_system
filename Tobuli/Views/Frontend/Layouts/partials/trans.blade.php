<script>
    window.lang = {
        lang: '{{ config('tobuli.languages.'.Auth::User()->lang.'.iso') }}',
        select_all: '{{ trans('front.select_all') }}',
        deselect_all: '{{ trans('front.deselect_all') }}',
        close: '{{ trans('front.close') }}',
        device: '{{ trans('global.device') }}',
        address: '{{ trans('front.address') }}',
        position: '{{ trans('front.position') }}',
        altitude: '{{ trans('front.altitude') }}',
        speed: '{{ trans('front.speed') }}',
        angle: '{{ trans('front.angle') }}',
        time: '{{ trans('front.time') }}',
        h: '{{ trans('front.h') }}',
        m: '{{ trans('front.'.Auth::User()->unit_of_altitude) }}',
        model: '{{ trans('front.model') }}',
        plate: '{{ trans('front.plate') }}',
        protocol: '{{ trans('front.protocol') }}',
        alerts_maximum_date_range: '{{ trans('front.alerts_maximum_date_range') }}',
        successfully_created_alert: '{{ trans('front.successfully_created_alert') }}',
        successfully_updated_alert: '{{ trans('front.successfully_updated_alert') }}',
        geofence: '{{ trans('front.geofence') }}',
        event: '{{ trans('front.event') }}',
        successfully_created_geofence: '{{ trans('front.successfully_created_geofence') }}',
        successfully_updated_geofence: '{{ trans('front.successfully_updated_geofence') }}',
        came: '{{ trans('front.came') }}',
        left: '{{ trans('front.left') }}',
        duration: '{{ trans('front.duration') }}',
        route_length: '{{ trans('front.route_length') }}',
        move_duration: '{{ trans('front.move_duration') }}',
        stop_duration: '{{ trans('front.stop_duration') }}',
        top_speed: '{{ trans('front.top_speed') }}',
        fuel_cons: '{{ trans('front.fuel_cons') }}',
        parameters: '{{ trans('front.tags') }}',
        driver: '{{ trans('front.driver') }}',
        street_view: '{{ trans('front.street_view') }}',
        preview: '{{ trans('front.preview') }}',
        route_start: '{{ trans('front.route_start') }}',
        route_end: '{{ trans('front.route_end') }}',
        sensors: '{{ trans('front.sensors') }}',
        successfully_created_route: '{{ trans('front.successfully_created_route') }}',
        successfully_updated_route: '{{ trans('front.successfully_updated_route') }}',
        gps: '{{ trans('front.gps') }}',
        lat: '{{ trans('front.latitude') }}',
        lng: '{{ trans('front.longitude') }}',
        all_parameters: '{{ trans('front.show_more') }}',
        hide_parameters: '{{ trans('front.show_less') }}',
        nothing_selected: '{{ trans('front.nothing_selected') }}',
        color: '{{ trans('validation.attributes.color') }}',
        from: '{{ trans('front.from') }}',
        to: '{{ trans('front.to') }}',
        add: '{{ trans('global.add') }}',
        follow: '{{ trans('front.follow') }}',
        on: '{{ trans('front.on') }} ',
        off: '{{ trans('front.off') }}',
        streetview: '{{ trans('front.streetview') }}',
        successfully_created_marker: '{{ trans('front.successfully_created_marker') }}',
        successfully_updated_marker: '{{ trans('front.successfully_updated_marker') }}',
        status_offline: '{{ trans('global.offline') }}',
        status_online: '{{ trans('global.online') }}',
        status_ack: '{{ trans('global.ack') }}',
        status_engine: '{{ trans('global.engine') }}',
        alert: '{{ trans('global.alert') }}'
    };
</script>