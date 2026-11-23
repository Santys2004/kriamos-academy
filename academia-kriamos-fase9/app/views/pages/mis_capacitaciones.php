<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-kriamos sticky-lg-top top-90">
                    <div class="card-header bg-white">
                        <h1 class="h4 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-clipboard-check me-2"></i>Mis capacitaciones</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger small"><?= e($error) ?></div>
                        <?php endif; ?>
                        <form action="<?= e(current_url('mis_capacitaciones_resultado')) ?>" method="post">
                            <label class="form-label fw-semibold">Ingrese su cédula</label>
                            <input type="text" name="document_number" value="<?= e($document ?? '') ?>" class="form-control form-control-lg mb-3" inputmode="numeric" placeholder="Ejemplo: 123456789" required>
                            <button type="submit" class="btn btn-kriamos-orange w-100 btn-lg">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Consultar cursos
                            </button>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">El examen oficial tiene 10 preguntas. Cada pregunta cuenta con 1 minuto y, una vez enviada, la respuesta queda bloqueada.</p>
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

                    <?php if (empty($modules)): ?>
                        <div class="alert alert-warning">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>No tiene módulos asignados. Revise las reglas de asignación del curso según tipo de usuario, sitio de trabajo, equipo y cargo.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($modules as $module): ?>
                                <?php
                                    $activeQuestions = (int)($module['active_questions'] ?? 0);
                                    $ready = $activeQuestions >= 10;
                                    $status = $module['last_status'] ?? null;
                                ?>
                                <div class="col-md-6">
                                    <div class="card h-100 card-kriamos">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between gap-2 mb-2">
                                                <span class="badge bg-kriamos-blue"><?= e($module['course_code']) ?></span>
                                                <span class="badge <?= $ready ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $activeQuestions ?> pregunta(s)</span>
                                            </div>
                                            <h3 class="h6 fw-bold text-kriamos-blue"><?= e($module['module_title']) ?></h3>
                                            <p class="small text-muted flex-grow-1"><?= e($module['course_title']) ?></p>
                                            <div class="small mb-3">
                                                <?php if ($status): ?>
                                                    Último intento: <strong><?= e(ucfirst(str_replace('_', ' ', $status))) ?></strong>
                                                    <?php if ($module['last_score'] !== null): ?> · Nota: <?= e((string)$module['last_score']) ?>%<?php endif; ?>
                                                <?php else: ?>
                                                    Sin intentos registrados.
                                                <?php endif; ?>
                                                <?php if (!empty($module['certificate_code'])): ?>
                                                    <div class="mt-2">
                                                        <span class="badge text-bg-success"><i class="fa-solid fa-certificate me-1"></i>Certificado</span>
                                                        <a class="ms-1" href="<?= e(current_url('ver_certificado', ['token' => $module['certificate_token']])) ?>">Ver certificado</a>
                                                        <div class="text-muted">Vence: <?= e(date('d/m/Y', strtotime($module['certificate_expires_at']))) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($ready): ?>
                                                <form action="<?= e(current_url('iniciar_examen')) ?>" method="post" onsubmit="return confirm('¿Desea iniciar el examen? Tendrá 1 minuto por pregunta y no podrá modificar respuestas enviadas.');">
                                                    <input type="hidden" name="document_number" value="<?= e($person['document_number']) ?>">
                                                    <input type="hidden" name="module_id" value="<?= (int)$module['module_id'] ?>">
                                                    <button type="submit" class="btn btn-kriamos-orange w-100">
                                                        <i class="fa-solid fa-play me-2"></i>Iniciar examen
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary w-100" disabled>
                                                    Banco incompleto: requiere 10 preguntas activas
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card card-kriamos">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-mobile-screen-button fa-3x text-kriamos-orange mb-3"></i>
                            <h2 class="h4 fw-bold text-kriamos-blue">Consulta tus cursos asignados</h2>
                            <p class="text-muted mb-0">Ingrese la cédula registrada para ver los módulos disponibles y presentar las evaluaciones.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
