<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card card-kriamos">
                    <div class="card-header bg-white">
                        <h1 class="h4 fw-bold text-kriamos-blue mb-1">Crear pregunta</h1>
                        <p class="text-muted mb-0"><?= e($module['course_title']) ?> · <?= e($module['title']) ?></p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('guardar_pregunta')) ?>" method="post" novalidate>
                            <input type="hidden" name="module_id" value="<?= (int)$module['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pregunta</label>
                                <textarea name="question_text" rows="4" class="form-control <?= isset($errors['question_text']) ? 'is-invalid' : '' ?>" required><?= e($old['question_text'] ?? '') ?></textarea>
                                <?php if (isset($errors['question_text'])): ?><div class="invalid-feedback"><?= e($errors['question_text']) ?></div><?php endif; ?>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Dificultad</label>
                                    <select name="difficulty" class="form-select">
                                        <?php foreach (['basica' => 'Básica', 'media' => 'Media', 'critica' => 'Crítica'] as $value => $label): ?>
                                            <option value="<?= e($value) ?>" <?= ($old['difficulty'] ?? 'basica') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Estado</label>
                                    <select name="status" class="form-select">
                                        <option value="activo" <?= ($old['status'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= ($old['status'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-light border">
                                <strong>Opciones de respuesta.</strong> Marque cuál es la correcta. La aplicación validará que cada pregunta tenga 4 opciones y una sola respuesta correcta.
                            </div>

                            <?php foreach (['A', 'B', 'C', 'D'] as $label): ?>
                                <div class="input-group mb-3">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="radio" name="correct_option" value="<?= e($label) ?>" <?= ($old['correct_option'] ?? 'A') === $label ? 'checked' : '' ?> aria-label="Opción correcta <?= e($label) ?>">
                                    </div>
                                    <span class="input-group-text fw-bold"><?= e($label) ?></span>
                                    <input type="text" name="option_<?= e($label) ?>" value="<?= e($old['option_' . $label] ?? '') ?>" class="form-control <?= isset($errors['option_' . $label]) ? 'is-invalid' : '' ?>" placeholder="Texto de la opción <?= e($label) ?>" required>
                                    <?php if (isset($errors['option_' . $label])): ?><div class="invalid-feedback"><?= e($errors['option_' . $label]) ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Retroalimentación opcional</label>
                                <textarea name="explanation" rows="3" class="form-control" placeholder="Explique brevemente la respuesta correcta o el fundamento del POE."><?= e($old['explanation'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="<?= e(current_url('preguntas_modulo', ['module_id' => (string)$module['id']])) ?>" class="btn btn-outline-secondary">Cancelar</a>
                                <button class="btn btn-kriamos-orange" type="submit"><i class="fa-solid fa-save me-2"></i>Guardar pregunta</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
