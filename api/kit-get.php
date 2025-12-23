<?php
// API: Obtener kit por ID (incluye seguridad)
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT id, nombre, slug, codigo, version, activo, seguridad FROM kits WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$kit) {
        echo json_encode(['ok' => false, 'error' => 'Kit no encontrado']);
        exit;
    }
    echo json_encode(['ok' => true, 'kit' => $kit], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
