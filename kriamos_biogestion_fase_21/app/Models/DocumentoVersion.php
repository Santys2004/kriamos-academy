<?php

namespace App\Models;

class DocumentoVersion extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO documento_versiones (
                documento_id, usuario_id, version, archivo_path, mime_type, extension,
                peso_bytes, comentario, created_at
            ) VALUES (
                :documento_id, :usuario_id, :version, :archivo_path, :mime_type, :extension,
                :peso_bytes, :comentario, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function byDocumento(int $documentoId): array
    {
        $stmt = $this->db->prepare("
            SELECT dv.*, u.nombres, u.apellidos, u.email
            FROM documento_versiones dv
            INNER JOIN usuarios u ON u.id = dv.usuario_id
            WHERE dv.documento_id = :documento_id
            ORDER BY dv.version DESC, dv.created_at DESC
        ");
        $stmt->execute(['documento_id' => $documentoId]);
        return $stmt->fetchAll();
    }
}
