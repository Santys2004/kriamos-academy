<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container px-4 py-4">
    <div class="mb-4">
        <a href="<?= BASE_PATH ?>/notificaciones" class="btn btn-outline-secondary mb-3">Volver</a>
        <h1 class="h3 fw-bold text-kriamos">Notificaciones push PWA</h1>
        <p class="text-muted">Registre este dispositivo para recibir notificaciones del aplicativo.</p>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <p>Las notificaciones push requieren que el sitio esté publicado bajo HTTPS y que el navegador permita notificaciones.</p>
            <button class="btn btn-kriamos" id="enablePushBtn">Activar notificaciones push</button>
            <div id="pushStatus" class="alert alert-info mt-3">Estado: pendiente de activación.</div>
        </div>
    </div>

    <div class="alert alert-warning mt-4">
        Esta fase deja preparada la suscripción push. Para envío real Web Push se debe configurar VAPID e integrar una librería especializada en producción.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
