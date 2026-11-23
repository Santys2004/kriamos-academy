<?php require dirname(__DIR__) . '/layouts/header.php'; ?>
<section class="container-fluid px-4 py-4 report-page">
    <div class="row align-items-center mb-4 no-print"><div class="col"><h1 class="h3 fw-bold text-kriamos mb-1">Reporte de certificaciones</h1><p class="text-muted mb-0">Vigencia de certificaciones por granja.</p></div><div class="col-auto"><a href="<?= BASE_PATH ?>/reportes" class="btn btn-outline-secondary">Volver</a><button onclick="window.print()" class="btn btn-kriamos">Imprimir</button></div></div>
    <div class="card card-kriamos"><div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Granja</th><th>Número</th><th>Entidad</th><th>Expedición</th><th>Vencimiento</th><th>Estado</th><th>Observaciones</th></tr></thead>
            <tbody>
                <?php foreach ($certificaciones as $row): ?>
                    <tr><td><?= htmlspecialchars($row['granja_sigla'] . ' - ' . $row['granja_nombre']) ?></td><td><?= htmlspecialchars($row['numero_certificacion'] ?? '') ?></td><td><?= htmlspecialchars($row['entidad']) ?></td><td><?= htmlspecialchars($row['fecha_expedicion']) ?></td><td><?= htmlspecialchars($row['fecha_vencimiento']) ?></td><td><?= htmlspecialchars($row['estado']) ?></td><td><?= htmlspecialchars($row['observaciones'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($certificaciones)): ?><tr><td colspan="7" class="text-center text-muted">Sin certificaciones registradas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
</section>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
