<?php
/**
 * Homepage - The Green Almanac
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';
require_once 'includes/materials-functions.php';

$page_title = 'Home';
$page_description = 'Practical chemistry guidance for homesteaders and farmers - Simple, safe methods using common chemicals';
$canonical_url = SITE_URL . '/';

// Get featured articles
$featured_articles = get_featured_articles($pdo, 3);

// Get recent articles
$recent_articles = get_articles($pdo, [], 6);

// Get featured materials
$featured_materials = get_featured_materials($pdo, 6);

// Get all sections for quick links
$sections = get_sections($pdo);

include 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <h2>Welcome to <?= SITE_NAME ?></h2>
        <p class="hero-subtitle">Practical chemistry guidance for homesteaders and farmers</p>
        <p>Simple, safe methods using common chemicals. Low-bandwidth, printable content for the modern homestead.</p>
        <div class="hero-actions">
            <a href="/library.php" class="btn btn-primary">Browse Library</a>
            <a href="https://shop.chemicalstore.com/?utm_source=thegreenalmanac&utm_medium=referral&utm_campaign=homepage_hero" target="_blank" rel="noopener" class="btn btn-secondary">Look for materials</a>
        </div>
    </section>
    
    <!-- Featured Articles -->
    <?php if (!empty($featured_articles)): ?>
    <section class="featured-articles">
        <h2>Featured Articles</h2>
        <div class="articles-grid featured">
            <?php foreach ($featured_articles as $article): ?>
            <article class="article-card featured" data-href="/article.php?slug=<?= h($article['slug']) ?>">
                <a class="card-link" href="/article.php?slug=<?= h($article['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge"><?= h($article['section_name']) ?></span>
                            <span class="difficulty-badge difficulty-<?= h($article['difficulty']) ?>"><?= h(ucfirst($article['difficulty'])) ?></span>
                        </div>
                        <h3><?= h($article['title']) ?></h3>
                        <p class="excerpt"><?= h($article['excerpt']) ?></p>
                        <div class="card-footer">
                            <span class="format"><?= h(ucfirst($article['format'])) ?></span>
                            <span class="read-time"><?= h($article['read_time_min']) ?> min read</span>
                            <span class="date"><?= format_date($article['published_at']) ?></span>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Sections Quick Access -->
    <section class="sections-overview">
        <h2>Browse by Section</h2>
        <div class="sections-grid">
            <?php foreach ($sections as $section): ?>
            <a href="/section.php?slug=<?= h($section['slug']) ?>" class="section-card">
                <h3><?= h($section['name']) ?></h3>
                <p><?= h($section['description']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Featured Materials -->
    <?php if (!empty($featured_materials)): ?>
    <section class="featured-materials">
        <h2>Essential Materials & Equipment</h2>
        <p class="section-subtitle">Quality supplies for your homestead projects</p>
        <div class="materials-grid">
            <?php foreach ($featured_materials as $material): ?>
            <a href="/material.php?slug=<?= h($material['slug']) ?>" class="material-card">
                <div class="material-header">
                    <span class="material-icon"><?= $material['category_icon'] ?></span>
                    <span class="material-category"><?= h($material['category_name']) ?></span>
                </div>
                <h3><?= h($material['common_name']) ?></h3>
                <?php if ($material['technical_name']): ?>
                <p class="material-technical"><?= h($material['technical_name']) ?></p>
                <?php endif; ?>
                <?php if ($material['chemical_formula']): ?>
                <p class="material-formula"><?= h($material['chemical_formula']) ?></p>
                <?php endif; ?>
                <p class="material-description"><?= h(substr($material['description'], 0, 100)) ?>...</p>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="/materials.php" class="btn btn-secondary">View All Materials</a>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Recent Articles -->
    <?php if (!empty($recent_articles)): ?>
    <section class="recent-articles">
        <h2>Recent Articles</h2>
        <div class="articles-grid">
            <?php foreach ($recent_articles as $article): ?>
            <article class="article-card" data-href="/article.php?slug=<?= h($article['slug']) ?>">
                <a class="card-link" href="/article.php?slug=<?= h($article['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge"><?= h($article['section_name']) ?></span>
                            <span class="difficulty-badge difficulty-<?= h($article['difficulty']) ?>"><?= h(ucfirst($article['difficulty'])) ?></span>
                        </div>
                        <h3><?= h($article['title']) ?></h3>
                        <p class="excerpt"><?= h($article['excerpt']) ?></p>
                        <div class="card-footer">
                            <span class="format"><?= h(ucfirst($article['format'])) ?></span>
                            <span class="read-time"><?= h($article['read_time_min']) ?> min read</span>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="/library.php" class="btn btn-secondary">View All Articles</a>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <section class="quick-links">
        <div class="quick-links-grid">
            <div class="quick-link-card">
                <h3>Look for materials</h3>
                <p>Purchase quality chemicals and supplies for your homestead.</p>
                <a href="https://shop.chemicalstore.com/?utm_source=thegreenalmanac&utm_medium=referral&utm_campaign=homepage_quicklink" target="_blank" rel="noopener">Visit Store &rarr;</a>
            </div>
            <div class="quick-link-card">
                <h3>Contact Us</h3>
                <p>Questions or suggestions? Get in touch with our team.</p>
                <a href="/contact.php">Contact &rarr;</a>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
