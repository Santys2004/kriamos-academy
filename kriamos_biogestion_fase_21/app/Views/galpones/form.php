<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Formulario de administración de galpones.</p>
        </div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= isset($galpon['granja_id']) && (int)$galpon['granja_id'] === (int)$granja['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($granja['sigla'] . ' - ' . $granja['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Número</label>
                    <input type="number" name="numero" class="form-control" min="1" required value="<?= htmlspecialchars($galpon['numero'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control text-uppercase" required value="<?= htmlspecialchars($galpon['codigo'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Capacidad</label>
                    <input type="number" name="capacidad_pollos" class="form-control" min="1" required value="<?= htmlspecialchars($galpon['capacidad_pollos'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Área m²</label>
                    <input type="number" step="0.01" name="area_m2" class="form-control" value="<?= htmlspecialchars($galpon['area_m2'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <?php $estado = $galpon['estado'] ?? 'activo'; ?>
                    <select name="estado" class="form-select">
                        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="mantenimiento" <?= $estado === 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="vacio_sanitario" <?= $estado === 'vacio_sanitario' ? 'selected' : '' ?>>Vacío sanitario</option>
                    </select>
                </div>
                <div class="col-md-4"></div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($galpon['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/galpones" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-kriamos"><i class="fa-solid fa-floppy-disk me-2"></i> Guardar</button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
