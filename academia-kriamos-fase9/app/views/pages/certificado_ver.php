<section class="py-5">
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger card-kriamos">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?>
            </div>
            <a href="<?= e(current_url('historial_certificados')) ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Consultar historial
            </a>
        <?php elseif (!empty($certificate)): ?>
            <div class="row justify-content-center">
                <div class="col-xl-11">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h1 class="h3 fw-bold text-kriamos-blue mb-1"><i class="fa-solid fa-certificate me-2 text-kriamos-orange"></i>Certificado de capacitación</h1>
                            <div class="text-muted small">Código: <?= e($certificate['certificate_code']) ?></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= e(current_url('descargar_certificado', ['code' => $certificate['certificate_code']])) ?>" class="btn btn-kriamos-orange">
                                <i class="fa-solid fa-file-pdf me-2"></i>Descargar PDF
                            </a>
                            <a href="<?= e(current_url('historial_certificados')) ?>" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i>Historial
                            </a>
                        </div>
                    </div>

                    <?php $status = $certificate['computed_status'] ?? $certificate['status']; ?>
                    <div class="alert <?= $status === 'vigente' ? 'alert-success' : ($status === 'vencido' ? 'alert-warning' : 'alert-danger') ?>">
                        <strong>Estado:</strong> <?= e(strtoupper($status)) ?> ·
                        <strong>Fecha de aprobación:</strong> <?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?> ·
                        <strong>Vigente hasta:</strong> <?= e(date('d/m/Y', strtotime($certificate['expires_at']))) ?>
                    </div>

                    <div class="card card-kriamos overflow-hidden">
                        <div class="card-body p-0 bg-light">
                            <iframe title="Certificado KRIAMOS" srcdoc="<?= e($service->certificateHtml($certificate, false)) ?>" class="w-100 border-0" style="height: 780px;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
