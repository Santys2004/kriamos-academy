<?php

namespace App\Models;

class LoginAttempt extends BaseModel
{
    public function record(?string $email, ?string $ip, ?string $userAgent, bool $success, ?string $reason = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (email, ip, user_agent, success, reason, created_at)
            VALUES (:email, :ip, :user_agent, :success, :reason, NOW())
        ");

        $stmt->execute([
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'success' => $success ? 1 : 0,
            'reason' => $reason,
        ]);
    }

    public function isLocked(string $email, ?string $ip, int $maxAttempts, int $lockMinutes): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM login_attempts
            WHERE success = 0
              AND created_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
              AND (LOWER(email) = LOWER(:email) OR ip = :ip)
        ");

        $stmt->bindValue('minutes', $lockMinutes, \PDO::PARAM_INT);
        $stmt->bindValue('email', $email);
        $stmt->bindValue('ip', $ip);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= $maxAttempts;
    }
}
