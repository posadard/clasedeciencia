<?php
/**
 * Sitemap Generator Script
 * Generates static sitemap.xml from science-projects-data.js
 * Run this script to update sitemap.xml
 */

echo "Starting sitemap generation...\n";

// Current date for lastmod
$currentDate = date('Y-m-d');

// Start XML content
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

// Static pages first
$staticPages = [
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'weekly', 'title' => 'Homepage'],
    ['url' => '/primary_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly', 'title' => 'Primary Projects (K-4)'],
    ['url' => '/elementary_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly', 'title' => 'Elementary Projects (4-6)'],
    ['url' => '/intermediate_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly', 'title' => 'Intermediate Projects (7-8)'],
    ['url' => '/senior_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly', 'title' => 'Senior Projects (9-12)'],
    ['url' => '/projects/', 'priority' => '0.8', 'changefreq' => 'weekly', 'title' => 'All Projects Catalog'],
];

$xml .= "\n  <!-- Main Pages -->\n";
foreach ($staticPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>https://freescienceproject.com{$page['url']}</loc>\n";
    $xml .= "    <lastmod>$currentDate</lastmod>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

// Read and parse science-projects-data.js
$jsFilePath = __DIR__ . '/js/science-projects-data.js';
if (file_exists($jsFilePath)) {
    $jsContent = file_get_contents($jsFilePath);
    
    // Extract all project data using regex
    preg_match_all('/{ title: "([^"]+)"[^}]*?url: "([^"]+)"[^}]*?subject: "([^"]+)"[^}]*?difficulty: "([^"]+)"[^}]*?description: "([^"]+)"[^}]*?category: "([^"]+)"[^}]*?grade: "([^"]+)"/s', $jsContent, $matches, PREG_SET_ORDER);
    
    if (!empty($matches)) {
        $xml .= "\n  <!-- Dynamic Science Projects -->\n";
        $projectCount = 0;
        
        foreach ($matches as $match) {
            $title = $match[1];
            $url = $match[2];
            $subject = $match[3];
            $difficulty = $match[4];
            $description = $match[5];
            $category = $match[6];
            $grade = $match[7];
            
            // Only include scienceproject URLs
            if (strpos($url, '/scienceproject/') === 0) {
                $xml .= "  <url>\n";
                $xml .= "    <loc>https://freescienceproject.com{$url}</loc>\n";
                $xml .= "    <lastmod>$currentDate</lastmod>\n";
                $xml .= "    <changefreq>monthly</changefreq>\n";
                $xml .= "    <priority>0.8</priority>\n";
                $xml .= "  </url>\n";
                $projectCount++;
            }
        }
        
        echo "Added $projectCount dynamic science projects\n";
    }
}

// Add existing project directories (legacy projects)
$legacyProjects = [
    '/projects/FruitElectricity/' => 'Make Electricity with Fruits',
    '/projects/PotatoElectricity/' => 'Potato Battery Experiment',
    '/projects/airbattery/' => 'Air Battery from Saltwater',
    '/projects/electromagnet/' => 'Build an Electromagnet',
    '/projects/electricity/' => 'Basic Electricity Projects',
    '/projects/electromotor/' => 'Electric Motor Construction',
    '/projects/ElectricBell/' => 'Electric Bell System',
    '/projects/batteries/' => 'Battery Experiments',
    '/projects/KITWG/' => 'Wooden Generator Kit',
    '/projects/CrystalRadio/' => 'Crystal Radio Receiver',
    '/projects/SteamBoat/' => 'Steam Powered Boat',
    '/projects/pulley/' => 'Pulley System Mechanics',
    '/projects/Boat/' => 'Boat Design Projects',
    '/projects/DNAmodel/' => 'DNA Double Helix Model',
    '/projects/MoldExperiment/' => 'Mold Growth Study',
    '/projects/triops/' => 'Triops Life Cycle',
    '/projects/volcano1/' => 'Volcano Model - Classic',
    '/projects/volcano2/' => 'Advanced Volcano Project',
    '/projects/Solar_Science_1/' => 'Solar Energy Experiments',
    '/projects/SolarSystem/' => 'Solar System Model',
    '/projects/magnetlevitation/' => 'Magnetic Levitation',
    '/projects/FloatingRings/' => 'Floating Ring Magnets',
];

$xml .= "\n  <!-- Legacy Project Directories -->\n";
foreach ($legacyProjects as $url => $title) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>https://freescienceproject.com{$url}</loc>\n";
    $xml .= "    <lastmod>$currentDate</lastmod>\n";
    $xml .= "    <changefreq>monthly</changefreq>\n";
    $xml .= "    <priority>0.7</priority>\n";
    $xml .= "  </url>\n";
}

$xml .= "\n</urlset>\n";

// Write to sitemap.xml
$sitemapPath = __DIR__ . '/sitemap.xml';
if (file_put_contents($sitemapPath, $xml)) {
    echo "✅ Sitemap generated successfully at: $sitemapPath\n";
    echo "Total URLs in sitemap: " . substr_count($xml, '<url>') . "\n";
    echo "File size: " . number_format(filesize($sitemapPath)) . " bytes\n";
} else {
    echo "❌ Error: Could not write sitemap.xml\n";
}

echo "Sitemap generation completed!\n";
?>