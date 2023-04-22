@extends('Admin.Layouts.default')

@section('content')
    <style>
        textarea.error {border-color: #a94442 !important;}
    </style>

    <div class="panel panel-default" id="table_translations">

        <div class="alert alert-success" style="position: fixed; top: 10px; width: 70%; z-index: 9996; display: none;">{{ trans('front.successfully_saved') }}</div>

        <div class="panel-heading">
            <div class="panel-title"><img src="{{ asset_flag($lang) }}" alt="{{ $lang }}"> {{ $language['title'] }}</div>

            <div class="panel-form">
                <div class="form-group">
                    {!! Form::select('tfile', $files, null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>

        <div class="panel-body">
            {!! Form::hidden('lang', $lang) !!}

            <table class="table table-striped" id="table-trans" style="table-layout: fixed;">
                <thead>
                <th>English</th>
                <th>{{ trans('front.original') }}</th>
                <th>{{ trans('front.current') }}</th>
                </thead>
                <tbody id="trans-ajax" class="form"></tbody>
            </table>
        </div>
    </div>

    <div class="row" style="position: fixed; bottom: 0; width: 100%; padding: 10px; background-color: #f9f9f9;">
        <div class="pull-right">
            <button type="button" class="btn btn-action" id="btn-save-trans">
                <i class="fa fa-check"></i> {{ trans('global.save') }}
            </button>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $(document).on('keyup', 'textarea[name^="trans"]', $.debounce(400, function () {
            var el = $(this);
            var parent = el.closest('td');
            var key = el.data('key');
            var lang = $('input[name="lang"]').val();
            var val = el.val();
            $.ajax({
                type: 'GET',
                url: '{{ route('admin.translations.check_trans') }}',
                data: {
                    key: key,
                    lang: lang,
                    val: val,
                    file: $('input[name="trans_file"]').val()
                },
                success: function (res) {
                    el.removeClass('error');
                    parent.find('.error').remove();
                    if (res.status == 0) {
                        parent.append('<span class="help-block error">' + res.error + '</span>');
                        el.addClass('error');
                    }
                }
            });
        }));

        $(document).on('click', '#btn-save-trans:enabled', function (e) {
            e.preventDefault();

            var el = $(this);
            var lang = $('input[name="lang"]').val();
            var file = $('input[name="trans_file"]').val();

            var data = $('textarea[name^="trans"]').serializeArray();
            if ($('textarea[name^="trans"].error').length) {
                $('html,body').animate({
                            scrollTop: $('textarea[name^="trans"].error').first().offset().top},
                        'fast');
                return false;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('admin.translations.save') }}?file=' + file + '&lang=' + lang,
                data: data,
                beforeSend: function () {
                    el.attr('disabled', 'disabled');
                },
                success: function (res) {
                    if (res.status == 0) {
                        $('.help-block.error').remove();
                        $('textarea[name^="trans"]').removeClass('error');
                        var ele = $('textarea[data-key="'+ res.error.key +'"]');
                        var parent = ele.closest('td');
                        parent.append('<span class="help-block error">' + res.error.message + '</span>');
                        ele.addClass('error');
                        $('html,body').animate({
                                    scrollTop: ele.offset().top},
                                'fast');
                    }
                    else {
                        $('.alert-success').fadeIn('fast');
                        setTimeout(function () {
                            $('.alert-success').fadeOut('slow');
                        }, 1500)
                    }
                },
                complete: function () {
                    el.removeAttr('disabled');
                }
            });
        });

        $(document).on('change', 'select[name="tfile"]', function () {
            var el = $(this);
            var lang = $('input[name="lang"]').val();
            var file = el.val();
            $.ajax({
                type: 'GET',
                url: '{{ route('admin.translations.file_trans') }}',
                data: {
                    lang: lang,
                    file: file
                },
                beforeSend: function () {
                    el.attr('disabled', 'disabled');
                },
                success: function (res) {
                    $('#trans-ajax').html(res);
                },
                complete: function () {
                    el.removeAttr('disabled');
                    el.selectpicker('refresh');
                }
            });
        });

        $('select[name="tfile"]').trigger('change');

    </script>
@stop