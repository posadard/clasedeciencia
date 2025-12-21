<?php
/**
 * API Endpoint: Datos de Clases para Búsqueda
 * Genera JSON dinámico con todas las clases activas
 * Se consume desde JavaScript para búsqueda client-side
 */

header('Content-Type: application/json; charset=utf-8');
// Evitar resultados desactualizados en el buscador (forzar fresh fetch)
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Mostrar errores en desarrollo (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en output HTML, solo log

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
            c.objetivo_aprendizaje,
            c.imagen_portada,
            c.destacado,
            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
            GROUP_CONCAT(DISTINCT comp.nombre ORDER BY comp.nombre SEPARATOR ' | ') AS competencias,
            GROUP_CONCAT(DISTINCT ct.tag ORDER BY ct.tag SEPARATOR ', ') AS tags
        FROM clases c
        LEFT JOIN clase_areas ca ON ca.clase_id = c.id
        LEFT JOIN areas a ON a.id = ca.area_id
        LEFT JOIN clase_competencias cc ON cc.clase_id = c.id
        LEFT JOIN competencias comp ON comp.id = cc.competencia_id
        LEFT JOIN clase_tags ct ON ct.clase_id = c.id
        WHERE c.activo = 1
        GROUP BY c.id
        ORDER BY c.destacado DESC, c.orden_popularidad DESC
    ");
    
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($clases === false) {
        throw new Exception('Error al ejecutar query: ' . print_r($pdo->errorInfo(), true));
    }
    
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
        
        // Agregar keywords adicionales para búsqueda
        $keywords = [];
        
        // Keywords de grados (con y sin símbolo)
        if (!empty($grados_array)) {
            foreach ($grados_array as $g) {
                $keywords[] = 'grado ' . $g;
                $keywords[] = $g . ' grado';
                $keywords[] = 'grado' . $g;
            }
        }
        
        // Keywords de ciclo
        $keywords[] = 'ciclo ' . $clase['ciclo'];
        $keywords[] = 'ciclo' . $clase['ciclo'];
        
        // Keywords de competencias MEN (simplificadas)
        $competencias_keywords = [];
        if (!empty($clase['competencias'])) {
            $comps = explode(' | ', $clase['competencias']);
            foreach ($comps as $comp) {
                // Extraer palabras clave de competencias
                if (stripos($comp, 'indagación') !== false || stripos($comp, 'pregunta') !== false) {
                    $competencias_keywords[] = 'indagacion';
                    $competencias_keywords[] = 'preguntas';
                    $competencias_keywords[] = 'investigacion';
                }
                if (stripos($comp, 'explicación') !== false || stripos($comp, 'explico') !== false) {
                    $competencias_keywords[] = 'explicacion';
                    $competencias_keywords[] = 'explicar';
                    $competencias_keywords[] = 'razonamiento';
                }
                if (stripos($comp, 'uso') !== false || stripos($comp, 'aplico') !== false) {
                    $competencias_keywords[] = 'aplicacion';
                    $competencias_keywords[] = 'practica';
                    $competencias_keywords[] = 'cotidiano';
                }
                if (stripos($comp, 'observo') !== false || stripos($comp, 'registro') !== false) {
                    $competencias_keywords[] = 'observacion';
                    $competencias_keywords[] = 'datos';
                    $competencias_keywords[] = 'registro';
                }
                if (stripos($comp, 'modelo') !== false) {
                    $competencias_keywords[] = 'modelado';
                    $competencias_keywords[] = 'representacion';
                }
                if (stripos($comp, 'cálculo') !== false || stripos($comp, 'medición') !== false) {
                    $competencias_keywords[] = 'medicion';
                    $competencias_keywords[] = 'calculo';
                    $competencias_keywords[] = 'matematicas';
                }
            }
        }
        $keywords = array_merge($keywords, array_unique($competencias_keywords));
        
        // Función para normalizar (quitar acentos)
        $normalize = function($text) {
            $text = strtolower($text);
            $text = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
                ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
                $text
            );
            return $text;
        };
        
        // Construir texto de búsqueda normalizado
        $search_parts = [
            $clase['nombre'] ?? '',
            $clase['resumen'] ?? '',
            $clase['objetivo_aprendizaje'] ?? '',
            $clase['areas'] ?? '',
            $clase['tags'] ?? '',
            $ciclo_nombre,
            $grados_texto,
            $dificultad,
            implode(' ', $keywords)
        ];
        
        $search_text_normalized = $normalize(implode(' ', $search_parts));
        
        // Construir objeto para búsqueda
        $proyectos[] = [
            'id' => (int)$clase['id'],
            'title' => $clase['nombre'],
            'url' => '/proyecto.php?slug=' . $clase['slug'],
            'slug' => $clase['slug'],
            'subject' => $clase['areas'] ?? 'General',
            'difficulty' => $dificultad,
            'duration' => $clase['duracion_minutos'] ? $clase['duracion_minutos'] . ' min' : '',
            'description' => $clase['resumen'] ?? '',
            'ciclo' => (int)$clase['ciclo'],
            'ciclo_nombre' => $ciclo_nombre,
            'grados' => $grados_texto,
            'image' => $clase['imagen_portada'] ?? '/assets/images/placeholder-proyecto.jpg',
            'featured' => (bool)$clase['destacado'],
            // Campos para búsqueda (normalizado sin acentos)
            'search_text' => $search_text_normalized
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
        'error' => 'Error al consultar proyectos: ' . $e->getMessage(),
        'code' => $e->getCode(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
    error_log('API clases-data PDO error: ' . $e->getMessage() . ' | Code: ' . $e->getCode());
    
} catch (Exception $e) {
    // Otros errores
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error general: ' . $e->getMessage(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
    error_log('API clases-data error: ' . $e->getMessage());
}
