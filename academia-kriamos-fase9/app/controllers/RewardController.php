<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/RewardService.php';

class RewardController
{
    private RewardService $service;

    public function __construct()
    {
        $this->service = new RewardService();
    }

    public function adminList(): void
    {
        $filters = $this->service->filtersFromRequest($_GET);
        try {
            $pdo = Database::connection();
            $rewards = $this->service->listRewards($pdo, $filters);
            $stats = $this->service->stats($pdo, $filters);
            view('recompensas_admin', [
                'title' => 'Recompensas',
                'filters' => $filters,
                'rewards' => $rewards,
                'stats' => $stats,
                'success' => $_GET['success'] ?? null,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('recompensas_admin', [
                'title' => 'Recompensas',
                'filters' => $filters,
                'rewards' => [],
                'stats' => [],
                'success' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function markDelivered(): void
    {
        $rewardId = (int)($_POST['reward_id'] ?? 0);
        $deliveredBy = trim((string)($_POST['delivered_by'] ?? ''));
        $notes = trim((string)($_POST['delivery_notes'] ?? ''));

        try {
            if ($rewardId <= 0) {
                throw new RuntimeException('Recompensa no válida.');
            }
            if ($deliveredBy === '') {
                throw new RuntimeException('Debe indicar quién entrega la recompensa.');
            }
            $pdo = Database::connection();
            $this->service->markDelivered($pdo, $rewardId, $deliveredBy, $notes);
            redirect(current_url('admin_recompensas', ['success' => 'entregada']));
        } catch (Throwable $e) {
            redirect(current_url('admin_recompensas', ['success' => 'error']));
        }
    }

    public function voidReward(): void
    {
        $rewardId = (int)($_POST['reward_id'] ?? 0);
        $deliveredBy = trim((string)($_POST['delivered_by'] ?? ''));
        $notes = trim((string)($_POST['delivery_notes'] ?? ''));

        try {
            if ($rewardId <= 0) {
                throw new RuntimeException('Recompensa no válida.');
            }
            if ($deliveredBy === '') {
                $deliveredBy = 'Administrador';
            }
            $pdo = Database::connection();
            $this->service->voidReward($pdo, $rewardId, $deliveredBy, $notes);
            redirect(current_url('admin_recompensas', ['success' => 'anulada']));
        } catch (Throwable) {
            redirect(current_url('admin_recompensas', ['success' => 'error']));
        }
    }

    public function personForm(): void
    {
        view('recompensas_persona', [
            'title' => 'Mis recompensas',
            'document' => $_GET['document'] ?? '',
            'rewards' => [],
            'error' => null,
            'searched' => false,
        ]);
    }

    public function personResult(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        $rewards = [];
        $error = null;

        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida.');
            }
            $pdo = Database::connection();
            $rewards = $this->service->listByDocument($pdo, $document);
            if (!$rewards) {
                $error = 'No se encontraron recompensas para esta cédula. Recuerde que las recompensas aplican solo para empleados con nota de 90% o 100%.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('recompensas_persona', [
            'title' => 'Mis recompensas',
            'document' => $document,
            'rewards' => $rewards,
            'error' => $error,
            'searched' => true,
        ]);
    }

    public function export(): void
    {
        $filters = $this->service->filtersFromRequest($_GET);
        try {
            $pdo = Database::connection();
            $csv = $this->service->exportCsv($pdo, $filters);
            $filename = 'recompensas_academia_kriamos_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo "\xEF\xBB\xBF";
            echo $csv;
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error al generar exportación: ' . $e->getMessage();
        }
    }
}
