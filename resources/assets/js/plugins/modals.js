$(document).ready(function() {
    $(document).on('hidden', '.modal', function(){
        // Todo
    });

    // Open on click
    $(document).on('click', '[data-modal]', function(){
        var data = $(this).data(),
            method = (typeof data.method == 'undefined' ? 'GET' : data.method),
            modal = $modal.initModal(data.modal);

        modal.on('hidden', function(){
            modal.remove();
        });

        $modal.getModalContent(data, method, modal);
    });

    $(document).on('click', '[data-submit="modal"],.modal .update:visible, .modal .update_hidden', function(){
        var element = $(this);
        var modal = element.closest('.modal');
        var form = modal.find('form');
        var url = form.attr('action');
        var method = form.find('input[name="_method"]').val();
        var data = form.serializeArray();

        method = (typeof method != 'undefined' ? method : 'POST');

        $modal.postData(url, method, modal, data);
    });

    $(document).on('click', '.modal .update_with_files:visible', function(){
        var element = $(this);
        var modal = element.closest('.modal');
        var form = modal.find('form');
        var url = form.attr('action');
        var method = form.find('input[name="_method"]').val();
        var data = new FormData(form['0']);

        method = (typeof method != 'undefined' ? method : 'POST');

        $modal.postData(url, method, modal, data, true);
    });
});

var $modal = {
    initModal: function(modal) {
        var element = $('#' + modal);
        if (!element.length) {
            $('body').append('<div class="modal fade" id="' + modal + '"><div class="contents"></div></div>');
            element = $('#' + modal);
        }

        return element;
    },
    getModalContent: function(data, method, modal) {
        $.ajax({
            type: method,
            dataType: "html",
            url: data.url,
            data: {
                id: data.id
            },
            beforeSend: function() {
                loader.add( $('body') );
            },
            success: function(res){
                modal.find('.contents').html(res);
                modal.modal('show');

                initComponents( modal );
            },
            complete: function() {
                loader.remove( $('body') );
            },
            error: function(jqXHR, textStatus, errorThrown) {
                handlerFailModal(jqXHR, textStatus, errorThrown);
            }
        });
    },
    postData: function(url, method, modal, data, with_files) {
        if (method == 'PUT' || method == 'DELETE')
            method = 'POST';

        var modal_content = modal.find('.modal-content').length ? modal.find('.modal-content') : modal;

        var ajax = {
            type: method,
            dataType: "json",
            url: url + '?_=' + $.now(),
            data: data,
            beforeSend: function() {
                modal.find('.help-block.error').remove();
                modal.find('.has-error').removeClass('has-error');

                loader.add( modal_content );
            },
            success: function(res){
                if (res.status != 0) {
                    if (res.status == 1)
                        modal.modal('hide');

                    $modal.initCallback(res, modal.attr('id'));
                }

                if (res.trigger) {
                    $(document).trigger(res.trigger, res);
                }
            },
            complete: function(jqXHR, textStatus) {
                loader.remove( modal_content );

                if (typeof jqXHR.responseJSON.errors) {
                    $modal.parseErrors(jqXHR.responseJSON.errors, modal);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                handlerFail(jqXHR, textStatus, errorThrown);
            }
        };

        if (typeof with_files != 'undefined') {
            $.extend(ajax, {
                processData: false,
                contentType: false
            });
        }

        $.ajax(ajax);
    },
    parseErrors: function(errors, modal) {
        $modal.defaultParseErrors(errors, modal);
    },
    defaultParseErrors: function(errors, modal) {
        $.each( errors, function( key, value ) {

            var name = key,
                parts = key.split('.');

            if (parts.length > 1)
                name = parts.shift()+ '[' + parts.join('][') + ']';

            var error_block = '<span class="help-block error input_error_' + name + '">' + value + '</span>',
                el = modal.find('input[name="' + name + '"]:not([type="radio"]), select[name="' + name + '"], select[name="' + name + '[]"], textarea[name="' + name + '"], input[name="' + name + '_fake"]:not([type="radio"]), select[name="' + name + '_fake"], textarea[name="' + name + '_fake"]');

            if (el.length > 0) {
                if (el.is(':visible') && el.attr('type') != 'hidden') {
                    el.after(error_block);
                    el.closest('.form-group').addClass('has-error');
                } else {
                    var $form_group = el.closest('.form-group');

                    if ($form_group.length > 0)
                        $form_group.append(error_block).addClass('has-error');
                    else
                        el.after(error_block);
                }

                var tab = el.closest('.tab-pane').attr('id');
                if (typeof tab != 'undefined') {
                    modal.find('a[href="#' + tab + '"]').addClass('has-error');
                }
            } else {
                $('.modal-body', modal).prepend(error_block);
            }

        });

        if (modal.find('.help-block.error').length > 0) {
            var fromTop = modal.find('.help-block.error').first().position().top;
            if (fromTop == 0)
                fromTop = modal.find('.help-block.error').first().offsetParent().position().top;
        }
    },
    initCallback: function(res, id) {
        var fnc = id.replace('-','_') + '_modal_callback';
        var callback_fnc = window[fnc];

        if (typeof callback_fnc == 'function') {
            callback_fnc(res);
        }
    }
}
