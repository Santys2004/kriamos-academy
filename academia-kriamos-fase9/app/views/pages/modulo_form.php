<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-kriamos-blue text-white">
                        <h1 class="h4 mb-0"><i class="fa-solid fa-layer-group me-2"></i>Crear módulo</h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-light border">
                            Curso: <strong><?= e($course['code'] ?? '') ?> — <?= e($course['title'] ?? '') ?></strong>
                        </div>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('guardar_modulo')) ?>" method="post" class="row g-3">
                            <input type="hidden" name="course_id" value="<?= e((string)($course['id'] ?? '')) ?>">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del módulo</label>
                                <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= e($old['title'] ?? '') ?>" placeholder="Ingreso bioseguro a granja" required>
                                <div class="invalid-feedback"><?= e($errors['title'] ?? '') ?></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Orden</label>
                                <input type="number" min="1" name="sequence_order" class="form-control <?= isset($errors['sequence_order']) ? 'is-invalid' : '' ?>" value="<?= e($old['sequence_order'] ?? '1') ?>" required>
                                <div class="invalid-feedback"><?= e($errors['sequence_order'] ?? '') ?></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Minutos</label>
                                <input type="number" min="1" name="estimated_minutes" class="form-control <?= isset($errors['estimated_minutes']) ? 'is-invalid' : '' ?>" value="<?= e($old['estimated_minutes'] ?? '15') ?>" required>
                                <div class="invalid-feedback"><?= e($errors['estimated_minutes'] ?? '') ?></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Contenido principal del módulo."><?= e($old['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <?php $status = $old['status'] ?? 'activo'; ?>
                                <select name="status" class="form-select">
                                    <option value="activo" <?= $status === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= $status === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-between pt-3">
                                <a href="<?= e(current_url('curso_detalle', ['id' => (string)($course['id'] ?? 0)])) ?>" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-kriamos-orange">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar módulo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
