<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckPasswordUpdated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
        {
            if ($request->user()) {
                if (!$request->user()->password_updated_at) {
                    return $next($request);
                }
                $passwordUpdated = $request->user()->password_updated_at;
                if ($passwordUpdated) {
                    $lastLogin = $request->session()->get('last_login_at');
                    if ($lastLogin === null) {
                        return $next($request);
                    }

                    // Criar uma instância do Carbon a partir da data/hora do último login
                    $lastLogin = Carbon::createFromFormat('Y-m-d H:i:s', $lastLogin);
                    $passwordUpdated = Carbon::createFromFormat('Y-m-d H:i:s', $passwordUpdated);
                    //$passwordUpdatedByUserId = $request->session()->get('password_updated_by_user_id', null);

                    if ($passwordUpdated->gt($lastLogin)) {
                        Auth::logout();

                        return redirect()->route('login')
                            ->withErrors(['message' => 'Sua senha foi alterada. Por favor, faça login novamente.']);
                    }
                }
            }

            return $next($request);
        }


}
