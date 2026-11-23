<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4 report-page">
    <div class="row align-items-center mb-4 no-print">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Reporte de formatos</h1>
            <p class="text-muted mb-0">Consulta consolidada de formatos diligenciados.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/reportes" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/reportes/formatos/csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-outline-kriamos">CSV / Excel</a>
            <button onclick="window.print()" class="btn btn-kriamos">Imprimir</button>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4 no-print">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
                <div class="col-md-2">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= ($_GET['granja_id'] ?? '') == $granja['id'] ? 'selected' : '' ?>><?= htmlspecialchars($granja['sigla']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Formato</label>
                    <select name="tipo_formato_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= (int) $tipo['id'] ?>" <?= ($_GET['tipo_formato_id'] ?? '') == $tipo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tipo['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado_documental_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= (int) $estado['id'] ?>" <?= ($_GET['estado_documental_id'] ?? '') == $estado['id'] ? 'selected' : '' ?>><?= htmlspecialchars($estado['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1"><button class="btn btn-kriamos w-100" type="submit">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="print-header only-print">
        <img src="<?= BASE_PATH ?>/assets/img/logo-kriamos.png" class="report-logo">
        <div><h1>Reporte de formatos</h1><p>Kriamos BioGestión ICA | Generado: <?= date('Y-m-d H:i:s') ?></p></div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($resumenGranjas as $resumen): ?>
            <div class="col-md-6">
                <div class="card card-kriamos h-100">
                    <div class="card-body">
                        <h2 class="h5 text-kriamos"><?= htmlspecialchars($resumen['sigla']) ?> - <?= htmlspecialchars($resumen['nombre']) ?></h2>
                        <p class="mb-1"><strong>Total:</strong> <?= (int) $resumen['total_registros'] ?></p>
                        <p class="mb-1"><strong>Cerrados/Aprobados:</strong> <?= (int) $resumen['cerrados'] ?></p>
                        <p class="mb-1"><strong>Pendientes:</strong> <?= (int) $resumen['pendientes'] ?></p>
                        <p class="mb-0"><strong>No aplica:</strong> <?= (int) $resumen['no_aplica'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead><tr><th>Consecutivo</th><th>Formato</th><th>Granja</th><th>Galpón</th><th>Ciclo</th><th>Fecha</th><th>Estado</th><th>Usuario</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['codigo_consecutivo']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_nombre']) ?></td>
                            <td><?= htmlspecialchars($row['granja_sigla']) ?></td>
                            <td><?= htmlspecialchars($row['galpon_codigo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['ciclo_codigo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['fecha_formato']) ?></td>
                            <td><?= htmlspecialchars($row['estado_nombre']) ?></td>
                            <td><?= htmlspecialchars($row['usuario_email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($registros)): ?><tr><td colspan="8" class="text-center text-muted">No hay registros para los filtros seleccionados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
