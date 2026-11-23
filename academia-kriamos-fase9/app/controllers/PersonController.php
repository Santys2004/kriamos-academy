<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class PersonController
{
    public function store(): void
    {
        $data = $_POST;
        $document = normalize_document($data['document_number'] ?? '');
        $personType = $data['person_type'] ?? '';

        $required = [
            'person_type' => 'tipo de registro',
            'full_name' => 'nombre completo',
            'document_number' => 'cédula',
            'residence' => 'lugar de residencia',
            'work_site' => 'sitio de trabajo',
            'cellphone' => 'teléfono celular',
            'email' => 'correo electrónico',
            'company' => 'empresa',
            'position' => 'cargo',
        ];

        if ($personType === 'empleado') {
            $required['department'] = 'equipo del empleado';
        }

        $errors = validate_required($data, $required);

        if (!in_array($personType, ['empleado', 'contratista'], true)) {
            $errors['person_type'] = 'Debe seleccionar empleado o contratista.';
        }

        if ($document === '') {
            $errors['document_number'] = 'La cédula debe contener números.';
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El correo electrónico no tiene un formato válido.';
        }

        if ($errors) {
            view('registro', [
                'title' => 'Registro de capacitación',
                'errors' => $errors,
                'old' => $data,
                'tipo' => $personType ?: 'empleado',
            ]);
            return;
        }

        try {
            $photoPath = upload_person_photo($_FILES['photo'] ?? [], $document);
            $pdo = Database::connection();

            $sql = "INSERT INTO people
                (person_type, full_name, document_number, residence, work_site, cellphone, email, company, position, department, photo_path, status, created_at, updated_at)
                VALUES
                (:person_type, :full_name, :document_number, :residence, :work_site, :cellphone, :email, :company, :position, :department, :photo_path, 'activo', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    person_type = VALUES(person_type),
                    full_name = VALUES(full_name),
                    residence = VALUES(residence),
                    work_site = VALUES(work_site),
                    cellphone = VALUES(cellphone),
                    email = VALUES(email),
                    company = VALUES(company),
                    position = VALUES(position),
                    department = VALUES(department),
                    photo_path = COALESCE(VALUES(photo_path), photo_path),
                    updated_at = NOW()";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':person_type' => $personType,
                ':full_name' => trim($data['full_name']),
                ':document_number' => $document,
                ':residence' => trim($data['residence']),
                ':work_site' => $data['work_site'],
                ':cellphone' => trim($data['cellphone']),
                ':email' => trim($data['email']),
                ':company' => trim($data['company']),
                ':position' => trim($data['position']),
                ':department' => $personType === 'empleado' ? ($data['department'] ?? null) : null,
                ':photo_path' => $photoPath,
            ]);

            view('registro_exitoso', [
                'title' => 'Registro exitoso',
                'person' => [
                    'full_name' => trim($data['full_name']),
                    'document_number' => $document,
                    'person_type' => $personType,
                    'work_site' => $data['work_site'],
                ],
            ]);
        } catch (Throwable $e) {
            view('registro', [
                'title' => 'Registro de capacitación',
                'errors' => ['general' => $e->getMessage()],
                'old' => $data,
                'tipo' => $personType ?: 'empleado',
            ]);
        }
    }

    public function list(): void
    {
        try {
            $pdo = Database::connection();
            $people = $pdo->query("SELECT * FROM people ORDER BY created_at DESC LIMIT 200")->fetchAll();
        } catch (Throwable $e) {
            $people = [];
            $error = $e->getMessage();
        }

        view('admin_personas', [
            'title' => 'Personas registradas',
            'people' => $people,
            'error' => $error ?? null,
        ]);
    }

    public function verify(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        $person = null;
        $error = null;

        if ($document === '') {
            $error = 'Ingrese una cédula válida.';
        } else {
            try {
                $pdo = Database::connection();
                $stmt = $pdo->prepare('SELECT * FROM people WHERE document_number = :document_number LIMIT 1');
                $stmt->execute([':document_number' => $document]);
                $person = $stmt->fetch() ?: null;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        view('verificar', [
            'title' => 'Verificar registro',
            'person' => $person,
            'error' => $error,
            'document' => $document,
        ]);
    }
}
