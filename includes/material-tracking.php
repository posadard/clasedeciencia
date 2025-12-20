<?php
/**
 * Material Click Tracking Functions
 * Track user interactions with materials for internal analytics
 */

/**
 * Track a material click
 * 
 * @param PDO $pdo Database connection
 * @param int $material_id Material ID
 * @param string $click_type Type: 'purchase_link', 'detail_view'
 * @param string $source_page Current page URL
 * @param int|null $source_article_id Article ID if clicked from article
 * @return bool Success
 */
function track_material_click($pdo, $material_id, $click_type = 'purchase_link', $source_page = null, $source_article_id = null) {
    try {
        // Get source page if not provided
        if ($source_page === null) {
            $source_page = $_SERVER['REQUEST_URI'] ?? '';
        }
        
        // Anonymize IP (remove last octet for IPv4, last 80 bits for IPv6)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $anonymized_ip = anonymize_ip($ip);
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Get referrer
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        $sql = "INSERT INTO material_clicks 
                (material_id, click_type, source_page, source_article_id, user_ip, user_agent, referrer)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $material_id,
            $click_type,
            $source_page,
            $source_article_id,
            $anonymized_ip,
            $user_agent,
            $referrer
        ]);
    } catch (Exception $e) {
        // Silently fail - don't break page if tracking fails
        error_log("Material click tracking error: " . $e->getMessage());
        // Store last error for debugging by API callers
        $GLOBALS['material_tracking_last_error'] = $e->getMessage();
        return false;
    }
}

/**
 * Anonymize IP address for privacy
 * Removes last octet from IPv4, last 80 bits from IPv6
 */
function anonymize_ip($ip) {
    if (empty($ip)) {
        return '';
    }
    
    // IPv4
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        $parts[3] = '0'; // Remove last octet
        return implode('.', $parts);
    }
    
    // IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        // Keep first 48 bits (3 groups), anonymize rest
        return implode(':', array_slice($parts, 0, 3)) . '::0';
    }
    
    return 'unknown';
}

/**
 * Get click statistics for a material
 */
function get_material_click_stats($pdo, $material_id) {
    $sql = "SELECT * FROM material_click_stats WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$material_id]);
    return $stmt->fetch();
}

/**
 * Get top clicked materials
 */
function get_top_clicked_materials($pdo, $limit = 10, $days = 30) {
    $sql = "SELECT 
                m.id, m.slug, m.common_name, m.category_id,
                mc.name as category_name,
                COUNT(mcl.id) as total_clicks,
                SUM(CASE WHEN mcl.click_type = 'purchase_link' THEN 1 ELSE 0 END) as purchase_clicks,
                SUM(CASE WHEN mcl.click_type = 'detail_view' THEN 1 ELSE 0 END) as detail_views,
                COUNT(DISTINCT mcl.user_ip) as unique_visitors,
                MAX(mcl.clicked_at) as last_clicked_at
            FROM materials m
            LEFT JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN material_clicks mcl ON m.id = mcl.material_id 
                AND mcl.clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE m.status = 'published'
            GROUP BY m.id, m.slug, m.common_name, m.category_id, mc.name
            HAVING total_clicks > 0
            ORDER BY total_clicks DESC
            LIMIT ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get click statistics for admin dashboard
 */
function get_click_statistics_summary($pdo, $days = 30) {
    $stats = [];
    
    // Total clicks in period
    $sql = "SELECT COUNT(*) as total FROM material_clicks 
            WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    $stats['total_clicks'] = $stmt->fetch()['total'];
    
    // Clicks by type
    $sql = "SELECT click_type, COUNT(*) as count 
            FROM material_clicks 
            WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY click_type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    // Only keep purchase_link and detail_view in summary
    $allByType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['by_type'] = [
        'purchase_link' => $allByType['purchase_link'] ?? 0,
        'detail_view' => $allByType['detail_view'] ?? 0
    ];
    
    // Clicks by day (last 7 days)
    $sql = "SELECT DATE(clicked_at) as date, COUNT(*) as count
            FROM material_clicks
            WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(clicked_at)
            ORDER BY date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats['by_day'] = $stmt->fetchAll();
    
    // Unique visitors
    $sql = "SELECT COUNT(DISTINCT user_ip) as total FROM material_clicks 
            WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    $stats['unique_visitors'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Get clicks by source article (which articles drive most material clicks)
 */
function get_clicks_by_article($pdo, $limit = 10, $days = 30) {
    $sql = "SELECT 
                a.id, a.title, a.slug,
                COUNT(mc.id) as clicks,
                COUNT(DISTINCT mc.material_id) as unique_materials
            FROM material_clicks mc
            JOIN articles a ON mc.source_article_id = a.id
            WHERE mc.clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY a.id, a.title, a.slug
            ORDER BY clicks DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days, $limit]);
    return $stmt->fetchAll();
}

/**
 * Export click data for analysis (CSV format)
 */
function export_click_data($pdo, $start_date = null, $end_date = null) {
    $where = [];
    $params = [];
    
    if ($start_date) {
        $where[] = "mc.clicked_at >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $where[] = "mc.clicked_at <= ?";
        $params[] = $end_date;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
    
    $sql = "SELECT 
                mc.clicked_at,
                m.slug as material_slug,
                m.common_name as material_name,
                cat.name as category,
                mc.click_type,
                mc.source_page,
                a.slug as source_article_slug,
                a.title as source_article_title
            FROM material_clicks mc
            JOIN materials m ON mc.material_id = m.id
            LEFT JOIN material_categories cat ON m.category_id = cat.id
            LEFT JOIN articles a ON mc.source_article_id = a.id
            $whereClause
            ORDER BY mc.clicked_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
