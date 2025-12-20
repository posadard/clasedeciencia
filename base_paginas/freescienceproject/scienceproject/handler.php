<?php
/**
 * Dynamic Project Router - Independent System
 * Extracts project ID from URL and renders using template
 * Example: /projects/PX001 -> /scienceproject/handler.php?project_id=PX001
 */

// Debug: Show that this file is being called
echo "<!-- DEBUG: scienceproject/handler.php called -->";

// Get project ID from GET parameter (from rewrite rule)
$project_id = isset($_GET['project_id']) ? strtoupper($_GET['project_id']) : null;

// Show debug information
echo "<!-- DEBUG: GET parameters: " . print_r($_GET, true) . " -->";
echo "<!-- DEBUG: REQUEST_URI = '" . $_SERVER['REQUEST_URI'] . "' -->";
echo "<!-- DEBUG: Extracted project_id = '$project_id' -->";

// Validate project ID format (should be like PR101, EX039, SR109, etc.)
if (!$project_id || !preg_match('/^[A-Z]{2}[0-9]{3}$/', $project_id)) {
    echo "<!-- DEBUG: Invalid project ID format, redirecting -->";
    // Invalid project ID format, redirect to projects index
    header('Location: /projects/', true, 301);
    exit;
}

echo "<!-- DEBUG: Valid project ID, proceeding to template -->";

// Test the project data function
require_once __DIR__ . '/../includes/project-helpers.php';
$test_project = getProjectData($project_id);
echo "<!-- DEBUG: getProjectData returned: " . ($test_project ? "FOUND" : "NULL") . " -->";
if ($test_project) {
    echo "<!-- DEBUG: Project title: " . $test_project['title'] . " -->";
} else {
    echo "<!-- DEBUG: Project NOT FOUND - searching for project_id: '$project_id' -->";
    // Let's see what the JS file contains around this project
    $js_content = file_get_contents(__DIR__ . '/../js/science-projects-data.js');
    if (strpos($js_content, $project_id) !== false) {
        echo "<!-- DEBUG: Project ID '$project_id' EXISTS in JS file -->";
    } else {
        echo "<!-- DEBUG: Project ID '$project_id' NOT FOUND in JS file -->";
    }
}

// Include the unified template
include __DIR__ . '/_template.php';
?>