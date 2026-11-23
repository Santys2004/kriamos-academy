<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Histórico de snapshots BI</h1>
            <p class="text-muted mb-0">Cortes históricos de indicadores gerenciales.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/bi" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Fecha snapshot</th><th>Usuario</th><th>Filtros</th><th>Creado</th></tr></thead>
                <tbody>
                    <?php foreach ($snapshots as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['fecha_snapshot']) ?></td>
                            <td><?= htmlspecialchars($s['email'] ?? 'Sistema') ?></td>
                            <td><small><?= htmlspecialchars($s['filtros_json']) ?></small></td>
                            <td><?= htmlspecialchars($s['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($snapshots)): ?><tr><td colspan="4" class="text-center text-muted">Sin snapshots registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
