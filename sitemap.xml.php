<?php
/**
 * Sitemap Generator - Dynamic XML Sitemap
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Homepage -->
    <url>
        <loc><?= h(SITE_URL) ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Library -->
    <url>
        <loc><?= h(SITE_URL) ?>/library.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- Sections -->
    <?php
    $sections = get_sections($pdo);
    foreach ($sections as $section):
    ?>
    <url>
        <loc><?= h(SITE_URL) ?>/section.php?slug=<?= h($section['slug']) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Articles -->
    <?php
    $articles = get_articles($pdo, []);
    foreach ($articles as $article):
    ?>
    <url>
        <loc><?= h(SITE_URL) ?>/article.php?slug=<?= h($article['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($article['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Static Pages -->
    <url>
        <loc><?= h(SITE_URL) ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
</urlset>
