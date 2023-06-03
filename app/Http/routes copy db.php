<?php

use App\Monitoring;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

require_once 'global.php';
//checkLogin();

if (env('forceSchema', false)) {
    URL::forceSchema(env('forceSchema'));
}

// Authentication
Route::group([], function () {
    Route::get('/', ['as' => 'home', 'uses' => function () {
        if (Auth::check()) {
            return Redirect::route('objects.index');
        } else {
            return Redirect::route('authentication.create');
        }
    }]);
    if (isPublic()) {
        Route::get('login/{hash}', ['as' => 'login', 'uses' => 'Frontend\LoginController@store']);
    } else {
        Route::get('login/{id?}', ['as' => 'login', 'uses' => 'Frontend\LoginController@create']);
    }

    Route::get('logout', ['as' => 'logout', 'uses' => 'Frontend\LoginController@destroy']);

    Route::any('authentication/store', ['as' => 'authentication.store', 'uses' => 'Frontend\LoginController@store']);
    Route::resource('authentication', 'Frontend\LoginController', ['only' => ['create', 'destroy']]);
    Route::resource('password_reminder', 'Frontend\PasswordReminderController', ['only' => ['create', 'store']]);
    Route::get('password/reset/{token}', ['uses' => 'Frontend\PasswordReminderController@reset', 'as' => 'password_reminder.reset']);
    Route::post('password/reset/{token}', ['uses' => 'Frontend\PasswordReminderController@update', 'as' => 'password_reminder.update']);

    if (settings('main_settings.allow_users_registration')) {
        Route::resource('registration', 'Frontend\RegistrationController', ['only' => ['create', 'store']]);
    }

    // GPS data
    Route::any('gpsdata_insert', ['as' => 'gpsdata_insert', 'uses' => 'Frontend\GpsDataController@insert']);

    Route::get('demo', ['as' => 'demo', 'uses' => 'Frontend\LoginController@demo']);

    Route::get('time', ['as' => 'time', 'uses' => function () {
        echo date('Y-m-d H:i:s O');
    }]);
});

