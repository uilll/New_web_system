@extends('Frontend.Layouts.default')

@section('content')
    <div class="panel panel-default" id="table_maintenance">

        <input type="hidden" name="sorting[sort_by]" value="{{ $sorting['sort_by'] or '' }}" data-filter>
        <input type="hidden" name="sorting[sort]" value="{{ $sorting['sort'] or '' }}" data-filter>

        <div class="panel-body" data-table>
            @include('Frontend.Maintenance.table')
        </div>
    </div>
@stop

@section('scripts')
    <script>
    tables.set_config('table_maintenance', {
        url:'{{ route("maintenance.table") }}',
    });
    </script>

    @include('Frontend.Layouts.partials.app')
@stop
