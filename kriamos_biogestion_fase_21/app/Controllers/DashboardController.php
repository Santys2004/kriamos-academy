<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Alerta;
use App\Models\Certificacion;
use App\Models\CicloProductivo;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\TipoFormato;
use App\Services\Auth;
use Throwable;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $pageTitle = 'Panel de control';
        $authUser = Auth::user();

        $dbOk = true;
        $errorDb = null;
        $granjas = [];
        $metricas = [
            'granjas' => 0,
            'galpones' => 0,
            'capacidad_total' => 0,
            'tipos_formato' => 0,
            'alertas_pendientes' => 0,
            'certificaciones_90' => 0,
            'ciclos_activos' => 0,
        ];

        try {
            $granjaModel = new Granja();
            $granjas = $granjaModel->all();
            $metricas['granjas'] = $granjaModel->count();
            $metricas['capacidad_total'] = $granjaModel->capacidadTotal();
            $metricas['galpones'] = (new Galpon())->count();
            $metricas['tipos_formato'] = (new TipoFormato())->count();
            $metricas['alertas_pendientes'] = (new Alerta())->pendientes();
            $metricas['certificaciones_90'] = (new Certificacion())->proximasVencer(90);
            $metricas['ciclos_activos'] = (new CicloProductivo())->countActive();
        } catch (Throwable $e) {
            $dbOk = false;
            $errorDb = $e->getMessage();
        }

        require dirname(__DIR__) . '/Views/dashboard/index.php';
    }
}
