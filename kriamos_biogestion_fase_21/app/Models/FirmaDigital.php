<?php

namespace App\Models;

class FirmaDigital extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO firmas_digitales (
                formato_registro_id, usuario_id, firma_path, metodo, ip, dispositivo, hash_firma, firmado_at
            ) VALUES (
                :formato_registro_id, :usuario_id, :firma_path, :metodo, :ip, :dispositivo, :hash_firma, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function byRegistro(int $registroId): array
    {
        $stmt = $this->db->prepare("
            SELECT f.*, u.nombres, u.apellidos, u.email
            FROM firmas_digitales f
            INNER JOIN usuarios u ON u.id = f.usuario_id
            WHERE f.formato_registro_id = :registro_id
            ORDER BY f.firmado_at DESC
        ");
        $stmt->execute(['registro_id' => $registroId]);

        return $stmt->fetchAll();
    }

    public function existsForRegistro(int $registroId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM firmas_digitales WHERE formato_registro_id = :registro_id");
        $stmt->execute(['registro_id' => $registroId]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
