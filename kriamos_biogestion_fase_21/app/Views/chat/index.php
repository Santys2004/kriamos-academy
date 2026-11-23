<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Chat de ayuda</h1>
            <p class="text-muted mb-0">Asistente interno basado en la base de conocimiento de Kriamos BioGestión ICA.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/ayuda" class="btn btn-outline-secondary">Centro de ayuda</a></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-kriamos chat-card">
                <div class="card-header bg-white"><strong>Consulta rápida</strong></div>
                <div class="card-body">
                    <div id="chatMessages" class="chat-messages">
                        <div class="chat-message bot">
                            <strong>Asistente Kriamos:</strong>
                            <p>Escriba una pregunta sobre formatos, firmas, alertas, documentos, reportes o sincronización.</p>
                        </div>
                    </div>

                    <form id="helpChatForm" class="mt-3">
                        <div class="input-group">
                            <input type="text" name="pregunta" class="form-control" placeholder="Ejemplo: ¿Cómo firmo un formato?" required>
                            <button class="btn btn-kriamos" type="submit">Preguntar</button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="post" action="<?= BASE_PATH ?>/ayuda/chat/rate" class="card card-kriamos mt-3 d-none" id="rateForm">
                <?= Csrf::input() ?>
                <input type="hidden" name="log_id" id="rateLogId">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <span>¿La respuesta fue útil?</span>
                    <div>
                        <button class="btn btn-sm btn-outline-kriamos" name="util" value="1">Sí</button>
                        <button class="btn btn-sm btn-outline-secondary" name="util" value="0">No</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card card-kriamos">
                <div class="card-header bg-white"><strong>Preguntas recientes</strong></div>
                <div class="card-body">
                    <?php foreach (array_slice($logs, 0, 10) as $log): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <small class="text-muted"><?= htmlspecialchars($log['created_at']) ?></small>
                            <p class="mb-1"><?= htmlspecialchars($log['pregunta']) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?><p class="text-muted">No hay preguntas recientes.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
