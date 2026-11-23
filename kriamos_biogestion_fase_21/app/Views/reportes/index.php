<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Reportes, impresión y exportación</h1>
            <p class="text-muted mb-0">Consolidación documental para control interno y soporte ante ICA.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/reportes/formatos" class="module-card h-100"><i class="fa-solid fa-file-signature"></i><span>Reporte de formatos</span></a></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/reportes/alertas" class="module-card h-100"><i class="fa-solid fa-bell"></i><span>Reporte de alertas</span></a></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/reportes/certificaciones" class="module-card h-100"><i class="fa-solid fa-certificate"></i><span>Certificaciones</span></a></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/reportes/incidencias" class="module-card h-100"><i class="fa-solid fa-shield-halved"></i><span>Incidencias de calidad</span></a></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/reportes/paquete-ica" class="module-card h-100"><i class="fa-solid fa-file-zipper"></i><span>Paquete documental ICA</span></a></div>
        <div class="col-md-4"><button onclick="window.print()" class="module-card h-100 w-100 text-start border-0"><i class="fa-solid fa-print"></i><span>Imprimir panel</span></button></div>
    </div>

    <div class="alert alert-info mt-4">
        Los reportes conservan trazabilidad de usuario, fecha y filtros. La exportación PDF formal se puede integrar posteriormente con una librería dedicada.
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
