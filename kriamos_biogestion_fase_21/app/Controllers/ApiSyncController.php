<?php

namespace App\Controllers;

use App\Models\SyncLog;
use App\Services\Auth;

class ApiSyncController
{
    public function formato(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Sesión no autorizada.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);

        if (!is_array($body) || empty($body['local_id']) || empty($body['payload'])) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Payload de sincronización inválido.']);
            return;
        }

        $payload = $body['payload'];
        $localId = $body['local_id'];

        // Base inicial: se registra la sincronización recibida.
        // En una fase de endurecimiento, este punto puede reutilizar FormatoController::store
        // o un servicio específico para convertir payload offline en formato definitivo.
        $remoteId = null;
        $estado = 'pendiente';
        $mensaje = 'Registro offline recibido. Pendiente de consolidación final por servicio transaccional.';

        (new SyncLog())->create([
            'usuario_id' => Auth::id(),
            'local_id' => $localId,
            'tipo' => $body['tipo'] ?? 'formato',
            'estado' => $estado,
            'remote_id' => $remoteId,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'mensaje' => $mensaje,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        echo json_encode([
            'ok' => true,
            'message' => $mensaje,
            'remote_id' => $remoteId,
            'local_id' => $localId,
        ]);
    }
}
