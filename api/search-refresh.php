<?php
// Admin-only: bump search data version to force clients to refresh cached search payloads
session_start();
header('Content-Type: application/json; charset=utf-8');

// Require admin login
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    $dir = __DIR__ . '/../assets/cache';
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de cache');
        }
    }
    $file = $dir . '/search-version.txt';
    $version = (string)time();
    if (file_put_contents($file, $version, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo escribir search-version');
    }
    echo json_encode(['ok' => true, 'version' => $version]);
    echo "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
