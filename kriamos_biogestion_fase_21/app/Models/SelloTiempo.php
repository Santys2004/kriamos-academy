<?php

namespace App\Models;

class SelloTiempo extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO sellos_tiempo (
                firma_avanzada_id, proveedor, timestamp_utc, hash_sellado,
                token_sello, respuesta_json, estado, created_at
            ) VALUES (
                :firma_avanzada_id, :proveedor, :timestamp_utc, :hash_sellado,
                :token_sello, :respuesta_json, :estado, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function byFirma(int $firmaId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM sellos_tiempo
            WHERE firma_avanzada_id = :firma_id
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['firma_id' => $firmaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
