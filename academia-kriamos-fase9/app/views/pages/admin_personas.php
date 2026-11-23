<section class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-kriamos-blue mb-1">Personas registradas</h1>
            <p class="text-muted mb-0">Panel básico de consulta de la Fase 1.</p>
        </div>
        <a href="<?= e(current_url('registro', ['tipo' => 'empleado'])) ?>" class="btn btn-kriamos-orange"><i class="fa-solid fa-plus me-2"></i>Nuevo registro</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">No se pudo consultar la base de datos: <?= e($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Tipo</th>
                            <th>Sitio</th>
                            <th>Empresa</th>
                            <th>Cargo</th>
                            <th>Equipo</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($people)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Aún no hay personas registradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($people as $person): ?>
                        <tr>
                            <td>
                                <?php if (!empty($person['photo_path'])): ?>
                                    <img src="<?= e($person['photo_path']) ?>" alt="Foto" class="avatar-photo">
                                <?php else: ?>
                                    <span class="avatar-empty"><i class="fa-solid fa-user"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($person['full_name']) ?></td>
                            <td><?= e($person['document_number']) ?></td>
                            <td><span class="badge <?= $person['person_type'] === 'empleado' ? 'bg-primary' : 'bg-secondary' ?>"><?= e(ucfirst($person['person_type'])) ?></span></td>
                            <td><?= e($person['work_site']) ?></td>
                            <td><?= e($person['company']) ?></td>
                            <td><?= e($person['position']) ?></td>
                            <td><?= e($person['department'] ?? '-') ?></td>
                            <td><?= e($person['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