// Authenticated Frontend |active_subscription
Route::group(['middleware' => ['auth', 'active_subscription', 'check_password_updated'], 'namespace' => 'Frontend'], function () {
    Route::delete('objects/destroy/{objects}', ['as' => 'objects.destroy', 'uses' => 'DevicesController@destroy']);
    Route::get('objects/items/{page?}/{search_item?}/{search_type?}/{device_per_page?}', ['as' => 'objects.items', 'uses' => 'ObjectsController@items']);
    //    Route::get('objects/items_page/{pagina}', ['as' => 'objects.itemspage', 'uses' => 'ObjectsController@items_page']);
    Route::get('objects/itemsSimple', ['as' => 'objects.items_simple', 'uses' => 'ObjectsController@itemsSimple']);
    Route::get('objects/search_item/{search_item}', ['as' => 'objects.search_item', 'uses' => 'ObjectsController@search_item']);

    Route::get('objects/items_json', ['as' => 'objects.items_json', 'uses' => 'ObjectsController@itemsJson']);
    Route::get('objects/change_group_status', ['as' => 'objects.change_group_status', 'uses' => 'ObjectsController@changeGroupStatus']);
    Route::get('objects/change_alarm_status', ['as' => 'objects.change_alarm_status', 'uses' => 'ObjectsController@changeAlarmStatus']);
    Route::get('objects/alarm_position', ['as' => 'objects.alarm_position', 'uses' => 'ObjectsController@alarmPosition']);
    Route::get('objects/show_address', ['as' => 'objects.show_address', 'uses' => 'ObjectsController@showAddress']);
    Route::any('objects/interaction_check/{id}', ['as' => 'objects.interaction_check', 'uses' => 'ObjectsController@interaction_check']);
    Route::any('objects/interaction/{id}', ['as' => 'objects.interaction', 'uses' => 'ObjectsController@interaction']);
    Route::any('objects/interaction_action/{id}', ['as' => 'objects.interaction_action', 'uses' => 'ObjectsController@interaction_action']);
    Route::any('objects/interaction_later/{id}', ['as' => 'objects.interaction_later', 'uses' => 'ObjectsController@interaction_later']);
    Route::get('objects/stop_time/{id?}', ['as' => 'objects.stop_time', 'uses' => 'DevicesController@stopTime']);
    Route::get('objects/traccer_route/{id?}', ['as' => 'objects.traccer_route', 'uses' => 'ObjectsController@traccer_route']);
    Route::get('objects/anchor/{id?}', ['as' => 'objects.anchor', 'uses' => 'ObjectsController@anchor']);
    Route::get('objects/sensores/{id?}', ['as' => 'objects.sensores', 'uses' => 'ObjectsController@sensores']);
    Route::resource('objects', 'ObjectsController', ['only' => ['index']]);

    Route::get('objects/list', ['as' => 'objects.listview', 'uses' => 'ObjectsListController@index']);
    Route::get('objects/list/items', ['as' => 'objects.listview.items', 'uses' => 'ObjectsListController@items']);
    Route::get('objects/list/settings', ['as' => 'objects.listview_settings.edit', 'uses' => 'ObjectsListController@edit']);
    Route::post('objects/list/settings', ['as' => 'objects.listview_settings.update', 'uses' => 'ObjectsListController@update']);

    // Autologin
    Route::get('autologin/{token}', ['as' => 'autologin', 'uses' => '\Watson\Autologin\AutologinController@autologin']);

    // Geofences
    Route::get('geofences/export', ['as' => 'geofences.export', 'uses' => 'GeofencesController@export']);
    Route::get('geofences/export_type', ['as' => 'geofences.export_type', 'uses' => 'GeofencesController@exportType']);
    Route::post('geofences/change_active', ['as' => 'geofences.change_active', 'uses' => 'GeofencesController@changeActive']);
    Route::post('geofences/export_create', ['as' => 'geofences.export_create', 'uses' => 'GeofencesController@exportCreate']);
    Route::post('geofences/import', ['as' => 'geofences.import', 'uses' => 'GeofencesController@import']);
    Route::resource('geofences', 'GeofencesController');

    // Geofences groups
    Route::get('geofences_groups/update_select', ['as' => 'geofences_groups.update_select', 'uses' => 'GeofencesGroupsController@updateSelect']);
    Route::get('geofences_groups/change_status', ['as' => 'geofences_groups.change_status', 'uses' => 'GeofencesGroupsController@changeStatus']);
    Route::resource('geofences_groups', 'GeofencesGroupsController');

    // Routes
    Route::post('routes/change_active', ['as' => 'routes.change_active', 'uses' => 'RoutesController@changeActive']);
    Route::resource('routes', 'RoutesController');

    Route::get('device/widgets/location/{id?}', ['as' => 'device.widgets.location', 'uses' => 'DeviceWidgetsController@location']);

    // Devices
    Route::get('devices/edit/{id}/{admin?}', ['as' => 'devices.edit', 'uses' => 'DevicesController@edit']);
    Route::post('devices/change_active', ['as' => 'devices.change_active', 'uses' => 'DevicesController@changeActive']);
    Route::get('devices/follow_map/{id?}', ['as' => 'devices.follow_map', 'uses' => 'DevicesController@followMap']);
    Route::get('devices/commands', ['as' => 'devices.commands', 'uses' => 'SendCommandController@getCommands']);
    Route::get('devices/do_destroy/{id}', ['as' => 'devices.do_destroy', 'uses' => 'DevicesController@doDestroy']);
    Route::resource('devices', 'DevicesController', ['except' => ['index', 'edit']]);

    // Devices Groups
    Route::resource('devices_groups', 'DevicesGroupsController');

    // Alerts
    Route::put('alerts/update/{id?}', ['as' => 'alerts.update', 'uses' => 'AlertsController@update']);
    Route::get('alerts/do_destroy/{id}', ['as' => 'alerts.do_destroy', 'uses' => 'AlertsController@doDestroy']);
    Route::delete('alerts/destroy/{id?}', ['as' => 'alerts.destroy', 'uses' => 'AlertsController@destroy']);
    Route::post('alerts/change_active', ['as' => 'alerts.change_active', 'uses' => 'AlertsController@changeActive']);
    Route::get('alerts/commands', ['as' => 'alerts.commands', 'uses' => 'AlertsController@getCommands']);
    Route::resource('alerts', 'AlertsController');

    // History
    Route::get('history', ['as' => 'history.index', 'uses' => 'HistoryController@index']);
    Route::get('history/positions', ['as' => 'history.positions', 'uses' => 'HistoryController@positionsPaginated']);
    Route::get('history/position', ['as' => 'history.position', 'uses' => 'HistoryController@getPosition']);
    Route::get('history/do_delete_positions', ['as' => 'history.do_delete_positions', 'uses' => 'HistoryController@doDeletePositions']);
    Route::any('history/delete_positions', ['as' => 'history.delete_positions', 'uses' => 'HistoryController@deletePositions']);

    Route::get('history/export', ['as' => 'history.export', 'uses' => 'HistoryExportController@generate']);
    Route::get('history/download/{file}/{name}', ['as' => 'history.download', 'uses' => 'HistoryExportController@download']);

    // Events
    Route::get('events', ['as' => 'events.index', 'uses' => 'EventsController@index']);
    if (App::environment() == 'staging') {
        Route::get('notifications', ['as' => 'events.index', 'uses' => 'EventsController@index']);
    }
    Route::get('events/do_destroy', ['as' => 'events.do_destroy', 'uses' => 'EventsController@doDestroy']);
    Route::delete('events/destroy', ['as' => 'events.destroy', 'uses' => 'EventsController@destroy']);
    Route::any('events/disable', ['as' => 'events.disable', 'uses' => 'EventsController@disable_push']);

    // Map Icons
    Route::get('map_icons/import', ['as' => 'map_icons.import', 'uses' => 'MapIconsController@import_form']);
    Route::post('map_icons/import', ['as' => 'map_icons.import', 'uses' => 'MapIconsController@import']);
    Route::get('map_icons/list', ['as' => 'map_icons.list', 'uses' => 'MapIconsController@iconsList']);
    Route::post('map_icons/change_active', ['as' => 'map_icons.change_active', 'uses' => 'MapIconsController@changeActive']);
    Route::resource('map_icons', 'MapIconsController');

    // Report Logs
    Route::get('reports/logs', ['as' => 'reports.logs', 'uses' => 'ReportsController@logs']);
    Route::any('reports/log/download/{id}', ['as' => 'reports.log_download', 'uses' => 'ReportsController@logDownload']);
    Route::any('reports/log/destroy', ['as' => 'reports.log_destroy', 'uses' => 'ReportsController@logDestroy']);

    // Reports
    Route::any('reports/types', ['as' => 'reports.types', 'uses' => 'ReportsController@getTypes']);
    Route::any('reports/types/{type?}', ['as' => 'reports.types.show', 'uses' => 'ReportsController@getType']);
    Route::any('reports/update', ['as' => 'reports.update', 'uses' => 'ReportsController@update']);
    Route::get('reports/do_destroy/{id}', ['as' => 'reports.do_destroy', 'uses' => 'ReportsController@doDestroy']);
    Route::resource('reports', 'ReportsController', ['except' => ['edit', 'update']]);

    // My account
    Route::post('my_account/change_map', ['as' => 'my_account.change_map', 'uses' => 'MyAccountController@changeMap']);
    Route::resource('my_account', 'MyAccountController', ['only' => ['edit', 'update']]);
    Route::get('email_confirmation/resend', ['as' => 'email_confirmation.resend_code', 'uses' => 'EmailConfirmationController@resendActivationCode']);
    Route::post('email_confirmation/resend', ['as' => 'email_confirmation.resend_code_submit', 'uses' => 'EmailConfirmationController@resendActivationCodeSubmit']);
    Route::resource('email_confirmation', 'EmailConfirmationController', ['only' => ['edit', 'update']]);
    Route::get('my_account_settings/change_language/{lang}', ['as' => 'my_account_settings.change_lang', 'uses' => 'MyAccountSettingsController@changeLang']);

    // User drivers
    Route::any('user_drivers/index', ['as' => 'user_drivers.index', 'uses' => 'UserDriversController@index']);
    Route::get('user_drivers/change/{id}', ['as' => 'user_drivers.change', 'uses' => 'UserDriversController@change']);
    Route::any('user_drivers/dochange', ['as' => 'user_drivers.dochange', 'uses' => 'UserDriversController@dochange']);
    Route::get('user_drivers/do_destroy/{id}', ['as' => 'user_drivers.do_destroy', 'uses' => 'UserDriversController@doDestroy']);
    Route::any('user_drivers/do_update/{id}', ['as' => 'user_drivers.do_update', 'uses' => 'UserDriversController@doUpdate']);
    Route::any('user_drivers/check_cnh', ['as' => 'user_drivers.check_cnh', 'uses' => 'UserDriversController@check_cnh']);
    Route::any('user_drivers/interaction/{driver_id?}/{type?}/{driver_name?}', ['as' => 'user_drivers.interaction', 'uses' => 'UserDriversController@interaction']);
    Route::any('user_drivers/interaction_action/{driver_id}', ['as' => 'user_drivers.interaction_action', 'uses' => 'UserDriversController@interaction_action']);
    Route::any('user_drivers/interaction_later/{driver_id}', ['as' => 'user_drivers.interaction_later', 'uses' => 'UserDriversController@interaction_later']);
    Route::resource('user_drivers', 'UserDriversController');

    // Sensors
    Route::get('sensors/do_destroy/{id}', ['as' => 'sensors.do_destroy', 'uses' => 'SensorsController@doDestroy']);
    Route::get('sensors/create/{device_id?}', ['as' => 'sensors.create', 'uses' => 'SensorsController@create']);
    Route::get('sensors/index/{device_id}', ['as' => 'sensors.index', 'uses' => 'SensorsController@index']);
    Route::get('sensors/engine_hours/{device_id?}', ['as' => 'sensors.get_engine_hours', 'uses' => 'SensorsController@getEngineHours']);
    Route::post('sensors/engine_hours/{device_id?}', ['as' => 'sensors.set_engine_hours', 'uses' => 'SensorsController@setEngineHours']);
    Route::resource('sensors', 'SensorsController', ['only' => ['store', 'edit', 'update', 'destroy']]);
    Route::get('sensors/param/{param}/{device_id}', ['as' => 'sensors.param', 'uses' => 'SensorsController@parameterSuggestion']);

    // Services
    Route::get('services/do_destroy/{id}', ['as' => 'services.do_destroy', 'uses' => 'ServicesController@doDestroy']);
    Route::get('services/create/{device_id?}', ['as' => 'services.create', 'uses' => 'ServicesController@create']);
    Route::get('services/index/{device_id?}', ['as' => 'services.index', 'uses' => 'ServicesController@index']);
    Route::get('services/table/{device_id?}', ['as' => 'services.table', 'uses' => 'ServicesController@table']);
    Route::resource('services', 'ServicesController', ['only' => ['store', 'edit', 'update', 'destroy']]);

    // Custom events
    Route::get('custom_events/do_destroy/{id}', ['as' => 'custom_events.do_destroy', 'uses' => 'CustomEventsController@doDestroy']);
    Route::post('custom_events/get_events', ['as' => 'custom_events.get_events', 'uses' => 'CustomEventsController@getEvents']);
    Route::post('custom_events/get_protocols', ['as' => 'custom_events.get_protocols', 'uses' => 'CustomEventsController@getProtocols']);
    Route::any('custom_events/get_events_by_device', ['as' => 'custom_events.get_events_by_device', 'uses' => 'CustomEventsController@getEventsByDevices']);
    Route::resource('custom_events', 'CustomEventsController');

    // User sms templates
    Route::get('user_sms_templates/do_destroy/{id}', ['as' => 'user_sms_templates.do_destroy', 'uses' => 'UserSmsTemplatesController@doDestroy']);
    Route::post('user_sms_templates/get_message', ['as' => 'user_sms_templates.get_message', 'uses' => 'UserSmsTemplatesController@getMessage']);
    Route::resource('user_sms_templates', 'UserSmsTemplatesController');

    // User gprs templates
    Route::get('user_gprs_templates/do_destroy/{id}', ['as' => 'user_gprs_templates.do_destroy', 'uses' => 'UserGprsTemplatesController@doDestroy']);
    Route::post('user_gprs_templates/get_message', ['as' => 'user_gprs_templates.get_message', 'uses' => 'UserGprsTemplatesController@getMessage']);
    Route::resource('user_gprs_templates', 'UserGprsTemplatesController');

    Route::get('membership/languages', ['as' => 'subscriptions.languages', 'uses' => 'SubscriptionsController@languages']);

    //My account settings
    Route::get('my_account_settings/change_top_toolbar', ['as' => 'my_account_settings.change_top_toolbar', 'uses' => 'MyAccountSettingsController@changeTopToolbar']);
    Route::get('my_account_settings/change_map_settings', ['as' => 'my_account_settings.change_map_settings', 'uses' => 'MyAccountSettingsController@changeMapSettings']);
    Route::resource('my_account_settings', 'MyAccountSettingsController', ['only' => ['edit', 'update']]);

    // Send command
    Route::post('send_command/gprs', ['as' => 'send_command.gprs', 'uses' => 'SendCommandController@gprsStore']);
    Route::get('send_command/get_device_sim_number', ['as' => 'send_command.get_device_sim_number', 'uses' => 'SendCommandController@getDeviceSimNumber']);
    Route::resource('send_command', 'SendCommandController', ['only' => ['create', 'store']]);

    //Camera
    Route::get('device_media/create', ['as' => 'device_media.create', 'uses' => 'DeviceMediaController@create']);
    Route::get('device_media/download/{file_name?}/{id?}', ['as' => 'device_media.download_file', 'uses' => 'DeviceMediaController@downloadFile']);
    Route::get('device_media/get_images/{id?}', ['as' => 'device_media.get_images', 'uses' => 'DeviceMediaController@getImages']);
    Route::get('device_media/get_image/{file_name?}/{id?}', ['as' => 'device_media.get_image', 'uses' => 'DeviceMediaController@getImage']);
    Route::get('device_media/delete_image/{file_name}/{id?}', ['as' => 'device_media.delete_image', 'uses' => 'DeviceMediaController@deleteImage']);

    // SMS gateway
    Route::get('sms_gateway/test_sms', ['as' => 'sms_gateway.test_sms', 'uses' => 'SmsGatewayController@testSms']);
    Route::post('sms_gateway/send_test_sms', ['as' => 'sms_gateway.send_test_sms', 'uses' => 'SmsGatewayController@sendTestSms']);
    Route::get('sms_gateway/clear_queue', ['as' => 'sms_gateway.clear_queue', 'uses' => 'SmsGatewayController@clearQueue']);

    Route::get('maintenance', ['as' => 'maintenance.index', 'uses' => 'MaintenanceController@index']);
    Route::get('maintenance/list', ['as' => 'maintenance.table', 'uses' => 'MaintenanceController@table']);

    Route::get('membership', ['as' => 'subscriptions.index', 'uses' => 'SubscriptionsController@index']);

    // Tasks
    Route::get('tasks/list', ['as' => 'tasks.list', 'uses' => 'TasksController@search']);
    Route::get('tasks/do_destroy/{id}', ['as' => 'tasks.do_destroy', 'uses' => 'TasksController@doDestroy']);
    Route::get('tasks/signature/{taskStatusId}', ['as' => 'tasks.signature', 'uses' => 'TasksController@getSignature']);
    Route::resource('tasks', 'TasksController');

    Route::any('address/autocomplete', ['as' => 'address.autocomplete', 'uses' => 'AddressController@autocomplete']);

    // Chats
    Route::get('chat/index', ['as' => 'chat.index', 'uses' => 'ChatController@index']);
    Route::get('chat/init/{chatableId}/{type?}', ['as' => 'chat.init', 'uses' => 'ChatController@initChat']);
    Route::get('chat/searchParticipant', ['as' => 'chat.searchParticipant', 'uses' => 'ChatController@searchParticipant']);
    Route::get('chat/{chatId}/messages', ['as' => 'chat.messages', 'uses' => 'ChatController@getMessages']);
    Route::get('chat/{chatId}', ['as' => 'chat.get', 'uses' => 'ChatController@getChat']);
    Route::post('chat/{chatId}', ['as' => 'chat.message', 'uses' => 'ChatController@createMessage']);

    //messages
    Route::get('messages_admin', 'MessageController@index_admin')->name('messages.index_admin'); // listar todas as mensagens
    Route::get('messages_client', ['as' => 'messages.client_index', 'uses' => 'MessageController@index_client', 'middleware' => 'auth']);
    //Route::get('messages', ['as' => 'messages.index', 'uses' => 'MessageController@index', 'middleware' => 'auth']);
    //Route::get('messages', 'MessageController@index')->name('messages.index'); // listar todas as mensagens lado do cliente
    Route::get('messages/create', 'MessageController@create')->name('messages.create'); // exibir o formulário para criar uma nova mensagem
    Route::post('messages', 'MessageController@store')->name('messages.store'); // salvar uma nova mensagem
    Route::get('messages/{id}', 'MessageController@show')->name('messages.show'); // exibir uma mensagem específica
    Route::get('messages/{id}/edit', 'MessageController@edit')->name('messages.edit'); // exibir o formulário para editar uma mensagem
    Route::put('messages/{id}', 'MessageController@update')->name('messages.update'); // atualizar uma mensagem existente
    Route::delete('messages/{id}', 'MessageController@destroy')->name('messages.destroy'); // excluir uma mensagem existente
    Route::get('/messages/client/{client_id}/subject/{subject}', 'MessageController@getMessagesByClientAndSubject')->name('messages.get_messages');

    //messagesreply
    Route::get('/messages/{message_id}/replies', 'MessageRepliesController@index')->name('message_replies.index'); // Exibir a lista de respostas para uma determinada mensagem
    Route::get('/messages/{message_id}/replies/create', 'MessageRepliesController@create')->name('message_replies.create'); // Exibir o formulário para criar uma nova resposta
    Route::post('/messages/{message_id}/replies', 'MessageRepliesController@store')->name('message_replies.store'); // Salvar uma nova resposta para uma mensagem
    Route::get('/messages/{message_id}/replies/{id}', 'MessageRepliesController@show')->name('message_replies.show'); // Exibir uma resposta específica
    Route::get('/messages/{message_id}/replies/{id}/edit', 'MessageRepliesController@edit')->name('message_replies.edit'); // Exibir o formulário para editar uma resposta
    Route::put('/messages/{message_id}/replies/{id}', 'MessageRepliesController@update')->name('message_replies.update'); // Atualizar uma resposta existente
    Route::put('messages/{message_id}/update_is_read', 'MessageRepliesController@updateIsRead')->name('message_replies.update_is_read');
    Route::delete('/messages/{message_id}/replies/{id}', 'MessageRepliesController@destroy')->name('message_replies.destroy'); // Excluir uma resposta existente
});

