<?php

namespace App\Services\AI;

use App\Models\AiContexto;

class ContextSearchService
{
    public function search(string $question, int $limit = 6): array
    {
        return (new AiContexto())->searchRelevant($question, $limit);
    }

    public function buildContextText(array $contexts): string
    {
        $sanitizer = new DataSanitizer();
        $chunks = [];

        foreach ($contexts as $ctx) {
            $text = trim(($ctx['titulo'] ?? '') . "\n" . ($ctx['contenido_resumen'] ?? '') . "\n" . ($ctx['contenido_texto'] ?? ''));
            $text = $sanitizer->sanitize($text);
            $chunks[] = "Fuente: " . ($ctx['tipo'] ?? 'contexto') . " | " . ($ctx['titulo'] ?? 'Sin título') . "\n" . $text;
        }

        return implode("\n\n---\n\n", $chunks);
    }
}
