<?php
/**
 * Article Detail Page
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';
require_once 'includes/materials-functions.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /library.php');
    exit;
}

$article = get_article_by_slug($pdo, $slug);

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Get materials for this article
$article_materials = get_article_materials($pdo, $article['id']);

$page_title = $article['seo_title'] ?? $article['title'];
$page_description = $article['seo_description'] ?? $article['excerpt'];
$canonical_url = $article['canonical_url'] ?? (SITE_URL . '/article.php?slug=' . $slug);
$og_type = 'article';
$body_class = 'article-page format-' . $article['format'];

// Generate Schema.org JSON-LD as an array of independent schemas
// First schema: the article itself (HowTo, Recipe, Article, etc.)
$schemas = [];

// Build the main article schema
$article_schema = [
    '@context' => 'https://schema.org',
    'headline' => $article['title'],
    'description' => $article['excerpt'],
    'url' => $canonical_url,
    'datePublished' => date('c', strtotime($article['published_at'])),
    'dateModified' => date('c', strtotime($article['updated_at'])),
    'author' => [
        '@type' => 'Organization',
        'name' => $article['author']
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => SITE_URL
    ]
];

// Add keywords from tags (valid for all types)
if (!empty($article['tags'])) {
    $keywords = array_map(function($tag) {
        return $tag['name'];
    }, $article['tags']);
    $article_schema['keywords'] = implode(', ', $keywords);
}

// Format-specific schema types and properties
switch ($article['format']) {
    case 'howto':
        $article_schema['@type'] = 'HowTo';
        
        // Total time from read_time_min
        if (!empty($article['read_time_min'])) {
            $article_schema['totalTime'] = 'PT' . $article['read_time_min'] . 'M';
        }
        
        // Supply/materials (simple list with URLs to material pages)
        if (!empty($article_materials)) {
            $supplies = [];
            foreach ($article_materials as $mat) {
                $supply_item = [
                    '@type' => 'HowToSupply',
                    'name' => $mat['common_name']
                ];
                
                if (!empty($mat['quantity'])) {
                    $supply_item['requiredQuantity'] = $mat['quantity'];
                }
                
                // Link to material detail page (where full ChemicalSubstance schema lives)
                $supply_item['url'] = SITE_URL . '/material.php?slug=' . $mat['slug'];
                
                $supplies[] = $supply_item;
            }
            $article_schema['supply'] = $supplies;
        }
        
        // Difficulty as skill level
        if (!empty($article['difficulty'])) {
            $article_schema['description'] = $article['excerpt'] . ' Difficulty: ' . ucfirst($article['difficulty']);
        }
        break;
        
    case 'recipe':
        $article_schema['@type'] = 'Recipe';
        
        // Total time from read_time_min
        if (!empty($article['read_time_min'])) {
            $article_schema['totalTime'] = 'PT' . $article['read_time_min'] . 'M';
        }
        
        // Recipe ingredients from materials (as strings for Recipe schema)
        if (!empty($article_materials)) {
            $ingredients = [];
            foreach ($article_materials as $mat) {
                $ingredient = $mat['common_name'];
                if (!empty($mat['quantity'])) {
                    $ingredient = $mat['quantity'] . ' ' . $ingredient;
                }
                $ingredients[] = $ingredient;
            }
            $article_schema['recipeIngredient'] = $ingredients;
        }
        
        // Recipe category from section
        if (!empty($article['section_name'])) {
            $article_schema['recipeCategory'] = $article['section_name'];
        }
        break;
        
    case 'reference':
        $article_schema['@type'] = 'TechArticle';
        
        // Add articleSection (valid for TechArticle)
        if (!empty($article['section_name'])) {
            $article_schema['articleSection'] = $article['section_name'];
        }
        break;
        
    case 'story':
    default:
        $article_schema['@type'] = 'Article';
        
        // Add articleSection (valid for Article)
        if (!empty($article['section_name'])) {
            $article_schema['articleSection'] = $article['section_name'];
        }
        break;
}

// Add the main article schema to the array
$schemas[] = $article_schema;

// Add DigitalDocument schema (articles are printable as PDF)
$document_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'DigitalDocument',
    'name' => $article['title'],
    'description' => $article['excerpt'],
    'url' => $canonical_url,
    'encodingFormat' => 'text/html',
    'datePublished' => date('c', strtotime($article['published_at'])),
    'dateModified' => date('c', strtotime($article['updated_at'])),
    'author' => [
        '@type' => 'Organization',
        'name' => $article['author']
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => SITE_URL
    ]
];

// Indicate PDF availability via print media
$document_schema['hasPart'] = [
    '@type' => 'DigitalDocument',
    'name' => $article['title'] . ' (Printable Version)',
    'encodingFormat' => 'application/pdf',
    'description' => 'Print-optimized version suitable for offline reference'
];

$schemas[] = $document_schema;

// Add independent schemas for each material (ChemicalSubstance or Thing)
if (!empty($article_materials)) {
    foreach ($article_materials as $mat) {
        $material_schema = [
            '@context' => 'https://schema.org',
            'name' => $mat['common_name'],
            'url' => SITE_URL . '/material.php?slug=' . $mat['slug']
        ];
        
        // Determine type based on category
        if ($mat['category_slug'] === 'substance') {
            // ChemicalSubstance schema
            $material_schema['@type'] = 'ChemicalSubstance';
            
            if (!empty($mat['technical_name'])) {
                $material_schema['alternateName'] = $mat['technical_name'];
            }
            
            if (!empty($mat['chemical_formula'])) {
                $material_schema['chemicalComposition'] = $mat['chemical_formula'];
            }
            
            if (!empty($mat['cas_number'])) {
                $material_schema['identifier'] = [
                    '@type' => 'PropertyValue',
                    'propertyID' => 'CAS',
                    'value' => $mat['cas_number']
                ];
            }
            
            // Brief description if available
            if (!empty($mat['description'])) {
                $material_schema['description'] = substr(strip_tags($mat['description']), 0, 200);
            }
        } else {
            // Thing schema for non-chemical materials
            $material_schema['@type'] = 'Thing';
            
            if (!empty($mat['category_name'])) {
                $material_schema['additionalType'] = $mat['category_name'];
            }
            
            if (!empty($mat['description'])) {
                $material_schema['description'] = substr(strip_tags($mat['description']), 0, 200);
            }
        }
        
        // Add image if available
        if (!empty($mat['image_url'])) {
            $material_schema['image'] = $mat['image_url'];
        }
        
        // Add this material schema to the array
        $schemas[] = $material_schema;
    }
}

// Encode the array of schemas
$schema_json = json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
?>

<div class="container article-container">
    <article class="article-detail">
        <!-- Breadcrumb + Compact Title -->
        <div class="breadcrumb">
            <a href="/">Home</a> / <a href="/library.php">Library</a> / <a href="/section.php?slug=<?= h($article['section_slug']) ?>"><?= h($article['section_name']) ?></a> / <strong><?= h($article['title']) ?></strong>
        </div>
        <div class="article-meta">
            <span class="author">By <?= h($article['author']) ?></span>
            <span class="date">Published <?= format_date($article['published_at']) ?></span>
            <span class="read-time"><?= h($article['read_time_min']) ?> min read</span>
        </div>
        
        <!-- Article Content -->
        <div class="article-body">
            <?= $article['body'] ?>
        </div>
        
        <!-- Materials / Products -->
        <?php if (!empty($article_materials)): ?>
        <aside class="article-materials">
            <h2><svg class="admin-icon" aria-hidden="true" width="18" height="18"><use xlink:href="#icon-flask"/></svg> Materials & Supplies</h2>
            <p class="materials-intro"><a href="https://shop.chemicalstore.com" target="_blank" rel="noopener">Look for materials</a></p>
            
            <div class="materials-list">
                <?php foreach ($article_materials as $mat): ?>
                <div class="material-item" data-material-id="<?= (int)$mat['id'] ?>">
                    <div class="material-info">
                        <h3>
                            <a href="/material.php?slug=<?= h($mat['slug']) ?>">
                                <?= h($mat['common_name']) ?>
                            </a>
                        </h3>
                        
                        <?php if (!empty($mat['other_names'])): ?>
                            <?php
                                // other_names stored as JSON array in materials table. Decode for friendly display.
                                $otherNamesArr = [];
                                $rawOther = $mat['other_names'];
                                if (is_string($rawOther)) {
                                    $decoded = json_decode($rawOther, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $otherNamesArr = $decoded;
                                    }
                                }
                            ?>
                            <p class="other-names">
                                <em>Also known as: 
                                    <?php if (!empty($otherNamesArr)): ?>
                                        <span class="other-names-list"><?= h(implode(', ', $otherNamesArr)) ?></span>
                                    <?php else: ?>
                                        <span class="other-names-list"><?= h($rawOther) ?></span>
                                    <?php endif; ?>
                                </em>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($mat['quantity'])): ?>
                            <p class="material-quantity"><strong>Quantity:</strong> <?= h($mat['quantity']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($mat['optional']) && $mat['optional']): ?>
                            <p class="material-optional"><em>Optional</em></p>
                        <?php endif; ?>

                        <?php if (!empty($mat['notes'])): ?>
                            <p class="material-notes">Notes: <?= h($mat['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($mat['abantecart_embed_code'])): ?>
                    <!-- Embedded AbanteCart Widget (clicks inside this area will be tracked) -->
                    <div class="material-widget">
                        <?= $mat['abantecart_embed_code'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>
        <?php endif; ?>
        
        <!-- Tags -->
        <?php if (!empty($article['tags'])): ?>
        <footer class="article-footer">
            <div class="article-tags">
                <strong>Tags:</strong>
                <?php foreach ($article['tags'] as $tag): ?>
                <a href="/library.php?tags=<?= h($tag['slug']) ?>" class="tag"><?= h($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </footer>
        <?php endif; ?>
    </article>
    
    <!-- Print Instructions -->
    <div class="print-instructions no-print">
        <p><strong>Print-Friendly:</strong> This article is optimized for printing. Use your browser's print function (Ctrl+P) for a clean, black-and-white copy.</p>
        <button onclick="window.print()" class="btn-pdf" aria-label="Download this article as PDF document">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Download PDF Document
        </button>
    </div>
</div>

<script>
    // Expose article id for global tracking code
    window.__ARTICLE_ID = <?= json_encode($article['id']) ?>;
</script>

<script>
// Attach click tracking for material widgets and title links without blocking default behavior
document.addEventListener('DOMContentLoaded', function() {
    function sendMaterialClick(materialId, clickType) {
        try {
            // Use navigator.sendBeacon when possible for reliability; fall back to fetch
            var payload = new URLSearchParams();
            payload.append('material_id', materialId);
            payload.append('click_type', clickType);
            payload.append('source_page', window.location.pathname + window.location.search);
            payload.append('source_article_id', window.__ARTICLE_ID || '');

            var url = '/api/track-click.php';
            if (navigator.sendBeacon) {
                // sendBeacon wants a Blob or FormData (use Blob of form-encoded string)
                var blob = new Blob([payload.toString()], { type: 'application/x-www-form-urlencoded' });
                navigator.sendBeacon(url, blob);
            } else {
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString(),
                    keepalive: true
                }).catch(function(){});
            }
        } catch (e) {
            // noop
            console.error('tracking error', e);
        }
    }

    // Attach handlers to each material item
    document.querySelectorAll('.materials-list .material-item').forEach(function(item) {
        var materialId = item.getAttribute('data-material-id');
        if (!materialId) return;

        // Track clicks anywhere inside the widget area (embedded purchase widget)
        var widget = item.querySelector('.material-widget');
        if (widget) {
            // Delegate clicks inside the widget but only treat Add-to-Cart / product-thumb clicks as purchase interactions
            widget.addEventListener('click', function (evt) {
                try {
                    // Look for Add to Cart button or product thumbnail link/button
                    var clicked = evt.target.closest('.abantecart_button, .product_thumb, [data-id][data-href]');
                    if (clicked) {
                        sendMaterialClick(materialId, 'purchase_link');
                    }
                } catch (e) {
                    // ignore errors in tracking
                }
            }, { passive: true });
        }

        // Track detail view when user clicks the material title/link
        var titleLink = item.querySelector('.material-info h3 a');
        if (titleLink) {
            titleLink.addEventListener('click', function () {
                sendMaterialClick(materialId, 'detail_view');
            }, { passive: true });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
