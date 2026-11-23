<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Alertas y vencimientos</h1>
            <p class="text-muted mb-0">Control de formatos atrasados, certificaciones, productos vencidos y acciones correctivas.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('alertas.generar')): ?>
                <a href="<?= BASE_PATH ?>/alertas/generar" class="btn btn-outline-kriamos">
                    <i class="fa-solid fa-rotate me-2"></i> Generar alertas
                </a>
                <a href="<?= BASE_PATH ?>/alertas/enviar-correos" class="btn btn-kriamos">
                    <i class="fa-solid fa-envelope me-2"></i> Enviar correos
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-bell metric-icon"></i>
                <span class="metric-label">Pendientes</span>
                <strong><?= (int) $metricas['pendientes'] ?></strong>
                <small>Alertas sin cierre.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-circle-exclamation metric-icon"></i>
                <span class="metric-label">Críticas</span>
                <strong><?= (int) $metricas['criticas'] ?></strong>
                <small>Requieren atención inmediata.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-triangle-exclamation metric-icon"></i>
                <span class="metric-label">Altas</span>
                <strong><?= (int) $metricas['altas'] ?></strong>
                <small>Seguimiento prioritario.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-clock metric-icon"></i>
                <span class="metric-label">Medias</span>
                <strong><?= (int) $metricas['medias'] ?></strong>
                <small>Revisión programada.</small>
            </div>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Prioridad</th>
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>Granja</th>
                        <th>Fecha límite</th>
                        <th>Estado</th>
                        <th>Creada</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertas as $alerta): ?>
                        <tr>
                            <td><span class="badge prioridad-<?= htmlspecialchars($alerta['prioridad']) ?>"><?= htmlspecialchars($alerta['prioridad']) ?></span></td>
                            <td><?= htmlspecialchars($alerta['tipo']) ?></td>
                            <td><?= htmlspecialchars($alerta['titulo']) ?></td>
                            <td><?= htmlspecialchars($alerta['granja_sigla'] ?? 'General') ?></td>
                            <td><?= htmlspecialchars($alerta['fecha_limite'] ?? 'No definida') ?></td>
                            <td><?= htmlspecialchars($alerta['estado']) ?></td>
                            <td><?= htmlspecialchars($alerta['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_PATH ?>/alertas/show?id=<?= (int) $alerta['id'] ?>" class="btn btn-sm btn-outline-kriamos">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($alertas)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No hay alertas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
