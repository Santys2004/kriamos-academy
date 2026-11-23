<?php
$plainToken = $_SESSION['plain_bi_token'] ?? null;
unset($_SESSION['plain_bi_token']);
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">BI Externo</h1>
            <p class="text-muted mb-0">Conexión segura con Power BI, Metabase, Looker Studio, Superset u otras herramientas autorizadas.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/bi-externo/datasets" class="btn btn-outline-kriamos">Datasets</a>
            <a href="<?= BASE_PATH ?>/bi-externo/conexiones/create" class="btn btn-kriamos">Nueva conexión</a>
        </div>
    </div>

    <?php if ($plainToken): ?>
        <div class="alert alert-warning">
            <strong>Token BI generado. Cópielo ahora:</strong>
            <pre class="mt-2 mb-0"><?= htmlspecialchars($plainToken) ?></pre>
            <small>No volverá a mostrarse.</small>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-plug metric-icon"></i><span class="metric-label">Conexiones</span><strong><?= (int) $metricas['conexiones'] ?></strong><small>Clientes BI registrados.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-table metric-icon"></i><span class="metric-label">Datasets</span><strong><?= (int) $metricas['datasets'] ?></strong><small>Conjuntos disponibles.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-chart-line metric-icon"></i><span class="metric-label">Consumos hoy</span><strong><?= (int) $metricas['consumos_hoy'] ?></strong><small>Peticiones externas.</small></div></div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Conexiones BI</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nombre</th><th>Herramienta</th><th>Contacto</th><th>Estado</th><th>Token</th><th>Consumos</th><th>Último uso</th></tr></thead>
                <tbody>
                    <?php foreach ($connections as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nombre']) ?><br><small><?= htmlspecialchars($c['descripcion'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($c['herramienta']) ?></td>
                            <td><?= htmlspecialchars($c['contacto_email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['estado']) ?></td>
                            <td><code><?= htmlspecialchars($c['token_prefix']) ?>...</code></td>
                            <td><?= (int) $c['total_consumos'] ?></td>
                            <td><?= htmlspecialchars($c['last_used_at'] ?? 'Sin uso') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($connections)): ?><tr><td colspan="7" class="text-center text-muted">No hay conexiones registradas.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Últimos consumos BI externo</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Conexión</th><th>Dataset</th><th>Formato</th><th>Status</th><th>Registros</th><th>ms</th><th>IP</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['conexion_nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($l['dataset_codigo']) ?></td>
                            <td><?= htmlspecialchars($l['formato']) ?></td>
                            <td><?= (int) $l['status_code'] ?></td>
                            <td><?= (int) $l['response_rows'] ?></td>
                            <td><?= (int) $l['response_ms'] ?></td>
                            <td><?= htmlspecialchars($l['ip'] ?? '') ?></td>
                            <td><?= htmlspecialchars($l['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?><tr><td colspan="8" class="text-center text-muted">Sin consumos registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
