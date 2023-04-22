<div class="table-responsive scrollbox-large">

    <table class="table table-list table-hover">
        <thead>
        <tr>
            <th class="sorting_disabled">
                {{trans('front.time')}}
            </th>
            <th class="sorting_disabled">
                {{trans('front.quality')}}
            </th>
            <th class="sorting_disabled">
                {{trans('admin.size')}}
            </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @if (count($images))
            @foreach ($images as $image)
                <tr class="pointer" data-imageContainer="{{ $image->name }}">
                    <td onClick="app.deviceMedia.loadImage('{{$deviceId}}','{{ $image->name }}', '#imgContainer'); ">{{ tdate($image->created_at) }}</td>
                    <td onClick="app.deviceMedia.loadImage('{{$deviceId}}','{{ $image->name }}', '#imgContainer');">{{ $image->imageQuality() }}</td>
                    <td onClick="app.deviceMedia.loadImage('{{$deviceId}}','{{ $image->name }}', '#imgContainer');">{{ $image->size }}</td>
                    <td>
                        <div class="btn-group dropleft droparrow" data-position="fixed">
                            <i class="btn icon options" data-toggle="dropdown" data-position="fixed"
                               aria-haspopup="true" aria-expanded="false"></i>
                            @if ( Auth::User()->perm('camera', 'remove') )
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:app.deviceMedia.deleteImage('{{$deviceId}}','{{ $image->name }}', '#ajax-photos');"
                                           class="object_show_history">
                                            <span class="icon delete"></span>
                                            <span class="text">{{trans('global.delete')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="3">{{trans('front.no_images')}}</td>
            </tr>
        @endif
        </tbody>
    </table>

</div>
<div class="nav-pagination" id="imgPaginate">
    @if (count($images))
        {!! $images->render() !!}
    @endif
</div>