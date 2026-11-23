<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Estructura de base de datos</h1>
            <p class="text-muted mb-0">Tablas principales desarrolladas en la Fase 02.</p>
        </div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>Propósito</th>
                            <th>Fase de uso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>roles</td><td>Define perfiles funcionales del sistema.</td><td>Fase 03</td></tr>
                        <tr><td>permisos</td><td>Define acciones autorizadas por módulo.</td><td>Fase 03</td></tr>
                        <tr><td>rol_permiso</td><td>Relaciona roles con permisos.</td><td>Fase 03</td></tr>
                        <tr><td>usuarios</td><td>Personal propio de Kriamos autorizado para ingresar.</td><td>Fase 03</td></tr>
                        <tr><td>granjas</td><td>GALM, GAEE y futuras granjas.</td><td>Fase 04</td></tr>
                        <tr><td>galpones</td><td>Galpones asociados a cada granja.</td><td>Fase 04</td></tr>
                        <tr><td>ciclos_productivos</td><td>Ciclos por granja y galpón.</td><td>Fase 04</td></tr>
                        <tr><td>fases_ciclo</td><td>Catálogo de fases productivas.</td><td>Fase 04</td></tr>
                        <tr><td>tipos_formato</td><td>Formatos requeridos por ICA 3652 y POE Kriamos.</td><td>Fase 05</td></tr>
                        <tr><td>formatos_registros</td><td>Registros diligenciados digitalmente.</td><td>Fase 05</td></tr>
                        <tr><td>firmas_digitales</td><td>Firmas asociadas a registros.</td><td>Fase 06</td></tr>
                        <tr><td>certificaciones_granja</td><td>Vigencia de certificaciones ICA por granja.</td><td>Fase 07</td></tr>
                        <tr><td>alertas</td><td>Alertas por atraso, vencimiento o corrección.</td><td>Fase 07</td></tr>
                        <tr><td>documentos</td><td>Núcleo documental de la plataforma.</td><td>Fase 09</td></tr>
                        <tr><td>evidencias</td><td>Fotos, videos y soportes asociados.</td><td>Fase 09</td></tr>
                        <tr><td>auditoria</td><td>Trazabilidad completa de acciones del usuario.</td><td>Transversal</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
