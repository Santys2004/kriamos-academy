<?php require dirname(dirname(__DIR__)) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Datasets BI externos</h1>
            <p class="text-muted mb-0">Conjuntos de datos autorizados para consumo externo.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/bi-externo" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Código</th><th>Nombre</th><th>Vista SQL</th><th>Estado</th><th>Último archivo</th><th>Registros</th><th>Endpoint</th></tr></thead>
                <tbody>
                    <?php foreach ($datasets as $d): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                            <td><?= htmlspecialchars($d['nombre']) ?><br><small><?= htmlspecialchars($d['descripcion'] ?? '') ?></small></td>
                            <td><code><?= htmlspecialchars($d['vista_sql']) ?></code></td>
                            <td><?= htmlspecialchars($d['estado']) ?></td>
                            <td><small><?= htmlspecialchars($d['ultimo_archivo_path'] ?? '') ?></small></td>
                            <td><?= (int) ($d['ultima_cantidad_registros'] ?? 0) ?></td>
                            <td><small>/api/bi/v1/dataset?dataset=<?= htmlspecialchars($d['codigo']) ?>&format=json</small></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($datasets)): ?><tr><td colspan="7" class="text-center text-muted">No hay datasets configurados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
