document.addEventListener('DOMContentLoaded', () => {
    console.log('Kriamos BioGestión ICA - Fase 06 iniciada');

    document.querySelectorAll('[data-no-copy-paste="true"]').forEach((field) => {
        ['copy', 'paste', 'cut'].forEach((eventName) => {
            field.addEventListener(eventName, (event) => event.preventDefault());
        });
    });

    const noAplicaCheck = document.getElementById('noAplicaCheck');
    if (noAplicaCheck) {
        noAplicaCheck.addEventListener('change', () => {
            const motivo = document.querySelector('[name="motivo_no_aplica"]');
            if (motivo) {
                motivo.required = noAplicaCheck.checked;
            }
        });
    }

    const canvas = document.getElementById('signatureCanvas');
    const input = document.getElementById('firmaBase64');
    const clear = document.getElementById('clearSignature');
    const form = document.getElementById('firmaForm');

    if (canvas && input) {
        const ctx = canvas.getContext('2d');
        let drawing = false;

        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#143560';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const pos = (event) => {
            const rect = canvas.getBoundingClientRect();
            const touch = event.touches ? event.touches[0] : event;
            return {
                x: (touch.clientX - rect.left) * (canvas.width / rect.width),
                y: (touch.clientY - rect.top) * (canvas.height / rect.height)
            };
        };

        const start = (event) => {
            drawing = true;
            const p = pos(event);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            event.preventDefault();
        };

        const move = (event) => {
            if (!drawing) return;
            const p = pos(event);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            event.preventDefault();
        };

        const end = (event) => {
            drawing = false;
            input.value = canvas.toDataURL('image/png');
            event.preventDefault();
        };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseleave', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', end, { passive: false });

        if (clear) {
            clear.addEventListener('click', () => {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                input.value = '';
            });
        }

        if (form) {
            form.addEventListener('submit', () => {
                if (!input.value) {
                    input.value = canvas.toDataURL('image/png');
                }
            });
        }
    }
});
