<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-kriamos sticky-lg-top top-90">
                    <div class="card-header bg-white">
                        <h1 class="h4 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-cloud-arrow-down me-2"></i>Modo sin conexión</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger small"><?= e($error) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('offline_resultado')) ?>" method="post" class="mb-3">
                            <label class="form-label fw-semibold">Ingrese su cédula</label>
                            <input type="text" name="document_number" value="<?= e($document ?? '') ?>" class="form-control form-control-lg mb-3" inputmode="numeric" placeholder="Ejemplo: 123456789" required>
                            <button type="submit" class="btn btn-kriamos-orange w-100 btn-lg">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Consultar materiales
                            </button>
                        </form>

                        <button type="button" id="pwaInstallButtonSidebar" class="btn btn-outline-primary w-100 mb-3 d-none">
                            <i class="fa-solid fa-mobile-screen-button me-2"></i>Instalar app en este equipo
                        </button>

                        <div class="alert alert-info small mb-0">
                            <strong>Importante:</strong> el modo sin conexión permite estudiar materiales descargados. El examen oficial debe presentarse con conexión para validar cronómetro, respuestas y trazabilidad.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <?php if (!empty($person)): ?>
                    <div class="card card-kriamos mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($person['photo_path'])): ?>
                                    <img src="<?= e($person['photo_path']) ?>" alt="Foto" class="rounded-circle object-fit-cover" style="width:72px;height:72px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-kriamos-blue text-white d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                                        <i class="fa-solid fa-user fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h2 class="h5 fw-bold text-kriamos-blue mb-1"><?= e($person['full_name']) ?></h2>
                                    <div class="text-muted small">
                                        Cédula: <?= e($person['document_number']) ?> · <?= e(ucfirst($person['person_type'])) ?> · <?= e($person['work_site']) ?><br>
                                        Empresa: <?= e($person['company']) ?> · Cargo: <?= e($person['position']) ?>
                                        <?php if (!empty($person['department'])): ?> · Equipo: <?= e($person['department']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h2 class="h4 fw-bold text-kriamos-blue mb-1">Materiales disponibles para descarga</h2>
                            <p class="text-muted mb-0">Descargue los materiales marcados como descargables para estudiarlos sin internet.</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger" data-clear-offline-cache>
                            <i class="fa-solid fa-trash-can me-2"></i>Limpiar descargas locales
                        </button>
                    </div>

                    <?php if (empty($materials)): ?>
                        <div class="alert alert-warning">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>No hay materiales descargables asignados para esta persona. Revise cursos, módulos, reglas de asignación o marque los materiales como descargables.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($materials as $material): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 card-kriamos material-offline-card" data-material-card="<?= (int)$material['id'] ?>">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between gap-2 mb-2">
                                                <span class="badge bg-kriamos-blue"><?= e($material['course_code']) ?></span>
                                                <span class="badge text-bg-light"><i class="fa-solid <?= e(material_icon($material['material_type'])) ?> me-1"></i><?= e(ucfirst($material['material_type'])) ?></span>
                                            </div>
                                            <h3 class="h6 fw-bold text-kriamos-blue"><?= e($material['title']) ?></h3>
                                            <p class="small text-muted mb-2"><strong><?= e($material['module_title']) ?></strong><br><?= e($material['course_title']) ?></p>
                                            <p class="small text-muted flex-grow-1"><?= e($material['description'] ?: 'Material sin descripción.') ?></p>
                                            <div class="small text-muted mb-3">
                                                Archivo: <?= e($material['original_filename']) ?><br>
                                                Tamaño: <?= e(format_bytes((int)$material['file_size'])) ?>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button type="button"
                                                    class="btn btn-kriamos-orange offline-download-btn"
                                                    data-material-id="<?= (int)$material['id'] ?>"
                                                    data-document="<?= e($person['document_number']) ?>"
                                                    data-url="<?= e($material['file_path']) ?>"
                                                    data-title="<?= e($material['title']) ?>">
                                                    <i class="fa-solid fa-cloud-arrow-down me-2"></i>Descargar para offline
                                                </button>
                                                <a class="btn btn-outline-primary" href="<?= e($material['file_path']) ?>" target="_blank" rel="noopener">
                                                    <i class="fa-solid fa-eye me-2"></i>Abrir material
                                                </a>
                                            </div>
                                            <div class="offline-material-status small mt-2 text-muted" data-material-status="<?= (int)$material['id'] ?>">
                                                Estado local: pendiente de descarga.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card card-kriamos mt-4">
                        <div class="card-header bg-white">
                            <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de descargas registradas</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($downloads)): ?>
                                <p class="text-muted mb-0">Aún no hay descargas registradas para esta cédula.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Curso</th>
                                                <th>Material</th>
                                                <th>Tipo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($downloads as $download): ?>
                                                <tr>
                                                    <td><?= e(human_datetime($download['downloaded_at'])) ?></td>
                                                    <td><?= e($download['course_code']) ?></td>
                                                    <td><?= e($download['material_title']) ?></td>
                                                    <td><?= e($download['material_type']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card card-kriamos">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-cloud-arrow-down fa-3x text-kriamos-orange mb-3"></i>
                            <h2 class="h4 fw-bold text-kriamos-blue">Descarga materiales de estudio</h2>
                            <p class="text-muted mb-0">Ingrese la cédula registrada para consultar los materiales asignados y guardarlos localmente en el dispositivo.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
