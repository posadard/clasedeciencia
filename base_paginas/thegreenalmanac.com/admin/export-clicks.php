<?php
/**
 * Export Material Click Data
 * Download click statistics as CSV or JSON
 */

require_once '../config.php';
require_once '../includes/material-tracking.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Get parameters
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
$format = $_GET['format'] ?? 'csv';

// Calculate date range
$end_date = date('Y-m-d 23:59:59');
$start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

// Get data
$data = export_click_data($pdo, $start_date, $end_date);

if (empty($data)) {
    header('HTTP/1.0 404 Not Found');
    exit('No data available for this period');
}

// Export as CSV
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="material-clicks-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, [
        'Date/Time',
        'Material Slug',
        'Material Name',
        'Category',
        'Click Type',
        'Source Page',
        'Source Article Slug',
        'Source Article Title'
    ]);
    
    // Data rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['clicked_at'],
            $row['material_slug'],
            $row['material_name'],
            $row['category'],
            $row['click_type'],
            $row['source_page'],
            $row['source_article_slug'] ?? '',
            $row['source_article_title'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}

// Export as JSON
if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="material-clicks-' . date('Y-m-d') . '.json"');
    
    echo json_encode([
        'export_date' => date('Y-m-d H:i:s'),
        'period' => [
            'start' => $start_date,
            'end' => $end_date,
            'days' => $days
        ],
        'total_records' => count($data),
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit;
}

// Invalid format
header('HTTP/1.0 400 Bad Request');
exit('Invalid format. Use csv or json.');
