<?php
/**
 * Material Detail Page
 * Individual material information with ChemicalStore integration
 */

require_once 'config.php';
// Este archivo ha sido reemplazado por componente.php
// Redirigimos permanentemente para mantener compatibilidad con enlaces antiguos
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /componentes');
    exit;
}
header('HTTP/1.1 301 Moved Permanently');
header('Location: /componente.php?slug=' . urlencode($slug));
exit;

$page_title = $material['seo_title'] ?: $material['common_name'];
$page_description = $material['seo_description'] ?: substr(strip_tags($material['description']), 0, 160);

// Generate Schema.org JSON-LD based on material category
$category_slug = $material['category_slug'] ?? '';

// Common properties for all schemas
$schema = [
    '@context' => 'https://schema.org',
    'name' => $material['common_name'],
    'url' => SITE_URL . '/material.php?slug=' . $material['slug']
];

// Add description if available
if (!empty($material['description'])) {
    $schema['description'] = strip_tags($material['description']);
}

// Add image if available
if (!empty($material['image_url'])) {
    $schema['image'] = $material['image_url'];
}

// Category-specific schema types and properties
switch ($category_slug) {
    case 'substance':
        // ChemicalSubstance for chemicals, minerals, natural materials
        $schema['@type'] = 'ChemicalSubstance';
        
        // Technical name as alternateName
        if (!empty($material['technical_name'])) {
            $schema['alternateName'] = $material['technical_name'];
        }
        
        // Other names as additional alternateNames
        if (!empty($material['other_names'])) {
            $other_names_decoded = json_decode($material['other_names'], true);
            if (is_array($other_names_decoded) && !empty($other_names_decoded)) {
                if (!isset($schema['alternateName'])) {
                    $schema['alternateName'] = $other_names_decoded;
                } else {
                    // Combine technical name with other names
                    $schema['alternateName'] = array_merge(
                        (array)$schema['alternateName'], 
                        $other_names_decoded
                    );
                }
            }
        }
        
        // Chemical formula → chemicalComposition
        if (!empty($material['chemical_formula'])) {
            $schema['chemicalComposition'] = $material['chemical_formula'];
        }
        
        // CAS number → identifier
        if (!empty($material['cas_number'])) {
            $schema['identifier'] = [
                '@type' => 'PropertyValue',
                'propertyID' => 'CAS',
                'value' => $material['cas_number']
            ];
        }
        
        // Traditional and modern uses → potentialUse
        $potential_uses = [];
        if (!empty($material['traditional_uses'])) {
            $potential_uses[] = [
                '@type' => 'DefinedTerm',
                'name' => 'Traditional Homesteading Uses',
                'description' => strip_tags($material['traditional_uses'])
            ];
        }
        if (!empty($material['modern_applications'])) {
            $potential_uses[] = [
                '@type' => 'DefinedTerm',
                'name' => 'Modern Applications',
                'description' => strip_tags($material['modern_applications'])
            ];
        }
        if (!empty($potential_uses)) {
            $schema['potentialUse'] = $potential_uses;
        }
        
        // Safety information in disambiguatingDescription
        if (!empty($material['safety_notes'])) {
            $schema['disambiguatingDescription'] = 'Safety: ' . strip_tags($material['safety_notes']);
        }
        break;
        
    case 'equipment':
        // Thing for laboratory/measuring equipment
        $schema['@type'] = 'Thing';
        $schema['additionalType'] = !empty($material['subcategory_name']) 
            ? $material['subcategory_name'] 
            : 'Laboratory Equipment';
        
        // Reference to ChemicalStore product
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        
        // Technical specifications
        if (!empty($material['technical_name'])) {
            $schema['alternateName'] = $material['technical_name'];
        }
        break;
        
    case 'tool':
        // Thing for hand tools and implements
        $schema['@type'] = 'Thing';
        $schema['additionalType'] = !empty($material['subcategory_name']) 
            ? $material['subcategory_name'] 
            : 'Tool';
        
        // Reference to ChemicalStore product
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        break;
        
    case 'container':
        // Thing for jars, bottles, vessels
        $schema['@type'] = 'Thing';
        $schema['additionalType'] = !empty($material['subcategory_name']) 
            ? $material['subcategory_name'] 
            : 'Container';
        
        // Reference to ChemicalStore product
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        
        // Add capacity/volume if in specifications
        if (!empty($material['specifications'])) {
            $specs = json_decode($material['specifications'], true);
            if (is_array($specs) && isset($specs['capacity'])) {
                $schema['disambiguatingDescription'] = 'Capacity: ' . $specs['capacity'];
            }
        }
        break;
        
    case 'safety':
        // Thing for personal protective equipment
        $schema['@type'] = 'Thing';
        $schema['additionalType'] = !empty($material['subcategory_name']) 
            ? $material['subcategory_name'] 
            : 'Safety Equipment';
        
        // Reference to ChemicalStore product
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        
        // Safety standards/certifications
        if (!empty($material['specifications'])) {
            $specs = json_decode($material['specifications'], true);
            if (is_array($specs) && isset($specs['standard'])) {
                $schema['disambiguatingDescription'] = 'Standard: ' . $specs['standard'];
            }
        }
        break;
        
    case 'consumable':
        // Thing for single-use items
        $schema['@type'] = 'Thing';
        $schema['additionalType'] = !empty($material['subcategory_name']) 
            ? $material['subcategory_name'] 
            : 'Consumable';
        
        // Reference to ChemicalStore product
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        break;
        
    default:
        // Fallback to generic Thing
        $schema['@type'] = 'Thing';
        if (!empty($material['category_name'])) {
            $schema['additionalType'] = $material['category_name'];
        }
        if (!empty($material['purchase_url'])) {
            $schema['sameAs'] = $material['purchase_url'];
        }
        break;
}

