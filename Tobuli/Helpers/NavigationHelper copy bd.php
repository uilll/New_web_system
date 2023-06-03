<?php

use App\Monitoring;
use App\tracker;

function getNavigation()
{
    $stats = Cache::remember(Auth::User()->id.'_online_cache', 1, function () {
        if (Auth::User()->isManager()) {
            $user_ = Auth::User()->id;
        } else {
            $user_ = 0;
        }

        $total_devices = Auth::User()->accessibleDevices()->count('devices.id');
        $online_devices = Auth::User()->accessibleDevices()->online()->count('devices.id');

        if (Auth::User()->isAdmin()) {
            $total_users = DB::table('users')->count() - 1;
        } else {
            $total_users = DB::table('users')->where('manager_id', '=', Auth::User()->id)->count() + 1;
        }

        //$total_events = Monitoring::all()->count();
        $total_trackers = tracker::where('active', '=', 1)
                                ->where('manager_id', $user_)
                                ->count();
        $total_maintence = DB::table('insta_maints')->where('active', '=', 1)->count();
        $total_payable = DB::table('insta_maints')
                        ->where('manager_id', $user_)
                        ->where('technician_id', '!=', 1)
                        ->where('technician_id', '!=', 3)
                        ->where('active', '=', 0)
                        ->where('payable', '=', 0)
                        ->count();

        $available_trackers = tracker::where('active', '=', 1)
                                    ->where('in_use', '=', 0)
                                    ->where('manager_id', $user_)
                                    ->count();
        $total_events = Monitoring::where('active', '=', 1)
                                    ->where('manager_id', $user_)
                                    ->count();
        $to_tratament = Monitoring::where('active', 1)
                                    ->where('manager_id', $user_)
                                    ->where('treated_occurence', 0)
                                    ->where('make_contact', 1)->count();
        $to_tratament = $total_events - $to_tratament;

        return [
            'total_devices' => $total_devices,
            'online_devices' => $online_devices,
            'total_users' => $total_users,
            'total_events' => $total_events,
            'total_active_events' => $to_tratament,
            'total_trackers' => $total_trackers,
            'available_trackers' => $available_trackers,
            'total_maintence' => $total_maintence,
            'total_payable' => $total_payable,
        ];
    });

    $currentRoute = Route::getCurrentRoute()->getName();
    if (Auth::User()->isAdmin()) {
        if (true) { //Auth::User()->id !=1024) {
            $items = [
                [
                    'title' => '<i class="icon map"></i> '.'<span class="text">'.trans('admin.map').'</span>',
                    'route' => 'objects.index',
                    'childs' => [],
                ],

                //Users
                [
                    'title' => '<i class="icon users"></i> '.'<span class="text">Clientes ('.array_get($stats, 'total_users', 0).')</span>',
                    'route' => '',
                    'childs' => [
                        [
                            'title' => '<i class="icon users"></i> '.'<span class="text">Cadastros ('.array_get($stats, 'total_users', 0).')</span>',
                            'route' => 'admin.customer.index',
                            'childs' => '',
                        ],
                        [
                            'title' => '<i class="icon users"></i> '.'<span class="text">'.trans('admin.users').' ('.array_get($stats, 'total_users', 0).')</span>',
                            'route' => 'admin.clients.index',
                            'childs' => '',
                        ],
                    ],
                ],
                //Objects
                [
                    'title' => '<i class="icon device"></i> '.'<span class="text">'.trans('admin.objects').' ('.array_get($stats, 'online_devices', 0).'/'.array_get($stats, 'total_devices', 0).')</span>',
                    'route' => '',
                    'childs' => [
                        [
                            'title' => '<span class="text">Contabilidade dos chips</span>',
                            'route' => 'admin.chips.index',
                            'childs' => '',
                        ],
                        [
                            'title' => '<i class="icon device"></i> '.'<span class="text">Veículos ('.array_get($stats, 'online_devices', 0).'/'.array_get($stats, 'total_devices', 0).')</span>',
                            'route' => 'admin.objects.index',
                            'childs' => '',
                        ],
                        [
                            'title' => '<span class="text">Rastreadores ('.array_get($stats, 'available_trackers', 0).'/'.array_get($stats, 'total_trackers', 0).')</span>',
                            'route' => 'admin.tracker.index',
                            'childs' => '',
                        ],
                    ],
                ],
                //Stock
                [
                    'title' => '<i class="icon device"></i> '.'<span class="text">'.'Estoque'.' ('.array_get($stats, 'cadastrados', 0).'/'.array_get($stats, 'em_faltas', 0).')</span>',
                    'route' => '',
                    'childs' => [
                        [
                            'title' => '<span class="text">Estoque</span>',
                            'route' => 'admin.Stock.index',
                            'childs' => '',
                        ],
                        [
                            'title' => '<i class="icon device"></i> '.'<span class="text">Faltas ('.array_get($stats, 'em_faltas', 0).')</span>',
                            'route' => 'admin.Stock.index',
                            'childs' => '',
                        ],
                    ],
                ],
            ];
        }
        if (Auth::User()->perm('monitoring', 'view')) {
            // Menu monitoramento
            //Serviços
            $items[] = [
                'title' => '<i class="icon check"></i> '.'<span class="text">'.'Serviços'.' ('.array_get($stats, 'total_active_events', 0).'/'.array_get($stats, 'total_maintence', 0).' Manut.)</span>',
                'route' => '',
                'childs' => [
                    [
                        'title' => '<i class="icon check"></i>'.'<span class="text">Monitoramento'.' ('.array_get($stats, 'total_active_events', 0).'/'.array_get($stats, 'total_events', 0).')</span>',
                        'route' => 'admin.monitoring.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">Manutenções'.' ('.array_get($stats, 'total_maintence', 0).'/'.array_get($stats, 'total_payable', 0).')</span>',
                        'route' => 'admin.insta_maint.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">Técnicos</span>',
                        'route' => 'admin.technician.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">Central de Mensagens</span>',
                        'route' => 'messages.index_admin',
                        'childs' => '',
                    ],
                ],
            ];
        }

        if (Auth::User()->perm('finances', 'view')) {
            //Menu Finanças
            $items[] = [

                'title' => '<i class="icon check"></i> '.'<span class="text">'.'Finanças'.' ('.array_get($stats, 'total_active_events', 0).'/'.array_get($stats, 'total_maintence', 0).' Manut.)</span>',
                'route' => '',
                'childs' => [
                    [
                        'title' => '<i class="icon users"></i>'.'<span class="text"> Clientes Asaas'.' ('.'00'.'/'.'00'.')</span>',
                        'route' => 'asaas.clientes.listarClientes',
                        'childs' => '',
                    ],
                    [
                        'title' => '<i class="icon check"></i>'.'<span class="text"> Cobranças Asaas'.' ('.'00'.'/'.'00'.')</span>',
                        'route' => 'asaas.cobranças.listarCobranças',
                        'childs' => '',
                    ],
                    [
                        'title' => '<i class="icon check"></i>'.'<span class="text">Mensalidades'.' ('.array_get($stats, 'total_active_events', 0).'/'.array_get($stats, 'total_events', 0).')</span>',
                        'route' => 'admin.financas.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">Cobranças'.' ('.array_get($stats, 'total_maintence', 0).'/'.array_get($stats, 'total_payable', 0).')</span>',
                        'route' => 'admin.insta_maint.index',
                        'childs' => '',
                    ],
                ],
            ];
        }
    } else {
        $items = [
            [
                'title' => '<i class="icon map"></i> '.'<span class="text">'.trans('admin.map').'</span>',
                'route' => 'objects.index',
                'childs' => [],
            ],
            [
                'title' => '<i class="icon device"></i> '.'<span class="text">Veículos ('.array_get($stats, 'online_devices', 0).'/'.array_get($stats, 'total_devices', 0).')</span>',
                'route' => 'admin.objects.index',
                'childs' => '',
            ],
            [
                'title' => '<i class="icon users"></i> '.'<span class="text">'.trans('admin.users').' ('.array_get($stats, 'total_users', 0).')</span>',
                'route' => 'admin.clients.index',
                'childs' => '',
            ],
        ];
    }
    if (Auth::user()->isManager()) {
        $items[] = [
            'title' => '<i class="icon setup"></i> '.'<span class="text">'.trans('validation.attributes.logo').'</span>',
            'route' => 'admin.main_server_settings.index',
            'childs' => [],
        ];
    }

    if (Auth::user()->isAdmin() && Auth::User()->id != 1024) {
        $items[] = [
            'title' => '<i class="icon events"></i> '.'<span class="text">'.trans('admin.events').'</span>',
            'route' => 'admin.events.index',
            'childs' => [],
        ];

        $items['content'] = [
            'title' => '<i class="icon content"></i> '.'<span class="text">'.trans('admin.content').'</span>',
            'route' => '',
            'childs' => [
                [
                    'title' => '<span class="text">'.trans('admin.email_templates').'</span>',
                    'route' => 'admin.email_templates.index',
                    'childs' => '',
                ],
                [
                    'title' => '<span class="text">'.trans('front.sms_templates').'</span>',
                    'route' => 'admin.sms_templates.index',
                    'childs' => '',
                ],
                [
                    'title' => '<span class="text">'.trans('admin.map_icons').'</span>',
                    'route' => 'admin.map_icons.index',
                    'childs' => '',
                ],
                [
                    'title' => '<span class="text">'.trans('admin.device_icons').'</span>',
                    'route' => 'admin.device_icons.index',
                    'childs' => '',
                ],
            ],
        ];

        if (env('SHOW_POPUPS', false)) {
            $items['content']['childs'][] = [
                'title' => '<span class="text">'.trans('admin.popups').'</span>',
                'route' => 'admin.popups.index',
                'childs' => '',
            ];
        }

        if (Auth::User()->perm('super_admin', 'view')) {
            $items['setup'] = [
                'title' => '<i class="icon setup"></i>'.'<span class="text">'.trans('front.setup').'</span>',
                'route' => '',
                'childs' => [
                    [
                        'title' => '<span class="text">'.trans('validation.attributes.email').'</span>',
                        'route' => 'admin.email_settings.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('front.main_server_settings').'</span>',
                        'route' => 'admin.main_server_settings.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('validation.attributes.user').'</span>',
                        'route' => 'admin.billing.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('admin.tracking_ports').'</span>',
                        'route' => 'admin.ports.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('admin.languages').'</span>',
                        'route' => 'admin.languages.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('admin.blocked_ips').'</span>',
                        'route' => 'admin.blocked_ips.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('front.tools').'</span>',
                        'route' => 'admin.tools.index',
                        'childs' => '',
                    ],
                    [
                        'title' => '<span class="text">'.trans('admin.plugins').'</span>',
                        'route' => 'admin.plugins.index',
                        'childs' => '',
                    ],
                ],
            ];

            $items['setup']['childs'][] = [
                'title' => '<span class="text">'.trans('admin.sensor_groups').'</span>',
                'route' => 'admin.sensor_groups.index',
                'childs' => '',
            ];

            $items['setup']['childs'][] = [
                'title' => '<span class="text">'.trans('front.sms_gateway').'</span>',
                'route' => 'admin.sms_gateway.index',
                'childs' => '',
            ];
        }

        $childs[] = [
            'title' => '<span class="text">'.trans('admin.tracker_logs').'</span>',
            'route' => 'admin.logs.index',
            'childs' => '',
        ];
        $childs[] = [
            'title' => '<span class="text"> Pesquisar na log </span>',
            'route' => 'admin.logs.search',
            'childs' => '',
        ];

        $childs[] = [
            'title' => '<span class="text">'.trans('admin.unregistered_devices_log').'</span>',
            'route' => 'admin.unregistered_devices_log.index',
            'childs' => '',
        ];

        $childs[] = [
            'title' => '<span class="text">'.trans('admin.report_log').'</span>',
            'route' => 'admin.report_logs.index',
            'childs' => '',
        ];

        $items[] = [
            'title' => '<i class="icon logs"></i>'.'<span class="text">'.trans('admin.logs').'</span>',
            'route' => '',
            'childs' => $childs,
        ];
    }

    $childs = [];

    if (Auth::User()->isAdmin() && Auth::User()->id != 1024) {
        $childs[] = [
            'title' => '<i class="icon restart"></i> '.'<span class="text">'.trans('admin.restart_tracking_service').'</span>',
            'route' => 'admin.restart_traccar',
            'childs' => '',
            'attribute' => 'class="js-confirm-link" data-confirm="'.trans('admin.do_restart_tracking_service').'"',
        ];
    }
    $childs[] = [
        'title' => '<i class="icon logout"></i>'.'<span class="text">'.trans('global.log_out').'</span>',
        'route' => 'logout',
        'childs' => '',
    ];

    $items[] = [
        'title' => Auth::User()->email.' ('.trans('admin.group_'.Auth::User()->group_id).') <i class="caret"></i>',
        'route' => '',
        'childs' => $childs,
    ];

    return parseNavigation($items, $currentRoute);
}

