<?php

//require base_path().'/filters.php';
require base_path().'/Tobuli/Helpers/SendSmsHelper.php';
require base_path().'/Tobuli/Helpers/Helper.php';
require base_path().'/Tobuli/Helpers/NavigationHelper.php';
require base_path().'/Tobuli/Helpers/FormHelper.php';
require base_path().'/Tobuli/Helpers/TableHelper.php';
require base_path().'/Tobuli/Helpers/SensorsHelper.php';
require base_path().'/Tobuli/Helpers/UTF8.php';
require base_path().'/Tobuli/Helpers/SettingsHelper.php';

View::addLocation(base_path().'/Tobuli/Views');
View::addNamespace('admin', base_path().'/Tobuli/Views/Admin');
View::addNamespace('front', base_path().'/Tobuli/Views/Frontend');

App::setLocale(settings('main_settings.default_language'));

class CustomRules extends \Illuminate\Validation\Validator
{
    public function validatePhone($attribute, $value, $parameters)
    {
        return preg_match("/^\+\d[0-9]{10}/", $value);
    }

    public function validateArrayMax($attribute, $value, $parameters)
    {
        return count($value) <= $parameters['0'];
    }

    protected function replaceArrayMax($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    protected function validateLesserThan($attribute, $value, $parameters)
    {
        $param = array_get($this->data, $parameters[0]);

        return $value < $param;
    }

    protected function replacelesserThan($message, $attribute, $rule, $parameters)
    {
        return str_replace(':other', trans('validation.attributes.'.$parameters[0]), $message);
    }
}

Validator::resolver(function ($translator, $data, $rules, $messages) {
    return new CustomRules($translator, $data, $rules, $messages);
});

Config::set('tobuli.plans', [
    '1' => trans('front.plan_1'),
    '5' => trans('front.plan_2'),
    '25' => trans('front.plan_3'),
    '29' => trans('front.plan_4'),
]);
$sensors = [
    'satellites' => trans('front.satellites'),
    'gsm' => trans('front.gsm'),
    'engine' => trans('front.engine_on_off'),
    'acc' => trans('front.acc_on_off'),
    'door' => trans('front.door_on_off'),
    'seatbelt' => trans('front.seatbelt_on_off'),
    'battery' => trans('front.battery'),
    'fuel_tank' => trans('front.fuel_tank'),
    'fuel_tank_calibration' => trans('front.fuel_tank_calibration'),
    'temperature' => trans('front.temperature'),
    'temperature_calibration' => trans('front.temperature_calibration'),
    'odometer' => trans('front.odometer'),
    'tachometer' => trans('front.tachometer'),
    'ignition' => trans('front.ignition_on_off'),
    'engine_hours' => trans('validation.attributes.engine_hours'),
    'harsh_acceleration' => trans('front.harsh_acceleration'),
    'harsh_breaking' => trans('front.harsh_breaking'),
    'logical' => trans('front.logical'),
    'numerical' => trans('front.numerical'),
    'textual' => trans('front.textual'),
    'route_color' => trans('front.route_color'),

];
if (settings('plugins.business_private_drive.status')) {
    $sensors['drive_business'] = trans('front.drive_business');
    $sensors['drive_private'] = trans('front.drive_private');
}

if (settings('plugins.route_color.status')) {
    $sensors['route_color'] = trans('front.route_color');
}
Config::set('tobuli.sensors', $sensors);

Config::set('tobuli.units_of_distance', [
    'km' => trans('front.kilometer'),
    'mi' => trans('front.mile'),
]);
Config::set('tobuli.units_of_capacity', [
    'lt' => trans('front.liter'),
    'gl' => trans('front.gallon'),
]);
Config::set('tobuli.units_of_altitude', [
    'mt' => trans('front.meter'),
    'ft' => trans('front.feet'),
]);
Config::set('tobuli.object_online_timeouts', [
    '1' => '1 '.trans('front.minute_short'),
    '2' => '2 '.trans('front.minute_short'),
    '3' => '3 '.trans('front.minute_short'),
    '5' => '5 '.trans('front.minute_short'),
    '6' => '6 '.trans('front.minute_short'),
    '7' => '7 '.trans('front.minute_short'),
    '8' => '8 '.trans('front.minute_short'),
    '9' => '9 '.trans('front.minute_short'),
    '10' => '10 '.trans('front.minute_short'),
    '15' => '15 '.trans('front.minute_short'),
    '30' => '30 '.trans('front.minute_short'),
    '60' => '60 '.trans('front.minute_short'),
    '120' => '120 '.trans('front.minute_short'),
    '180' => '180 '.trans('front.minute_short'),
]);
Config::set('tobuli.stops_minutes', [
    '1' => '> 1 '.trans('front.minute_short'),
    '2' => '> 2 '.trans('front.minute_short'),
    '3' => '> 3 '.trans('front.minute_short'),
    '4' => '> 4 '.trans('front.minute_short'),
    '5' => '> 5 '.trans('front.minute_short'),
    '10' => '> 10 '.trans('front.minute_short'),
    '15' => '> 15 '.trans('front.minute_short'),
    '20' => '> 20 '.trans('front.minute_short'),
    '30' => '> 30 '.trans('front.minute_short'),
    '60' => '> 1 '.trans('front.hour_short'),
    '120' => '> 2 '.trans('front.hour_short'),
    '300' => '> 5 '.trans('front.hour_short'),
]);

Config::set('tobuli.listview_fields_trans', [
    'name' => trans('validation.attributes.name'),
    'imei' => trans('validation.attributes.imei'),
    'status' => trans('validation.attributes.status'),
    'address' => trans('front.address'),
    'protocol' => trans('front.protocol'),
    'position' => trans('front.position'),
    'time' => trans('admin.last_connection'),
    'sim_number' => trans('validation.attributes.sim_number'),
    'device_model' => trans('validation.attributes.device_model'),
    'plate_number' => trans('validation.attributes.plate_number'),
    'vin' => trans('validation.attributes.vin'),
    'registration_number' => trans('validation.attributes.registration_number'),
    'object_owner' => trans('validation.attributes.object_owner'),
    'additional_notes' => trans('validation.attributes.additional_notes'),
    'group' => trans('validation.attributes.group_id'),
    'speed' => trans('front.speed'),
    'fuel' => trans('front.fuel'),
    'route_color' => trans('front.route_color'),
    'stop_duration' => trans('front.stop_duration'),
]);

Config::set('lists.widgets', [
    'device' => trans('front.object'),
    'sensors' => trans('front.sensors'),
    'services' => trans('front.services'),
    'streetview' => trans('front.streetview'),
    'location' => trans('front.location'),
]);

$minutes = [];
for ($i = 0; $i < 16; $i += 1) {
    $minutes[$i] = $i.' '.trans('front.minute_short');
}
for ($i = 15; $i < 65; $i += 5) {
    $minutes[$i] = $i.' '.trans('front.minute_short');
}
Config::set('tobuli.minutes', $minutes);

$format = settings('main_settings.default_time_format') == 'h:i:s A' ? 'h:i A' : 'H:i';
//$format = 'h:i A';
$date = Carbon::createMidnightDate();
$times = [];
for ($i = 0; $i < 96; $i++) {
    $times[$date->format('H:i')] = $date->format($format);
    $date->addMinutes(15);
}
Config::set('tobuli.history_time', $times);
