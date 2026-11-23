<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Centro de notificaciones</h1>
            <p class="text-muted mb-0">Notificaciones internas, correos SMTP y push PWA.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('notificaciones.gestionar')): ?>
                <a href="<?= BASE_PATH ?>/notificaciones/generar-desde-alertas" class="btn btn-outline-kriamos">Generar desde alertas</a>
                <a href="<?= BASE_PATH ?>/notificaciones/correos" class="btn btn-kriamos">Cola de correos</a>
            <?php endif; ?>
            <a href="<?= BASE_PATH ?>/notificaciones/push" class="btn btn-outline-secondary">Push</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-bell metric-icon"></i><span class="metric-label">Pendientes</span><strong><?= (int) $metricas['pendientes'] ?></strong><small>Notificaciones sin leer.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-paper-plane metric-icon"></i><span class="metric-label">Enviadas</span><strong><?= (int) $metricas['enviadas'] ?></strong><small>Eventos enviados.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-eye metric-icon"></i><span class="metric-label">Leídas</span><strong><?= (int) $metricas['leidas'] ?></strong><small>Notificaciones vistas.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-mobile-screen metric-icon"></i><span class="metric-label">Push activas</span><strong><?= (int) $metricas['push_activas'] ?></strong><small>Dispositivos registrados.</small></div></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Prioridad</th><th>Título</th><th>Canal</th><th>Estado</th><th>Fecha</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($notificaciones as $n): ?>
                        <tr>
                            <td><?= htmlspecialchars($n['prioridad']) ?></td>
                            <td><?= htmlspecialchars($n['titulo']) ?><br><small class="text-muted"><?= htmlspecialchars(substr($n['mensaje'], 0, 120)) ?></small></td>
                            <td><?= htmlspecialchars($n['canal']) ?></td>
                            <td><?= htmlspecialchars($n['estado']) ?></td>
                            <td><?= htmlspecialchars($n['created_at']) ?></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/notificaciones/show?id=<?= (int) $n['id'] ?>" class="btn btn-sm btn-outline-kriamos">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($notificaciones)): ?><tr><td colspan="6" class="text-center text-muted">No hay notificaciones.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
