<?php
/**
 * API Endpoint: Datos de Kits para Búsqueda
 * JSON con kits activos para buscador del header
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once dirname(__DIR__) . '/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a base de datos',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
        $sql = "SELECT k.id, k.nombre, k.slug, k.codigo, k.version, k.updated_at, k.activo
            FROM kits k
            WHERE k.activo = 1
            ORDER BY k.updated_at DESC, k.id DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $normalize = function($text) {
        $text = strtolower((string)$text);
        $repl = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u'
        ];
        return strtr($text, $repl);
    };

    $items = [];
    foreach ($rows as $r) {
        $search_parts = [
            $r['nombre'] ?? '',
            $r['codigo'] ?? '',
            'kit',
        ];
        $items[] = [
            'type' => 'kit',
            'id' => (int)($r['id'] ?? 0),
            'title' => $r['nombre'] ?? '',
            'url' => '/' . ($r['slug'] ?? ''),
            'slug' => $r['slug'] ?? '',
            'codigo' => $r['codigo'] ?? '',
            'featured' => false,
            'description' => isset($r['codigo']) && $r['codigo'] !== '' ? ('Código: ' . $r['codigo']) : '',
            'search_text' => $normalize(implode(' ', $search_parts))
        ];
    }

    echo json_encode([
        'success' => true,
        'total' => count($items),
        'kits' => $items,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar kits: ' . $e->getMessage(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    error_log('API kits-data error: ' . $e->getMessage());
}
