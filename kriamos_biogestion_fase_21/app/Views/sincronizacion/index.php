<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Modo sin conexión y sincronización</h1>
            <p class="text-muted mb-0">Control de cola offline, registros sincronizados y eventos de conexión.</p>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-kriamos" onclick="KriamosSync.syncPending()">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Sincronizar ahora
            </button>
        </div>
    </div>

    <div class="alert alert-info">
        Estado actual del navegador: <strong id="connectionStatusPanel">verificando...</strong>.
        Los formatos marcados para modo offline se almacenan en IndexedDB cuando no hay conexión.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-cloud-check metric-icon"></i><span class="metric-label">Sincronizados</span><strong><?= (int) $metricas['sincronizados'] ?></strong><small>Eventos registrados en servidor.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">Pendientes servidor</span><strong><?= (int) $metricas['pendientes'] ?></strong><small>Recibidos pendientes de consolidar.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-triangle-exclamation metric-icon"></i><span class="metric-label">Errores</span><strong><?= (int) $metricas['error'] ?></strong><small>Eventos con falla.</small></div></div>
        <div class="col-md-3"><div class="metric-card"><i class="fa-solid fa-code-compare metric-icon"></i><span class="metric-label">Conflictos</span><strong><?= (int) $metricas['conflicto'] ?></strong><small>Requieren revisión.</small></div></div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Cola local del navegador</strong></div>
        <div class="card-body">
            <p class="mb-2">Pendientes locales en este dispositivo: <strong id="localPendingCount">0</strong></p>
            <button class="btn btn-outline-kriamos" type="button" id="refreshLocalQueue">Actualizar conteo local</button>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Últimos eventos de sincronización en servidor</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead><tr><th>Local ID</th><th>Tipo</th><th>Estado</th><th>Usuario</th><th>Mensaje</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($log['local_id']) ?></small></td>
                            <td><?= htmlspecialchars($log['tipo']) ?></td>
                            <td><?= htmlspecialchars($log['estado']) ?></td>
                            <td><?= htmlspecialchars($log['email'] ?? 'No disponible') ?></td>
                            <td><?= htmlspecialchars($log['mensaje'] ?? '') ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?><tr><td colspan="6" class="text-center text-muted">Sin eventos registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const panel = document.getElementById('connectionStatusPanel');
    const localCount = document.getElementById('localPendingCount');
    const refresh = document.getElementById('refreshLocalQueue');

    async function updateLocal() {
        if (panel) panel.textContent = navigator.onLine ? 'En línea' : 'Sin conexión';
        if (localCount && window.KriamosDB) {
            localCount.textContent = await window.KriamosDB.countPending();
        }
    }

    if (refresh) refresh.addEventListener('click', updateLocal);
    window.addEventListener('online', updateLocal);
    window.addEventListener('offline', updateLocal);
    updateLocal();
});
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
