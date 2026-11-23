<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/OfflineService.php';

class OfflineController
{
    private OfflineService $service;

    public function __construct()
    {
        $this->service = new OfflineService();
    }

    public function form(): void
    {
        view('offline_consulta', [
            'title' => 'Modo sin conexión',
            'person' => null,
            'materials' => [],
            'downloads' => [],
            'error' => null,
            'document' => $_GET['document'] ?? '',
        ]);
    }

    public function result(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        $person = null;
        $materials = [];
        $downloads = [];
        $error = null;

        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida.');
            }

            $pdo = Database::connection();
            $person = $this->service->findPersonByDocument($pdo, $document);
            if (!$person) {
                throw new RuntimeException('La cédula no está registrada o la persona está inactiva. Primero debe realizar el registro inicial.');
            }

            $materials = $this->service->getDownloadableMaterials($pdo, $person);
            $downloads = $this->service->getDownloadHistory($pdo, (int)$person['id']);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('offline_consulta', [
            'title' => 'Modo sin conexión',
            'person' => $person,
            'materials' => $materials,
            'downloads' => $downloads,
            'error' => $error,
            'document' => $document,
        ]);
    }

    public function registerDownload(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $document = normalize_document($_POST['document_number'] ?? '');
            $materialId = (int)($_POST['material_id'] ?? 0);
            $deviceInfo = $_POST['device_info'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');

            if ($document === '' || $materialId <= 0) {
                throw new RuntimeException('Datos incompletos para registrar la descarga.');
            }

            $pdo = Database::connection();
            $person = $this->service->findPersonByDocument($pdo, $document);
            if (!$person) {
                throw new RuntimeException('Persona no encontrada.');
            }

            $this->service->logDownload($pdo, (int)$person['id'], $materialId, $deviceInfo);
            echo json_encode(['ok' => true, 'message' => 'Descarga registrada.']);
        } catch (Throwable $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
    }
}
