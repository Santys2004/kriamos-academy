<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Tablero gerencial BI</h1>
            <p class="text-muted mb-0">Indicadores de cumplimiento documental, alertas, calidad, certificaciones y uso del sistema.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/bi/export-csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-outline-kriamos">Exportar CSV</a>
            <?php if (\App\Services\Auth::can('bi.gestionar')): ?>
                <a href="<?= BASE_PATH ?>/bi/snapshot?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-kriamos">Generar snapshot</a>
            <?php endif; ?>
            <a href="<?= BASE_PATH ?>/bi/snapshots" class="btn btn-outline-secondary">Histórico</a>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($filters['fecha_inicio']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filters['fecha_fin']) ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= ($filters['granja_id'] ?? '') == $granja['id'] ? 'selected' : '' ?>><?= htmlspecialchars($granja['sigla'] . ' - ' . $granja['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <?php $r = $data['resumen']; $c = $data['certificaciones']; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-percent metric-icon"></i><span class="metric-label">Tasa de compleción</span><strong><?= number_format((float)$r['tasa_complecion'], 2, ',', '.') ?>%</strong><small>Score: <?= htmlspecialchars($score) ?></small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-file-signature metric-icon"></i><span class="metric-label">Formatos</span><strong><?= (int) $r['total_formatos'] ?></strong><small>Total del periodo.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">Atrasados</span><strong><?= (int) $r['formatos_atrasados'] ?></strong><small>Más de 3 días.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-certificate metric-icon"></i><span class="metric-label">Cert. vencidas</span><strong><?= (int) ($c['vencidas'] ?? 0) ?></strong><small>Requieren gestión.</small></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card card-kriamos h-100">
                <div class="card-header bg-white"><strong>Cumplimiento por granja</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead><tr><th>Granja</th><th>Total</th><th>Cerrados</th><th>Pendientes</th><th>Atrasados</th><th>Compleción</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['por_granja'] as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['sigla']) ?></td>
                                    <td><?= (int) $row['total_formatos'] ?></td>
                                    <td><?= (int) $row['cerrados'] ?></td>
                                    <td><?= (int) $row['pendientes'] ?></td>
                                    <td><?= (int) $row['atrasados'] ?></td>
                                    <td><div class="progress"><div class="progress-bar bg-kriamos" style="width: <?= (float) $row['tasa_complecion'] ?>%"><?= number_format((float)$row['tasa_complecion'], 1, ',', '.') ?>%</div></div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-kriamos h-100">
                <div class="card-header bg-white"><strong>Alertas por prioridad</strong></div>
                <div class="card-body">
                    <?php foreach ($data['alertas_por_prioridad'] as $row): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= htmlspecialchars($row['prioridad']) ?></span>
                            <strong><?= (int) $row['total'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($data['alertas_por_prioridad'])): ?><p class="text-muted">Sin alertas en el periodo.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-kriamos h-100">
                <div class="card-header bg-white"><strong>Mortalidad diaria consolidada</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Fecha</th><th>Granja</th><th>Aves muertas</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['mortalidad'] as $row): ?>
                                <tr><td><?= htmlspecialchars($row['fecha']) ?></td><td><?= htmlspecialchars($row['granja']) ?></td><td><?= (int) $row['aves_muertas'] ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (empty($data['mortalidad'])): ?><tr><td colspan="3" class="text-center text-muted">Sin registros de mortalidad.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-kriamos h-100">
                <div class="card-header bg-white"><strong>Incidencias de calidad</strong></div>
                <div class="card-body">
                    <?php foreach ($data['incidencias_por_nivel'] as $row): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= htmlspecialchars($row['nivel']) ?></span>
                            <strong><?= (int) $row['total'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($data['incidencias_por_nivel'])): ?><p class="text-muted">Sin incidencias en el periodo.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
