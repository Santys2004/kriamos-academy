<?php

namespace App\Middleware;

use App\Services\Auth;
use App\Services\Session;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debe iniciar sesión con su correo corporativo para ingresar.');
            redirect('/login');
            exit;
        }
    }
}
