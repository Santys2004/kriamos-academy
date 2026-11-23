<?php

namespace App\Models;

class ChatAyuda extends BaseModel
{
    public function buscarRespuesta(string $pregunta): ?array
    {
        $tokens = $this->tokens($pregunta);

        if (empty($tokens)) {
            return null;
        }

        $conditions = [];
        $params = [];

        foreach ($tokens as $i => $token) {
            $conditions[] = "(pregunta LIKE :t$i OR respuesta LIKE :t$i OR palabras_clave LIKE :t$i)";
            $params["t$i"] = '%' . $token . '%';
        }

        $sql = "
            SELECT *,
                   (" . implode(' + ', array_fill(0, count($tokens), '1')) . ") AS score
            FROM chat_base_conocimiento
            WHERE activo = 1
              AND (" . implode(' OR ', $conditions) . ")
            ORDER BY prioridad DESC, updated_at DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function registrarPregunta(?int $usuarioId, string $pregunta, ?string $respuesta, ?int $baseId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO chat_ayuda_logs (
                usuario_id, pregunta, respuesta, base_conocimiento_id, ip, dispositivo, created_at
            ) VALUES (
                :usuario_id, :pregunta, :respuesta, :base_conocimiento_id, :ip, :dispositivo, NOW()
            )
        ");

        $stmt->execute([
            'usuario_id' => $usuarioId,
            'pregunta' => $pregunta,
            'respuesta' => $respuesta,
            'base_conocimiento_id' => $baseId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT l.*, u.email
            FROM chat_ayuda_logs l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            ORDER BY l.created_at DESC
            LIMIT 100
        ");
        return $stmt->fetchAll();
    }

    public function calificar(int $logId, int $util): void
    {
        $stmt = $this->db->prepare("
            UPDATE chat_ayuda_logs
            SET util = :util
            WHERE id = :id
        ");
        $stmt->execute([
            'util' => $util,
            'id' => $logId,
        ]);
    }

    private function tokens(string $text): array
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^a-záéíóúñ0-9 ]/iu', ' ', $text);
        $parts = preg_split('/\\s+/', $text);

        $stop = ['como','cómo','que','qué','para','por','con','una','uno','del','las','los','de','la','el','en','y','o','un','al','se','me'];
        $tokens = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) >= 3 && !in_array($part, $stop, true)) {
                $tokens[] = $part;
            }
        }

        return array_values(array_unique(array_slice($tokens, 0, 8)));
    }
}
