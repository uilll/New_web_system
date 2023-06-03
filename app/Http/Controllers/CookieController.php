<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CookieController extends Controller
{
    public function setCookie(Request $request, string $name, string $value)
    {
        $minutes = 0;
        $response = new Response($value);
        $response->withCookie(cookie($name, $value, $minutes));

        return $response;
    }

    public function getCookie(Request $request, string $name)
    {
        $value = $request->cookie($name);

        return $value;
    }
}
