<?php

namespace App\Controllers\Api;

use App\Middleware\Api\ApiAuthMiddleware;
use App\Models\Alerta;
use App\Models\Documento;
use App\Models\FormatoRegistro;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\Api\ApiResponse;
use App\Services\BI\BiDashboardService;

class ApiPublicController
{
    public function health(): void
    {
        ApiAuthMiddleware::handle();
        ApiAuthMiddleware::log(200);

        ApiResponse::success([
            'service' => 'Kriamos BioGestión ICA API',
            'version' => '1.0',
            'timestamp' => date('c'),
        ]);
    }

    public function granjas(): void
    {
        ApiAuthMiddleware::handle();
        $data = (new Granja())->active();
        ApiAuthMiddleware::log(200);

        ApiResponse::success(['data' => $data]);
    }

    public function galpones(): void
    {
        ApiAuthMiddleware::handle();
        $data = (new Galpon())->all();
        ApiAuthMiddleware::log(200);

        ApiResponse::success(['data' => $data]);
    }

    public function formatos(): void
    {
        ApiAuthMiddleware::handle();
        $data = (new FormatoRegistro())->all();
        ApiAuthMiddleware::log(200);

        ApiResponse::success(['data' => $data]);
    }

    public function alertas(): void
    {
        ApiAuthMiddleware::handle();
        $data = (new Alerta())->all();
        ApiAuthMiddleware::log(200);

        ApiResponse::success(['data' => $data]);
    }

    public function documentos(): void
    {
        ApiAuthMiddleware::handle();
        $data = (new Documento())->search([
            'q' => $_GET['q'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'granja_id' => $_GET['granja_id'] ?? '',
            'categoria_documental_id' => $_GET['categoria_documental_id'] ?? '',
        ]);
        ApiAuthMiddleware::log(200);

        ApiResponse::success(['data' => $data]);
    }

    public function bi(): void
    {
        ApiAuthMiddleware::handle();

        $filters = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'granja_id' => $_GET['granja_id'] ?? '',
        ];

        $data = (new BiDashboardService())->build($filters);
        ApiAuthMiddleware::log(200);

        ApiResponse::success([
            'filters' => $filters,
            'data' => $data,
        ]);
    }
}
