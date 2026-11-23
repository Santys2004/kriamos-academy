<?php

namespace App\Models;

class Auditoria extends BaseModel
{
    public function record(?int $usuarioId, string $modulo, string $accion, ?string $tabla, ?int $registroId, ?array $antes = null, ?array $despues = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO auditoria (
                usuario_id, modulo, accion, tabla_afectada, registro_id,
                datos_anteriores, datos_nuevos, ip, dispositivo
            ) VALUES (
                :usuario_id, :modulo, :accion, :tabla_afectada, :registro_id,
                :datos_anteriores, :datos_nuevos, :ip, :dispositivo
            )
        ");

        $stmt->execute([
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'datos_anteriores' => $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
            'datos_nuevos' => $despues ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
