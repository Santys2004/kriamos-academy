<section class="hero-kriamos">
    <div class="hero-overlay">
        <div class="container py-5">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-7 text-white">
                    <span class="badge bg-kriamos-orange mb-3">Sistema interno de capacitación · Fase 9</span>
                    <h1 class="display-5 fw-bold mb-3">Academia KRIAMOS</h1>
                    <p class="lead mb-4">Sistema para registrar personas, organizar cursos, evaluar conocimientos, certificar módulos, gestionar recompensas, permitir estudio de materiales en modo sin conexión y ofrecer chat de ayuda con base de conocimiento interna.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= e(current_url('registro', ['tipo' => 'empleado'])) ?>" class="btn btn-kriamos-orange btn-lg"><i class="fa-solid fa-user-tie me-2"></i>Registrar empleado</a>
                        <a href="<?= e(current_url('registro', ['tipo' => 'contratista'])) ?>" class="btn btn-light btn-lg"><i class="fa-solid fa-helmet-safety me-2"></i>Registrar contratista</a>
                        <a href="<?= e(current_url('admin_cursos')) ?>" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-graduation-cap me-2"></i>Gestionar cursos</a>
                        <a href="<?= e(current_url('mis_capacitaciones')) ?>" class="btn btn-light btn-lg"><i class="fa-solid fa-clipboard-check me-2"></i>Presentar examen</a>
                        <a href="<?= e(current_url('mis_recompensas')) ?>" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-gift me-2"></i>Mis recompensas</a>
                        <a href="<?= e(current_url('offline')) ?>" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-cloud-arrow-down me-2"></i>Estudiar sin internet</a>
                        <a href="<?= e(current_url('chat_ia')) ?>" class="btn btn-kriamos-orange btn-lg"><i class="fa-solid fa-robot me-2"></i>Chat IA</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card h-100 card-kriamos">
                <div class="card-body">
                    <div class="icon-box"><i class="fa-solid fa-id-card"></i></div>
                    <h5 class="fw-bold">Registro único</h5>
                    <p class="text-muted">Captura datos básicos, sitio de trabajo, empresa, cargo y foto del participante.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 card-kriamos">
                <div class="card-body">
                    <div class="icon-box"><i class="fa-solid fa-people-group"></i></div>
                    <h5 class="fw-bold">Empleados y contratistas</h5>
                    <p class="text-muted">Diferencia el registro según el tipo de usuario y el equipo interno del empleado.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 card-kriamos">
                <div class="card-body">
                    <div class="icon-box"><i class="fa-solid fa-book-open-reader"></i></div>
                    <h5 class="fw-bold">Cursos y módulos</h5>
                    <p class="text-muted">Permite crear cursos y organizar la capacitación por módulos temáticos.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 card-kriamos">
                <div class="card-body">
                    <div class="icon-box"><i class="fa-solid fa-robot"></i></div>
                    <h5 class="fw-bold">Chat IA</h5>
                    <p class="text-muted">Asistente de ayuda con base de conocimiento interna, trazabilidad y escalamiento responsable.</p>
                </div>
            </div>
        </div>
    </div>
</section>