// Authenticated Admin
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'auth.manager', 'active_subscription', 'check_password_updated'], 'namespace' => 'Admin'], function () {
    Route::get('/', ['as' => 'admin', 'uses' => function () {
        return Redirect::route('admin.clients.index');
    }]);

    // Clients
    Route::get('users/clients/import_geofences', ['as' => 'admin.clients.import_geofences', 'uses' => 'ClientsController@importGeofences']);
    Route::post('users/clients/import_geofences', ['as' => 'admin.clients.import_geofences_set', 'uses' => 'ClientsController@importGeofencesSet']);
    Route::get('users/clients/import_map_icon', ['as' => 'admin.clients.import_map_icon', 'uses' => 'ClientsController@importMapIcon']);
    Route::post('users/clients/import_map_icon', ['as' => 'admin.clients.import_map_icon_set', 'uses' => 'ClientsController@importMapIconSet']);
    Route::any('users/clients', ['as' => 'admin.clients.index', 'uses' => 'ClientsController@index']);
    Route::any('users/clients/disable_push/{id}', ['as' => 'admin.clients.disable_push', 'uses' => 'ClientsController@disable_push']);
    Route::any('users/clients/get_devices/{id}', ['as' => 'admin.clients.get_devices', 'uses' => 'ClientsController@getDevices']);
    Route::any('users/clients/get_permissions_table', ['as' => 'admin.clients.get_permissions_table', 'uses' => 'ClientsController@getPermissionsTable']);
    Route::resource('clients', 'ClientsController', ['except' => ['index']]);

    // Asaas Clients
    Route::get('asaas/clientes', 'AsaasClientesController@listarClientes')->name('asaas.clientes.listarClientes');
    //Route::get('asaas/contar', 'AsaasClientesController@contar')->name('asaas.clientes.contar');
    Route::get('asaas/clientes/create', 'AsaasClientesController@create')->name('asaas.clientes.create');
    Route::any('asaas/clientes/cadastrarCliente', 'AsaasClientesController@cadastrarCliente')->name('asaas.clientes.cadastrarCliente');
    //Route::post('asaas/clientes', 'AsaasClientesController@store')->name('asaas.clientes.store');
    Route::get('asaas/clientes/{id}/edit', 'AsaasClientesController@edit')->name('asaas.clientes.edit');
    Route::any('asaas/clientes/atualizarCliente/{id}', 'AsaasClientesController@atualizarCliente')->name('asaas.clientes.atualizarCliente');
    //Route::put('asaas/clientes/{id}', 'AsaasClientesController@update')->name('asaas.clientes.update');
    Route::get('asaas/clientes/{id}/delete', 'AsaasClientesController@delete')->name('asaas.clientes.delete');
    Route::any('asaas/clientes/excluirCliente', 'AsaasClientesController@excluirCliente')->name('asaas.clientes.excluirCliente');
   //

    // Asaas Billings
    Route::get('asaas/cobranças', 'AsaasClientesController@listarCobranças')->name('asaas.cobranças.listarCobranças');
    Route::get('asaas/cobranças/{id}/cobrar', 'AsaasClientesController@cobrar')->name('asaas.cobranças.cobrar');
    Route::any('asaas/cobranças/criarCobrança', 'AsaasClientesController@criarCobrança')->name('asaas.cobranças.criarCobrança');
    Route::get('asaas/cobranças/{id}/editCo', 'AsaasClientesController@editCo')->name('asaas.cobranças.editCo');
    Route::any('asaas/cobranças/atualizarCobrança/{id}', 'AsaasClientesController@atualizarCobrança')->name('asaas.cobranças.atualizarCobrança');
    Route::get('asaas/cobranças/{id}/delete', 'AsaasClientesController@deleteCo')->name('asaas.cobranças.deleteCo');
    Route::any('asaas/cobranças/excluirCobrança', 'AsaasClientesController@excluirCobrança')->name('asaas.cobranças.excluirCobrança');
    Route::get('asaas/cobranças/{id}/pagar', 'AsaasClientesController@pagar')->name('asaas.cobranças.pagar');
    Route::any('asaas/cobranças/{id}/receiveInCash', 'AsaasClientesController@receiveInCash')->name('asaas.cobranças.receiveInCash');

    // Login as
    Route::get('login_as/{id}', ['as' => 'admin.clients.login_as', 'uses' => 'ClientsController@loginAs']);
    Route::get('login_as_agree/{id}', ['as' => 'admin.clients.login_as_agree', 'uses' => 'ClientsController@loginAsAgree']);

    // Objects
    Route::any('users/objects', ['as' => 'admin.objects.index', 'uses' => 'ObjectsController@index']);
    Route::get('objects/import', ['as' => 'admin.objects.import', 'uses' => 'ObjectsController@import']);
    Route::post('objects/import', ['as' => 'admin.objects.import_set', 'uses' => 'ObjectsController@importSet']);
    Route::get('objects/do_destroy', ['as' => 'admin.objects.do_destroy', 'uses' => 'ObjectsController@doDestroy']);
    Route::resource('objects', 'ObjectsController', ['except' => ['index']]);

    //CHIPS
    Route::any('users/chips', ['as' => 'admin.chips.index', 'uses' => 'ChipsController@index']);
    Route::any('users/chips/upload', ['as' => 'admin.chips.upload', 'uses' => 'ChipsController@upload']);
    Route::any('users/chips/import', ['as' => 'admin.chips.import_filter', 'uses' => 'ChipsController@importar']);
    Route::any('users/chips/import_file', ['as' => 'admin.chips.import_file', 'uses' => 'ChipsController@importar_csv']);

    //monitoring
    Route::any('users/monitoring', ['as' => 'admin.monitoring.index', 'uses' => 'MonitoringsController@index']);
    Route::any('users/monitoring/page/{page?}/{search_item?}', ['as' => 'admin.monitoring.index', 'uses' => 'MonitoringsController@index']);
    Route::any('users/monitoring/create', ['as' => 'admin.monitoring.create', 'uses' => 'MonitoringsController@create']);
    Route::any('users/monitoring/store', ['as' => 'admin.monitoring.store', 'uses' => 'MonitoringsController@store']);
    Route::any('users/monitoring/edit/{id}', ['as' => 'admin.monitoring.edit', 'uses' => 'MonitoringsController@edit']);
    Route::any('users/monitoring/update', ['as' => 'admin.monitoring.update', 'uses' => 'MonitoringsController@update']);
    Route::any('users/monitoring/info/{id}', ['as' => 'admin.monitoring.info', 'uses' => 'MonitoringsController@info']);
    Route::any('users/monitoring/rem_add_alert/{id}', ['as' => 'admin.monitoring.rem_add_alert', 'uses' => 'MonitoringsController@rem_add_alert']);
    Route::any('users/monitoring/do_destroy', ['as' => 'admin.monitoring.do_destroy', 'uses' => 'MonitoringsController@dodestroy']);
    Route::any('users/monitoring/destroy', ['as' => 'admin.monitoring.destroy', 'uses' => 'MonitoringsController@destroy']);
    Route::any('users/monitoring/auto_store', ['as' => 'admin.monitoring.auto_store', 'uses' => 'MonitoringsController@auto_store']);

    //finanças
    Route::any('users/finacas/{search_item?}', ['as' => 'admin.financas.index', 'uses' => 'MontPayController@index']);

    //instalation and maintence Insta_maintController.php
    Route::any('users/Insta_maint', ['as' => 'admin.insta_maint.index', 'uses' => 'Insta_maintController@index']);
    Route::any('users/Insta_maint/page/{page?}/{search_item?}', ['as' => 'admin.insta_maint.index', 'uses' => 'Insta_maintController@index']);
    Route::any('users/Insta_maint/create', ['as' => 'admin.insta_maint.create', 'uses' => 'Insta_maintController@create']);
    Route::any('users/Insta_maint/store', ['as' => 'admin.insta_maint.store', 'uses' => 'Insta_maintController@store']);
    Route::any('users/Insta_maint/edit/{id}', ['as' => 'admin.insta_maint.edit', 'uses' => 'Insta_maintController@edit']);
    Route::any('users/Insta_maint/cancel/{id}', ['as' => 'admin.insta_maint.cancel', 'uses' => 'Insta_maintController@cancel']);
    Route::any('users/Insta_maint/canceled', ['as' => 'admin.insta_maint.canceled', 'uses' => 'Insta_maintController@canceled']);
    Route::any('users/Insta_maint/update', ['as' => 'admin.insta_maint.update', 'uses' => 'Insta_maintController@update']);
    Route::any('users/Insta_maint/do_destroy', ['as' => 'admin.insta_maint.do_destroy', 'uses' => 'Insta_maintController@dodestroy']);
    Route::any('users/Insta_maint/destroy', ['as' => 'admin.insta_maint.destroy', 'uses' => 'Insta_maintController@destroy']);
    Route::any('users/Insta_maint/os/{id}', ['as' => 'admin.insta_maint.os', 'uses' => 'Insta_maintController@os']);

    //estoque
    Route::any('users/Stock', ['as' => 'admin.Stock.index', 'uses' => 'StockController@index']);
    Route::any('users/Stock/page/{page?}/{search_item?}', ['as' => 'admin.Stock.index', 'uses' => 'StockController@index']);
    Route::any('users/Stock/create', ['as' => 'admin.Stock.create', 'uses' => 'StockController@create']);
    Route::any('users/Stock/store', ['as' => 'admin.Stock.store', 'uses' => 'StockController@store']);
    Route::any('users/Stock/edit/{id}', ['as' => 'admin.Stock.edit', 'uses' => 'StockController@edit']);
    Route::any('users/Stock/cancel/{id}', ['as' => 'admin.Stock.cancel', 'uses' => 'StockController@cancel']);
    Route::any('users/Stock/canceled', ['as' => 'admin.Stock.canceled', 'uses' => 'StockController@canceled']);
    Route::any('users/Stock/update', ['as' => 'admin.Stock.update', 'uses' => 'StockController@update']);
    Route::any('users/Stock/do_destroy', ['as' => 'admin.Stock.do_destroy', 'uses' => 'StockController@dodestroy']);
    Route::any('users/Stock/destroy', ['as' => 'admin.Stock.destroy', 'uses' => 'StockController@destroy']);
    Route::any('users/Stock/os/{id}', ['as' => 'admin.Stock.os', 'uses' => 'StockController@os']);

    //Técnicos (Technicians)
    Route::any('users/Technician', ['as' => 'admin.technician.index', 'uses' => 'TechnicianController@index']);
    Route::any('users/Technician/page/{page?}/{search_item?}', ['as' => 'admin.technician.index', 'uses' => 'TechnicianController@index']);
    Route::any('users/Technician/create', ['as' => 'admin.technician.create', 'uses' => 'TechnicianController@create']);
    Route::any('users/Technician/store', ['as' => 'admin.technician.store', 'uses' => 'TechnicianController@store']);
    Route::any('users/Technician/edit/{id}', ['as' => 'admin.technician.edit', 'uses' => 'TechnicianController@edit']);
    Route::any('users/Technician/update', ['as' => 'admin.technician.update', 'uses' => 'TechnicianController@update']);
    Route::any('users/Technician/do_destroy', ['as' => 'admin.technician.do_destroy', 'uses' => 'TechnicianController@dodestroy']);
    Route::any('users/Technician/destroy', ['as' => 'admin.technician.destroy', 'uses' => 'TechnicianController@destroy']);
    Route::any('users/Technician/auto_store', ['as' => 'admin.technician.auto_store', 'uses' => 'TechnicianController@auto_store']);

    //Central de mensagens (Messengers)
    Route::any('users/Messengers', ['as' => 'admin.messengers.index', 'uses' => 'MessengersController@index']);
    /*Route::any('users/Technician/page/{page?}/{search_item?}', ['as' => 'admin.technician.index', 'uses' => 'TechnicianController@index']);
    Route::any('users/Technician/create', ['as' => 'admin.technician.create', 'uses' => 'TechnicianController@create']);
    Route::any('users/Technician/store', ['as' => 'admin.technician.store', 'uses' => 'TechnicianController@store']);
    Route::any('users/Technician/edit/{id}', ['as' => 'admin.technician.edit', 'uses' => 'TechnicianController@edit']);
    Route::any('users/Technician/update', ['as' => 'admin.technician.update', 'uses' => 'TechnicianController@update']);
    Route::any('users/Technician/do_destroy', ['as' => 'admin.technician.do_destroy', 'uses' => 'TechnicianController@dodestroy']);
    Route::any('users/Technician/destroy', ['as' => 'admin.technician.destroy', 'uses' => 'TechnicianController@destroy']);
    Route::any('users/Technician/auto_store', ['as' => 'admin.technician.auto_store', 'uses' => 'TechnicianController@auto_store']);*/

    //Rastreadores (trackers)
    Route::any('users/Tracker', ['as' => 'admin.tracker.index', 'uses' => 'TrackerController@index']);
    Route::any('users/Tracker/page/{page?}/{search_item?}', ['as' => 'admin.tracker.index', 'uses' => 'TrackerController@index']);
    Route::any('users/Tracker/create', ['as' => 'admin.tracker.create', 'uses' => 'TrackerController@create']);
    Route::any('users/Tracker/store', ['as' => 'admin.tracker.store', 'uses' => 'TrackerController@store']);
    Route::any('users/Tracker/edit/{id}', ['as' => 'admin.tracker.edit', 'uses' => 'TrackerController@edit']);
    Route::any('users/Tracker/update', ['as' => 'admin.tracker.update', 'uses' => 'TrackerController@update']);
    Route::any('users/Tracker/do_destroy', ['as' => 'admin.tracker.do_destroy', 'uses' => 'TrackerController@dodestroy']);
    Route::any('users/Tracker/destroy', ['as' => 'admin.tracker.destroy', 'uses' => 'TrackerController@destroy']);
    Route::any('users/Tracker/auto_store', ['as' => 'admin.tracker.auto_store', 'uses' => 'TrackerController@auto_store']);

    //clientes (customers)
    Route::any('users/customer', ['as' => 'admin.customer.index', 'uses' => 'CustomerController@index']);
    Route::any('users/customer/page/{page?}/{search_item?}', ['as' => 'admin.customer.index', 'uses' => 'CustomerController@index']);
    Route::any('users/customer/create', ['as' => 'admin.customer.create', 'uses' => 'CustomerController@create']);
    Route::any('users/customer/store', ['as' => 'admin.customer.store', 'uses' => 'CustomerController@store']);
    Route::any('users/customer/edit/{id}', ['as' => 'admin.customer.edit', 'uses' => 'CustomerController@edit']);
    Route::any('users/customer/update', ['as' => 'admin.customer.update', 'uses' => 'CustomerController@update']);
    Route::any('users/customer/do_destroy', ['as' => 'admin.customer.do_destroy', 'uses' => 'CustomerController@dodestroy']);
    Route::any('users/customer/destroy', ['as' => 'admin.customer.destroy', 'uses' => 'CustomerController@destroy']);
    Route::any('users/customer/auto_store', ['as' => 'admin.customer.auto_store', 'uses' => 'CustomerController@auto_store']);

    // Main server settings
    Route::get('main_server_settings/index', ['as' => 'admin.main_server_settings.index', 'uses' => 'MainServerSettingsController@index']);
    Route::post('main_server_settings/logo_save', ['as' => 'admin.main_server_settings.logo_save', 'uses' => 'MainServerSettingsController@logoSave']);

    // Popups
    Route::get('popups/index', ['as' => 'admin.popups.index', 'uses' => 'PopupsController@index']);
    Route::resource('popups', 'PopupsController', ['except' => ['index']]);

    // Custom assets
    Route::get('custom/{asset}', ['as' => 'admin.custom.asset', 'uses' => 'CustomAssetsController@getCustomAsset']);
    Route::post('custom/{asset}', ['as' => 'admin.custom.asset_set', 'uses' => 'CustomAssetsController@setCustomAsset']);
});

