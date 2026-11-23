<?php
$statusClass = static function (string $status): string {
    return match ($status) {
        'vigente' => 'text-bg-success',
        'por_vencer' => 'text-bg-warning',
        'vencido', 'reactivado' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
};
$statusText = static function (string $status): string {
    return match ($status) {
        'vigente' => 'Vigente',
        'por_vencer' => 'Próximo a vencer',
        'vencido' => 'Vencido',
        'reactivado' => 'Reactivado para nuevo ciclo',
        default => ucfirst($status),
    };
};
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-kriamos mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 fw-bold text-kriamos-blue mb-2"><i class="fa-solid fa-calendar-check me-2 text-kriamos-orange"></i>Mi calendario de capacitación</h1>
                        <p class="text-muted">Consulta la fecha en que se activa nuevamente cada módulo aprobado. La recertificación se programa cada seis meses.</p>
                        <form method="post" action="<?= e(current_url('mi_calendario_resultado')) ?>" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Cédula</label>
                                <input type="text" name="document_number" class="form-control form-control-lg" value="<?= e($document ?? '') ?>" placeholder="Ingrese su número de cédula" required>
                            </div>
                            <div class="col-md-4 d-grid align-items-end">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button class="btn btn-kriamos-orange btn-lg" type="submit"><i class="fa-solid fa-search me-2"></i>Consultar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-warning"><i class="fa-solid fa-circle-info me-2"></i><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($items)): ?>
                    <div class="card card-kriamos">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Curso / módulo</th>
                                            <th>Certificación</th>
                                            <th>Próximo ciclo</th>
                                            <th>Alertas</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= e($item['course_code']) ?></strong> <?= e($item['course_title']) ?><br>
                                                    <span class="small text-muted"><?= e($item['module_title']) ?></span>
                                                </td>
                                                <td class="small">
                                                    Certificado: <span class="badge bg-kriamos-blue"><?= e($item['certificate_code']) ?></span><br>
                                                    Aprobado: <?= e(human_datetime($item['last_passed_at'])) ?>
                                                </td>
                                                <td>
                                                    <strong><?= e(human_datetime($item['next_due_at'])) ?></strong><br>
                                                    <a class="small" href="<?= e(current_url('ver_certificado', ['token' => $item['verification_token']])) ?>">Ver certificado</a>
                                                </td>
                                                <td class="small">
                                                    5 días antes: <?= e(human_datetime($item['reminder_5d_at'])) ?><br>
                                                    1 día antes: <?= e(human_datetime($item['reminder_1d_at'])) ?>
                                                </td>
                                                <td><span class="badge <?= e($statusClass($item['status'])) ?>"><?= e($statusText($item['status'])) ?></span></td>
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
