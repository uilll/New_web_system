<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Html\FormFacade as Form;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Repositories\BillingPlan\BillingPlanRepositoryInterface as BillingPlan;
use Tobuli\Repositories\Config\ConfigRepositoryInterface as Config;
use Tobuli\Repositories\Timezone\TimezoneRepositoryInterface as Timezone;
use Tobuli\Validation\AdminBillingGatewayFormValidator;
use Tobuli\Validation\AdminBillingPlanFormValidator;

class BillingController extends BaseController
{
    private $payment_types = [
        'paypal' => 'Paypal',
        'stripe' => 'Stripe',
    ];

    public function index(BillingPlan $billingPlanRepo, Timezone $timezoneRepo)
    {
        $items = $billingPlanRepo->getWhere([], 'objects', 'asc');

        $settings = settings('main_settings');

        $timezones = $timezoneRepo->order()->pluck('title', 'id')->all();
        $payment_types = $this->payment_types;

        $perms = LaravelConfig::get('tobuli.permissions');

        return view('admin::Billing.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items', 'timezones', 'settings', 'payment_types', 'perms'));
    }

    public function store(Config $configRepo, AdminBillingGatewayFormValidator $adminBillingGatewayFormValidator)
    {
        $input = Request::all();

        try {
            $adminBillingGatewayFormValidator->validate('update', $input);

            beginTransaction();
            try {
                $settings = settings('main_settings');

                $settings = array_merge($settings, array_only($input, [
                    'payment_type',
                    'paypal_client_id',
                    'paypal_secret',
                    'paypal_currency',
                    'paypal_payment_name',
                    'stripe_secret_key',
                    'stripe_public_key',
                    'stripe_currency',
                ]));

                settings('main_settings', $settings);
            } catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
            }
            commitTransaction();
        } catch (ValidationException $e) {
            return Redirect::route('admin.billing.index')->withInput()->withBillingErrors($e->getErrors());
        }

        return Redirect::route('admin.billing.index')->withBillingSuccess(trans('front.successfully_saved'));
    }

    public function create()
    {
        $duration_types = [
            'days' => trans('front.days'),
            'months' => trans('front.months'),
            'years' => trans('front.years'),
        ];

        $perms = LaravelConfig::get('tobuli.permissions');

        return view('admin::Billing.create')->with(compact('duration_types', 'perms'));
    }

    public function planStore(BillingPlan $billingPlanRepo, AdminBillingPlanFormValidator $adminBillingPlanFormValidator)
    {
        $input = Request::all();
        $permissions = LaravelConfig::get('tobuli.permissions');

        try {
            $adminBillingPlanFormValidator->validate('create', $input);

            beginTransaction();
            try {
                $plan = $billingPlanRepo->create($input);

                if (array_key_exists('perms', $input)) {
                    foreach ($permissions as $key => $val) {
                        if (! array_key_exists($key, $input['perms'])) {
                            continue;
                        }

                        DB::table('billing_plan_permissions')->insert([
                            'plan_id' => $plan->id,
                            'name' => $key,
                            'view' => $val['view'] && (array_get($input, "perms.$key.view") || array_get($input, "perms.$key.edit") || array_get($input, "perms.$key.remove")) ? 1 : 0,
                            'edit' => $val['edit'] && array_get($input, "perms.$key.edit") ? 1 : 0,
                            'remove' => $val['remove'] && array_get($input, "perms.$key.remove") ? 1 : 0,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
            }
            commitTransaction();
        } catch (ValidationException $e) {
            return response()->json(['status' => 0, 'errors' => $e->getErrors()]);
        }

        return response()->json(['status' => 1]);
    }

    public function edit($id, BillingPlan $billingPlanRepo)
    {
        $item = $billingPlanRepo->find($id);
        if (empty($item)) {
            return modalError(dontExist('validation.attributes.plan'));
        }

        $duration_types = [
            'days' => trans('front.days'),
            'months' => trans('front.months'),
            'years' => trans('front.years'),
        ];

        $perms = LaravelConfig::get('tobuli.permissions');

        return view('admin::Billing.edit')->with(compact('item', 'duration_types', 'perms'));
    }

    public function update(BillingPlan $billingPlanRepo, AdminBillingPlanFormValidator $adminBillingPlanFormValidator)
    {
        $input = Request::all();
        $permissions = LaravelConfig::get('tobuli.permissions');

        try {
            $adminBillingPlanFormValidator->validate('create', $input);

            beginTransaction();
            try {
                $billingPlanRepo->update($input['id'], $input);

                DB::table('billing_plan_permissions')->where('plan_id', '=', $input['id'])->delete();
                if (array_key_exists('perms', $input)) {
                    foreach ($permissions as $key => $val) {
                        if (! array_key_exists($key, $input['perms'])) {
                            continue;
                        }

                        DB::table('billing_plan_permissions')->insert([
                            'plan_id' => $input['id'],
                            'name' => $key,
                            'view' => $val['view'] && (array_get($input, "perms.$key.view") || array_get($input, "perms.$key.edit") || array_get($input, "perms.$key.remove")) ? 1 : 0,
                            'edit' => $val['edit'] && array_get($input, "perms.$key.edit") ? 1 : 0,
                            'remove' => $val['remove'] && array_get($input, "perms.$key.remove") ? 1 : 0,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
            }
            commitTransaction();
        } catch (ValidationException $e) {
            return response()->json(['status' => 0, 'errors' => $e->getErrors()]);
        }

        return response()->json(['status' => 1]);
    }

    public function plans(BillingPlan $billingPlanRepo)
    {
        $items = $billingPlanRepo->getWhere([], 'objects', 'asc');

        return view('admin::Billing.table')->with(compact('items'));
    }

    public function billingPlansForm(BillingPlan $billingPlanRepo)
    {
        $items = $billingPlanRepo->all()->pluck('title', 'id')->all();

        return Form::select('default_billing_plan', $items, settings('main_settings.default_billing_plan'), ['class' => 'form-control']);
    }

    public function destroy(BillingPlan $billingPlanRepo)
    {
        $input = Request::all();
        if (! isset($input['id'])) {
            return response()->json(['status' => 0]);
        }

        $ids = $input['id'];

        $settings = settings('main_settings');
        if (settings('main_settings.enable_plans')) {
            foreach ($ids as $key => $val) {
                if ($settings['default_billing_plan'] == $val) {
                    unset($ids[$key]);
                    break;
                }
            }
        }

        $billingPlanRepo->deleteWhereIn($ids);

        if (settings('main_settings.enable_plans')) {
            updateUsersBillingPlan();
        }

        return response()->json(['status' => 1]);
    }
}
