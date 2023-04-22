@if (!empty($mapIcons) && !empty($items = $mapIcons->toArray()))
<ul class="group-list">
    @foreach ($items as $key => $item)
        <?php $item['coordinates'] = json_decode($item['coordinates']) ;?>
        <li data-mapicon-id="{{ $item['id'] }}">
            <div class="checkbox">
                <input type="checkbox" name="mapIcon[{{ $item['id'] }}]" value="{{ $item['id'] }}" {{ !empty($item['active']) ? 'checked="checked"' : '' }} onChange="app.mapIcons.active('{{ $item['id'] }}', this.checked);"/>
                <label></label>
            </div>
            <div class="name">
                <span data-mapicon="name">{{ $item['name'] }}</span>
            </div>
            <div class="details">
                @if (Auth::User()->perm('poi', 'edit') || Auth::User()->perm('poi', 'remove'))
                    <div class="btn-group dropleft droparrow"  data-position="fixed">
                        <i class="btn icon options" data-toggle="dropdown" data-position="fixed" aria-haspopup="true" aria-expanded="false"></i>
                        <ul class="dropdown-menu" >
                            @if ( Auth::User()->perm('poi', 'edit') )
                                <li>
                                    <a href='javascript:;' onclick="app.mapIcons.edit({{ $item['id'] }});">
                                        <span class="icon edit"></span>
                                        <span class="text">{{ trans('global.edit') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::User()->perm('poi', 'remove'))
                                <li>
                                    <a href='javascript:;' data-target='#deleteMapIcon' onclick="app.mapIcons.delete({{ $item['id'] }});" data-toggle='modal'>
                                        <span class="icon delete"></span>
                                        <span class="text">{{ trans('global.delete') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
            <script>app.mapIcons.add(jQuery.parseJSON('{!! json_encode($item) !!}'));</script>
        </li>
    @endforeach

</ul>
@else
    <p class="no-results">{!! trans('front.no_map_icons') !!}</p>
@endif
