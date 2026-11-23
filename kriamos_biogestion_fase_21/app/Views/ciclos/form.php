<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Formulario de ciclos productivos.</p>
        </div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= isset($ciclo['granja_id']) && (int)$ciclo['granja_id'] === (int)$granja['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($granja['sigla'] . ' - ' . $granja['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Galpón</label>
                    <select name="galpon_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($galpones as $galpon): ?>
                            <option value="<?= (int) $galpon['id'] ?>" <?= isset($ciclo['galpon_id']) && (int)$ciclo['galpon_id'] === (int)$galpon['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($galpon['granja_sigla'] . ' - ' . $galpon['codigo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">En fases posteriores se filtrará automáticamente por granja.</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fase del ciclo</label>
                    <select name="fase_ciclo_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($fases as $fase): ?>
                            <option value="<?= (int) $fase['id'] ?>" <?= isset($ciclo['fase_ciclo_id']) && (int)$ciclo['fase_ciclo_id'] === (int)$fase['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fase['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Código ciclo</label>
                    <input type="text" name="codigo" class="form-control text-uppercase" required value="<?= htmlspecialchars($ciclo['codigo'] ?? '') ?>" placeholder="GALM-G1-C46">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" required value="<?= htmlspecialchars($ciclo['fecha_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha encasetamiento</label>
                    <input type="date" name="fecha_encasetamiento" class="form-control" value="<?= htmlspecialchars($ciclo['fecha_encasetamiento'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha estimada salida</label>
                    <input type="date" name="fecha_estimada_salida" class="form-control" value="<?= htmlspecialchars($ciclo['fecha_estimada_salida'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha cierre</label>
                    <input type="date" name="fecha_cierre" class="form-control" value="<?= htmlspecialchars($ciclo['fecha_cierre'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Edad aves días</label>
                    <input type="number" name="edad_aves_dias" class="form-control" min="0" value="<?= htmlspecialchars($ciclo['edad_aves_dias'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Línea genética</label>
                    <input type="text" name="linea_genetica" class="form-control" value="<?= htmlspecialchars($ciclo['linea_genetica'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Aves recibidas</label>
                    <input type="number" name="aves_recibidas" class="form-control" min="0" value="<?= htmlspecialchars($ciclo['aves_recibidas'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Aves actuales</label>
                    <input type="number" name="aves_actuales" class="form-control" min="0" value="<?= htmlspecialchars($ciclo['aves_actuales'] ?? 0) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Mortalidad acumulada</label>
                    <input type="number" name="mortalidad_acumulada" class="form-control" min="0" value="<?= htmlspecialchars($ciclo['mortalidad_acumulada'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <?php $estado = $ciclo['estado'] ?? 'activo'; ?>
                    <select name="estado" class="form-select">
                        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="cerrado" <?= $estado === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                        <option value="suspendido" <?= $estado === 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($ciclo['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/ciclos" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-kriamos"><i class="fa-solid fa-floppy-disk me-2"></i> Guardar</button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
