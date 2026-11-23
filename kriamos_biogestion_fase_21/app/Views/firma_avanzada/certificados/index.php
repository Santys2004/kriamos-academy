<?php
use App\Services\Csrf;
require dirname(dirname(__DIR__)) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Certificados de firma</h1>
            <p class="text-muted mb-0">Certificados internos y externos asociados a usuarios firmantes.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/firma-avanzada" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= BASE_PATH ?>/firma-avanzada/certificados/store" class="card card-kriamos mb-4">
        <?= Csrf::input() ?>
        <div class="card-header bg-white"><strong>Crear certificado interno</strong></div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <select name="usuario_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= htmlspecialchars($u['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="interno">Interno</option>
                        <option value="externo">Externo</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Entidad emisora</label><input type="text" name="entidad_emisora" class="form-control" value="Kriamos Autoridad Interna"></div>
                <div class="col-md-2"><label class="form-label">Desde</label><input type="date" name="vigente_desde" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" name="vigente_hasta" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>"></div>
                <div class="col-md-12"><button class="btn btn-kriamos" type="submit">Crear certificado</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Usuario</th><th>Tipo</th><th>Serial</th><th>Huella SHA-256</th><th>Entidad</th><th>Vigencia</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php foreach ($certificados as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['tipo']) ?></td>
                            <td><code><?= htmlspecialchars($c['serial']) ?></code></td>
                            <td><small><code><?= htmlspecialchars(substr($c['huella_sha256'], 0, 24)) ?>...</code></small></td>
                            <td><?= htmlspecialchars($c['entidad_emisora']) ?></td>
                            <td><?= htmlspecialchars($c['vigente_desde']) ?> / <?= htmlspecialchars($c['vigente_hasta']) ?></td>
                            <td><?= htmlspecialchars($c['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($certificados)): ?><tr><td colspan="7" class="text-center text-muted">No hay certificados registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
