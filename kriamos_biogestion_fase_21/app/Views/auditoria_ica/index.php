<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Auditoría ICA</h1>
            <p class="text-muted mb-0">Expedientes, matriz documental, hallazgos, planes de acción y paquetes de auditoría.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/auditoria-ica/create" class="btn btn-kriamos">Crear expediente</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="metric-card"><i class="fa-solid fa-file-circle-plus metric-icon"></i><span class="metric-label">Borrador</span><strong><?= (int) $metricas['borrador'] ?></strong><small>En creación.</small></div></div>
        <div class="col-md-2"><div class="metric-card"><i class="fa-solid fa-spinner metric-icon"></i><span class="metric-label">Preparación</span><strong><?= (int) $metricas['preparacion'] ?></strong><small>En revisión.</small></div></div>
        <div class="col-md-2"><div class="metric-card"><i class="fa-solid fa-circle-check metric-icon"></i><span class="metric-label">Cerrados</span><strong><?= (int) $metricas['cerrado'] ?></strong><small>Finalizados.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">Hallazgos críticos</span><strong><?= (int) $metricas['hallazgos_criticos'] ?></strong><small>Atención inmediata.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-calendar-xmark metric-icon"></i><span class="metric-label">Planes vencidos</span><strong><?= (int) $metricas['planes_vencidos'] ?></strong><small>Compromisos vencidos.</small></div></div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($granjas as $g): ?>
                            <option value="<?= (int) $g['id'] ?>" <?= ($_GET['granja_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['sigla'] . ' - ' . $g['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="borrador" <?= ($_GET['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                        <option value="preparacion" <?= ($_GET['estado'] ?? '') === 'preparacion' ? 'selected' : '' ?>>Preparación</option>
                        <option value="en_revision" <?= ($_GET['estado'] ?? '') === 'en_revision' ? 'selected' : '' ?>>En revisión</option>
                        <option value="cerrado" <?= ($_GET['estado'] ?? '') === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
                <div class="col-md-3"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Código</th><th>Nombre</th><th>Granja</th><th>Periodo</th><th>Estado</th><th>Hallazgos</th><th>Planes</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($expedientes as $e): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($e['codigo']) ?></code></td>
                            <td><?= htmlspecialchars($e['nombre']) ?></td>
                            <td><?= htmlspecialchars($e['granja_sigla']) ?></td>
                            <td><?= htmlspecialchars($e['fecha_inicio']) ?> / <?= htmlspecialchars($e['fecha_fin'] ?? '') ?></td>
                            <td><?= htmlspecialchars($e['estado']) ?></td>
                            <td><?= (int) $e['total_hallazgos'] ?></td>
                            <td><?= (int) $e['total_planes'] ?></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/auditoria-ica/show?id=<?= (int) $e['id'] ?>" class="btn btn-sm btn-outline-kriamos">Abrir</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($expedientes)): ?><tr><td colspan="8" class="text-center text-muted">No hay expedientes registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
