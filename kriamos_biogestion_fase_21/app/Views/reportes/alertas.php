<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4 report-page">
    <div class="row align-items-center mb-4 no-print">
        <div class="col"><h1 class="h3 fw-bold text-kriamos mb-1">Reporte de alertas</h1><p class="text-muted mb-0">Alertas, vencimientos y eventos críticos.</p></div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/reportes" class="btn btn-outline-secondary">Volver</a><a href="<?= BASE_PATH ?>/reportes/alertas/csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-outline-kriamos">CSV / Excel</a><button onclick="window.print()" class="btn btn-kriamos">Imprimir</button></div>
    </div>

    <form method="get" class="card card-kriamos mb-4 no-print">
        <div class="card-body"><div class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label">Granja</label><select name="granja_id" class="form-select"><option value="">Todas</option><?php foreach ($granjas as $granja): ?><option value="<?= (int) $granja['id'] ?>" <?= ($_GET['granja_id'] ?? '') == $granja['id'] ? 'selected' : '' ?>><?= htmlspecialchars($granja['sigla']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><button class="btn btn-kriamos w-100" type="submit">Filtrar</button></div>
        </div></div>
    </form>

    <div class="card card-kriamos"><div class="card-body table-responsive">
        <table class="table table-sm table-bordered">
            <thead><tr><th>Prioridad</th><th>Tipo</th><th>Título</th><th>Granja</th><th>Estado</th><th>Fecha límite</th><th>Creada</th></tr></thead>
            <tbody>
                <?php foreach ($alertas as $row): ?>
                    <tr><td><?= htmlspecialchars($row['prioridad']) ?></td><td><?= htmlspecialchars($row['tipo']) ?></td><td><?= htmlspecialchars($row['titulo']) ?></td><td><?= htmlspecialchars($row['granja_sigla'] ?? '') ?></td><td><?= htmlspecialchars($row['estado']) ?></td><td><?= htmlspecialchars($row['fecha_limite'] ?? '') ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($alertas)): ?><tr><td colspan="7" class="text-center text-muted">Sin alertas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
