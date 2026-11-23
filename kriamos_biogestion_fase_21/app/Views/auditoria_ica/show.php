<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($expediente['codigo']) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($expediente['nombre']) ?> | <?= htmlspecialchars($expediente['granja_sigla']) ?></p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/auditoria-ica" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/auditoria-ica/csv?id=<?= (int) $expediente['id'] ?>" class="btn btn-outline-kriamos">Exportar matriz CSV</a>
            <a href="<?= BASE_PATH ?>/auditoria-ica/generar-paquete?id=<?= (int) $expediente['id'] ?>" class="btn btn-kriamos">Generar paquete</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-percent metric-icon"></i><span class="metric-label">Cumplimiento</span><strong><?= number_format((float) ($resumen['porcentaje_cumplimiento'] ?? 0), 2, ',', '.') ?>%</strong><small>Matriz documental.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-list-check metric-icon"></i><span class="metric-label">Ítems matriz</span><strong><?= (int) ($resumen['total'] ?? 0) ?></strong><small>Total requisitos.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">Hallazgos</span><strong><?= count($hallazgos) ?></strong><small>Registrados.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-calendar-check metric-icon"></i><span class="metric-label">Planes</span><strong><?= count($planes) ?></strong><small>Acciones correctivas.</small></div></div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Matriz documental ICA</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead><tr><th>Categoría</th><th>Requisito</th><th>Soporte</th><th>Estado</th><th>Observación</th><th>Actualizar</th></tr></thead>
                <tbody>
                    <?php foreach ($matriz as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['categoria']) ?></td>
                            <td><?= htmlspecialchars($m['requisito']) ?><br><small class="text-muted"><?= htmlspecialchars($m['descripcion'] ?? '') ?></small></td>
                            <td>
                                <?php if ($m['documento_id']): ?>Documento: <?= htmlspecialchars($m['documento_titulo']) ?><br><?php endif; ?>
                                <?php if ($m['tipo_formato_id']): ?>Formato: <?= htmlspecialchars($m['tipo_formato_nombre']) ?><br><?php endif; ?>
                                <?php if ($m['certificacion_id']): ?>Certificación: <?= htmlspecialchars($m['numero_certificacion']) ?><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($m['estado_cumplimiento']) ?></td>
                            <td><?= htmlspecialchars($m['observacion'] ?? '') ?></td>
                            <td>
                                <form method="post" action="<?= BASE_PATH ?>/auditoria-ica/matriz/update" class="d-flex gap-1">
                                    <?= Csrf::input() ?>
                                    <input type="hidden" name="expediente_id" value="<?= (int) $expediente['id'] ?>">
                                    <input type="hidden" name="matriz_id" value="<?= (int) $m['id'] ?>">
                                    <select name="estado_cumplimiento" class="form-select form-select-sm">
                                        <option value="pendiente" <?= $m['estado_cumplimiento'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="pendiente_revision" <?= $m['estado_cumplimiento'] === 'pendiente_revision' ? 'selected' : '' ?>>Pendiente revisión</option>
                                        <option value="cumple" <?= $m['estado_cumplimiento'] === 'cumple' ? 'selected' : '' ?>>Cumple</option>
                                        <option value="no_cumple" <?= $m['estado_cumplimiento'] === 'no_cumple' ? 'selected' : '' ?>>No cumple</option>
                                        <option value="no_aplica" <?= $m['estado_cumplimiento'] === 'no_aplica' ? 'selected' : '' ?>>No aplica</option>
                                    </select>
                                    <input type="text" name="observacion" class="form-control form-control-sm" placeholder="Observación">
                                    <button class="btn btn-sm btn-outline-kriamos">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($matriz)): ?><tr><td colspan="6" class="text-center text-muted">Sin matriz documental.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <form method="post" action="<?= BASE_PATH ?>/auditoria-ica/hallazgos/store" class="card card-kriamos h-100">
                <?= Csrf::input() ?>
                <input type="hidden" name="expediente_id" value="<?= (int) $expediente['id'] ?>">
                <div class="card-header bg-white"><strong>Registrar hallazgo</strong></div>
                <div class="card-body">
                    <div class="mb-2"><input type="text" name="titulo" class="form-control" placeholder="Título del hallazgo" required></div>
                    <div class="mb-2"><textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción" required></textarea></div>
                    <div class="mb-2">
                        <select name="criticidad" class="form-select">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>
                    <button class="btn btn-kriamos">Registrar hallazgo</button>
                </div>
            </form>
        </div>

        <div class="col-lg-6">
            <form method="post" action="<?= BASE_PATH ?>/auditoria-ica/planes/store" class="card card-kriamos h-100">
                <?= Csrf::input() ?>
                <input type="hidden" name="expediente_id" value="<?= (int) $expediente['id'] ?>">
                <div class="card-header bg-white"><strong>Crear plan de acción</strong></div>
                <div class="card-body">
                    <div class="mb-2">
                        <select name="hallazgo_id" class="form-select">
                            <option value="">Sin hallazgo asociado</option>
                            <?php foreach ($hallazgos as $h): ?>
                                <option value="<?= (int) $h['id'] ?>"><?= htmlspecialchars($h['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><textarea name="accion" class="form-control" rows="3" placeholder="Acción correctiva" required></textarea></div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <select name="responsable_id" class="form-select">
                                <option value="">Responsable</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= (int) $u['id'] ?>"><?= htmlspecialchars($u['email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><input type="date" name="fecha_compromiso" class="form-control" required></div>
                    </div>
                    <button class="btn btn-kriamos">Crear plan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Hallazgos</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Criticidad</th><th>Título</th><th>Estado</th><th>Reportado por</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($hallazgos as $h): ?>
                        <tr><td><?= htmlspecialchars($h['criticidad']) ?></td><td><?= htmlspecialchars($h['titulo']) ?></td><td><?= htmlspecialchars($h['estado']) ?></td><td><?= htmlspecialchars($h['reportado_por_email'] ?? '') ?></td><td><?= htmlspecialchars($h['created_at']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($hallazgos)): ?><tr><td colspan="5" class="text-center text-muted">Sin hallazgos.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Planes de acción</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Hallazgo</th><th>Acción</th><th>Responsable</th><th>Compromiso</th><th>Estado</th><th>Avance</th></tr></thead>
                <tbody>
                    <?php foreach ($planes as $p): ?>
                        <tr><td><?= htmlspecialchars($p['hallazgo_titulo'] ?? '') ?></td><td><?= htmlspecialchars($p['accion']) ?></td><td><?= htmlspecialchars($p['responsable_email'] ?? '') ?></td><td><?= htmlspecialchars($p['fecha_compromiso']) ?></td><td><?= htmlspecialchars($p['estado']) ?></td><td><?= (int) $p['avance_porcentaje'] ?>%</td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($planes)): ?><tr><td colspan="6" class="text-center text-muted">Sin planes de acción.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Exportaciones generadas</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Tipo</th><th>Archivo</th><th>Hash SHA-256</th><th>Archivos</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($exportaciones as $ex): ?>
                        <tr><td><?= htmlspecialchars($ex['tipo']) ?></td><td><small><?= htmlspecialchars($ex['archivo_path']) ?></small></td><td><small><code><?= htmlspecialchars(substr($ex['hash_sha256'], 0, 24)) ?>...</code></small></td><td><?= (int) $ex['total_archivos'] ?></td><td><?= htmlspecialchars($ex['created_at']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($exportaciones)): ?><tr><td colspan="5" class="text-center text-muted">Sin exportaciones.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
