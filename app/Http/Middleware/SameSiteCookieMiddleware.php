<?php

namespace App\Http\Middleware;

use Closure;

class SameSiteCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $headers = $response->headers->all();
        if (isset($headers['set-cookie'])) {
            $cookies = $headers['set-cookie'];

            $newCookies = [];
            foreach ($cookies as $cookie) {
                $newCookies[] = $cookie.'; SameSite=None; Secure';
            }

            $response->headers->set('Set-Cookie', $newCookies);
        }

        return $response;
    }
}
