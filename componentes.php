<?php
// Página de listado de Componentes (kit_items) con categorías
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';
require_once 'includes/materials-functions.php';

// Filtros
$category = trim($_GET['category'] ?? '');
$q = trim($_GET['q'] ?? '');
$current_page = get_current_page();
$offset = get_offset($current_page);

$page_title = 'Componentes';
$page_description = 'Explora los componentes de los kits por categoría o búsqueda.';
$canonical_url = SITE_URL . (
    $q !== '' && $category === ''
        ? ('/componentes/buscar/' . rawurlencode($q))
        : ($category !== ''
            ? ('/componentes/categoria/' . rawurlencode($category) . ($q!=='' ? ('/buscar/' . rawurlencode($q)) : ''))
            : '/componentes')
);

$categories = get_material_categories($pdo);
$filters = [];
if ($category !== '') { $filters['category'] = $category; }
if ($q !== '') { $filters['search'] = $q; }

$items = get_materials($pdo, $filters, POSTS_PER_PAGE, $offset);
$total = count_materials($pdo, $filters);
$view = isset($_GET['view']) && in_array($_GET['view'], ['cards','rows'], true) ? $_GET['view'] : 'cards';

$item_list_elements = [];
if (!empty($items) && is_array($items)) {
    foreach (array_values($items) as $idx => $it) {
        $item_list_elements[] = [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'url' => SITE_URL . '/' . urlencode((string)($it['slug'] ?? '')),
            'name' => (string)($it['common_name'] ?? 'Componente')
        ];
    }
}

$schema_json = cdc_encode_schema_json([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'ItemList',
            '@id' => $canonical_url . '#itemlist',
            'name' => $page_title,
            'url' => $canonical_url,
            'numberOfItems' => (int)$total,
            'itemListElement' => $item_list_elements
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical_url . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Inicio',
                    'item' => SITE_URL . '/'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Componentes',
                    'item' => SITE_URL . '/componentes'
                ]
            ]
        ]
    ]
]);

include 'includes/header.php';
?>
<div class="container library-page componentes-page view-<?= $view ?>">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Componentes</strong>
    </div>
    <h1><?= $q !== '' || $category !== '' ? 'Componentes filtrados' : 'Componentes disponibles' ?></h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Filtros</h2>
            <form method="get" action="/componentes" class="filters-form">
                <div class="filter-group">
                    <label>Categoría</label>
                    <select name="category">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= h($c['slug']) ?>" <?= $category===$c['slug']?'selected':'' ?>><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="q">Buscar</label>
                    <input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="Nombre o advertencias..." />
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="margin-right:6px;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        Filtrar
                    </button>
                    <a href="/componentes" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </aside>
        <div class="library-content">
            <div class="results-header">
                <?php if ($q !== '' || $category !== ''): ?>
                <div class="search-active-banner">
                    <span class="search-term">🔍 Filtros activos</span>
                    <a href="/componentes" class="clear-search">✕ Ver todos</a>
                </div>
                <?php endif; ?>
                <p class="results-count">
                    Mostrando <?= count($items) ?> de <?= $total ?> componentes
                    <?php if ($total > POSTS_PER_PAGE): ?>
                        (Página <?= get_current_page() ?> de <?= ceil($total / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
                <div class="view-toggle" aria-label="Cambiar vista">
                    <button type="button" class="btn btn-secondary vt-cards" title="Vista tarjetas" onclick="updateView('cards')" <?= $view==='cards'?'disabled':'' ?>>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="margin-right:6px;">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                        </svg>
                        Tarjetas
                    </button>
                    <button type="button" class="btn btn-secondary vt-rows" title="Vista filas" onclick="updateView('rows')" <?= $view==='rows'?'disabled':'' ?>>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="margin-right:6px;">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        Filas
                    </button>
                </div>
            </div>

            <?php if (empty($items)): ?>
            <div class="no-results">
                <p>No hay componentes con los criterios seleccionados.</p>
                <a href="/componentes" class="btn btn-secondary">Ver todos</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($items as $it): ?>
                <article class="article-card" data-href="/<?= h($it['slug']) ?>">
                    <a class="card-link" href="/<?= h($it['slug']) ?>">
                        <?php if (!empty($it['foto_url'])): ?>
                        <div class="card-thumb">
                            <img src="<?= h($it['foto_url']) ?>" alt="<?= h($it['common_name']) ?>" loading="lazy"
                                 onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'card-thumb-placeholder\'>📦</span>';" />
                        </div>
                        <?php endif; ?>
                        <div class="card-content">
                            <div class="card-meta">
                                <span class="section-badge">Componente</span>
                                <?php if (!empty($it['category_name'])): ?>
                                <span class="difficulty-badge" title="Categoría"><?= h($it['category_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($it['manuales_count'])): ?>
                                <span class="badge" title="Manuales disponibles">📘 <?= (int)$it['manuales_count'] ?></span>
                                <?php endif; ?>
                            </div>
                            <h3><?= h($it['common_name']) ?></h3>
                            <?php $desc_text = cdc_format_safety_warning($it['description'] ?? ''); ?>
                            <?php if ($desc_text !== ''): ?>
                            <p class="card-warning">⚠️ <?= h($desc_text) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <span class="card-magnify" aria-hidden="true">🔎</span>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($total > POSTS_PER_PAGE): ?>
                <?= pagination($total, get_current_page(), '/componentes') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
console.log('🔍 [componentes] Filtros:', <?= json_encode($filters) ?>);
console.log('✅ [componentes] Componentes cargados:', <?= count($items) ?>, 'de', <?= (int)$total ?>);

function updateView(view) {
    const url = new URL(window.location.href);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}
</script>
<?php include 'includes/footer.php'; ?>
