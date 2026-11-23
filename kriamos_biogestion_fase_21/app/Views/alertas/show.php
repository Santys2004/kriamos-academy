<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Detalle de alerta</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($alerta['titulo']) ?></p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/alertas" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong><?= htmlspecialchars($alerta['titulo']) ?></strong>
                <div class="small text-muted">Tipo: <?= htmlspecialchars($alerta['tipo']) ?></div>
            </div>
            <span class="badge prioridad-<?= htmlspecialchars($alerta['prioridad']) ?>"><?= htmlspecialchars($alerta['prioridad']) ?></span>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($alerta['mensaje'])) ?></p>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><strong>Estado:</strong><br><?= htmlspecialchars($alerta['estado']) ?></div>
                <div class="col-md-3"><strong>Granja:</strong><br><?= htmlspecialchars($alerta['granja_sigla'] ?? 'General') ?></div>
                <div class="col-md-3"><strong>Galpón:</strong><br><?= htmlspecialchars($alerta['galpon_codigo'] ?? 'No aplica') ?></div>
                <div class="col-md-3"><strong>Fecha límite:</strong><br><?= htmlspecialchars($alerta['fecha_limite'] ?? 'No definida') ?></div>
                <div class="col-md-6"><strong>Usuario responsable:</strong><br><?= htmlspecialchars(trim(($alerta['usuario_nombres'] ?? '') . ' ' . ($alerta['usuario_apellidos'] ?? '')) ?: 'No asignado') ?></div>
                <div class="col-md-6"><strong>Formato relacionado:</strong><br><?= htmlspecialchars($alerta['codigo_consecutivo'] ?? 'No aplica') ?></div>
            </div>

            <?php if (\App\Services\Auth::can('alertas.gestionar')): ?>
                <form method="post" action="<?= BASE_PATH ?>/alertas/estado" class="d-flex gap-2 flex-wrap">
                    <?= Csrf::input() ?>
                    <input type="hidden" name="id" value="<?= (int) $alerta['id'] ?>">
                    <button type="submit" name="estado" value="leida" class="btn btn-outline-kriamos">Marcar como leída</button>
                    <button type="submit" name="estado" value="atendida" class="btn btn-kriamos">Marcar como atendida</button>
                    <button type="submit" name="estado" value="cerrada" class="btn btn-outline-danger">Cerrar alerta</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