/**
 * @param $env
 * @param  int  $active
 * @param  int  $level
 * @return string
 */
function parseNavigation($items, $currentRoute, &$active = 0, $level = 1)
{
    $html = '';
    if (! empty($items)) {
        foreach ($items as $item) {
            ($level == 1) && $active = 0;
            $childs = ! empty($item['childs']);
            $innerLevel = $level + 1;
            //Sets active item
            ($currentRoute == $item['route']) && $active = 1;

            // Gets childs html
            $innerHtml = parseNavigation($item['childs'], $currentRoute, $active, $innerLevel);

            $html .= '<li class="'.array_get($item, 'class', '')
                .($active && $level == 1 ? ' active' : '')
                .($childs && $level > 1 ? ' dropdown-submenu' : '')
                .'">

            <a '.($level > 1 ? '' : ($childs ? 'data-hover="dropdown" data-toggle="dropdown"' : '')).' href="'.(! empty($item['route']) ? route($item['route']) : 'javascript:;').'"'.(! empty($item['attribute']) ? $item['attribute'] : '').'>
                '.$item['title'].
                ($level == 1 && $childs ? '<i class="'.($active ? 'selected' : '').'"></i>' : '').'
            </a>';

            $html .= ($childs ? '<ul class="dropdown-menu">'.$innerHtml.'</ul>' : '');

            $html .= '</li>';
        }

        return $html;
    }

}
