<?php

namespace App\Models;

class CertificadoFirma extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, u.email, u.nombres, u.apellidos
            FROM certificados_firma c
            INNER JOIN usuarios u ON u.id = c.usuario_id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function activeByUsuario(int $usuarioId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM certificados_firma
            WHERE usuario_id = :usuario_id
              AND estado = 'activo'
              AND (vigente_hasta IS NULL OR vigente_hasta >= CURDATE())
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO certificados_firma (
                usuario_id, tipo, serial, huella_sha256, entidad_emisora,
                vigente_desde, vigente_hasta, estado, metadata_json, created_at
            ) VALUES (
                :usuario_id, :tipo, :serial, :huella_sha256, :entidad_emisora,
                :vigente_desde, :vigente_hasta, :estado, :metadata_json, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function revoke(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE certificados_firma
            SET estado = 'revocado',
                revocado_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}
