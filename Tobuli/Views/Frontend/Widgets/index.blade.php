<?php
$widgets = Auth::user()->getSettings('widgets');
if ( empty($widgets) ) {
    $widgets = settings('widgets');
}
?>
@if( ! empty($widgets['status']) && ! empty($widgets['list']) )
<div id="widgets" style="display: none;">
    <a class="btn-collapse" id="btn-collapse" onclick="app.changeSetting('toggleWidgets');"><i></i></a>

    <div class="widgets-content">		
        @foreach( $widgets['list'] as $widget)	
			<br>		
            @include('Frontend.Widgets.'.$widget)			
        @endforeach		
    </div>
</div>
@endif
