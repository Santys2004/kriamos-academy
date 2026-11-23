<?php
$tipo = $tipo ?? ($_GET['tipo'] ?? 'empleado');
$old = $old ?? [];
$errors = $errors ?? [];
$isEmployee = ($old['person_type'] ?? $tipo) === 'empleado';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-kriamos-blue text-white p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h3 class="mb-1"><i class="fa-solid fa-clipboard-list me-2"></i>Planilla de registro de capacitación</h3>
                            <p class="mb-0 opacity-75">Fase 1: identificación de empleados y contratistas.</p>
                        </div>
                        <span class="badge bg-kriamos-orange fs-6">KRIAMOS</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-warning">Revise los campos marcados antes de continuar.</div>
                    <?php endif; ?>

                    <form action="<?= e(current_url('guardar_registro')) ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tipo de registro</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input class="btn-check" type="radio" name="person_type" id="tipoEmpleado" value="empleado" <?= $isEmployee ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-primary w-100 py-3" for="tipoEmpleado"><i class="fa-solid fa-user-tie me-2"></i>Empleado KRIAMOS</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input class="btn-check" type="radio" name="person_type" id="tipoContratista" value="contratista" <?= !$isEmployee ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-secondary w-100 py-3" for="tipoContratista"><i class="fa-solid fa-helmet-safety me-2"></i>Contratista</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" value="<?= e($old['full_name'] ?? '') ?>" required>
                                <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Campo obligatorio') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cédula *</label>
                                <input type="number" name="document_number" class="form-control <?= isset($errors['document_number']) ? 'is-invalid' : '' ?>" value="<?= e($old['document_number'] ?? '') ?>" required>
                                <div class="invalid-feedback"><?= e($errors['document_number'] ?? 'Campo obligatorio') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lugar de residencia *</label>
                                <input type="text" name="residence" class="form-control <?= isset($errors['residence']) ? 'is-invalid' : '' ?>" value="<?= e($old['residence'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sitio de trabajo *</label>
                                <select name="work_site" class="form-select <?= isset($errors['work_site']) ? 'is-invalid' : '' ?>" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach (['GAEE','GALM','Oficina'] as $site): ?>
                                        <option value="<?= e($site) ?>" <?= (($old['work_site'] ?? '') === $site) ? 'selected' : '' ?>><?= e($site) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono celular *</label>
                                <input type="tel" name="cellphone" class="form-control <?= isset($errors['cellphone']) ? 'is-invalid' : '' ?>" value="<?= e($old['cellphone'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico *</label>
                                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e($old['email'] ?? '') ?>" required>
                                <div class="invalid-feedback"><?= e($errors['email'] ?? 'Correo no válido') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Empresa *</label>
                                <input type="text" name="company" class="form-control <?= isset($errors['company']) ? 'is-invalid' : '' ?>" value="<?= e($old['company'] ?? ($isEmployee ? 'KRIAMOS S.A.S.' : '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cargo *</label>
                                <input type="text" name="position" class="form-control <?= isset($errors['position']) ? 'is-invalid' : '' ?>" value="<?= e($old['position'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 employee-field">
                                <label class="form-label">Equipo del empleado *</label>
                                <select name="department" class="form-select <?= isset($errors['department']) ? 'is-invalid' : '' ?>">
                                    <option value="">Seleccione...</option>
                                    <?php foreach (['Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos'] as $department): ?>
                                        <option value="<?= e($department) ?>" <?= (($old['department'] ?? '') === $department) ? 'selected' : '' ?>><?= e($department) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registro fotográfico</label>
                                <input type="file" name="photo" class="form-control" accept="image/*" capture="user">
                                <div class="form-text">Permite tomar foto desde el celular o cargar una imagen. Máximo 3 MB.</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-kriamos-orange btn-lg"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar registro</button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
