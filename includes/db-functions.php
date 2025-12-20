<?php
/**
 * The Green Almanac - Database Query Functions
 */

/**
 * Clase de Ciencia - Proyecto/Áreas helpers (CdC schema)
 * Lightweight adapters used by homepage and catalog while we migrate.
 */

function cdc_get_featured_proyectos($pdo, $limit = 3) {
    // Adaptado a nuevo esquema: clases + clase_areas
    $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres
            FROM clases c
            LEFT JOIN clase_areas ca ON ca.clase_id = c.id
            LEFT JOIN areas a ON a.id = ca.area_id
            WHERE c.activo = 1 AND c.destacado = 1
            GROUP BY c.id
            ORDER BY c.updated_at DESC
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function cdc_get_recent_proyectos($pdo, $limit = 6) {
    // Adaptado a nuevo esquema: clases + clase_areas
    $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres
            FROM clases c
            LEFT JOIN clase_areas ca ON ca.clase_id = c.id
            LEFT JOIN areas a ON a.id = ca.area_id
            WHERE c.activo = 1
            GROUP BY c.id
            ORDER BY c.updated_at DESC
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function cdc_get_areas($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug, color, descripcion FROM areas ORDER BY nombre");
    return $stmt->fetchAll();
}

/**
 * Get all sections
 */
function get_sections($pdo) {
    // Legacy TGA: sections table no longer exists in CdC.
    // Keep function for backward compatibility but return empty array in CdC context.
    try {
        $stmt = $pdo->query("SELECT * FROM sections ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get section by slug
 */
function get_section_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get all tags
 */
function get_all_tags($pdo) {
    $stmt = $pdo->query("SELECT * FROM tags ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Get featured articles for homepage
 */
function get_featured_articles($pdo, $limit = 3) {
    // Legacy TGA function; CdC uses proyectos. Return empty in CdC to avoid fatal errors.
    try {
        $sql = "SELECT a.*, s.name as section_name, s.slug as section_slug
                FROM articles a
                LEFT JOIN sections s ON a.section_id = s.id
                WHERE a.status = 'published' AND a.featured = 1
                ORDER BY a.published_at DESC
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get articles with filters
 */
function get_articles($pdo, $filters = [], $limit = null, $offset = 0) {
    $params = [];
    $where = ["a.status = 'published'"];
    $joins = ["LEFT JOIN sections s ON a.section_id = s.id"];
    $having = [];
    
    // Section filter
    if (!empty($filters['section'])) {
        $where[] = "s.slug = ?";
        $params[] = $filters['section'];
    }
    
    // Difficulty filter
    if (!empty($filters['difficulty'])) {
        $where[] = "a.difficulty = ?";
        $params[] = $filters['difficulty'];
    }
    
    // Format filter
    if (!empty($filters['format'])) {
        $where[] = "a.format = ?";
        $params[] = $filters['format'];
    }
    
    // Issue filter
    if (!empty($filters['issue'])) {
        $joins[] = "LEFT JOIN issues i ON a.issue_id = i.id";
        $where[] = "i.slug = ?";
        $params[] = $filters['issue'];
    }
    
    // Tags filter (AND logic)
    if (!empty($filters['tags']) && is_array($filters['tags'])) {
        $joins[] = "LEFT JOIN article_tags at ON a.id = at.article_id";
        $joins[] = "LEFT JOIN tags t ON at.tag_id = t.id";
        
        $placeholders = implode(',', array_fill(0, count($filters['tags']), '?'));
        $where[] = "t.slug IN ($placeholders)";
        $params = array_merge($params, $filters['tags']);
        
        $count = count($filters['tags']);
        $having[] = "COUNT(DISTINCT t.slug) = $count";
    }
    
    // Season filter
    if (!empty($filters['season'])) {
        $joins[] = "LEFT JOIN article_seasons ase ON a.id = ase.article_id";
        $where[] = "ase.season = ?";
        $params[] = $filters['season'];
    }
    
    // Build query
    $sql = "SELECT a.*, s.name as section_name, s.slug as section_slug
            FROM articles a
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY a.id";
    
    if (!empty($having)) {
        $sql .= " HAVING " . implode(' AND ', $having);
    }
    
    $sql .= " ORDER BY a.featured DESC, a.published_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Count articles with filters
 */
function count_articles($pdo, $filters = []) {
    $params = [];
    $where = ["a.status = 'published'"];
    $joins = ["LEFT JOIN sections s ON a.section_id = s.id"];
    $having = [];
    
    // Section filter
    if (!empty($filters['section'])) {
        $where[] = "s.slug = ?";
        $params[] = $filters['section'];
    }
    
    // Difficulty filter
    if (!empty($filters['difficulty'])) {
        $where[] = "a.difficulty = ?";
        $params[] = $filters['difficulty'];
    }
    
    // Format filter
    if (!empty($filters['format'])) {
        $where[] = "a.format = ?";
        $params[] = $filters['format'];
    }
    
    // Issue filter
    if (!empty($filters['issue'])) {
        $joins[] = "LEFT JOIN issues i ON a.issue_id = i.id";
        $where[] = "i.slug = ?";
        $params[] = $filters['issue'];
    }
    
    // Tags filter
    if (!empty($filters['tags']) && is_array($filters['tags'])) {
        $joins[] = "LEFT JOIN article_tags at ON a.id = at.article_id";
        $joins[] = "LEFT JOIN tags t ON at.tag_id = t.id";
        
        $placeholders = implode(',', array_fill(0, count($filters['tags']), '?'));
        $where[] = "t.slug IN ($placeholders)";
        $params = array_merge($params, $filters['tags']);
        
        $count = count($filters['tags']);
        $having[] = "COUNT(DISTINCT t.slug) = $count";
    }
    
    // Season filter
    if (!empty($filters['season'])) {
        $joins[] = "LEFT JOIN article_seasons ase ON a.id = ase.article_id";
        $where[] = "ase.season = ?";
        $params[] = $filters['season'];
    }
    
    $sql = "SELECT COUNT(DISTINCT a.id) as total
            FROM articles a
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where);
    
    if (!empty($having)) {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT a.id
                    FROM articles a
                    " . implode(' ', array_unique($joins)) . "
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY a.id
                    HAVING " . implode(' AND ', $having) . "
                ) as filtered";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get article by slug with all relationships
 */
function get_article_by_slug($pdo, $slug) {
    // Get article
    $sql = "SELECT a.*, s.name as section_name, s.slug as section_slug, i.title as issue_title, i.slug as issue_slug
            FROM articles a
            LEFT JOIN sections s ON a.section_id = s.id
            LEFT JOIN issues i ON a.issue_id = i.id
            WHERE a.slug = ? AND a.status = 'published'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    
    if (!$article) return null;
    
    // Get tags
    $stmt = $pdo->prepare("
        SELECT t.* FROM tags t
        JOIN article_tags at ON t.id = at.tag_id
        WHERE at.article_id = ?
    ");
    $stmt->execute([$article['id']]);
    $article['tags'] = $stmt->fetchAll();
    
    // Get seasons
    $stmt = $pdo->prepare("SELECT season FROM article_seasons WHERE article_id = ?");
    $stmt->execute([$article['id']]);
    $article['seasons'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Legacy: CTAs are now replaced by article_materials
    // Kept for backward compatibility but returns empty array
    $article['ctas'] = [];
    
    return $article;
}

/**
 * Get all issues
 */
function get_issues($pdo) {
    $stmt = $pdo->query("SELECT * FROM issues WHERE status = 'published' ORDER BY published_at DESC");
    return $stmt->fetchAll();
}
