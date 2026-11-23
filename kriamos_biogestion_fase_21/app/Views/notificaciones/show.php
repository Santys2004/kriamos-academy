<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container px-4 py-4">
    <div class="mb-4">
        <a href="<?= BASE_PATH ?>/notificaciones" class="btn btn-outline-secondary mb-3">Volver</a>
        <h1 class="h3 fw-bold text-kriamos"><?= htmlspecialchars($notificacion['titulo']) ?></h1>
        <p class="text-muted"><?= htmlspecialchars($notificacion['created_at']) ?> | Prioridad: <?= htmlspecialchars($notificacion['prioridad']) ?></p>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($notificacion['mensaje'])) ?></p>

            <?php if (!empty($notificacion['url_accion'])): ?>
                <a href="<?= htmlspecialchars($notificacion['url_accion']) ?>" class="btn btn-kriamos">Ir al evento relacionado</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
