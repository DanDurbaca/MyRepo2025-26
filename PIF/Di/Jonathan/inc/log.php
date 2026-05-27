<?php
// Simple structured logger (JSON lines) for the project
// Write a timestamped JSON log entry to logs/app.log for debugging/audit
function app_log($level, $message, $context = []) {
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $fn = $logDir . '/app.log';
    $entry = [
        'ts' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'pid' => getmypid()
    ];
    @file_put_contents($fn, json_encode($entry, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
}
