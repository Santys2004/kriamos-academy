<?php require dirname(dirname(__DIR__)) . '/layouts/header.php'; ?>

<section class="container px-4 py-4">
    <div class="mb-4">
        <a href="<?= BASE_PATH ?>/ia/contextos" class="btn btn-outline-secondary mb-3">Volver</a>
        <h1 class="h3 fw-bold text-kriamos"><?= htmlspecialchars($contexto['titulo']) ?></h1>
        <p class="text-muted"><?= htmlspecialchars($contexto['tipo']) ?> | Sensibilidad: <?= htmlspecialchars($contexto['sensibilidad']) ?></p>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Resumen</strong></div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($contexto['contenido_resumen'] ?? '')) ?></p>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Texto indexado</strong></div>
        <div class="card-body">
            <pre class="ai-context-box"><?= htmlspecialchars($contexto['contenido_texto'] ?? '') ?></pre>
        </div>
    </div>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
