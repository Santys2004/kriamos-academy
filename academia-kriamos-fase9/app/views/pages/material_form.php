<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-kriamos-blue text-white">
                        <h1 class="h4 mb-0"><i class="fa-solid fa-file-arrow-up me-2"></i>Subir material de capacitación</h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-light border">
                            Curso: <strong><?= e($module['course_title'] ?? '') ?></strong><br>
                            Módulo: <strong><?= e($module['title'] ?? '') ?></strong>
                        </div>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('guardar_material')) ?>" method="post" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="module_id" value="<?= e((string)($module['id'] ?? '')) ?>">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Nombre del material</label>
                                <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= e($old['title'] ?? '') ?>" placeholder="Manual de ingreso bioseguro" required>
                                <div class="invalid-feedback"><?= e($errors['title'] ?? '') ?></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Tipo de material</label>
                                <?php $type = $old['material_type'] ?? 'pdf'; ?>
                                <select name="material_type" class="form-select <?= isset($errors['material_type']) ? 'is-invalid' : '' ?>" required>
                                    <option value="manual" <?= $type === 'manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Video</option>
                                    <option value="podcast" <?= $type === 'podcast' ? 'selected' : '' ?>>Podcast / audio</option>
                                    <option value="pdf" <?= $type === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                    <option value="word" <?= $type === 'word' ? 'selected' : '' ?>>Word</option>
                                    <option value="powerpoint" <?= $type === 'powerpoint' ? 'selected' : '' ?>>PowerPoint</option>
                                    <option value="imagen" <?= $type === 'imagen' ? 'selected' : '' ?>>Imagen</option>
                                    <option value="otro" <?= $type === 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['material_type'] ?? '') ?></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Archivo</label>
                                <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mov,.mp3,.m4a,.wav,.jpg,.jpeg,.png,.webp" required>
                                <div class="form-text">Formatos permitidos: PDF, Word, PowerPoint, video MP4/MOV, audio MP3/M4A/WAV e imágenes. Tamaño máximo: 250 MB.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Breve orientación para el usuario sobre cómo estudiar este material."><?= e($old['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_downloadable" id="isDownloadable" value="1" <?= ($old['is_downloadable'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isDownloadable">
                                        Permitir descarga del material para estudio sin conexión en fases posteriores.
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-between pt-3">
                                <a href="<?= e(current_url('curso_detalle', ['id' => (string)($module['course_id'] ?? 0)])) ?>" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-kriamos-orange">
                                    <i class="fa-solid fa-upload me-2"></i>Subir material
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
