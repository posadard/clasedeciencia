<?php
/**
 * The Green Almanac - Materials Database Functions
 * Functions for querying materials, categories, and relationships
 */

/**
 * Get all material categories
 */
function get_material_categories($pdo) {
    $stmt = $pdo->query("SELECT * FROM material_categories ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get category by slug
 */
function get_material_category_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM material_categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get subcategories for a category
 */
function get_subcategories_by_category($pdo, $category_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM material_subcategories 
        WHERE category_id = ? 
        ORDER BY sort_order ASC
    ");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll();
}

/**
 * Get all subcategories
 */
function get_all_subcategories($pdo) {
    $stmt = $pdo->query("
        SELECT ms.*, mc.name as category_name, mc.slug as category_slug
        FROM material_subcategories ms
        JOIN material_categories mc ON ms.category_id = mc.id
        ORDER BY mc.sort_order, ms.sort_order
    ");
    return $stmt->fetchAll();
}

/**
 * Get materials with filters
 */
function get_materials($pdo, $filters = [], $limit = null, $offset = 0) {
    $params = [];
    $where = ["m.status = 'published'"];
    $joins = [];
    
    // Category filter
    if (!empty($filters['category'])) {
        if (is_numeric($filters['category'])) {
            $where[] = "m.category_id = ?";
            $params[] = $filters['category'];
        } else {
            // base SQL already LEFT JOINs material_categories as mc, reuse that alias
            $where[] = "mc.slug = ?";
            $params[] = $filters['category'];
        }
    }
    
    // Subcategory filter
    if (!empty($filters['subcategory'])) {
        if (is_numeric($filters['subcategory'])) {
            $where[] = "m.subcategory_id = ?";
            $params[] = $filters['subcategory'];
        } else {
            // base SQL already LEFT JOINs material_subcategories as ms, reuse that alias
            $where[] = "ms.slug = ?";
            $params[] = $filters['subcategory'];
        }
    }
    
    // Search query - prefer FULLTEXT MATCH but fall back to LIKE if the index is missing
    $searchParamPos = null;
    if (!empty($filters['search'])) {
        $searchParamPos = count($params); // remember where the search param was added
        $where[] = "MATCH(m.common_name, m.technical_name, m.description) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $params[] = $filters['search'];
    }
    
    // Featured only
    if (!empty($filters['featured'])) {
        $where[] = "m.featured = 1";
    }
    
    // Essential only
    if (!empty($filters['essential'])) {
        $where[] = "m.essential = 1";
    }
    
    // Difficulty level
    if (!empty($filters['difficulty'])) {
        $where[] = "m.difficulty_level = ?";
        $params[] = $filters['difficulty'];
    }
    
    // Build query
    $sql = "SELECT m.*, 
            mc.name as category_name, mc.slug as category_slug, mc.icon as category_icon,
            ms.name as subcategory_name, ms.slug as subcategory_slug
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.featured DESC, m.common_name ASC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute($params);
    } catch (PDOException $e) {
        // If the FULLTEXT index is missing, fall back to a safe LIKE-based search
        if (!empty($filters['search']) && (stripos($e->getMessage(), 'FULLTEXT') !== false || stripos($e->getMessage(), "Can't find FULLTEXT index") !== false || strpos($e->getMessage(), '1191') !== false)) {
            // Find the MATCH() clause in the where array and replace it with LIKE clauses
            foreach ($where as $wi => $wval) {
                if (stripos($wval, 'MATCH(') !== false) {
                    $where[$wi] = "(m.common_name LIKE ? OR m.technical_name LIKE ? OR m.description LIKE ? )";
                    break;
                }
            }

            // Replace the single MATCH param with three LIKE params (with wildcards)
            $likeTerm = '%' . $filters['search'] . '%';
            if ($searchParamPos !== null) {
                array_splice($params, $searchParamPos, 1, [$likeTerm, $likeTerm, $likeTerm]);
            }

            // Rebuild SQL and re-run (preserve LIMIT/OFFSET if present)
            $sql = "SELECT m.*, 
            mc.name as category_name, mc.slug as category_slug, mc.icon as category_icon,
            ms.name as subcategory_name, ms.slug as subcategory_slug
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.featured DESC, m.common_name ASC";

            if ($limit !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                // limit/offset should already be present at the end of $params, so do not push again
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // Not the expected error - rethrow so calling code can see it
            throw $e;
        }
    }

    return $stmt->fetchAll();
}

/**
 * Count materials with filters
 */
function count_materials($pdo, $filters = []) {
    $params = [];
    $where = ["m.status = 'published'"];
    $joins = [];
    
    if (!empty($filters['category'])) {
        if (is_numeric($filters['category'])) {
            $where[] = "m.category_id = ?";
            $params[] = $filters['category'];
        } else {
            // base SQL already LEFT JOINs material_categories as mc, reuse that alias
            $where[] = "mc.slug = ?";
            $params[] = $filters['category'];
        }
    }
    
    if (!empty($filters['subcategory'])) {
        if (is_numeric($filters['subcategory'])) {
            $where[] = "m.subcategory_id = ?";
            $params[] = $filters['subcategory'];
        } else {
            // base SQL already LEFT JOINs material_subcategories as ms, reuse that alias
            $where[] = "ms.slug = ?";
            $params[] = $filters['subcategory'];
        }
    }
    
    // Search query - prefer FULLTEXT MATCH but fall back to LIKE if the index is missing
    $searchParamPos = null;
    if (!empty($filters['search'])) {
        $searchParamPos = count($params);
        $where[] = "MATCH(m.common_name, m.technical_name, m.description) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $params[] = $filters['search'];
    }
    
    if (!empty($filters['featured'])) {
        $where[] = "m.featured = 1";
    }
    
    if (!empty($filters['essential'])) {
        $where[] = "m.essential = 1";
    }
    
    $sql = "SELECT COUNT(*) as total
        FROM materials m
        LEFT JOIN material_categories mc ON m.category_id = mc.id
        LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
        " . implode(' ', array_unique($joins)) . "
        WHERE " . implode(' AND ', $where);
    
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute($params);
    } catch (PDOException $e) {
        // Fallback when FULLTEXT index is missing
        if (!empty($filters['search']) && (stripos($e->getMessage(), 'FULLTEXT') !== false || stripos($e->getMessage(), "Can't find FULLTEXT index") !== false || strpos($e->getMessage(), '1191') !== false)) {
            foreach ($where as $wi => $wval) {
                if (stripos($wval, 'MATCH(') !== false) {
                    $where[$wi] = "(m.common_name LIKE ? OR m.technical_name LIKE ? OR m.description LIKE ? )";
                    break;
                }
            }

            $likeTerm = '%' . $filters['search'] . '%';
            if ($searchParamPos !== null) {
                array_splice($params, $searchParamPos, 1, [$likeTerm, $likeTerm, $likeTerm]);
            }

            $sql = "SELECT COUNT(*) as total
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            throw $e;
        }
    }

    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get material by slug with full details
 */
function get_material_by_slug($pdo, $slug) {
    $sql = "SELECT m.*, 
            mc.name as category_name, mc.slug as category_slug, mc.icon as category_icon,
            ms.name as subcategory_name, ms.slug as subcategory_slug
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            WHERE m.slug = ? AND m.status = 'published'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $material = $stmt->fetch();
    
    if (!$material) return null;
    
    // Decode JSON fields - always set array keys even if empty
    $material['other_names_array'] = !empty($material['other_names']) 
        ? json_decode($material['other_names'], true) ?: []
        : [];
    
    $material['specifications_array'] = !empty($material['specifications']) 
        ? json_decode($material['specifications'], true) ?: []
        : [];
    
    $material['gallery_images_array'] = !empty($material['gallery_images']) 
        ? json_decode($material['gallery_images'], true) ?: []
        : [];
    
    // Get articles that use this material
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.slug, a.excerpt, s.name as section_name
        FROM article_materials am
        JOIN articles a ON am.article_id = a.id
        LEFT JOIN sections s ON a.section_id = s.id
        WHERE am.material_id = ? AND a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT 10
    ");
    $stmt->execute([$material['id']]);
    $material['used_in_articles'] = $stmt->fetchAll();
    
    return $material;
}

/**
 * Get material by ID (for admin)
 */
function get_material_by_id($pdo, $id) {
    $sql = "SELECT m.*, 
            mc.name as category_name, mc.slug as category_slug,
            ms.name as subcategory_name, ms.slug as subcategory_slug
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            WHERE m.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get materials for an article
 */
function get_article_materials($pdo, $article_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, am.quantity, am.optional, am.notes, am.sort_order,
               mc.name as category_name, mc.slug as category_slug
        FROM article_materials am
        JOIN materials m ON am.material_id = m.id
        LEFT JOIN material_categories mc ON m.category_id = mc.id
        WHERE am.article_id = ?
        ORDER BY am.sort_order ASC, m.common_name ASC
    ");
    $stmt->execute([$article_id]);
    return $stmt->fetchAll();
}

/**
 * Get featured materials for homepage
 */
function get_featured_materials($pdo, $limit = 6) {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               mc.name as category_name, mc.slug as category_slug, mc.icon as category_icon
        FROM materials m
        LEFT JOIN material_categories mc ON m.category_id = mc.id
        WHERE m.status = 'published' AND m.featured = 1
        ORDER BY m.updated_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Search materials by name or description
 */
function search_materials($pdo, $query, $limit = 20) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.slug, m.common_name, m.technical_name,
               mc.name as category_name, mc.icon as category_icon
        FROM materials m
        LEFT JOIN material_categories mc ON m.category_id = mc.id
        WHERE m.status = 'published' 
        AND (m.common_name LIKE ? OR m.technical_name LIKE ? OR m.description LIKE ?)
        ORDER BY m.common_name ASC
        LIMIT ?
    ");
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get materials by category (admin use)
 */
function get_all_materials_by_category($pdo, $category_id = null, $status = null) {
    $params = [];
    $where = [];
    
    if ($category_id) {
        $where[] = "m.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($status) {
        $where[] = "m.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
    
    $sql = "SELECT m.*, 
            mc.name as category_name, mc.slug as category_slug,
            ms.name as subcategory_name
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
            $whereClause
            ORDER BY mc.sort_order, m.common_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Create material
 */
function create_material($pdo, $data) {
    $sql = "INSERT INTO materials (
        slug, common_name, technical_name, other_names,
        chemical_formula, cas_number,
        category_id, subcategory_id,
        description, traditional_uses, modern_applications,
        safety_notes, storage_instructions, maintenance_care,
        specifications, image_url, gallery_images,
        featured, essential, difficulty_level,
        purchase_url, abantecart_embed_code,
        seo_title, seo_description, canonical_url,
        status, published_at,
        created_at, updated_at
    ) VALUES (
        ?, ?, ?, ?,
        ?, ?,
        ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?,
        ?, ?, ?,
        ?, ?,
        NOW(), NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['slug'], $data['common_name'], $data['technical_name'], $data['other_names'],
        $data['chemical_formula'], $data['cas_number'],
        $data['category_id'], $data['subcategory_id'],
        $data['description'], $data['traditional_uses'], $data['modern_applications'],
        $data['safety_notes'], $data['storage_instructions'], $data['maintenance_care'],
        $data['specifications'], $data['image_url'], $data['gallery_images'],
        $data['featured'], $data['essential'], $data['difficulty_level'],
        $data['purchase_url'], $data['abantecart_embed_code'],
        $data['seo_title'], $data['seo_description'], $data['canonical_url'],
        $data['status'], $data['published_at']
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Update material
 */
function update_material($pdo, $id, $data) {
    $sql = "UPDATE materials SET
        slug = ?, common_name = ?, technical_name = ?, other_names = ?,
        chemical_formula = ?, cas_number = ?,
        category_id = ?, subcategory_id = ?,
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
