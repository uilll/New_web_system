@extends('Frontend.Layouts.modal')

@section('modal_class', 'modal-md')

@section('title')
    <i class="icon reports"></i> {!!trans('front.reports')!!}
@stop



@section('body')
    <div id="tab_reports" style="overflow-x:auto; white-space: nowrap; width:100%">
		<ul class="nav nav-tabs nav-default" role="tablist" style="width: max-content">
			@if ( Auth::User()->perm('reports', 'edit') )
				<li class="active" style="display: -webkit-inline-box"><a href="#reports-form-reports" class="reports-form-reports-link" role="tab" data-toggle="tab">{!!trans('front.main')!!}</a></li>
			@endif
			<li style="display: -webkit-inline-box" @if ( !Auth::User()->perm('reports', 'edit') ) class="active" @endif><a href="#reports-form-generated-reports" class="reports-form-generated-reports-tab-link" role="tab" data-toggle="tab" style="width: auto; padding-left: 10px; padding-right: 10px;">{!!trans('front.generated_reports')!!}</a></li>
			<li style="display: -webkit-inline-box"><a href="#reports-form-report-logs" class="reports-form-report-logs-tab-link" role="tab" data-toggle="tab">{{ trans('front.report_logs') }}</a></li>
		</ul>
	</div>
    <div id="reports-modal">
    {!!Form::open(['route' => 'reports.store', 'method' => 'POST', 'id' => 'report_form'])!!}
    {!!Form::hidden('id')!!}
    {!!Form::hidden('_method', 'POST')!!}

        <div class="tab-content" id="reports_form_inputs">
            @if ( Auth::User()->perm('reports', 'edit') )

            <div id="reports-form-reports" class="tab-pane active">
                <div class="row">
					<div class="col-sm-6">
                        <div class="form-group">
                            <label for="to" class="col-sm-3 control-label">{!!trans('front.plate')!!}</label>
                            <div class="form-group">
                                {!!Form::select('devices[]', $devices, null, ['id' => 'devices_list','class' => 'form-control', 'multiple' => 'multiple', 'data-icon' => 'icon devices', 'data-live-search' => true, 'data-actions-box' => true])!!}                                
                            </div>
                        </div>
							<div class="form-group">
								{!!Form::label('type', trans('validation.attributes.type'))!!}
								{!!Form::select('type', $types_list, null, ['class' => 'form-control', 'id' => 'reports_type'])!!}
							</div>
							<div class="form-group">
								{!!Form::label('format', trans('validation.attributes.format'))!!}
								{!!Form::select('format', $formats, null, ['class' => 'form-control', 'id' => 'reports_format'])!!}
							</div>
                    </div>
                </div>

                <div class="row form-horizontal">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="type" class="col-sm-3 control-label">{{ trans('validation.attributes.period') }}</label>
                            <div class="col-sm-9">
                                {!! Form::select('filter', $filters, 1, ['class' => 'form-control', 'id' => 'reports_period', 'data-icon' => 'icon time']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="from" class="col-sm-3 control-label">{{ trans('validation.attributes.date_from') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="has-feedback">
                                        <i class="icon calendar form-control-feedback"></i>
                                        <input name="date_from" type="text" class="datepicker form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <span class="input-group-btn">
                                        {!!Form::select('from_time', Config::get('tobuli.history_time'), '00:00', ['class' => 'form-control timeselect'])!!}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="to" class="col-sm-3 control-label">{{ trans('validation.attributes.date_to') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="has-feedback">
                                        <i class="icon calendar form-control-feedback"></i>
                                        <input class="datepicker form-control" name="date_to" type="text" value="{{ date('Y-m-d', strtotime(date('Y-m-d').' +1 day')) }}">
                                    </div>
                                    <span class="input-group-btn">
                                        {!!Form::select('to_time', Config::get('tobuli.history_time'), '00:00', ['class' => 'form-control timeselect'])!!}
                                    </span>
                                </div>
                            </div>
                        </div>
						<div class="form-group">
                            <label for="to" class="col-sm-3 control-label">{!!trans('front.geofences')!!}</label>
                            <div class="col-sm-9">
                                {!!Form::select('geofences[]', $geofences->pluck('name', 'id')->all(), null, ['class' => 'form-control', 'multiple' => 'multiple', 'data-icon' => 'icon geofences', 'data-live-search' => true, 'data-actions-box' => true])!!}
                            </div>
                        </div>
						<div class="form-group">
							<label  class="col-sm-3">{{ trans('validation.attributes.speed_limit') }}</label>
							<div class="col-sm-3">								
								<input name="speed_limit" class="form-control" type="text" placeholder="60" />
							</div>
                        </div>
                    </div>
					<div class="form-group">
                        
                    </div>
                    
                </div>

                <div class="row" id="report-additional"></div>
				
                <hr class="section-line">
				<div class="checkbox">
					<input type="checkbox" name="details" id="check_more_details" value="0">
					<label>
						Agendamento e mais detalhes...
					</label>
				</div>

                <div class="row" id="more_details">
					
					<div class="col-md-3">
                        <div class="form-group">
                            <label>{{ trans('validation.attributes.stops') }}</label>
                            {!! Form::select('stops', $stops, null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
					<div class="col-sm-12">
                        <div class="form-group">
                            {!!Form::label('title', trans('validation.attributes.title'))!!}
                            {!!Form::text('title', null, ['class' => 'form-control'])!!}
                        </div>
                    </div>					
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('validation.attributes.send_to_email') }}</label>
                            <div class="has-feedback">
                                <i class="icon email form-control-feedback"></i>
                                <input name="send_to_email" class="form-control" type="email" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="checkbox" name="daily" id="daily" value="1">
                                <label for="daily">
                                    {{ trans('validation.attributes.daily') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="checkbox" name="weekly" id="weekly" value="1">
                                <label for="weekly">
                                    {{ trans('validation.attributes.weekly') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="checkbox" name="show_addresses" id="show_addresses" value="1" checked="check">
                                <label for="show_addresses">
                                    {{ trans('validation.attributes.show_addresses') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="checkbox" name="zones_instead" id="zones_instead" value="1">
                                <label for="zones_instead">
                                    {{ trans('validation.attributes.zones_instead') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::text('daily_time', '00:00', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::text('weekly_time', '00:00', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div id="reports-form-generated-reports" class="tab-pane @if ( !Auth::User()->perm('reports', 'edit') ) active @endif
                    ">
                <div data-table>
                    @include('Frontend.Reports.index')
                </div>
            </div>
            <div id="reports-form-report-logs" class="tab-pane">
                <div data-table>
                    @include('Frontend.Reports.logs')
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
    <script>
        if ( typeof _static_reports_create === "undefined") {
            var _static_reports_create = true;

            $(document).on('change', 'select[name="filter"]', function() {
                momentCalendar($(this).val(), '#reports_create')
            });

            $(document).on('click', '#reports_create button.save:visible', function() {
                $('#reports_create form').attr('action', $(this).data('action'));
                $('#reports_create input[name="_method"]').val('POST');
                $('#reports_create button.update_hidden').trigger('click');
            });

            $(document).on('click', '#reports_create button.generate:visible', function() {
                $('#reports_create form').attr('action', $(this).data('action'));
                $('#reports_create input[name="_method"]').val('POST');
                $('#reports_create button.update_hidden').trigger('click');
            });

            $(document).on('click', '#reports_create button.new:visible', function() {
                var parent = $('#reports_create');

                parent.find('input[name="id"]').val('')
                parent.find('input[name="title"]').val('');
                parent.find('select[name="type"]').val(1).trigger('change');
                parent.find('select[name="format"]').val('html').trigger('change');
                parent.find('input[name="show_addresses"]').prop('checked', 0);
                parent.find('input[name="zones_instead"]').prop('checked', 0);
                parent.find('select[name="stops"]').val(1).trigger('change');
                parent.find('input[name="speed_limit"]').val('');
                parent.find('input[name="daily"]').prop('checked', 0).trigger('change');
                parent.find('input[name="weekly"]').prop('checked', 0).trigger('change');
                parent.find('input[name="send_to_email"]').val('');
                parent.find('select.reports_geofences').val([]);
                parent.find('select.reports_devices').val([]);
                parent.find('input[name="daily_time"]').val('00:00');
                parent.find('input[name="weekly_time"]').val('00:00');

                $('a.reports-form-reports-link').trigger('click');
            });

            $(document).on('click', '#reports_create .report_item_edit', function() {
                var item = jQuery.parseJSON($(this).closest('td').find('.report_item_json').html());

                loadReportForm(item.type, item);

                $('a.reports-form-reports-link').trigger('click');
            });

            $(document).on('change', '#reports_type', function() {
                loadReportForm($(this).val());
            });
        }

        $(document).ready(function() {
            $('select[name="filter"]:first, #reports_type').trigger('change');

            // Função para verificar a seleção dos campos "format" e "type"
                function checkReportOptions() {
                    var format = $('#reports_format').val();
                    var type = $('#reports_type').val();

                    if (format === 'pdf' && type === 'historico_posicoes') {
                        var fromDate = new Date($('#date_from').val());
                        var toDate = new Date($('#date_to').val());

                        // Calcula a diferença em dias entre as datas
                        var diffInDays = Math.floor((toDate - fromDate) / (1000 * 60 * 60 * 24));

                        if (diffInDays > 7) {
                            // Exibe a mensagem de limite de período
                            $('#limit-message').text('O período máximo permitido é de 7 dias.');
                            $('#limit-message').show();

                            // Bloqueia o envio do formulário
                            $('#reports-form').submit(function(e) {
                                e.preventDefault();
                            });
                        } else {
                            // Remove a mensagem e permite o envio do formulário
                            $('#limit-message').hide();
                            $('#reports-form').unbind('submit');
                        }
                    } else {
                        // Remove a mensagem e permite o envio do formulário
                        $('#limit-message').hide();
                        $('#reports-form').unbind('submit');
                    }
                }

                // Verifica a seleção dos campos sempre que houver alteração
                $('#format, #type, #date_from, #date_to').change(function() {
                    checkReportOptions();
                });

                // Verifica a seleção dos campos quando a página for carregada
                checkReportOptions();


        });

        tables.set_config('reports-form-report-logs', {
            url:'{{ route("reports.logs") }}',
            delete_url:'{{ route("reports.log_destroy") }}'
        });

        tables.set_config('reports-form-generated-reports', {
            url:'{{ route('reports.index') }}'
        });

        function reports_create_modal_callback(res) {
            if (res.status == 3) {
                if (typeof res.url != 'undefined') {
                     window.open(res.url, '_self');
                    /*var form = $('<form method="POST" action="' + res.url + '">');
                    $('body').append(form);
                    form.submit();*/
                }
            }
            if (res.status == 2) {
                tables.get('reports-form-generated-reports');
                $('a.reports-form-generated-reports-tab-link').trigger('click');
            }
        }

        function reports_destroy_modal_callback() {
            tables.get('reports-form-generated-reports');
        }

        $('input[name="daily"]').on('change', timeCon);
        $('input[name="weekly"]').on('change', timeCon);

        function loadReportForm(type, data)
        {
            $.ajax({
                //async: false,
                type: 'GET',
                dataType: 'json',
                url: '{{ route('reports.types.show') }}/'+type,
                beforeSend: function() {
                    loader.add( $('#reports-modal') );
                },
                success: function (response) {
                    buildReportForm(response);

                    if (data)
                        setReportData(data);
                },
                complete: function() {
                    loader.remove( $('#reports-modal') );
                }
            });
        }

        function buildReportForm(type)
        {
            var $form = $('#reports_create');
            var $format = $('#format', $form);
            var $additional = $('#report-additional', $form);

            $('option', $format).attr('disabled','disabled');
            $.each(type.formats, function( index, value ) {
                $("option[value='"+value+"']", $format).removeAttr('disabled');
            });
            $format.selectpicker('refresh');

            var fields = ['geofences', 'speed_limit', 'stops', 'show_addresses', 'zones_instead'];
            $.each(fields, function( index, value ) {
                if (value == 'geofences')
                    value += '[]';

                var $field = $("[name='"+value+"']", $form);

                $field.attr('disabled','disabled');

                if ($field.is('select'))
                    $field.selectpicker('refresh');
            });

            $.each(type.fields, function( index, value ) {
                if (value == 'geofences')
                    value += '[]';

                var $field = $("[name='"+value+"']", $form);

                $field.removeAttr('disabled');

                if ($field.is('select'))
                    $field.selectpicker('refresh');
            });

            buildReportAttributes(type.parameters, $additional);
        }

        function buildReportAttributes(parameters, $parametersContainer)
        {
            $parametersContainer = $( $parametersContainer );
            $parametersContainer.html('');

            if ( ! parameters)
                return;

            $.each(parameters, function( index, parameter )
            {
                var $formGroup = $('<div class="form-group"></div>');
                var value = parameter.default ? parameter.default : '';

                $formGroup.append( '<label for="'+parameter.name+'">'+parameter.title+':</label>' );

                switch (parameter.type) {
                    case 'integer':
                    case 'string':
                        $formGroup.append( '<input type="text" class="form-control" name="'+parameter.name+'" value="'+value+'" />' );
                        break;
                    case 'text':
                        $formGroup.append( '<textarea class="form-control" name="'+parameter.name+'">'+value+'</textarea>' );
                        break;
                    case 'select':
                        var $select = $('<select class="form-control" name="'+parameter.name+'"></select> ');
                        $.each(parameter.options, function( index, option ){
                            $select.append('<option value="'+option.id+'">'+option.title+'</option>');
                        });
                        $formGroup.append( $select );
                        $select
                                .val(value)
                                .selectpicker();
                        break;
                }

                if (parameter.description) {
                    $formGroup.append('<small>'+parameter.description+'</small>');
                }

                var $container = $('<div class="col-sm-12"></div>');

                if (parameters.length > 1 && parameter.type != 'text' ) {
                    $container = $('<div class="col-sm-6"></div>');
                }

                $container.append($formGroup);

                $parametersContainer.append($container);
            });
        }

        function setReportData(item) {
            var parent = $('#reports_create');

            parent.find('input[name="id"]').val(item.id)
            parent.find('input[name="title"]').val(item.title);
            parent.find('select[name="type"]').val(item.type);
            parent.find('select[name="format"]').val(item.format).trigger('change');
            parent.find('input[name="show_addresses"]').prop('checked', item.show_addresses);
            parent.find('input[name="zones_instead"]').prop('checked', item.zones_instead);
            parent.find('select[name="stops"]').val(item.stops).trigger('change');
            parent.find('input[name="speed_limit"]').val(item.speed_limit);
            parent.find('input[name="daily"]').prop('checked', item.daily).trigger('change');
            parent.find('input[name="weekly"]').prop('checked', item.weekly).trigger('change');
            parent.find('input[name="send_to_email"]').val(item.email);
            parent.find('input[name="daily_time"]').val(item.daily_time);
            parent.find('input[name="weekly_time"]').val(item.weekly_time);

            var from_time = moment(item.from_formated).format('HH:mm');
            parent.find('select[name="from_time"]').val(from_time).trigger('change');

            var to_time = moment(item.to_formated).format('HH:mm');
            parent.find('select[name="to_time"]').val(to_time).trigger('change');

            var geofences = [];
            $.each(item.geofences, function( index, value ) {
                geofences.push( value.id );
            });
            $('#report_form select[name="geofences[]"]').val( geofences ).trigger('change').selectpicker('refresh');
            dd( 'geofences', geofences );

            var devices = [];
            $.each(item.devices, function( index, value ) {
                devices.push( value.id );
            });
            $('#report_form select[name="devices[]"]').val( devices ).trigger('change').selectpicker('refresh');
            dd( 'devices', devices );

            if (item.parameters) {
                $.each(item.parameters, function( index, value ) {
                    var $field = parent.find('[name="'+index+'"]');

                    $field.val(value);

                    if ($field.is('select'))
                        $field.selectpicker('refresh');
                });
            }

        }

        function timeCon() {
            var daily = $('input[name="daily"]').prop("checked");
            if (daily) {
                $('input[name="daily_time"]').removeAttr('disabled');
            }
            else {
                $('input[name="daily_time"]').attr('disabled', 'disabled');
            }

            var weekly = $('input[name="weekly"]').prop("checked");
            if (weekly) {
                $('input[name="weekly_time"]').removeAttr('disabled');
            }
            else {
                $('input[name="weekly_time"]').attr('disabled', 'disabled');
            }
        }

        timeCon();
    </script>
@stop

@section('buttons')
    <button type="button" class="update_hidden" style="display: none;"></button>
    @if ( Auth::User()->perm('reports', 'edit') )
        <button type="button" class="btn btn-action generate" data-action="{!!route('reports.update')!!}" download>{!!trans('front.generate')!!}</button>
        <button type="button" class="btn btn-default save" data-action="{!!route('reports.store')!!}">{!!trans('global.save')!!}</button>
        <button type="button" class="btn btn-default new">{!!trans('front.new')!!}</button>
    @endif
    <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('global.cancel')!!}</button>
@stop