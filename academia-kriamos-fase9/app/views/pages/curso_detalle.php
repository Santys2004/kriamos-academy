<section class="py-5">
    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>
                <?php
                    $message = match ($success) {
                        '1' => 'Curso creado correctamente.',
                        'modulo' => 'Módulo creado correctamente.',
                        'material' => 'Material cargado correctamente.',
                        'asignacion' => 'Asignación creada correctamente.',
                        default => 'Proceso realizado correctamente.',
                    };
                ?>
                <?= e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="card card-kriamos mb-4 overflow-hidden">
            <div class="course-hero p-4 p-lg-5 text-white">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-kriamos-orange mb-2"><?= e($course['code']) ?></span>
                        <h1 class="display-6 fw-bold mb-2"><?= e($course['title']) ?></h1>
                        <p class="mb-3 opacity-90"><?= e($course['description'] ?: 'Curso sin descripción registrada.') ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-white text-kriamos-blue">Público: <?= e(ucfirst($course['target_audience'])) ?></span>
                            <span class="badge <?= $course['status'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?>">Estado: <?= e(ucfirst($course['status'])) ?></span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= e(current_url('crear_modulo', ['course_id' => (string)$course['id']])) ?>" class="btn btn-kriamos-orange mb-2 w-100 w-lg-auto">
                            <i class="fa-solid fa-layer-group me-2"></i>Agregar módulo
                        </a>
                        <a href="<?= e(current_url('crear_asignacion', ['course_id' => (string)$course['id']])) ?>" class="btn btn-light mb-2 w-100 w-lg-auto">
                            <i class="fa-solid fa-user-check me-2"></i>Asignar curso
                        </a>
                        <a href="<?= e(current_url('admin_cursos')) ?>" class="btn btn-outline-light w-100 w-lg-auto">
                            <i class="fa-solid fa-arrow-left me-2"></i>Volver a cursos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 fw-bold text-kriamos-blue">Módulos y materiales</h2>
                        <span class="badge bg-kriamos-blue"><?= count($modules ?? []) ?> módulo(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($modules)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-layer-group fa-2x mb-3"></i>
                                <p class="mb-3">Este curso aún no tiene módulos.</p>
                                <a href="<?= e(current_url('crear_modulo', ['course_id' => (string)$course['id']])) ?>" class="btn btn-kriamos-orange">Crear primer módulo</a>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="modulesAccordion">
                                <?php foreach ($modules as $index => $module): ?>
                                    <?php $materials = $materialsByModule[$module['id']] ?? []; ?>
                                    <?php $questionInfo = $questionsCountByModule[$module['id']] ?? ['total_questions' => 0, 'active_questions' => 0]; ?>
                                    <div class="accordion-item mb-3 border rounded-4 overflow-hidden">
                                        <h3 class="accordion-header" id="heading<?= (int)$module['id'] ?>">
                                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= (int)$module['id'] ?>">
                                                <span class="badge bg-kriamos-orange me-3"><?= (int)$module['sequence_order'] ?></span>
                                                <span>
                                                    <strong><?= e($module['title']) ?></strong><br>
                                                    <small class="text-muted"><?= (int)$module['estimated_minutes'] ?> minutos · <?= count($materials) ?> material(es) · <?= (int)($questionInfo['active_questions'] ?? 0) ?> pregunta(s) activas</small>
                                                </span>
                                            </button>
                                        </h3>
                                        <div id="collapse<?= (int)$module['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#modulesAccordion">
                                            <div class="accordion-body">
                                                <p class="text-muted"><?= e($module['description'] ?: 'Módulo sin descripción.') ?></p>
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge <?= $module['status'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($module['status'])) ?></span>
                                                        <span class="badge <?= (int)($questionInfo['active_questions'] ?? 0) >= 10 ? 'text-bg-success' : 'text-bg-warning' ?>">
                                                            <?= (int)($questionInfo['active_questions'] ?? 0) ?>/10 preguntas activas
                                                        </span>
                                                    </div>
                                                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                                                        <a href="<?= e(current_url('subir_material', ['module_id' => (string)$module['id']])) ?>" class="btn btn-outline-primary">
                                                            <i class="fa-solid fa-upload me-1"></i>Material
                                                        </a>
                                                        <a href="<?= e(current_url('preguntas_modulo', ['module_id' => (string)$module['id']])) ?>" class="btn btn-outline-success">
                                                            <i class="fa-solid fa-circle-question me-1"></i>Preguntas
                                                        </a>
                                                        <a href="<?= e(current_url('mis_capacitaciones')) ?>" class="btn btn-outline-dark">
                                                            <i class="fa-solid fa-clipboard-check me-1"></i>Examen
                                                        </a>
                                                    </div>
                                                </div>

                                                <?php if (empty($materials)): ?>
                                                    <div class="alert alert-light border mb-0">No hay materiales cargados para este módulo.</div>
                                                <?php else: ?>
                                                    <div class="list-group">
                                                        <?php foreach ($materials as $material): ?>
                                                            <a href="<?= e($material['file_path']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                                                <div class="d-flex gap-3">
                                                                    <div class="material-icon"><i class="fa-solid <?= e(material_icon($material['material_type'])) ?>"></i></div>
                                                                    <div>
                                                                        <div class="fw-bold"><?= e($material['title']) ?></div>
                                                                        <div class="small text-muted">
                                                                            <?= e(ucfirst($material['material_type'])) ?> · <?= e(format_bytes((int)$material['file_size'])) ?> · <?= e($material['original_filename']) ?>
                                                                        </div>
                                                                        <?php if (!empty($material['description'])): ?>
                                                                            <div class="small mt-1"><?= e($material['description']) ?></div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <span class="badge <?= (int)$material['is_downloadable'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                                                    <?= (int)$material['is_downloadable'] === 1 ? 'Descargable' : 'Solo lectura' ?>
                                                                </span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-kriamos mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0 fw-bold text-kriamos-blue">Asignaciones</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p class="text-muted">Aún no hay reglas de asignación.</p>
                            <a href="<?= e(current_url('crear_asignacion', ['course_id' => (string)$course['id']])) ?>" class="btn btn-sm btn-kriamos-orange">Crear asignación</a>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($assignments as $assignment): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between">
                                            <strong><?= e(ucfirst($assignment['person_type'])) ?></strong>
                                            <span class="badge <?= (int)$assignment['active'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                                <?= (int)$assignment['active'] === 1 ? 'Activa' : 'Inactiva' ?>
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            Sitio: <?= e($assignment['work_site']) ?><br>
                                            Equipo: <?= e($assignment['department']) ?><br>
                                            Cargo contiene: <?= e($assignment['position_keyword'] ?: 'No aplica') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card card-kriamos">
                    <div class="card-body">
                        <h2 class="h6 fw-bold text-kriamos-blue"><i class="fa-solid fa-circle-info me-2"></i>Nota de fase</h2>
                        <p class="small text-muted mb-0">Fase 3 activa: banco de preguntas, examen de 10 preguntas, cronómetro de 1 minuto, bloqueo de respuestas y nota mínima del 80%.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