// Payments
Route::any('payments/checkout/{plan_id}', ['as' => 'payments.checkout', 'uses' => 'Frontend\PaymentsController@getCheckout', 'middleware' => ['auth', 'check_password_updated']]);
Route::any('payments/get_done/{plan_id}/{user_id}', ['as' => 'payments.get_done', 'uses' => 'Frontend\PaymentsController@getPayment']);
Route::get('payments/get_cancel', ['as' => 'payments.get_cancel', 'uses' => 'Frontend\PaymentsController@getCancel']);
Route::get('subscriptions/renew', ['as' => 'subscriptions.renew', 'uses' => 'Frontend\SubscriptionsController@renew', 'middleware' => ['auth', 'check_password_updated']]);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'auth.admin', 'check_password_updated'], 'namespace' => 'Admin'], function () {
    // Billing
    Route::any('billing/index', ['as' => 'admin.billing.index', 'uses' => 'BillingController@index']);
    Route::any('billing/plans', ['as' => 'admin.billing.plans', 'uses' => 'BillingController@plans']);
    Route::post('billing/plan_store', ['as' => 'admin.billing.plan_store', 'uses' => 'BillingController@planStore']);
    Route::get('billing/billing_plans_form', ['as' => 'admin.billing.billing_plans_form', 'uses' => 'BillingController@billingPlansForm']);
    Route::resource('billing', 'BillingController', ['except' => ['index']]);

    // Events
    Route::any('events/index', ['as' => 'admin.events.index', 'uses' => 'EventsController@index']);
    Route::resource('events', 'EventsController', ['except' => ['index']]);

    // Email templates
    Route::any('email_templates/index', ['as' => 'admin.email_templates.index', 'uses' => 'EmailTemplatesController@index']);
    Route::resource('email_templates', 'EmailTemplatesController', ['except' => ['index', 'create', 'store']]);

    // Sms templates
    Route::any('sms_templates/index', ['as' => 'admin.sms_templates.index', 'uses' => 'SmsTemplatesController@index']);
    Route::resource('sms_templates', 'SmsTemplatesController', ['except' => ['index', 'create', 'store']]);

    // Sms gateway
    Route::get('sms_gateway/index', ['as' => 'admin.sms_gateway.index', 'uses' => 'SmsGatewayController@index']);
    Route::post('sms_gateway/store', ['as' => 'admin.sms_gateway.store', 'uses' => 'SmsGatewayController@store']);

    // Map icons
    Route::any('map_icons/index', ['as' => 'admin.map_icons.index', 'uses' => 'MapIconsController@index']);
    Route::resource('map_icons', 'MapIconsController', ['only' => ['store', 'destroy']]);

    // Device icons
    Route::any('device_icons/index', ['as' => 'admin.device_icons.index', 'uses' => 'DeviceIconsController@index']);
    Route::resource('device_icons', 'DeviceIconsController', ['except' => ['index']]);

    // Logs
    Route::any('logs/index', ['as' => 'admin.logs.index', 'uses' => 'LogsController@index']);
    Route::any('logs/search/{search_log?}', ['as' => 'admin.logs.search', 'uses' => 'LogsController@search']);
    Route::resource('logs', 'LogsController', ['only' => ['edit', 'destroy']]);

    // Unregistered devices log
    Route::any('unregistered_devices_log/index', ['as' => 'admin.unregistered_devices_log.index', 'uses' => 'UnregisteredDevicesLogController@index']);
    Route::resource('unregistered_devices_log', 'UnregisteredDevicesLogController', ['only' => ['destroy']]);

    // Restart traccar
    Route::any('restart_traccar', ['as' => 'admin.restart_traccar', 'uses' => 'ObjectsController@restartTraccar']);

    // Email settings
    Route::get('email_settings/index', ['as' => 'admin.email_settings.index', 'uses' => 'EmailSettingsController@index']);
    Route::post('email_settings/save', ['as' => 'admin.email_settings.save', 'uses' => 'EmailSettingsController@save']);
    Route::get('email_settings/test_email', ['as' => 'admin.email_settings.test_email', 'uses' => 'EmailSettingsController@testEmail']);
    Route::post('email_settings/test_email_send', ['as' => 'admin.email_settings.test_email_send', 'uses' => 'EmailSettingsController@testEmailSend']);

    // Main server settings
    Route::post('main_server_settings/save', ['as' => 'admin.main_server_settings.save', 'uses' => 'MainServerSettingsController@save']);
    Route::post('main_server_settings/new_user_defaults_save', ['as' => 'admin.main_server_settings.new_user_defaults_save', 'uses' => 'MainServerSettingsController@newUserDefaultsSave']);
    Route::post('main_server_settings/delete_geocoder_cache', ['as' => 'admin.main_server_settings.delete_geocoder_cache', 'uses' => 'MainServerSettingsController@deleteGeocoderCache']);

    // Backups
    Route::get('backups/index', ['as' => 'admin.backups.index', 'uses' => 'BackupsController@index']);
    Route::get('backups/panel', ['as' => 'admin.backups.panel', 'uses' => 'BackupsController@panel']);
    Route::post('backups/save', ['as' => 'admin.backups.save', 'uses' => 'BackupsController@save']);
    Route::get('backups/test', ['as' => 'admin.backups.test', 'uses' => 'BackupsController@test']);
    Route::get('backups/logs', ['as' => 'admin.backups.logs', 'uses' => 'BackupsController@logs']);

    // Ports
    Route::any('ports/index', ['as' => 'admin.ports.index', 'uses' => 'PortsController@index']);
    Route::get('ports/do_update_config', ['as' => 'admin.ports.do_update_config', 'uses' => 'PortsController@doUpdateConfig']);
    Route::post('ports/update_config', ['as' => 'admin.ports.update_config', 'uses' => 'PortsController@updateConfig']);
    Route::get('ports/do_reset_default', ['as' => 'admin.ports.do_reset_default', 'uses' => 'PortsController@doResetDefault']);
    Route::post('ports/reset_default', ['as' => 'admin.ports.reset_default', 'uses' => 'PortsController@resetDefault']);
    Route::resource('ports', 'PortsController', ['only' => ['edit', 'update']]);

    // Translations
    Route::get('translations/check_trans', ['as' => 'admin.translations.check_trans', 'uses' => 'TranslationsController@checkTrans']);
    Route::get('translations/file_trans', ['as' => 'admin.translations.file_trans', 'uses' => 'TranslationsController@fileTrans']);
    Route::post('translations/save', ['as' => 'admin.translations.save', 'uses' => 'TranslationsController@save']);
    Route::resource('translations', 'TranslationsController', ['only' => ['index', 'show', 'edit', 'update']]);

    // Languages
    Route::resource('languages', 'LanguagesController', ['only' => ['index', 'edit', 'update']]);

    // Report Logs
    Route::any('report_logs/index', ['as' => 'admin.report_logs.index', 'uses' => 'ReportLogsController@index']);
    Route::resource('report_logs', 'ReportLogsController', ['only' => ['edit', 'destroy']]);

    // Sensor groups
    Route::any('sensor_groups/index', ['as' => 'admin.sensor_groups.index', 'uses' => 'SensorGroupsController@index']);
    Route::resource('sensor_groups', 'SensorGroupsController', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);

    Route::any('sensor_group_sensors/index/{id}/{ajax?}', ['as' => 'admin.sensor_group_sensors.index', 'uses' => 'SensorGroupSensorsController@index']);
    Route::get('sensor_group_sensors/create/{id}', ['as' => 'admin.sensor_group_sensors.create', 'uses' => 'SensorGroupSensorsController@create']);
    Route::resource('sensor_group_sensors', 'SensorGroupSensorsController', ['only' => ['store', 'edit', 'update', 'destroy']]);

    // Blocked ips
    Route::any('blocked_ips/index', ['as' => 'admin.blocked_ips.index', 'uses' => 'BlockedIpsController@index']);
    Route::get('ports/do_destroy/{id}', ['as' => 'admin.blocked_ips.do_destroy', 'uses' => 'BlockedIpsController@doDestroy']);
    Route::resource('blocked_ips', 'BlockedIpsController', ['only' => ['create', 'store', 'destroy']]);

    // Tools
    Route::any('tools/index', ['as' => 'admin.tools.index', 'uses' => 'ToolsController@index']);

    // DB clear
    Route::any('db_clear/panel', ['as' => 'admin.db_clear.panel', 'uses' => 'DatabaseClearController@panel']);
    Route::post('db_clear/save', ['as' => 'admin.db_clear.save', 'uses' => 'DatabaseClearController@save']);

    // Plugins
    Route::any('plugins/index', ['as' => 'admin.plugins.index', 'uses' => 'PluginsController@index']);
    Route::post('plugins/save', ['as' => 'admin.plugins.save', 'uses' => 'PluginsController@save']);
});

