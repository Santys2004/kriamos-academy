<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class CourseController
{
    public function list(): void
    {
        try {
            $pdo = Database::connection();
            $sql = "SELECT c.*,
                    COUNT(DISTINCT m.id) AS modules_count,
                    COUNT(DISTINCT mat.id) AS materials_count,
                    COUNT(DISTINCT ca.id) AS assignments_count
                FROM courses c
                LEFT JOIN course_modules m ON m.course_id = c.id
                LEFT JOIN course_materials mat ON mat.module_id = m.id
                LEFT JOIN course_assignments ca ON ca.course_id = c.id
                GROUP BY c.id
                ORDER BY c.created_at DESC";
            $courses = $pdo->query($sql)->fetchAll();
        } catch (Throwable $e) {
            $courses = [];
            $error = $e->getMessage();
        }

        view('admin_cursos', [
            'title' => 'Administración de cursos',
            'courses' => $courses,
            'error' => $error ?? null,
        ]);
    }

    public function create(): void
    {
        view('curso_form', [
            'title' => 'Crear curso',
            'errors' => [],
            'old' => ['status' => 'activo', 'target_audience' => 'todos'],
        ]);
    }

    public function store(): void
    {
        $data = $_POST;
        $required = [
            'code' => 'código del curso',
            'title' => 'nombre del curso',
            'target_audience' => 'público objetivo',
            'status' => 'estado',
        ];
        $errors = validate_required($data, $required);

        $target = $data['target_audience'] ?? 'todos';
        $status = $data['status'] ?? 'activo';
        if (!in_array($target, ['todos', 'empleados', 'contratistas'], true)) {
            $errors['target_audience'] = 'Seleccione un público objetivo válido.';
        }
        if (!in_array($status, ['activo', 'inactivo'], true)) {
            $errors['status'] = 'Seleccione un estado válido.';
        }

        if ($errors) {
            view('curso_form', [
                'title' => 'Crear curso',
                'errors' => $errors,
                'old' => $data,
            ]);
            return;
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare("INSERT INTO courses
                (code, title, description, target_audience, status, created_at, updated_at)
                VALUES (:code, :title, :description, :target_audience, :status, NOW(), NOW())");
            $stmt->execute([
                ':code' => strtoupper(trim($data['code'])),
                ':title' => trim($data['title']),
                ':description' => trim($data['description'] ?? ''),
                ':target_audience' => $target,
                ':status' => $status,
            ]);
            redirect(current_url('curso_detalle', ['id' => (string)$pdo->lastInsertId(), 'created' => '1']));
        } catch (Throwable $e) {
            view('curso_form', [
                'title' => 'Crear curso',
                'errors' => ['general' => $e->getMessage()],
                'old' => $data,
            ]);
        }
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect(current_url('admin_cursos'));
        }

        try {
            $pdo = Database::connection();
            $course = $this->findCourse($pdo, $id);
            if (!$course) {
                throw new RuntimeException('Curso no encontrado.');
            }

            $stmtModules = $pdo->prepare('SELECT * FROM course_modules WHERE course_id = :course_id ORDER BY sequence_order ASC, id ASC');
            $stmtModules->execute([':course_id' => $id]);
            $modules = $stmtModules->fetchAll();

            $materialsByModule = [];
            $questionsCountByModule = [];
            if ($modules) {
                $moduleIds = array_column($modules, 'id');
                $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
                $stmtMaterials = $pdo->prepare("SELECT * FROM course_materials WHERE module_id IN ($placeholders) ORDER BY created_at DESC");
                $stmtMaterials->execute($moduleIds);
                foreach ($stmtMaterials->fetchAll() as $material) {
                    $materialsByModule[$material['module_id']][] = $material;
                }

                try {
                    $stmtQuestions = $pdo->prepare("SELECT module_id, COUNT(*) AS total_questions, SUM(CASE WHEN status = 'activo' THEN 1 ELSE 0 END) AS active_questions FROM module_questions WHERE module_id IN ($placeholders) GROUP BY module_id");
                    $stmtQuestions->execute($moduleIds);
                    foreach ($stmtQuestions->fetchAll() as $row) {
                        $questionsCountByModule[$row['module_id']] = $row;
                    }
                } catch (Throwable) {
                    $questionsCountByModule = [];
                }
            }

            $stmtAssignments = $pdo->prepare('SELECT * FROM course_assignments WHERE course_id = :course_id ORDER BY created_at DESC');
            $stmtAssignments->execute([':course_id' => $id]);
            $assignments = $stmtAssignments->fetchAll();

            view('curso_detalle', [
                'title' => 'Detalle del curso',
                'course' => $course,
                'modules' => $modules,
                'materialsByModule' => $materialsByModule,
                'questionsCountByModule' => $questionsCountByModule,
                'assignments' => $assignments,
                'success' => $_GET['created'] ?? $_GET['success'] ?? null,
            ]);
        } catch (Throwable $e) {
            view('admin_cursos', [
                'title' => 'Administración de cursos',
                'courses' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function createModule(): void
    {
        $id = (int)($_GET['course_id'] ?? 0);
        $pdo = Database::connection();
        $course = $this->findCourse($pdo, $id);
        if (!$course) {
            redirect(current_url('admin_cursos'));
        }

        view('modulo_form', [
            'title' => 'Crear módulo',
            'course' => $course,
            'errors' => [],
            'old' => ['status' => 'activo', 'sequence_order' => '1', 'estimated_minutes' => '15'],
        ]);
    }

    public function storeModule(): void
    {
        $data = $_POST;
        $courseId = (int)($data['course_id'] ?? 0);
        $required = [
            'course_id' => 'curso',
            'title' => 'nombre del módulo',
            'sequence_order' => 'orden',
            'estimated_minutes' => 'tiempo estimado',
            'status' => 'estado',
        ];
        $errors = validate_required($data, $required);

        try {
            $pdo = Database::connection();
            $course = $this->findCourse($pdo, $courseId);
            if (!$course) {
                throw new RuntimeException('Curso no encontrado.');
            }

            if ($errors) {
                view('modulo_form', ['title' => 'Crear módulo', 'course' => $course, 'errors' => $errors, 'old' => $data]);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO course_modules
                (course_id, title, description, sequence_order, estimated_minutes, status, created_at, updated_at)
                VALUES (:course_id, :title, :description, :sequence_order, :estimated_minutes, :status, NOW(), NOW())");
            $stmt->execute([
                ':course_id' => $courseId,
                ':title' => trim($data['title']),
                ':description' => trim($data['description'] ?? ''),
                ':sequence_order' => max(1, (int)$data['sequence_order']),
                ':estimated_minutes' => max(1, (int)$data['estimated_minutes']),
                ':status' => $data['status'] === 'inactivo' ? 'inactivo' : 'activo',
            ]);

            redirect(current_url('curso_detalle', ['id' => (string)$courseId, 'success' => 'modulo']));
        } catch (Throwable $e) {
            $course = $course ?? ['id' => $courseId, 'title' => 'Curso'];
            view('modulo_form', ['title' => 'Crear módulo', 'course' => $course, 'errors' => ['general' => $e->getMessage()], 'old' => $data]);
        }
    }

    public function createMaterial(): void
    {
        $moduleId = (int)($_GET['module_id'] ?? 0);
        try {
            $pdo = Database::connection();
            $module = $this->findModuleWithCourse($pdo, $moduleId);
            if (!$module) {
                throw new RuntimeException('Módulo no encontrado.');
            }
            view('material_form', [
                'title' => 'Subir material',
                'module' => $module,
                'errors' => [],
                'old' => ['material_type' => 'pdf', 'is_downloadable' => '1'],
            ]);
        } catch (Throwable $e) {
            redirect(current_url('admin_cursos'));
        }
    }

    public function storeMaterial(): void
    {
        $data = $_POST;
        $moduleId = (int)($data['module_id'] ?? 0);
        $required = [
            'module_id' => 'módulo',
            'title' => 'nombre del material',
            'material_type' => 'tipo de material',
        ];
        $errors = validate_required($data, $required);

        try {
            $pdo = Database::connection();
            $module = $this->findModuleWithCourse($pdo, $moduleId);
            if (!$module) {
                throw new RuntimeException('Módulo no encontrado.');
            }

            $type = $data['material_type'] ?? 'otro';
            $validTypes = ['manual', 'video', 'podcast', 'pdf', 'word', 'powerpoint', 'imagen', 'otro'];
            if (!in_array($type, $validTypes, true)) {
                $errors['material_type'] = 'Seleccione un tipo de material válido.';
            }

            if ($errors) {
                view('material_form', ['title' => 'Subir material', 'module' => $module, 'errors' => $errors, 'old' => $data]);
                return;
            }

            $uploaded = upload_course_material($_FILES['material_file'] ?? [], $module['course_code'], $type);

            $stmt = $pdo->prepare("INSERT INTO course_materials
                (module_id, material_type, title, description, file_path, original_filename, mime_type, file_size, is_downloadable, created_at, updated_at)
                VALUES (:module_id, :material_type, :title, :description, :file_path, :original_filename, :mime_type, :file_size, :is_downloadable, NOW(), NOW())");
            $stmt->execute([
                ':module_id' => $moduleId,
                ':material_type' => $type,
                ':title' => trim($data['title']),
                ':description' => trim($data['description'] ?? ''),
                ':file_path' => $uploaded['path'],
                ':original_filename' => $uploaded['original_filename'],
                ':mime_type' => $uploaded['mime_type'],
                ':file_size' => $uploaded['file_size'],
                ':is_downloadable' => isset($data['is_downloadable']) ? 1 : 0,
            ]);

            redirect(current_url('curso_detalle', ['id' => (string)$module['course_id'], 'success' => 'material']));
        } catch (Throwable $e) {
            $module = $module ?? ['id' => $moduleId, 'course_id' => 0, 'title' => 'Módulo', 'course_title' => 'Curso'];
            view('material_form', ['title' => 'Subir material', 'module' => $module, 'errors' => ['general' => $e->getMessage()], 'old' => $data]);
        }
    }

    public function createAssignment(): void
    {
        $courseId = (int)($_GET['course_id'] ?? 0);
        $pdo = Database::connection();
        $course = $this->findCourse($pdo, $courseId);
        if (!$course) {
            redirect(current_url('admin_cursos'));
        }

        view('asignacion_form', [
            'title' => 'Asignar curso',
            'course' => $course,
            'errors' => [],
            'old' => ['person_type' => 'todos', 'work_site' => 'todos', 'department' => 'todos', 'active' => '1'],
        ]);
    }

    public function storeAssignment(): void
    {
        $data = $_POST;
        $courseId = (int)($data['course_id'] ?? 0);

        try {
            $pdo = Database::connection();
            $course = $this->findCourse($pdo, $courseId);
            if (!$course) {
                throw new RuntimeException('Curso no encontrado.');
            }

            $personType = $data['person_type'] ?? 'todos';
            $workSite = $data['work_site'] ?? 'todos';
            $department = $data['department'] ?? 'todos';

            $validPersonTypes = ['todos', 'empleado', 'contratista'];
            $validSites = ['todos', 'GAEE', 'GALM', 'Oficina'];
            $validDepartments = ['todos', 'Operaciones', 'Mantenimiento', 'Compras', 'Gestión Humana', 'Administrativos'];

            $errors = [];
            if (!in_array($personType, $validPersonTypes, true)) {
                $errors['person_type'] = 'Seleccione un tipo de persona válido.';
            }
            if (!in_array($workSite, $validSites, true)) {
                $errors['work_site'] = 'Seleccione un sitio de trabajo válido.';
            }
            if (!in_array($department, $validDepartments, true)) {
                $errors['department'] = 'Seleccione un equipo válido.';
            }
            if ($personType === 'contratista') {
                $department = 'todos';
            }

            if ($errors) {
                view('asignacion_form', ['title' => 'Asignar curso', 'course' => $course, 'errors' => $errors, 'old' => $data]);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO course_assignments
                (course_id, person_type, work_site, department, position_keyword, active, created_at, updated_at)
                VALUES (:course_id, :person_type, :work_site, :department, :position_keyword, :active, NOW(), NOW())");
            $stmt->execute([
                ':course_id' => $courseId,
                ':person_type' => $personType,
                ':work_site' => $workSite,
                ':department' => $department,
                ':position_keyword' => trim($data['position_keyword'] ?? ''),
                ':active' => isset($data['active']) ? 1 : 0,
            ]);

            redirect(current_url('curso_detalle', ['id' => (string)$courseId, 'success' => 'asignacion']));
        } catch (Throwable $e) {
            $course = $course ?? ['id' => $courseId, 'title' => 'Curso'];
            view('asignacion_form', ['title' => 'Asignar curso', 'course' => $course, 'errors' => ['general' => $e->getMessage()], 'old' => $data]);
        }
    }

    private function findCourse(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private function findModuleWithCourse(PDO $pdo, int $moduleId): ?array
    {
        $stmt = $pdo->prepare("SELECT m.*, c.title AS course_title, c.code AS course_code
            FROM course_modules m
            INNER JOIN courses c ON c.id = m.course_id
            WHERE m.id = :id
            LIMIT 1");
        $stmt->execute([':id' => $moduleId]);
        return $stmt->fetch() ?: null;
    }
}
