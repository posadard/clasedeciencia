<?php
/**
 * Dynamic Sitemap Generator
 * Reads science-projects-data.js and generates XML sitemap
 */

// Set XML headers
header('Content-Type: application/xml; charset=utf-8');

// Start XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

// Current date for lastmod
$currentDate = date('Y-m-d');

// Static pages first
$staticPages = [
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['url' => '/primary_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['url' => '/elementary_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['url' => '/intermediate_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['url' => '/senior_projects.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['url' => '/projects/', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['url' => '/privacy-policy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['url' => '/terms-of-service.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>https://freescienceproject.com{$page['url']}</loc>\n";
    echo "    <lastmod>$currentDate</lastmod>\n";
    echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Read and parse science-projects-data.js
$jsFilePath = __DIR__ . '/js/science-projects-data.js';
if (file_exists($jsFilePath)) {
    $jsContent = file_get_contents($jsFilePath);
    
    // Extract all project URLs using regex
    preg_match_all('/url: "([^"]+)"/', $jsContent, $matches);
    
    if (!empty($matches[1])) {
        $projectUrls = array_unique($matches[1]); // Remove duplicates
        
        foreach ($projectUrls as $url) {
            // Include ALL project URLs from the JS file
            if (strpos($url, '/scienceproject/') === 0) {
                // Dynamic scienceproject URLs - higher priority
                echo "  <url>\n";
                echo "    <loc>https://freescienceproject.com{$url}</loc>\n";
                echo "    <lastmod>$currentDate</lastmod>\n";
                echo "    <changefreq>monthly</changefreq>\n";
                echo "    <priority>0.8</priority>\n";
                echo "  </url>\n";
            } elseif (strpos($url, '/projects/') === 0) {
                // Legacy project URLs - slightly lower priority
                echo "  <url>\n";
                echo "    <loc>https://freescienceproject.com{$url}</loc>\n";
                echo "    <lastmod>$currentDate</lastmod>\n";
                echo "    <changefreq>monthly</changefreq>\n";
                echo "    <priority>0.7</priority>\n";
                echo "  </url>\n";
            }
        }
    }
}

// Legacy projects are now automatically included from science-projects-data.js above

echo "</urlset>\n";
?>