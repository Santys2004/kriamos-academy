<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Detalle de formato</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($registro['codigo_consecutivo']) ?></p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/formatos" class="btn btn-outline-secondary">Volver</a>
            <?php if (empty($firmas) && \App\Services\Auth::can('formatos.aprobar')): ?>
                <a href="<?= BASE_PATH ?>/firmas/create?id=<?= (int) $registro['id'] ?>" class="btn btn-outline-kriamos">
                    <i class="fa-solid fa-signature me-2"></i> Firmar
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-kriamos">
                <i class="fa-solid fa-print me-2"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card card-kriamos printable-area">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 fw-bold text-kriamos mb-0"><?= htmlspecialchars($registro['tipo_nombre']) ?></h2>
                <small class="text-muted"><?= htmlspecialchars($registro['fundamento'] ?? '') ?></small>
            </div>
            <span class="badge badge-kriamos"><?= htmlspecialchars($registro['estado_nombre']) ?></span>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><strong>Granja:</strong><br><?= htmlspecialchars($registro['granja_sigla'] . ' - ' . $registro['granja_nombre']) ?></div>
                <div class="col-md-3"><strong>Galpón:</strong><br><?= htmlspecialchars($registro['galpon_codigo'] ?? 'No aplica') ?></div>
                <div class="col-md-3"><strong>Ciclo:</strong><br><?= htmlspecialchars($registro['ciclo_codigo'] ?? 'No aplica') ?></div>
                <div class="col-md-3"><strong>Fecha:</strong><br><?= htmlspecialchars($registro['fecha_formato']) ?> <?= htmlspecialchars($registro['hora_formato'] ?? '') ?></div>
                <div class="col-md-3"><strong>Usuario:</strong><br><?= htmlspecialchars(trim($registro['usuario_nombres'] . ' ' . $registro['usuario_apellidos'])) ?></div>
                <div class="col-md-3"><strong>Versión:</strong><br><?= (int) $registro['version'] ?></div>
                <div class="col-md-6"><strong>Hash documental:</strong><br><small><?= htmlspecialchars($registro['hash_documento'] ?? '') ?></small></div>
            </div>

            <?php if ((int) $registro['es_no_aplica'] === 1): ?>
                <div class="alert alert-warning">
                    <strong>Registro marcado como No aplica.</strong><br>
                    <?= nl2br(htmlspecialchars($registro['motivo_no_aplica'] ?? '')) ?>
                </div>
            <?php endif; ?>

            <h3 class="h5 text-kriamos">Información diligenciada</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <?php foreach (($registro['datos'] ?? []) as $campo => $valor): ?>
                            <tr>
                                <th style="width: 30%;"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($campo))) ?></th>
                                <td><?= nl2br(htmlspecialchars(is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string) $valor)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($registro['observaciones'])): ?>
                <h3 class="h5 text-kriamos">Observaciones generales</h3>
                <p><?= nl2br(htmlspecialchars($registro['observaciones'])) ?></p>
            <?php endif; ?>

            
            <?php if (!empty($firmas)): ?>
                <h3 class="h5 text-kriamos mt-4">Firma digital interna</h3>
                <?php foreach ($firmas as $firma): ?>
                    <div class="border rounded p-3 mb-2">
                        <p class="mb-1"><strong>Firmado por:</strong> <?= htmlspecialchars(trim($firma['nombres'] . ' ' . $firma['apellidos'])) ?></p>
                        <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($firma['email']) ?></p>
                        <p class="mb-1"><strong>Fecha y hora:</strong> <?= htmlspecialchars($firma['firmado_at']) ?></p>
                        <p class="mb-1"><strong>Método:</strong> <?= htmlspecialchars($firma['metodo']) ?></p>
                        <p class="mb-1"><strong>Hash firma:</strong> <small><?= htmlspecialchars($firma['hash_firma']) ?></small></p>
                        <?php if (!empty($firma['firma_path'])): ?>
                            <img src="<?= htmlspecialchars($firma['firma_path']) ?>" alt="Firma digital" class="firma-preview">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($incidencias)): ?>
                <h3 class="h5 text-kriamos mt-4">Incidencias de calidad</h3>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Tipo</th><th>Nivel</th><th>Mensaje</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td><?= htmlspecialchars($incidencia['tipo']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['nivel']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['mensaje']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['estado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="alert alert-info small mb-0">
                Este registro fue generado digitalmente por Kriamos BioGestión ICA y cuenta con trazabilidad interna.
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
