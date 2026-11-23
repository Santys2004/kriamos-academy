<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Editar rol</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($rol['nombre']) ?></p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/admin/roles" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= BASE_PATH ?>/admin/roles/update?id=<?= (int) $rol['id'] ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($rol['nombre']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Descripción</label><input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($rol['descripcion'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Activo</label><div class="form-check mt-2"><input type="checkbox" name="activo" class="form-check-input" <?= (int)$rol['activo'] === 1 ? 'checked' : '' ?>></div></div>
            </div>

            <h2 class="h5 text-kriamos">Permisos</h2>
            <div class="row g-2">
                <?php foreach ($permisos as $permiso): ?>
                    <div class="col-md-4">
                        <label class="border rounded p-2 d-block">
                            <input type="checkbox" name="permisos[]" value="<?= (int) $permiso['id'] ?>" <?= (int) $permiso['asignado'] === 1 ? 'checked' : '' ?>>
                            <strong><?= htmlspecialchars($permiso['codigo']) ?></strong><br>
                            <small><?= htmlspecialchars($permiso['descripcion'] ?? '') ?></small>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="<?= BASE_PATH ?>/admin/roles" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-kriamos" type="submit">Guardar cambios</button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
