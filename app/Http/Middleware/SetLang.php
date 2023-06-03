<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Session;

class SetLang
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check()) {
            // Get the user specific language
            $lang = Session::has('language') ? Session::get('language') : $this->auth->user()->lang;
            // Set the language
            App::setLocale($lang);
        }

        return $next($request);
    }
}
