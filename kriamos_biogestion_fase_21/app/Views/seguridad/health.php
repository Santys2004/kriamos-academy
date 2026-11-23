<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Salud del sistema</h1>
            <p class="text-muted mb-0">Verificación generada: <?= htmlspecialchars($health['generated_at']) ?></p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/seguridad" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="alert <?= $health['ok'] ? 'alert-success' : 'alert-warning' ?>">
        Estado general: <strong><?= $health['ok'] ? 'Correcto' : 'Requiere revisión' ?></strong>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead><tr><th>Verificación</th><th>Estado</th><th>Valor actual</th><th>Esperado</th></tr></thead>
                <tbody>
                    <?php foreach ($health['checks'] as $check): ?>
                        <tr>
                            <td><?= htmlspecialchars($check['name']) ?></td>
                            <td><?= $check['ok'] ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-warning text-dark">Revisar</span>' ?></td>
                            <td><?= htmlspecialchars($check['value']) ?></td>
                            <td><?= htmlspecialchars($check['expected']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
