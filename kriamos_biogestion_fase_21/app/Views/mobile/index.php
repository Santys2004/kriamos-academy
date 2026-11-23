<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">App móvil</h1>
            <p class="text-muted mb-0">Base Ionic/Capacitor para Android, iOS y operación móvil de Kriamos BioGestión ICA.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-mobile-screen metric-icon"></i>
                <span class="metric-label">Dispositivos activos</span>
                <strong><?= (int) $metricas['activos'] ?></strong>
                <small>Registrados por la app móvil.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-brands fa-android metric-icon"></i>
                <span class="metric-label">Android</span>
                <strong>Capacitor</strong>
                <small>Preparado para Android Studio.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-brands fa-apple metric-icon"></i>
                <span class="metric-label">iOS</span>
                <strong>Capacitor</strong>
                <small>Preparado para Xcode.</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fa-solid fa-wifi metric-icon"></i>
                <span class="metric-label">Offline</span>
                <strong>Local</strong>
                <small>Cola móvil preparada.</small>
            </div>
        </div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Comandos de preparación móvil</strong></div>
        <div class="card-body">
            <pre class="mobile-code">cd mobile
npm install
npm run build
npx cap add android
npx cap add ios
npx cap sync</pre>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Dispositivos registrados</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Plataforma</th>
                        <th>Dispositivo</th>
                        <th>Versión app</th>
                        <th>Estado</th>
                        <th>Último uso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['email'] ?? 'No disponible') ?></td>
                            <td><?= htmlspecialchars($device['platform']) ?></td>
                            <td><?= htmlspecialchars($device['device_name'] ?? $device['device_uuid']) ?></td>
                            <td><?= htmlspecialchars($device['app_version'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['estado']) ?></td>
                            <td><?= htmlspecialchars($device['last_seen_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($devices)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay dispositivos móviles registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-warning mt-4">
        Para compilar iOS se requiere macOS con Xcode. Para Android se requiere Android Studio y SDK actualizado.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
