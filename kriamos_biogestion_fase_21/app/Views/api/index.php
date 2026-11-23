<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">API externa segura</h1>
            <p class="text-muted mb-0">Clientes, tokens, consumo y trazabilidad de integraciones autorizadas.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/api-admin/clientes/create" class="btn btn-kriamos">Crear cliente API</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-plug metric-icon"></i><span class="metric-label">Consumos hoy</span><strong><?= (int) $metricas['consumos_hoy'] ?></strong><small>Peticiones registradas.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-circle-check metric-icon"></i><span class="metric-label">2xx</span><strong><?= (int) $metricas['status_2xx'] ?></strong><small>Respuestas exitosas.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">4xx</span><strong><?= (int) $metricas['status_4xx'] ?></strong><small>Errores cliente.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-bug metric-icon"></i><span class="metric-label">5xx</span><strong><?= (int) $metricas['status_5xx'] ?></strong><small>Errores servidor.</small></div></div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Clientes API</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Cliente</th><th>Contacto</th><th>Estado</th><th>Tokens</th><th>IP permitidas</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['nombre']) ?><br><small class="text-muted"><?= htmlspecialchars($client['descripcion'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($client['contacto_email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['estado']) ?></td>
                            <td><?= (int) $client['total_tokens'] ?></td>
                            <td><small><?= htmlspecialchars($client['ip_permitidas'] ?? 'Sin restricción') ?></small></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/api-admin/clientes/show?id=<?= (int) $client['id'] ?>" class="btn btn-sm btn-outline-kriamos">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($clients)): ?><tr><td colspan="6" class="text-center text-muted">No hay clientes API registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Últimos consumos API</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Cliente</th><th>Método</th><th>Endpoint</th><th>Status</th><th>IP</th><th>ms</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['cliente_nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($log['metodo']) ?></td>
                            <td><small><?= htmlspecialchars($log['endpoint']) ?></small></td>
                            <td><?= (int) $log['status_code'] ?></td>
                            <td><?= htmlspecialchars($log['ip'] ?? '') ?></td>
                            <td><?= (int) $log['response_ms'] ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?><tr><td colspan="7" class="text-center text-muted">Sin consumos API.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
