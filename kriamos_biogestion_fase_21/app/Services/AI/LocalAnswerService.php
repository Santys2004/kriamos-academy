<?php

namespace App\Services\AI;

class LocalAnswerService
{
    public function answer(string $question, array $contexts): string
    {
        if (empty($contexts)) {
            return "No encontré información suficiente en la base documental local para responder con seguridad. Recomiendo revisar el núcleo documental o pedir al administrador que indexe más documentos al contexto IA.";
        }

        $lines = [];
        $lines[] = "Respuesta generada con base en la información documental interna disponible:";
        $lines[] = "";

        foreach ($contexts as $index => $ctx) {
            $summary = trim($ctx['contenido_resumen'] ?? '');
            if ($summary === '') {
                $summary = mb_substr(strip_tags($ctx['contenido_texto'] ?? ''), 0, 500);
            }

            $lines[] = ($index + 1) . ". " . ($ctx['titulo'] ?? 'Documento sin título') . ": " . $summary;
        }

        $lines[] = "";
        $lines[] = "Criterio de uso: valide siempre la información contra el documento oficial, el POE vigente y el responsable técnico antes de presentar datos ante el ICA.";

        return implode("\n", $lines);
    }
}
