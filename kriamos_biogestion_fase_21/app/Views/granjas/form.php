<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Formulario de administración de granjas.</p>
        </div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre de la granja</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($granja['nombre'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sigla</label>
                    <input type="text" name="sigla" class="form-control text-uppercase" required value="<?= htmlspecialchars($granja['sigla'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Código interno</label>
                    <input type="text" name="codigo_interno" class="form-control" value="<?= htmlspecialchars($granja['codigo_interno'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Capacidad de pollos</label>
                    <input type="number" name="capacidad_pollos" class="form-control" required min="1" value="<?= htmlspecialchars($granja['capacidad_pollos'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Número de galpones</label>
                    <input type="number" name="numero_galpones" class="form-control" required min="1" value="<?= htmlspecialchars($granja['numero_galpones'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <?php $estado = $granja['estado'] ?? 'activa'; ?>
                    <select name="estado" class="form-select">
                        <option value="activa" <?= $estado === 'activa' ? 'selected' : '' ?>>Activa</option>
                        <option value="inactiva" <?= $estado === 'inactiva' ? 'selected' : '' ?>>Inactiva</option>
                        <option value="suspendida" <?= $estado === 'suspendida' ? 'selected' : '' ?>>Suspendida</option>
                        <option value="en_auditoria" <?= $estado === 'en_auditoria' ? 'selected' : '' ?>>En auditoría</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Municipio</label>
                    <input type="text" name="municipio" class="form-control" value="<?= htmlspecialchars($granja['municipio'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <input type="text" name="departamento" class="form-control" value="<?= htmlspecialchars($granja['departamento'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion" class="form-control" value="<?= htmlspecialchars($granja['ubicacion'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($granja['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/granjas" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-kriamos">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar
            </button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
