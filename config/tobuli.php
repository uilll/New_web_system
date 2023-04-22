<?php
return [
    'version' => '0.3.2.2.9.77', 
    'key'  => env('key', 'Hdaiohaguywhga12344hdsbsdsfsd'),
    'type' => env('APP_TYPE', 'ss3'),

    'logs_path' => env('logs_path', '/opt/traccar/logs'),
    'media_path' => env('media_path', 'images/requestPhoto/'),

    'geocoder_cache_driver' => env('GEOCODER_CACHE_DRIVER', 'sqlite'),

    'main_settings' => [
        'server_name' => env('title', 'GPS Tracker'),
        'available_maps' => [
            1, 4, 5, 2
        ],
        'default_language' => 'en',
        'default_timezone' => 16,
        'default_date_format' => 'Y-m-d',
        'default_time_format' => 'H:i:s',
        'default_unit_of_distance' => 'km',
        'default_unit_of_capacity' => 'lt',
        'default_unit_of_altitude' => 'mt',
        'default_map' => 1,
        'default_object_online_timeout' => 5,
        'allow_users_registration' => 0,

        'devices_limit' => 5,
        'subscription_expiration_after_days' => 30,
        'enable_plans' => 0,
        'payment_type' => 1,
        'paypal_client_id' => '',
        'paypal_secret' => '',
        'paypal_currency' => '',
        'paypal_payment_name' => '',
        'default_billing_plan' => '',
        'stripe_secret_key' => '',
        'stripe_public_key' => '',
        'stripe_currency' => '',
        'dst' => NULL,
        'dst_date_from' => '',
        'dst_date_to' => '',
        'geocoder_api' => 'default',
        'api_key' => '',
        'map_center_latitude' => '51.505',
        'map_center_longitude' => '-0.09',
        'map_zoom_level' => 19,
        'user_permissions' => [],
        'geocoder_cache_enabled' => 1,
        'geocoder_cache_days' => 90,

        'template_color' => 'light-blue',
        'welcome_text' => "BEM VINDO!",
        'bottom_text' => null,
        'apple_store_link' => null,
        'google_play_link' => null,
    ],

  # Minutes before device is offline
    'device_offline_minutes' => 3,
    'check_frequency' => env('APP_CHECK_FREQUENCY', 5),
    #Groups
    'group_admin' => 1,
    'group_client' => 2,
    'alert_zones' => [
        '1' => 'Zone in',
        '2' => 'Zone Out'
    ],
    'alert_fuel_type' => [
        '1' => 'L',
        '2' => 'Gal'
    ],
    'alert_distance' => [
        '1' => 'km',
        '2' => 'mi'
    ],
    'history_time' => [
        '00:00' => '00:00', '00:15' => '00:15', '00:30' => '00:30', '00:45' => '00:45', '01:00' => '01:00', '01:15' => '01:15', '01:30' => '01:30', '01:45' => '01:45',
        '02:00' => '02:00', '02:15' => '02:15', '02:30' => '02:30', '02:45' => '02:45', '03:00' => '03:00', '03:15' => '03:15', '03:30' => '03:30', '03:45' => '03:45',
        '04:00' => '04:00', '04:15' => '04:15', '04:30' => '04:30', '04:45' => '04:45', '05:00' => '05:00', '05:15' => '05:15', '05:30' => '05:30', '05:45' => '05:45',
        '06:00' => '06:00', '06:15' => '06:15', '06:30' => '06:30', '06:45' => '06:45', '07:00' => '07:00', '07:15' => '07:15', '07:30' => '07:30', '07:45' => '07:45',
        '08:00' => '08:00', '08:15' => '08:15', '08:30' => '08:30', '08:45' => '08:45', '09:00' => '09:00', '09:15' => '09:15', '09:30' => '09:30', '09:45' => '09:45',
        '10:00' => '10:00', '10:15' => '10:15', '10:30' => '10:30', '10:45' => '10:45', '11:00' => '11:00', '11:15' => '11:15', '11:30' => '11:30', '11:45' => '11:45',
        '12:00' => '12:00', '12:15' => '12:15', '12:30' => '12:30', '12:45' => '12:45', '13:00' => '13:00', '13:15' => '13:15', '13:30' => '13:30', '13:45' => '13:45',
        '14:00' => '14:00', '14:15' => '14:15', '14:30' => '14:30', '14:45' => '14:45', '15:00' => '15:00', '15:15' => '15:15', '15:30' => '15:30', '15:45' => '15:45',
        '16:00' => '16:00', '16:15' => '16:15', '16:30' => '16:30', '16:45' => '16:45', '17:00' => '17:00', '17:15' => '17:15', '17:30' => '17:30', '17:45' => '17:45',
        '18:00' => '18:00', '18:15' => '18:15', '18:30' => '18:30', '18:45' => '18:45', '19:00' => '19:00', '19:15' => '19:15', '19:30' => '19:30', '19:45' => '19:45',
        '20:00' => '20:00', '20:15' => '20:15', '20:30' => '20:30', '20:45' => '20:45', '21:00' => '21:00', '21:15' => '21:15', '21:30' => '21:30', '21:45' => '21:45',
        '22:00' => '22:00', '22:15' => '22:15', '22:30' => '22:30', '22:45' => '22:45', '23:00' => '23:00', '23:15' => '23:15', '23:30' => '23:30', '23:45' => '23:45'
    ],

    'maps' => [
        'Google Normal' => 1,
        'OpenStreetMap' => 2,
        'Google Hybrid' => 3,
        'Google Satellite' => 4,
        'Google Terrain' => 5,
        'Yandex' => 6,
        'Bing Normal' => 7,
        'Bing Satellite' => 8,
        'Bing Hybrid' => 9,
        'Here Normal' => 10,
        'Here Sattelite' => 11,
        'Here Hybrid' => 12,
        'MapBox Normal' => 14,
        'MapBox Satellite' => 15,
        'MapBox Hybrid' => 16,
    ],
    'frontend' => 'https://www.gpswox.com',
    'frontend_shop' => 'https://www.gpswox.com/en/gps-trackers-shop',
    'frontend_login' => 'https://www.gpswox.com/en/sign-in',
    'frontend_subscriptions' => 'https://www.gpswox.com/en/gps-trackers-shop/all/gps-tracking-and-fleet-management-system-1',
    'frontend_change_password' => 'https://www.gpswox.com/en/change_password?email=',
    'frontend_url' => 'https://www.gpswox.com/addons/shared_addons/themes/gpswox/',
    'frontend_curl' => 'https://www.gpswox.com/api/',
    'frontend_curl_password' => env('FRONTEND_PASSWORD', ''),

    'plans' => [],
    'min_database_clear_days' => 30,
    'max_history_period_days' => env('MAX_HISTORY_PERIOD_DAYS', 31),
    'demos' => [],
    'additional_protocols' => [
        'gpsdata' => 'gpsdata',
        'ios' => 'ios',
        'android' => 'android'
    ],
    'protocols' => [
        'gps103' => 'gps103',
        'tk103' => 'tk103',
        'gl100' => 'gl100',
        'gl200' => 'gl200',
        't55' => 't55',
        'xexun' => 'xexun',
        'totem' => 'totem',
        'enfora' => 'enfora',
        'meiligao' => 'meiligao',
        'maxon' => 'maxon',
        'suntech' => 'suntech',
        'progress' => 'progress',
        'h02' => 'h02',
        'jt600' => 'jt600',
        'ev603' => 'ev603',
        'v680' => 'v680',
        'pt502' => 'pt502',
        'tr20' => 'tr20',
        'navis' => 'navis',
        'meitrack' => 'meitrack',
        'skypatrol' => 'skypatrol',
        'gt02' => 'gt02',
        'gt06' => 'gt06',
        'megastek' => 'megastek',
        'navigil' => 'navigil',
        'gpsgate' => 'gpsgate',
        'teltonika' => 'teltonika',
        'mta6' => 'mta6',
        'mta6can' => 'mta6can',
        'tlt2h' => 'tlt2h',
        'syrus' => 'syrus',
        'wondex' => 'wondex',
        'cellocator' => 'cellocator',
        'galileo' => 'galileo',
        'ywt' => 'ywt',
        'tk102' => 'tk102',
        'intellitrac' => 'intellitrac',
        'xt7' => 'xt7',
        'wialon' => 'wialon',
        'carscop' => 'carscop',
        'apel' => 'apel',
        'manpower' => 'manpower',
        'globalsat' => 'globalsat',
        'atrack' => 'atrack',
        'pt3000' => 'pt3000',
        'ruptela' => 'ruptela',
        'topflytech' => 'topflytech',
        'laipac' => 'laipac',
        'aplicom' => 'aplicom',
        'gotop' => 'gotop',
        'sanav' => 'sanav',
        'gator' => 'gator',
        'noran' => 'noran',
        'm2m' => 'm2m',
        'osmand' => 'osmand',
        'easytrack' => 'easytrack',
        'taip' => 'taip',
        'khd' => 'khd',
        'piligrim' => 'piligrim',
        'stl060' => 'stl060',
        'cartrack' => 'cartrack',
        'minifinder' => 'minifinder',
        'haicom' => 'haicom',
        'eelink' => 'eelink',
        'box' => 'box',
        'freedom' => 'freedom',
        'telik' => 'telik',
        'trackbox' => 'trackbox',
        'visiontek' => 'visiontek',
        'orion' => 'orion',
        'riti' => 'riti',
        'ulbotech' => 'ulbotech',
        'tramigo' => 'tramigo',
        'tr900' => 'tr900',
        'ardi01' => 'ardi01',
        'xt013' => 'xt013',
        'autofon' => 'autofon',
        'gosafe' => 'gosafe',
        'autofon45' => 'autofon45',
        'bce' => 'bce',
        'xirgo' => 'xirgo',
        'calamp' => 'calamp',
        'mtx' => 'mtx',
        'gpsdata' => 'gpsdata'
    ],
    'sensors' => [],
    'units_of_distance' => [],
    'units_of_capacity' => [],
    'units_of_altitude' => [],
    'date_formats' => [
        'Y-m-d' => 'yyyy-mm-dd',
        'm-d-Y' => 'mm-dd-yyyy',
        'd-m-Y' => 'dd-mm-yyyy'
    ],
    'time_formats' => [
        'H:i:s' => '24 hour clock',
        'h:i:s A' => 'AM/PM',
    ],
    'object_online_timeouts' => [],
    'zoom_levels' => [
        '19' => '19', '18' => '18', '17' => '17', '16' => '16', '15' => '15', '14' => '14', '13' => '13', '12' => '12', '11' => '11', '10' => '10', '9' => '9', '8' => '8', '7' => '7', '6' => '6', '5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1', '0' => '0',
    ],
    'permissions' => [
        'devices' => [
            'view' => 1,
            'edit' => 1,
            'remove' => 1,
        ],
        'alerts' => [
            'view' => 1,
            'edit' => 1,
            'remove' => 1,
        ],
        'geofences' => [
            'view' => 1,
            'edit' => 1,
            'remove' => 1,
        ],
        'routes' => [
            'view' => 1,
            'edit' => 1,
            'remove' => 1,
        ],
        'poi' => [
            'view' => 1,
            'edit' => 1,
            'remove' => 1,
        ],
        'reports' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'finances' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'monitoring' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'share_device' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 0,
        ],
        'clients' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'super_admin' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'sms_gateway' => [
            'view' => 0,
            'edit' => 0,
            'remove' => 0,
        ],
        'protocol' => [
            'view' => 0,
            'edit' => 0,
            'remove' => 0,
        ],
        'send_command' => [
            'view' => 1,
            'edit' => 0,
            'remove' => 0,
        ],
        'history' => [
            'view' => 1,
            'edit' => 0,
            'remove' => 1,
        ],
        'maintenance' => [
            'view' => 1,
            'edit' => 0,
            'remove' => 0,
        ],
        'camera' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'tasks' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 1,
        ],
        'chat' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 0,
        ],
        'forward' => [
            'view'  => 1,
            'edit'  => 1,
            'remove'  => 0,
        ]
    ],
    'permissions_modes' => [
        'view' => 1,
        'edit' => 1,
        'remove' => 1
    ],

    'numeric_sensors' => [
        'battery',
        'temperature',
        'temperature_calibration',
        'tachometer',
        'fuel_tank_calibration',
        'fuel_tank',
        'satellites',
        'odometer',
        'gsm',
        'numerical'
    ],
    'listview_fields' => [
        'name' => [
            'field' => 'name',
            'class' => 'device'
        ],
        'imei' => [
            'field' => 'imei',
            'class' => 'device'
        ],
        'status' => [
            'field' => 'status',
            'class' => 'device'
        ],
        'speed' => [
            'field' => 'speed',
            'class' => 'device'
        ],
        'time' => [
            'field' => 'time',
            'class' => 'device'
        ],
        'protocol' => [
            'field' => 'protocol',
            'class' => 'device'
        ],
        'position' => [
            'field' => 'position',
            'class' => 'device'
        ],
        'address' => [
            'field' => 'address',
            'class' => 'device'
        ],
        'sim_number' => [
            'field' => 'sim_number',
            'class' => 'device'
        ],
        'device_model' => [
            'field' => 'device_model',
            'class' => 'device'
        ],
        'plate_number' => [
            'field' => 'plate_number',
            'class' => 'device'
        ],
        'vin' => [
            'field' => 'vin',
            'class' => 'device'
        ],
        'registration_number' => [
            'field' => 'registration_number',
            'class' => 'device'
        ],
        'object_owner' => [
            'field' => 'object_owner',
            'class' => 'device'
        ],
        'group' => [
            'field' => 'group',
            'class' => 'device'
        ],
        'fuel' => [
            'field' => 'fuel',
            'class' => 'device'
        ],
        'stop_duration' => [
            'field' => 'stop_duration',
            'class' => 'device'
        ],
    ],
    'listview' => [
        'groupby' => 'protocol',
        'columns' => [
            'name' => [
                'field' => 'name',
                'class' => 'device'
            ],
            'status' => [
                'field' => 'status',
                'class' => 'device'
            ],
            'time' => [
                'field' => 'time',
                'class' => 'device'
            ],
            'position' => [
                'field' => 'position',
                'class' => 'device'
            ]
        ]
    ],

    'plugins' => [
        'show_object_info_after' => [
            'status' => 0,
        ],
        'object_listview' => [
            'status' => 0,
        ],
        'business_private_drive' => [
            'status' => 0,
            'options' => [
                'business_color' => [
                    'value' => 'blue'
                ],
                'private_color' => [
                    'value' => 'red'
                ]
            ]
        ],
        'route_color' => [
            'status' => 0,
            'options' => [
                'value' => 'orange'
            ]
        ],
        'birla_report' => [
            'status' => 0,
        ],
        'object_history_report' => [
            'status' => 0,
        ],
        'automon_report' => [
            'status' => 0,
        ],
    ],

    'process' => [
        'insert_timeout' => env('PROC_INSERT_TIMEOUT', 60),
        'insert_limit' => env('PROC_INSERT_LIMIT', 10),
        'reportdaily_timeout' => env('PROC_REPORT_TIMEOUT', 180),
        'reportdaily_limit' => env('PROC_REPORT_LIMIT', 2),
    ],

    'template_colors' => [
        'light-blue'        => 'Light Blue',
        'light-green'       => 'Light Green',
        'light-red'         => 'Light Red',
        'light-orange'      => 'Light Orange',
        'light-pink'        => 'Light Pink',
        'light-win10-blue'  => 'Light Win10 Blue',
        'light-black'       => 'Light Black',
        'dark-blue'         => 'Dark Blue',
        'dark-green'        => 'Dark Green',
        'dark-red'          => 'Dark Red',
        'dark-orange'       => 'Dark Orange',
        'dark-pink'         => 'Dark Pink',
        'dark-win10-blue'   => 'Dark Win10 Blue',
    ],

    'widgets' => [
        'default' => true,
        'status' => true,
        'list' => [
            'device', 'sensors', 'services'
        ]
    ],

    'db_clear' => [
        'status' => true,
        'days' => 90
    ],

    'limits' => [
        'alert_phones'   => env('LIMIT_ALERT_PHONES', 5),
        'alert_emails'   => env('LIMIT_ALERT_EMAILS', 5),
        'alert_webhooks' => env('LIMIT_ALERT_WEBHOOKS', 2),
    ],

    'languages' => [
        'en' => [
            'key'    => 'en',
            'iso'    => 'en',
            'title'  => 'English(USA)',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'en.png',
        ],
        'au' => [
            'key'    => 'au',
            'iso'    => 'en',
            'title'  => 'Australian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'au.png'
        ],
        'az' => [
            'key'    => 'az',
            'iso'    => 'az',
            'title'  => 'Azerbaijan',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'az.png'
        ],
        'ar' => [
            'key'    => 'ar',
            'iso'    => 'ar',
            'title'  => 'Arabic',
            'active' => true,
            'dir'    => 'rtl',
            'flag'   => 'ar.png'
        ],
        'sk' => [
            'key'    => 'sk',
            'iso'    => 'sk',
            'title'  => 'Slovakian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'sk.png'
        ],
        'th' => [
            'key'    => 'th',
            'iso'    => 'th',
            'title'  => 'Thai',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'th.png'
        ],
        'nl' => [
            'key'    => 'nl',
            'iso'    => 'nl',
            'title'  => 'Dutch',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'nl.png'
        ],
        'de' => [
            'key'    => 'de',
            'iso'    => 'de',
            'title'  => 'German',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'de.png'
        ],
        'gr' => [
            'key'    => 'gr',
            'iso'    => 'el',
            'title'  => 'Greek',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'gr.png'
        ],
        'pl' => [
            'key'    => 'pl',
            'iso'    => 'pl',
            'title'  => 'Polish',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'pl.png'
        ],
        'uk' => [
            'key'    => 'uk',
            'iso'    => 'gb',
            'title'  => 'English(UK)',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'uk.png'
        ],
        'fr' => [
            'key'    => 'fr',
            'iso'    => 'fr',
            'title'  => 'French',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'fr.png'
        ],
        'br' => [
            'key'    => 'br',
            'iso'    => 'pt',
            'title'  => 'Brazilian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'br.png'
        ],
        'pt' => [
            'key'    => 'pt',
            'iso'    => 'pt',
            'title'  => 'Portuguese',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'pt.png'
        ],
        'es' => [
            'key'    => 'es',
            'iso'    => 'es',
            'title'  => 'Spanish',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'es.png'
        ],
        'it' => [
            'key'    => 'it',
            'iso'    => 'it',
            'title'  => 'Italian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'it.png'
        ],
        'ch' => [
            'key'    => 'ch',
            'iso'    => 'es',
            'title'  => 'Chile',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'ch.png'
        ],
        'sr' => [
            'key'    => 'sr',
            'iso'    => 'sr',
            'title'  => 'Serbian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'sr.png'
        ],
        'fi' => [
            'key'    => 'fi',
            'iso'    => 'fi',
            'title'  => 'Finnish',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'fi.png'
        ],
        'dk' => [
            'key'    => 'dk',
            'iso'    => 'dk',
            'title'  => 'Danish',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'dk.png'
        ],
        'ph' => [
            'key'    => 'ph',
            'iso'    => 'en',
            'title'  => 'Philippines',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'ph.png'
        ],
        'sv' => [
            'key'    => 'sv',
            'iso'    => 'sv',
            'title'  => 'Swedish',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'sv.png'
        ],
        'ro' => [
            'key'    => 'ro',
            'iso'    => 'ro',
            'title'  => 'Romanian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'ro.png'
        ],
        'bg' => [
            'key'    => 'bg',
            'iso'    => 'bg',
            'title'  => 'Bulgarian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'bg.png'
        ],
        'hr' => [
            'key'    => 'hr',
            'iso'    => 'hr',
            'title'  => 'Croatian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'hr.png'
        ],
        'cw' => [
            'key'    => 'cw',
            'iso'    => 'pt',
            'title'  => 'Papiamento',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'cw.png'
        ],
        'id' => [
            'key'    => 'id',
            'iso'    => 'id',
            'title'  => 'Indonesian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'id.png'
        ],
        'ru' => [
            'key'    => 'ru',
            'iso'    => 'ru',
            'title'  => 'Russian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'ru.png'
        ],
        'mk' => [
            'key'    => 'mk',
            'iso'    => 'mk',
            'title'  => 'Macedonian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'mk.png'
        ],
        'ir' => [
            'key'    => 'ir',
            'iso'    => 'fa',
            'title'  => 'Persian',
            'active' => true,
            'dir'    => 'rtl',
            'flag'   => 'ir.png'
        ],
        'cn' => [
            'key'    => 'cn',
            'iso'    => 'zh',
            'title'  => 'Chinese',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'cn.png'
        ],
        'nz' => [
            'key'    => 'nz',
            'iso'    => 'en',
            'title'  => 'New Zealand',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'nz.png'
        ],
        'cz' => [
            'key'    => 'cz',
            'iso'    => 'cs',
            'title'  => 'Czech',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'cz.png'
        ],
        'he' => [
            'key'    => 'he',
            'iso'    => 'he',
            'title'  => 'Hebrew',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'il.png'
        ],
        'hu' => [
            'key'    => 'hu',
            'iso'    => 'hu',
            'title'  => 'Hungarian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'hu.png'
        ],
        'ka' => [
            'key'    => 'ka',
            'iso'    => 'ka',
            'title'  => 'Georgian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'ka.png'
        ],
        'no' => [
            'key'    => 'no',
            'iso'    => 'no',
            'title'  => 'Norwegian',
            'active' => true,
            'dir'    => 'ltr',
            'flag'   => 'no.png'
        ],
    ],

    'sms_gateway' => [
        'enabled'         => false,
        'request_method'  => '',
        'sms_gateway_url' => '',
        'custom_headers'  => '',
        'authentication'  => '0',
        'username'        => '',
        'password'        => '',
        'encoding'        => '',
        'auth_id'         => '',
        'auth_token'      => '',
        'senders_phone'   => '',
        'user_id' => null
    ],

    'object_delete_pass' => env('object_delete_pass'),

    'backups' => [
        'type'         => 'auto',
        'period'       => 1,
        'hour'         => '00:00',
        'ftp_server'   => null,
        'ftp_username' => null,
        'ftp_password' => null,
        'ftp_port'     => null,
        'ftp_path'     => null,
    ]
];