<div class="footer-table" id="bottom-history">
    <div class="bottom-history-header">
        <ul class="nav nav-tabs pull-right" role="tablist">
            <li role="presentation" class="active">
                <a href="#graph" class="btn btn-default btn-xs" aria-controls="graph" role="tab" data-toggle="tab" aria-expanded="false">
                    <i class="fa fa-line-chart"></i> {!! trans('front.graph') !!}
                </a>
            </li>
            <li role="presentation">
                <a href="#datalog" class="btn btn-default btn-xs" aria-controls="datalog" role="tab" data-toggle="tab" aria-expanded="true">
                    <i class="fa fa-reorder"></i> {!! trans('front.data_log') !!}
                </a>
            </li>
            <li role="presentation">
                <a href="javascript:" class="btn-close" onClick="app.history.graph.clear();"><i class="fa fa-times"></i></a>
            </li>
        </ul>

        <ul class="nav nav-tabs nav-default" id="graph_sensors"></ul>
    </div>


    <div class="collapse-control">
        <a href="javascript:"></a>
    </div>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="graph">

            <div class="graph-controls">
                <ul class="nav nav-pills pull-left">
                    <li><a href="javascript:" class="btn btn-xs" onclick="app.history.player.play();"><i class="icon play"></i></a></li>
                    <li><a href="javascript:" class="btn btn-xs" onclick="app.history.player.pause();"><i class="icon pause"></i></a></li>
                    <li><a href="javascript:" class="btn btn-xs" onclick="app.history.player.stop();"><i class="icon stop"></i></a></li>
                    <li><select id="historySpeed" class="form-control" onchange="app.history.player.setSpeed( this.value );">
                            <option value="2000">x1</option>
                            <option value="1000">x2</option>
                            <option value="500">x3</option>
                            <option value="250">x4</option>
                            <option value="125">x5</option>
                            <option value="65">x6</option>
                        </select></li>
                </ul>

                <ul class="nav nav-pills pull-right">
                    <li id="hoverdata"></li>
                    <li id="hoverdata-date"></li>
                    <li>
                        <a href="javascript:" class="btn btn-xs" onClick="app.history.graph.zoomIn();"><i class="fa fa-search-plus"></i></a>
                    </li>
                    <li>
                        <a href="javascript:" class="btn btn-xs" onClick="app.history.graph.zoomOut();"><i class="fa fa-search-minus"></i></a>
                    </li>
                </ul>
            </div>

            <div class="graph-1-wrap">
                <div id="placeholder" class="graph-1"></div>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="datalog">
            <div id="messages_tab">
                <div data-table></div>
            </div>
        </div>
    </div>
</div>

<script>
    tables.set_config('messages_tab', {
        url:'{{ route("history.positions") }}',
        delete_url:'{{ route("history.delete_positions") }}'
    });
</script>