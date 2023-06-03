<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Tobuli\Entities\User;

class ApiAuthenticate
{
    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct()
    {
        Config::set('tobuli.api', 1);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $input = Input::all();
        $user = null;
        if (isset($input['user_api_hash'])) {
            $hash = $input['user_api_hash'];
            $user = User::where('api_hash', $hash)->first();

            if (isPublic()) {
                if (empty($user) || strtotime($user->api_hash_expire) < time()) {
                    $user = \Facades\RemoteUser::getByApiHash($hash);
                }
            }
        }

        if (empty($user)) {
            return response()->json(['status' => 0, 'message' => trans('front.login_failed')], 401);
        }

        if (! $user->active) {
            return response()->json(['status' => 0, 'message' => 'Unauthorized'], 401);
        }

        App::setLocale(empty($input['lang']) ? $user->lang : $input['lang']);

        Auth::loginUsingId($user->id);

        Auth::User()->loged_at = date('Y-m-d H:i:s');
        Auth::User()->save();

        return $next($request);
    }
}
