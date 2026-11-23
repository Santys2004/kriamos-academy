<?php
$statusClass = static function (string $status): string {
    return match ($status) {
        'vigente' => 'text-bg-success',
        'por_vencer' => 'text-bg-warning',
        'vencido', 'reactivado' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
};
$statusLabel = static function (string $status): string {
    return match ($status) {
        'vigente' => 'VIGENTE',
        'por_vencer' => 'POR VENCER',
        'vencido' => 'VENCIDO',
        'reactivado' => 'REACTIVADO',
        default => strtoupper($status),
    };
};
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h3 fw-bold text-kriamos-blue mb-1"><i class="fa-solid fa-calendar-days me-2 text-kriamos-orange"></i>Calendario de recertificación</h1>
                <p class="text-muted mb-0">Controla vencimientos, reactivaciones automáticas y alertas por correo de Academia KRIAMOS.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= e(current_url('admin_calendario', ['sync' => 1])) ?>" class="btn btn-outline-primary"><i class="fa-solid fa-rotate me-2"></i>Sincronizar certificados</a>
                <form method="post" action="<?= e(current_url('procesar_recordatorios')) ?>">
                    <button class="btn btn-kriamos-orange" type="submit"><i class="fa-solid fa-envelope-circle-check me-2"></i>Procesar alertas</button>
                </form>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($synced !== null): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i>Sincronización ejecutada. Certificados revisados: <?= e((string)$synced) ?>.</div>
        <?php endif; ?>
        <?php if (!empty($result)): ?>
            <div class="alert alert-info">
                <strong>Resultado de alertas:</strong>
                Procesadas: <?= e((string)$result['processed']) ?> · Enviadas/simuladas: <?= e((string)$result['sent']) ?> · Fallidas: <?= e((string)$result['failed']) ?> · Omitidas: <?= e((string)$result['skipped']) ?>.
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Total</div><div class="h3 fw-bold mb-0 text-kriamos-blue"><?= e((string)($stats['total'] ?? 0)) ?></div></div></div></div>
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Vigentes</div><div class="h3 fw-bold mb-0 text-success"><?= e((string)($stats['vigente'] ?? 0)) ?></div></div></div></div>
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Por vencer</div><div class="h3 fw-bold mb-0 text-warning"><?= e((string)($stats['por_vencer'] ?? 0)) ?></div></div></div></div>
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Reactivados</div><div class="h3 fw-bold mb-0 text-danger"><?= e((string)($stats['reactivado'] ?? 0)) ?></div></div></div></div>
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Vencidos</div><div class="h3 fw-bold mb-0 text-danger"><?= e((string)($stats['vencido'] ?? 0)) ?></div></div></div></div>
            <div class="col-6 col-lg-2"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Emails pendientes</div><div class="h3 fw-bold mb-0 text-kriamos-orange"><?= e((string)($stats['pendientes_email'] ?? 0)) ?></div></div></div></div>
        </div>

        <div class="card card-kriamos mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="index.php">
                    <input type="hidden" name="page" value="admin_calendario">
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <?php foreach (['vigente'=>'Vigente','por_vencer'=>'Por vencer','reactivado'=>'Reactivado','vencido'=>'Vencido'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sitio de trabajo</label>
                        <select class="form-select" name="work_site">
                            <option value="">Todos</option>
                            <?php foreach (['GAEE','GALM','Oficina'] as $site): ?>
                                <option value="<?= e($site) ?>" <?= (($filters['work_site'] ?? '') === $site) ? 'selected' : '' ?>><?= e($site) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de persona</label>
                        <select class="form-select" name="person_type">
                            <option value="">Todos</option>
                            <option value="empleado" <?= (($filters['person_type'] ?? '') === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                            <option value="contratista" <?= (($filters['person_type'] ?? '') === 'contratista') ? 'selected' : '' ?>>Contratista</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary w-100" type="submit"><i class="fa-solid fa-filter me-2"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-kriamos mb-5">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h2 class="h5 fw-bold mb-0 text-kriamos-blue">Programación por trabajador o contratista</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Persona</th>
                                <th>Curso / módulo</th>
                                <th>Fechas</th>
                                <th>Alertas</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-5">No hay programaciones para los filtros seleccionados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($item['full_name']) ?></strong><br>
                                        <span class="small text-muted">CC <?= e($item['document_number']) ?> · <?= e(ucfirst($item['person_type'])) ?> · <?= e($item['work_site']) ?></span><br>
                                        <span class="small text-muted"><?= e($item['email']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= e($item['course_code']) ?></strong> <?= e($item['course_title']) ?><br>
                                        <span class="small text-muted"><?= e($item['module_title']) ?></span>
                                    </td>
                                    <td class="small">
                                        Aprobó: <?= e(human_datetime($item['last_passed_at'])) ?><br>
                                        Nuevo ciclo: <strong><?= e(human_datetime($item['next_due_at'])) ?></strong>
                                    </td>
                                    <td class="small">
                                        5 días antes: <?= e(human_datetime($item['reminder_5d_at'])) ?><br>
                                        1 día antes: <?= e(human_datetime($item['reminder_1d_at'])) ?>
                                    </td>
                                    <td><span class="badge <?= e($statusClass($item['status'])) ?>"><?= e($statusLabel($item['status'])) ?></span></td>
                                    <td class="text-end">
                                        <form method="post" action="<?= e(current_url('enviar_recordatorio_manual')) ?>" class="d-inline">
                                            <input type="hidden" name="calendar_id" value="<?= e((string)$item['id']) ?>">
                                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-kriamos">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h2 class="h5 fw-bold mb-0 text-kriamos-blue">Historial de alertas</h2>
                <p class="text-muted small mb-0">En ambiente local, los correos quedan simulados en <code>storage/logs/mail_preview.log</code>.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Programada</th>
                                <th>Persona</th>
                                <th>Tipo</th>
                                <th>Curso</th>
                                <th>Correo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay alertas creadas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <tr>
                                    <td class="small"><?= e(human_datetime($n['scheduled_at'])) ?></td>
                                    <td class="small"><strong><?= e($n['full_name']) ?></strong><br>CC <?= e($n['document_number']) ?></td>
                                    <td><span class="badge text-bg-light border"><?= e(str_replace('_', ' ', $n['notification_type'])) ?></span></td>
                                    <td class="small"><?= e($n['course_code']) ?><br><?= e($n['module_title']) ?></td>
                                    <td class="small"><?= e($n['recipient_email']) ?></td>
                                    <td><span class="badge <?= $n['status'] === 'enviado' ? 'text-bg-success' : ($n['status'] === 'fallido' ? 'text-bg-danger' : ($n['status'] === 'omitido' ? 'text-bg-secondary' : 'text-bg-warning')) ?>"><?= e(strtoupper($n['status'])) ?></span></td>
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
