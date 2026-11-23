<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/ExamService.php';

class OfflineService
{
    private ExamService $examService;

    public function __construct()
    {
        $this->examService = new ExamService();
    }

    public function findPersonByDocument(PDO $pdo, string $document): ?array
    {
        return $this->examService->findPersonByDocument($pdo, $document);
    }

    public function getDownloadableMaterials(PDO $pdo, array $person): array
    {
        $modules = $this->examService->getAssignedModules($pdo, $person);
        if (!$modules) {
            return [];
        }

        $moduleIds = array_values(array_unique(array_map(static fn(array $m): int => (int)$m['module_id'], $modules)));
        if (!$moduleIds) {
            return [];
        }

        $moduleInfo = [];
        foreach ($modules as $module) {
            $moduleInfo[(int)$module['module_id']] = $module;
        }

        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $sql = "SELECT mat.*
            FROM course_materials mat
            WHERE mat.module_id IN ($placeholders)
              AND mat.is_downloadable = 1
            ORDER BY mat.module_id ASC, mat.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($moduleIds);

        $materials = [];
        foreach ($stmt->fetchAll() as $row) {
            $module = $moduleInfo[(int)$row['module_id']] ?? [];
            $row['course_code'] = $module['course_code'] ?? '';
            $row['course_title'] = $module['course_title'] ?? '';
            $row['module_title'] = $module['module_title'] ?? '';
            $row['estimated_minutes'] = $module['estimated_minutes'] ?? null;
            $materials[] = $row;
        }

        return $materials;
    }

    public function getDownloadHistory(PDO $pdo, int $personId): array
    {
        try {
            $stmt = $pdo->prepare("SELECT od.*, mat.title AS material_title, mat.file_path, mat.material_type,
                    m.title AS module_title, c.code AS course_code, c.title AS course_title
                FROM offline_downloads od
                INNER JOIN course_materials mat ON mat.id = od.material_id
                INNER JOIN course_modules m ON m.id = mat.module_id
                INNER JOIN courses c ON c.id = m.course_id
                WHERE od.person_id = :person_id
                ORDER BY od.downloaded_at DESC
                LIMIT 50");
            $stmt->execute([':person_id' => $personId]);
            return $stmt->fetchAll();
        } catch (Throwable) {
            return [];
        }
    }

    public function logDownload(PDO $pdo, int $personId, int $materialId, string $deviceInfo = ''): void
    {
        $stmt = $pdo->prepare("INSERT INTO offline_downloads
            (person_id, material_id, device_info, downloaded_at, created_at, updated_at)
            VALUES (:person_id, :material_id, :device_info, NOW(), NOW(), NOW())
            ON DUPLICATE KEY UPDATE device_info = VALUES(device_info), downloaded_at = NOW(), updated_at = NOW()");
        $stmt->execute([
            ':person_id' => $personId,
            ':material_id' => $materialId,
            ':device_info' => mb_substr($deviceInfo, 0, 250),
        ]);
    }
}
