<?php
return [
    // Fase 9 - Chat IA.
    // Modo recomendado inicial: local_knowledge. Responde únicamente con base en la base documental interna.
    'mode' => 'local_knowledge',
    'external_provider_enabled' => false,
    'external_api_url' => '',
    'external_api_key' => '',
    'external_model' => '',
    'max_context_items' => 3,
    'min_confidence' => 1.5,
    'sensitive_keywords' => [
        'influenza', 'newcastle', 'salmonella', 'mortalidad', 'emergencia', 'brote', 'sospecha',
        'medicamento', 'antibiótico', 'eutanasia', 'sacrificio', 'cuarentena', 'enfermedad'
    ],
];
