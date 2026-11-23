<?php require dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Roles y permisos</h1>
            <p class="text-muted mb-0">Control de accesos por rol.</p>
        </div>
        <div class="col-auto"><a href="<?= BASE_PATH ?>/admin" class="btn btn-outline-secondary">Volver</a></div>
    </div>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Rol</th><th>Descripción</th><th>Usuarios</th><th>Permisos</th><th>Activo</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($roles as $rol): ?>
                        <tr>
                            <td><?= htmlspecialchars($rol['nombre']) ?></td>
                            <td><?= htmlspecialchars($rol['descripcion'] ?? '') ?></td>
                            <td><?= (int) $rol['total_usuarios'] ?></td>
                            <td><?= (int) $rol['total_permisos'] ?></td>
                            <td><?= (int) $rol['activo'] === 1 ? 'Sí' : 'No' ?></td>
                            <td class="text-end"><a href="<?= BASE_PATH ?>/admin/roles/edit?id=<?= (int) $rol['id'] ?>" class="btn btn-sm btn-outline-kriamos">Editar permisos</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
