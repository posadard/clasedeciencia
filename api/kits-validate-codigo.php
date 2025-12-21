<?php
// API: Validate uniqueness of kits.codigo
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { $data = $_POST; }

    $codigo = isset($data['codigo']) ? trim((string)$data['codigo']) : '';
    $exclude_id = isset($data['exclude_id']) ? (int)$data['exclude_id'] : 0;

    if ($codigo === '') {
        echo json_encode(['ok' => true, 'unique' => null, 'message' => 'Empty codigo']);
        exit;
    }

    // Query count
    if ($exclude_id > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM kits WHERE codigo = ? AND id <> ?');
        $stmt->execute([$codigo, $exclude_id]);
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM kits WHERE codigo = ?');
        $stmt->execute([$codigo]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = (int)($row['total'] ?? 0);

    echo json_encode([
        'ok' => true,
        'unique' => $count === 0,
        'count' => $count
    ], JSON_UNESCAPED_UNICODE);
    echo "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
