<?php
/**
 * Section View - Filtered by section
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /library.php');
    exit;
}

$section = get_section_by_slug($pdo, $slug);

if (!$section) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

$page_title = $section['name'];
$page_description = $section['description'];
$canonical_url = SITE_URL . '/section.php?slug=' . $slug;

// Get active filters from querystring, but force section to the current slug
$filters = get_active_filters();
$filters['section'] = $slug;

// Get filter options for sidebar
$sections = get_sections($pdo);
$all_tags = get_all_tags($pdo);

// Get current page
$current_page = get_current_page();
$offset = get_offset($current_page);

// Get articles using filters
$articles = get_articles($pdo, $filters, POSTS_PER_PAGE, $offset);
$total_articles = count_articles($pdo, $filters);

// Generate Schema.org ItemList for the section catalog
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $section['name'] . ' - Articles',
    'description' => $section['description'],
    'url' => $canonical_url,
    'numberOfItems' => $total_articles
];

// Add itemListElement with current page articles
if (!empty($articles)) {
    $itemListElements = [];
    $position = ($current_page - 1) * POSTS_PER_PAGE + 1; // Global position
    
    foreach ($articles as $article) {
        $item = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => [
                '@type' => $article['format'] === 'howto' ? 'HowTo' : ($article['format'] === 'recipe' ? 'Recipe' : ($article['format'] === 'reference' ? 'TechArticle' : 'Article')),
                'name' => $article['title'],
                'url' => SITE_URL . '/article.php?slug=' . $article['slug'],
                'description' => $article['excerpt']
            ]
        ];
        
        // Add additional properties based on format
        if ($article['format'] === 'howto' || $article['format'] === 'recipe') {
            if (!empty($article['read_time_min'])) {
                $item['item']['totalTime'] = 'PT' . $article['read_time_min'] . 'M';
            }
        }
        
        // Add section-specific properties
        if ($article['format'] === 'article' || $article['format'] === 'reference') {
            $item['item']['articleSection'] = $section['name'];
        } elseif ($article['format'] === 'recipe') {
            $item['item']['recipeCategory'] = $section['name'];
        }
        
        $itemListElements[] = $item;
        $position++;
    }
    
    $schema['itemListElement'] = $itemListElements;
}

$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
?>

<div class="container section-page">
    <div class="breadcrumb">
        <a href="/">Home</a> / <a href="/library.php">Library</a> / <strong><?= h($section['name']) ?></strong>
    </div>
    <h1><?= h($section['name']) ?></h1>

    <div class="library-layout">
        <!-- Sidebar Filters (same as library.php) -->
        <aside class="filters-sidebar" aria-label="Filter Articles">
            <h2>Filter Articles</h2>
            <form method="get" action="/section.php" class="filters-form">
                <input type="hidden" name="slug" value="<?= h($slug) ?>">
                <!-- Section Filter (fixed for this page) -->
                <div class="filter-group">
                    <label>Section</label>
                    <select name="section" disabled>
                        <?php foreach ($sections as $s): ?>
                        <option value="<?= h($s['slug']) ?>" <?= $s['slug'] === $slug ? 'selected' : '' ?>>
                            <?= h($s['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Difficulty Filter -->
                <div class="filter-group">
                    <label>Difficulty</label>
                    <select name="difficulty">
                        <option value="">All Levels</option>
                        <option value="basic" <?= isset($filters['difficulty']) && $filters['difficulty'] === 'basic' ? 'selected' : '' ?>>Basic</option>
                        <option value="intermediate" <?= isset($filters['difficulty']) && $filters['difficulty'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= isset($filters['difficulty']) && $filters['difficulty'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>

                <!-- Format Filter -->
                <div class="filter-group">
                    <label>Format</label>
                    <select name="format">
                        <option value="">All Formats</option>
                        <option value="howto" <?= isset($filters['format']) && $filters['format'] === 'howto' ? 'selected' : '' ?>>How-To</option>
                        <option value="reference" <?= isset($filters['format']) && $filters['format'] === 'reference' ? 'selected' : '' ?>>Reference</option>
                        <option value="story" <?= isset($filters['format']) && $filters['format'] === 'story' ? 'selected' : '' ?>>Story</option>
                        <option value="recipe" <?= isset($filters['format']) && $filters['format'] === 'recipe' ? 'selected' : '' ?>>Recipe</option>
                    </select>
                </div>

                <!-- Season Filter -->
                <div class="filter-group">
                    <label>Season</label>
                    <select name="season">
                        <option value="">All Seasons</option>
                        <option value="Spring" <?= isset($filters['season']) && $filters['season'] === 'Spring' ? 'selected' : '' ?>>Spring</option>
                        <option value="Summer" <?= isset($filters['season']) && $filters['season'] === 'Summer' ? 'selected' : '' ?>>Summer</option>
                        <option value="Fall" <?= isset($filters['season']) && $filters['season'] === 'Fall' ? 'selected' : '' ?>>Fall</option>
                        <option value="Winter" <?= isset($filters['season']) && $filters['season'] === 'Winter' ? 'selected' : '' ?>>Winter</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/section.php?slug=<?= h($slug) ?>" class="btn btn-secondary">Clear All</a>
                </div>
            </form>
        </aside>

        <!-- Articles Grid -->
        <div class="library-content">
            <div class="results-header">
                <p class="results-count">
                    Showing <?= count($articles) ?> of <?= $total_articles ?> articles
                    <?php if ($total_articles > POSTS_PER_PAGE): ?>
                    (Page <?= $current_page ?> of <?= ceil($total_articles / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
                <?php if (!empty($filters)): ?>
                <div class="active-filters compact">
                    <strong>Active Filters:</strong>
                    <span class="filters-list">
                        <?php
                        // helper to build a querystring without a specific key while preserving slug and resetting page
                        function build_query_without_section($removeKey, $replacement = null) {
                            $qs = $_GET;
                            // ensure slug stays in query so section remains fixed
                            $qs['slug'] = isset($_GET['slug']) ? $_GET['slug'] : '';
                            unset($qs['page']);
                            if ($replacement === null) {
                                unset($qs[$removeKey]);
                            } else {
                                $qs[$removeKey] = $replacement;
                            }
                            $q = http_build_query($qs);
                            return $q ? ('?' . $q) : ('/section.php?slug=' . urlencode($qs['slug']));
                        }
                        ?>

                        <?php if (isset($filters['difficulty']) && $filters['difficulty']): ?>
                            <span class="filter-item">Difficulty: <?= h(ucfirst($filters['difficulty'])) ?> <a href="<?= build_query_without_section('difficulty') ?>" class="filter-remove" aria-label="Remove difficulty filter">✕</a></span>
                        <?php endif; ?>

                        <?php if (isset($filters['format']) && $filters['format']): ?>
                            <span class="filter-item">Format: <?= h(ucfirst($filters['format'])) ?> <a href="<?= build_query_without_section('format') ?>" class="filter-remove" aria-label="Remove format filter">✕</a></span>
                        <?php endif; ?>

                        <?php if (isset($filters['season']) && $filters['season']): ?>
                            <span class="filter-item">Season: <?= h($filters['season']) ?> <a href="<?= build_query_without_section('season') ?>" class="filter-remove" aria-label="Remove season filter">✕</a></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (empty($articles)): ?>
            <div class="no-results">
                <p>No articles found matching your filters. Try adjusting your selection.</p>
                <a href="/library.php" class="btn btn-secondary">View All Articles</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
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
                                <span class="date"><?= format_date($article['published_at'], 'M j, Y') ?></span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_articles > POSTS_PER_PAGE): ?>
            <?= pagination($total_articles, $current_page, '/section.php?slug=' . urlencode($slug)) ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
