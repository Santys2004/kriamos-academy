<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Checklist de despliegue</h1>
            <p class="text-muted mb-0">Puntos mínimos antes de activar el sistema en producción.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/seguridad" class="btn btn-outline-secondary">Volver</a>
            <button onclick="window.print()" class="btn btn-kriamos">Imprimir</button>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Área</th><th>Requisito</th><th>Crítico</th><th>Validado</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['area']) ?></td>
                            <td><?= htmlspecialchars($item['item']) ?></td>
                            <td><?= $item['critical'] ? '<span class="badge bg-danger">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                            <td><input type="checkbox" class="form-check-input checklist-box"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-warning mt-4">
        Este checklist debe ser validado por el responsable técnico del sistema, el área de TI y el responsable sanitario antes de iniciar operación oficial.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
