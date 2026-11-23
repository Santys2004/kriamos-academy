<?php require dirname(dirname(__DIR__)) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Contextos IA</h1>
            <p class="text-muted mb-0">Información documental local utilizada por el asistente IA.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/ia" class="btn btn-outline-secondary">Volver</a>
            <?php if (\App\Services\Auth::can('ia.gestionar')): ?>
                <a href="<?= BASE_PATH ?>/ia/contextos/indexar" class="btn btn-kriamos">Indexar contexto</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5"><label class="form-label">Buscar</label><input type="text" name="q" class="form-control" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="documento" <?= ($_GET['tipo'] ?? '') === 'documento' ? 'selected' : '' ?>>Documento</option>
                        <option value="ayuda" <?= ($_GET['tipo'] ?? '') === 'ayuda' ? 'selected' : '' ?>>Ayuda</option>
                        <option value="formato_tipo" <?= ($_GET['tipo'] ?? '') === 'formato_tipo' ? 'selected' : '' ?>>Tipo de formato</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Título</th><th>Tipo</th><th>Sensibilidad</th><th>Estado</th><th>Actualizado</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($contextos as $ctx): ?>
                        <tr>
                            <td><?= htmlspecialchars($ctx['titulo']) ?><br><small class="text-muted"><?= htmlspecialchars($ctx['contenido_resumen'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($ctx['tipo']) ?></td>
                            <td><?= htmlspecialchars($ctx['sensibilidad']) ?></td>
                            <td><?= htmlspecialchars($ctx['estado']) ?></td>
                            <td><?= htmlspecialchars($ctx['updated_at']) ?></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/ia/contextos/show?id=<?= (int) $ctx['id'] ?>" class="btn btn-sm btn-outline-kriamos">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($contextos)): ?><tr><td colspan="6" class="text-center text-muted">No hay contextos indexados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
