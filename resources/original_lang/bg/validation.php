<?php

return array(

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'between'  => array(
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ),
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'max'  => array(
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ),
    'mimes' => 'The :attribute must be a file of type: :values.',
    'min'  => array(
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ),
    'not_in' => 'The selected :attribute is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values is present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size'  => array(
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ),
    'unique' => 'The :attribute has already been taken.',
    'url' => 'The :attribute format is invalid.',
    'array_max' => 'The :attribute max items :max.',
    'lesser_than' => 'The :attribute must be lesser than :other',
    'custom'  => array(
        'attribute-name'  => array(
            'rule-name' => 'custom-message',
        ),
    ),
    'attributes'  => array(
        'email' => 'Email',
        'password' => 'Парола',
        'password_confirmation' => 'Потвърждение на паролата',
        'remember_me' => 'Запомни ме',
        'name' => 'Име',
        'imei' => 'IMEI',
        'imei_device' => 'IMEI или име на устройството',
        'fuel_measurement_type' => 'Измерване на горивото',
        'fuel_cost' => 'Разходи за горива',
        'icon_id' => 'Символ на утройството',
        'active' => 'Активен',
        'polygon_color' => 'Цвят на фона',
        'devices' => 'Устройства',
        'geofences' => 'Геозони',
        'overspeed' => 'Превишена скорост',
        'fuel_consumption' => 'Разход на гориво',
        'description' => 'Описание',
        'map_icon_id' => 'Marker icon',
        'coordinates' => 'Map point',
        'date_from' => 'От дата',
        'date_to' => 'До дата',
        'code' => 'Код',
        'title' => 'Название',
        'note' => 'Съдържание',
        'path' => 'Файл',
        'period_name' => 'Име на периода',
        'days' => 'Дни',
        'devices_limit' => 'Лимит на устройствата',
        'trial' => 'Пробен период',
        'price' => 'Цена',
        'message' => 'Съобщение',
        'tag' => 'Параметър',
        'timezone_id' => 'Часова зона',
        'unit_of_distance' => 'Мерна единица за разстояние',
        'unit_of_capacity' => 'Мерна единица за обем',
        'unit_of_altitude' => 'Мерна единица за височина',
        'user' => 'Потребител',
        'group_id' => 'Група',
        'permission_to_add_devices' => 'Добавяне на устройство',
        'sms_gateway_url' => 'URL адрес на SMS портала',
        'mobile_phone' => 'Мобилен телефон',
        'permission_to_use_sms_gateway' => 'SMS портал',
        'loged_at' => 'Last login',
        'manager_id' => 'Manager',
        'sim_number' => 'SIM тел. номер',
        'device_model' => 'Устройство модел',
        'rfid' => 'RFID',
        'phone' => 'Телефон',
        'device_id' => 'Устройство',
        'tag_value' => 'Parameter value',
        'device_port' => 'Device port',
        'event' => 'Събитие',
        'port' => 'Port',
        'device_protocol' => 'Device protocol',
        'protocol' => 'Протокол',
        'sensor_name' => 'Име на сензора',
        'sensor_type' => 'Тип на сензора',
        'sensor_template' => 'Сензор шаблони',
        'tag_name' => 'Име на параметъра',
        'min_value' => 'Мин. стойност',
        'max_value' => 'Макс. стойност',
        'on_value' => 'Стойност на ВКЛ',
        'off_value' => 'Стойност на ИЗКЛ',
        'shown_value_by' => 'Покажи стойността от',
        'full_tank_value' => 'Стойност на параметъра',
        'formula' => 'Формула',
        'parameters' => 'Параметри',
        'full_tank' => 'Пълен резервоар в литри/галони',
        'fuel_tank_name' => 'Име на резервоара',
        'odometer_value' => 'Стойност',
        'odometer_value_by' => 'Километраж',
        'unit_of_measurement' => 'Мерни единици',
        'plate_number' => 'Рег. номер',
        'vin' => 'VIN',
        'registration_number' => 'Регистрационен номер',
        'object_owner' => 'Собственик/отговорник на обекта',
        'expiration_date' => 'Дата на изтичане',
        'days_to_remind' => 'Колко дни преди изтичането да Ви напомни?',
        'type' => 'Тип',
        'format' => 'Формат',
        'show_addresses' => 'Показване на адресите',
        'stops' => 'Спирки',
        'speed_limit' => 'Ограничение на скоростта',
        'zones_instead' => 'Локация вместо адреси',
        'daily' => 'Дневен',
        'weekly' => 'Седмичен',
        'send_to_email' => 'Изпращане на мейл',
        'filter' => 'Филтър',
        'status' => 'Статус',
        'date' => 'Дата',
        'geofence_name' => 'Име на геозона',
        'tail_color' => 'Цвят на следата',
        'tail_length' => 'Дължина на следата',
        'engine_hours' => 'Моточасове',
        'detect_engine' => 'Установява ВКЛ/ИЗКЛ на двигателя по: ',
        'min_moving_speed' => 'Мин. скорост на движение в км/ч',
        'min_fuel_fillings' => 'Мин. разлика в горивото за засичане на зареждане',
        'min_fuel_thefts' => 'Мин. разлика в горивото за засичане на кражба',
        'expiration_by' => 'Изтича на',
        'interval' => 'Интервал',
        'last_service' => 'Последен сервиз',
        'trigger_event_left' => 'Задействане на събитие когато остават',
        'current_odometer' => 'Текущ километраж',
        'current_engine_hours' => 'Текущи моточасове',
        'renew_after_expiration' => 'Обнови след изтичане',
        'sms_template_id' => 'SMS шаблон',
        'frequency' => 'Честота',
        'unit' => 'Единица',
        'noreply_email' => 'No reply email address',
        'signature' => 'Подпис',
        'use_smtp_server' => 'Използвай SMTP сървър',
        'smtp_server_host' => 'SMTP сървър host',
        'smtp_server_port' => 'SMTP сървър port',
        'smtp_security' => 'SMTP security',
        'smtp_username' => 'SMTP потребителско име',
        'smtp_password' => 'SMTP парола',
        'from_name' => 'От име',
        'icons' => 'Икони',
        'server_name' => 'Име на сървъра',
        'available_maps' => 'Налични карти',
        'default_language' => 'Език по подразбиране',
        'default_timezone' => 'Часова зона по подразбиране',
        'default_unit_of_distance' => 'Единици за разстояние по подразбиране',
        'default_unit_of_capacity' => 'Единици за обем по подразбиране',
        'default_unit_of_altitude' => 'Единици за височина по подразбиране',
        'default_date_format' => 'Формат на датата по подразбиране',
        'default_time_format' => 'Формат на часа по подразбиране',
        'default_map' => 'Карта по подразбиране',
        'default_object_online_timeout' => 'Време за показване "на линия" по подразбиране',
        'logo' => 'Лого',
        'login_page_logo' => 'Лого на страницата за влизане',
        'frontpage_logo' => 'Лого на началната страница',
        'favicon' => 'Favicon',
        'allow_users_registration' => 'Позволи регистрация на потребители',
        'frontpage_logo_padding_top' => 'Подложка на логото на началната страница',
        'default_maps' => 'Карти по подразбиране',
        'subscription_expiration_after_days' => 'Оставащи дни до изтичане на абонамента',
        'gprs_template_id' => 'GPRS шаблон',
        'calibrations' => 'Калибриране',
        'ftp_server' => 'FTP Сървър',
        'ftp_port' => 'FTP порт',
        'ftp_username' => 'FTP потребител',
        'ftp_password' => 'FTP парола',
        'ftp_path' => 'FTP адрес',
        'period' => 'Период',
        'hour' => 'Час',
        'color' => 'Цвят',
        'polyline' => 'Маршрут',
        'request_method' => 'Метод на заявката',
        'authentication' => 'Идентификация',
        'username' => 'Потребител',
        'encoding' => 'Кодиране',
        'time_adjustment' => 'Настройка на времето',
        'parameter' => 'Параметър',
        'export_type' => 'Тип експорт',
        'groups' => 'Групи',
        'file' => 'File',
        'extra' => 'Extra',
        'parameter_value' => 'Стойност на параметъра',
        'enable_plans' => 'Активиране на планове',
        'payment_type' => 'Вид плащане',
        'paypal_client_id' => 'Paypal client ID',
        'paypal_secret' => 'Paypal secret',
        'paypal_currency' => 'Paypal currency',
        'paypal_payment_name' => 'Paypal payment name',
        'objects' => 'Обекти',
        'duration_value' => 'Времетраене',
        'permissions' => 'Разрешения',
        'plan' => 'План',
        'default_billing_plan' => 'План за таксуване по подразбиране',
        'sensor_group_id' => 'Група сензори',
        'daylight_saving_time' => 'Лятно часово време',
        'phone_number' => 'Телефонен номер',
        'action' => 'Действие',
        'time' => 'Време',
        'order' => 'Поръчка',
        'geocoder_api' => 'Geocoder API',
        'geocoder_cache' => 'Geocoder cache',
        'geocoder_cache_days' => 'Geocoder cache days',
        'geocoder_cache_delete' => 'Delete geocoder cache',
        'api_key' => 'API key',
        'map_center_latitude' => 'Географска ширина на центъра на картата',
        'map_center_longitude' => 'Географска дължина на центъра на картата',
        'map_zoom_level' => 'Ниво на мащабиране на картата',
        'dst_type' => 'Тип',
        'provider' => 'Доставчик',
        'week_start_day' => 'Начален ден на седмицата по подразбиране',
        'ip' => 'IP',
        'gprs_templates_only' => 'Показвай само шаблоните за GPRS команди',
        'select_all_objects' => 'Избери всички обекти',
        'icon_type' => 'Тип символ',
        'on_setflag_1' => 'Начален символ',
        'on_setflag_2' => 'Брой символи',
        'on_setflag_3' => 'Стойност на параметъра',
        'domain' => 'Domain',
        'auth_id' => 'Auth ID',
        'auth_token' => 'Auth token',
        'senders_phone' => 'Телефонен номер на подателя',
        'database_clear_status' => 'Автоматично изтриване на данните на историята',
        'database_clear_days' => 'Съхранение - бр. дни',
        'ignition_detection' => 'Запалването на двигателя се определя по',
        'template_color' => 'Цвят на шаблона',
        'background' => 'Заден план',
        'login_page_text_color' => 'Цвят на текста на страницата за вход',
        'login_page_background_color' => 'Входния цвят на фона на страницата',
        'welcome_text' => 'Добре дошли текст',
        'bottom_text' => 'Долен текст',
        'apple_store_link' => 'Връзка към магазина на Apple',
        'google_play_link' => 'Връзка за Google Play',
        'here_map_id' => 'HERE.com app ID',
        'here_map_code' => 'HERE.com app code',
        'login_page_panel_background_color' => 'Входния цвят на фона на панела на страницата',
        'login_page_panel_transparency' => 'Прозрачност на панела за вход в страницата',
        'visible' => 'видим',
        'additional_notes' => 'Допълнителни бележки',
        'api_url' => 'API URL адрес',
        'position' => 'позиция',
        'stop_duration_longer_than' => 'Спиране на продължителността повече от',
        'mapbox_access_token' => 'Доказателство за достъп до MapBox',
        'flag' => 'флаг',
        'shift_start' => 'Старт на превключването',
        'shift_finish' => 'Завършване на преместването',
        'shift_start_tolerance' => 'Толеранс на началото на превключването',
        'shift_finish_tolerance' => 'Трансферен толеранс',
        'excessive_exit' => 'Прекомерно излизане',
        'smtp_authentication' => 'SMTP удостоверяване',
        'skip_calibration' => 'Изключете изчисленията от диапазона на калибриране',
        'bing_maps_key' => 'Bing карта ключ',
        'stripe_public_key' => 'Публичен ключ STRIPE',
        'stripe_secret_key' => 'Скрий ключ',
        'stripe_currency' => 'Мрежа STRIPE',
        'priority' => 'приоритет',
        'pickup_address' => 'Адрес за приемане',
        'delivery_address' => 'Адрес за доставка',
        'schedule' => 'разписание',
        'sound_notification' => 'Звукова нотификация',
        'push_notification' => 'Известие',
        'email_notification' => 'Известие по имейл',
        'sms_notification' => 'SMS известяване',
        'webhook_notification' => 'Известие за Webhook',
        'offline_duration_longer_than' => 'Продължителността на връзката е по-дълга от',
        'sms_gateway_headers' => 'Ключове за SMS шлюзове',
        'forward' => 'напред',
        'by_status' => 'По статут',
        'icon_status_online' => 'Икона за онлайн състояние',
        'icon_status_offline' => 'Икона за статус офлайн',
        'icon_status_ack' => 'Икона на състоянието Ack',
        'icon_status_engine' => 'Икона за състоянието на двигателя',
    ),
    'same_protocol' => 'Устройствата трябва да са от същия протокол.',
    'contains' => ':attribute трябва да съдържа :value .',
    'ip_port' => ':attribute не съответства на формата IP:PORT',
);