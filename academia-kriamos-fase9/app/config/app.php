<?php
return [
    'name' => 'Academia KRIAMOS',
    'version' => 'Fase 9',
    'company' => 'KRIAMOS S.A.S.',
    'base_url' => '/academia-kriamos-fase9/public',
    'timezone' => 'America/Bogota',

    'upload_photo_dir' => __DIR__ . '/../../public/uploads/photos',
    'upload_photo_url' => 'uploads/photos',
    'allowed_photo_types' => ['image/jpeg', 'image/png', 'image/webp'],
    'max_photo_size' => 3 * 1024 * 1024,

    'upload_material_dir' => __DIR__ . '/../../public/uploads/materials',
    'upload_material_url' => 'uploads/materials',
    'max_material_size' => 250 * 1024 * 1024,


    'certificate_dir' => __DIR__ . '/../../storage/certificates',
    'certificate_relative_dir' => 'storage/certificates',
    'qr_dir' => __DIR__ . '/../../storage/qr',
    'qr_relative_dir' => 'storage/qr',
    'certificate_valid_months' => 6,
    'recertification_months' => 6,
    'reminder_days' => [5, 1],


    // Fase 9 - PWA y modo sin conexión.
    'pwa_cache_version' => 'kriamos-fase9-v1',
    'offline_material_cache' => 'kriamos-offline-materials-v1',
    'offline_enabled' => true,

    'allowed_material_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'video/mp4',
        'video/quicktime',
        'audio/mpeg',
        'audio/mp4',
        'audio/wav',
        'audio/x-wav',
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
];
