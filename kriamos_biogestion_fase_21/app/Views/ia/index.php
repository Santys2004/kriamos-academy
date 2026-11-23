<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">IA Documental</h1>
            <p class="text-muted mb-0">Asistente interno basado en contexto documental, POE, formatos, ayuda y registros de Kriamos.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/ia/contextos" class="btn btn-outline-kriamos">Contextos</a>
            <a href="<?= BASE_PATH ?>/ia/consultas" class="btn btn-outline-secondary">Consultas</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-database metric-icon"></i><span class="metric-label">Contextos</span><strong><?= (int) $metricas['contextos'] ?></strong><small>Base documental IA.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-file-lines metric-icon"></i><span class="metric-label">Documentos</span><strong><?= (int) $metricas['documentos'] ?></strong><small>Indexados.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-comments metric-icon"></i><span class="metric-label">Consultas hoy</span><strong><?= (int) $metricas['consultas_hoy'] ?></strong><small>Uso diario.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-lock metric-icon"></i><span class="metric-label">Modo</span><strong>Local</strong><small>Privado por defecto.</small></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-kriamos ai-chat-card">
                <div class="card-header bg-white"><strong>Asistente IA interno</strong></div>
                <div class="card-body">
                    <div id="aiMessages" class="chat-messages">
                        <div class="chat-message bot">
                            <strong>IA Kriamos:</strong>
                            <p>Consulte sobre formatos ICA, POE, documentos, alertas, certificaciones, reportes o sincronización. La respuesta se genera con base en el contexto documental local.</p>
                        </div>
                    </div>

                    <form id="aiAskForm" class="mt-3">
                        <div class="row g-2">
                            <div class="col-md-9">
                                <input type="text" name="pregunta" class="form-control" placeholder="Ejemplo: ¿Cómo debo justificar un No aplica en un formato?" required>
                            </div>
                            <div class="col-md-2">
                                <select name="modo" class="form-select">
                                    <option value="local">Local</option>
                                    <option value="externo">Externo preparado</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-kriamos w-100" type="submit">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-warning mt-3">
                No ingrese contraseñas, tokens, datos personales sensibles ni información confidencial no autorizada. El modo externo está desactivado por defecto.
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-kriamos">
                <div class="card-header bg-white"><strong>Acciones rápidas</strong></div>
                <div class="card-body d-grid gap-2">
                    <?php if (\App\Services\Auth::can('ia.gestionar')): ?>
                        <a href="<?= BASE_PATH ?>/ia/contextos/indexar" class="btn btn-kriamos">Indexar contexto documental</a>
                    <?php endif; ?>
                    <a href="<?= BASE_PATH ?>/ia/contextos" class="btn btn-outline-kriamos">Ver contextos</a>
                    <a href="<?= BASE_PATH ?>/ia/consultas" class="btn btn-outline-secondary">Historial de consultas</a>
                    <a href="<?= BASE_PATH ?>/documentos" class="btn btn-outline-secondary">Núcleo documental</a>
                    <a href="<?= BASE_PATH ?>/ayuda" class="btn btn-outline-secondary">Centro de ayuda</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('aiAskForm');
    const messages = document.getElementById('aiMessages');

    if (!form || !messages) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const data = new FormData(form);
        const pregunta = data.get('pregunta');

        appendMessage('user', 'Usuario', pregunta);
        form.querySelector('[name="pregunta"]').value = '';

        try {
            const response = await fetch('/ia/ask', { method: 'POST', body: data });
            const result = await response.json();

            if (result.ok) {
                appendMessage('bot', 'IA Kriamos', result.respuesta + "\n\nFuente: " + result.proveedor);
            } else {
                appendMessage('bot', 'IA Kriamos', result.message || 'No fue posible responder.');
            }
        } catch (error) {
            appendMessage('bot', 'IA Kriamos', 'Error consultando el asistente: ' + error.message);
        }
    });

    function appendMessage(type, author, text) {
        const div = document.createElement('div');
        div.className = 'chat-message ' + type;
        div.innerHTML = '<strong>' + escapeHtml(author) + ':</strong><p>' + escapeHtml(text).replace(/\n/g, '<br>') + '</p>';
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(text) {
        const span = document.createElement('span');
        span.textContent = text;
        return span.innerHTML;
    }
});
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
