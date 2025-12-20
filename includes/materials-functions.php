<?php
/**
 * The Green Almanac - Materials Database Functions
 * Functions for querying materials, categories, and relationships
 */

/**
<?php
/**
 * Clase de Ciencia - Materials Functions (adapted)
 * Map legacy calls to CdC schema: categorias_materiales, materiales
 */

// Categories
function get_material_categories($pdo) {
    $stmt = $pdo->query("SELECT id, nombre AS name, slug, icono AS icon, descripcion AS description FROM categorias_materiales ORDER BY nombre ASC");
    return $stmt->fetchAll();
}

function get_material_category_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT id, nombre AS name, slug, icono AS icon, descripcion AS description FROM categorias_materiales WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_subcategories_by_category($pdo, $category_id) {
    // CdC: no subcategories table (return empty)
    return [];
}

function get_all_subcategories($pdo) {
    // CdC: no subcategories
    return [];
}

// Materials listing
function get_materials($pdo, $filters = [], $limit = null, $offset = 0) {
    $params = [];
    $where = ["1=1"]; // CdC materiales doesn't have status

    $joins = ["LEFT JOIN categorias_materiales cm ON m.categoria_id = cm.id"];

    if (!empty($filters['category'])) {
        // filters['category'] is slug
        $where[] = "cm.slug = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(m.nombre_comun LIKE ? OR m.nombre_tecnico LIKE ? OR m.descripcion LIKE ?)";
        $term = '%' . $filters['search'] . '%';
        $params[] = $term; $params[] = $term; $params[] = $term;
    }

    $sql = "SELECT 
                m.id,
                m.slug,
                m.nombre_comun AS common_name,
                m.nombre_tecnico AS technical_name,
                m.descripcion AS description,
                cm.nombre AS category_name,
                cm.slug AS category_slug,
                cm.icono AS category_icon,
                NULL AS featured,
                NULL AS essential,
                NULL AS difficulty_level,
                NULL AS chemical_formula,
                NULL AS cas_number
            FROM materiales m
            " . implode(' ', $joins) . "
            WHERE " . implode(' AND ', $where) . "
            ORDER BY cm.nombre ASC, m.nombre_comun ASC";

    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function count_materials($pdo, $filters = []) {
    $params = [];
    $where = ["1=1"];
    $joins = ["LEFT JOIN categorias_materiales cm ON m.categoria_id = cm.id"];

    if (!empty($filters['category'])) {
        $where[] = "cm.slug = ?";
        $params[] = $filters['category'];
    }
    if (!empty($filters['search'])) {
        $where[] = "(m.nombre_comun LIKE ? OR m.nombre_tecnico LIKE ? OR m.descripcion LIKE ?)";
        $term = '%' . $filters['search'] . '%';
        $params[] = $term; $params[] = $term; $params[] = $term;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM materiales m
            " . implode(' ', $joins) . "
            WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// Material detail
function get_material_by_slug($pdo, $slug) {
    $sql = "SELECT 
                m.*, 
                cm.nombre AS category_name, cm.slug AS category_slug, cm.icono AS category_icon
            FROM materiales m
            LEFT JOIN categorias_materiales cm ON m.categoria_id = cm.id
            WHERE m.slug = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $material = $stmt->fetch();
    if (!$material) return null;
    // Placeholder arrays expected by legacy templates
    $material['other_names_array'] = [];
    $material['specifications_array'] = [];
    $material['gallery_images_array'] = [];
    $material['used_in_articles'] = [];
    return $material;
}

// Admin-oriented and legacy functions below could be adapted later as needed.
        description = ?, traditional_uses = ?, modern_applications = ?,
        safety_notes = ?, storage_instructions = ?, maintenance_care = ?,
        specifications = ?, image_url = ?, gallery_images = ?,
        featured = ?, essential = ?, difficulty_level = ?,
        purchase_url = ?, abantecart_embed_code = ?,
        seo_title = ?, seo_description = ?, canonical_url = ?,
        status = ?, published_at = ?,
        updated_at = NOW()
        WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['slug'], $data['common_name'], $data['technical_name'], $data['other_names'],
        $data['chemical_formula'], $data['cas_number'],
        $data['category_id'], $data['subcategory_id'],
        $data['description'], $data['traditional_uses'], $data['modern_applications'],
        $data['safety_notes'], $data['storage_instructions'], $data['maintenance_care'],
        $data['specifications'], $data['image_url'], $data['gallery_images'],
        $data['featured'], $data['essential'], $data['difficulty_level'],
        $data['purchase_url'], $data['abantecart_embed_code'],
        $data['seo_title'], $data['seo_description'], $data['canonical_url'],
        $data['status'], $data['published_at'],
        $id
    ]);
}

/**
 * Delete material
 */
function delete_material($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Add material to article
 */
function add_material_to_article($pdo, $article_id, $material_id, $quantity = null, $optional = false, $notes = null, $sort_order = 0) {
    $sql = "INSERT INTO article_materials (article_id, material_id, quantity, optional, notes, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = ?, optional = ?, notes = ?, sort_order = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $article_id, $material_id, $quantity, $optional, $notes, $sort_order,
        $quantity, $optional, $notes, $sort_order
    ]);
}

/**
 * Remove material from article
 */
function remove_material_from_article($pdo, $article_id, $material_id) {
    $stmt = $pdo->prepare("DELETE FROM article_materials WHERE article_id = ? AND material_id = ?");
    return $stmt->execute([$article_id, $material_id]);
}

/**
 * Get material statistics (for admin dashboard)
 */
function get_material_stats($pdo) {
    $stats = [];
    
    // Total materials
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials");
    $stats['total'] = $stmt->fetch()['total'];
    
    // Published materials
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE status = 'published'");
    $stats['published'] = $stmt->fetch()['total'];
    
    // By category
    $stmt = $pdo->query("
        SELECT mc.name, COUNT(m.id) as count
        FROM material_categories mc
        LEFT JOIN materials m ON mc.id = m.category_id AND m.status = 'published'
        GROUP BY mc.id, mc.name
        ORDER BY mc.sort_order
    ");
    $stats['by_category'] = $stmt->fetchAll();
    
    // Featured materials
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE featured = 1 AND status = 'published'");
    $stats['featured'] = $stmt->fetch()['total'];
    
    return $stats;
}
