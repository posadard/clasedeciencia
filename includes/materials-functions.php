<?php
/**
 * Clase de Ciencia - Materials Functions (CdC)
 * Alineado a esquema CdC: categorias_items y kit_items.
 */

// Categorías
function get_material_categories($pdo) {
    // Nuevo esquema: categorias_items (sin icono/descripcion)
    $stmt = $pdo->query("SELECT id, nombre AS name, slug, NULL AS icon, NULL AS description FROM categorias_items ORDER BY nombre ASC");
    return $stmt->fetchAll();
}

function get_material_category_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT id, nombre AS name, slug, NULL AS icon, NULL AS description FROM categorias_items WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_subcategories_by_category($pdo, $category_id) {
    // CdC: no existe subcategorías
    return [];
}

function get_all_subcategories($pdo) {
    // CdC: no existe subcategorías
    return [];
}

// Listado de materiales
function get_materials($pdo, $filters = [], $limit = null, $offset = 0) {
    $params = [];
    $where = ["1=1"];
    $joins = ["LEFT JOIN categorias_items cm ON m.categoria_id = cm.id"];

    if (!empty($filters['category'])) {
        $where[] = "cm.slug = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(m.nombre_comun LIKE ? OR m.advertencias_seguridad LIKE ?)";
        $term = '%' . $filters['search'] . '%';
        $params[] = $term; $params[] = $term;
    }

    $sql = "SELECT 
                m.id,
                m.slug AS slug,
                m.nombre_comun AS common_name,
                NULL AS technical_name,
                m.advertencias_seguridad AS description,
                m.foto_url,
                cm.nombre AS category_name,
                cm.slug AS category_slug,
                NULL AS category_icon,
                (SELECT COUNT(*) FROM kit_manuals km
                 WHERE km.ambito = 'componente' AND km.item_id = m.id
                 AND km.status IN ('published','discontinued')) AS manuales_count,
                (SELECT COUNT(DISTINCT kc.kit_id) FROM kit_componentes kc
                 JOIN kits k ON k.id = kc.kit_id AND k.activo = 1
                 WHERE kc.item_id = m.id) AS kits_count,
                (SELECT COUNT(DISTINCT ck.clase_id) FROM kit_componentes kc2
                 JOIN kits k2 ON k2.id = kc2.kit_id AND k2.activo = 1
                 JOIN clase_kits ck ON ck.kit_id = k2.id
                 JOIN clases c ON c.id = ck.clase_id AND c.activo = 1
                 WHERE kc2.item_id = m.id) AS clases_count
            FROM kit_items m
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
    $joins = ["LEFT JOIN categorias_items cm ON m.categoria_id = cm.id"];

    if (!empty($filters['category'])) {
        $where[] = "cm.slug = ?";
        $params[] = $filters['category'];
    }
    if (!empty($filters['search'])) {
        $where[] = "(m.nombre_comun LIKE ? OR m.advertencias_seguridad LIKE ?)";
        $term = '%' . $filters['search'] . '%';
        $params[] = $term; $params[] = $term;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM kit_items m
            " . implode(' ', $joins) . "
            WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// Detalle de material
function get_material_by_slug($pdo, $slug) {
    $sql = "SELECT 
                m.id,
                m.slug AS slug,
                m.nombre_comun AS common_name,
                m.advertencias_seguridad AS description,
                m.descripcion_html AS descripcion_html,
                m.foto_url AS foto_url,
                    m.sku AS sku,
                m.categoria_id,
                cm.nombre AS category_name, cm.slug AS category_slug
            FROM kit_items m
            LEFT JOIN categorias_items cm ON m.categoria_id = cm.id
            WHERE m.slug = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$material) return null;
    // Placeholders esperados por plantillas heredadas (no usados aquí, pero mantienen compatibilidad)
    $material['other_names_array'] = $material['other_names_array'] ?? [];
    $material['specifications_array'] = $material['specifications_array'] ?? [];
    $material['gallery_images_array'] = $material['gallery_images_array'] ?? [];
    $material['used_in_articles'] = $material['used_in_articles'] ?? [];
    return $material;
}
