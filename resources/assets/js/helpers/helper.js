function dd() {
    if ( ! app.debug )
        return;

    console.log( arguments );
}

var decodeHtmlEntity = function(str) {
    return str.replace(/&#(\d+);/g, function(match, dec) {
        return String.fromCharCode(dec);
    });
};

var encodeHtmlEntity = function(str) {
    var buf = [];
    for (var i=str.length-1;i>=0;i--) {
        buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
    }
    return buf.join('');
};

sidebarSearch = function( value, items, select, container ) {
    dd('sidebarSearch');

    var _items,
        $list = $(container);
    dd('sidebarSearch.hide.complete');

    if (value !== '')
    {
        $('.group .group-heading', $list).hide();

        $( 'li[' + select +']' , $list).addClass('hidden');
        $( 'tr[' + select +']' , $list).addClass('hidden');

        _items = items.filter( function(item) {
            return (item.searchValue.indexOf( value ) >= 0);
        });

        $.each( _items, function(index, item) {
            $( 'li[' + select +'="'+ item.id() + '"]' , $list).removeClass('hidden');
            $( 'tr[' + select +'="'+ item.id() + '"]' , $list).removeClass('hidden');
        });

    } else {
        $( 'li[' + select +']' , $list).removeClass('hidden');
        $( 'tr[' + select +']' , $list).removeClass('hidden');
    }

    $('.group', $list).each(function(){
        var $group = $(this);

        if ( $('.group-list li[' + select +']:not(.hidden)', $group).length )
        {
            $('.group-heading', $group).show();

            $('.collapse', $group).addClass('in');
            $('.group-title', $group).removeClass('collapsed');
        }
    });

    if (value === '') {
        $('.group-title[aria-expanded="false"]', $list).addClass('collapsed');
        $('.collapse[aria-expanded="false"]', $list).removeClass('in');
    }
};

function handlerFail(jqXHR, textStatus, errorThrown) {
    dd(jqXHR, textStatus, errorThrown);

    if (jqXHR.status == 401) {
        return window.location.reload();
    }

    if (jqXHR.status == 503) {
        return window.location.reload();
    }
}

function handlerFailTarget(jqXHR, textStatus, errorThrown, target)
{
    handlerFail(jqXHR, textStatus, errorThrown);

    var
        _text = jqXHR.responseText,
        _dom = $(_text);

    if (_dom.find('.modal-body').length)
        _text = _dom.find('.modal-body').html();

    target.html(_text);
}

function handlerFailModal(jqXHR, textStatus, errorThrown)
{
    handlerFail(jqXHR, textStatus, errorThrown);

    modal = $modal.initModal('error-modal');
    modal.find('.contents').html( jqXHR.responseText );
    modal.modal('show');

    initComponents( modal );
}

function fetchFromObject(obj, prop) {

    if(typeof obj === 'undefined') {
        return false;
    }

    var _index = prop.indexOf('.');

    if(_index > -1) {
        return fetchFromObject(obj[prop.substring(0, _index)], prop.substr(_index + 1));
    }

    return obj[prop];
}

function dialogMoveToTop(el, check) {
    var elz = parseInt(el.css('z-index'));
    var index = parseInt(el.css('z-index'));
    $.each($('.ui-dialog.ui-widget.ui-widget-content'), function( key, value ) {
        if (index < parseInt($(this).css('z-index'))) {
            index = parseInt($(this).css('z-index'));
        }
    });

    if ((elz < index && typeof check === 'undefined') || typeof check !== 'undefined' ) {
        el.css('z-index', index + 2);
    }
}

function convertHex(hex,opacity){
    hex = hex.replace('#','');
    r = parseInt(hex.substring(0,2), 16);
    g = parseInt(hex.substring(2,4), 16);
    b = parseInt(hex.substring(4,6), 16);
    result = 'rgba('+r+','+g+','+b+','+opacity/100+')';

    return result;
}

function degToLatLng(deg) {
    var arr = deg.split(':');

    var d = parseFloat(arr[0]);
    var m = parseInt(arr[1]);
    var s = parseFloat(arr[2]);

    var v = (Math.abs(d) + (m / 60) + (s / 3600));
    if (d < 0.0) v = 0.0 - v;

    return v.toFixed(6);
}

function placesRouteLatLngsToPointsString(a) {
    if (a.length > 0) {
        var d = [];
        for (var c = 0; c < a.length; c++) {
            var f = a[c];
            var e = f.lat;
            var b = f.lng;
            d.push({lat: parseFloat(e).toFixed(6), lng: parseFloat(b).toFixed(6)});
        }
        return JSON.stringify(d);
    }

    return '';
}

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

var DateDiff = {

    inDays: function(d1, d2) {
        var t2 = d2.getTime();
        var t1 = d1.getTime();

        return parseInt((t2-t1)/(24*3600*1000));
    },

    inWeeks: function(d1, d2) {
        var t2 = d2.getTime();
        var t1 = d1.getTime();

        return parseInt((t2-t1)/(24*3600*1000*7));
    },

    inMonths: function(d1, d2) {
        var d1Y = d1.getFullYear();
        var d2Y = d2.getFullYear();
        var d1M = d1.getMonth();
        var d2M = d2.getMonth();

        return (d2M+12*d2Y)-(d1M+12*d1Y);
    },

    inYears: function(d1, d2) {
        return d2.getFullYear()-d1.getFullYear();
    }
};

