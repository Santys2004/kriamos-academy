<?php
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

$appName = 'Kriamos BioGestión ICA';
$pageTitle = $pageTitle ?? $appName;
$currentUser = Auth::user();
$flashSuccess = Session::pullFlash('success');
$flashError = Session::pullFlash('error');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($appName) ?></title>
    <meta name="theme-color" content="#143560">
    <link rel="manifest" href="<?= BASE_PATH ?>/manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_PATH ?>/assets/css/kriamos.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-kriamos shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_PATH ?>/">
            <img src="<?= BASE_PATH ?>/assets/img/logo-kriamos.png" alt="Kriamos" class="brand-logo">
            <span class="fw-bold text-white">BioGestión ICA</span><span id="connectionStatus" class="connection-online ms-2">En línea</span><span id="offlineQueueBadge" class="badge bg-warning text-dark d-none ms-1">0</span>
        </a>

        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="mainNavbar" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <?php if ($currentUser): ?>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/"><i class="fa-solid fa-gauge-high me-1"></i> Panel</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/granjas"><i class="fa-solid fa-warehouse me-1"></i> Granjas</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/galpones"><i class="fa-solid fa-house-flag me-1"></i> Galpones</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/ciclos"><i class="fa-solid fa-rotate me-1"></i> Ciclos</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/formatos"><i class="fa-solid fa-file-signature me-1"></i> Formatos</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/calidad"><i class="fa-solid fa-shield-check me-1"></i> Calidad</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/alertas"><i class="fa-solid fa-bell me-1"></i> Alertas</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/reportes"><i class="fa-solid fa-chart-column me-1"></i> Reportes</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/documentos"><i class="fa-solid fa-folder-open me-1"></i> Documentos</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/sincronizacion"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Sync</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/ayuda"><i class="fa-solid fa-circle-question me-1"></i> Ayuda</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/seguridad"><i class="fa-solid fa-shield-halved me-1"></i> Seguridad</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/admin"><i class="fa-solid fa-screwdriver-wrench me-1"></i> Admin</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/notificaciones"><i class="fa-solid fa-envelope-open-text me-1"></i> Notificaciones</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/bi"><i class="fa-solid fa-chart-line me-1"></i> BI Gerencial</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/api-admin"><i class="fa-solid fa-code me-1"></i> API</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/ia"><i class="fa-solid fa-brain me-1"></i> IA Documental</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/mobile"><i class="fa-solid fa-mobile-screen me-1"></i> App Móvil</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/bi-externo"><i class="fa-solid fa-chart-pie me-1"></i> BI Externo</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/firma-avanzada"><i class="fa-solid fa-file-signature me-1"></i> Firma Avanzada</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/auditoria-ica"><i class="fa-solid fa-clipboard-check me-1"></i> Auditoría ICA</a></li>
                    <?php if (Auth::can('auditoria.ver')): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_PATH ?>/database/schema"><i class="fa-solid fa-database me-1"></i> BD</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-shield me-1"></i>
                            <?= htmlspecialchars($currentUser['nombres'] ?? 'Usuario') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($currentUser['rol_nombre'] ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= BASE_PATH ?>/logout" class="px-3">
                                    <?= Csrf::input() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn btn-kriamos-outline ms-lg-2 px-3" href="<?= BASE_PATH ?>/login">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content">
    <div class="container-fluid px-4 pt-3">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
