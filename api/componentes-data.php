<?php
/**
 * API Endpoint: Datos de Componentes (kit_items) para Búsqueda
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
    $sql = "SELECT 
                m.id,
                m.slug,
                m.nombre_comun,
                m.advertencias_seguridad,
                m.categoria_id,
                c.nombre AS categoria_nombre,
                c.slug AS categoria_slug,
                m.activo
            FROM kit_items m
            LEFT JOIN categorias_items c ON c.id = m.categoria_id
            WHERE m.activo = 1
            ORDER BY c.nombre ASC, m.nombre_comun ASC";
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
        $desc = $r['advertencias_seguridad'] ?? '';
        $cat = $r['categoria_nombre'] ?? '';
        $search_parts = [
            $r['nombre_comun'] ?? '',
            $cat,
            $desc,
            'componente'
        ];
        $items[] = [
            'type' => 'componente',
            'id' => (int)($r['id'] ?? 0),
            'title' => $r['nombre_comun'] ?? '',
            'url' => '/' . ($r['slug'] ?? ''),
            'slug' => $r['slug'] ?? '',
            'categoria' => $cat,
            'categoria_slug' => $r['categoria_slug'] ?? '',
            'featured' => false,
            'description' => $desc,
            'search_text' => $normalize(implode(' ', $search_parts))
        ];
    }

    echo json_encode([
        'success' => true,
        'total' => count($items),
        'componentes' => $items,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar componentes: ' . $e->getMessage(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    error_log('API componentes-data error: ' . $e->getMessage());
}
