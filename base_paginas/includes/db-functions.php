<?php
/**
 * The Green Almanac - Database Query Functions
 */

/**
 * Get all sections
 */
function get_sections($pdo) {
    $stmt = $pdo->query("SELECT * FROM sections ORDER BY sort_order ASC");
    return $stmt->fetchAll();
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
    $sql = "SELECT a.*, s.name as section_name, s.slug as section_slug
            FROM articles a
            LEFT JOIN sections s ON a.section_id = s.id
            WHERE a.status = 'published' AND a.featured = 1
            ORDER BY a.published_at DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
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
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
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
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
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
