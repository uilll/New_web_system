$.ajaxSetup({ cache: false });

var tables = {
    loader: '',
    config: {},
    items_arr : {},

    getFilters: function(table) {
        var $tableContainer = $("#" + table),
            $filters = $('[data-filter]', $tableContainer),
            filter = {};

        if ($filters.length){

            $filters.each(function( index ) {
                var type = $(this).attr('type');

                switch (type) {
                    case 'checkbox':
                    case 'radio':
                        if (!$(this).is(':checked'))
                            return true;
                        break;
                    default:
                        break;
                }

                filter[$(this).attr('name')] = $(this).val();
            });
        }

        return filter;
    },

    get: function (table, url){
        var config = tables.config[table];
        if (typeof config == 'undefined') {
            alert('"' + table + '" configs is not defined!');
        }

        if (typeof url == 'undefined') {
            url = config.url;
        }

        var $tableContainer = $("#" + table),
            $table = $('[data-table]', $tableContainer),
            filter = {};

        filter = tables.getFilters(table);

        $.ajax({
            type: "GET",
            dataType: "html",
            url: url,
            cache: false,
            data: filter,
            beforeSend: function() {
                loader.add($tableContainer);
            },
            success: function(res) {

                $table.html(res);

                tables.check_checkboxes(table);

                initComponents( $table );
            },
            complete: function() {
                loader.remove($tableContainer);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                dd('table.get.fail', jqXHR);
                handlerFailTarget(jqXHR, textStatus, errorThrown, $table);
            }
        });
    },

    set_config: function(table, configs) {
        dd( 'tables.set_config' );

        if (typeof tables.config[table] == 'undefined') {
            tables.config[table] = configs;
        }
        else {
            for (var key in configs) {
                if (configs.hasOwnProperty(key))
                    tables.config[table][key] = configs[key]
            }
        }

        tables.events(table);
    },
    check_checkboxes : function(table) {
        if (typeof tables.items_arr[table] == 'undefined') {
            tables.items_arr[table] = {};
        }
        $("#" + table + " input:checkbox").not(":disabled").each(function() {
            var id = $(this).val();
            if (id && id != 'on') {
                if (id in tables.items_arr[table]) {
                    if (!$(this).is(":checked")) {
                        $(this).prop("checked", true);
                    }
                }
                else {
                    if ($(this).is(":checked")) {
                        $(this).prop("checked", false);
                    }
                }
            }
        });
    },
    events: function(table) {
        dd( 'tables.events' );
        var $tableContainer = $("#" + table),
            $filters = $('[data-filter]', $tableContainer);

        $tableContainer.off('keyup', '[data-filter]');
        $tableContainer.on('keyup', '[data-filter]', $.debounce(500, function () {
            tables.get(table);
        }));

        $tableContainer.off('click', '.pagination a');
        $tableContainer.on('click', '.pagination a', function (e) {
            e.preventDefault();

            if ( $(this).attr('href') == '#' || $(this).hasClass('disabled') || $(this).hasClass('active'))
                return;

            $tableContainer.animate({ scrollTop: 0 }, "fast");
            tables.get(table, $(this).attr('href'));
        });

        $tableContainer.off('click', 'table th:not(.sorting_disabled)');
        $tableContainer.on('click', 'table th:not(.sorting_disabled)' ,function(){
            var sort = 'desc';
            var table = $(this).closest('table');
            if ($(this).hasClass("sorting_asc") || $(this).hasClass("sorting_desc")) {
                if ($(this).hasClass("sorting_desc")) {
                    $(this).removeClass('sorting_desc').addClass('sorting_asc');
                    sort = 'asc';
                }
                else {
                    $(this).removeClass('sorting_asc').addClass('sorting_desc');
                    sort = 'desc';
                }
            }
            else {
                $(this).removeClass('sorting_desc').addClass('sorting_asc');
                sort = 'asc';
            }
            $tableContainer.find('input[name="sorting[sort_by]"]').val($(this).attr("data-id"));
            $tableContainer.find('input[name="sorting[sort]"]').val(sort);
            $tableContainer
                .find('th:not(.sorting_disabled)')
                .not(this).removeClass('sorting_asc')
                .removeClass('sorting_desc')
                .addClass('sorting');
            tables.get($tableContainer.attr('id'));
        });

        $tableContainer.off('change', 'input[type="checkbox"]');
        $tableContainer.on('change', 'input[type="checkbox"]', function () {
            var table = $tableContainer.attr('id'),
                id = $(this).val(),
                check = $(this).is(':checked');

            if (typeof tables.items_arr[table] == 'undefined') {
                tables.items_arr[table] = {};
            }

            if ( id && id != 'on' ) {
                if ( check ) {
                    tables.items_arr[table][id] = id;
                } else {
                    if (id in tables.items_arr[table]) {
                        delete(tables.items_arr[table][id]);
                    }
                }
            }
        });

        $tableContainer.off('multichanged');
        $tableContainer.on('multichanged', function(e, data){
            dd( '$tableContainer.multichanged', data );
            $.each( data.items, function() {
                $( this ).trigger('change');
            });
        });

        $tableContainer.off('click', '[data-multi]');
        $tableContainer.on('click', '[data-multi]', function () {
            var table = $tableContainer.attr('id'),
                config = tables.config[table],
                action = $(this).attr('data-multi');

            if (typeof config == 'undefined') {
                alert('"' + table + '" configs is not defined!');
            }
            if (typeof tables.config[table][action] == 'undefined') {
                alert('"' + table + '" configs "'+action+'" is not defined!');
            }

            var _url,
                _modal,
                _method;

            if (typeof tables.config[table][action] === 'string') {
                _url = tables.config[table][action];
                _method = 'DELETE';
            } else {
                _url = tables.config[table][action]['url'];
                _method = tables.config[table][action]['method'];
                _modal = tables.config[table][action]['modal'];
            }

            var data = tables.getFilters(table);
                data.id = tables.items_arr[table];
                if (_method === 'DELETE')
                    data._method = 'DELETE';

            if (_method === 'GET') {
                return $modal.getModalContent({
                    url: _url,
                    id: data.id
                }, _method, $modal.initModal(_modal));
            }

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: _url,
                data: data,
                beforeSend: function() {
                    $tableContainer.find('.table_error').html('');
                },
                success: function(res){
                    dd( res );
                    if (typeof res.error != 'undefined') {
                        $tableContainer.find('.table_error').html('<div class="alert alert-danger" style="margin-top: 20px;">' + res.error + '</div>');
                    }
                    tables.items_arr[table] = {};
                    tables.get(table);
                    if (res.trigger) {
                        $(document).trigger(res.trigger, res);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    handlerFailTarget(jqXHR, textStatus, errorThrown, $tableContainer.find('.table_error'));
                }
            });
        });

        $tableContainer.off('click', 'td[data-editable-field]');
        $tableContainer.on('click', 'td[data-editable-field]', function () {
            var
                $td = $(this),
                orginValue = $td.html(),
                field = $td.data('editable-field');

            //checking if form already build
            if ($td.find('.input-group').length)
                return;

            var $form = $('<div class="input-group input-group-sm">' +
                '      <input type="text" class="form-control" value="'+orginValue+'">' +
                '      <i class="input-group-btn">' +
                '        <button class="btn btn-primary" type="button"><i class="icon check"></i> </button>' +
                '      </span>' +
                '    </div>');

            var inlineSubmit = function () {
                var formData = {};
                formData[field] = $form.find("input").val();
                $.ajax({
                    type: "POST",
                    url: $td.data('submit-url'),
                    data: formData,
                    dataType: "json",
                    beforeSend: function() {
                        $tableContainer.find('.table_error').html('');
                    },
                    success: function(res){
                        $td.html(formData[field]);
                        $form.find('button').off('click');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        handlerFailTarget(jqXHR, textStatus, errorThrown, $tableContainer.find('.table_error'));
                    }
                });
            };

            $td.html( $form );
            $form.find("input").focus();

            $(document).mouseup(function(e)
            {
                if ( ! $td.find($form).length )
                    return;

                if ( ! $td.is(e.target) && $td.has(e.target).length === 0)
                {
                    $td.html( orginValue );
                }
            });

            $form.find('button').on('click', function () {
                inlineSubmit();
            });
            $form.find('input').keydown(function (e) {
                if (e.keyCode != 13)
                    return;

                inlineSubmit();
            });
        });

    }
};

$(document).ready(function(){

    $(document).on('click', '.js-confirm-link', function(e){
        var url = $(this).attr('href');
        var text = $(this).data('confirm');
        $('#js-confirm-link .modal-body').html(text);
        var method = $(this).data('method');
        var data = $(this).data();
        var table = $(this).closest('[data-table]').closest('[id]');
        e.preventDefault();
        $('#js-confirm-link').modal({ backdrop: 'static', keyboard: false })
            .one('click', '.js-confirm-link-yes', function (e) {
                if (method == 'DELETE' || method == 'POST') {
                    if (method == 'DELETE')
                        data['_method'] = 'DELETE';
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: url,
                        cache: false,
                        data: data,
                        success: function(res){
                            $('#js-confirm-link').modal('hide');
                            if (table.length) {
                                tables.get(table.attr('id'));
                            }
                        }
                    });
                }
                else {
                    window.location = url;
                }
            });
    });

});