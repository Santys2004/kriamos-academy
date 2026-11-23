<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Formatos ICA 3652</h1>
            <p class="text-muted mb-0">Diligenciamiento digital de formatos sanitarios, operativos y de bioseguridad.</p>
        </div>
        <div class="col-auto">
            <?php if (\App\Services\Auth::can('formatos.crear')): ?>
                <a href="<?= BASE_PATH ?>/formatos/create" class="btn btn-kriamos">
                    <i class="fa-solid fa-plus me-2"></i> Nuevo formato
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-file-signature metric-icon"></i>
                <span class="metric-label">Total registros</span>
                <strong><?= (int) $totalRegistros ?></strong>
                <small>Formatos diligenciados.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-calendar-day metric-icon"></i>
                <span class="metric-label">Registros de hoy</span>
                <strong><?= (int) $registrosHoy ?></strong>
                <small>Actividad documental diaria.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-ban metric-icon"></i>
                <span class="metric-label">No aplica</span>
                <strong><?= (int) $noAplica ?></strong>
                <small>Registros justificados por fase o condición.</small>
            </div>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Consecutivo</th>
                        <th>Formato</th>
                        <th>Granja</th>
                        <th>Galpón</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?= htmlspecialchars($registro['codigo_consecutivo']) ?></td>
                            <td><?= htmlspecialchars($registro['tipo_nombre']) ?></td>
                            <td><?= htmlspecialchars($registro['granja_sigla']) ?></td>
                            <td><?= htmlspecialchars($registro['galpon_codigo'] ?? 'No aplica') ?></td>
                            <td><?= htmlspecialchars($registro['fecha_formato']) ?></td>
                            <td><span class="badge badge-kriamos"><?= htmlspecialchars($registro['estado_nombre']) ?></span></td>
                            <td><?= htmlspecialchars(trim($registro['usuario_nombres'] . ' ' . $registro['usuario_apellidos'])) ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_PATH ?>/formatos/show?id=<?= (int) $registro['id'] ?>" class="btn btn-sm btn-outline-kriamos">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($registros)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No hay formatos diligenciados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
