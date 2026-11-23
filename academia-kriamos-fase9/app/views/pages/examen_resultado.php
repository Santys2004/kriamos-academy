<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-kriamos overflow-hidden mb-4">
                    <div class="p-4 p-lg-5 text-white <?= (int)$attempt['passed'] === 1 ? 'bg-success' : 'bg-danger' ?>">
                        <div class="row align-items-center g-4">
                            <div class="col-md-8">
                                <span class="badge bg-light text-dark mb-2"><?= e($attempt['course_code']) ?></span>
                                <h1 class="h2 fw-bold mb-2"><?= (int)$attempt['passed'] === 1 ? 'Curso aprobado' : 'Curso no aprobado' ?></h1>
                                <p class="mb-0"><?= e($attempt['full_name']) ?> · <?= e($attempt['module_title']) ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="display-5 fw-bold"><?= e((string)$attempt['score']) ?>%</div>
                                <div>Nota mínima: <?= e((string)$passingScore) ?>%</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 text-center">
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="h3 fw-bold text-kriamos-blue mb-0"><?= (int)$attempt['correct_answers'] ?></div>
                                    <div class="small text-muted">Correctas de <?= (int)$attempt['total_questions'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="h3 fw-bold text-kriamos-blue mb-0"><?= (int)$attempt['attempt_number'] ?></div>
                                    <div class="small text-muted">Intento</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="h3 fw-bold text-kriamos-blue mb-0"><?= e(ucfirst($attempt['status'])) ?></div>
                                    <div class="small text-muted">Estado</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <?php if ((int)$attempt['passed'] === 1): ?>
                            <?php if (!empty($certificate)): ?>
                                <div class="alert alert-success">
                                    <i class="fa-solid fa-circle-check me-2"></i>Certificado generado correctamente. Vigencia de seis meses hasta el <strong><?= e(date('d/m/Y', strtotime($certificate['expires_at']))) ?></strong>.
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <a href="<?= e(current_url('ver_certificado', ['token' => $certificate['verification_token']])) ?>" class="btn btn-kriamos-orange">
                                        <i class="fa-solid fa-certificate me-2"></i>Ver certificado
                                    </a>
                                    <a href="<?= e(current_url('descargar_certificado', ['code' => $certificate['certificate_code']])) ?>" class="btn btn-outline-primary">
                                        <i class="fa-solid fa-file-pdf me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>El curso fue aprobado, pero no fue posible generar el certificado en este momento.
                                    <?php if (!empty($certificateError)): ?><br><span class="small"><?= e($certificateError) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($reward)): ?>
                                <div class="alert alert-warning border-0">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="fs-3 text-kriamos-orange"><i class="fa-solid fa-gift"></i></div>
                                        <div>
                                            <div class="fw-bold text-kriamos-blue">¡Felicitaciones! Generaste una recompensa interna KRIAMOS.</div>
                                            <div>Por obtener <strong><?= e((string)$reward['score']) ?>%</strong>, tienes pendiente <strong><?= (int)$reward['frozen_chickens_qty'] ?> pollo(s) congelado(s)</strong>.</div>
                                            <div class="small text-muted mt-1">La recompensa aplica solo para empleados y debe ser gestionada por Gestión Humana o el administrador autorizado.</div>
                                            <a href="<?= e(current_url('mis_recompensas')) ?>" class="btn btn-sm btn-outline-dark mt-2"><i class="fa-solid fa-gift me-1"></i>Consultar mis recompensas</a>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif (!empty($rewardError)): ?>
                                <div class="alert alert-light border">
                                    <i class="fa-solid fa-circle-info me-2"></i>El curso fue aprobado, pero no fue posible validar la recompensa en este momento.
                                    <span class="small text-muted"><?= e($rewardError) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Debe repetir el examen. El sistema intentará asignar 10 preguntas distintas al siguiente intento.
                            </div>
                            <form action="<?= e(current_url('iniciar_examen')) ?>" method="post" class="mb-3">
                                <input type="hidden" name="document_number" value="<?= e($attempt['document_number']) ?>">
                                <input type="hidden" name="module_id" value="<?= (int)$attempt['module_id'] ?>">
                                <button type="submit" class="btn btn-kriamos-orange">
                                    <i class="fa-solid fa-rotate-right me-2"></i>Repetir examen
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="<?= e(current_url('mis_capacitaciones')) ?>" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-2"></i>Volver a mis capacitaciones
                        </a>
                    </div>
                </div>

                <div class="card card-kriamos">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0">Detalle del intento</h2>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="reviewAccordion">
                            <?php foreach ($review as $row): ?>
                                <div class="accordion-item mb-2 border rounded-4 overflow-hidden">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#review<?= (int)$row['question_order'] ?>">
                                            <span class="badge <?= (int)$row['is_correct'] === 1 ? 'text-bg-success' : 'text-bg-danger' ?> me-3">
                                                <?= (int)$row['is_correct'] === 1 ? 'Correcta' : 'Incorrecta' ?>
                                            </span>
                                            Pregunta <?= (int)$row['question_order'] ?>
                                        </button>
                                    </h3>
                                    <div id="review<?= (int)$row['question_order'] ?>" class="accordion-collapse collapse" data-bs-parent="#reviewAccordion">
                                        <div class="accordion-body small">
                                            <p class="fw-bold mb-2"><?= e($row['question_text']) ?></p>
                                            <p class="mb-1"><strong>Respuesta enviada:</strong> <?= e($row['selected_label'] ? $row['selected_label'] . '. ' . $row['selected_text'] : 'Sin respuesta / tiempo agotado') ?></p>
                                            <p class="mb-1"><strong>Respuesta correcta:</strong> <?= e($row['correct_label'] . '. ' . $row['correct_text']) ?></p>
                                            <?php if (!empty($row['explanation'])): ?>
                                                <p class="mb-0 text-muted"><strong>Retroalimentación:</strong> <?= e($row['explanation']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
