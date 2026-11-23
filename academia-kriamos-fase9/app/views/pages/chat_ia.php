<section class="py-5 chat-ai-hero text-white">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-kriamos-orange mb-3"><i class="fa-solid fa-robot me-1"></i>Fase 9</span>
                <h1 class="display-6 fw-bold mb-3">Asistente KRIAMOS IA</h1>
                <p class="lead mb-0">Chat de ayuda para empleados y contratistas sobre capacitaciones, certificados, bioseguridad, bienestar animal y uso de la aplicación.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= e(current_url('admin_chat_ia')) ?>" class="btn btn-light btn-lg"><i class="fa-solid fa-database me-2"></i>Base de conocimiento</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-kriamos sticky-lg-top top-90">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-id-card me-2"></i>Identificación</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($session)): ?>
                            <p class="text-muted small">Ingrese la cédula registrada en la planilla de capacitación para iniciar una sesión de ayuda.</p>
                            <form action="<?= e(current_url('chat_ia_iniciar')) ?>" method="post">
                                <label class="form-label fw-semibold">Cédula</label>
                                <input type="text" name="document_number" value="<?= e($document ?? '') ?>" class="form-control form-control-lg mb-3" inputmode="numeric" placeholder="Ejemplo: 123456789" required>
                                <button class="btn btn-kriamos-orange btn-lg w-100" type="submit"><i class="fa-solid fa-comments me-2"></i>Iniciar chat</button>
                            </form>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <?php if (!empty($session['photo_path'])): ?>
                                    <img src="<?= e($session['photo_path']) ?>" alt="Foto" class="rounded-circle object-fit-cover" style="width:64px;height:64px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-kriamos-blue text-white d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                                        <i class="fa-solid fa-user fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="h6 fw-bold text-kriamos-blue mb-1"><?= e($session['full_name']) ?></h3>
                                    <div class="small text-muted"><?= e(ucfirst($session['person_type'])) ?> · <?= e($session['work_site']) ?></div>
                                </div>
                            </div>
                            <div class="small text-muted mb-3">
                                Cédula: <?= e($session['document_number']) ?><br>
                                Empresa: <?= e($session['company']) ?><br>
                                Cargo: <?= e($session['position']) ?>
                                <?php if (!empty($session['department'])): ?><br>Equipo: <?= e($session['department']) ?><?php endif; ?>
                            </div>
                            <a class="btn btn-outline-primary w-100" href="<?= e(current_url('chat_ia')) ?>"><i class="fa-solid fa-plus me-2"></i>Nueva sesión</a>
                        <?php endif; ?>

                        <hr>
                        <div class="alert alert-warning small mb-0">
                            <strong>Uso responsable:</strong> el chat orienta con base en información interna. No reemplaza decisiones del Médico Veterinario, Administrador de Granja, Dirección de Operaciones, SG-SST ni autoridad sanitaria.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-kriamos chat-panel">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-robot me-2"></i>Chat de ayuda</h2>
                            <div class="small text-muted">Respuestas basadas en base de conocimiento interna de KRIAMOS.</div>
                        </div>
                        <?php if (!empty($session)): ?>
                            <span class="badge text-bg-success">Sesión activa</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Pendiente de identificación</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="chat-messages p-4" id="chatMessages">
                            <?php if (empty($session)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-comments fa-3x text-kriamos-orange mb-3"></i>
                                    <h3 class="h5 fw-bold text-kriamos-blue">Inicie sesión con su cédula</h3>
                                    <p class="mb-0">Después podrá hacer preguntas sobre cursos, certificaciones, bioseguridad, bienestar animal y uso de la app.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="chat-bubble <?= $message['sender'] === 'user' ? 'chat-user' : 'chat-assistant' ?>">
                                        <div class="chat-bubble-label">
                                            <?= $message['sender'] === 'user' ? 'Usted' : 'Asistente KRIAMOS IA' ?> · <?= e(date('d/m/Y h:i a', strtotime($message['created_at']))) ?>
                                        </div>
                                        <div class="chat-bubble-text"><?= nl2br(e($message['message'])) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($session)): ?>
                        <div class="card-footer bg-white p-3">
                            <form action="<?= e(current_url('chat_ia_preguntar')) ?>" method="post" class="chat-form">
                                <input type="hidden" name="session_token" value="<?= e($session['session_token']) ?>">
                                <div class="input-group input-group-lg">
                                    <textarea name="question" class="form-control" rows="1" maxlength="1200" placeholder="Escriba su pregunta..." required></textarea>
                                    <button class="btn btn-kriamos-orange" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
                                </div>
                            </form>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php foreach (['¿Cómo presento un examen?', '¿Cuándo vence mi certificado?', '¿Qué hago si sospecho una emergencia sanitaria?', '¿Cómo descargo materiales offline?'] as $sample): ?>
                                    <form action="<?= e(current_url('chat_ia_preguntar')) ?>" method="post">
                                        <input type="hidden" name="session_token" value="<?= e($session['session_token']) ?>">
                                        <input type="hidden" name="question" value="<?= e($sample) ?>">
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><?= e($sample) ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const messages = document.getElementById('chatMessages');
    if (messages) messages.scrollTop = messages.scrollHeight;
});
</script>
