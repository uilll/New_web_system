<?php

namespace ModalHelpers;

use Facades\Repositories\BillingPlanRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\RegistrationFormValidator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tobuli\Entities\EmailTemplate;
use Tobuli\Exceptions\ValidationException;

class RegistrationModalHelper extends ModalHelper
{
    public function create()
    {
        try {
            $permissions = Config::get('tobuli.permissions');
            RegistrationFormValidator::validate('create', $this->data);
            $password = str_random(6);

            $this->data['lang'] = settings('main_settings.default_language');
            $this->data['unit_of_altitude'] = settings('main_settings.default_unit_of_altitude');
            $this->data['unit_of_distance'] = settings('main_settings.default_unit_of_distance');
            $this->data['unit_of_capacity'] = settings('main_settings.default_unit_of_capacity');
            $this->data['timezone_id'] = settings('main_settings.default_timezone');
            $this->data['map_id'] = settings('main_settings.default_map');
            if (! settings('main_settings.enable_plans') || ! settings('main_settings.default_billing_plan')) {
                $expiration_days = settings('main_settings.subscription_expiration_after_days');
                $this->data['subscription_expiration'] = is_null($expiration_days) ? '' : date('Y-m-d H:i:s', strtotime('+'.$expiration_days.' days'));
                $this->data['devices_limit'] = settings('main_settings.devices_limit');
            } else {
                $plan = BillingPlanRepo::find(settings('main_settings.default_billing_plan'));
                $this->data['devices_limit'] = $plan->objects;
                $this->data['billing_plan_id'] = settings('main_settings.default_billing_plan');

                if ($plan->price) {
                    $expiration = date('Y-m-d H:i:s');
                } else {
                    $expiration = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." + {$plan->duration_value} {$plan->duration_type}"));
                }
                $this->data['subscription_expiration'] = $expiration;
            }

            if (settings('main_settings.dst')) {
                $this->data['dst_date_from'] = settings('main_settings.dst_date_from');
                $this->data['dst_date_to'] = settings('main_settings.dst_date_to');
            }

            $this->data['available_maps'] = settings('main_settings.available_maps');
            $this->data['open_device_groups'] = '["0"]';
            $this->data['open_geofence_groups'] = '["0"]';

            $this->data['manager_id'] = null;
            if (Session::has('referer_id')) {
                $user = UserRepo::find(Session::get('referer_id'));
                if (! empty($user) && $user->isManager()) {
                    $this->data['manager_id'] = $user->id;
                }
            }

            $item = UserRepo::create($this->data + [
                'password' => $password,
                'group_id' => 2,
            ]);

            if (! settings('main_settings.enable_plans') || ! settings('main_settings.default_billing_plan')) {
                foreach ($permissions as $key => $val) {
                    DB::table('user_permissions')->insert([
                        'user_id' => $item->id,
                        'name' => $key,
                        'view' => getMainPermission($key, 'view'),
                        'edit' => getMainPermission($key, 'edit'),
                        'remove' => getMainPermission($key, 'remove'),
                    ]);
                }
            }

            $item['password_to_email'] = $password;

            $this->sendRegistrationEmail($item);

            return $this->api ? ['status' => 1, 'message' => trans('front.registration_successful')] : ['status' => 1];
        } catch (ValidationException $e) {
            return $this->api ? ['status' => 0, 'errors' => $e->getErrors()] : ['status' => 0, 'errors' => $e->getErrors()];
        }
    }

    public function sendRegistrationEmail($item)
    {
        $email_template = EmailTemplate::where('name', 'registration')->first();

        App::setLocale(Config::get('app.locale'));

        try {
            sendTemplateEmail($item->email, $email_template, $item);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }
    }
}
