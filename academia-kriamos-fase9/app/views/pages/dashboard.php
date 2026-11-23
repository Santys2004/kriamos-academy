<?php
$overview = $dashboard['overview'] ?? [];
$completion = $dashboard['completion'] ?? [];
$time = $dashboard['time'] ?? [];
$target = (float)($dashboard['target_percent'] ?? 95);
$percent = static fn($value): string => number_format((float)$value, 2, ',', '.');
$int = static fn($value): string => number_format((int)$value, 0, ',', '.');
$barClass = static function (float $value, float $targetValue = 95.0): string {
    if ($value >= $targetValue) {
        return 'bg-success';
    }
    if ($value >= 80) {
        return 'bg-warning';
    }
    return 'bg-danger';
};
$badgeClass = static function (float $value, float $targetValue = 95.0): string {
    if ($value >= $targetValue) {
        return 'text-bg-success';
    }
    if ($value >= 80) {
        return 'text-bg-warning';
    }
    return 'text-bg-danger';
};
$progress = static function (float $value, float $targetValue = 95.0) use ($barClass, $percent): string {
    $width = max(0, min(100, $value));
    return '<div class="progress kriamos-progress" role="progressbar" aria-valuenow="' . e((string)$width) . '" aria-valuemin="0" aria-valuemax="100">'
        . '<div class="progress-bar ' . e($barClass($value, $targetValue)) . '" style="width:' . e((string)$width) . '%">' . e($percent($value)) . '%</div>'
        . '</div>';
};
?>
<section class="dashboard-hero py-5">
    <div class="container py-2">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="dashboard-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div>
                        <h1 class="h2 fw-bold text-white mb-1">Dashboard gerencial de capacitación</h1>
                        <p class="text-white-50 mb-0">Cumplimiento, completitud, aprobación, tiempo dedicado y adquisición de habilidades.</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill text-bg-light text-kriamos-blue px-3 py-2">Meta empleados: <?= e($percent($target)) ?>%</span>
                    <span class="badge rounded-pill <?= e($badgeClass((float)($overview['employee_coverage_percent'] ?? 0), $target)) ?> px-3 py-2">
                        Cumplimiento actual: <?= e($percent($overview['employee_coverage_percent'] ?? 0)) ?>%
                    </span>
                    <span class="badge rounded-pill text-bg-warning px-3 py-2">Brecha: <?= e($percent($overview['employee_gap_percent'] ?? 0)) ?>%</span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-light fw-bold me-2 mb-2" href="<?= e(current_url('dashboard_export', $filters ?? [])) ?>"><i class="fa-solid fa-file-csv me-2"></i>Exportar CSV</a>
                <a class="btn btn-kriamos-orange mb-2" href="<?= e(current_url('admin_calendario')) ?>"><i class="fa-solid fa-calendar-days me-2"></i>Ver calendario</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
            <div class="alert alert-warning mb-4">
                Verifique que la migración <code>006_create_dashboard_metrics.sql</code> ya fue ejecutada después de las fases anteriores.
            </div>
        <?php endif; ?>

        <div class="card card-kriamos mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="index.php">
                    <input type="hidden" name="page" value="dashboard">
                    <div class="col-md-2">
                        <label class="form-label">Sitio</label>
                        <select class="form-select" name="work_site">
                            <option value="">Todos</option>
                            <?php foreach (['GAEE','GALM','Oficina'] as $site): ?>
                                <option value="<?= e($site) ?>" <?= (($filters['work_site'] ?? '') === $site) ? 'selected' : '' ?>><?= e($site) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="person_type">
                            <option value="">Todos</option>
                            <option value="empleado" <?= (($filters['person_type'] ?? '') === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                            <option value="contratista" <?= (($filters['person_type'] ?? '') === 'contratista') ? 'selected' : '' ?>>Contratista</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Equipo</label>
                        <select class="form-select" name="department">
                            <option value="">Todos</option>
                            <?php foreach (['Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos'] as $dept): ?>
                                <option value="<?= e($dept) ?>" <?= (($filters['department'] ?? '') === $dept) ? 'selected' : '' ?>><?= e($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input class="form-control" type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input class="form-control" type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-primary w-100" type="submit" title="Filtrar"><i class="fa-solid fa-filter"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Cumplimiento empleados</div>
                        <div class="display-6 fw-bold text-kriamos-blue mb-2"><?= e($percent($overview['employee_coverage_percent'] ?? 0)) ?>%</div>
                        <?= $progress((float)($overview['employee_coverage_percent'] ?? 0), $target) ?>
                        <div class="small text-muted mt-2"><?= e($int($overview['employee_certified'] ?? 0)) ?> de <?= e($int($overview['employee_total'] ?? 0)) ?> empleados con certificado vigente.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Completitud por módulos asignados</div>
                        <div class="display-6 fw-bold text-kriamos-blue mb-2"><?= e($percent($completion['completion_percent'] ?? 0)) ?>%</div>
                        <?= $progress((float)($completion['completion_percent'] ?? 0), 95) ?>
                        <div class="small text-muted mt-2"><?= e($int($completion['completed_total'] ?? 0)) ?> aprobados de <?= e($int($completion['assigned_total'] ?? 0)) ?> asignaciones.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Tasa de aprobación</div>
                        <div class="display-6 fw-bold text-kriamos-blue mb-2"><?= e($percent($overview['approval_rate_percent'] ?? 0)) ?>%</div>
                        <?= $progress((float)($overview['approval_rate_percent'] ?? 0), 80) ?>
                        <div class="small text-muted mt-2">Promedio general: <?= e($percent($overview['avg_score'] ?? 0)) ?>% · Intentos: <?= e($int($overview['attempts_total'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Tiempo dedicado</div>
                        <div class="display-6 fw-bold text-kriamos-blue mb-2"><?= e($percent($time['total_hours'] ?? 0)) ?> h</div>
                        <div class="small text-muted mt-2">Promedio por examen finalizado: <?= e($percent($time['avg_attempt_minutes'] ?? 0)) ?> min.</div>
                        <div class="small text-muted">Eventos de estudio registrados: <?= e($int($time['activity_events'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Personas activas</div><div class="h2 fw-bold text-kriamos-blue mb-0"><?= e($int($overview['people_total'] ?? 0)) ?></div><div class="small text-muted">Empleados: <?= e($int($overview['employee_total'] ?? 0)) ?> · Contratistas: <?= e($int($overview['contractor_total'] ?? 0)) ?></div></div></div></div>
            <div class="col-md-4"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Pendientes por completar</div><div class="h2 fw-bold text-kriamos-orange mb-0"><?= e($int($completion['pending_total'] ?? 0)) ?></div><div class="small text-muted">Módulos asignados sin certificado vigente.</div></div></div></div>
            <div class="col-md-4"><div class="card card-kriamos h-100"><div class="card-body"><div class="small text-muted">Estado frente a meta 95%</div><div class="h2 fw-bold <?= !empty($overview['goal_reached']) ? 'text-success' : 'text-danger' ?> mb-0"><?= !empty($overview['goal_reached']) ? 'Cumplida' : 'Pendiente' ?></div><div class="small text-muted">Brecha actual: <?= e($percent($overview['employee_gap_percent'] ?? 0)) ?> puntos porcentuales.</div></div></div></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 fw-bold text-kriamos-blue mb-0">Cumplimiento por granja / sede</h2></div>
                    <div class="card-body">
                        <?php if (empty($dashboard['by_site'])): ?>
                            <p class="text-muted mb-0">No hay datos para mostrar.</p>
                        <?php else: ?>
                            <?php foreach ($dashboard['by_site'] as $row): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong><?= e($row['work_site']) ?></strong>
                                        <span class="badge <?= e($badgeClass((float)$row['coverage_percent'], 95)) ?>"><?= e($percent($row['coverage_percent'])) ?>%</span>
                                    </div>
                                    <?= $progress((float)$row['coverage_percent'], 95) ?>
                                    <div class="small text-muted mt-1"><?= e($int($row['people_certified'])) ?> de <?= e($int($row['people_total'])) ?> personas con certificado vigente · Completitud módulos: <?= e($percent($row['completion_percent'])) ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 fw-bold text-kriamos-blue mb-0">Cumplimiento por equipo</h2></div>
                    <div class="card-body">
                        <?php if (empty($dashboard['by_department'])): ?>
                            <p class="text-muted mb-0">No hay datos para mostrar.</p>
                        <?php else: ?>
                            <?php foreach ($dashboard['by_department'] as $row): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong><?= e($row['department']) ?></strong>
                                        <span class="badge <?= e($badgeClass((float)$row['coverage_percent'], 95)) ?>"><?= e($percent($row['coverage_percent'])) ?>%</span>
                                    </div>
                                    <?= $progress((float)$row['coverage_percent'], 95) ?>
                                    <div class="small text-muted mt-1"><?= e($int($row['people_certified'])) ?> de <?= e($int($row['people_total'])) ?> personas · Pendientes módulo: <?= e($int($row['pending_total'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0">Adquisición de habilidades</h2>
                        <p class="small text-muted mb-0">Calculada con base en módulos asignados, certificados vigentes y puntajes de evaluación.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Habilidad</th><th>Avance</th><th>Promedio</th><th>Pendientes</th></tr></thead>
                                <tbody>
                                <?php if (empty($dashboard['skills'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No hay habilidades calculadas.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($dashboard['skills'] as $row): ?>
                                        <tr>
                                            <td><strong><?= e($row['skill_name']) ?></strong><br><span class="small text-muted"><?= e($int($row['completed_total'])) ?> de <?= e($int($row['assigned_total'])) ?> módulos certificados</span></td>
                                            <td style="min-width: 180px"><?= $progress((float)$row['acquisition_percent'], 95) ?></td>
                                            <td><span class="badge text-bg-light border"><?= e($percent($row['avg_score'])) ?>%</span></td>
                                            <td><?= e($int($row['pending_total'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0">Aprobación por granja</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboard['approval_by_site'])): ?>
                            <p class="text-muted mb-0">No hay intentos finalizados para el periodo.</p>
                        <?php else: ?>
                            <?php foreach ($dashboard['approval_by_site'] as $row): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between"><strong><?= e($row['work_site']) ?></strong><span><?= e($percent($row['approval_rate_percent'])) ?>%</span></div>
                                    <?= $progress((float)$row['approval_rate_percent'], 80) ?>
                                    <div class="small text-muted mt-1">Aprobados: <?= e($int($row['passed_total'])) ?> · Reprobados: <?= e($int($row['failed_total'])) ?> · Promedio: <?= e($percent($row['avg_score'])) ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 fw-bold text-kriamos-blue mb-0">Cursos con mayor necesidad de seguimiento</h2></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Curso</th><th>Completitud</th><th>Pendientes</th><th>Promedio</th></tr></thead>
                                <tbody>
                                <?php if (empty($dashboard['courses'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No hay cursos asignados para los filtros seleccionados.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($dashboard['courses'] as $row): ?>
                                        <tr>
                                            <td><strong><?= e($row['course_code']) ?></strong><br><?= e($row['course_title']) ?></td>
                                            <td style="min-width: 190px"><?= $progress((float)$row['completion_percent'], 95) ?></td>
                                            <td><?= e($int($row['pending_total'])) ?></td>
                                            <td><?= e($percent($row['avg_score'])) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card card-kriamos h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 fw-bold text-kriamos-blue mb-0">Personas con capacitación pendiente</h2></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Persona</th><th>Área</th><th>Pend.</th><th>%</th></tr></thead>
                                <tbody>
                                <?php if (empty($dashboard['pending_people'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No hay pendientes para los filtros seleccionados.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($dashboard['pending_people'] as $row): ?>
                                        <tr>
                                            <td><strong><?= e($row['full_name']) ?></strong><br><span class="small text-muted">CC <?= e($row['document_number']) ?> · <?= e(ucfirst($row['person_type'])) ?></span></td>
                                            <td class="small"><?= e($row['work_site']) ?><br><?= e($row['department']) ?></td>
                                            <td><span class="badge text-bg-danger"><?= e($int($row['pending_total'])) ?></span></td>
                                            <td><?= e($percent($row['completion_percent'])) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
