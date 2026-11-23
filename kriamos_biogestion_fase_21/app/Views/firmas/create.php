<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Firmar formato</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($registro['codigo_consecutivo']) ?> | <?= htmlspecialchars($registro['tipo_nombre']) ?></p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/formatos/show?id=<?= (int) $registro['id'] ?>" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <form method="post" action="<?= BASE_PATH ?>/firmas/store" class="card card-kriamos" id="firmaForm">
        <?= Csrf::input() ?>
        <input type="hidden" name="formato_registro_id" value="<?= (int) $registro['id'] ?>">
        <input type="hidden" name="firma_base64" id="firmaBase64">

        <div class="card-body">
            <div class="alert alert-warning">
                Al firmar este registro, usted confirma que revisó la información diligenciada y que corresponde a datos reales de la operación.
                El formato pasará a estado aprobado y quedará con trazabilidad de usuario, fecha, hora, IP y dispositivo.
            </div>

            <div class="mb-3">
                <label class="form-label">Firma dibujada en pantalla</label>
                <div class="signature-box">
                    <canvas id="signatureCanvas" width="700" height="220"></canvas>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature">
                    Limpiar firma
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirme su contraseña</label>
                <input type="password" name="password" class="form-control" required autocomplete="off">
                <small class="text-muted">Esta confirmación evita firmas no autorizadas con una sesión abierta.</small>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/formatos/show?id=<?= (int) $registro['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-kriamos">
                <i class="fa-solid fa-signature me-2"></i> Firmar y aprobar
            </button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
