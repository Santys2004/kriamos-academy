<?php

namespace App\Middleware;

use App\Services\Auth;

class GuestMiddleware
{
    public static function handle(): void
    {
        if (Auth::check()) {
            redirect('/');
            exit;
        }
    }
}
