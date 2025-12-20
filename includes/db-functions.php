<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

// Consultas preparadas - actualizar según tablas reales.
function get_proyectos($pdo, $filtros = []) {
    if (!$pdo) return [];
    $sql = "SELECT id, nombre, slug, ciclo, dificultad, duracion_minutos FROM proyectos WHERE activo = 1";
    $params = [];
    if (!empty($filtros['ciclo'])) { $sql .= " AND ciclo = ?"; $params[] = (int)$filtros['ciclo']; }
    if (!empty($filtros['grado'])) { $sql .= " AND JSON_CONTAINS(grados, ?)"; $params[] = json_encode((int)$filtros['grado']); }
    if (!empty($filtros['area'])) { $sql .= " AND JSON_CONTAINS(areas, ?)"; $params[] = json_encode($filtros['area']); }
    if (!empty($filtros['dificultad'])) { $sql .= " AND dificultad = ?"; $params[] = $filtros['dificultad']; }
    $sql .= " ORDER BY id DESC LIMIT 200";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('get_proyectos: ' . $e->getMessage());
        return [];
    }
}

function get_proyecto_por_slug($pdo, $slug) {
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT p.*, g.pasos, g.explicacion_cientifica FROM proyectos p LEFT JOIN guias g ON g.proyecto_id = p.id WHERE p.slug = ? AND p.activo = 1 LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('get_proyecto_por_slug: ' . $e->getMessage());
        return null;
    }
}

function buscar_proyectos($pdo, $termino) {
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, slug, ciclo, dificultad FROM proyectos WHERE activo = 1 AND nombre LIKE ?");
        $stmt->execute(['%' . $termino . '%']);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('buscar_proyectos: ' . $e->getMessage());
        return [];
    }
}
?>