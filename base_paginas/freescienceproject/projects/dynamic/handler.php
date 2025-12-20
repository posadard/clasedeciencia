<?php
/**
 * Dynamic Project Router in subfolder
 * Extracts project ID from URL and renders using template
 * Example: /projects/PX001 -> /projects/dynamic/handler.php?project_id=PX001
 */

// Debug: Show that this file is being called
echo "<!-- DEBUG: dynamic/handler.php called -->";

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
require_once __DIR__ . '/../../includes/project-helpers.php';
$test_project = getProjectData($project_id);
echo "<!-- DEBUG: getProjectData returned: " . ($test_project ? "FOUND" : "NULL") . " -->";
if ($test_project) {
    echo "<!-- DEBUG: Project title: " . $test_project['title'] . " -->";
}

// Include the unified template
include __DIR__ . '/_template.php';
?>