// API

Route::get('login/app/{email}/{password}', ['as' => 'api.loginapp', 'uses' => 'Frontend\ApiController@loginApp', 'middleware' => ['api_active', 'throttle:60,1', 'check_password_updated']]);
Route::get('api/save_token/{id}/{token}/{code_security}', ['as' => 'save_token', 'uses' => 'Frontend\ApiController@save_token']);

//Route::any('api/login/app/', 'Frontend\ApiController@loginAppPost');

Route::any('api/login', ['as' => 'api.login', 'uses' => 'Frontend\ApiController@login', 'middleware' => ['api_active', 'throttle:60,1', 'check_password_updated']]);
Route::any('api/geo_address', ['as' => 'api.geo_address', 'uses' => 'Frontend\ApiController@geoAddress']);
Route::group(['prefix' => 'api', 'middleware' => ['api_auth', 'active_subscription', 'api_active', 'check_password_updated'], 'namespace' => 'Frontend'], function () {
    Route::any('get_devices', ['as' => 'api.get_devices', 'uses' => 'ApiController@getDevices']);
    Route::any('get_devices_latest', ['as' => 'api.get_devices_json', 'uses' => 'ApiController@getDevicesJson']);

    //api sistema de Rafael
    /*Route::get('api/listar/dispositivos/app/{iduser}', ['as' => 'api.listardispositivos', 'uses' => 'Frontend\ApiController@listarDispositivos', 'middleware' => ['api_active', 'throttle:60,1']]);
    Route::get('teste/mapa', ['as' => 'api.testemap', 'uses' => 'Frontend\ApiController@testemapa', 'middleware' => ['api_active', 'throttle:60,1']]);
    Route::get('registration_status', function() {
        return ['status' => settings('main_settings.allow_users_registration') ? 1 : 0];    });
    Route::get('obtemdispositivos/app/{iduser}/{latitudes}/{longitudes}', ['as' => 'api.obtemdispositivosapp', 'uses' => 'Frontend\ApiController@obtemdispositivosapp', 'middleware' => ['api_active', 'throttle:60,1']]);
    Route::get('/api/obtemposicaoatual/app/{user_id}/{device_id}', ['as' => 'api.obtemposicaoatualapp', 'uses' => 'Frontend\ApiController@obtemposicaoatualapp', 'middleware' => ['api_active', 'throttle:60,1']]);
    Route::get('/api/obtemposicaoatualrefresh/app/{user_id}/{device_id}/{latitude}/{longitude}', ['as' => 'api.obtemposicaoatualrefreshapp', 'uses' => 'Frontend\ApiController@obtemposicaoatualrefreshapp', 'middleware' => ['api_active', 'throttle:60,1']]);*/

    Route::any('add_device_data', ['as' => 'api.add_device_data', 'uses' => 'ApiController@DevicesController#create']);
    Route::any('add_device', ['as' => 'api.add_device', 'uses' => 'ApiController@DevicesController#store']);
    Route::any('edit_device_data', ['as' => 'api.edit_device_data', 'uses' => 'ApiController@DevicesController#edit']);
    Route::any('edit_device', ['as' => 'api.edit_device', 'uses' => 'ApiController@DevicesController#update']);
    Route::any('change_active_device', ['as' => 'api.change_active_device', 'uses' => 'ApiController@DevicesController#changeActive']);
    Route::any('destroy_device', ['as' => 'api.destroy_device', 'uses' => 'ApiController@DevicesController#destroy']);
    Route::get('change_alarm_status', ['as' => 'api.change_alarm_status', 'uses' => 'ApiController@ObjectsController#changeAlarmStatus']);
    Route::get('device_stop_time', ['as' => 'api.device_stop_time', 'uses' => 'ApiController@DevicesController#stopTime']);
    Route::get('alarm_position', ['as' => 'api.alarm_position', 'uses' => 'ApiController@ObjectsController#alarmPosition']);
    Route::any('set_device_expiration', ['as' => 'api.set_device_expiration', 'uses' => 'ApiController@setDeviceExpiration']);
    Route::any('get_device_commands', ['as' => 'api.get_device_commands', 'uses' => 'SendCommandController@getCommands']);
    Route::get('enable_device', ['as' => 'api.enable_device_active', 'uses' => 'ApiController@enableDeviceActive']);
    Route::get('disable_device', ['as' => 'api.disable_device_active', 'uses' => 'ApiController@disableDeviceActive']);

    Route::any('get_sensors', ['as' => 'api.get_sensors', 'uses' => 'ApiController@SensorsController#index']);
    Route::any('add_sensor_data', ['as' => 'api.add_sensor_data', 'uses' => 'ApiController@SensorsController#create']);
    Route::any('add_sensor', ['as' => 'api.add_sensor', 'uses' => 'ApiController@SensorsController#store']);
    Route::any('edit_sensor_data', ['as' => 'api.edit_sensor_data', 'uses' => 'ApiController@SensorsController#edit']);
    Route::any('edit_sensor', ['as' => 'api.edit_sensor', 'uses' => 'ApiController@SensorsController#update']);
    Route::any('destroy_sensor', ['as' => 'api.destroy_sensor', 'uses' => 'ApiController@SensorsController#destroy']);
    Route::any('get_protocols', ['as' => 'api.get_protocols', 'uses' => 'ApiController@SensorsController#getProtocols']);
    Route::any('get_events_by_protocol', ['as' => 'api.get_events_by_protocol', 'uses' => 'ApiController@SensorsController#getEvents']);

    Route::any('get_services', ['as' => 'api.get_services', 'uses' => 'ApiController@ServicesController#index']);
    Route::any('add_service_data', ['as' => 'api.add_service_data', 'uses' => 'ApiController@ServicesController#create']);
    Route::any('add_service', ['as' => 'api.add_service', 'uses' => 'ApiController@ServicesController#store']);
    Route::any('edit_service_data', ['as' => 'api.edit_service_data', 'uses' => 'ApiController@ServicesController#edit']);
    Route::any('edit_service', ['as' => 'api.edit_service', 'uses' => 'ApiController@ServicesController#update']);
    Route::any('destroy_service', ['as' => 'api.destroy_service', 'uses' => 'ApiController@ServicesController#destroy']);

    Route::any('get_events', ['as' => 'api.get_events', 'uses' => 'ApiController@EventsController#index']);
    Route::any('destroy_events', ['as' => 'api.destroy_events', 'uses' => 'ApiController@EventsController#destroy']);

    Route::any('get_history', ['as' => 'api.get_history', 'uses' => 'ApiController@HistoryController#index']);
    Route::any('get_history_messages', ['as' => 'api.get_history_messages', 'uses' => 'ApiController@HistoryController#positionsPaginated']);
    Route::any('delete_history_positions', ['as' => 'api.delete_history_positions', 'uses' => 'ApiController@HistoryController#deletePositions']);

    Route::any('get_alerts', ['as' => 'api.get_alerts', 'uses' => 'ApiController@AlertsController#index']);
    Route::any('add_alert_data', ['as' => 'api.add_alert_data', 'uses' => 'ApiController@AlertsController#create']);
    Route::any('add_alert', ['as' => 'api.add_alert', 'uses' => 'ApiController@AlertsController#store']);
    Route::any('edit_alert_data', ['as' => 'api.edit_alert_data', 'uses' => 'ApiController@AlertsController#edit']);
    Route::any('edit_alert', ['as' => 'api.edit_alert', 'uses' => 'ApiController@AlertsController#update']);
    Route::any('change_active_alert', ['as' => 'api.change_active_alert', 'uses' => 'ApiController@AlertsController#changeActive']);
    Route::any('destroy_alert', ['as' => 'api.destroy_alert', 'uses' => 'ApiController@AlertsController#destroy']);
    Route::any('set_alert_devices', ['as' => 'api.set_alert_devices', 'uses' => 'ApiController@AlertsController#syncDevices']);
    Route::get('get_alerts_commands', ['as' => 'api.get_alerts_commands', 'uses' => 'ApiController@AlertsController#getCommands']);

    Route::any('get_geofences', ['as' => 'api.get_geofences', 'uses' => 'ApiController@GeofencesController#index']);
    Route::any('add_geofence_data', ['as' => 'api.add_geofence_data', 'uses' => 'ApiController@GeofencesController#create']);
    Route::any('add_geofence', ['as' => 'api.add_geofence', 'uses' => 'ApiController@GeofencesController#store']);
    Route::any('edit_geofence', ['as' => 'api.edit_geofence', 'uses' => 'ApiController@GeofencesController#update']);
    Route::any('change_active_geofence', ['as' => 'api.change_active_geofence', 'uses' => 'ApiController@GeofencesController#changeActive']);
    Route::any('destroy_geofence', ['as' => 'api.destroy_geofence', 'uses' => 'ApiController@GeofencesController#destroy']);

    Route::any('get_routes', ['as' => 'api.get_routes', 'uses' => 'ApiController@RoutesController#index']);
    Route::any('add_route', ['as' => 'api.add_route', 'uses' => 'ApiController@RoutesController#store']);
    Route::any('edit_route', ['as' => 'api.edit_route', 'uses' => 'ApiController@RoutesController#update']);
    Route::any('change_active_route', ['as' => 'api.change_active_route', 'uses' => 'ApiController@RoutesController#changeActive']);
    Route::any('destroy_route', ['as' => 'api.destroy_route', 'uses' => 'ApiController@RoutesController#destroy']);

    Route::any('get_reports', ['as' => 'api.get_reports', 'uses' => 'ApiController@ReportsController#index']);
    Route::any('add_report_data', ['as' => 'api.add_report_data', 'uses' => 'ApiController@ReportsController#create']);
    Route::any('add_report', ['as' => 'api.add_report', 'uses' => 'ApiController@ReportsController#store']);
    Route::any('edit_report', ['as' => 'api.edit_report', 'uses' => 'ApiController@ReportsController#store']);
    Route::any('generate_report', ['as' => 'api.generate_report', 'uses' => 'ApiController@ReportsController#update']);
    Route::any('destroy_report', ['as' => 'api.destroy_report', 'uses' => 'ApiController@ReportsController#destroy']);
    Route::any('get_reports_types', ['as' => 'api.get_reports_types', 'uses' => 'ApiController@ReportsController#getTypes']);

    Route::any('get_user_map_icons', ['as' => 'api.get_user_map_icons', 'uses' => 'ApiController@MapIconsController#index']);
    Route::any('get_map_icons', ['as' => 'api.get_map_icons', 'uses' => 'ApiController@MapIconsController#getIcons']);
    Route::any('add_map_icon', ['as' => 'api.add_map_icon', 'uses' => 'ApiController@MapIconsController#store']);
    Route::any('edit_map_icon', ['as' => 'api.edit_map_icon', 'uses' => 'ApiController@MapIconsController#update']);
    Route::any('change_active_map_icon', ['as' => 'api.change_active_map_icon', 'uses' => 'ApiController@MapIconsController#changeActive']);
    Route::any('destroy_map_icon', ['as' => 'api.destroy_map_icon', 'uses' => 'ApiController@MapIconsController#destroy']);

    Route::any('send_command_data', ['as' => 'api.send_command_data', 'uses' => 'ApiController@SendCommandController#create']);
    Route::any('send_sms_command', ['as' => 'api.send_sms_command', 'uses' => 'ApiController@SendCommandController#store']);
    Route::any('send_gprs_command', ['as' => 'api.send_gprs_command', 'uses' => 'ApiController@SendCommandController#gprsStore']);

    /*Route::any('add_my_icon_data', ['as' => 'api.add_my_icons_data', 'uses' => 'ApiController@addMyIconsData']);
    Route::any('add_my_icon', ['as' => 'api.add_my_icons', 'uses' => 'ApiController@addMyIcons']);
    Route::any('destroy_my_icon', ['as' => 'api.destroy_my_icons', 'uses' => 'ApiController@destroyMyIcons']);*/

    Route::any('edit_setup_data', ['as' => 'api.edit_setup_data', 'uses' => 'ApiController@MyAccountSettingsController#edit']);
    Route::any('edit_setup', ['as' => 'api.edit_setup', 'uses' => 'ApiController@MyAccountSettingsController#update']);

    Route::any('get_user_drivers', ['as' => 'api.get_user_drivers', 'uses' => 'ApiController@UserDriversController#index']);
    Route::any('add_user_driver_data', ['as' => 'api.add_user_driver_data', 'uses' => 'ApiController@UserDriversController#create']);
    Route::any('add_user_driver', ['as' => 'api.add_user_driver', 'uses' => 'ApiController@UserDriversController#store']);
    Route::any('edit_user_driver_data', ['as' => 'api.edit_user_driver_data', 'uses' => 'ApiController@UserDriversController#edit']);
    Route::any('edit_user_driver', ['as' => 'api.edit_user_driver', 'uses' => 'ApiController@UserDriversController#update']);
    Route::any('destroy_user_driver', ['as' => 'api.destroy_user_driver', 'uses' => 'ApiController@UserDriversController#destroy']);

    Route::any('get_custom_events', ['as' => 'api.get_custom_events', 'uses' => 'ApiController@CustomEventsController#index']);
    Route::any('get_custom_events_by_device', ['as' => 'api.get_events_by_device', 'uses' => 'ApiController@CustomEventsController#getEventsByDevices']);
    Route::any('add_custom_event_data', ['as' => 'api.add_custom_event_data', 'uses' => 'ApiController@CustomEventsController#create']);
    Route::any('add_custom_event', ['as' => 'api.add_custom_event', 'uses' => 'ApiController@CustomEventsController#store']);
    Route::any('edit_custom_event_data', ['as' => 'api.edit_custom_event_data', 'uses' => 'ApiController@CustomEventsController#edit']);
    Route::any('edit_custom_event', ['as' => 'api.edit_custom_event', 'uses' => 'ApiController@CustomEventsController#update']);
    Route::any('destroy_custom_event', ['as' => 'api.destroy_custom_event', 'uses' => 'ApiController@CustomEventsController#destroy']);

    Route::any('send_test_sms', ['as' => 'api.send_test_sms', 'uses' => 'ApiController@SmsGatewayController#sendTestSms']);

    Route::any('get_user_gprs_templates', ['as' => 'api.get_user_gprs_templates', 'uses' => 'ApiController@UserGprsTemplatesController#index']);
    Route::any('add_user_gprs_template_data', ['as' => 'api.add_user_gprs_template', 'uses' => 'ApiController@UserGprsTemplatesController#create']);
    Route::any('add_user_gprs_template', ['as' => 'api.add_user_gprs_template', 'uses' => 'ApiController@UserGprsTemplatesController#store']);
    Route::any('edit_user_gprs_template_data', ['as' => 'api.edit_user_gprs_template_data', 'uses' => 'ApiController@UserGprsTemplatesController#edit']);
    Route::any('edit_user_gprs_template', ['as' => 'api.edit_user_gprs_template', 'uses' => 'ApiController@UserGprsTemplatesController#update']);
    Route::any('get_user_gprs_message', ['as' => 'api.get_user_gprs_message', 'uses' => 'ApiController@UserGprsTemplatesController#getMessage']);
    Route::any('destroy_user_gprs_template', ['as' => 'api.destroy_user_gprs_template', 'uses' => 'ApiController@UserGprsTemplatesController#destroy']);

    Route::any('get_user_sms_templates', ['as' => 'api.get_user_sms_templates', 'uses' => 'ApiController@UserSmsTemplatesController#index']);
    Route::any('add_user_sms_template_data', ['as' => 'api.add_user_sms_template', 'uses' => 'ApiController@UserSmsTemplatesController#create']);
    Route::any('add_user_sms_template', ['as' => 'api.add_user_sms_template', 'uses' => 'ApiController@UserSmsTemplatesController#store']);
    Route::any('edit_user_sms_template_data', ['as' => 'api.edit_user_sms_template_data', 'uses' => 'ApiController@UserSmsTemplatesController#edit']);
    Route::any('edit_user_sms_template', ['as' => 'api.edit_user_sms_template', 'uses' => 'ApiController@UserSmsTemplatesController#update']);
    Route::any('get_user_sms_message', ['as' => 'api.get_user_sms_message', 'uses' => 'ApiController@UserSmsTemplatesController#getMessage']);
    Route::any('destroy_user_sms_template', ['as' => 'api.destroy_user_sms_template', 'uses' => 'ApiController@UserSmsTemplatesController#destroy']);

    Route::any('get_user_data', ['as' => 'api.get_user_data', 'uses' => 'ApiController@getUserData']);

    Route::any('register', ['as' => 'api.register', 'uses' => 'ApiController@RegistrationController#store']);
    Route::any('change_password', ['as' => 'api.change_password', 'uses' => 'ApiController@RegistrationController#changePassword']);

    Route::any('get_sms_events', ['as' => 'api.get_sms_events', 'uses' => 'ApiController@getSmsEvents']);

    Route::any('fcm_token', ['as' => 'api.fcm_token', 'uses' => 'ApiController@setFcmToken']);
    Route::any('services_keys', ['as' => 'api.services_keys', 'uses' => 'ApiController@getServicesKeys']);
});

