<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Granjas</h1>
            <p class="text-muted mb-0">Administración de unidades productivas de Kriamos.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('granjas.crear')): ?>
                <a href="<?= BASE_PATH ?>/granjas/create" class="btn btn-kriamos">
                    <i class="fa-solid fa-plus me-2"></i> Nueva granja
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Sigla</th>
                            <th>Nombre</th>
                            <th>Capacidad</th>
                            <th>Galpones</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($granjas as $granja): ?>
                            <tr>
                                <td><span class="badge badge-kriamos"><?= htmlspecialchars($granja['sigla']) ?></span></td>
                                <td><?= htmlspecialchars($granja['nombre']) ?></td>
                                <td><?= number_format((int) $granja['capacidad_pollos'], 0, ',', '.') ?> aves</td>
                                <td><?= (int) $granja['galpones_registrados'] ?> / <?= (int) $granja['numero_galpones'] ?></td>
                                <td><?= htmlspecialchars($granja['estado']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_PATH ?>/granjas/show?id=<?= (int) $granja['id'] ?>" class="btn btn-sm btn-outline-kriamos">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (\App\Services\Auth::can('granjas.editar')): ?>
                                        <a href="<?= BASE_PATH ?>/granjas/edit?id=<?= (int) $granja['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($granjas)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay granjas registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
