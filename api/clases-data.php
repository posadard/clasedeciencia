<?php
/**
 * API Endpoint: Datos de Clases para Búsqueda
 * Genera JSON dinámico con todas las clases activas
 * Se consume desde JavaScript para búsqueda client-side
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600'); // Cache 1 hora

require_once dirname(__DIR__) . '/config.php';

try {
    // Query optimizado: todas las clases activas con sus relaciones
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.nombre,
            c.slug,
            c.ciclo,
            c.grados,
            c.dificultad,
            c.duracion_minutos,
            c.resumen,
            c.imagen_portada,
            c.destacado,
            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
            GROUP_CONCAT(DISTINCT t.nombre ORDER BY t.nombre SEPARATOR ', ') AS tags
        FROM clases c
        LEFT JOIN clase_areas ca ON ca.clase_id = c.id
        LEFT JOIN areas a ON a.id = ca.area_id
        LEFT JOIN clase_tags ct ON ct.clase_id = c.id
        LEFT JOIN tags t ON t.id = ct.tag_id
        WHERE c.activo = 1
        GROUP BY c.id
        ORDER BY c.destacado DESC, c.orden_popularidad DESC
    ");
    
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar datos para búsqueda
    $proyectos = [];
    
    foreach ($clases as $clase) {
        // Decodificar JSON de grados
        $grados_array = json_decode($clase['grados'], true) ?? [];
        $grados_texto = !empty($grados_array) ? implode('°, ', $grados_array) . '°' : '';
        
        // Obtener nombre del ciclo
        $ciclos_nombres = [
            1 => 'Ciclo 1: Exploración',
            2 => 'Ciclo 2: Experimentación',
            3 => 'Ciclo 3: Análisis'
        ];
        $ciclo_nombre = $ciclos_nombres[$clase['ciclo']] ?? 'Ciclo ' . $clase['ciclo'];
        
        // Formatear dificultad
        $dificultad_map = [
            'facil' => 'Fácil',
            'media' => 'Media',
            'dificil' => 'Difícil'
        ];
        $dificultad = $dificultad_map[$clase['dificultad']] ?? ucfirst($clase['dificultad']);
        
        // Construir objeto para búsqueda
        $proyectos[] = [
            'id' => (int)$clase['id'],
            'title' => $clase['nombre'],
            'url' => '/proyecto.php?slug=' . $clase['slug'],
            'slug' => $clase['slug'],
            'subject' => $clase['areas'] ?? 'General',
            'difficulty' => $dificultad,
            'duration' => $clase['duracion_minutos'] ? $clase['duracion_minutos'] . ' min' : '',
            'description' => $clase['resumen'],
            'ciclo' => (int)$clase['ciclo'],
            'ciclo_nombre' => $ciclo_nombre,
            'grados' => $grados_texto,
            'tags' => $clase['tags'] ?? '',
            'image' => $clase['imagen_portada'] ?? '/assets/images/placeholder-proyecto.jpg',
            'featured' => (bool)$clase['destacado'],
            // Campos para búsqueda (lowercase para comparación)
            'search_text' => strtolower(implode(' ', [
                $clase['nombre'],
                $clase['resumen'],
                $clase['areas'] ?? '',
                $clase['tags'] ?? '',
                $ciclo_nombre,
                $grados_texto,
                $dificultad
            ]))
        ];
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'total' => count($proyectos),
        'proyectos' => $proyectos,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar proyectos',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
    error_log('API clases-data error: ' . $e->getMessage());
}