Route::group(['prefix' => 'api/v2', 'middleware' => ['api_active', 'check_password_updated']], function () {
    Route::group(['prefix' => 'tracker', 'middleware' => ['tracker_auth', 'check_password_updated']], function () {
        Route::any('login', ['as' => 'tracker.login', 'uses' => 'Frontend\Tracker\ApiController@login']);
        Route::get('tasks', ['as' => 'tracker.task.index', 'uses' => 'Frontend\Tracker\TasksController@getTasks']);
        Route::get('tasks/statuses', ['as' => 'tracker.task.statuses', 'uses' => 'Frontend\Tracker\TasksController@getStatuses']);
        Route::put('tasks/{id}', ['as' => 'tracker.task.update', 'uses' => 'Frontend\Tracker\TasksController@update']);
        Route::get('tasks/signature/{taskStatusId}', ['as' => 'tracker.task.signature', 'uses' => 'Frontend\Tracker\TasksController@getSignature']);

        Route::get('chat/init', ['as' => 'tracker.chat.init', 'uses' => 'Frontend\Tracker\ChatController@initChat']);
        Route::get('chat/users', ['as' => 'tracker.chat.users', 'uses' => 'Frontend\Tracker\ChatController@getChattableObjects']);
        Route::get('chat/messages', ['as' => 'tracker.chat.messages', 'uses' => 'Frontend\Tracker\ChatController@getMessages']);
        Route::post('chat/message', ['as' => 'tracker.chat.message', 'uses' => 'Frontend\Tracker\ChatController@createMessage']);
    });
});

