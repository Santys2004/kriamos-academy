<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Auditoría del sistema</h1>
            <p class="text-muted mb-0">Consulta de acciones registradas por usuario, módulo y fecha.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/admin" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/admin/auditoria/csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-outline-kriamos">Exportar CSV</a>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <select name="usuario_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= (int) $usuario['id'] ?>" <?= ($_GET['usuario_id'] ?? '') == $usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Módulo</label>
                    <select name="modulo" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($modulos as $modulo): ?>
                            <option value="<?= htmlspecialchars($modulo) ?>" <?= ($_GET['modulo'] ?? '') === $modulo ? 'selected' : '' ?>><?= htmlspecialchars($modulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Acción</label><input type="text" name="accion" class="form-control" value="<?= htmlspecialchars($_GET['accion'] ?? '') ?>"></div>
                <div class="col-md-1"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Tabla</th><th>Registro</th><th>IP</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? 'Sistema') ?></td>
                            <td><?= htmlspecialchars($row['modulo']) ?></td>
                            <td><?= htmlspecialchars($row['accion']) ?></td>
                            <td><?= htmlspecialchars($row['tabla_afectada'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['registro_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['ip'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($registros)): ?><tr><td colspan="7" class="text-center text-muted">No hay registros de auditoría.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
