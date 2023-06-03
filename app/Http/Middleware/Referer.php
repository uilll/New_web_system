<?php

namespace App\Http\Middleware;

use Closure;
use Facades\Repositories\UserRepo;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Session;

class Referer
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
        $referer_id = $request->input('referer_id', null);

        if (! is_null($referer_id)) {
            if (Session::get('referer_id') != $referer_id) {
                $user = UserRepo::find($referer_id);

                if (! empty($user) && $user->isManager()) {
                    Session::set('referer_id', $user->id);
                }
            }
        }

        return $next($request);
    }
}
