<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Galpones</h1>
            <p class="text-muted mb-0">Administración de galpones por granja.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('galpones.crear')): ?>
                <a href="<?= BASE_PATH ?>/galpones/create" class="btn btn-kriamos"><i class="fa-solid fa-plus me-2"></i> Nuevo galpón</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Granja</th><th>Código</th><th>Número</th><th>Capacidad</th><th>Área m²</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($galpones as $galpon): ?>
                        <tr>
                            <td><?= htmlspecialchars($galpon['granja_sigla']) ?></td>
                            <td><?= htmlspecialchars($galpon['codigo']) ?></td>
                            <td><?= (int) $galpon['numero'] ?></td>
                            <td><?= number_format((int) $galpon['capacidad_pollos'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($galpon['area_m2'] ?? '') ?></td>
                            <td><?= htmlspecialchars($galpon['estado']) ?></td>
                            <td class="text-end">
                                <?php if (\App\Services\Auth::can('galpones.crear')): ?>
                                    <a href="<?= BASE_PATH ?>/galpones/edit?id=<?= (int) $galpon['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($galpones)): ?><tr><td colspan="7" class="text-center text-muted">No hay galpones registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
