<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-kriamos-blue text-white p-4">
                    <h3 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Verificar registro por cédula</h3>
                </div>
                <div class="card-body p-4">
                    <form action="<?= e(current_url('verificar_resultado')) ?>" method="post" class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Cédula</label>
                            <input type="number" name="document_number" class="form-control form-control-lg" value="<?= e($document ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-kriamos-orange btn-lg w-100" type="submit">Verificar</button>
                        </div>
                    </form>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-warning mt-4"><?= e($error) ?></div>
                    <?php endif; ?>

                    <?php if (isset($person) && $person): ?>
                        <div class="alert alert-success mt-4">
                            <h5 class="fw-bold mb-2"><i class="fa-solid fa-circle-check me-2"></i>Persona registrada</h5>
                            <strong>Nombre:</strong> <?= e($person['full_name']) ?><br>
                            <strong>Tipo:</strong> <?= e(ucfirst($person['person_type'])) ?><br>
                            <strong>Sitio:</strong> <?= e($person['work_site']) ?><br>
                            <strong>Empresa:</strong> <?= e($person['company']) ?><br>
                            <strong>Cargo:</strong> <?= e($person['position']) ?>
                        </div>
                    <?php elseif (isset($person) && $person === null && empty($error) && !empty($document)): ?>
                        <div class="alert alert-danger mt-4">
                            <h5 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>No encontrado</h5>
                            No existe registro para la cédula consultada.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
