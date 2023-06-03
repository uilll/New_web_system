<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPasswordUpdated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Código sem otimização
        /*
           if ($request->user()) {
               $passwordUpdated = $request->user()->password_updated_at;
               if (!$passwordUpdated || $passwordUpdated === '0000-00-00 00:00:00') {
                   return $next($request);
               }
               else{
                   $passwordUpdated = Carbon::createFromFormat('Y-m-d H:i:s', $passwordUpdated);
                   $lastLogin = $request->session()->get('last_login_at');

                   //dd($lastLogin, $passwordUpdated);
                   if ($lastLogin === null) {
                       //Auth::logout();
                       debugar(true, "Deslogou");
                       return redirect()->route('login')
                           ->withErrors(['message' => 'Sua senha foi alterada. Por favor, faça login novamente.']);
                   }
                   else{
                       $lastLogin = Carbon::createFromFormat('Y-m-d H:i:s', $request->session()->get('last_login_at'));
                       if ($passwordUpdated->gt($lastLogin)) {
                           //Auth::logout();
                           debugar(true, "Deslogou2");

                           return redirect()->route('login')
                               ->withErrors(['message' => 'Sua senha foi alterada. Por favor, faça login novamente.']);
                       }
                   }
               }
           }
           return $next($request);
           */

        // Código otimizado
        if ($request->user()) {
            $passwordUpdated = $request->user()->password_updated_at;

            if (! $passwordUpdated || $passwordUpdated === '0000-00-00 00:00:00') {
                return $next($request);
            }

            $passwordUpdated = Carbon::parse($passwordUpdated);
            $lastLogin = Carbon::parse($request->session()->get('last_login_at'));

            if (! $lastLogin || $passwordUpdated->gt($lastLogin)) {
                Auth::logout(); //Aguardar o pessoal logar amanhã 24/04/2023 e depois descomentar esssa linha
                //debugar(true, "Deslogou");

                return redirect()->route('login')
                    ->withErrors(['message' => 'Sua senha foi alterada. Por favor, faça login novamente.']);
            }
        }

        return $next($request);
    }
}
