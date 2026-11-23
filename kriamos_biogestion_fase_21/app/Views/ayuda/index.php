<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Centro de ayuda</h1>
            <p class="text-muted mb-0">Manual, guías de llenado y soporte interno para usuarios de Kriamos BioGestión ICA.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/ayuda/manual" class="btn btn-outline-kriamos"><i class="fa-solid fa-book-open me-2"></i> Manual interactivo</a>
            <a href="<?= BASE_PATH ?>/ayuda/chat" class="btn btn-kriamos"><i class="fa-solid fa-comments me-2"></i> Chat de ayuda</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card"><i class="fa-solid fa-book metric-icon"></i><span class="metric-label">Artículos</span><strong><?= (int) $totalArticulos ?></strong><small>Base de conocimiento local.</small></div></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/ayuda/manual" class="module-card h-100"><i class="fa-solid fa-list-check"></i><span>Manual paso a paso</span></a></div>
        <div class="col-md-4"><a href="<?= BASE_PATH ?>/ayuda/chat" class="module-card h-100"><i class="fa-solid fa-headset"></i><span>Asistente interno</span></a></div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6"><label class="form-label">Buscar en ayuda</label><input type="text" name="q" class="form-control" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Ejemplo: firmar formato, cargar documento, sincronizar"></div>
                <div class="col-md-4">
                    <label class="form-label">Módulo</label>
                    <select name="modulo" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($modulos as $modulo): ?>
                            <option value="<?= htmlspecialchars($modulo) ?>" <?= ($_GET['modulo'] ?? '') === $modulo ? 'selected' : '' ?>><?= htmlspecialchars($modulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100" type="submit">Buscar</button></div>
            </div>
        </div>
    </form>

    <div class="row g-3">
        <?php foreach ($articulos as $articulo): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-kriamos h-100">
                    <div class="card-body">
                        <span class="badge badge-kriamos mb-2"><?= htmlspecialchars($articulo['modulo']) ?></span>
                        <h2 class="h5 text-kriamos"><?= htmlspecialchars($articulo['titulo']) ?></h2>
                        <p class="text-muted"><?= htmlspecialchars($articulo['resumen'] ?? '') ?></p>
                        <a href="<?= BASE_PATH ?>/ayuda/articulo?id=<?= (int) $articulo['id'] ?>" class="btn btn-sm btn-outline-kriamos">Leer guía</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($articulos)): ?>
            <div class="col-12"><div class="alert alert-warning">No se encontraron artículos para la búsqueda indicada.</div></div>
        <?php endif; ?>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
