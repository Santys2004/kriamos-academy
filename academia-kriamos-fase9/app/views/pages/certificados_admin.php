<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold text-kriamos-blue mb-1"><i class="fa-solid fa-list-check me-2 text-kriamos-orange"></i>Panel de certificados</h1>
                <p class="text-muted mb-0">Consulta administrativa de certificados generados por Academia KRIAMOS.</p>
            </div>
            <a href="<?= e(current_url('historial_certificados')) ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-search me-2"></i>Buscar por cédula
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="card card-kriamos">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Persona</th>
                                <th>Curso / módulo</th>
                                <th>Nota</th>
                                <th>Vigencia</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certificates)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">Todavía no hay certificados generados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($certificates as $certificate): ?>
                                    <?php $status = $certificate['computed_status'] ?? $certificate['status']; ?>
                                    <tr>
                                        <td><span class="badge bg-kriamos-blue"><?= e($certificate['certificate_code']) ?></span></td>
                                        <td>
                                            <strong><?= e($certificate['full_name']) ?></strong><br>
                                            <span class="small text-muted">CC <?= e($certificate['document_number']) ?> · <?= e(ucfirst($certificate['person_type'])) ?> · <?= e($certificate['company']) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= e($certificate['course_code']) ?></strong> <?= e($certificate['course_title']) ?><br>
                                            <span class="small text-muted"><?= e($certificate['module_title']) ?></span>
                                        </td>
                                        <td><?= e((string)$certificate['score']) ?>%</td>
                                        <td class="small">
                                            Aprobó: <?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?><br>
                                            Vence: <?= e(date('d/m/Y', strtotime($certificate['expires_at']))) ?>
                                        </td>
                                        <td><span class="badge <?= $status === 'vigente' ? 'text-bg-success' : ($status === 'vencido' ? 'text-bg-warning' : 'text-bg-danger') ?>"><?= e(strtoupper($status)) ?></span></td>
                                        <td class="text-end">
                                            <a href="<?= e(current_url('ver_certificado', ['token' => $certificate['verification_token']])) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                            <a href="<?= e(current_url('descargar_certificado', ['code' => $certificate['certificate_code']])) ?>" class="btn btn-sm btn-kriamos-orange">PDF</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
