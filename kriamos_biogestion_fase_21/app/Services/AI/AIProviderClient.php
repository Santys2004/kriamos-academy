<?php

namespace App\Services\AI;

use App\Services\Database;

class AIProviderClient
{
    public function ask(string $prompt, string $context): array
    {
        $config = $this->config();

        if (($config['ai_external_enabled'] ?? '0') !== '1') {
            return [
                'ok' => false,
                'message' => 'Proveedor externo IA desactivado por política de privacidad.',
            ];
        }

        // Punto de extensión para proveedor externo.
        // No se implementa llamada real por defecto para evitar exposición de datos.
        return [
            'ok' => false,
            'message' => 'Integración externa preparada, pendiente de configurar proveedor aprobado por Kriamos.',
        ];
    }

    private function config(): array
    {
        $stmt = Database::connection()->query("
            SELECT clave, valor
            FROM parametros_sistema
            WHERE grupo = 'ia'
        ");

        $values = [];
        foreach ($stmt->fetchAll() as $row) {
            $values[$row['clave']] = $row['valor'];
        }

        return $values;
    }
}
