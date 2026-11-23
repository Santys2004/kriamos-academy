<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container px-4 py-4">
    <div class="mb-4">
        <a href="<?= BASE_PATH ?>/ayuda" class="btn btn-outline-secondary mb-3">Volver</a>
        <span class="badge badge-kriamos"><?= htmlspecialchars($articulo['modulo']) ?></span>
        <h1 class="h3 fw-bold text-kriamos mt-2"><?= htmlspecialchars($articulo['titulo']) ?></h1>
        <p class="text-muted"><?= htmlspecialchars($articulo['resumen'] ?? '') ?></p>
    </div>

    <div class="card card-kriamos">
        <div class="card-body ayuda-content">
            <?= nl2br(htmlspecialchars($articulo['contenido'])) ?>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
