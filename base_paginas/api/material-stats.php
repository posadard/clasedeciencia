<?php
/**
 * API: material-stats.php
 * Returns JSON summary of material click statistics for admin live polling
 */

require_once '../config.php';
require_once '../includes/material-tracking.php';

header('Content-Type: application/json');

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
// Require admin session for this endpoint
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    // Return a clear JSON payload so client-side can detect auth problems
    echo json_encode(['success' => false, 'auth' => false, 'error' => 'Forbidden - admin session required']);
    exit;
}
if ($days < 1) $days = 30;

try {
    $summary = get_click_statistics_summary($pdo, $days);
    $top_materials = get_top_clicked_materials($pdo, 20, $days);
    $top_articles = get_clicks_by_article($pdo, 10, $days);

    echo json_encode([
        'success' => true,
        'days' => $days,
        'summary' => $summary,
        'top_materials' => $top_materials,
        'top_articles' => $top_articles
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
