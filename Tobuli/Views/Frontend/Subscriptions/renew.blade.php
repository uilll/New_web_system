@extends('Frontend.Layouts.default')

@section('header-menu-items')
    @if ( Auth::User() )
        <li>
            <a href="{{ route('logout') }}">
                <i class="icon logout"></i> <span class="text">{{ trans('global.log_out') }}</span>
            </a>
        </li>
    @endif
@stop

@section('content')
    @if (Session::has('message'))
        <div class="alert alert-danger alert-dismissible">
            {!! Session::get('message') !!}
        </div>
    @endif
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible">
            {!! Session::get('success') !!}
        </div>
    @endif

    <h1>{!! trans('front.renew_upgrade') !!}</h1>

    <div class="plans">
        @foreach($plans as $plan)
            <div class="plan-col">
                <div class="plan">
                    <div class="plan-heading">
                        <div class="plan-title">{{ $plan->title }}</div>
                    </div>
                    <div class="plan-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>{{ trans('validation.attributes.objects') }}</td>
                                    <td>{{ $plan->objects }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('front.duration') }}</td>
                                    <td>{{ $plan->duration_value }} {{ trans('front.'.$plan->duration_type) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('validation.attributes.price') }}</td>
                                    <td>{{ float($plan->price) }} {{ strtoupper(settings('main_settings.paypal_currency')) }}</td>
                                </tr>
                                @foreach ($permissions as $perm => $value)
                                    <tr>
                                        <td>{{ trans('front.'.$perm) }}</td>
                                        <td><i class="icon check {{ $plan->perm($perm, 'view') ? '' : 'disabled' }}"></i></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="plan-footer">
                        @if ( ! ($plan->id == Auth::User()->billing_plan_id && $plan->price == 0))
                        @if (isPublic())
                            <a href="{!!Config::get('tobuli.frontend_subscriptions')!!}?email={{base64_encode(Auth::User()->email)}}" class="btn btn-action btn-plan">
                                {{ $plan->id == Auth::User()->billing_plan_id ? trans('front.renew') : trans('front.upgrade') }}
                            </a>
                        @elseif (settings('main_settings.payment_type') == 'stripe')
                            {!! Form::open(['route' => ['payments.checkout', $plan->id], 'method' => 'POST']) !!}
                            <script
                                    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                    data-key="{!! settings('main_settings.stripe_public_key') !!}"
                                    data-amount="{{(int)($plan->price*100) }}}}"
                                    data-name="{{$plan->title}}"
                                    data-email="{{Auth::user()->email}}"
                                    data-allow-remember-me="false"
                                    data-label="{{ $plan->id == Auth::User()->billing_plan_id ? trans('front.renew') : trans('front.upgrade') }}">
                            </script>
                            {!! Form::close() !!}
                        @elseif (settings('main_settings.payment_type') == 'paypal')
                            <a href="{{ route('payments.checkout', $plan->id) }}" class="btn btn-action btn-plan">
                                {{ $plan->id == Auth::User()->billing_plan_id ? trans('front.renew') : trans('front.upgrade') }}
                            </a>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop