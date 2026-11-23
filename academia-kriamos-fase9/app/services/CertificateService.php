<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/CalendarService.php';

class CertificateService
{
    public function ensureCertificateForPassedAttempt(PDO $pdo, int $attemptId): ?array
    {
        $attempt = $this->getPassedAttemptData($pdo, $attemptId);
        if (!$attempt) {
            return null;
        }

        $existing = $this->findByAttemptId($pdo, $attemptId);
        if ($existing) {
            $this->refreshStatusIfExpired($pdo, (int)$existing['id']);
            try {
                (new CalendarService())->syncCertificate($pdo, (int)$existing['id']);
            } catch (Throwable) {
                // La creación del certificado no debe fallar si el calendario todavía no fue migrado.
            }
            return $this->findById($pdo, (int)$existing['id']);
        }

        $certificateCode = $this->generateUniqueCode($pdo);
        $verificationToken = $this->generateUniqueToken($pdo);
        $validMonths = (int)(app_config('certificate_valid_months') ?: 6);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO certificates
                (certificate_code, verification_token, person_id, course_id, module_id, exam_attempt_id, issued_at, expires_at, status, created_at, updated_at)
                VALUES (:certificate_code, :verification_token, :person_id, :course_id, :module_id, :exam_attempt_id, NOW(), DATE_ADD(NOW(), INTERVAL {$validMonths} MONTH), 'vigente', NOW(), NOW())");
            $stmt->execute([
                ':certificate_code' => $certificateCode,
                ':verification_token' => $verificationToken,
                ':person_id' => $attempt['person_id'],
                ':course_id' => $attempt['course_id'],
                ':module_id' => $attempt['module_id'],
                ':exam_attempt_id' => $attemptId,
            ]);
            $certificateId = (int)$pdo->lastInsertId();
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $certificate = $this->findById($pdo, $certificateId);
        if (!$certificate) {
            return null;
        }

        $qrRelativePath = $this->generateQrFile($certificate);
        $stmtQr = $pdo->prepare('UPDATE certificates SET qr_path = :qr_path, updated_at = NOW() WHERE id = :id');
        $stmtQr->execute([':qr_path' => $qrRelativePath, ':id' => $certificateId]);

        $certificate = $this->findById($pdo, $certificateId);
        if ($certificate && class_exists('\\Dompdf\\Dompdf')) {
            try {
                $pdfRelativePath = $this->generatePdfFile($certificate);
                $stmtPdf = $pdo->prepare('UPDATE certificates SET pdf_path = :pdf_path, updated_at = NOW() WHERE id = :id');
                $stmtPdf->execute([':pdf_path' => $pdfRelativePath, ':id' => $certificateId]);
            } catch (Throwable) {
                // Si falla PDF, el certificado sigue vigente y puede verse en HTML.
            }
        }

        try {
            (new CalendarService())->syncCertificate($pdo, $certificateId);
        } catch (Throwable) {
            // La sincronización del calendario se puede ejecutar después desde el panel de Fase 5.
        }

        return $this->findById($pdo, $certificateId);
    }

