<?php
use App\Services\Csrf;
require dirname(dirname(__DIR__)) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Crear expediente ICA</h1>
            <p class="text-muted mb-0">Cree un expediente de auditoría por granja y periodo.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/auditoria-ica" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <form method="post" action="<?= BASE_PATH ?>/auditoria-ica/store" class="card card-kriamos">
        <?= Csrf::input() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nombre expediente</label><input type="text" name="nombre" class="form-control" required placeholder="Ej: Preparación auditoría ICA GALM ciclo 46"></div>
                <div class="col-md-4">
                    <label class="form-label">Granja</label>
                    <select name="granja_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($granjas as $g): ?>
                            <option value="<?= (int) $g['id'] ?>"><?= htmlspecialchars($g['sigla'] . ' - ' . $g['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-2"><label class="form-label">Fecha fin</label><input type="date" name="fecha_fin" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Objetivo</label><textarea name="objetivo" class="form-control" rows="4"></textarea></div>
                <div class="col-md-6"><label class="form-label">Alcance</label><textarea name="alcance" class="form-control" rows="4"></textarea></div>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-kriamos" type="submit">Crear expediente</button>
        </div>
    </form>
</section>

<?php require dirname(dirname(__DIR__)) . '/layouts/footer.php'; ?>
