<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Gestión de usuario corporativo.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/admin/usuarios" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Nombres</label><input type="text" name="nombres" class="form-control" required value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Apellidos</label><input type="text" name="apellidos" class="form-control" required value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Correo corporativo</label><input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int) $rol['id'] ?>" <?= isset($usuario['rol_id']) && (int)$usuario['rol_id'] === (int)$rol['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Cargo</label><input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <?php $estado = $usuario['estado'] ?? 'activo'; ?>
                    <select name="estado" class="form-select">
                        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="bloqueado" <?= $estado === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>

                <?php if (empty($usuario)): ?>
                    <div class="col-md-4"><label class="form-label">Contraseña temporal</label><input type="text" name="password" class="form-control" value="Kriamos2026*"></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/admin/usuarios" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-kriamos" type="submit">Guardar</button>
        </div>
    </form>

    <?php if (!empty($usuario)): ?>
        <form method="post" action="<?= BASE_PATH ?>/admin/usuarios/reset-password" class="card card-kriamos mt-4">
            <?= Csrf::input() ?>
            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
            <div class="card-body">
                <h2 class="h5 text-kriamos">Restablecer contraseña</h2>
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" name="password" class="form-control" value="Kriamos2026*"></div>
                    <div class="col-md-6"><button class="btn btn-outline-kriamos" type="submit">Restablecer contraseña temporal</button></div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
