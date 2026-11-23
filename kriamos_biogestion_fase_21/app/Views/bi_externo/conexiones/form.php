<?php
use App\Services\Csrf;
require dirname(dirname(__DIR__)) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Registre una herramienta BI externa autorizada.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/bi-externo" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="col-md-4">
                    <label class="form-label">Herramienta</label>
                    <select name="herramienta" class="form-select">
                        <option value="power_bi">Power BI</option>
                        <option value="metabase">Metabase</option>
                        <option value="looker_studio">Looker Studio</option>
                        <option value="superset">Apache Superset</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Correo contacto</label><input type="email" name="contacto_email" class="form-control"></div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-8"><label class="form-label">IP permitidas</label><input type="text" name="ip_permitidas" class="form-control" placeholder="Separadas por coma. Vacío = sin restricción"></div>
                <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="4"></textarea></div>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-kriamos" type="submit">Crear conexión y token</button>
        </div>
    </form>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
