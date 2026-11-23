<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Nuevo formato digital</h1>
            <p class="text-muted mb-0">Seleccione el formato y diligencie la información requerida.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/formatos" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-body">
            <form method="get" action="<?= BASE_PATH ?>/formatos/create" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label">Tipo de formato</label>
                    <select name="tipo_formato_id" class="form-select" required>
                        <option value="">Seleccione un formato...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= (int) $tipo['id'] ?>" <?= $tipoSeleccionado && (int)$tipoSeleccionado['id'] === (int)$tipo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(str_pad((string)$tipo['orden'], 2, '0', STR_PAD_LEFT) . ' - ' . $tipo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-kriamos w-100">Cargar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($tipoSeleccionado): ?>
        <div class="alert alert-info">
            <h2 class="h5"><?= htmlspecialchars($guide['titulo']) ?></h2>
            <p><?= htmlspecialchars($guide['resumen']) ?></p>
            <ul class="mb-0">
                <?php foreach ($guide['items'] as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="post" action="<?= BASE_PATH ?>/formatos/store" class="card card-kriamos" data-offline-enabled="true">
            <?= Csrf::input() ?>
            <input type="hidden" name="tipo_formato_id" value="<?= (int) $tipoSeleccionado['id'] ?>">

            <div class="card-header bg-white">
                <strong><?= htmlspecialchars($tipoSeleccionado['nombre']) ?></strong>
                <div class="small text-muted"><?= htmlspecialchars($tipoSeleccionado['fundamento'] ?? '') ?></div>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha del formato</label>
                        <input type="date" name="fecha_formato" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora_formato" class="form-control" value="<?= date('H:i') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Granja</label>
                        <select name="granja_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($granjas as $granja): ?>
                                <option value="<?= (int) $granja['id'] ?>"><?= htmlspecialchars($granja['sigla'] . ' - ' . $granja['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Galpón</label>
                        <select name="galpon_id" class="form-select">
                            <option value="">No aplica / No requerido</option>
                            <?php foreach ($galpones as $galpon): ?>
                                <option value="<?= (int) $galpon['id'] ?>"><?= htmlspecialchars($galpon['granja_sigla'] . ' - ' . $galpon['codigo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ciclo productivo</label>
                        <select name="ciclo_id" class="form-select">
                            <option value="">No aplica / No requerido</option>
                            <?php foreach ($ciclos as $ciclo): ?>
                                <option value="<?= (int) $ciclo['id'] ?>">
                                    <?= htmlspecialchars($ciclo['granja_sigla'] . ' - ' . $ciclo['galpon_codigo'] . ' - ' . $ciclo['codigo'] . ' - ' . $ciclo['fase_nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ((int) $tipoSeleccionado['permite_no_aplica'] === 1): ?>
                        <div class="col-md-6">
                            <label class="form-label">Aplicabilidad</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="es_no_aplica" value="1" id="noAplicaCheck">
                                <label class="form-check-label" for="noAplicaCheck">Marcar como No aplica</label>
                            </div>
                            <small class="text-muted">Debe justificarse cuando el formato no aplique por fase del ciclo.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Motivo de No aplica</label>
                            <textarea name="motivo_no_aplica" class="form-control" rows="2" data-no-copy-paste="true"></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="col-12"><hr></div>

                    <?php foreach ($fields as $field): ?>
                        <div class="<?= ($field['type'] ?? 'text') === 'textarea' ? 'col-12' : 'col-md-4' ?>">
                            <label class="form-label">
                                <?= htmlspecialchars($field['label']) ?>
                                <?php if (!empty($field['required'])): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>

                            <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($field['name']) ?>" class="form-control" rows="3" <?= !empty($field['required']) ? 'required' : '' ?> data-no-copy-paste="true"></textarea>
                            <?php elseif (($field['type'] ?? 'text') === 'select'): ?>
                                <select name="<?= htmlspecialchars($field['name']) ?>" class="form-select" <?= !empty($field['required']) ? 'required' : '' ?>>
                                    <option value="">Seleccione...</option>
                                    <?php foreach (($field['options'] ?? []) as $option): ?>
                                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= htmlspecialchars($field['type'] ?? 'text') ?>" name="<?= htmlspecialchars($field['name']) ?>" class="form-control" <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-12">
                        <label class="form-label">Observaciones generales</label>
                        <textarea name="observaciones" class="form-control" rows="3" data-no-copy-paste="true"></textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between">
                <a href="<?= BASE_PATH ?>/formatos" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-kriamos">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar formato
                </button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