function momentCalendar(val, parent) {
    var date_from = $(parent).find('input[name="date_from"]');
    var date_to = $(parent).find('input[name="date_to"]');
    var format = 'YYYY-MM-DD';

    switch (val) {
        case "0":
            date_from.val(moment().minute(0).hour(0).format(format));
            date_to.val(moment().minute(0).hour(0).format(format));
            break;
        case "1":
            date_from.val(moment().minute(0).hour(0).format(format));
            date_to.val(moment().add(1, "days").minute(0).hour(0).format(format));
            break;
        case "2":
            date_from.val(moment().subtract(1, "days").minute(0).hour(0).format(format));
            date_to.val(moment().minute(0).hour(0).format(format));
            break;
        case "3":
            date_from.val(moment().subtract(2, "days").minute(0).hour(0).format(format));
            date_to.val(moment().subtract(1, "days").minute(0).hour(0).format(format));
            break;
        case "4":
            date_from.val(moment().subtract(3, "days").minute(0).hour(0).format(format));
            date_to.val(moment().subtract(2, "days").minute(0).hour(0).format(format));
            break;
        case "5":
            date_from.val(moment().day("Monday").minute(0).hour(0).format(format));
            date_to.val(moment().add(1, "days").minute(0).hour(0).format(format));
            break;
        case "6":
            date_from.val(moment().day("Monday").subtract(1, "week").minute(0).hour(0).format(format));
            date_to.val(moment().day("Monday").minute(0).hour(0).format(format));
            break;
        case "7":
            date_from.val(moment().startOf("month").minute(0).hour(0).format(format));
            date_to.val(moment().add(1, "days").minute(0).hour(0).format(format));
            break;
        case "8":
            date_from.val(moment().startOf("month").subtract(1, "month").minute(0).hour(0).format(format));
            date_to.val(moment().startOf("month").minute(0).hour(0).format(format));
            break;
        case "9":
            date_from.val(moment().startOf("month").subtract(3, "month").minute(0).hour(0).format(format));
            date_to.val(moment().startOf("month").minute(0).hour(0).format(format));
            break;
    }
}

function secondsToTime(_seconds)
{
    var
        hours               = Math.floor(_seconds / (60 * 60)),
        divisor_for_minutes = _seconds % (60 * 60),
        minutes             = Math.floor(divisor_for_minutes / 60),
        divisor_for_seconds = divisor_for_minutes % 60,
        seconds             = Math.ceil(divisor_for_seconds);

    if (hours < 0 || minutes < 0 || seconds < 0)
        return '0s';

    return (hours ? hours+"h " : '')+(minutes ? minutes+"min " : '')+seconds+"s";
}

function checkPerm(el) {
    if (el.hasClass('perm_edit') || el.hasClass('perm_remove')) {
        var parent = el.closest('tr');
        var view = parent.find('.perm_view');
        var edit = parent.find('.perm_edit').prop('checked');
        var remove = parent.find('.perm_remove').prop('checked');

        if (edit || remove) {
            if (!view.prop('checked'))
                view.trigger('click');

            view.prop('disabled', true).closest('div').addClass('disabled');
        }
        else {
            view.prop('disabled', false).closest('div').removeClass('disabled');
        }
    }
}

function checkPerms() {
    $.each($('.perm_edit, .perm_remove'), function (key, value) {
        checkPerm($(this));
    });
}

function readImage(input, target) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $(target).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function formBuilder($attributesContainer, attributes, values) {
    $attributesContainer = $( $attributesContainer );

    $attributesContainer.html('');

    if (values) {
        $.each(values, function( name, value )
        {
            $.each(attributes, function( index, attribute )
            {
                if (attribute.name !== name)
                    return true;

                attributes[index].default = value;
            });
        });
    }

    $.each(attributes, function( index, attribute )
    {
        var $formGroup = $('<div class="form-group"></div>');
        var value = attribute.default ? attribute.default : '';

        $formGroup.append( '<label for="'+attribute.name+'">'+attribute.title+':</label>' );

        switch (attribute.type) {
            case 'integer':
            case 'string':
                $formGroup.append( '<input type="text" class="form-control" name="'+attribute.name+'" value="'+value+'" />' );
                break;
            case 'text':
                $formGroup.append( '<textarea class="form-control" name="'+attribute.name+'">'+value+'</textarea>' );
                break;
            case 'select':
                var $select = $('<select class="form-control" name="'+attribute.name+'"></select> ');
                $.each(attribute.options, function( index, option ){
                    $select.append('<option value="'+option.id+'">'+option.title+'</option>');
                });
                $formGroup.append( $select );
                $select
                    .val(value)
                    .selectpicker();
                break;
            case 'multiselect':
                var $select = $('<select class="form-control multiexpand" name="'+attribute.name+'" multiple="multiple" data-live-search="true" data-actions-box="true"></select> ');
                $.each(attribute.options, function( index, option ){
                    $select.append('<option value="'+option.id+'">'+option.title+'</option>');
                });
                $formGroup.append( $select );
                $select
                    .val(value)
                    .selectpicker();
                break;
        }

        if (attribute.description) {
            $formGroup.append('<small>'+attribute.description+'</small>');
        }

        var $container = $('<div class="col-sm-12"></div>');

        if (attributes.length > 1 && attribute.type != 'text' ) {
            $container = $('<div class="col-sm-6"></div>');
        }

        $container.append($formGroup);

        $attributesContainer.append($container);
    });
}