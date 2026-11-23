<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Manual interactivo</h1>
            <p class="text-muted mb-0">Guía paso a paso para el uso de cada módulo del sistema.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/ayuda" class="btn btn-outline-secondary">Centro de ayuda</a></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card card-kriamos">
                <div class="card-header bg-white"><strong>Módulos</strong></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($modulos as $m): ?>
                        <a href="<?= BASE_PATH ?>/ayuda/manual?modulo=<?= urlencode($m) ?>" class="list-group-item list-group-item-action <?= $modulo === $m ? 'active-kriamos' : '' ?>">
                            <?= htmlspecialchars($m) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card card-kriamos">
                <div class="card-header bg-white"><strong><?= htmlspecialchars($modulo) ?></strong></div>
                <div class="card-body">
                    <?php foreach ($pasos as $paso): ?>
                        <div class="manual-step">
                            <div class="manual-number"><?= (int) $paso['orden'] ?></div>
                            <div>
                                <h2 class="h5 text-kriamos"><?= htmlspecialchars($paso['titulo']) ?></h2>
                                <p><?= nl2br(htmlspecialchars($paso['descripcion'])) ?></p>
                                <?php if (!empty($paso['ruta_sistema'])): ?>
                                    <a href="<?= htmlspecialchars($paso['ruta_sistema']) ?>" class="btn btn-sm btn-outline-kriamos">Ir al módulo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($pasos)): ?>
                        <div class="alert alert-warning">No hay pasos registrados para este módulo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
