<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Cola de correos SMTP</h1>
            <p class="text-muted mb-0">Correos pendientes, enviados y con error.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/notificaciones" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/notificaciones/procesar-cola" class="btn btn-kriamos">Procesar cola</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">Pendientes</span><strong><?= (int) $metricas['pendientes'] ?></strong><small>Correos por enviar.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-circle-check metric-icon"></i><span class="metric-label">Enviados</span><strong><?= (int) $metricas['enviados'] ?></strong><small>Correos enviados.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">Errores</span><strong><?= (int) $metricas['errores'] ?></strong><small>Correos con error.</small></div></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead><tr><th>Destinatario</th><th>Asunto</th><th>Prioridad</th><th>Estado</th><th>Intentos</th><th>Creado</th><th>Enviado</th></tr></thead>
                <tbody>
                    <?php foreach ($correos as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['destinatario']) ?></td>
                            <td><?= htmlspecialchars($c['asunto']) ?></td>
                            <td><?= htmlspecialchars($c['prioridad']) ?></td>
                            <td><?= htmlspecialchars($c['estado']) ?></td>
                            <td><?= (int) $c['intentos'] ?></td>
                            <td><?= htmlspecialchars($c['created_at']) ?></td>
                            <td><?= htmlspecialchars($c['enviado_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($correos)): ?><tr><td colspan="7" class="text-center text-muted">No hay correos en cola.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        Configure SMTP en <strong>Admin &gt; Parámetros</strong>, grupo <strong>smtp</strong>. En producción se recomienda validar SPF, DKIM y DMARC del dominio Kriamos.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
