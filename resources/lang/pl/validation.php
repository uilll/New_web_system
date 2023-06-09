<?php

return array(

    'accepted' => ':attribute musi być zaakceptowany.',
    'active_url' => ':attribute nie jest poprawnym adresem URL.',
    'after' => ':attribute musi być datą po :date.',
    'alpha' => ':attribute może składać się tylko z liter.',
    'alpha_dash' => ':attribute może składać się tylko z liter, cyfr lub kresek.',
    'alpha_num' => ':attribute może składać się tylko z liter i cyfr.',
    'array' => ':attribute musi być tabelą.',
    'before' => ':attribute musi być datą przed :date.',
    'between'  => array(
        'numeric' => ':attribute musi być między :min i :max.',
        'file' => ':attribute musi być między :min i :max kilobajtów.',
        'string' => ':attribute musi być między :min i :max znaków.',
        'array' => ':attribute musi być między :min i :max elementów.',
    ),
    'confirmed' => ':attribute nie jest zgodny.',
    'date' => ':attribute nie jest prawidłową datą.',
    'date_format' => ':attribute nie jest zgodna z formatem :format.',
    'different' => ':attribute i :other muszą być różne.',
    'digits' => ':attribute musi mieć :digits cyfr.',
    'digits_between' => ':attribute musi mieć między :min i :max cyfr.',
    'email' => ':attribute musi być poprawnym adresem email.',
    'exists' => 'wybrany :attribute jest nieprawidłowy.',
    'image' => ':attribute musi być obrazem.',
    'in' => 'wybrany :attribute jest nieprawidłowy.',
    'integer' => ':attribute musi być liczbą całkowitą.',
    'ip' => ':attribute musi być poprawnym adresem IP.',
    'max'  => array(
        'numeric' => ':attribute nie może być większy niż :max.',
        'file' => ':attribute nie może być większy niż :max kilobajtów.',
        'string' => ':attribute nie może być dłuższy niż :max znaków.',
        'array' => ':attribute nie może mieć więcej niż :max elementów.',
    ),
    'mimes' => ':attribute musi być plikiem typu: :values.',
    'min'  => array(
        'numeric' => ':attribute musi być przynajmniej :min.',
        'file' => ':attribute musi mieć przynajmniej :min kilobajtów.',
        'string' => ':attribute musi mieć przynajmniej :min znaków.',
        'array' => ':attribute musi mieć przynajmniej :min elementów.',
    ),
    'not_in' => 'Wybrany :attribute jest nieprawidłowy.',
    'numeric' => ':attribute musi być liczbą.',
    'regex' => ':attribute format jest nieprawidłowy.',
    'required' => 'Pole :attribute jest wymagane.',
    'required_if' => 'Pole :attribute jest wymagane.',
    'required_with' => 'Pole :attribute jest wymagane jeśli wypełnione jest :values .',
    'required_with_all' => 'Pole :attribute jest wymagane jeśli wypełnione są :values.',
    'required_without' => 'Pole :attribute jest wymagane jeśli nie jest wypełnione :values.',
    'required_without_all' => 'Pole :attribute jest wymagane jeśli nie są wypełnione :values.',
    'same' => ':attribute i :other muszą być zgodne.',
    'size'  => array(
        'numeric' => ':attribute musi mieć rozmiar :size.',
        'file' => ':attribute musi mieć rozmiar :size kilobajtów.',
        'string' => ':attribute musi mieć :size znaków.',
        'array' => ':attribute musi zawierać :size elementów.',
    ),
    'unique' => ':attribute jest już użyty.',
    'url' => 'Format :attribute jest nieprawidłowy.',
    'array_max' => 'Maksymalna ilość elementów :attribute to :max.',
    'lesser_than' => ':attribute musi być mniejsza niż :other',
    'custom'  => array(
        'attribute-name'  => array(
            'rule-name' => 'custom-message',
        ),
    ),
    'attributes'  => array(
        'email' => 'E-mail',
        'password' => 'Hasło',
        'password_confirmation' => 'Potwierdzenie hasła',
        'remember_me' => 'Zapamiętaj mnie',
        'name' => 'Nazwa',
        'imei' => 'IMEI',
        'imei_device' => 'IMEI lub identyfikator urządzenia',
        'fuel_measurement_type' => 'Pomiar paliwa',
        'fuel_cost' => 'Koszt paliwa',
        'icon_id' => 'Ikona urządzenia',
        'active' => 'Aktywne',
        'polygon_color' => 'Kolor tła',
        'devices' => 'Urządzenia',
        'geofences' => 'Geostrefy',
        'overspeed' => 'Przekroczenie prędkości',
        'fuel_consumption' => 'Zużycie paliwa',
        'description' => 'Opis',
        'map_icon_id' => 'Ikona znacznika',
        'coordinates' => 'Punkt mapy',
        'date_from' => 'Data od',
        'date_to' => 'Data do',
        'code' => 'Kod',
        'title' => 'Tytuł',
        'note' => 'Zawartość',
        'path' => 'Plik',
        'period_name' => 'Nazwa okresu',
        'days' => 'Dni',
        'devices_limit' => 'Limit urządzeń',
        'trial' => 'Okres próbny',
        'price' => 'Cena',
        'message' => 'Wiadomość',
        'tag' => 'Parametr',
        'timezone_id' => 'Strefa czasowa',
        'unit_of_distance' => 'Jednostka odległości',
        'unit_of_capacity' => 'Jednostka pojemności',
        'user' => 'Użytkownik',
        'group_id' => 'Grupa',
        'permission_to_add_devices' => 'Dodawanie urządzeń',
        'unit_of_altitude' => 'Jednostka wysokości',
        'sms_gateway_url' => 'Adres bramki SMS',
        'mobile_phone' => 'Telefon komórkowy',
        'permission_to_use_sms_gateway' => 'Bramka SMS',
        'loged_at' => 'Ostatnie logowanie',
        'manager_id' => 'Menedżer',
        'sim_number' => 'Numer SIM',
        'device_model' => 'Model urządzenia',
        'rfid' => 'RFID',
        'phone' => 'Telefon',
        'device_id' => 'Urządzenie',
        'tag_value' => 'Wartość parametru',
        'device_port' => 'Urządzenie portu',
        'event' => 'Wydarzenie',
        'port' => 'Port',
        'device_protocol' => 'Protokół urządzenie',
        'protocol' => 'Protokół',
        'sensor_name' => 'Nazwa czujnika',
        'sensor_type' => 'Typ czujnika',
        'sensor_template' => 'Szablon czujnika',
        'tag_name' => 'Nazwa parametru',
        'min_value' => 'Min. wartość',
        'max_value' => 'Max. wartość',
        'on_value' => 'O wartości',
        'off_value' => 'Wartość WYŁ',
        'shown_value_by' => 'Pokaż wartość przez',
        'full_tank_value' => 'Wartość parametru',
        'formula' => 'Wzór',
        'parameters' => 'Parametry',
        'full_tank' => 'Pełna zbiornika w litrach/galon',
        'fuel_tank_name' => 'Nazwa zbiornika paliwa',
        'odometer_value' => 'Wartość',
        'odometer_value_by' => 'Drogomierz',
        'unit_of_measurement' => 'Jednostka miary',
        'plate_number' => 'Numer rejestracyjny',
        'vin' => 'VIN',
        'registration_number' => 'Rejestracja/Numer aktywów',
        'object_owner' => 'Właściciel obiektu/Kierownik',
        'additional_notes' => 'Dodatkowe uwagi',
        'expiration_date' => 'Termin ważności',
        'days_to_remind' => 'Dni przed upływem przypominać',
        'type' => 'Typ',
        'format' => 'Format',
        'show_addresses' => 'Pokaż adresy',
        'stops' => 'Przystanki',
        'speed_limit' => 'Limit prędkości',
        'zones_instead' => 'Stref, a nie adresy',
        'daily' => 'Codziennie',
        'weekly' => 'Tygodniowo',
        'send_to_email' => 'Wyślij na e-mail',
        'filter' => 'Filtr',
        'status' => 'Status',
        'date' => 'Data',
        'geofence_name' => 'Nazwa Geofence',
        'tail_color' => 'Kolor ogona',
        'tail_length' => 'Długość ogona',
        'engine_hours' => 'Godziny pracy silnika',
        'detect_engine' => 'Wykrywanie na silniku WŁ/WYŁ',
        'min_moving_speed' => 'Min . ruchu prędkość w km/h',
        'min_fuel_fillings' => 'Min . Różnica paliwa do wykrywania wypełnień paliwa',
        'min_fuel_thefts' => 'Min . Różnica paliwa do wykrywania kradzieży paliwa',
        'expiration_by' => 'Wygaśnięcie przez',
        'interval' => 'Interwał',
        'last_service' => 'Ostatni serwis',
        'trigger_event_left' => 'Wyzwolić zdarzenie po lewej',
        'current_odometer' => 'Aktualny licznik kilometrów',
        'current_engine_hours' => 'Aktualne godziny silnika',
        'renew_after_expiration' => 'Odnów po upływie',
        'sms_template_id' => 'Szablon SMS',
        'frequency' => 'Częstotliwość',
        'unit' => 'Jednostka',
        'noreply_email' => 'No adres e-mail odpowiedź',
        'signature' => 'Podpis',
        'use_smtp_server' => 'Użyj serwera SMTP',
        'smtp_server_host' => 'Hosta serwera SMTP',
        'smtp_server_port' => 'Portu serwera SMTP',
        'smtp_security' => 'Bezpieczeństwo SMTP',
        'smtp_username' => 'SMTP nazwę użytkownika',
        'smtp_password' => 'Hasło SMTP',
        'from_name' => 'Od nazwy',
        'icons' => 'Ikony',
        'server_name' => 'Nazwa serwera',
        'available_maps' => 'Dostępne mapy',
        'default_language' => 'Domyślny język',
        'default_timezone' => 'Domyślnie strefa czasowa',
        'default_unit_of_distance' => 'Domyślna jednostka odległości',
        'default_unit_of_capacity' => 'Domyślna jednostka pojemności',
        'default_unit_of_altitude' => 'Domyślne jednostki wysokości',
        'default_date_format' => 'Domyślny format daty',
        'default_time_format' => 'Domyślny format czasu',
        'default_map' => 'Domyślnie mapa',
        'default_object_online_timeout' => 'Domyślny limit czasu w Internecie obiektu',
        'logo' => 'Logo',
        'login_page_logo' => 'Strona logowania logo',
        'frontpage_logo' => 'Frontpage logo',
        'favicon' => 'Favicon',
        'allow_users_registration' => 'Pozwala na rejestrację użytkowników',
        'frontpage_logo_padding_top' => 'Frontpage logo wyściółka góry',
        'default_maps' => 'Mapy domyślne',
        'subscription_expiration_after_days' => 'Subskrypcja ważności po dniach',
        'gprs_template_id' => 'Szablon GPRS',
        'calibrations' => 'Kalibracja',
        'ftp_server' => 'FTP serwer',
        'ftp_port' => 'FTP port',
        'ftp_username' => 'FTP nazwa użytkownika',
        'ftp_password' => 'FTP hasło',
        'ftp_path' => 'FTP ścieżka',
        'period' => 'Okres',
        'hour' => 'Godzina',
        'color' => 'Kolor',
        'polyline' => 'Trasa',
        'request_method' => 'Metoda zapytanie',
        'authentication' => 'Uwierzytelnianie',
        'username' => 'Nazwa użytkownika',
        'encoding' => 'Kodowanie',
        'time_adjustment' => 'Regulacja czasu',
        'parameter' => 'Parametr',
        'export_type' => 'Typ Export',
        'groups' => 'Grupy',
        'file' => 'Plik',
        'extra' => 'Dodatkowy',
        'parameter_value' => 'Wartość parametru',
        'enable_plans' => 'Włącz plany',
        'payment_type' => 'Typ płatności',
        'paypal_client_id' => 'Paypal ID klienta',
        'paypal_secret' => 'Paypal tajne',
        'paypal_currency' => 'Paypal waluty',
        'paypal_payment_name' => 'Nazwa płatności Paypal',
        'objects' => 'Przedmioty',
        'duration_value' => 'Trwanie',
        'permissions' => 'Uprawnienia',
        'plan' => 'Plan',
        'default_billing_plan' => 'Plan Domyślnie rozliczeniowy',
        'sensor_group_id' => 'Grupa Sensor',
        'daylight_saving_time' => 'Czas letni',
        'phone_number' => 'Numer telefonu',
        'action' => 'Czynność',
        'time' => 'Czas',
        'order' => 'Zamówienie',
        'geocoder_api' => 'Geocoder API',
        'geocoder_cache' => 'Geocoder cache',
        'geocoder_cache_days' => 'Geocoder cache days',
        'geocoder_cache_delete' => 'Delete geocoder cache',
        'api_key' => 'API key',
        'api_url' => 'API url',
        'map_center_latitude' => 'Centrum Mapa Latitude',
        'map_center_longitude' => 'Centrum mapa geograficzna',
        'map_zoom_level' => 'Mapa poziom powiększenia',
        'dst_type' => 'Rodzaj',
        'provider' => 'Dostawca',
        'week_start_day' => 'Domyślny kalendarz tydzień początek dnia',
        'ip' => 'IP',
        'gprs_templates_only' => 'Polecenia Pokaż tylko GPRS Szablony',
        'select_all_objects' => 'Zaznacz wszystkie obiekty',
        'icon_type' => 'Icon type',
        'on_setflag_1' => 'Starting character',
        'on_setflag_2' => 'Amount of characters',
        'on_setflag_3' => 'Value of parameter',
        'domain' => 'Domain',
        'auth_id' => 'Auth ID',
        'auth_token' => 'Auth token',
        'senders_phone' => 'Sender\'s phone number',
        'database_clear_status' => 'Automatyczne czyszczenie historii',
        'database_clear_days' => 'Dni do wygaśnięcia',
        'ignition_detection' => 'Wykrywanie zapłonem',
        'template_color' => 'Kolor szablonu',
        'background' => 'Tło',
        'login_page_text_color' => 'Kolor tekstu strony logowania',
        'login_page_background_color' => 'Kolor tła strony logowania',
        'welcome_text' => 'Tekst powitalny',
        'bottom_text' => 'Tekst na dole',
        'apple_store_link' => 'Link AppleStore',
        'google_play_link' => 'Link GooglePlay',
        'here_map_id' => 'Identyfikator aplikacji HERE.com',
        'here_map_code' => 'Kod aplikacji HERE.com',
        'login_page_panel_background_color' => 'Kolor tła panelu logowania',
        'login_page_panel_transparency' => 'Przejrzystość strony panelu logowania',
        'visible' => 'Widoczny',
        'position' => 'Pozycja',
        'stop_duration_longer_than' => 'Zatrzymaj czas trwania dłuższy niż',
        'mapbox_access_token' => 'Token dostępu do MapBox',
        'flag' => 'Flaga',
        'shift_start' => 'Shift start',
        'shift_finish' => 'Zakończenie Shift',
        'shift_start_tolerance' => 'Tolerancja początku skoku',
        'shift_finish_tolerance' => 'Tolerancja końca skoku',
        'excessive_exit' => 'Nadmierne wyjście',
        'smtp_authentication' => 'Uwierzytelnianie SMTP',
        'skip_calibration' => 'Wyklucz obliczenia poza zakresem kalibracji',
        'bing_maps_key' => 'Klawisz mapy Bing',
        'stripe_public_key' => 'STRIPE klucz publiczny',
        'stripe_secret_key' => 'STRIPE tajny klucz',
        'stripe_currency' => 'PASKA waluta',
        'priority' => 'Priorytet',
        'pickup_address' => 'Adres odbioru',
        'delivery_address' => 'Adres dostawy',
        'schedule' => 'Harmonogram',
        'sound_notification' => 'Powiadomienie dźwiękowe',
        'push_notification' => 'Aktywne powiadomienie',
        'email_notification' => 'Powiadomienie e-mail',
        'sms_notification' => 'Powiadomienie SMS',
        'webhook_notification' => 'Powiadomienie z Webhook',
        'offline_duration_longer_than' => 'Czas trwania w trybie offline dłuższy niż',
        'sms_gateway_headers' => 'Nagłówki bramki SMS',
        'forward' => 'Naprzód',
        'by_status' => 'Według statusu',
        'icon_status_online' => 'Ikona statusu online',
        'icon_status_offline' => 'Ikona stanu offline',
        'icon_status_ack' => 'Ikona statusu Ack',
        'icon_status_engine' => 'Ikona stanu silnika',
    ),
    'same_protocol' => 'Urządzenia muszą być tego samego protokołu.',
    'contains' => ':attribute musi zawierać :value .',
    'ip_port' => ':attribute nie jest zgodny z formatem IP:PORT',
);