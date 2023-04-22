<?php

namespace Tobuli\Helpers\SMS;


use Illuminate\Support\Facades\Auth;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Tobuli\Entities\User;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Helpers\SMS\Services\SendSmsPlivo;
use Tobuli\Helpers\SMS\Services\HTTP\SendSmsGET;
use Tobuli\Helpers\SMS\Services\HTTP\SendSmsPOST;
use Tobuli\Helpers\SMS\Services\SendSmsApp;

class SMSGatewayManager
{
    /**
     * @param $user_id
     * @param null $test_args
     * @return mixed
     * @throws ValidationException
     */
    public function loadSender($user_id, $gateway_args = null)
    {
        if (is_null($gateway_args))
            $gateway_args = $this->getGatewayArguments($user_id);

        switch ($gateway_args['request_method']) {
            case 'get':
                $sender = SendSmsGET::class;
                break;
            case 'post':
                $sender = SendSmsPOST::class;
                break;
            case 'plivo':
                $sender = SendSmsPlivo::class;
                break;
            case 'app':
                $sender = SendSmsApp::class;
                break;
            case 'server':
                $settings = settings('sms_gateway');

                if (empty($settings['enabled']))
                    throw new ValidationException(['sender_service' => trans('validation.sms_gateway_error')]);

                return $this->loadSender($user_id, $settings);
            default:
                throw new ValidationException(['sender_service' => trans('validation.sms_gateway_error')]);
        }

        return new $sender($gateway_args);
    }

    /**
     * @param $user_id
     * @param $test_args
     * @return mixed
     */
    private function getGatewayArguments($user_id)
    {
        return $this->getUserGatewayArgs($user_id);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    protected function getUserGatewayArgs($user_id)
    {
        $user = $this->getUser($user_id);

        $gateway_args = $user->sms_gateway_params;
        $gateway_args['sms_gateway_status'] = $user->sms_gateway;
        $gateway_args['sms_gateway_url'] = $user->sms_gateway_url;
        $gateway_args['user_id'] = $user->id;

        return $gateway_args;
    }

    /**
     * @param $user_id
     * @return User
     */
    private function getUser($user_id)
    {
        if ( ! is_null($user_id))
            return User::find($user_id);

        if (auth()->check())
            return Auth::user();

        throw new NotFoundResourceException('No user found loading SMS gateway arguments.');
    }
}