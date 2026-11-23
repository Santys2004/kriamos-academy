<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/CalendarService.php';

class CalendarController
{
    private CalendarService $service;

    public function __construct()
    {
        $this->service = new CalendarService();
    }

    public function adminList(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'work_site' => $_GET['work_site'] ?? '',
            'person_type' => $_GET['person_type'] ?? '',
        ];

        try {
            $pdo = Database::connection();
            $synced = isset($_GET['sync']) ? $this->service->syncAllCertificates($pdo) : null;
            $items = $this->service->listAdmin($pdo, $filters, 300);
            $stats = $this->service->stats($pdo);
            $notifications = $this->service->notificationHistory($pdo, 80);
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => $items,
                'stats' => $stats,
                'notifications' => $notifications,
                'filters' => $filters,
                'synced' => $synced,
                'result' => null,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => [],
                'stats' => [],
                'notifications' => [],
                'filters' => $filters,
                'synced' => null,
                'result' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function processReminders(): void
    {
        try {
            $pdo = Database::connection();
            $result = $this->service->processDueNotifications($pdo, 80);
            $items = $this->service->listAdmin($pdo, [], 300);
            $stats = $this->service->stats($pdo);
            $notifications = $this->service->notificationHistory($pdo, 80);
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => $items,
                'stats' => $stats,
                'notifications' => $notifications,
                'filters' => ['status' => '', 'work_site' => '', 'person_type' => ''],
                'synced' => null,
                'result' => $result,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => [],
                'stats' => [],
                'notifications' => [],
                'filters' => ['status' => '', 'work_site' => '', 'person_type' => ''],
                'synced' => null,
                'result' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendManualReminder(): void
    {
        $calendarId = (int)($_POST['calendar_id'] ?? 0);
        try {
            if ($calendarId <= 0) {
                throw new RuntimeException('Seleccione una programación válida.');
            }
            $pdo = Database::connection();
            $this->service->sendManualReminder($pdo, $calendarId);
            $result = $this->service->processDueNotifications($pdo, 1);
            $items = $this->service->listAdmin($pdo, [], 300);
            $stats = $this->service->stats($pdo);
            $notifications = $this->service->notificationHistory($pdo, 80);
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => $items,
                'stats' => $stats,
                'notifications' => $notifications,
                'filters' => ['status' => '', 'work_site' => '', 'person_type' => ''],
                'synced' => null,
                'result' => $result,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('calendario_admin', [
                'title' => 'Calendario de recertificación',
                'items' => [],
                'stats' => [],
                'notifications' => [],
                'filters' => ['status' => '', 'work_site' => '', 'person_type' => ''],
                'synced' => null,
                'result' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function personForm(): void
    {
        view('calendario_persona', [
            'title' => 'Mi calendario de capacitación',
            'document' => $_GET['document'] ?? '',
            'items' => [],
            'error' => null,
        ]);
    }

    public function personResult(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida.');
            }
            $pdo = Database::connection();
            $items = $this->service->listByPersonDocument($pdo, $document);
            view('calendario_persona', [
                'title' => 'Mi calendario de capacitación',
                'document' => $document,
                'items' => $items,
                'error' => $items ? null : 'No se encontraron capacitaciones programadas para la cédula consultada.',
            ]);
        } catch (Throwable $e) {
            view('calendario_persona', [
                'title' => 'Mi calendario de capacitación',
                'document' => $document,
                'items' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
