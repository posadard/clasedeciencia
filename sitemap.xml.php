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
    
    <!-- Catálogo -->
    <url>
        <loc><?= h(SITE_URL) ?>/clases</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- Componentes (listado) -->
    <url>
        <loc><?= h(SITE_URL) ?>/componentes</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    
    <!-- Proyectos -->
    <?php
    try {
            $stmtP = $pdo->query("SELECT slug, updated_at FROM clases WHERE activo = 1 ORDER BY orden_popularidad DESC, updated_at DESC");
        $proyectos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
            error_log('Error sitemap clases: ' . $e->getMessage());
        $proyectos = [];
    }
    foreach ($proyectos as $p):
    ?>
    <url>
        <loc><?= h(SITE_URL) ?>/proyecto.php?slug=<?= h($p['slug']) ?></loc>
        <?php if (!empty($p['updated_at'])): ?>
        <lastmod><?= h(date('Y-m-d', strtotime($p['updated_at']))) ?></lastmod>
        <?php endif; ?>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Componentes -->
    <?php
    try {
            $stmtM = $pdo->query("SELECT slug, created_at FROM kit_items ORDER BY created_at DESC");
        $materiales = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
            error_log('Error sitemap kit_items: ' . $e->getMessage());
        $materiales = [];
    }
    foreach ($materiales as $m):
    ?>
    <url>
        <loc><?= h(SITE_URL) ?>/<?= h($m['slug']) ?></loc>
        <?php if (!empty($m['created_at'])): ?>
        <lastmod><?= h(date('Y-m-d', strtotime($m['created_at']))) ?></lastmod>
        <?php endif; ?>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

    <!-- Kits (listado) -->
    <url>
        <loc><?= h(SITE_URL) ?>/kits</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Kits individuales -->
    <?php
    try {
        $stmtK = $pdo->query("SELECT slug, updated_at FROM kits WHERE activo = 1 ORDER BY updated_at DESC, id DESC");
        $kits = $stmtK->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error sitemap kits: ' . $e->getMessage());
        $kits = [];
    }
    foreach ($kits as $k): ?>
    <url>
        <loc><?= h(SITE_URL) ?>/kit-<?= h($k['slug']) ?></loc>
        <?php if (!empty($k['updated_at'])): ?>
        <lastmod><?= h(date('Y-m-d', strtotime($k['updated_at']))) ?></lastmod>
        <?php endif; ?>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Static Pages -->
    <url>
        <loc><?= h(SITE_URL) ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
</urlset>
