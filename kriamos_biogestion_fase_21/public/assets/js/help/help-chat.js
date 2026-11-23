document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('helpChatForm');
    const messages = document.getElementById('chatMessages');
    const rateForm = document.getElementById('rateForm');
    const rateLogId = document.getElementById('rateLogId');

    if (!form || !messages) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const input = form.querySelector('[name="pregunta"]');
        const pregunta = input.value.trim();

        if (!pregunta) return;

        appendMessage('user', 'Usuario', pregunta);
        input.value = '';

        try {
            const data = new FormData();
            data.append('pregunta', pregunta);

            const response = await fetch('/ayuda/chat/ask', {
                method: 'POST',
                body: data
            });

            const result = await response.json();

            if (result.ok) {
                appendMessage('bot', 'Asistente Kriamos', result.respuesta);
                if (rateForm && rateLogId) {
                    rateLogId.value = result.log_id;
                    rateForm.classList.remove('d-none');
                }
            } else {
                appendMessage('bot', 'Asistente Kriamos', result.message || 'No fue posible procesar la pregunta.');
            }
        } catch (error) {
            appendMessage('bot', 'Asistente Kriamos', 'No fue posible conectar con el servicio de ayuda.');
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
