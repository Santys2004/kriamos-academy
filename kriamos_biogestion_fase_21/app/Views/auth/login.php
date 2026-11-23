<?php
use App\Services\Csrf;
require dirname(__DIR__) . '/layouts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card card-kriamos shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="<?= BASE_PATH ?>/assets/img/logo-kriamos.png" alt="Kriamos" class="login-logo mb-3">
                        <h1 class="h4 fw-bold text-kriamos">Ingreso corporativo</h1>
                        <p class="text-muted mb-0">Acceso exclusivo para personal propio de Kriamos.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_PATH ?>/login" autocomplete="off">
                        <?= Csrf::input() ?>

                        <div class="mb-3">
                            <label class="form-label">Correo electrónico corporativo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="usuario@kriamos.com.co" required>
                            </div>
                            <small class="text-muted">Solo se aceptan correos corporativos autorizados.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-kriamos w-100">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Ingresar
                        </button>
                    </form>

                    <div class="alert alert-info mt-4 mb-0 small">
                        El sistema registra intentos de acceso, valida dominio corporativo y bloquea temporalmente accesos fallidos repetidos.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
