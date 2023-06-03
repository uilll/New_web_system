<?php

return [

    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => 'Le champ :attribute n\'est pas une URL valide.',
    'after' => 'Le champ :attribute doit être une date postérieure à :date.',
    'alpha' => 'Le champ :attribute doit seulement contenir des lettres.',
    'alpha_dash' => 'Le champ :attribute doit seulement contenir des lettres, des chiffres et des tirets.',
    'alpha_num' => 'Le champ :attribute doit seulement contenir des chiffres et des lettres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'between' => [
        'numeric' => 'La valeur de :attribute doit être comprise entre :min et :max.',
        'file' => 'Le fichier :attribute doit avoir une taille entre :min et :max ko.',
        'string' => 'Le texte :attribute doit avoir entre :min et :max caractères.',
        'array' => 'Le tableau :attribute doit avoir entre :min et :max éléments.',
    ],
    'confirmed' => 'Le champ de confirmation :attribute ne correspond pas.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit avoir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit avoir entre :min and :max chiffres.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',
    'exists' => 'Le champ :attribute sélectionné est invalide.',
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'Le champ :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'max' => [
        'numeric' => 'La valeur de :attribute ne peut être supérieure à :max.',
        'file' => 'Le fichier :attribute ne peut être plus gros que :max ko.',
        'string' => 'Le texte de :attribute ne peut contenir plus de :max caractères.',
        'array' => 'Le tableau :attribute ne peut avoir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'numeric' => 'La valeur de :attribute doit être supérieure à :min.',
        'file' => 'Le fichier :attribute doit être plus gros que :min ko.',
        'string' => 'Le texte :attribute doit contenir au moins :min caractères.',
        'array' => 'Le tableau :attribute doit avoir au moins :min éléments.',
    ],
    'not_in' => 'Le champ :attribute sélectionné n\'est pas valide.',
    'numeric' => 'Le champ :attribute doit contenir un nombre.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand la valeur de :other est :value.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_without' => 'Le champ :attribute est obligatoire quand :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est requis quand aucun de :values n\'est présent.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'size' => [
        'numeric' => 'La valeur de :attribute doit être :size.',
        'file' => 'La taille du fichier de :attribute doit être de :size ko.',
        'string' => 'Le texte de :attribute doit contenir :size caractères.',
        'array' => 'Le tableau :attribute doit contenir :size éléments.',
    ],
    'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    'url' => 'Le format de l\'URL de :attribute n\'est pas valide.',
    'array_max' => 'L\' :attribute articles max :max.',
    'lesser_than' => 'L\' :attribute doit être inférieure à celle :other',
    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    'attributes' => [
        'email' => 'E-mail',
        'password' => 'Mot de passe',
        'password_confirmation' => 'Confirmation du mot de passe',
        'remember_me' => 'Se souvenir de moi',
        'name' => 'Nom',
        'imei' => 'IMEI ou identifiant de l\'Appareil',
        'fuel_measurement_type' => 'Mesure du carburant',
        'fuel_cost' => 'Coût du carburant',
        'icon_id' => 'Icône Traceur',
        'active' => 'Actif',
        'polygon_color' => 'Couleur de fond',
        'devices' => 'Traceurs',
        'geofences' => 'Geo-clotures',
        'overspeed' => 'Survitesse',
        'fuel_consumption' => 'Consommation de carburant',
        'description' => 'Description',
        'map_icon_id' => 'Icône des marqeurs',
        'coordinates' => 'Coordonnées',
        'date_from' => 'Date de début',
        'date_to' => 'Date de fin',
        'code' => 'Code',
        'title' => 'Titre',
        'note' => 'Note',
        'path' => 'Fichier',
        'period_name' => 'Période',
        'days' => 'Journées',
        'devices_limit' => 'Limite Traceurs',
        'trial' => 'Essai',
        'price' => 'Prix',
        'message' => 'Message',
        'tag' => 'Paramètre',
        'timezone_id' => 'Fuseau horaire',
        'unit_of_distance' => 'Unité de distance',
        'unit_of_capacity' => 'Unité de capacité',
        'unit_of_altitude' => 'Unité d\'altitude',
        'icons' => 'Icônes',
        'sms_gateway_url' => 'URL de la passerelle SMS',
        'mobile_phone' => 'Portable',
        'sim_number' => 'Numéro SIM',
        'device_model' => 'Modèle du traceur',
        'group_id' => 'Groupe',
        'rfid' => 'RFID',
        'phone' => 'Téléphone',
        'device_id' => 'Traceur',
        'tag_value' => 'Valeur du paramètre',
        'device_port' => 'Port traceur',
        'event' => 'Evénement',
        'port' => 'Port',
        'device_protocol' => 'Protocole',
        'protocol' => 'Protocole',
        'sensor_name' => 'Nom du capteur',
        'sensor_type' => 'Type de capteur',
        'sensor_template' => 'Modèle de capteur',
        'tag_name' => 'Nom du paramètre',
        'min_value' => 'Valeur Min.',
        'max_value' => 'Valeur Max.',
        'on_value' => 'Valeur pour ON',
        'off_value' => 'Valeur pour OFF',
        'shown_value_by' => 'Afficher la valeur en',
        'full_tank_value' => 'Valeur du paramètre',
        'formula' => 'Formule',
        'parameters' => 'Paramètres',
        'full_tank' => 'Réservoir plein en litres',
        'fuel_tank_name' => 'Nom du réservoir',
        'odometer_value' => 'Valeur odomètre',
        'odometer_value_by' => 'Odomètre',
        'unit_of_measurement' => 'Unité de mesure',
        'plate_number' => 'Immatriculation',
        'vin' => 'Numéro de série',
        'registration_number' => 'Inscription/nombre d\'actifs',
        'object_owner' => 'Propriétaire/Gestionnaire du traceur',
        'additional_notes' => 'Notes complémentaires',
        'expiration_date' => 'Date d\'expiration',
        'days_to_remind' => 'Jours pour rappel avant expiration',
        'type' => 'Type',
        'format' => 'Format',
        'show_addresses' => 'Afficher les adresses',
        'stops' => 'Arrêts',
        'speed_limit' => 'Limitation de vitesse',
        'zones_instead' => 'Zones au lieu des adresses',
        'daily' => 'Journalier',
        'weekly' => 'Hebdomadaire',
        'send_to_email' => 'Envoyer à cet email',
        'filter' => 'Filtre',
        'status' => 'Statut',
        'date' => 'Date',
        'geofence_name' => 'Nom Geo-cloture',
        'tail_color' => 'Couleur de la trace',
        'tail_length' => 'Longueur de la trace',
        'engine_hours' => 'Heures moteur',
        'detect_engine' => 'Détection de l\' etat du moteur (ON/OFF) par',
        'min_moving_speed' => 'Vitesse minimale de déplacement en km/h',
        'min_fuel_fillings' => 'Valeur de changement minimale de carburant pour détecter les remplissages ',
        'min_fuel_thefts' => 'Valeur de changement minimale de carburant pour détecter les vols de carburant',
        'expiration_by' => 'Expiration par',
        'interval' => 'Intervalle',
        'last_service' => 'Dernière révision',
        'trigger_event_left' => 'L\'événement déclencheur lorsqu\'il est laissé',
        'current_odometer' => 'Odomètre',
        'current_engine_hours' => 'Heures moteur actuels',
        'renew_after_expiration' => 'Renouveler après l\'expiration',
        'sms_template_id' => 'Modèle de SMS',
        'frequency' => 'Fréquence',
        'unit' => 'Unité',
        'noreply_email' => 'Aucune adresse E-mail de réponse',
        'signature' => 'Signature',
        'use_smtp_server' => 'Utiliser un serveur SMTP',
        'smtp_server_host' => 'Serveur hôte SMTP',
        'smtp_server_port' => 'Port du serveur SMTP',
        'smtp_security' => 'Sécurité SMTP',
        'smtp_username' => 'Nom d\'utilisateur SMTP',
        'smtp_password' => 'Mot de passe SMTP',
        'from_name' => 'De nom',
        'server_name' => 'Nom du serveur',
        'available_maps' => 'Cartes disponibles',
        'default_language' => 'Langue par défaut',
        'default_timezone' => 'Fuseau horaire par défaut',
        'default_unit_of_distance' => 'Unité de distance par défaut',
        'default_unit_of_capacity' => 'Unité de capacité par défaut',
        'default_unit_of_altitude' => 'Unité d\'altitude par défaut',
        'default_date_format' => 'Format de la date par défaut',
        'default_time_format' => 'Format de l\'heure par défaut',
        'default_map' => 'Carte par défaut',
        'default_object_online_timeout' => 'Délai d\'attente en ligne par défaut',
        'logo' => 'Logo',
        'login_page_logo' => 'Logo page de connexion',
        'frontpage_logo' => 'Logo interface utilisateurs',
        'favicon' => 'Favicon',
        'allow_users_registration' => 'Autoriser l\'enregistrement des utilisateurs',
        'frontpage_logo_padding_top' => 'Marges supérieure pour le logo de l\interface',
        'default_maps' => 'Cartes par défaut',
        'subscription_expiration_after_days' => 'Abonnement expire aprés ',
        'gprs_template_id' => 'Modèle GPRS',
        'calibrations' => 'étalonnages',
        'ftp_server' => 'Serveur FTP',
        'ftp_port' => 'Port FTP',
        'ftp_username' => 'Nom d\'utilisateur FTP',
        'ftp_password' => 'Mot de passe FTP',
        'ftp_path' => 'Chemin FTP',
        'period' => 'Période',
        'hour' => 'Heure',
        'color' => 'Couleur',
        'polyline' => 'Itinéraire',
        'request_method' => 'Méthode de Demande',
        'authentication' => 'Authentification',
        'username' => 'Nom d\'utilisateur',
        'encoding' => 'Encodage',
        'time_adjustment' => 'Réglage de l\'heure',
        'parameter' => 'Paramètre',
        'export_type' => 'Type d\'exportation',
        'groups' => 'Groupes',
        'file' => 'Dossier',
        'extra' => 'En supplément',
        'parameter_value' => 'Valeur du paramètre',
        'enable_plans' => 'Activer les abonnements',
        'payment_type' => 'Type de paiement',
        'paypal_client_id' => 'Paypal ID client',
        'paypal_secret' => 'Paypal secrète',
        'paypal_currency' => 'Monnaie Paypal',
        'paypal_payment_name' => 'Paypal Nom de paiement',
        'objects' => 'Traceurs',
        'duration_value' => 'Durée',
        'permissions' => 'Autorisations',
        'plan' => 'Abonnement',
        'default_billing_plan' => 'Abonnement par défaut',
        'sensor_group_id' => 'Groupe de capteur',
        'daylight_saving_time' => 'Heure d\'été/d\'hiver',
        'phone_number' => 'Numéro de téléphone',
        'action' => 'Action',
        'time' => 'Date',
        'order' => 'Commande',
        'geocoder_api' => 'Geocoder API',
        'geocoder_cache' => 'Geocoder cache',
        'geocoder_cache_days' => 'Geocoder cache days',
        'geocoder_cache_delete' => 'Delete geocoder cache',
        'api_key' => 'Clé API',
        'api_url' => 'API url',
        'map_center_latitude' => 'Centre de la carte latitude',
        'map_center_longitude' => 'Centre de la carte longitude',
        'map_zoom_level' => 'Niveau de zoom',
        'dst_type' => 'Type',
        'provider' => 'Fournisseur',
        'week_start_day' => 'Jour de début de la semaine',
        'ip' => 'IP',
        'gprs_templates_only' => 'Afficher uniquement les modèles de commandes GPRS',
        'select_all_objects' => 'Sélectionnez tous les traceurs',
        'icon_type' => 'Type d\'icône',
        'on_setflag_1' => 'Caractère de départ',
        'on_setflag_2' => 'nombre de caractères',
        'on_setflag_3' => 'valeur du paramètre',
        'domain' => 'Domaine',
        'auth_id' => 'Auth ID',
        'auth_token' => 'Auth token',
        'senders_phone' => 'Téléphone expéditeur',
        'database_clear_status' => 'Nettoyage automatique de l\'historique',
        'database_clear_days' => 'Jours à conserver',
        'ignition_detection' => 'Détection mise en rouge du moteur par',
        'template_color' => 'Couleur du modèle',
        'background' => 'Arrière plan',
        'login_page_text_color' => 'Couleur du texte de la page de connexion',
        'login_page_background_color' => 'Couleur de fond de la page de connexion',
        'welcome_text' => 'Texte de bienvenue',
        'bottom_text' => 'Texte en bas',
        'apple_store_link' => 'Lien AppleStore',
        'google_play_link' => 'Lien GooglePlay',
        'here_map_id' => 'ICI ID de l\'application',
        'here_map_code' => 'Code de l\'application ici',
        'login_page_panel_background_color' => 'Panneau de la page de connexion couleur de fond',
        'login_page_panel_transparency' => 'Transparence du panneau de la page de connexion',
        'visible' => 'Visible',
        'imei_device' => 'Identifiant IMEI ou Device',
        'user' => 'Utilisateur',
        'permission_to_add_devices' => 'Ajouter des appareils un deux',
        'permission_to_use_sms_gateway' => 'Passerelle SMS',
        'loged_at' => 'Dernière connexion',
        'manager_id' => 'Directeur',
        'position' => 'Position',
        'stop_duration_longer_than' => 'Arrêtez la durée plus longtemps que',
        'mapbox_access_token' => 'Jeton d&#39;accès MapBox',
        'flag' => 'Drapeau',
        'shift_start' => 'Décaler le début',
        'shift_finish' => 'Finition de décalage',
        'shift_start_tolerance' => 'Tolérance de démarrage de décalage',
        'shift_finish_tolerance' => 'Tolérance de fin de travail',
        'excessive_exit' => 'Sortie excessive',
        'smtp_authentication' => 'Authentification SMTP',
        'skip_calibration' => 'Exclure les calculs de la plage d&#39;étalonnage',
        'bing_maps_key' => 'Bing cartes clés',
        'stripe_public_key' => 'STRIPE clé publique',
        'stripe_secret_key' => 'STRIPE clé secrète',
        'stripe_currency' => 'STRIPE devise',
        'priority' => 'Priorité',
        'pickup_address' => 'Adresse de ramassage',
        'delivery_address' => 'Adresse de livraison',
        'schedule' => 'Programme',
        'sound_notification' => 'Notification sonore',
        'push_notification' => 'Notification push',
        'email_notification' => 'Notification par email',
        'sms_notification' => 'Notification par SMS',
        'webhook_notification' => 'Notification Webhook',
        'offline_duration_longer_than' => 'Durée hors ligne plus longue que',
        'sms_gateway_headers' => 'En-têtes de passerelle SMS',
        'forward' => 'Vers l&#39;avant',
        'by_status' => 'Par statut',
        'icon_status_online' => 'Icône d&#39;état en ligne',
        'icon_status_offline' => 'Icône d&#39;état hors ligne',
        'icon_status_ack' => 'Ack status icon',
        'icon_status_engine' => 'Icône d&#39;état du moteur',
    ],
    'same_protocol' => 'Les appareils doivent être du même protocole.',
    'contains' => 'L&#39; :attribute doit contenir :value .',
    'ip_port' => 'L&#39; :attribute ne correspond pas au format IP:PORT',
];
