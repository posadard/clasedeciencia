<?php
// Project-specific variables for header
$page_title = $project_title ?? "Science Project - Free Science Project";
$page_description = $project_description ?? "Step-by-step science project instructions with materials list and safety guidelines.";
$page_keywords = $project_keywords ?? "science project, experiment, STEM education, student project";
$canonical_url = "https://freescienceproject.com" . $_SERVER['REQUEST_URI'];
$content_group1 = $project_grade_level ?? "All Projects";
$content_group2 = $project_subject ?? "General Science";

// Breadcrumbs for project pages
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Projects', 'url' => '/projects/'],
    ['name' => $project_title ?? 'Science Project']
];

// Schema.org markup disabled for legacy projects to avoid duplication
// The new dynamic system (scienceproject/_template.php) handles Course schemas
// Legacy projects rely on the base EducationalOrganization schema from header.php
$schema_markup = null;

// Include header (use absolute path based on this includes directory)
include_once __DIR__ . '/header.php';
?>

<!-- Project content wrapper -->
<div class="project-content">
    <div class="container">
        <!-- Project header intentionally removed by request. Title/badges are handled inline in legacy project pages. -->

        <!-- Project navigation removed per user request -->

        <!-- Main project content -->
        <main class="project-main">
            <!-- Content will be inserted here by individual project pages -->