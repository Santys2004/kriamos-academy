<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <span class="badge bg-kriamos-orange mb-2">Fase 2</span>
                <h1 class="h3 fw-bold text-kriamos-blue mb-1">Administración de cursos</h1>
                <p class="text-muted mb-0">Cree cursos, módulos, materiales y reglas de asignación para empleados y contratistas.</p>
            </div>
            <a href="<?= e(current_url('crear_curso')) ?>" class="btn btn-kriamos-orange">
                <i class="fa-solid fa-plus me-2"></i>Crear curso
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card card-kriamos h-100">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-book"></i></div>
                        <h2 class="h6 fw-bold">Cursos</h2>
                        <p class="text-muted small mb-0">Agrupan los módulos de capacitación por tema: bioseguridad, bienestar animal, mantenimiento y otros.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kriamos h-100">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-layer-group"></i></div>
                        <h2 class="h6 fw-bold">Módulos</h2>
                        <p class="text-muted small mb-0">Cada módulo organiza una parte del curso y será la base del examen en la fase 3.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kriamos h-100">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-file-arrow-up"></i></div>
                        <h2 class="h6 fw-bold">Materiales</h2>
                        <p class="text-muted small mb-0">Permite cargar manuales, videos, podcast, PDF, Word y PowerPoint.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-kriamos">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0 fw-bold text-kriamos-blue">Listado de cursos</h2>
                <span class="badge bg-kriamos-blue"><?= count($courses ?? []) ?> curso(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($courses)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-circle-info me-2"></i>No hay cursos creados todavía.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Curso</th>
                                <th>Público</th>
                                <th>Estado</th>
                                <th class="text-center">Módulos</th>
                                <th class="text-center">Materiales</th>
                                <th class="text-center">Asignaciones</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><span class="badge text-bg-light border"><?= e($course['code']) ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= e($course['title']) ?></div>
                                        <div class="small text-muted"><?= e(mb_strimwidth($course['description'] ?? '', 0, 90, '...')) ?></div>
                                    </td>
                                    <td><?= e(ucfirst($course['target_audience'])) ?></td>
                                    <td>
                                        <span class="badge <?= $course['status'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            <?= e(ucfirst($course['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= (int)$course['modules_count'] ?></td>
                                    <td class="text-center"><?= (int)$course['materials_count'] ?></td>
                                    <td class="text-center"><?= (int)$course['assignments_count'] ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(current_url('curso_detalle', ['id' => (string)$course['id']])) ?>">
                                            Gestionar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
