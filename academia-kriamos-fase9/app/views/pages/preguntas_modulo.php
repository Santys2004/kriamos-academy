<section class="py-5">
    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>Pregunta creada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card card-kriamos mb-4 overflow-hidden">
            <div class="course-hero p-4 p-lg-5 text-white">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <span class="badge bg-kriamos-orange mb-2"><?= e($module['course_code']) ?></span>
                        <h1 class="h2 fw-bold mb-2">Banco de preguntas</h1>
                        <p class="mb-0 opacity-90"><?= e($module['course_title']) ?> · <?= e($module['title']) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= e(current_url('crear_pregunta', ['module_id' => (string)$module['id']])) ?>" class="btn btn-kriamos-orange mb-2 w-100 w-lg-auto">
                            <i class="fa-solid fa-plus me-2"></i>Crear pregunta
                        </a>
                        <a href="<?= e(current_url('curso_detalle', ['id' => (string)$module['course_id']])) ?>" class="btn btn-outline-light w-100 w-lg-auto">
                            <i class="fa-solid fa-arrow-left me-2"></i>Volver al curso
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0">Preguntas registradas</h2>
                        <span class="badge bg-kriamos-blue"><?= count($questions ?? []) ?> pregunta(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($questions)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-circle-question fa-3x mb-3"></i>
                                <p>Este módulo aún no tiene preguntas. Para habilitar el examen se requieren mínimo 10 preguntas activas.</p>
                                <a href="<?= e(current_url('crear_pregunta', ['module_id' => (string)$module['id']])) ?>" class="btn btn-kriamos-orange">Crear primera pregunta</a>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($questions as $question): ?>
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <div class="fw-bold"><?= e($question['question_text']) ?></div>
                                                <div class="small text-muted mt-1">
                                                    Dificultad: <?= e(ucfirst($question['difficulty'])) ?> ·
                                                    Opciones: <?= (int)$question['options_count'] ?> ·
                                                    Correctas: <?= (int)$question['correct_options_count'] ?>
                                                </div>
                                            </div>
                                            <span class="badge <?= $question['status'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?> align-self-start">
                                                <?= e(ucfirst($question['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-kriamos">
                    <div class="card-body">
                        <h2 class="h6 fw-bold text-kriamos-blue"><i class="fa-solid fa-shield-halved me-2"></i>Reglas del examen</h2>
                        <ul class="small text-muted mb-0">
                            <li>10 preguntas aleatorias por intento.</li>
                            <li>1 minuto por pregunta.</li>
                            <li>Después de enviar una respuesta no puede modificarse.</li>
                            <li>La nota mínima aprobatoria es 80%.</li>
                            <li>Si reprueba, el siguiente intento usa 10 preguntas distintas.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