// Create array of schemas to include DigitalDocument
$schemas = [];
$schemas[] = $schema;

// Add DigitalDocument schema (materials are printable as PDF)
$document_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'DigitalDocument',
    'name' => $material['common_name'],
    'description' => strip_tags($material['description'] ?? ''),
    'url' => SITE_URL . '/material.php?slug=' . $material['slug'],
    'encodingFormat' => 'text/html',
    'hasPart' => [
        '@type' => 'DigitalDocument',
        'name' => $material['common_name'] . ' (Printable Version)',
        'encodingFormat' => 'application/pdf',
        'description' => 'Print-optimized version suitable for offline reference'
    ]
];
$schemas[] = $document_schema;

$schema_json = json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
?>

<div class="container material-detail">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / 
        <a href="/componentes">Componentes</a> / 
        <strong>Redirigido…</strong>
    </div>

    <div class="material-top">
        <div class="material-main">
            <h1><?= htmlspecialchars($material['common_name']) ?></h1>
            <!-- Technical/spec table: label (left) / value (right) -->
            <table class="material-specs" aria-labelledby="material-specs-title">
                <caption id="material-specs-title" class="sr-only">Technical specifications for <?= htmlspecialchars($material['common_name']) ?></caption>
                <tbody>
                    <?php if (!empty($material['technical_name'])): ?>
                    <tr>
                        <th scope="row">Technical name</th>
                        <td><?= htmlspecialchars($material['technical_name']) ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($material['other_names_array']) && is_array($material['other_names_array'])): ?>
                    <tr>
                        <th scope="row">Also known as</th>
                        <td><?= htmlspecialchars(implode(', ', array_map('htmlspecialchars', $material['other_names_array']))) ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($material['chemical_formula'])): ?>
                    <tr>
                        <th scope="row">Chemical formula</th>
                        <td class="mono"><?= htmlspecialchars($material['chemical_formula']) ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($material['cas_number'])): ?>
                    <tr>
                        <th scope="row">CAS number</th>
                        <td class="mono"><?= htmlspecialchars($material['cas_number']) ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr>
                        <th scope="row">Category</th>
                        <td><?= htmlspecialchars($material['category_name']) ?></td>
                    </tr>
                    <?php if (!empty($material['subcategory_name'])): ?>
                    <tr>
                        <th scope="row">Subcategory</th>
                        <td><?= htmlspecialchars($material['subcategory_name']) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
            <!-- Description and sections remain below -->
        </div>

        <aside class="product-card">
            <div class="product-badges">
                <?php if ($material['featured']): ?><span class="material-badge badge-featured">⭐ Featured</span><?php endif; ?>
                <?php if ($material['essential']): ?><span class="material-badge badge-essential">✓ Essential</span><?php endif; ?>
                <span class="material-badge"><?= $material['category_icon'] ?> <?= htmlspecialchars($material['category_name']) ?></span>
                <?php if ($material['subcategory_name']): ?><span class="material-badge" style="background:#e9ecef;color:#333"><?= htmlspecialchars($material['subcategory_name']) ?></span><?php endif; ?>
            </div>

            <div class="product-image">
                <?php if (!empty($material['image_url'])): ?>
                    <img src="<?= h($material['image_url']) ?>" alt="<?= h($material['common_name']) ?>" />
                <?php else: ?>
                    <div class="product-image-fallback" aria-hidden="true"><?= $material['category_icon'] ?? '📦' ?> <?= htmlspecialchars($material['category_name'] ?? '') ?></div>
                <?php endif; ?>
            </div>

            <div class="quick-meta">
                <?php if ($material['chemical_formula']): ?><div class="meta"><strong>Formula</strong><div class="value"><?= htmlspecialchars($material['chemical_formula']) ?></div></div><?php endif; ?>
                <?php if ($material['cas_number']): ?><div class="meta"><strong>CAS</strong><div class="value"><?= htmlspecialchars($material['cas_number']) ?></div></div><?php endif; ?>
            </div>

            <?php if ($material['purchase_url']): ?>
            <div class="cta-wrap"><a href="<?= htmlspecialchars($material['purchase_url']) ?>" class="cta" target="_blank" rel="noopener" data-material-id="<?= $material['id'] ?>" data-click-type="info_link" onclick="trackMaterialClick(this)">More info</a></div>
            <?php endif; ?>
        </aside>
    </div>
        
        <!-- Description -->
        <div class="content-section">
            <h2>About This Material</h2>
            <?= nl2br(htmlspecialchars($material['description'])) ?>
        </div>
        
        <!-- Traditional Uses -->
        <?php if ($material['traditional_uses']): ?>
        <div class="content-section">
            <h2>Traditional Homesteading Uses</h2>
            <?= nl2br(htmlspecialchars($material['traditional_uses'])) ?>
        </div>
        <?php endif; ?>
        
        <!-- Modern Applications -->
        <?php if ($material['modern_applications']): ?>
        <div class="content-section">
            <h2>Modern Applications</h2>
            <?= nl2br(htmlspecialchars($material['modern_applications'])) ?>
        </div>
        <?php endif; ?>
        
        <!-- Specifications (for equipment) -->
        <?php if (!empty($material['specifications_array']) && is_array($material['specifications_array'])): ?>
        <div class="content-section">
            <h2>Technical Specifications</h2>
            <div class="specifications">
                <dl>
                    <?php foreach ($material['specifications_array'] as $key => $value): ?>
                    <dt><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</dt>
                    <dd><?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Safety Notes -->
        <?php if ($material['safety_notes']): ?>
        <div class="content-section">
            <div class="safety-warning">
                <h3>⚠️ Safety Information</h3>
                <?= nl2br(htmlspecialchars($material['safety_notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Storage Instructions -->
        <?php if ($material['storage_instructions']): ?>
        <div class="content-section">
            <h2>Storage Instructions</h2>
            <?= nl2br(htmlspecialchars($material['storage_instructions'])) ?>
        </div>
        <?php endif; ?>
        
        <!-- Maintenance (for equipment) -->
        <?php if ($material['maintenance_care']): ?>
        <div class="content-section">
            <h2>Maintenance & Care</h2>
            <?= nl2br(htmlspecialchars($material['maintenance_care'])) ?>
        </div>
        <?php endif; ?>
        
        <!-- Compact Learn More CTA (purchase info) -->
        <?php if ($material['purchase_url']): ?>
        <div class="purchase-section purchase-learnmore">
            <a href="<?= htmlspecialchars($material['purchase_url']) ?>"
               class="material-cta purchase-button"
               target="_blank"
               rel="noopener"
               aria-label="Learn more about <?= htmlspecialchars($material['common_name']) ?>"
               data-material-id="<?= $material['id'] ?>"
               data-click-type="info_link"
               onclick="trackMaterialClick(this)">
                Learn More →
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Related Articles (render as site article-cards for visual consistency) -->
        <?php if (!empty($material['used_in_articles'])): ?>
        <div class="related-articles no-print">
            <h2>Articles Using This Material</h2>
            <div class="articles-grid">
                <?php foreach ($material['used_in_articles'] as $article): ?>
                <article class="article-card" data-href="/article.php?slug=<?= urlencode($article['slug']) ?>">
                    <a class="card-link" href="/article.php?slug=<?= urlencode($article['slug']) ?>">
                        <div class="card-content">
                            <div class="card-meta">
                                <?php if (!empty($article['section_name'])): ?>
                                <span class="section-badge"><?= htmlspecialchars($article['section_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($article['difficulty'])): ?>
                                <span class="difficulty-badge difficulty-<?= htmlspecialchars($article['difficulty']) ?>"><?= htmlspecialchars(ucfirst($article['difficulty'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($article['title']) ?></h3>
                            <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                            <div class="card-footer">
                                <?php if (!empty($article['format'])): ?><span class="format"><?= htmlspecialchars(ucfirst($article['format'])) ?></span><?php endif; ?>
                                <?php if (!empty($article['read_time_min'])): ?><span class="read-time"><?= htmlspecialchars($article['read_time_min']) ?> min read</span><?php endif; ?>
                                <?php if (!empty($article['published_at'])): ?><span class="date"><?= htmlspecialchars(date('F j, Y', strtotime($article['published_at']))) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="/materials.php?category=<?= urlencode($material['category_slug']) ?>" class="back-button">← Back to <?= htmlspecialchars($material['category_name']) ?></a>

    <!-- Print Instructions -->
    <div class="print-instructions no-print">
        <p><strong>Print-Friendly:</strong> This material reference is optimized for printing. Use your browser's print function (Ctrl+P) for a clean, black-and-white copy.</p>
        <button onclick="window.print()" class="btn-pdf" aria-label="Download this material reference as PDF document">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Download PDF Document
        </button>
    </div>

    </div> <!-- .container .material-detail -->

<script>
    // Track material clicks for internal analytics (define only if not provided by main.js)
    if (typeof window.trackMaterialClick !== 'function') {
        function trackMaterialClick(element) {
            const materialId = element.getAttribute('data-material-id');
            const clickType = element.getAttribute('data-click-type') || 'purchase_link';
            const sourcePage = window.location.pathname;

            // Send async request (don't wait for response)
            fetch('/api/track-click.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    material_id: materialId,
                    click_type: clickType,
                    source_page: sourcePage
                })
            }).catch(err => {
                // Silently fail - don't block user
                console.log('Tracking failed:', err);
            });

            // Don't prevent default link behavior
            return true;
        }
        window.trackMaterialClick = trackMaterialClick;
    }
    

    // Track detail view on page load
    window.addEventListener('DOMContentLoaded', function() {
        <?php if ($material): ?>
        fetch('/api/track-click.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                material_id: <?= $material['id'] ?>,
                click_type: 'detail_view',
                source_page: window.location.pathname
            })
        }).catch(err => console.log('View tracking failed:', err));
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
