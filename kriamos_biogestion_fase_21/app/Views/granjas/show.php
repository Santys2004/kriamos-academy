<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($granja['nombre']) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($granja['sigla']) ?> | Capacidad: <?= number_format((int) $granja['capacidad_pollos'], 0, ',', '.') ?> aves</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/granjas" class="btn btn-outline-secondary">Volver</a>
            <?php if (\App\Services\Auth::can('granjas.editar')): ?>
                <a href="<?= BASE_PATH ?>/granjas/edit?id=<?= (int) $granja['id'] ?>" class="btn btn-kriamos">Editar</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><span class="metric-label">Galpones registrados</span><strong><?= count($galpones) ?></strong><small>Meta: <?= (int) $granja['numero_galpones'] ?></small></div></div>
        <div class="col-md-4"><div class="metric-card"><span class="metric-label">Ciclos registrados</span><strong><?= count($ciclos) ?></strong><small>Histórico de ciclos productivos.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><span class="metric-label">Estado</span><strong><?= htmlspecialchars($granja['estado']) ?></strong><small>Estado operativo de la granja.</small></div></div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Galpones</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Código</th><th>Número</th><th>Capacidad</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php foreach ($galpones as $galpon): ?>
                        <tr>
                            <td><?= htmlspecialchars($galpon['codigo']) ?></td>
                            <td><?= (int) $galpon['numero'] ?></td>
                            <td><?= number_format((int) $galpon['capacidad_pollos'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($galpon['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Ciclos productivos</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Código</th><th>Galpón</th><th>Fase</th><th>Inicio</th><th>Aves actuales</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php foreach ($ciclos as $ciclo): ?>
                        <tr>
                            <td><?= htmlspecialchars($ciclo['codigo']) ?></td>
                            <td><?= htmlspecialchars($ciclo['galpon_codigo']) ?></td>
                            <td><?= htmlspecialchars($ciclo['fase_nombre']) ?></td>
                            <td><?= htmlspecialchars($ciclo['fecha_inicio']) ?></td>
                            <td><?= number_format((int) $ciclo['aves_actuales'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($ciclo['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
