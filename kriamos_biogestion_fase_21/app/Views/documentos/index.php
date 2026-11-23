<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Núcleo documental</h1>
            <p class="text-muted mb-0">Gestión de POE, certificaciones, fichas técnicas, hojas de seguridad, evidencias y documentos de soporte.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('documentos.crear')): ?>
                <a href="<?= BASE_PATH ?>/documentos/create" class="btn btn-kriamos"><i class="fa-solid fa-upload me-2"></i> Cargar documento</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-folder metric-icon"></i><span class="metric-label">Total documentos</span><strong><?= (int) $metricas['total'] ?></strong><small>Repositorio interno.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-circle-check metric-icon"></i><span class="metric-label">Vigentes</span><strong><?= (int) $metricas['vigentes'] ?></strong><small>Documentos activos.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">En revisión</span><strong><?= (int) $metricas['revision'] ?></strong><small>Pendientes por validar.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-box-archive metric-icon"></i><span class="metric-label">Obsoletos</span><strong><?= (int) $metricas['obsoletos'] ?></strong><small>Control de versiones.</small></div></div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Buscar</label><input type="text" name="q" class="form-control" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Título, descripción o extensión"></div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_documental_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['id'] ?>" <?= ($_GET['categoria_documental_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= ($_GET['granja_id'] ?? '') == $granja['id'] ? 'selected' : '' ?>><?= htmlspecialchars($granja['sigla']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="vigente" <?= ($_GET['estado'] ?? '') === 'vigente' ? 'selected' : '' ?>>Vigente</option>
                        <option value="en_revision" <?= ($_GET['estado'] ?? '') === 'en_revision' ? 'selected' : '' ?>>En revisión</option>
                        <option value="obsoleto" <?= ($_GET['estado'] ?? '') === 'obsoleto' ? 'selected' : '' ?>>Obsoleto</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100" type="submit">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Título</th><th>Categoría</th><th>Granja</th><th>Ext.</th><th>Versión</th><th>Estado</th><th>Actualizado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($doc['descripcion'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($doc['categoria_nombre']) ?></td>
                            <td><?= htmlspecialchars($doc['granja_sigla'] ?? 'General') ?></td>
                            <td><?= htmlspecialchars(strtoupper($doc['extension'] ?? '')) ?></td>
                            <td>v<?= (int) $doc['version'] ?></td>
                            <td><?= htmlspecialchars($doc['estado']) ?></td>
                            <td><?= htmlspecialchars($doc['updated_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_PATH ?>/documentos/show?id=<?= (int) $doc['id'] ?>" class="btn btn-sm btn-outline-kriamos"><i class="fa-solid fa-eye"></i></a>
                                <a href="<?= BASE_PATH ?>/documentos/download?id=<?= (int) $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-download"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($documentos)): ?><tr><td colspan="8" class="text-center text-muted">No hay documentos registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
