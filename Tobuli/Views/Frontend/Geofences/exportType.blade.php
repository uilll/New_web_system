{!! Form::label($input, trans('validation.attributes.'.$input).':') !!}
{!! Form::select($input.'[]', $items, $selected, ['class' => 'form-control', 'multiple' => 'multiple', 'data-live-search' => true]) !!}