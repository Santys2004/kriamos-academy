<?php $appName = app_config('name'); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $appName) ?> | <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#12365A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Academia KRIAMOS">
    <link rel="apple-touch-icon" href="assets/img/logo-kriamos.png">
    <link href="assets/css/kriamos.css" rel="stylesheet">
</head>
<body>
<div id="connectionStatus" class="connection-status d-none" role="status"></div>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/img/logo-kriamos.png" alt="KRIAMOS" class="brand-logo">
            <span class="fw-bold text-kriamos-blue">Academia KRIAMOS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKriamos">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarKriamos">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-house me-1"></i>Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('offline')) ?>"><i class="fa-solid fa-cloud-arrow-down me-1"></i>Offline</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('chat_ia')) ?>"><i class="fa-solid fa-robot me-1"></i>Chat IA</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('registro', ['tipo' => 'empleado'])) ?>"><i class="fa-solid fa-user-tie me-1"></i>Empleado</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('registro', ['tipo' => 'contratista'])) ?>"><i class="fa-solid fa-helmet-safety me-1"></i>Contratista</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('verificar')) ?>"><i class="fa-solid fa-magnifying-glass me-1"></i>Verificar</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('admin_personas')) ?>"><i class="fa-solid fa-users me-1"></i>Personas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('admin_cursos')) ?>"><i class="fa-solid fa-graduation-cap me-1"></i>Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('mis_capacitaciones')) ?>"><i class="fa-solid fa-clipboard-check me-1"></i>Evaluaciones</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('historial_certificados')) ?>"><i class="fa-solid fa-certificate me-1"></i>Certificados</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('mi_calendario')) ?>"><i class="fa-solid fa-calendar-check me-1"></i>Mi calendario</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('dashboard')) ?>"><i class="fa-solid fa-chart-line me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('admin_chat_ia')) ?>"><i class="fa-solid fa-database me-1"></i>Base IA</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('admin_recompensas')) ?>"><i class="fa-solid fa-gift me-1"></i>Recompensas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(current_url('admin_calendario')) ?>"><i class="fa-solid fa-calendar-days me-1"></i>Calendario</a></li>
            </ul>
            <button id="pwaInstallButton" class="btn btn-sm btn-kriamos-orange ms-lg-3 d-none" type="button"><i class="fa-solid fa-mobile-screen-button me-1"></i>Instalar</button>
        </div>
    </div>
</nav>
<main>
