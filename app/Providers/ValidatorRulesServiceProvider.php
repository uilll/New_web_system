<?php namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use Facades\Repositories\DeviceRepo;

class ValidatorRulesServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Validator::extend('same_protocol', function ($attribute, $value, $parameters, $validator) {

            $protocols = DeviceRepo::getProtocols($value)->lists('protocol', 'protocol')->all();

            if (count($protocols) > 1)
                return false;

            return true;
        });

        Validator::extend('contains', function ($attribute, $value, $parameters, $validator) {
            if (!count($parameters) || !strpos($value, $parameters[0]))
                return false;

            return true;
        });

        Validator::replacer('contains', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':value', $parameters[0], $message);
        });

        Validator::extend('key_value_format', function ($attribute, $value, $parameters, $validator) {
            $headers_array = array_filter(explode(';', $value));
            $headers_array = array_map('trim',  $headers_array);

            $pattern = '/^(^.*:.*;?)+$/';
            foreach ($headers_array as $header) {
                if (! preg_match($pattern, $header))
                    return false;
            }

            return true;
        });

        Validator::extend('ip_port', function ($attribute, $value, $parameters, $validator) {

            $parts = explode(':', $value);

            if (count($parts) !== 2)
                return false;

            if (filter_var($parts[0], FILTER_VALIDATE_IP) === false)
                return false;

            if (filter_var($parts[1], FILTER_VALIDATE_INT) === false)
                return false;

            return true;
        });
    }

    public function register()
    {
    }
}