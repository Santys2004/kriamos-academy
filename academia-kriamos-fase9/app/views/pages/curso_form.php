<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-kriamos-blue text-white">
                        <h1 class="h4 mb-0"><i class="fa-solid fa-book me-2"></i>Crear curso de capacitación</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('guardar_curso')) ?>" method="post" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Código del curso</label>
                                <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" value="<?= e($old['code'] ?? '') ?>" placeholder="KRI-CAP-001" required>
                                <div class="invalid-feedback"><?= e($errors['code'] ?? '') ?></div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del curso</label>
                                <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= e($old['title'] ?? '') ?>" placeholder="Bioseguridad para ingreso a granjas" required>
                                <div class="invalid-feedback"><?= e($errors['title'] ?? '') ?></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Objetivo, alcance y enfoque del curso."><?= e($old['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Público objetivo</label>
                                <select name="target_audience" class="form-select <?= isset($errors['target_audience']) ? 'is-invalid' : '' ?>" required>
                                    <?php $target = $old['target_audience'] ?? 'todos'; ?>
                                    <option value="todos" <?= $target === 'todos' ? 'selected' : '' ?>>Todos</option>
                                    <option value="empleados" <?= $target === 'empleados' ? 'selected' : '' ?>>Solo empleados</option>
                                    <option value="contratistas" <?= $target === 'contratistas' ? 'selected' : '' ?>>Solo contratistas</option>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['target_audience'] ?? '') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <?php $status = $old['status'] ?? 'activo'; ?>
                                <select name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                                    <option value="activo" <?= $status === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= $status === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['status'] ?? '') ?></div>
                            </div>
                            <div class="col-12 d-flex justify-content-between pt-3">
                                <a href="<?= e(current_url('admin_cursos')) ?>" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-kriamos-orange">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar curso
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
