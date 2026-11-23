<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container-fluid px-4 py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-kriamos mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Administración de usuarios corporativos del sistema.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_PATH ?>/admin" class="btn btn-outline-secondary">Volver</a>
            <a href="<?= BASE_PATH ?>/admin/usuarios/create" class="btn btn-kriamos">Crear usuario</a>
        </div>
    </div>

    <form method="get" class="card card-kriamos mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label class="form-label">Buscar</label><input type="text" name="q" class="form-control" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int) $rol['id'] ?>" <?= ($_GET['rol_id'] ?? '') == $rol['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="bloqueado" <?= ($_GET['estado'] ?? '') === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-kriamos w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="card card-kriamos">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Cargo</th><th>Estado</th><th>Último ingreso</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['rol_nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['cargo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($usuario['estado']) ?></td>
                            <td><?= htmlspecialchars($usuario['ultimo_ingreso_at'] ?? 'Sin registro') ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_PATH ?>/admin/usuarios/edit?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-kriamos">Editar</a>
                                <form method="post" action="<?= BASE_PATH ?>/admin/usuarios/estado" class="d-inline">
                                    <?= Csrf::input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                    <input type="hidden" name="estado" value="<?= $usuario['estado'] === 'activo' ? 'bloqueado' : 'activo' ?>">
                                    <button class="btn btn-sm btn-outline-secondary"><?= $usuario['estado'] === 'activo' ? 'Bloquear' : 'Activar' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?><tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
