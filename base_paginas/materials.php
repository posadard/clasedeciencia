<?php
/**
 * Materials Library - Public Listing (homogenized)
 * Uses common header/footer for consistent site layout
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';
require_once 'includes/materials-functions.php';

// Get raw filter parameters (we'll parse combined category/subcategory below)
$raw_category_choice = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$featured_only = isset($_GET['featured']);
$essential_only = isset($_GET['essential']);

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 24;
$offset = ($page - 1) * $per_page;

// Get categories and subcategories for filter
$categories = get_material_categories($pdo);
$all_subcategories = get_all_subcategories($pdo);

// Parse combined category/subcategory choice. We support values like:
//  - "category:substance" to mean entire category
//  - "subcategory:measuring" to mean a specific subcategory
// For backward compatibility we also accept raw slugs (category or subcategory)
$category_slug = '';
$subcategory_slug = '';
if ($raw_category_choice) {
    if (strpos($raw_category_choice, 'category:') === 0) {
        $category_slug = substr($raw_category_choice, strlen('category:'));
    } elseif (strpos($raw_category_choice, 'subcategory:') === 0) {
        $subcategory_slug = substr($raw_category_choice, strlen('subcategory:'));
    } else {
        // backward compat: see if the slug matches a category
        $maybe_cat = get_material_category_by_slug($pdo, $raw_category_choice);
        if ($maybe_cat) {
            $category_slug = $raw_category_choice;
        } else {
            // see if it matches a subcategory slug
            foreach ($all_subcategories as $sc) {
                if ($sc['slug'] === $raw_category_choice) {
                    $subcategory_slug = $raw_category_choice;
                    // set parent category for UX
                    $category_slug = $sc['category_slug'] ?? '';
                    break;
                }
            }
        }
    }
}

// Get current category info and list of subcategories for the selected category
$current_category = null;
$subcategories = [];
if ($category_slug) {
    $current_category = get_material_category_by_slug($pdo, $category_slug);
    if ($current_category) {
        $subcategories = get_subcategories_by_category($pdo, $current_category['id']);
    }
}

// Build filters array (after parsing category/subcategory)
$filters = [
    'category' => $category_slug,
    'subcategory' => $subcategory_slug,
    'search' => $search_query,
    'featured' => $featured_only,
    'essential' => $essential_only
];

// Get materials
$materials = get_materials($pdo, $filters, $per_page, $offset);
$total_materials = count_materials($pdo, $filters);
$total_pages = ceil($total_materials / $per_page);

// Page title and description
$page_title = 'Materials Library';
$page_description = 'Browse our comprehensive collection of materials, equipment, and tools for homesteading and traditional crafts.';

if ($current_category) {
    $page_title = $current_category['name'] . ' - Materials Library';
    $page_description = $current_category['description'];
}

if ($search_query) {
    $page_title = 'Search Results: ' . htmlspecialchars($search_query);
}

// Generate Schema.org ItemList for the materials catalog
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $page_title,
    'description' => $page_description,
    'url' => SITE_URL . '/materials.php' . ($raw_category_choice ? '?category=' . urlencode($raw_category_choice) : ''),
    'numberOfItems' => $total_materials
];

// Add itemListElement with current page materials
if (!empty($materials)) {
    $itemListElements = [];
    $position = ($page - 1) * $per_page + 1; // Global position
    
    foreach ($materials as $material) {
        $item = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => [
                'name' => $material['common_name'],
                'url' => SITE_URL . '/material.php?slug=' . $material['slug']
            ]
        ];
        
        // Determine type based on category
        if ($material['category_slug'] === 'substance') {
            $item['item']['@type'] = 'ChemicalSubstance';
            
            if (!empty($material['chemical_formula'])) {
                $item['item']['chemicalComposition'] = $material['chemical_formula'];
            }
            
            if (!empty($material['cas_number'])) {
                $item['item']['identifier'] = [
                    '@type' => 'PropertyValue',
                    'propertyID' => 'CAS',
                    'value' => $material['cas_number']
                ];
            }
        } else {
            $item['item']['@type'] = 'Thing';
            
            if (!empty($material['category_name'])) {
                $item['item']['additionalType'] = $material['category_name'];
            }
        }
        
        // Add description if available (truncated)
        if (!empty($material['description'])) {
            $item['item']['description'] = substr(strip_tags($material['description']), 0, 200);
        }
        
        $itemListElements[] = $item;
        $position++;
    }
    
    $schema['itemListElement'] = $itemListElements;
}

$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// Use common header
include 'includes/header.php';
?>

<div class="container materials-page">
    <div class="breadcrumb">
        <a href="/">Home</a> / <strong>Materials</strong>
    </div>
        <h1><?= htmlspecialchars($page_title) ?></h1>

        <div class="library-layout">
            <!-- Sidebar Filters (library-style) -->
            <aside class="filters-sidebar">
                <h2>Filter Materials</h2>
                <form method="get" action="/materials.php" class="filters-form">
                    <div class="filter-group">
                        <label>Category / Type</label>
                        <select name="category">
                            <option value="">All Categories & Types</option>
                            <?php foreach ($categories as $cat_opt): ?>
                                <?php $catVal = 'category:' . $cat_opt['slug']; ?>
                                <option value="<?= htmlspecialchars($catVal) ?>" <?= ($category_slug === $cat_opt['slug'] && empty($subcategory_slug)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat_opt['name']) ?>
                                </option>
                                <?php
                                // show subcategories under this category as indented options
                                $localSubs = array_filter($all_subcategories, function($s) use ($cat_opt){ return $s['category_slug'] == $cat_opt['slug']; });
                                foreach ($localSubs as $ls) {
                                    $subVal = 'subcategory:' . $ls['slug'];
                                ?>
                                    <option value="<?= htmlspecialchars($subVal) ?>" <?= ($subcategory_slug === $ls['slug']) ? 'selected' : '' ?>>
                                        &nbsp;&nbsp;<?= htmlspecialchars($ls['name']) ?>
                                    </option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search materials..." />
                    </div>

                    <div class="filter-group filter-inline">
                        <label for="filter-featured">Featured only</label>
                        <input id="filter-featured" type="checkbox" name="featured" value="1" <?= $featured_only ? 'checked' : '' ?> />
                    </div>

                    <div class="filter-group filter-inline">
                        <label for="filter-essential">Essential only</label>
                        <input id="filter-essential" type="checkbox" name="essential" value="1" <?= $essential_only ? 'checked' : '' ?> />
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="/materials.php" class="btn btn-secondary">Clear All</a>
                    </div>
                </form>

                <!-- active-filters moved to results header -->
            </aside>

            <!-- Materials Content -->
            <div class="library-content">

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">Showing <strong><?= count($materials) ?></strong> of <strong><?= $total_materials ?></strong> materials</div>
            <?php if ($category_slug || $subcategory_slug || $search_query || $featured_only || $essential_only): ?>
            <div class="active-filters compact">
                <strong>Active Filters:</strong>
                <span class="filters-list">
                    <?php
                    // helper to build a querystring without a specific key (resets page)
                    function build_query_without($removeKey, $replacement = null) {
                        $qs = $_GET;
                        unset($qs['page']);
                        if ($replacement === null) {
                            unset($qs[$removeKey]);
                        } else {
                            $qs[$removeKey] = $replacement;
                        }
                        $q = http_build_query($qs);
                        return $q ? ('?' . $q) : '/materials.php';
                    }
                    ?>

                    <?php if ($category_slug || $subcategory_slug): ?>
                        <?php if ($subcategory_slug): ?>
                            <?php
                                // If the raw choice was a subcategory, removing the subcategory should fall back to the parent category (if known)
                                if (!empty($category_slug)) {
                                    $fallback = 'category:' . $category_slug;
                                } else {
                                    $fallback = null;
                                }
                                // build URL: replace 'category' param with fallback or remove it
                                $removeUrl = build_query_without('category', $fallback);
                            ?>
                            <span class="filter-item">Type: <?= htmlspecialchars($subcategory_slug) ?> <a href="<?= $removeUrl ?>" class="filter-remove" aria-label="Remove Type filter">✕</a></span>
                        <?php else: ?>
                            <?php $removeUrl = build_query_without('category'); ?>
                            <span class="filter-item">Category: <?= htmlspecialchars($category_slug) ?> <a href="<?= $removeUrl ?>" class="filter-remove" aria-label="Remove Category filter">✕</a></span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($search_query): ?>
                        <span class="filter-item">Search: <?= htmlspecialchars($search_query) ?> <a href="<?= build_query_without('search') ?>" class="filter-remove" aria-label="Remove search filter">✕</a></span>
                    <?php endif; ?>

                    <?php if ($featured_only): ?>
                        <span class="filter-item">★ Featured only <a href="<?= build_query_without('featured') ?>" class="filter-remove" aria-label="Remove featured filter">✕</a></span>
                    <?php endif; ?>

                    <?php if ($essential_only): ?>
                        <span class="filter-item">✓ Essential only <a href="<?= build_query_without('essential') ?>" class="filter-remove" aria-label="Remove essential filter">✕</a></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Materials Grid -->
        <?php if (empty($materials)): ?>
        <div class="empty-state">
            <h2>No materials found</h2>
            <p>Try adjusting your filters or search query.</p>
            <a href="/materials.php" class="btn btn-primary">View All Materials</a>
        </div>
        <?php else: ?>
        <div class="materials-grid">
            <?php foreach ($materials as $material): ?>
            <a href="/material.php?slug=<?= urlencode($material['slug']) ?>" class="material-card">
                <div class="material-badges">
                    <span class="material-badge badge-<?= $material['category_slug'] ?>"><?= $material['category_icon'] ?> <?= htmlspecialchars($material['category_name']) ?></span>
                    <?php if ($material['featured']): ?><span class="material-badge badge-featured">★ Featured</span><?php endif; ?>
                    <?php if ($material['essential']): ?><span class="material-badge badge-essential">✓ Essential</span><?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($material['common_name']) ?></h3>
                <?php if ($material['technical_name']): ?><div class="material-technical"><?= htmlspecialchars($material['technical_name']) ?></div><?php endif; ?>
                <?php if ($material['chemical_formula']): ?><div class="material-formula"><?= htmlspecialchars($material['chemical_formula']) ?></div><?php endif; ?>
                <div class="material-description"><?= htmlspecialchars($material['description']) ?></div>
                <div class="material-footer">
                    <span class="material-category-icon"><?= $material['category_icon'] ?></span>
                    <span class="material-cta">Learn More →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← Previous</a>
            <?php else: ?>
            <span class="disabled">← Previous</span>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
                <?php elseif ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php elseif (abs($i - $page) == 3): ?>
                <span>...</span>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next →</a>
            <?php else: ?>
            <span class="disabled">Next →</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        </div> <!-- .library-content -->
        </div> <!-- .library-layout -->

    </div>

<?php include 'includes/footer.php'; ?>
