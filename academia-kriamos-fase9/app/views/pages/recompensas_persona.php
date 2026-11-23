<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card card-kriamos mb-4">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-kriamos-orange mb-2">Empleados KRIAMOS</span>
                    <h1 class="h3 fw-bold text-kriamos-blue">Consulta de recompensas</h1>
                    <p class="text-muted">Ingrese la cédula para consultar recompensas causadas por evaluaciones con 90% o 100%. Las recompensas aplican únicamente para empleados.</p>
                    <form action="<?= e(current_url('mis_recompensas_resultado')) ?>" method="post" class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Cédula</label>
                            <input type="text" name="document_number" value="<?= e($document ?? '') ?>" class="form-control form-control-lg" placeholder="Número de cédula" required>
                        </div>
                        <div class="col-md-4 d-grid align-items-end">
                            <button class="btn btn-kriamos-orange btn-lg mt-md-4" type="submit"><i class="fa-solid fa-gift me-2"></i>Consultar</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning"><i class="fa-solid fa-circle-info me-2"></i><?= e($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($rewards)): ?>
                <div class="card card-kriamos">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0">Recompensas encontradas</h2>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rewards as $reward): ?>
                            <div class="border rounded-4 p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <h3 class="h6 fw-bold text-kriamos-blue mb-1"><?= e($reward['course_title']) ?></h3>
                                        <div class="small text-muted"><?= e($reward['module_title']) ?> · <?= e(human_datetime($reward['earned_at'])) ?></div>
                                    </div>
                                    <span class="badge <?= e($reward['status_badge']) ?> align-self-start"><?= e($reward['status_label']) ?></span>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4"><div class="small text-muted">Nota</div><div class="fw-bold"><?= e((string)$reward['score']) ?>%</div></div>
                                    <div class="col-md-4"><div class="small text-muted">Recompensa</div><div class="fw-bold text-kriamos-orange"><?= (int)$reward['frozen_chickens_qty'] ?> pollo(s) congelado(s)</div></div>
                                    <div class="col-md-4"><div class="small text-muted">Entrega</div><div class="fw-bold"><?= !empty($reward['delivered_at']) ? e(human_datetime($reward['delivered_at'])) : 'Pendiente' ?></div></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
