<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Indicadores BI detallados</h1>
            <p class="text-muted mb-0">Detalle técnico de los indicadores calculados.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/bi" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <pre class="bi-json"><?= htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
