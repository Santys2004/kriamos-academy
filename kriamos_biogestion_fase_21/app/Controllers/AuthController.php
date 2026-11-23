<?php

namespace App\Controllers;

use App\Middleware\GuestMiddleware;
use App\Services\Auth;
use App\Services\AuthService;
use App\Services\Csrf;
use App\Services\Session;

class AuthController
{
    public function login(): void
    {
        GuestMiddleware::handle();

        $pageTitle = 'Ingreso corporativo';
        $error = Session::pullFlash('error');
        $success = Session::pullFlash('success');

        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    public function authenticate(): void
    {
        GuestMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
            exit;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'La sesión expiró o el formulario no es válido. Intente nuevamente.');
            redirect('/login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = (new AuthService())->attempt($email, $password);

        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            redirect('/login');
            exit;
        }

        Session::flash('success', 'Ingreso exitoso.');
        redirect('/');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        $_SESSION['_flash_success'] = 'Sesión cerrada correctamente.';
        redirect('/login');
        exit;
    }
}
