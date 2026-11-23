<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($documento['titulo']) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($documento['categoria_nombre']) ?> | v<?= (int) $documento['version'] ?> | <?= htmlspecialchars($documento['estado']) ?></p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/documentos" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/documentos/download?id=<?= (int) $documento['id'] ?>" class="btn btn-outline-kriamos">Descargar</a>
            <?php if (\App\Services\Auth::can('documentos.editar')): ?>
                <a href="<?= BASE_PATH ?>/documentos/edit?id=<?= (int) $documento['id'] ?>" class="btn btn-kriamos">Editar</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-kriamos mb-4">
                <div class="card-header bg-white"><strong>Información documental</strong></div>
                <div class="card-body">
                    <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($documento['descripcion'] ?? 'Sin descripción')) ?></p>
                    <div class="row">
                        <div class="col-md-4"><strong>Granja:</strong><br><?= htmlspecialchars($documento['granja_sigla'] ?? 'General') ?></div>
                        <div class="col-md-4"><strong>Galpón:</strong><br><?= htmlspecialchars($documento['galpon_codigo'] ?? 'No aplica') ?></div>
                        <div class="col-md-4"><strong>Archivo:</strong><br><?= htmlspecialchars(strtoupper($documento['extension'] ?? '')) ?> | <?= number_format((int) $documento['peso_bytes'] / 1024, 2, ',', '.') ?> KB</div>
                    </div>
                </div>
            </div>

            <div class="card card-kriamos mb-4">
                <div class="card-header bg-white"><strong>Versiones</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Versión</th><th>Usuario</th><th>Extensión</th><th>Peso</th><th>Comentario</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($versiones as $version): ?>
                                <tr>
                                    <td>v<?= (int) $version['version'] ?></td>
                                    <td><?= htmlspecialchars(trim($version['nombres'] . ' ' . $version['apellidos'])) ?></td>
                                    <td><?= htmlspecialchars(strtoupper($version['extension'])) ?></td>
                                    <td><?= number_format((int) $version['peso_bytes'] / 1024, 2, ',', '.') ?> KB</td>
                                    <td><?= htmlspecialchars($version['comentario'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($version['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-kriamos">
                <div class="card-header bg-white"><strong>Evidencias asociadas</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Título</th><th>Tipo</th><th>Ext.</th><th>Usuario</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($evidencias as $ev): ?>
                                <tr>
                                    <td><a href="<?= htmlspecialchars($ev['archivo_path']) ?>" target="_blank"><?= htmlspecialchars($ev['titulo'] ?? 'Evidencia') ?></a></td>
                                    <td><?= htmlspecialchars($ev['tipo']) ?></td>
                                    <td><?= htmlspecialchars(strtoupper($ev['extension'])) ?></td>
                                    <td><?= htmlspecialchars($ev['email']) ?></td>
                                    <td><?= htmlspecialchars($ev['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($evidencias)): ?><tr><td colspan="5" class="text-center text-muted">Sin evidencias asociadas.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <form method="post" action="<?= BASE_PATH ?>/evidencias/store" enctype="multipart/form-data" class="card card-kriamos">
                <?= Csrf::input() ?>
                <input type="hidden" name="documento_id" value="<?= (int) $documento['id'] ?>">
                <div class="card-header bg-white"><strong>Cargar evidencia</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Título</label><input type="text" name="titulo" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Archivo</label><input type="file" name="archivo" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Fecha/hora de toma</label><input type="datetime-local" name="tomada_at" class="form-control"></div>
                    <div class="row">
                        <div class="col-md-6"><label class="form-label">Latitud</label><input type="number" step="0.00000001" name="latitud" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Longitud</label><input type="number" step="0.00000001" name="longitud" class="form-control"></div>
                    </div>
                </div>
                <div class="card-footer bg-white"><button type="submit" class="btn btn-kriamos w-100">Cargar evidencia</button></div>
            </form>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
