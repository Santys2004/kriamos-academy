<?php

return [
    'max_upload_mb' => 50,
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv',
        'jpg', 'jpeg', 'png', 'webp',
        'mp4', 'mov',
        'txt'
    ],
    'allowed_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'image/jpeg',
        'image/png',
        'image/webp',
        'video/mp4',
        'video/quicktime',
        'application/octet-stream'
    ],
    'public_upload_path' => dirname(__DIR__) . '/public/uploads/documentos',
    'public_upload_url' => '/uploads/documentos',
];
