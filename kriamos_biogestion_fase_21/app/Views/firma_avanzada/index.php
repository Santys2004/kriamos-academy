<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Firma electrónica avanzada</h1>
            <p class="text-muted mb-0">Firma reforzada, hash documental, OTP y sellado de tiempo interno.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/firma-avanzada/solicitar-otp" class="btn btn-outline-kriamos">Solicitar OTP</a>
            <a href="<?= BASE_PATH ?>/firma-avanzada/certificados" class="btn btn-outline-secondary">Certificados</a>
            <a href="<?= BASE_PATH ?>/firma-avanzada/csv?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn btn-kriamos">Exportar CSV</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-pen-nib metric-icon"></i><span class="metric-label">Firmadas</span><strong><?= (int) $metricas['firmadas'] ?></strong><small>Firmas emitidas.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-clock metric-icon"></i><span class="metric-label">Selladas</span><strong><?= (int) $metricas['selladas'] ?></strong><small>Con sello de tiempo.</small></div></div>
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-ban metric-icon"></i><span class="metric-label">Revocadas</span><strong><?= (int) $metricas['revocadas'] ?></strong><small>Firmas anuladas.</small></div></div>
    </div>

    <?php if (!empty($_SESSION['last_signature_otp_demo'])): ?>
        <div class="alert alert-warning">
            OTP demostrativo vigente: <strong><?= htmlspecialchars($_SESSION['last_signature_otp_demo']) ?></strong>.
            En producción debe enviarse por canal seguro y no mostrarse en pantalla.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <form method="post" action="<?= BASE_PATH ?>/firma-avanzada/firmar-formato" class="card card-kriamos h-100">
                <?= Csrf::input() ?>
                <div class="card-header bg-white"><strong>Firmar formato</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">ID formato registro</label><input type="number" name="formato_registro_id" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">OTP</label><input type="text" name="otp" class="form-control" required></div>
                    <button class="btn btn-kriamos" type="submit">Firmar formato</button>
                </div>
            </form>
        </div>

        <div class="col-lg-6">
            <form method="post" action="<?= BASE_PATH ?>/firma-avanzada/firmar-documento" class="card card-kriamos h-100">
                <?= Csrf::input() ?>
                <div class="card-header bg-white"><strong>Firmar documento</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">ID documento</label><input type="number" name="documento_id" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">OTP</label><input type="text" name="otp" class="form-control" required></div>
                    <button class="btn btn-kriamos" type="submit">Firmar documento</button>
                </div>
            </form>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <select name="usuario_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= ($_GET['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="firmada" <?= ($_GET['estado'] ?? '') === 'firmada' ? 'selected' : '' ?>>Firmada</option>
                        <option value="sellada" <?= ($_GET['estado'] ?? '') === 'sellada' ? 'selected' : '' ?>>Sellada</option>
                        <option value="revocada" <?= ($_GET['estado'] ?? '') === 'revocada' ? 'selected' : '' ?>>Revocada</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Objeto</th><th>Hash original</th><th>Hash firma</th><th>Estado</th><th>Verificación</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($firmas as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['created_at']) ?></td>
                            <td><?= htmlspecialchars($f['email']) ?></td>
                            <td><?= htmlspecialchars($f['tipo_objeto']) ?> #<?= (int) $f['objeto_id'] ?></td>
                            <td><small><code><?= htmlspecialchars(substr($f['hash_original'], 0, 18)) ?>...</code></small></td>
                            <td><small><code><?= htmlspecialchars(substr($f['hash_firma'], 0, 18)) ?>...</code></small></td>
                            <td><?= htmlspecialchars($f['estado']) ?></td>
                            <td><?= htmlspecialchars($f['estado_verificacion'] ?? 'sin verificar') ?></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/firma-avanzada/verificar?id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline-kriamos">Verificar</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($firmas)): ?><tr><td colspan="8" class="text-center text-muted">No hay firmas avanzadas registradas.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