    public function getPassedAttemptData(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare("SELECT
                ea.*,
                p.full_name,
                p.document_number,
                p.person_type,
                p.company,
                p.work_site,
                p.position,
                p.department,
                p.email,
                p.photo_path,
                m.title AS module_title,
                m.course_id,
                c.title AS course_title,
                c.code AS course_code
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            INNER JOIN course_modules m ON m.id = ea.module_id
            INNER JOIN courses c ON c.id = m.course_id
            WHERE ea.id = :id AND ea.status = 'aprobado' AND ea.passed = 1
            LIMIT 1");
        $stmt->execute([':id' => $attemptId]);
        return $stmt->fetch() ?: null;
    }

    public function findByAttemptId(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM certificates WHERE exam_attempt_id = :exam_attempt_id LIMIT 1');
        $stmt->execute([':exam_attempt_id' => $attemptId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(PDO $pdo, int $certificateId): ?array
    {
        $stmt = $pdo->prepare($this->certificateSelectSql() . ' WHERE cert.id = :id LIMIT 1');
        $stmt->execute([':id' => $certificateId]);
        $certificate = $stmt->fetch() ?: null;
        return $certificate ? $this->appendDerivedFields($certificate) : null;
    }

    public function findByCodeOrToken(PDO $pdo, string $codeOrToken): ?array
    {
        $stmt = $pdo->prepare($this->certificateSelectSql() . ' WHERE cert.certificate_code = :value1 OR cert.verification_token = :value2 LIMIT 1');
        $stmt->execute([':value1' => $codeOrToken, ':value2' => $codeOrToken]);
        $certificate = $stmt->fetch() ?: null;
        return $certificate ? $this->appendDerivedFields($certificate) : null;
    }

    public function findByPersonDocument(PDO $pdo, string $document): array
    {
        $stmt = $pdo->prepare($this->certificateSelectSql() . " WHERE p.document_number = :document ORDER BY cert.issued_at DESC, cert.id DESC");
        $stmt->execute([':document' => $document]);
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => $this->appendDerivedFields($row), $rows);
    }

    public function listRecent(PDO $pdo, int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $pdo->query($this->certificateSelectSql() . " ORDER BY cert.issued_at DESC, cert.id DESC LIMIT {$limit}");
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => $this->appendDerivedFields($row), $rows);
    }

    public function refreshStatusIfExpired(PDO $pdo, int $certificateId): void
    {
        $stmt = $pdo->prepare("UPDATE certificates
            SET status = 'vencido', updated_at = NOW()
            WHERE id = :id AND status = 'vigente' AND expires_at < NOW()");
        $stmt->execute([':id' => $certificateId]);
    }

    public function generatePdfFile(array $certificate): string
    {
        if (!class_exists('\\Dompdf\\Dompdf')) {
            throw new RuntimeException('Para generar certificados PDF debe instalar dependencias con: composer install');
        }

        $relativePath = rtrim((string)app_config('certificate_relative_dir'), '/') . '/' . $certificate['certificate_code'] . '.pdf';
        $absolutePath = $this->absoluteProjectPath($relativePath);
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        $dompdf->loadHtml($this->certificateHtml($certificate, true), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        file_put_contents($absolutePath, $dompdf->output());

        return $relativePath;
    }

    public function generateQrFile(array $certificate): string
    {
        $relativePath = rtrim((string)app_config('qr_relative_dir'), '/') . '/' . $certificate['certificate_code'] . '.svg';
        $absolutePath = $this->absoluteProjectPath($relativePath);
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $verificationUrl = $this->verificationUrl($certificate['verification_token']);

        if (class_exists('\\chillerlan\\QRCode\\QRCode') && class_exists('\\chillerlan\\QRCode\\QROptions')) {
            $options = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel' => \chillerlan\QRCode\QRCode::ECC_M,
                'scale' => 6,
                'addQuietzone' => true,
            ]);
            $svg = (new \chillerlan\QRCode\QRCode($options))->render($verificationUrl);
            file_put_contents($absolutePath, $svg);
            return $relativePath;
        }

        file_put_contents($absolutePath, $this->fallbackQrSvg($certificate, $verificationUrl));
        return $relativePath;
    }

    public function certificateHtml(array $certificate, bool $forPdf = false): string
    {
        $logoPath = $this->absoluteProjectPath('public/assets/img/logo-kriamos.png');
        $heroPath = $this->absoluteProjectPath('public/assets/img/hero-galpon.jpg');
        $qrPath = !empty($certificate['qr_path']) ? $this->absoluteProjectPath($certificate['qr_path']) : '';

        $logoSrc = file_exists($logoPath) ? 'file://' . $logoPath : '';
        $heroSrc = file_exists($heroPath) ? 'file://' . $heroPath : '';
        $qrSrc = ($qrPath && file_exists($qrPath)) ? 'file://' . $qrPath : '';
        $issued = $this->formatDate($certificate['issued_at']);
        $expires = $this->formatDate($certificate['expires_at']);
        $score = number_format((float)$certificate['score'], 2, ',', '.');
        $status = $this->computedStatus($certificate);
        $statusText = strtoupper($status);

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 24px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #12365A; margin: 0; background: #f6f8fa; }
    .certificate { border: 8px solid #12365A; border-radius: 18px; background: #fff; min-height: 650px; position: relative; overflow: hidden; }
    .topbar { height: 18px; background: #F28C00; }
    .hero { height: 130px; background: #12365A; overflow: hidden; position: relative; }
    .hero img { width: 100%; opacity: .33; margin-top: -130px; }
    .logo { position: absolute; top: 32px; left: 40px; background: #fff; border-radius: 14px; padding: 10px 16px; max-width: 210px; }
    .logo img { width: 190px; }
    .content { padding: 34px 48px 26px 48px; text-align: center; }
    .eyebrow { color: #F28C00; font-size: 15px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
    h1 { color: #12365A; font-size: 36px; margin: 10px 0 8px 0; }
    h2 { color: #12365A; font-size: 30px; margin: 16px 0 10px 0; }
    .course { font-size: 22px; color: #1F2933; margin: 10px auto; max-width: 850px; }
    .module { font-size: 16px; color: #4b5563; margin-bottom: 18px; }
    .grid { width: 100%; margin-top: 24px; border-collapse: collapse; }
    .grid td { width: 25%; border: 1px solid #e5e7eb; padding: 12px 8px; font-size: 13px; color: #1F2933; vertical-align: top; }
    .label { display: block; color: #6b7280; font-size: 11px; text-transform: uppercase; margin-bottom: 4px; }
    .value { font-weight: bold; color: #12365A; font-size: 14px; }
    .footer { margin-top: 28px; display: table; width: 100%; }
    .left, .right { display: table-cell; vertical-align: bottom; width: 50%; }
    .right { text-align: right; }
    .signature { border-top: 1px solid #12365A; width: 260px; margin: 48px auto 0 auto; padding-top: 8px; font-size: 12px; color: #1F2933; }
    .qr { width: 128px; height: 128px; border: 1px solid #e5e7eb; padding: 5px; background: #fff; }
    .code { font-size: 11px; color: #6b7280; margin-top: 4px; word-break: break-all; }
    .badge { display: inline-block; padding: 8px 14px; border-radius: 999px; background: #12365A; color: #fff; font-size: 12px; margin-top: 10px; }
    .orange { color: #F28C00; }
</style>
</head>
<body>
<div class="certificate">
    <div class="topbar"></div>
    <div class="hero">
        {$this->imageTag($heroSrc, '')}
        <div class="logo">{$this->imageTag($logoSrc, 'KRIAMOS')}</div>
    </div>
    <div class="content">
        <div class="eyebrow">Certificado de capacitación</div>
        <h1>Academia KRIAMOS</h1>
        <div>Certifica que</div>
        <h2>{$this->esc($certificate['full_name'])}</h2>
        <div class="course">aprobó satisfactoriamente el curso <strong>{$this->esc($certificate['course_title'])}</strong></div>
        <div class="module">Módulo: {$this->esc($certificate['module_title'])}</div>
        <span class="badge">Estado: {$this->esc($statusText)}</span>

        <table class="grid">
            <tr>
                <td><span class="label">Cédula</span><span class="value">{$this->esc($certificate['document_number'])}</span></td>
                <td><span class="label">Tipo de usuario</span><span class="value">{$this->esc(ucfirst($certificate['person_type']))}</span></td>
                <td><span class="label">Empresa</span><span class="value">{$this->esc($certificate['company'])}</span></td>
                <td><span class="label">Sede</span><span class="value">{$this->esc($certificate['work_site'])}</span></td>
            </tr>
            <tr>
                <td><span class="label">Nota</span><span class="value">{$score}%</span></td>
                <td><span class="label">Aprobación</span><span class="value">{$issued}</span></td>
                <td><span class="label">Vigencia hasta</span><span class="value">{$expires}</span></td>
                <td><span class="label">Código</span><span class="value">{$this->esc($certificate['certificate_code'])}</span></td>
            </tr>
        </table>

        <div class="footer">
            <div class="left">
                <div class="signature">
                    Responsable de capacitación<br>
                    <strong>KRIAMOS S.A.S.</strong>
                </div>
            </div>
            <div class="right">
                {$this->imageTag($qrSrc, 'QR', 'qr')}
                <div class="code">Verificación: {$this->esc($certificate['verification_token'])}</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
HTML;
    }

    private function certificateSelectSql(): string
    {
        return "SELECT
                cert.*,
                p.full_name,
                p.document_number,
                p.person_type,
                p.company,
                p.work_site,
                p.position,
                p.department,
                p.email,
                p.photo_path,
                c.code AS course_code,
                c.title AS course_title,
                m.title AS module_title,
                ea.score,
                ea.correct_answers,
                ea.total_questions,
                ea.attempt_number,
                ea.started_at,
                ea.finished_at
            FROM certificates cert
            INNER JOIN people p ON p.id = cert.person_id
            INNER JOIN courses c ON c.id = cert.course_id
            INNER JOIN course_modules m ON m.id = cert.module_id
            INNER JOIN exam_attempts ea ON ea.id = cert.exam_attempt_id";
    }

    private function appendDerivedFields(array $certificate): array
    {
        $certificate['computed_status'] = $this->computedStatus($certificate);
        $certificate['verification_url'] = $this->verificationUrl($certificate['verification_token']);
        $certificate['pdf_absolute_path'] = !empty($certificate['pdf_path']) ? $this->absoluteProjectPath($certificate['pdf_path']) : null;
        $certificate['qr_absolute_path'] = !empty($certificate['qr_path']) ? $this->absoluteProjectPath($certificate['qr_path']) : null;
        return $certificate;
    }

    public function computedStatus(array $certificate): string
    {
        if (($certificate['status'] ?? '') === 'revocado') {
            return 'revocado';
        }
        if (!empty($certificate['expires_at']) && strtotime((string)$certificate['expires_at']) < time()) {
            return 'vencido';
        }
        return (string)($certificate['status'] ?? 'vigente');
    }

    private function generateUniqueCode(PDO $pdo): string
    {
        do {
            $code = 'KRI-CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE certificate_code = :code');
            $stmt->execute([':code' => $code]);
        } while ((int)$stmt->fetchColumn() > 0);
        return $code;
    }

    private function generateUniqueToken(PDO $pdo): string
    {
        do {
            $token = bin2hex(random_bytes(20));
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE verification_token = :token');
            $stmt->execute([':token' => $token]);
        } while ((int)$stmt->fetchColumn() > 0);
        return $token;
    }

    private function verificationUrl(string $token): string
    {
        $baseUrl = rtrim((string)app_config('base_url'), '/');
        $path = 'index.php?page=ver_certificado&token=' . urlencode($token);

        if (str_starts_with($baseUrl, 'http://') || str_starts_with($baseUrl, 'https://')) {
            return $baseUrl . '/' . $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $baseUrl . '/' . $path;
    }

    private function fallbackQrSvg(array $certificate, string $verificationUrl): string
    {
        $hash = hash('sha256', $verificationUrl);
        $size = 25;
        $cell = 8;
        $svgSize = $size * $cell;
        $rects = '';
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $i = ($x + $y * $size) % strlen($hash);
                $on = hexdec($hash[$i]) % 2 === 0;
                if ($x < 7 && $y < 7 || $x > 17 && $y < 7 || $x < 7 && $y > 17) {
                    $on = ($x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4));
                }
                if ($on) {
                    $rects .= '<rect x="' . ($x * $cell) . '" y="' . ($y * $cell) . '" width="' . $cell . '" height="' . $cell . '" fill="#12365A"/>';
                }
            }
        }
        $code = $this->esc($certificate['certificate_code']);
        $url = $this->esc($verificationUrl);
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="260" height="300" viewBox="0 0 260 300">
  <rect width="260" height="300" fill="#ffffff"/>
  <g transform="translate(30 20)">
    <rect width="200" height="200" fill="#ffffff" stroke="#12365A" stroke-width="2"/>
    {$rects}
  </g>
  <text x="130" y="240" text-anchor="middle" font-family="Arial" font-size="11" fill="#12365A">{$code}</text>
  <text x="130" y="258" text-anchor="middle" font-family="Arial" font-size="9" fill="#6b7280">Instale composer para QR escaneable</text>
  <text x="130" y="276" text-anchor="middle" font-family="Arial" font-size="8" fill="#6b7280">{$url}</text>
</svg>
SVG;
    }

    private function absoluteProjectPath(string $relativePath): string
    {
        if (str_starts_with($relativePath, '/')) {
            return $relativePath;
        }
        return dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
    }

    public function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }
        return date('d/m/Y', strtotime($date));
    }

    private function esc(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    private function imageTag(string $src, string $alt = '', string $class = ''): string
    {
        if ($src === '') {
            return '';
        }
        $classAttr = $class ? ' class="' . $this->esc($class) . '"' : '';
        return '<img src="' . $this->esc($src) . '" alt="' . $this->esc($alt) . '"' . $classAttr . '>';
    }
}
