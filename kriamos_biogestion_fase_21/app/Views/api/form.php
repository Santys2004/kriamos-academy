<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Configure el cliente autorizado para consumir la API.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/api-admin" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nombre del cliente</label><input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($client['nombre'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Correo contacto</label><input type="email" name="contacto_email" class="form-control" value="<?= htmlspecialchars($client['contacto_email'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">IP permitidas</label><input type="text" name="ip_permitidas" class="form-control" placeholder="Separadas por coma. Vacío = sin restricción" value="<?= htmlspecialchars($client['ip_permitidas'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="4"><?= htmlspecialchars($client['descripcion'] ?? '') ?></textarea></div>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-kriamos" type="submit">Guardar cliente</button>
        </div>
    </form>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
