<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-kriamos sticky-lg-top top-90">
                    <div class="card-header bg-white">
                        <h1 class="h4 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de certificados</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-warning small"><?= e($error) ?></div>
                        <?php endif; ?>
                        <form action="<?= e(current_url('historial_certificados_resultado')) ?>" method="post">
                            <label class="form-label fw-semibold">Cédula registrada</label>
                            <input type="text" name="document_number" value="<?= e($document ?? '') ?>" class="form-control form-control-lg mb-3" inputmode="numeric" placeholder="Ejemplo: 123456789" required>
                            <button type="submit" class="btn btn-kriamos-orange w-100">
                                <i class="fa-solid fa-search me-2"></i>Consultar certificados
                            </button>
                        </form>
                        <hr>
                        <a href="<?= e(current_url('certificados')) ?>" class="btn btn-outline-primary w-100">
                            <i class="fa-solid fa-list-check me-2"></i>Panel administrativo
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <?php if (empty($certificates)): ?>
                    <div class="card card-kriamos">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-certificate fa-3x text-kriamos-orange mb-3"></i>
                            <h2 class="h4 fw-bold text-kriamos-blue">Consulta de certificados</h2>
                            <p class="text-muted mb-0">Ingrese la cédula para revisar certificados aprobados, vigencia y descarga PDF.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card card-kriamos">
                        <div class="card-header bg-white">
                            <h2 class="h5 fw-bold text-kriamos-blue mb-0">Certificados encontrados</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Persona</th>
                                            <th>Curso / módulo</th>
                                            <th>Nota</th>
                                            <th>Vigencia</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($certificates as $certificate): ?>
                                            <?php $status = $certificate['computed_status'] ?? $certificate['status']; ?>
                                            <tr>
                                                <td>
                                                    <strong><?= e($certificate['full_name']) ?></strong><br>
                                                    <span class="small text-muted">CC <?= e($certificate['document_number']) ?> · <?= e($certificate['work_site']) ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= e($certificate['course_code']) ?></strong> <?= e($certificate['course_title']) ?><br>
                                                    <span class="small text-muted"><?= e($certificate['module_title']) ?></span>
                                                </td>
                                                <td><?= e((string)$certificate['score']) ?>%</td>
                                                <td>
                                                    <span class="small">Desde <?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?><br>Hasta <?= e(date('d/m/Y', strtotime($certificate['expires_at']))) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $status === 'vigente' ? 'text-bg-success' : ($status === 'vencido' ? 'text-bg-warning' : 'text-bg-danger') ?>"><?= e(strtoupper($status)) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= e(current_url('ver_certificado', ['token' => $certificate['verification_token']])) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                                    <a href="<?= e(current_url('descargar_certificado', ['code' => $certificate['certificate_code']])) ?>" class="btn btn-sm btn-kriamos-orange">PDF</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
