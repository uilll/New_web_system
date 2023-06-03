<?php

namespace App\Http\Middleware;

use Closure;

class ApiActive
{
    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Facades\Server::isApiDisabled()) {
            return response()->json(['status' => 0, 'error' => 'Your server API is disabled due to unpaid invoices.'], 401);
        }

        return $next($request);
    }
}
