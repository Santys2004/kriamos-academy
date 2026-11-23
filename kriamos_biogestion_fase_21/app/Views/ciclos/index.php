<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Ciclos productivos</h1>
            <p class="text-muted mb-0">Gestión de ciclos por granja, galpón y fase productiva.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('ciclos.crear')): ?>
                <a href="<?= BASE_PATH ?>/ciclos/create" class="btn btn-kriamos"><i class="fa-solid fa-plus me-2"></i> Nuevo ciclo</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Código</th><th>Granja</th><th>Galpón</th><th>Fase</th><th>Inicio</th><th>Edad</th><th>Aves actuales</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($ciclos as $ciclo): ?>
                        <tr>
                            <td><?= htmlspecialchars($ciclo['codigo']) ?></td>
                            <td><?= htmlspecialchars($ciclo['granja_sigla']) ?></td>
                            <td><?= htmlspecialchars($ciclo['galpon_codigo']) ?></td>
                            <td><span class="badge badge-kriamos"><?= htmlspecialchars($ciclo['fase_nombre']) ?></span></td>
                            <td><?= htmlspecialchars($ciclo['fecha_inicio']) ?></td>
                            <td><?= (int) $ciclo['edad_aves_dias'] ?> días</td>
                            <td><?= number_format((int) $ciclo['aves_actuales'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($ciclo['estado']) ?></td>
                            <td class="text-end">
                                <?php if (\App\Services\Auth::can('ciclos.crear')): ?>
                                    <a href="<?= BASE_PATH ?>/ciclos/edit?id=<?= (int) $ciclo['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ciclos)): ?><tr><td colspan="9" class="text-center text-muted">No hay ciclos registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
