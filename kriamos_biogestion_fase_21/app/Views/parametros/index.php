<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Parámetros del sistema</h1>
            <p class="text-muted mb-0">Variables operativas editables sin modificar código.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/admin" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="mb-3">
        <a href="<?= BASE_PATH ?>/admin/parametros" class="btn btn-sm btn-outline-secondary">Todos</a>
        <?php foreach ($grupos as $g): ?>
            <a href="<?= BASE_PATH ?>/admin/parametros?grupo=<?= urlencode($g) ?>" class="btn btn-sm btn-outline-kriamos"><?= htmlspecialchars($g) ?></a>
        <?php endforeach; ?>
    </div>

    <form method="post" action="<?= BASE_PATH ?>/admin/parametros/update" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Grupo</th><th>Clave</th><th>Valor</th><th>Descripción</th><th>Editable</th></tr></thead>
                <tbody>
                    <?php foreach ($parametros as $parametro): ?>
                        <tr>
                            <td><?= htmlspecialchars($parametro['grupo']) ?></td>
                            <td><code><?= htmlspecialchars($parametro['clave']) ?></code></td>
                            <td>
                                <?php if ((int) $parametro['editable'] === 1): ?>
                                    <input type="text" name="parametros[<?= (int) $parametro['id'] ?>]" class="form-control" value="<?= htmlspecialchars($parametro['valor']) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($parametro['valor']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($parametro['descripcion'] ?? '') ?></td>
                            <td><?= (int) $parametro['editable'] === 1 ? 'Sí' : 'No' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-kriamos" type="submit">Guardar parámetros</button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
