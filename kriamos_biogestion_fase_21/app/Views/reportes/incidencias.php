<?php require dirname(__DIR__) . '/layouts/header.php'; ?>
<section class="container-fluid px-4 py-4 report-page">
    <div class="row align-items-center mb-4 no-print"><div class="col"><h1 class="h3 fw-bold text-kriamos mb-1">Reporte de incidencias de calidad</h1><p class="text-muted mb-0">Validaciones, inconsistencias y alertas de calidad de datos.</p></div><div class="col-auto"><a href="<?= BASE_PATH ?>/reportes" class="btn btn-outline-secondary">Volver</a><button onclick="window.print()" class="btn btn-kriamos">Imprimir</button></div></div>
    <form method="get" class="card card-kriamos mb-4 no-print"><div class="card-body"><div class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div><div class="col-md-4"><button class="btn btn-kriamos w-100" type="submit">Filtrar</button></div></div></div></form>
    <div class="card card-kriamos"><div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Registro</th><th>Granja</th><th>Formato</th><th>Tipo</th><th>Nivel</th><th>Mensaje</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
                <?php foreach ($incidencias as $row): ?>
                    <tr><td><?= htmlspecialchars($row['codigo_consecutivo']) ?></td><td><?= htmlspecialchars($row['granja_sigla']) ?></td><td><?= htmlspecialchars($row['tipo_formato']) ?></td><td><?= htmlspecialchars($row['tipo']) ?></td><td><?= htmlspecialchars($row['nivel']) ?></td><td><?= htmlspecialchars($row['mensaje']) ?></td><td><?= htmlspecialchars($row['estado']) ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($incidencias)): ?><tr><td colspan="8" class="text-center text-muted">Sin incidencias.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
</section>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
