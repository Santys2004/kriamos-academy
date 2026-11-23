<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <span class="badge bg-kriamos-orange mb-2"><i class="fa-solid fa-robot me-1"></i>Fase 9</span>
                <h1 class="h3 fw-bold text-kriamos-blue mb-1">Administración del Chat IA</h1>
                <p class="text-muted mb-0">Gestione la base de conocimiento interna y revise la trazabilidad de conversaciones.</p>
            </div>
            <a href="<?= e(current_url('chat_ia')) ?>" class="btn btn-kriamos-orange"><i class="fa-solid fa-comments me-2"></i>Probar asistente</a>
        </div>

        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card card-kriamos">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-plus me-2"></i>Agregar conocimiento</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= e(current_url('guardar_conocimiento_ia')) ?>" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Título</label>
                                <input type="text" name="title" class="form-control" maxlength="180" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contenido / respuesta base</label>
                                <textarea name="content" class="form-control" rows="7" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Palabras clave</label>
                                <textarea name="keywords" class="form-control" rows="2" placeholder="Ejemplo: bioseguridad ingreso ducha pediluvio"></textarea>
                                <div class="form-text">Separe conceptos importantes con espacios o comas.</div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fuente</label>
                                    <select name="source_type" class="form-select" required>
                                        <option value="poe">POE</option>
                                        <option value="manual">Manual</option>
                                        <option value="curso">Curso</option>
                                        <option value="app">Aplicación</option>
                                        <option value="faq">FAQ</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Audiencia</label>
                                    <select name="target_audience" class="form-select" required>
                                        <option value="todos">Todos</option>
                                        <option value="empleado">Empleado</option>
                                        <option value="contratista">Contratista</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Sede</label>
                                    <select name="work_site" class="form-select" required>
                                        <option value="todos">Todas</option>
                                        <option value="GAEE">GAEE</option>
                                        <option value="GALM">GALM</option>
                                        <option value="Oficina">Oficina</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Equipo</label>
                                    <select name="department" class="form-select" required>
                                        <option value="todos">Todos</option>
                                        <option value="Operaciones">Operaciones</option>
                                        <option value="Mantenimiento">Mantenimiento</option>
                                        <option value="Compras">Compras</option>
                                        <option value="Gestión Humana">Gestión Humana</option>
                                        <option value="Administrativos">Administrativos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label fw-semibold">Creado por</label>
                                <input type="text" name="created_by" class="form-control" value="Administrador">
                            </div>
                            <button class="btn btn-kriamos-orange w-100" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar conocimiento</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-kriamos mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-database me-2"></i>Base de conocimiento</h2>
                        <span class="badge bg-kriamos-blue"><?= count($knowledge ?? []) ?> registros</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Fuente</th>
                                        <th>Filtros</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($knowledge)): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-4">No hay registros de conocimiento.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($knowledge as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-kriamos-blue"><?= e($item['title']) ?></div>
                                                    <div class="small text-muted text-truncate" style="max-width: 360px;"><?= e($item['keywords'] ?? '') ?></div>
                                                </td>
                                                <td><span class="badge text-bg-light"><?= e($item['source_type']) ?></span></td>
                                                <td class="small text-muted"><?= e($item['target_audience']) ?> · <?= e($item['work_site']) ?> · <?= e($item['department']) ?></td>
                                                <td><span class="badge <?= $item['status'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e($item['status']) ?></span></td>
                                                <td class="text-end">
                                                    <form action="<?= e(current_url('cambiar_estado_conocimiento_ia')) ?>" method="post">
                                                        <input type="hidden" name="knowledge_id" value="<?= (int)$item['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $item['status'] === 'activo' ? 'Inactivar' : 'Activar' ?></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-kriamos">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold text-kriamos-blue mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Sesiones recientes</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Última interacción</th>
                                        <th>Persona</th>
                                        <th>Tipo / Sede</th>
                                        <th>Mensajes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sessions)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-4">Aún no hay sesiones registradas.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($sessions as $sessionRow): ?>
                                            <tr>
                                                <td><?= e(human_datetime($sessionRow['last_interaction_at'])) ?></td>
                                                <td>
                                                    <div class="fw-semibold"><?= e($sessionRow['full_name']) ?></div>
                                                    <div class="small text-muted"><?= e($sessionRow['document_number']) ?></div>
                                                </td>
                                                <td><?= e($sessionRow['person_type']) ?> · <?= e($sessionRow['work_site']) ?></td>
                                                <td><span class="badge text-bg-light"><?= (int)$sessionRow['total_messages'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
