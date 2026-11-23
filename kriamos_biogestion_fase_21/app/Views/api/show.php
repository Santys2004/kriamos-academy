<?php
use App\Services\Csrf;
$plainToken = $_SESSION['plain_api_token'] ?? null;
unset($_SESSION['plain_api_token']);
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($client['nombre']) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($client['descripcion'] ?? '') ?></p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/api-admin" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <?php if ($plainToken): ?>
        <div class="alert alert-warning">
            <strong>Token generado. Cópielo ahora:</strong>
            <pre class="mt-2 mb-0"><?= htmlspecialchars($plainToken) ?></pre>
            <small>No volverá a mostrarse.</small>
        </div>
    <?php endif; ?>

    <div class="card card-kriamos mb-4">
        <div class="card-header bg-white"><strong>Generar token</strong></div>
        <div class="card-body">
            <form method="post" action="<?= BASE_PATH ?>/api-admin/tokens/generate" class="row g-3 align-items-end">
                <?= Csrf::input() ?>
                <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
                <div class="col-md-4"><label class="form-label">Fecha de vencimiento</label><input type="datetime-local" name="expires_at" class="form-control"></div>
                <div class="col-md-4"><button class="btn btn-kriamos" type="submit">Generar token</button></div>
            </form>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white"><strong>Tokens</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Prefijo</th><th>Estado</th><th>Vence</th><th>Último uso</th><th>Creado</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($tokens as $token): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($token['token_prefix']) ?>...</code></td>
                            <td><?= htmlspecialchars($token['estado']) ?></td>
                            <td><?= htmlspecialchars($token['expires_at'] ?? 'No vence') ?></td>
                            <td><?= htmlspecialchars($token['last_used_at'] ?? 'Sin uso') ?></td>
                            <td><?= htmlspecialchars($token['created_at']) ?></td>
                            <td class="text-end">
                                <?php if ($token['estado'] === 'activo'): ?>
                                    <form method="post" action="<?= BASE_PATH ?>/api-admin/tokens/revoke" class="d-inline">
                                        <?= Csrf::input() ?>
                                        <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
                                        <input type="hidden" name="token_id" value="<?= (int) $token['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Revocar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tokens)): ?><tr><td colspan="6" class="text-center text-muted">Sin tokens registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        Ejemplo de consumo:
        <pre>curl -H "Authorization: Bearer TOKEN" <?= htmlspecialchars((($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'dominio'))) ?>/api/v1/health</pre>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
