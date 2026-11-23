<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <div class="success-circle mx-auto mb-3"><i class="fa-solid fa-check"></i></div>
                    <h2 class="fw-bold text-kriamos-blue">Registro guardado</h2>
                    <p class="text-muted">La persona quedó registrada en la base inicial de Academia KRIAMOS.</p>
                    <div class="alert alert-light border text-start mt-4">
                        <strong>Nombre:</strong> <?= e($person['full_name']) ?><br>
                        <strong>Cédula:</strong> <?= e($person['document_number']) ?><br>
                        <strong>Tipo:</strong> <?= e(ucfirst($person['person_type'])) ?><br>
                        <strong>Sitio:</strong> <?= e($person['work_site']) ?>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="<?= e(current_url('registro', ['tipo' => $person['person_type']])) ?>" class="btn btn-kriamos-orange">Nuevo registro</a>
                        <a href="<?= e(current_url('admin_personas')) ?>" class="btn btn-outline-primary">Ver panel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
