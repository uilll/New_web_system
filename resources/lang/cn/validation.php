<?php

return array(

    'accepted' => ':attribute：属性

该属性必须被接受。',
    'active_url' => ':attribute：属性
URL: 网址

 该属性不是有效的网址。',
    'after' => ' :attribute: 属性
:date:日期

该属性必须是一个日期之后的：日期',
    'alpha' => ' :attribute: 属性

该属性只能包含字母',
    'alpha_dash' => ':attribute:该属性

该属性只能包含字母，数字，破折号。',
    'alpha_num' => ':attribute:该属性

该属性只能包含字母和数字',
    'array' => ':attribute:该属性

该属性必须是一个数组。',
    'before' => ':attribute:该属性
:date:日期

该属性必须是一个日期之前的：日期。',
    'between'  => array(
        'numeric' => ':attribute:该属性 :min：最少 :max:最多

该属性必须介于最少和最多之间。',
        'file' => ':attribute:该属性 :min：最少 :max:最多

该属性必须介于最少和最多千位元组之间。',
        'string' => ':attribute:该属性
:min：最少
:max:最多',
        'array' => ':attribute:该属性 :min：最少 :max:最多

该属性的项目必须介于最少和最多之间。',
    ),
    'confirmed' => ':attribute:该属性

该属性确认不匹配。',
    'date' => ':attribute:该属性

该属性不是有效的日期。',
    'date_format' => ':attribute:该属性 
:format：格式

该属性与格式不匹配：格式。',
    'different' => ':attribute:该属性 
:other:其他

该属性与其他的必须不一样。',
    'digits' => ':attribute:该属性
:digits:数字

该属性必须是数字：数字。',
    'digits_between' => ':attribute:该属性 :min：最少 :max:最多

该属性必须介于最少和最多数字之间。',
    'email' => ':attribute:该属性

该属性必须是有效的电子邮件地址。',
    'exists' => ':attribute:该属性

该被选择的属性无效。',
    'image' => ':attribute:该属性

该属性必须是个图像。',
    'in' => ':attribute:该属性 

该被选择的属性无效。',
    'integer' => ':attribute:该属性 

该属性必须是一个整数。',
    'ip' => ':attribute:该属性 

该属性必须是有效的IP地址。

',
    'max'  => array(
        'numeric' => ':attribute:该属性  
:max:最大

该属性不会大于:最大。',
        'file' => ':attribute:该属性 
:max:最大 

该属性不可以大于：最大千位元组。',
        'string' => ':attribute:该属性 
:max:最大 

该属性不可以大于：最大千位元组。',
        'array' => ':attribute:该属性 
:max:最大 

该属性不可以超过：最大项目。',
    ),
    'mimes' => ':attribute:该属性 
:values:价值

该属性必须是类型为一个文件：价值。',
    'min'  => array(
        'numeric' => ':attribute:该属性 
:min:最少

该属性必须至少：最少。',
        'file' => ':attribute:该属性 
:min:最少

该属性必须至少位最少千位元数。',
        'string' => ':attribute:该属性 
:min:最少

该属性必须至少：最少字符。',
        'array' => ':attribute:该属性 
:min:最少

该属性必须至少：最少项目。',
    ),
    'not_in' => ':attribute:该属性 
该被选择的属性无效。',
    'numeric' => ':attribute:该属性 

该属性必须是一个数字。',
    'regex' => ' :attribute：该属性

该属性格式无效。',
    'required' => ' :attribute：该属性

该属性字段是必须的。',
    'required_if' => ' :attribute：该属性

该属性字段是必需的。',
    'required_with' => ':attribute:该属性
:values:价值

该属性字段必需的当：价值存在。',
    'required_with_all' => ':attribute:该属性 :values:价值 该属性字段必需的当：价值存在。',
    'required_without' => ':attribute:该属性 :values:价值 该属性字段必需的当：价值不存在。',
    'required_without_all' => ':attribute:该属性 :values:价值 该属性字段必须的当没有：价值存在。',
    'same' => ':attribute:该属性 :other:其他
该属性与其他必须匹配',
    'size'  => array(
        'numeric' => ':attribute:该属性
:size:大小

该属性必须是：大小。',
        'file' => ':attribute:该属性 :size:大小 

该属性必须是：大小千位元组。',
        'string' => ':attribute:该属性 :size:大小 

该属性必须是：大小字符。',
        'array' => ':attribute:该属性 :size:大小 

该属性必须包含：大小项目。',
    ),
    'unique' => ':attribute:该属性 
该属性已被采用。',
    'url' => ':attribute:该属性

该属性格式无效。',
    'array_max' => ':attribute:该属性 
:max:最大

该属性最大项目：最大。',
    'lesser_than' => ':attribute:该属性 
:other:其他

该属性必须少于：其他。',
    'custom'  => array(
        'attribute-name'  => array(
            'rule-name' => '定制消息',
        ),
    ),
    'attributes'  => array(
        'email' => '电子邮件',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'remember_me' => '记住我',
        'name' => '名称',
        'imei' => 'IMEI',
        'imei_device' => 'IMEI或设备标识符',
        'fuel_measurement_type' => '燃料测量',
        'fuel_cost' => '燃料成本',
        'icon_id' => '设备图标',
        'active' => '活跃',
        'polygon_color' => '背景颜色',
        'devices' => '设备',
        'geofences' => '地理围栏',
        'overspeed' => '超速',
        'fuel_consumption' => '燃油消耗',
        'description' => '描述',
        'map_icon_id' => '标记图标',
        'coordinates' => '地图点',
        'date_from' => '日期从',
        'date_to' => '日期至',
        'code' => '码',
        'title' => '标题',
        'note' => '内容',
        'path' => '文件',
        'period_name' => '周期名称',
        'days' => '天',
        'devices_limit' => '设备限制',
        'trial' => '审讯',
        'price' => '价钱',
        'message' => '信息',
        'tag' => '参数',
        'timezone_id' => '时区',
        'unit_of_distance' => '距离单位',
        'unit_of_capacity' => '容量单位',
        'user' => '用户',
        'group_id' => '组',
        'permission_to_add_devices' => '一个两个添加设备',
        'unit_of_altitude' => '海拔单位',
        'sms_gateway_url' => '短信网关网址',
        'mobile_phone' => '移动电话',
        'permission_to_use_sms_gateway' => '短信网关',
        'loged_at' => '上次登录',
        'manager_id' => '经理',
        'sim_number' => 'SIM卡号码',
        'device_model' => '设备模型',
        'rfid' => 'RFID',
        'phone' => '电话',
        'device_id' => '设备',
        'tag_value' => '参数值',
        'device_port' => '设备端口',
        'event' => '事件',
        'port' => '港口',
        'device_protocol' => '设备协议',
        'protocol' => '协议',
        'sensor_name' => '传感器名称',
        'sensor_type' => '传感器类型',
        'sensor_template' => '传感器模板',
        'tag_name' => '参数名称',
        'min_value' => '最小值',
        'max_value' => '最大。值',
        'on_value' => '开值',
        'off_value' => '关值',
        'shown_value_by' => '显示值',
        'full_tank_value' => '参数值',
        'formula' => '格式',
        'parameters' => '参数',
        'full_tank' => '满罐以升/加仑计',
        'fuel_tank_name' => '油箱名称',
        'odometer_value' => '值',
        'odometer_value_by' => '里程表',
        'unit_of_measurement' => '测量单位',
        'plate_number' => '车牌号码',
        'vin' => 'VIN',
        'registration_number' => '注册/资产编号',
        'object_owner' => '对象所有者/管理者',
        'additional_notes' => '补充笔记',
        'expiration_date' => '截止日期',
        'days_to_remind' => '到期前提醒的日子',
        'type' => '类型',
        'format' => '格式',
        'show_addresses' => '显示地址',
        'stops' => '停止',
        'speed_limit' => '速度极限',
        'zones_instead' => '区域而不是地址',
        'daily' => '每天',
        'weekly' => '每周',
        'send_to_email' => '发送到电子邮件',
        'filter' => '过滤',
        'status' => '状态',
        'date' => '日期',
        'geofence_name' => '地理栅栏名称',
        'tail_color' => '尾巴颜色',
        'tail_length' => '尾巴长度',
        'engine_hours' => '引擎小时',
        'detect_engine' => ' 通过检测引擎的开/关',
        'min_moving_speed' => ' 最小。以公里/小时为单位的移动速度',
        'min_fuel_fillings' => '最小。燃料差异来检测燃料填充物。',
        'min_fuel_thefts' => '最小。燃料差异来检测燃料盗窃',
        'expiration_by' => '到期',
        'interval' => '间隔',
        'last_service' => '上次服务',
        'trigger_event_left' => '当离开时触发事件',
        'current_odometer' => '当前的里程表',
        'current_engine_hours' => '当前引擎小时数',
        'renew_after_expiration' => '到期后续订',
        'sms_template_id' => '短信模板',
        'frequency' => '频率',
        'unit' => '单位',
        'noreply_email' => '没有回复邮箱地址',
        'signature' => '签名',
        'use_smtp_server' => '使用SMTP服务器',
        'smtp_server_host' => 'SMTP服务器主机',
        'smtp_server_port' => 'SMTP服务器端口',
        'smtp_security' => 'SMTP安全性',
        'smtp_username' => 'SMTP用户名',
        'smtp_password' => 'SMTP密码',
        'from_name' => '来自名字',
        'icons' => '图标',
        'server_name' => '服务器名称',
        'available_maps' => '可用的地图',
        'default_language' => '默认语言',
        'default_timezone' => '默认时区',
        'default_unit_of_distance' => '距离的默认单位',
        'default_unit_of_capacity' => '容量的默认单位',
        'default_unit_of_altitude' => '高度的默认单位',
        'default_date_format' => '默认日期格式',
        'default_time_format' => '默认时间格式',
        'default_map' => '默认地图',
        'default_object_online_timeout' => '默认对象联机超时',
        'logo' => '商标',
        'login_page_logo' => '登录页面徽标',
        'frontpage_logo' => '首页徽标',
        'favicon' => '网站图标',
        'allow_users_registration' => '允许用户注册',
        'frontpage_logo_padding_top' => '首页徽标填充顶部',
        'default_maps' => '默认地图',
        'subscription_expiration_after_days' => '几天后订阅到期',
        'gprs_template_id' => 'GPRS模板',
        'calibrations' => '校准',
        'ftp_server' => 'FTP服务器',
        'ftp_port' => 'FTP端口',
        'ftp_username' => 'FTP用户名',
        'ftp_password' => 'FTP密码',
        'ftp_path' => 'FTP路径',
        'period' => '期',
        'hour' => '小时',
        'color' => '颜色',
        'polyline' => '路线',
        'request_method' => '请求方法',
        'authentication' => '认证',
        'username' => '用户名',
        'encoding' => '编码',
        'time_adjustment' => '时间调整',
        'parameter' => '参数',
        'export_type' => '导出类型',
        'groups' => '组',
        'file' => '文件',
        'extra' => '额外',
        'parameter_value' => '参数值',
        'enable_plans' => '启用计划',
        'payment_type' => '付款方式',
        'paypal_client_id' => '贝宝客户端ID',
        'paypal_secret' => '贝宝秘密',
        'paypal_currency' => '贝宝货币',
        'paypal_payment_name' => '贝宝付款名称',
        'objects' => '对象',
        'duration_value' => '持续时间',
        'permissions' => '权限',
        'plan' => '计划',
        'default_billing_plan' => '默认计费计划',
        'sensor_group_id' => '传感器组',
        'daylight_saving_time' => '夏令时',
        'phone_number' => '电话号码',
        'action' => '行动',
        'time' => '时间',
        'order' => '订购',
        'geocoder_api' => '地理编码器API',
        'geocoder_cache' => '地理编码器缓存',
        'geocoder_cache_days' => '地理编码器缓存天数
',
        'geocoder_cache_delete' => '删除地理编码器缓存',
        'api_key' => 'API密钥',
        'api_url' => 'API网址',
        'map_center_latitude' => '地图中心的纬度',
        'map_center_longitude' => '地图中心经度',
        'map_zoom_level' => '地图缩放级别',
        'dst_type' => '类型',
        'provider' => '提供商',
        'week_start_day' => '默认日历周开始日期',
        'ip' => 'IP',
        'gprs_templates_only' => '仅显示GPRS模板命令',
        'select_all_objects' => '选择所有对象',
        'icon_type' => '图标类型',
        'on_setflag_1' => '开始角色',
        'on_setflag_2' => '字符数量',
        'on_setflag_3' => '参数值',
        'domain' => '域',
        'auth_id' => '身份验证ID',
        'auth_token' => '身份验证令牌',
        'senders_phone' => '发件人的电话号码',
        'database_clear_status' => '自动历史清理',
        'database_clear_days' => '保持的日子',
        'ignition_detection' => '通过点火检测',
        'here_map_id' => 'HERE.com应用ID',
        'here_map_code' => 'HERE.com应用程序代码',
        'login_page_panel_background_color' => '登录页面面板背景颜色',
        'login_page_panel_transparency' => '登录页面面板透明度',
        'visible' => '可见',
        'template_color' => '模板颜色',
        'background' => '背景',
        'login_page_text_color' => '登录页面的文字颜色',
        'login_page_background_color' => '登录页面背景颜色',
        'welcome_text' => '欢迎文字',
        'bottom_text' => '底部的文本',
        'apple_store_link' => '苹果商店链接',
        'google_play_link' => 'Google play 链接',
        'position' => '位置',
        'stop_duration_longer_than' => '停止持续时间超过',
        'mapbox_access_token' => 'MapBox访问令牌',
        'flag' => '旗',
        'shift_start' => '班次开始',
        'shift_finish' => '换档完成',
        'shift_start_tolerance' => '换档启动容差',
        'shift_finish_tolerance' => '轮班完成容差',
        'excessive_exit' => '过度退出',
        'smtp_authentication' => 'SMTP认证',
        'skip_calibration' => '排除校准范围外的计算',
        'bing_maps_key' => '必应地图键',
        'stripe_public_key' => 'STRIPE公钥',
        'stripe_secret_key' => 'STRIPE密钥',
        'stripe_currency' => 'STRIPE货币',
        'priority' => '优先',
        'pickup_address' => '取件地址',
        'delivery_address' => '邮寄地址',
        'schedule' => '时间表',
        'sound_notification' => '声音通知',
        'push_notification' => '推送通知',
        'email_notification' => '电子邮件通知',
        'sms_notification' => '短信通知',
        'webhook_notification' => 'Webhook通知',
        'offline_duration_longer_than' => '离线持续时间超过',
        'sms_gateway_headers' => '短信网关标头',
        'forward' => '向前',
        'by_status' => '按地位',
        'icon_status_online' => '在线状态图标',
        'icon_status_offline' => '离线状态图标',
        'icon_status_ack' => '确认状态图标',
        'icon_status_engine' => '引擎状态图标',
    ),
    'same_protocol' => '这些设备必须具有相同的协议。',
    'contains' => ':attribute必须包含:value 。',
    'ip_port' => ':attribute与格式IP:PORT不匹配',
);