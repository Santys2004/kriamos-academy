<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <span class="badge bg-kriamos-orange mb-2">Fase 7</span>
            <h1 class="h3 fw-bold text-kriamos-blue mb-1">Sistema de recompensas</h1>
            <p class="text-muted mb-0">Premios solo para empleados: 100% = 4 pollos congelados, 90% = 1 pollo congelado.</p>
        </div>
        <a href="<?= e(current_url('recompensas_export', $filters ?? [])) ?>" class="btn btn-outline-primary">
            <i class="fa-solid fa-file-csv me-2"></i>Exportar CSV
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (($success ?? '') === 'entregada'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>Recompensa marcada como entregada correctamente.</div>
    <?php elseif (($success ?? '') === 'anulada'): ?>
        <div class="alert alert-secondary"><i class="fa-solid fa-ban me-2"></i>Recompensa anulada correctamente.</div>
    <?php elseif (($success ?? '') === 'error'): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>No fue posible procesar la acción solicitada.</div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-kriamos h-100"><div class="card-body">
                <div class="small text-muted">Recompensas causadas</div>
                <div class="display-6 fw-bold text-kriamos-blue"><?= (int)($stats['total_rewards'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card card-kriamos h-100"><div class="card-body">
                <div class="small text-muted">Pendientes</div>
                <div class="display-6 fw-bold text-warning"><?= (int)($stats['pending_rewards'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card card-kriamos h-100"><div class="card-body">
                <div class="small text-muted">Pollos pendientes</div>
                <div class="display-6 fw-bold text-kriamos-orange"><?= (int)($stats['pending_chickens'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card card-kriamos h-100"><div class="card-body">
                <div class="small text-muted">Pollos entregados</div>
                <div class="display-6 fw-bold text-success"><?= (int)($stats['delivered_chickens'] ?? 0) ?></div>
            </div></div>
        </div>
    </div>

    <div class="card card-kriamos mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin_recompensas">
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['pendiente' => 'Pendiente', 'entregada' => 'Entregada', 'anulada' => 'Anulada'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sede</label>
                    <select name="work_site" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (['GAEE','GALM','Oficina'] as $site): ?>
                            <option value="<?= e($site) ?>" <?= (($filters['work_site'] ?? '') === $site) ? 'selected' : '' ?>><?= e($site) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Equipo</label>
                    <select name="department" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos'] as $department): ?>
                            <option value="<?= e($department) ?>" <?= (($filters['department'] ?? '') === $department) ? 'selected' : '' ?>><?= e($department) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-kriamos-orange" type="submit"><i class="fa-solid fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-bold text-kriamos-blue mb-0">Listado de recompensas</h2>
            <span class="badge text-bg-light"><?= count($rewards ?? []) ?> registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Empleado</th>
                            <th>Curso / módulo</th>
                            <th>Nota</th>
                            <th>Recompensa</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th style="min-width:280px">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rewards ?? [] as $reward): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-kriamos-blue"><?= e($reward['full_name']) ?></div>
                                    <div class="small text-muted">CC <?= e($reward['document_number']) ?> · <?= e($reward['work_site']) ?> · <?= e($reward['department'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($reward['course_code']) ?> · <?= e($reward['course_title']) ?></div>
                                    <div class="small text-muted"><?= e($reward['module_title']) ?></div>
                                </td>
                                <td><span class="badge text-bg-primary"><?= e((string)$reward['score']) ?>%</span></td>
                                <td>
                                    <div class="fw-bold text-kriamos-orange"><?= (int)$reward['frozen_chickens_qty'] ?> pollo(s)</div>
                                    <div class="small text-muted"><?= e($reward['reward_description']) ?></div>
                                </td>
                                <td><span class="badge <?= e($reward['status_badge']) ?>"><?= e($reward['status_label']) ?></span></td>
                                <td>
                                    <div class="small"><strong>Ganada:</strong> <?= e(human_datetime($reward['earned_at'])) ?></div>
                                    <?php if (!empty($reward['delivered_at'])): ?>
                                        <div class="small text-success"><strong>Entregada:</strong> <?= e(human_datetime($reward['delivered_at'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reward['status'] === 'pendiente'): ?>
                                        <form action="<?= e(current_url('marcar_recompensa_entregada')) ?>" method="post" class="row g-2 mb-2">
                                            <input type="hidden" name="reward_id" value="<?= (int)$reward['id'] ?>">
                                            <div class="col-12 col-md-5"><input type="text" name="delivered_by" class="form-control form-control-sm" placeholder="Entregado por" required></div>
                                            <div class="col-12 col-md-5"><input type="text" name="delivery_notes" class="form-control form-control-sm" placeholder="Observación"></div>
                                            <div class="col-12 col-md-2 d-grid"><button class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i></button></div>
                                        </form>
                                        <form action="<?= e(current_url('anular_recompensa')) ?>" method="post" onsubmit="return confirm('¿Anular esta recompensa?');">
                                            <input type="hidden" name="reward_id" value="<?= (int)$reward['id'] ?>">
                                            <input type="hidden" name="delivered_by" value="Administrador">
                                            <input type="hidden" name="delivery_notes" value="Anulada desde panel administrativo">
                                            <button class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-ban me-1"></i>Anular</button>
                                        </form>
                                    <?php else: ?>
                                        <div class="small text-muted">
                                            <?php if (!empty($reward['delivered_by'])): ?>Responsable: <?= e($reward['delivered_by']) ?><br><?php endif; ?>
                                            <?= e($reward['delivery_notes'] ?? 'Sin acción pendiente') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rewards)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No hay recompensas con los filtros seleccionados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
