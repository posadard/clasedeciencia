<?php
/**
 * Library - Browse all articles with filters
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = 'Library';
$page_description = 'Browse our complete collection of chemistry articles for homesteaders and farmers';
$canonical_url = SITE_URL . '/library.php';

// Get active filters
$filters = get_active_filters();

// Get current page
$current_page = get_current_page();
$offset = get_offset($current_page);

// Get articles with filters
$articles = get_articles($pdo, $filters, POSTS_PER_PAGE, $offset);
$total_articles = count_articles($pdo, $filters);

// Get filter options
$sections = get_sections($pdo);
$all_tags = get_all_tags($pdo);

// Generate Schema.org ItemList for the library catalog
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Library - Chemistry Articles for Homesteaders',
    'description' => $page_description,
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
                '@type' => $article['format'] === 'howto' ? 'HowTo' : ($article['format'] === 'recipe' ? 'Recipe' : 'Article'),
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
        
        // Add keywords if available
        if (!empty($article['section_name'])) {
            if ($article['format'] === 'article' || $article['format'] === 'reference') {
                $item['item']['articleSection'] = $article['section_name'];
            } elseif ($article['format'] === 'recipe') {
                $item['item']['recipeCategory'] = $article['section_name'];
            }
        }
        
        $itemListElements[] = $item;
        $position++;
    }
    
    $schema['itemListElement'] = $itemListElements;
}

$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
?>

<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Home</a> / <strong>Library</strong>
    </div>
    <h1>Library</h1>
    
    <div class="library-layout">
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
            <h2>Filter Articles</h2>
            
            <form method="get" action="/library.php" class="filters-form">
                <!-- Section Filter -->
                <div class="filter-group">
                    <label>Section</label>
                    <select name="section">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $section): ?>
                        <option value="<?= h($section['slug']) ?>" <?= isset($filters['section']) && $filters['section'] === $section['slug'] ? 'selected' : '' ?>>
                            <?= h($section['name']) ?>
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
                    <a href="/library.php" class="btn btn-secondary">Clear All</a>
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
                        // helper to build a querystring without a specific key (resets page)
                        function build_query_without_lib($removeKey, $replacement = null) {
                            $qs = $_GET;
                            unset($qs['page']);
                            if ($replacement === null) {
                                unset($qs[$removeKey]);
                            } else {
                                $qs[$removeKey] = $replacement;
                            }
                            $q = http_build_query($qs);
                            return $q ? ('?' . $q) : '/library.php';
                        }
                        ?>

                        <?php if (isset($filters['section']) && $filters['section']): ?>
                            <span class="filter-item">Section: <?= h($filters['section']) ?> <a href="<?= build_query_without_lib('section') ?>" class="filter-remove" aria-label="Remove section filter">✕</a></span>
                        <?php endif; ?>

                        <?php if (isset($filters['difficulty']) && $filters['difficulty']): ?>
                            <span class="filter-item">Difficulty: <?= h(ucfirst($filters['difficulty'])) ?> <a href="<?= build_query_without_lib('difficulty') ?>" class="filter-remove" aria-label="Remove difficulty filter">✕</a></span>
                        <?php endif; ?>

                        <?php if (isset($filters['format']) && $filters['format']): ?>
                            <span class="filter-item">Format: <?= h(ucfirst($filters['format'])) ?> <a href="<?= build_query_without_lib('format') ?>" class="filter-remove" aria-label="Remove format filter">✕</a></span>
                        <?php endif; ?>

                        <?php if (isset($filters['season']) && $filters['season']): ?>
                            <span class="filter-item">Season: <?= h($filters['season']) ?> <a href="<?= build_query_without_lib('season') ?>" class="filter-remove" aria-label="Remove season filter">✕</a></span>
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
            <?= pagination($total_articles, $current_page, '/library.php') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
