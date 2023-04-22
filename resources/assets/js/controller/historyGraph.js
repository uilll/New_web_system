function HistoryGraph() {
    var
        _this = this;
    
    _this.init = function() {
            _this.plot =  '';
            _this.graph_data = {};
            _this.variable = null;
            _this.variables = {
                speed: app.settings.units.speed,
                altitude: " " + window.lang.m
            };
            _this.options = {
                colors: ["rgba(76, 84, 84, 1)"],
                series: {
                    shadowSize: 0
                },
                crosshair: {
                    mode: "x"
                },
                lines: {
                    show: true,
                    lineWidth: 1.5,
                    lineColor: 'red',
                    fill: true,
                    fillColor: "rgba(76, 84, 84, 0.4)"
                },
                zoom: {
                    interactive: false
                },
                pan: {
                    interactive: true
                },
                xaxis: {
                    minTickSize: [30, "minute"],
                    mode: 'time',
                    twelveHourClock: false,
                },
                yaxis: {
                    tickFormatter: function(value, axis) {
                        return value.toFixed(axis.tickDecimals) + _this.variables[_this.variable];
                    },
                    minTickSize: 1,
                    tickDecimals: 0,
                    zoomRange: false

                },
                legend: {
                    noColumns: 0,
                    labelFormatter: function (label, series) {
                        return "<font color=\"white\">" + label + "</font>";
                    },
                    backgroundColor: "#000",
                    backgroundOpacity: 1.0,
                    labelBoxBorderColor: "#000000",
                    position: "nw"
                },
                grid: {
                    color: "#999999",
                    margin: {
                        top: 10,
                        left: 10,
                        bottom: 10,
                        right: 10
                    },
                    hoverable: true,
                    clickable: true,
                    borderWidth: 1,
                    borderColor: '#DDDDDD'
                }
            };
    };
    
    _this.init();

    _this.events = function() {
        $(document).on('show.bs.tab', '#graph_sensors [data-toggle="tab"]', function() {
            _this.variable = $(this).attr('data-id');
            dd( 'variable', _this.variable );
            _this.parseData();

            app.history.player.clear();
        });
    };

    _this.events();

    _this.clear = function() {
        $('#bottom-history').hide();

        if (typeof _this.plot.shutdown !== 'undefined') {
            _this.plot.shutdown();
        }
        $("#placeholder").unbind("plotclick");
        $("#placeholder").unbind("plothover");

        _this.graph_data = {};
        _this.plot = {};

        $('#hoverdata').html('');
        $('#hoverdata-date').html('');

        app.map.invalidateSize();
    };

    _this.getData = function(type) {
        if (typeof _this.graph_data[type] === 'undefined') {
            _this.graph_data[type] = [];
            if (typeof window.history_sensors_values[type] !== 'undefined') {
                $.each(window.history_sensors_values[type], function(index, value) {
                    var tdate = value.t;
                    var y = tdate.substr(0, 10);
                    var h = tdate.substr(11, 8);
                    var date = new Date(y + 'T' + h + 'Z');
                    _this.graph_data[type].push([date.getTime(), value.v, 0, 'i' + value.i]);
                });
            }
        }
    };
    _this.parseData = function() {
        _this.variables = {
            speed: app.settings.units.speed,
            altitude: " " + window.lang.m
        };

        var type = _this.variable;
        var measure = _this.variables[_this.variable];
        if (typeof measure === 'undefined') {
            if (typeof window.history_sensors[type] !== 'undefined') {
                measure = window.history_sensors[type].sufix;
                measure = measure === null ? '' : measure;
                _this.variables[type] = measure;
            }
        }
        _this.getData(type);
        _this.plot = $.plot("#placeholder", [{
            data: _this.graph_data[type],
            color: "#999999",
            lines: {
                fill: true,
                lineWidth: 1
            }
        }], _this.options);

        $("#placeholder").bind("plothover", function (event, pos, item) {
            if (item != null) {
                var strKmh = item.datapoint[1] + " " + measure;
                var fixDate = moment.utc(item.datapoint[0]).format('YYYY-MM-DD HH:mm:ss');

                $("#hoverdata").text(strKmh);
                $("#hoverdata-date").text(fixDate);
            }
        });

        $("#placeholder").bind("plotclick", function (event, pos, pitem) {
            if (pitem != null) {
                var item = window.history_cords[pitem.series.data[pitem.dataIndex][3]];

                var nav = '';
                nav += '<ul class="nav nav-tabs nav-default" role="tablist">';
                nav += '<li data-toggle="tooltip" data-placement="top" title="Close"><a href="javascript:" data-dismiss="popup"><i class="fa fa-times fa-1"></i></a></li>';
                nav += '</ul>';

                var parametersHTML = '';
                parametersHTML += '<table class="table table-condensed"><tbody>';
                parametersHTML += '<tr><th>' + window.lang.address + ':</th><td><span data-device="address" data-lat="'+item.lat+'" data-lng="'+item.lng+'"></span></td></tr>';
                parametersHTML += '<tr><th>' + window.lang.lat + ':</th><td>' + item.lat + '&deg;</td></tr>';
                parametersHTML += '<tr><th>' + window.lang.lng + ':</th><td>' + item.lng + '&deg;</td></tr>';
                parametersHTML += '<tr><th>' + window.lang.street_view + ':</th><td><a href="http://maps.google.com/?q=&cbll=' + item.lat + ',' + item.lng + '&cbp=12,20.09,,0,5&layer=c&hl=' + window.lang.lang + '" target="_blank">' + window.lang.preview + ' &gt;&gt;</a></td></tr>';
                parametersHTML += '<tr><th>' + window.lang.altitude + ':</th><td>' + item.altitude + ' ' + window.lang.m + '</td></tr>';
                parametersHTML += '<tr><th>' + window.lang.speed + ':</th><td>' + item.speed + ' ' + app.settings.units.speed + '</td></tr>';
                parametersHTML += '<tr><th>' + window.lang.time + ':</th><td>' + item.time + '</td></tr>';
                parametersHTML += '</tbody></table>';

                var html  = '';
                html += '<div class="popup-content">';
                html += '   <div class="popup-header">'+nav+'<div class="popup-title"></div></div>';
                html += '   <div class="popup-body">'+parametersHTML+'</div>';
                html += '</div>';

                var popup = L.popup({className: 'leaflet-popup-history'})
                    .setLatLng([item.lat, item.lng])
                    .setContent(html)
                    .openOn(app.map);

                initComponents( popup.getElement() );

                //History.polyline.addLayer(popup);
                //Devices.map.setView(popup.getLatLng());
            }
        });
    };

    _this.graphLeft = function(e) {
        if (e)
            e.preventDefault();

        _this.plot.pan({
            left: -100
        });
    };

    _this.graphRight = function(e) {
        if (e)
            e.preventDefault();

        _this.plot.pan({
            left: +100
        });
    };

    _this.zoomIn = function(e) {
        if (e)
            e.preventDefault();

        _this.plot.zoom({ center: { left: 10, top: 0 } });
    };

    _this.zoomOut = function(e) {
        if (e)
            e.preventDefault();

        _this.plot.zoomOut({ center: { left: 10, top: 0 } });
    };
}
