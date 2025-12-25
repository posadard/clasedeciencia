<?php
/**
 * API Endpoint: Obtener seguridad del componente por ID
 * Returns kit_items.advertencias_seguridad and basic fields
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__) . '/config.php';

function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    if ($id === '' || !ctype_digit($id)) {
        respond(400, [ 'ok' => false, 'error' => 'Parámetro id inválido' ]);
    }
    $item_id = (int)$id;

    $stmt = $pdo->prepare('SELECT id, nombre_comun, slug, advertencias_seguridad FROM kit_items WHERE id = ? LIMIT 1');
    $stmt->execute([ $item_id ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        respond(404, [ 'ok' => false, 'error' => 'Componente no encontrado' ]);
    }

    $adv = $row['advertencias_seguridad'] ?? '';
    $isJson = false;
    $advJson = null;
    if (is_string($adv) && $adv !== '') {
      try {
        $tmp = json_decode($adv, true);
        if (is_array($tmp)) { $isJson = true; $advJson = $tmp; }
      } catch (Throwable $e) { /* not JSON */ }
    }

    respond(200, [
        'ok' => true,
        'item' => [
            'id' => (int)$row['id'],
            'nombre_comun' => (string)($row['nombre_comun'] ?? ''),
            'slug' => (string)($row['slug'] ?? ''),
            'advertencias_seguridad' => $adv,
            'advertencias_is_json' => $isJson,
            'advertencias_json' => $advJson
        ]
    ]);
} catch (Throwable $e) {
    respond(500, [ 'ok' => false, 'error' => 'Error del servidor: ' . $e->getMessage() ]);
}
