<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Seguridad del sistema</h1>
            <p class="text-muted mb-0">Eventos críticos, revisión de salud y preparación para producción.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/seguridad/health" class="btn btn-outline-kriamos"><i class="fa-solid fa-heart-pulse me-2"></i> Health check</a>
            <a href="<?= BASE_PATH ?>/despliegue/checklist" class="btn btn-kriamos"><i class="fa-solid fa-list-check me-2"></i> Checklist despliegue</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-circle-exclamation metric-icon"></i><span class="metric-label">Críticos</span><strong><?= (int) $metricas['criticos'] ?></strong><small>Eventos de mayor riesgo.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">Altos</span><strong><?= (int) $metricas['altos'] ?></strong><small>Revisión prioritaria.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">Medios</span><strong><?= (int) $metricas['medios'] ?></strong><small>Seguimiento.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-info-circle metric-icon"></i><span class="metric-label">Bajos</span><strong><?= (int) $metricas['bajos'] ?></strong><small>Informativos.</small></div></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Eventos recientes</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Nivel</th><th>Tipo</th><th>Descripción</th><th>Usuario</th><th>IP</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><?= htmlspecialchars($evento['nivel']) ?></td>
                            <td><?= htmlspecialchars($evento['tipo']) ?></td>
                            <td><?= htmlspecialchars($evento['descripcion']) ?></td>
                            <td><?= htmlspecialchars($evento['email'] ?? 'No disponible') ?></td>
                            <td><?= htmlspecialchars($evento['ip'] ?? '') ?></td>
                            <td><?= htmlspecialchars($evento['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($eventos)): ?><tr><td colspan="6" class="text-center text-muted">No hay eventos de seguridad registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
