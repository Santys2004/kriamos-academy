<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Administración avanzada</h1>
            <p class="text-muted mb-0">Usuarios, roles, permisos, parámetros y auditoría del sistema.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-users metric-icon"></i><span class="metric-label">Usuarios</span><strong><?= (int) $metricas['usuarios'] ?></strong><small>Usuarios registrados.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-user-shield metric-icon"></i><span class="metric-label">Roles</span><strong><?= (int) $metricas['roles'] ?></strong><small>Roles configurados.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-sliders metric-icon"></i><span class="metric-label">Parámetros</span><strong><?= (int) $metricas['parametros'] ?></strong><small>Variables editables.</small></div></div>
    </div>

    <div class="row g-3">
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/admin/usuarios" class="module-card h-100"><i class="fa-solid fa-users"></i><span>Usuarios</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/admin/roles" class="module-card h-100"><i class="fa-solid fa-user-shield"></i><span>Roles y permisos</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/admin/parametros" class="module-card h-100"><i class="fa-solid fa-sliders"></i><span>Parámetros</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/admin/auditoria" class="module-card h-100"><i class="fa-solid fa-clock-rotate-left"></i><span>Auditoría</span></a></div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
