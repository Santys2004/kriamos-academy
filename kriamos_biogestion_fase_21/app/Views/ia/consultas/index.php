<?php require dirname(dirname(__DIR__)) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Consultas IA</h1>
            <p class="text-muted mb-0">Historial de preguntas, respuestas, modo y proveedor utilizado.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/ia" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/ia/consultas/csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-outline-kriamos">Exportar CSV</a>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Usuario</label>
                    <select name="usuario_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= ($_GET['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Modo</th><th>Proveedor</th><th>Pregunta</th><th>Respuesta</th><th>Tokens</th></tr></thead>
                <tbody>
                    <?php foreach ($consultas as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['created_at']) ?></td>
                            <td><?= htmlspecialchars($c['email'] ?? 'Sistema') ?></td>
                            <td><?= htmlspecialchars($c['modo']) ?></td>
                            <td><?= htmlspecialchars($c['proveedor']) ?></td>
                            <td><?= htmlspecialchars(mb_substr($c['pregunta'], 0, 120)) ?></td>
                            <td><?= htmlspecialchars(mb_substr($c['respuesta'], 0, 160)) ?></td>
                            <td><?= (int) $c['tokens_estimados'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($consultas)): ?><tr><td colspan="7" class="text-center text-muted">Sin consultas registradas.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
