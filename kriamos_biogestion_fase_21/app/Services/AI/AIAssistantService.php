<?php

namespace App\Services\AI;

use App\Models\AiConsulta;
use App\Services\Auth;

class AIAssistantService
{
    public function ask(string $question, string $mode = 'local'): array
    {
        $search = new ContextSearchService();
        $contexts = $search->search($question, 6);
        $contextText = $search->buildContextText($contexts);
        $sanitizer = new DataSanitizer();

        if ($sanitizer->hasSensitiveRisk($question)) {
            $answer = "La pregunta puede contener información sensible. Por seguridad, reformule la consulta sin contraseñas, tokens, claves, cédulas ni datos confidenciales.";
            $mode = 'bloqueado';
            $provider = 'local-policy';
        } elseif ($mode === 'externo') {
            $providerClient = new AIProviderClient();
            $providerResponse = $providerClient->ask($question, $contextText);

            if ($providerResponse['ok'] ?? false) {
                $answer = $providerResponse['answer'] ?? '';
                $provider = 'externo';
            } else {
                $answer = "No se usó proveedor externo. Motivo: " . ($providerResponse['message'] ?? 'No disponible') . "\n\n" . (new LocalAnswerService())->answer($question, $contexts);
                $provider = 'local-fallback';
                $mode = 'local';
            }
        } else {
            $answer = (new LocalAnswerService())->answer($question, $contexts);
            $provider = 'local';
        }

        $contextIds = array_map(fn($ctx) => (int) $ctx['id'], $contexts);
        $tokensEstimated = (int) ceil((mb_strlen($question . $contextText . $answer) / 4));

        $consultaId = (new AiConsulta())->create([
            'usuario_id' => Auth::id(),
            'pregunta' => $question,
            'respuesta' => $answer,
            'modo' => $mode,
            'proveedor' => $provider,
            'contexto_ids_json' => json_encode($contextIds),
            'tokens_estimados' => $tokensEstimated,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        return [
            'ok' => true,
            'consulta_id' => $consultaId,
            'respuesta' => $answer,
            'contextos' => $contexts,
            'modo' => $mode,
            'proveedor' => $provider,
        ];
    }
}
