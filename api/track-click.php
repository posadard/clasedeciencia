<?php
/**
 * Track Material Click - AJAX Endpoint
 * Called when user clicks on material purchase link or views material
 */

require_once '../config.php';
require_once '../includes/material-tracking.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
$click_type = $_POST['click_type'] ?? 'purchase_link';
$source_page = $_POST['source_page'] ?? '';
$source_article_id = isset($_POST['source_article_id']) ? intval($_POST['source_article_id']) : null;

// Validate
if ($material_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid material ID']);
    exit;
}

// Validate click type (only keep purchase_link and detail_view)
$valid_types = ['purchase_link', 'detail_view'];
if (!in_array($click_type, $valid_types)) {
    $click_type = 'purchase_link';
}

// Track the click
$success = track_material_click(
    $pdo, 
    $material_id, 
    $click_type, 
    $source_page, 
    $source_article_id
);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Click tracked'
    ]);
} else {
    http_response_code(500);
    $err = $GLOBALS['material_tracking_last_error'] ?? null;
    echo json_encode([
        'success' => false,
        'error' => 'Failed to track click',
        'debug' => $err
    ]);
}
