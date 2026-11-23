<?php

namespace App\Models;

class Evidencia extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO evidencias (
                formato_registro_id, documento_id, usuario_id, tipo, titulo,
                archivo_path, mime_type, extension, peso_bytes, latitud, longitud, tomada_at
            ) VALUES (
                :formato_registro_id, :documento_id, :usuario_id, :tipo, :titulo,
                :archivo_path, :mime_type, :extension, :peso_bytes, :latitud, :longitud, :tomada_at
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function byDocumento(int $documentoId): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.nombres, u.apellidos, u.email
            FROM evidencias e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            WHERE e.documento_id = :documento_id
            ORDER BY e.created_at DESC
        ");
        $stmt->execute(['documento_id' => $documentoId]);
        return $stmt->fetchAll();
    }

    public function byFormato(int $formatoRegistroId): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.nombres, u.apellidos, u.email
            FROM evidencias e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            WHERE e.formato_registro_id = :formato_registro_id
            ORDER BY e.created_at DESC
        ");
        $stmt->execute(['formato_registro_id' => $formatoRegistroId]);
        return $stmt->fetchAll();
    }
}
