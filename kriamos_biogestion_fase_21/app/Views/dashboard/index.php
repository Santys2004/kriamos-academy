<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Panel de control</h1>
            <p class="text-muted mb-0">
                Bienvenido, <?= htmlspecialchars($authUser['nombres'] ?? 'Usuario') ?>.
                Rol: <?= htmlspecialchars($authUser['rol_nombre'] ?? 'No definido') ?>.
            </p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('auditoria.ver')): ?>
                <a href="<?= BASE_PATH ?>/database/schema" class="btn btn-kriamos">
                    <i class="fa-solid fa-database me-2"></i> Ver estructura
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$dbOk): ?>
        <div class="alert alert-warning">
            <h2 class="h5">Base de datos pendiente por importar</h2>
            <p class="mb-2">Ejecute los archivos SQL de Fase 02 y Fase 03.</p>
            <p class="small mb-0">Detalle técnico: <?= htmlspecialchars($errorDb ?? 'No disponible') ?></p>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-warehouse metric-icon"></i>
                <span class="metric-label">Granjas registradas</span>
                <strong><?= (int) $metricas['granjas'] ?></strong>
                <small>GALM y GAEE como datos iniciales.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-house-flag metric-icon"></i>
                <span class="metric-label">Galpones configurados</span>
                <strong><?= (int) $metricas['galpones'] ?></strong>
                <small>14 GALM y 11 GAEE.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-file-signature metric-icon"></i>
                <span class="metric-label">Tipos de formatos</span>
                <strong><?= (int) $metricas['tipos_formato'] ?></strong>
                <small>Formatos base Resolución ICA 3652.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-rotate metric-icon"></i>
                <span class="metric-label">Ciclos activos</span>
                <strong><?= (int) $metricas['ciclos_activos'] ?></strong>
                <small>Ciclos productivos en operación.</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card card-kriamos h-100">
                <div class="card-body">
                    <h2 class="h5 fw-bold text-kriamos">Capacidad total inicial</h2>
                    <p class="display-6 fw-bold mb-0"><?= number_format((int) $metricas['capacidad_total'], 0, ',', '.') ?></p>
                    <p class="text-muted">aves de engorde entre GALM y GAEE.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-kriamos h-100">
                <div class="card-body">
                    <h2 class="h5 fw-bold text-kriamos">Certificaciones próximas a vencer</h2>
                    <p class="display-6 fw-bold mb-0"><?= (int) $metricas['certificaciones_90'] ?></p>
                    <p class="text-muted">Conteo preparado para vencimientos a 90 días.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/granjas" class="module-card"><i class="fa-solid fa-warehouse"></i><span>Administrar granjas</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/galpones" class="module-card"><i class="fa-solid fa-house-flag"></i><span>Administrar galpones</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/ciclos" class="module-card"><i class="fa-solid fa-rotate"></i><span>Administrar ciclos</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/formatos" class="module-card"><i class="fa-solid fa-file-signature"></i><span>Administrar formatos</span></a></div>
        <div class="col-md-3"><a href="<?= BASE_PATH ?>/alertas" class="module-card"><i class="fa-solid fa-bell"></i><span>Gestionar alertas</span></a></div>
    </div>

    <?php if ($dbOk): ?>
        <div class="row g-3">
            <?php foreach ($granjas as $granja): ?>
                <div class="col-lg-6">
                    <div class="card card-kriamos h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h5 fw-bold text-kriamos mb-0"><?= htmlspecialchars($granja['nombre']) ?></h2>
                                <small class="text-muted"><?= htmlspecialchars($granja['sigla']) ?></small>
                            </div>
                            <span class="badge badge-kriamos"><?= number_format((int) $granja['capacidad_pollos'], 0, ',', '.') ?> aves</span>
                        </div>
                        <div class="card-body">
                            <p><strong>Galpones:</strong> <?= (int) $granja['numero_galpones'] ?></p>
                            <p><strong>Estado:</strong> <?= htmlspecialchars($granja['estado']) ?></p>
                            <div class="progress progress-kriamos mb-3">
                                <div class="progress-bar" style="width:0%">0%</div>
                            </div>
                            <small class="text-muted">La tasa de compleción será funcional al implementar los formatos digitales.</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
