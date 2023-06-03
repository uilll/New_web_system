<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        'App\Http\Middleware\SetLang',
        'App\Http\Middleware\Referer',
        'App\Http\Middleware\ServerActive',
        'Fideloper\Proxy\TrustProxies',
        \App\Http\Middleware\SameSiteCookieMiddleware::class,
        \App\Http\Middleware\CorsMiddleware::class,
        \App\Http\Middleware\UpdateLastLogin::class,
        //\App\Http\Middleware\UpdateLastLogin::class, Está dando erro, não ativar
        //'App\Http\Middleware\VerifyCsrfToken'
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => 'App\Http\Middleware\Authenticate',
        'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'report_finish' => 'App\Http\Middleware\ReportFinish',
        'active_subscription' => 'App\Http\Middleware\ActiveSubscription',
        'auth.admin' => 'App\Http\Middleware\AdminAuthenticate',
        'auth.manager' => 'App\Http\Middleware\ManagerAuthenticate',
        'api_auth' => 'App\Http\Middleware\ApiAuthenticate',
        'server_active' => 'App\Http\Middleware\ServerActive',
        'api_active' => 'App\Http\Middleware\ApiActive',
        'throttle' => 'App\Http\Middleware\ThrottleRequests',
        'tracker_auth' => 'App\Http\Middleware\TrackerAuth',
        'cors' => 'App\Http\Middleware\Cors::class',
        'CheckPasswordUpdated' => \App\Http\Middleware\CheckPasswordUpdated::class,
        //'password.updated' => \App\Http\Middleware\CheckPasswordUpdated::class,
    ];
}
