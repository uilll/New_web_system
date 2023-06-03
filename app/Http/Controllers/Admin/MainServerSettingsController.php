<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Repositories\Config\ConfigRepositoryInterface as Config;
use Tobuli\Repositories\Timezone\TimezoneRepositoryInterface as Timezone;
use Tobuli\Validation\AdminLogoUploadValidator;
use Tobuli\Validation\AdminMainServerSettingsFormValidator;
use Tobuli\Validation\AdminNewUserDefaultsFormValidator;

class MainServerSettingsController extends BaseController
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * @var AdminMainServerSettingsFormValidator
     */
    private $adminMainServerSettingsFormValidator;

    /**
     * @var AdminLogoUploadValidator
     */
    private $adminLogoUploadValidator;

    /**
     * @var AdminNewUserDefaultsFormValidator
     */
    private $adminNewUserDefaultsFormValidator;

    public function __construct(AdminMainServerSettingsFormValidator $adminMainServerSettingsFormValidator, Config $config, Timezone $timezone, AdminLogoUploadValidator $adminLogoUploadValidator, AdminNewUserDefaultsFormValidator $adminNewUserDefaultsFormValidator)
    {
        parent::__construct();
        $this->config = $config;
        $this->timezone = $timezone;
        $this->adminMainServerSettingsFormValidator = $adminMainServerSettingsFormValidator;
        $this->adminLogoUploadValidator = $adminLogoUploadValidator;
        $this->adminNewUserDefaultsFormValidator = $adminNewUserDefaultsFormValidator;
    }

    public function index()
    {
        $settings = settings('main_settings');
        $maps = getMaps();

        $langs = array_sort(settings('languages'), function ($language) {
            return $language['title'];
        });

        $timezones = $this->timezone->order()->pluck('title', 'id')->all();
        $units_of_distance = LaravelConfig::get('tobuli.units_of_distance');
        $units_of_capacity = LaravelConfig::get('tobuli.units_of_capacity');
        $units_of_altitude = LaravelConfig::get('tobuli.units_of_altitude');
        $date_formats = LaravelConfig::get('tobuli.date_formats');
        $time_formats = LaravelConfig::get('tobuli.time_formats');
        $object_online_timeouts = LaravelConfig::get('tobuli.object_online_timeouts');
        $zoom_levels = LaravelConfig::get('tobuli.zoom_levels');

        $geocoder_apis = [
            'default' => trans('front.default'),
            'google' => 'Google API',
            'openstreet' => 'OpenStreet API',
            'geocodio' => 'Geocod.io API',
            'locationiq' => 'LocationIQ API',
            'nominatim' => 'Nominatim',
        ];

        // Is geocoder cache enabled
        $geocoder_cache_status = [
            1 => 'Enabled',
            0 => 'Disabled',
        ];

        // How long to keep geocoder cache
        $days_range = range(5, 180, 5);
        $geocoder_cache_days = array_combine($days_range, $days_range);

        $zoom_levels = [
            '19' => '19', '18' => '18', '17' => '17', '16' => '16', '15' => '15', '14' => '14', '13' => '13', '12' => '12', '11' => '11', '10' => '10', '9' => '9', '8' => '8', '7' => '7', '6' => '6', '5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1', '0' => '0',
        ];

        return View::make('admin::MainServerSettings.index')
            ->with(compact('settings', 'maps', 'langs', 'timezones', 'units_of_distance',
                'units_of_capacity', 'units_of_altitude', 'date_formats', 'time_formats',
                'object_online_timeouts', 'geocoder_apis', 'zoom_levels', 'geocoder_cache_status',
                'geocoder_cache_days'));
    }

    public function save()
    {
        $input = Request::all();

        $settings = settings('main_settings');
        try {
            if ($_ENV['server'] == 'demo') {
                //throw new ValidationException(['id' => trans('front.demo_acc')]);
                $input = array_merge($settings, [
                    'default_maps' => $settings['available_maps'],
                    'geocoder_api' => $input['geocoder_api'],
                    'api_key' => $input['api_key'],

                    'here_map_code' => $input['here_map_code'],
                    'here_map_id' => $input['here_map_id'],
                    'mapbox_access_token' => $input['mapbox_access_token'],
                ]);
            }

            if (in_array(10, $input['default_maps']) || in_array(11, $input['default_maps']) || in_array(12, $input['default_maps'])) {
                if (empty($input['here_map_code'])) {
                    throw new ValidationException(['here_map_code' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.here_map_code')])]);
                }
                if (empty($input['here_map_id'])) {
                    throw new ValidationException(['here_map_id' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.here_map_id')])]);
                }
            }

            if (in_array(14, $input['default_maps']) || in_array(15, $input['default_maps']) || in_array(16, $input['default_maps'])) {
                if (empty($input['mapbox_access_token'])) {
                    throw new ValidationException(['mapbox_access_token' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.mapbox_access_token')])]);
                }
            }

            if (in_array(7, $input['default_maps']) || in_array(8, $input['default_maps']) || in_array(9, $input['default_maps'])) {
                if (empty($input['bing_maps_key'])) {
                    throw new ValidationException(['bing_maps_key' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.bing_maps_key')])]);
                }
            }

            if (($input['geocoder_api'] == 'google' || $input['geocoder_api'] == 'geocodio') && empty($input['api_key'])) {
                throw new ValidationException(['api_key' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.api_key')])]);
            }

            if ($input['geocoder_api'] == 'nominatim' && (empty($input['api_url']) || filter_var($input['api_url'], FILTER_VALIDATE_URL) === false)) {
                throw new ValidationException(['api_url' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.api_url')])]);
            }

            $this->adminMainServerSettingsFormValidator->validate('update', $input);

            if (! in_array($input['default_map'], $input['default_maps'])) {
                throw new ValidationException(['default_map' => trans('front.default_map_must_be')]);
            }

            beginTransaction();
            try {
                $input['available_maps'] = $input['default_maps'];
                unset($input['_token'], $input['default_maps']);
                $settings = array_merge($settings, $input);

                settings('main_settings', $settings);

                DB::table('users')->whereNotIn('map_id', $input['available_maps'])->update(['map_id' => $input['default_map']]);
            } catch (\Exception $e) {
                rollbackTransaction();
                throw new ValidationException(['id' => trans('global.unexpected_db_error')]);
            }

            commitTransaction();

            return Redirect::route('admin.main_server_settings.index')->withSuccess(trans('front.successfully_saved'));
        } catch (ValidationException $e) {
            return Redirect::route('admin.main_server_settings.index')->withInput()->withErrors($e->getErrors());
        }
    }

    public function logoSave()
    {
        $input = Request::all();
        $login_page_logo = Request::file('login_page_logo');
        $frontpage_logo = Request::file('frontpage_logo');
        $favicon = Request::file('favicon');
        $login_page_background = Request::file('background');

        if (Auth::User()->isAdmin()) {
            $base_path = '/var/www/html/images/';
            $front_path = 'images/header/';
            $logo_name = 'logo.';
            $logo_main_name = 'logo-main.';
            $fav_name = 'favicon.ico';
            $background_main_name = 'background.';
        } else {
            $base_path = '/var/www/html/images/logos/';
            $front_path = 'images/header/logos/';
            $logo_name = 'logo-'.Auth::User()->id.'.';
            $logo_main_name = 'logo-main-'.Auth::User()->id.'.';
            $fav_name = 'favicon-'.Auth::User()->id.'.ico';
            $background_main_name = 'background-'.Auth::User()->id.'.';
        }

        try {
            $this->adminLogoUploadValidator->validate('update', $input);

            if (! empty($favicon)) {
                [$width, $height] = getimagesize($favicon->getRealPath());
                if ($height != 16 || $width != 16) {
                    throw new ValidationException(['favicon' => trans('front.favicon_dim')]);
                }

                foreach (glob($base_path.$fav_name) as $filename) {
                    unlink($filename);
                }

                $favicon->move($base_path, $fav_name);
            }

            if (! empty($frontpage_logo)) {
                [$width, $height] = getimagesize($frontpage_logo->getRealPath());
                if ($height > 60) {
                    throw new ValidationException(['frontpage_logo' => trans('front.frontpage_logo_dim')]);
                }

                $extension = strtolower($frontpage_logo->getClientOriginalExtension());
                foreach (glob($base_path.$logo_name.'*') as $filename) {
                    unlink($filename);
                }

                foreach (glob(public_path($front_path.$logo_name)) as $filename) {
                    unlink($filename);
                }

                $frontpage_logo->move($base_path, $logo_name.$extension);
            }

            if (! empty($login_page_logo)) {
                $extension = strtolower($login_page_logo->getClientOriginalExtension());
                foreach (glob($base_path.$logo_main_name.'*') as $filename) {
                    unlink($filename);
                }

                foreach (glob(public_path($front_path.$logo_main_name.'*')) as $filename) {
                    unlink($filename);
                }

                $login_page_logo->move($base_path, $logo_main_name.$extension);
            }

            if (! empty($login_page_background)) {
                $extension = strtolower($login_page_background->getClientOriginalExtension());
                foreach (glob($base_path.$background_main_name.'*') as $filename) {
                    unlink($filename);
                }

                foreach (glob(public_path($front_path.$background_main_name.'*')) as $filename) {
                    unlink($filename);
                }

                $login_page_background->move($base_path, $background_main_name.$extension);
            }

            if (Auth::User()->isAdmin()) {
                $settings = settings('main_settings');

                $settings = array_merge($settings, [
                    'template_color' => $input['template_color'],
                    'login_page_background_color' => $input['login_page_background_color'],
                    'login_page_text_color' => $input['login_page_text_color'],
                    'login_page_panel_background_color' => $input['login_page_panel_background_color'],
                    'login_page_panel_transparency' => $input['login_page_panel_transparency'],
                    'welcome_text' => $input['welcome_text'],
                    'bottom_text' => $input['bottom_text'],
                    'apple_store_link' => $input['apple_store_link'],
                    'google_play_link' => $input['google_play_link'],
                ]);

                settings('main_settings', $settings);
            }

            return Redirect::route('admin.main_server_settings.index')->withSuccess(trans('front.successfully_saved'));
        } catch (ValidationException $e) {
            return Redirect::route('admin.main_server_settings.index')->withLogoErrors($e->getErrors());
        }
    }

    public function newUserDefaultsSave()
    {
        $input = Request::all();

        try {
            if (! isset($input['enable_plans'])) {
                if (isset($input['enable_devices_limit']) && empty($input['devices_limit'])) {
                    throw new ValidationException(['devices_limit' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.devices_limit')])]);
                }

                if (isset($input['enable_subscription_expiration_after_days']) && empty($input['subscription_expiration_after_days'])) {
                    throw new ValidationException(['subscription_expiration_after_days' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.subscription_expiration_after_days')])]);
                }

                $this->adminNewUserDefaultsFormValidator->validate('update', $input);
            } else {
                if (empty($input['default_billing_plan'])) {
                    throw new ValidationException(['default_billing_plan' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.default_billing_plan')])]);
                }
            }

            if (isset($input['dst'])) {
                if (empty($input['dst_date_from'])) {
                    throw new ValidationException(['dst_date_from' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.date_from')])]);
                }

                if (empty($input['dst_date_to'])) {
                    throw new ValidationException(['dst_date_from' => strtr(trans('validation.required'), [':attribute' => trans('validation.attributes.date_to')])]);
                }
            }

            $settings = settings('main_settings');

            $settings['devices_limit'] = ! isset($input['enable_devices_limit']) ? null : $input['devices_limit'];
            $settings['subscription_expiration_after_days'] = ! isset($input['enable_subscription_expiration_after_days']) ? null : $input['subscription_expiration_after_days'];

            $settings['allow_users_registration'] = boolval($input['allow_users_registration']);
            $settings['enable_plans'] = isset($input['enable_plans']);
            $settings['default_billing_plan'] = isset($input['enable_plans']) ? $input['default_billing_plan'] : null;
            $settings['default_timezone'] = $input['default_timezone'];
            $settings['dst'] = isset($input['dst']) && $input['dst'] > 0 ? 1 : null;
            $settings['dst_date_from'] = $input['dst_date_from'];
            $settings['dst_date_to'] = $input['dst_date_to'];

            $settings['user_permissions'] = [];
            if (array_key_exists('perms', $input)) {
                $permissions = LaravelConfig::get('tobuli.permissions');
                foreach ($permissions as $key => $val) {
                    if (! array_key_exists($key, $input['perms'])) {
                        continue;
                    }

                    $settings['user_permissions'][$key] = [
                        'view' => $val['view'] && (array_get($input, "perms.$key.view") || array_get($input, "perms.$key.edit") || array_get($input, "perms.$key.remove")) ? 1 : 0,
                        'edit' => $val['edit'] && array_get($input, "perms.$key.edit") ? 1 : 0,
                        'remove' => $val['remove'] && array_get($input, "perms.$key.remove") ? 1 : 0,
                    ];
                }
            }

            settings('main_settings', $settings);

            updateUsersBillingPlan();

            return Redirect::route('admin.billing.index')->withSuccess(trans('front.successfully_saved'));
        } catch (ValidationException $e) {
            return Redirect::route('admin.billing.index')->withUserDefaultsErrors($e->getErrors());
        }
    }

    /**
     * Deletes (flushes) all geocoder cache
     *
     * @return mixed
     */
    public function deleteGeocoderCache()
    {
        $redirect = Redirect::route('admin.main_server_settings.index');

        try {
            \Facades\GeoLocation::flushCache();
        } catch (\Exception $e) {
            return $redirect->withError(trans('admin.geocoder_cache_flush_fail'));
        }

        return $redirect->withSuccess(trans('admin.geocoder_cache_flush_success'));
    }
}
