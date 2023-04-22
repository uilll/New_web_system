<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Netshell\Paypal\Facades\Paypal;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Tobuli\Repositories\BillingPlan\BillingPlanRepositoryInterface as BillingPlan;
use Tobuli\Repositories\User\UserRepositoryInterface as User;

class PaymentsController extends Controller {

    private $_apiContext;

    private $billingPlanRepo;

    private $userRepo;

    public function __construct(BillingPlan $billingPlanRepo, User $userRepo)
    {
        $this->billingPlanRepo = $billingPlanRepo;

        $this->userRepo = $userRepo;

        switch (settings('main_settings.payment_type')) {
            case 'paypal':
                $this->setUpPaypal();
                break;
        }
    }

    private function setUpPaypal() {
        $this->_apiContext = PayPal::ApiContext(
            settings('main_settings.paypal_client_id'),
            settings('main_settings.paypal_secret'));

        $this->_apiContext->setConfig(array(
            'mode' => 'live',
            'service.EndPoint' => 'https://api.paypal.com',
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'FINE'
        ));
    }

    public function getCancel() {
        return Redirect::route('home');
    }

    public function getCheckout(Request $request, $plan_id) {
        $plan = $this->billingPlanRepo->find($plan_id);

        $user = Auth::User();

        if (empty($plan))
            return redirect(route('subscriptions.renew'))->with(['message' => trans('front.plan_not_found')]);

        if (empty($plan->price))
        {
            $this->updateUser($plan, $user);

            return redirect(route('subscriptions.renew'))->with(['success' => trans('front.payment_received')]);
        }

        try {
            switch (settings('main_settings.payment_type')) {
                case 'paypal':
                    return $this->checkoutPaypal($request, $plan, $user);

                    break;
                case 'stripe':
                    return $this->checkoutStripe($request, $plan, $user);

                    break;
                default:
                    throw new \Exception('Unsupported payment gateway');
            }
        } catch (\Exception $e) {
            return redirect(route('subscriptions.renew'))->with(['message' => $e->getMessage()]);
        }
    }

    public function getPayment(Request $request, $plan_id, $user_id = null) {
        $plan = $this->billingPlanRepo->find($plan_id);

        if (Auth::check())
            $user = Auth::User();
        else
            $user = $this->userRepo->find($user_id);

        if (empty($plan))
            return redirect(route('subscriptions.renew'))->with(['message' => trans('front.plan_not_found')]);

        if (empty($plan->price))
        {
            $this->updateUser($plan, $user);

            return redirect(route('subscriptions.renew'))->with(['success' => trans('front.payment_received')]);
        }

        try {
            switch (settings('main_settings.payment_type')) {
                case 'paypal':
                    return $this->paymentPaypal($request, $plan, $user);

                    break;
                case 'stripe':
                    return $this->paymentStripe($request, $plan, $user);

                    break;
                default:
                    throw new \Exception('Unsupported payment gateway');
            }
        } catch (\Exception $e) {
            return redirect(route('subscriptions.renew'))->with(['message' => $e->getMessage()]);
        }
    }


    private function checkoutPaypal(Request $request, $plan, $user)
    {
        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $amount = PayPal::Amount();
        $amount->setCurrency(strtoupper(settings('main_settings.paypal_currency')));
        $amount->setTotal($plan->price);

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription(settings('main_settings.paypal_payment_name'));

        $redirectUrls = PayPal::RedirectUrls();
        $redirectUrls->setReturnUrl(route('payments.get_done', ['user_id' => $user->id, 'plan_id' => $plan->id]));
        $redirectUrls->setCancelUrl(route('payments.get_cancel'));

        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        try {
            $response = $payment->create($this->_apiContext);
            $redirectUrl = $response->links[1]->href;
        }
        catch(\Exception $e) {
            throw new \Exception('Unable to connect to paypal.');
        }

        return Redirect::to($redirectUrl);
    }

    private function paymentPaypal(Request $request, $plan, $user)
    {
        $id = $request->get('paymentId');
        $payer_id = $request->get('PayerID');

        try {
            $payment = PayPal::getById($id, $this->_apiContext);

            $paymentExecution = PayPal::PaymentExecution();

            $paymentExecution->setPayerId($payer_id);
            $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

            $this->updateUser($plan, $user);
        } catch (\Exception $e) {
            return redirect(route('subscriptions.renew'))->with(['message' => trans('front.unexpected_error')]);
        }

        return redirect(route('subscriptions.renew'))->with(['success' => trans('front.payment_received')]);
    }

    private function checkoutStripe(Request $request, $plan, $user)
    {
        return $this->paymentStripe($request, $plan, $user);
    }

    private function paymentStripe(Request $request, $plan, $user){
        try {
            Stripe::setApiKey(settings('main_settings.stripe_secret_key'));

            $customer = Customer::create(array(
                'email' => $request->stripeEmail,
                'source'  => $request->stripeToken
            ));

            $charge = Charge::create(array(
                'customer' => $customer->id,
                'amount'   => $plan->price * 100,
                'currency' => strtolower(settings('main_settings.stripe_currency'))
            ));

            if ( ! $charge)
                throw new \Exception(trans('front.unexpected_error'));

            $this->updateUser($plan, $user);

        } catch (\Exception $e) {
            throw new \Exception(trans('front.unexpected_error'));
        }

        return redirect(route('subscriptions.renew'))->with(['success' => trans('front.payment_received')]);
    }

    private function updateUser($plan, $user)
    {
        if (strtotime($user->subscription_expiration) > time() && $user->billing_plan_id == $plan->id)
            $date = date('Y-m-d H:i:s', strtotime($user->subscription_expiration." + {$plan->duration_value} {$plan->duration_type}"));
        else
            $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." + {$plan->duration_value} {$plan->duration_type}"));

        $update = [
            'billing_plan_id' => $plan->id,
            'devices_limit' => $plan->objects,
            'subscription_expiration' => $date
        ];

        $this->userRepo->update($user->id, $update);
    }
}