Route::group(['prefix' => 'api/admin', 'middleware' => ['api_auth', 'active_subscription', 'api_active', 'auth.admin', 'check_password_updated']], function () {
    Route::post('client', ['as' => 'api.admin.client.store', 'uses' => 'Admin\ClientsController@store']);
});

Route::any('api/insert_position', ['uses' => 'Frontend\PositionsController@insert']);

Route::group([], function () {
    Route::get('streetview.jpg', ['as' => 'streetview', 'uses' => function (Illuminate\Http\Request $request, Tobuli\Services\StreetviewService $streetviewService) {
        try {
            $location = $request->get('location');
            $size = $request->get('size');
            $heading = $request->get('heading');

            $image = $streetviewService->getImage($location, $size, $heading);

            $response = Response::make($image);
            $response->header('Content-Type', 'image/jpeg');

            return $response;
        } catch (Exception $e) {
            $image = public_path('assets/images/no-streetview.jpg');

            if (file_exists(public_path('assets/images/no-streetview-'.$size.'.jpg'))) {
                $image = public_path('assets/images/no-streetview-'.$size.'.jpg');
            }

            $response = Response::make(file_get_contents($image));
            $response->header('Content-Type', 'image/jpeg');

            return $response;
        }
    }]);
});

// Login as
Route::get('kjadiagdiogb', ['as' => 'loginas', 'uses' => 'Frontend\LoginController@loginAs']);
Route::post('kjadiagdiogbpost', ['as' => 'loginaspost', 'uses' => 'Frontend\LoginController@loginAsPost']);

/* Route::get('/testing', ['as' => 'testing', 'uses' => function () {}]);

Route::get('/teste', ['as' => 'teste', 'uses' => function () {echo 'teste2';}]); */

//Route::post('authentication/store/app', 'Frontend\LoginController@storeApp');
Route::get('authentication/store/app/{email}/{senha}', 'Frontend\LoginController@storeApp');

// Autologin
Route::get('autologin/{token}', ['as' => 'autologin', 'uses' => '\Watson\Autologin\AutologinController@autologin']);

Route::get('testex', 'Frontend\LoginController@testex');
// Objects
Route::any('users/objects/app', ['as' => 'objects.indexApp', 'uses' => 'Frontend\ObjectsController@indexApp']);

// Cookies
Route::get('/cookie/set/{name}/{value}', ['as' => 'set_cookie', 'uses' => 'CookieController@setCookie']);
Route::get('/cookie/get/{name}', ['as' => 'get_cookie', 'uses' => 'CookieController@getCookie']);

//Route::any('users/monitoring', ['as' => 'admin.monitoring.index', 'uses' => 'MonitoringController@index']);
//
