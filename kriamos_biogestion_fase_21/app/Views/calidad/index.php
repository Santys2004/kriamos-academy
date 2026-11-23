<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Control de calidad de información</h1>
            <p class="text-muted mb-0">Validaciones, incidencias, duplicados, errores y revisión básica de datos.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-triangle-exclamation metric-icon"></i>
                <span class="metric-label">Incidencias abiertas</span>
                <strong><?= (int) $incidenciasAbiertas ?></strong>
                <small>Registros que requieren revisión.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-copy metric-icon"></i>
                <span class="metric-label">Duplicados</span>
                <strong>Activo</strong>
                <small>Validación antes de guardar.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <i class="fa-solid fa-signature metric-icon"></i>
                <span class="metric-label">Firma digital</span>
                <strong>Activa</strong>
                <small>Firma interna por usuario autenticado.</small>
            </div>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <h2 class="h5 fw-bold text-kriamos">Validaciones implementadas</h2>
            <ul>
                <li>Bloqueo de copiar, pegar y cortar en observaciones, hallazgos, acciones correctivas y justificación de No aplica.</li>
                <li>Validación de registros duplicados por tipo de formato, granja, galpón, ciclo y fecha.</li>
                <li>Validación de fechas futuras en registros.</li>
                <li>Validación de números negativos.</li>
                <li>Advertencia de productos vencidos cuando el campo fecha de vencimiento sea menor a la fecha actual.</li>
                <li>Validación de mortalidad contra aves actuales del ciclo.</li>
                <li>Advertencia sanitaria cuando la mortalidad diaria sea igual o superior al 1% de aves actuales.</li>
                <li>Revisión básica de errores de digitación en campos de texto.</li>
            </ul>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
