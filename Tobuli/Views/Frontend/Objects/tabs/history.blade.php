<div class="tab-pane-header" id="history_header" style="width:50%">
    <div id="history-form" class="form-horizontal">
        <div class="form-group">
            <label class="col-xs-3 control-label">{!!trans('global.device')!!}:</label>
            <div class="col-xs-9">
                {!!Form::select('devices', $devices, $history['def_device'], ['class' => 'form-control devices_list', 'data-live-search' => true])!!}
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-3 control-label">{!!trans('global.from')!!}:</label>
            <div class="col-xs-9">
                <div class="input-group">
                    {!!Form::text('from_date', $history['start'], ['class' => 'datepicker form-control'])!!}
                    <span class="input-group-btn">
                        {!!Form::select('from_time', Config::get('tobuli.history_time'), null, ['class' => 'form-control timeselect'])!!}
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-3 control-label">{!!trans('global.to')!!}:</label>
            <div class="col-xs-9">
                <div class="input-group">
                    {!!Form::text('to_date', $history['end'], ['class' => 'datepicker form-control'])!!}
                    <span class="input-group-btn">
                        {!!Form::select('to_time', Config::get('tobuli.history_time'), $history['end_time'], ['class' => 'form-control timeselect'])!!}
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12 text-right">
                <a href="javascript:" class="collapsed" data-toggle="collapse" data-target="#history-advanced" data-parent="#history_tab" aria-expanded="false" aria-controls="history-advanced">
                    {{ trans('global.advanced') }}
                </a>
            </div>
        </div>

        <div id="history-advanced" class="group-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px; position:absolute; width:40%; top: 150px">
            <div class="form-group">
                <label class="col-xs-3 control-label">{{ trans('validation.attributes.stops') }}</label>
                <div class="col-xs-9">
                    {!! Form::select('stops', config('tobuli.stops_minutes'), 1, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label"></label>
                <div class="col-xs-9">
                    <div class="checkbox">
                        <input id="snap_to_road" name="snap_to_road" type="checkbox" value="true">
                        <label>{{ trans('front.snap_to_road') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <div id="input_buttons" class="input-group" style="position: absolute; width: 50%; right: 2px; bottom: 125px;">
			<span class="input-group-btn">
				<div class="btn-group dropdown">
                    <button class="btn btn-default" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon history-export"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:" onclick="app.history.export( 'gsr' )">{{ trans('front.export_gsr') }}</a></li>
                        <li><a href="javascript:" onclick="app.history.export( 'kml' )">{{ trans('front.export_kml') }}</a></li>
                        <li><a href="javascript:" onclick="app.history.export( 'gpx' )">{{ trans('front.export_gpx') }}</a></li>
                        <li><a href="javascript:" onclick="app.history.export( 'csv' )">{{ trans('front.export_csv') }}</a></li>
                    </ul>
                </div>
                <button class="btn btn-default" type="button" onclick="app.history.clear()">
                    <i class="icon history-clean"></i>
                </button>
            </span>
			
            <button class="btn btn-primary btn-block" type="button" onclick="app.history.get()">{!!trans('front.show_history')!!}</button>
            
        </div>
    </div>
</div>
<div id="tab_history" class="tab-pane-body" style="overflow-y: auto; width: 50%; position: absolute; right: 0vw; top: 50px; min-height: 170px;">
    <div id="ajax-history"></div>
</div>