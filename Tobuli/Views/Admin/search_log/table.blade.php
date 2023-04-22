<div class="table_error"></div>
<div class="table-responsive" style="text-align: center;">
    <div style="display: none;">
        {{$color =true}}
    </div> 
    @if($status)
        @foreach ($matches as $matche)
            <div style="background-color:{{ $color ? 'gainsboro' : 'white' }}">
                {{$matche}}   
            </div>
            <div style="display: none;">
                {{$color = !$color}}
            </div>
        @endforeach
    @endif
</div>