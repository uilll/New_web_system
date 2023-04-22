<div class="form-inline">
    <div class="form-group">
        {!! Form::label('route_color', trans('front.route_color').' '.trans('validation.attributes.color')) !!}
        {!! Form::text('plugins['.$plugin->key.'][options][value]', $plugin->options['value'], ['class' => 'form-control colorpicker']) !!}
    </div>
</div>