<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/CertificateService.php';

class CertificateController
{
    private CertificateService $service;

    public function __construct()
    {
        $this->service = new CertificateService();
    }

    public function adminList(): void
    {
        try {
            $pdo = Database::connection();
            $certificates = $this->service->listRecent($pdo, 150);
            view('certificados_admin', [
                'title' => 'Certificados',
                'certificates' => $certificates,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('certificados_admin', [
                'title' => 'Certificados',
                'certificates' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function historyForm(): void
    {
        view('certificados_historial', [
            'title' => 'Historial de certificados',
            'certificates' => [],
            'document' => $_GET['document'] ?? '',
            'error' => null,
        ]);
    }

    public function historyResult(): void
    {
        $document = normalize_document($_POST['document_number'] ?? $_GET['document_number'] ?? '');
        $certificates = [];
        $error = null;

        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida.');
            }
            $pdo = Database::connection();
            $certificates = $this->service->findByPersonDocument($pdo, $document);
            if (!$certificates) {
                $error = 'No se encontraron certificados para la cédula consultada.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('certificados_historial', [
            'title' => 'Historial de certificados',
            'certificates' => $certificates,
            'document' => $document,
            'error' => $error,
        ]);
    }

    public function show(): void
    {
        $token = trim((string)($_GET['token'] ?? $_GET['code'] ?? ''));
        try {
            if ($token === '') {
                throw new RuntimeException('Debe indicar un código o token de certificado.');
            }
            $pdo = Database::connection();
            $certificate = $this->service->findByCodeOrToken($pdo, $token);
            if (!$certificate) {
                throw new RuntimeException('Certificado no encontrado.');
            }
            $this->service->refreshStatusIfExpired($pdo, (int)$certificate['id']);
            $certificate = $this->service->findById($pdo, (int)$certificate['id']) ?: $certificate;

            view('certificado_ver', [
                'title' => 'Ver certificado',
                'certificate' => $certificate,
                'service' => $this->service,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('certificado_ver', [
                'title' => 'Ver certificado',
                'certificate' => null,
                'service' => $this->service,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function download(): void
    {
        $code = trim((string)($_GET['code'] ?? ''));
        try {
            if ($code === '') {
                throw new RuntimeException('Debe indicar el código del certificado.');
            }
            $pdo = Database::connection();
            $certificate = $this->service->findByCodeOrToken($pdo, $code);
            if (!$certificate) {
                throw new RuntimeException('Certificado no encontrado.');
            }

            if (empty($certificate['pdf_path']) || !file_exists((string)$certificate['pdf_absolute_path'])) {
                $pdfRelativePath = $this->service->generatePdfFile($certificate);
                $stmt = $pdo->prepare('UPDATE certificates SET pdf_path = :pdf_path, updated_at = NOW() WHERE id = :id');
                $stmt->execute([':pdf_path' => $pdfRelativePath, ':id' => $certificate['id']]);
                $certificate = $this->service->findById($pdo, (int)$certificate['id']) ?: $certificate;
            }

            $file = (string)$certificate['pdf_absolute_path'];
            if (!file_exists($file)) {
                throw new RuntimeException('El archivo PDF no está disponible.');
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $certificate['certificate_code'] . '.pdf"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } catch (Throwable $e) {
            view('certificado_ver', [
                'title' => 'Descargar certificado',
                'certificate' => null,
                'service' => $this->service,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
