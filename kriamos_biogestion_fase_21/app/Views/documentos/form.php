<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Cargue y clasifique documentos del sistema documental Kriamos.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/documentos" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" enctype="multipart/form-data" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Título</label><input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($documento['titulo'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_documental_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['id'] ?>" <?= isset($documento['categoria_documental_id']) && (int)$documento['categoria_documental_id'] === (int)$categoria['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <?php $estado = $documento['estado'] ?? 'vigente'; ?>
                    <select name="estado" class="form-select">
                        <option value="vigente" <?= $estado === 'vigente' ? 'selected' : '' ?>>Vigente</option>
                        <option value="en_revision" <?= $estado === 'en_revision' ? 'selected' : '' ?>>En revisión</option>
                        <option value="obsoleto" <?= $estado === 'obsoleto' ? 'selected' : '' ?>>Obsoleto</option>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Archivo</label><input type="file" name="archivo" class="form-control" <?= empty($documento) ? 'required' : '' ?>></div>

                <div class="col-md-3">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select">
                        <option value="">General</option>
                        <?php foreach ($granjas as $granja): ?>
                            <option value="<?= (int) $granja['id'] ?>" <?= isset($documento['granja_id']) && (int)$documento['granja_id'] === (int)$granja['id'] ? 'selected' : '' ?>><?= htmlspecialchars($granja['sigla'] . ' - ' . $granja['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Galpón</label>
                    <select name="galpon_id" class="form-select">
                        <option value="">No aplica</option>
                        <?php foreach ($galpones as $galpon): ?>
                            <option value="<?= (int) $galpon['id'] ?>" <?= isset($documento['galpon_id']) && (int)$documento['galpon_id'] === (int)$galpon['id'] ? 'selected' : '' ?>><?= htmlspecialchars(($galpon['granja_sigla'] ?? '') . ' - ' . $galpon['codigo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Comentario de versión</label><input type="text" name="comentario_version" class="form-control" placeholder="Descripción del cambio cuando cargue nueva versión"></div>

                <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" rows="4" class="form-control"><?= htmlspecialchars($documento['descripcion'] ?? '') ?></textarea></div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/documentos" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-kriamos" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i> Guardar</button>
        </div>
    </form>

    <div class="alert alert-info mt-4">
        Formatos permitidos: PDF, Word, Excel, CSV, JPG, PNG, WEBP, MP4, MOV y TXT. Tamaño máximo inicial: 50 MB.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
