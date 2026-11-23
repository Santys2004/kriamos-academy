<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-kriamos">
                    <div class="card-header bg-kriamos-blue text-white">
                        <h1 class="h4 mb-0"><i class="fa-solid fa-user-check me-2"></i>Asignar curso por perfil</h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-light border">
                            Curso: <strong><?= e($course['code'] ?? '') ?> — <?= e($course['title'] ?? '') ?></strong>
                        </div>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form action="<?= e(current_url('guardar_asignacion')) ?>" method="post" class="row g-3">
                            <input type="hidden" name="course_id" value="<?= e((string)($course['id'] ?? '')) ?>">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo de persona</label>
                                <?php $personType = $old['person_type'] ?? 'todos'; ?>
                                <select name="person_type" class="form-select">
                                    <option value="todos" <?= $personType === 'todos' ? 'selected' : '' ?>>Todos</option>
                                    <option value="empleado" <?= $personType === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                                    <option value="contratista" <?= $personType === 'contratista' ? 'selected' : '' ?>>Contratista</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sitio de trabajo</label>
                                <?php $site = $old['work_site'] ?? 'todos'; ?>
                                <select name="work_site" class="form-select">
                                    <option value="todos" <?= $site === 'todos' ? 'selected' : '' ?>>Todos</option>
                                    <option value="GAEE" <?= $site === 'GAEE' ? 'selected' : '' ?>>GAEE</option>
                                    <option value="GALM" <?= $site === 'GALM' ? 'selected' : '' ?>>GALM</option>
                                    <option value="Oficina" <?= $site === 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Equipo del empleado</label>
                                <?php $department = $old['department'] ?? 'todos'; ?>
                                <select name="department" class="form-select">
                                    <option value="todos" <?= $department === 'todos' ? 'selected' : '' ?>>Todos / No aplica</option>
                                    <option value="Operaciones" <?= $department === 'Operaciones' ? 'selected' : '' ?>>Operaciones</option>
                                    <option value="Mantenimiento" <?= $department === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                                    <option value="Compras" <?= $department === 'Compras' ? 'selected' : '' ?>>Compras</option>
                                    <option value="Gestión Humana" <?= $department === 'Gestión Humana' ? 'selected' : '' ?>>Gestión Humana</option>
                                    <option value="Administrativos" <?= $department === 'Administrativos' ? 'selected' : '' ?>>Administrativos</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Palabra clave del cargo</label>
                                <input type="text" name="position_keyword" class="form-control" value="<?= e($old['position_keyword'] ?? '') ?>" placeholder="Ejemplo: galponero, administrador, electricista, conductor">
                                <div class="form-text">Campo opcional. Permite orientar asignaciones por cargo. La lógica automática fina se implementará en fases posteriores.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="active" id="activeAssignment" value="1" <?= ($old['active'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="activeAssignment">Asignación activa</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-between pt-3">
                                <a href="<?= e(current_url('curso_detalle', ['id' => (string)($course['id'] ?? 0)])) ?>" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-kriamos-orange">
                                    <i class="fa-solid fa-link me-2"></i>Guardar asignación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
