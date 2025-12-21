<?php
// Página de listado de Componentes (kit_items) con categorías
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/materials-functions.php';

// Filtros
$category = trim($_GET['category'] ?? '');
$q = trim($_GET['q'] ?? '');
$current_page = get_current_page();
$offset = get_offset($current_page);

$page_title = 'Componentes';
$page_description = 'Explora los componentes de los kits por categoría o búsqueda.';
$canonical_url = SITE_URL . '/componentes';

$categories = get_material_categories($pdo);
$filters = [];
if ($category !== '') { $filters['category'] = $category; }
if ($q !== '') { $filters['search'] = $q; }

$items = get_materials($pdo, $filters, POSTS_PER_PAGE, $offset);
$total = count_materials($pdo, $filters);

include 'includes/header.php';
?>
<div class="container library-page">
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
                    <button type="submit" class="btn btn-primary">Aplicar</button>
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
                        <div class="card-content">
                            <div class="card-meta">
                                <span class="section-badge">Componente</span>
                                <?php if (!empty($it['category_name'])): ?>
                                <span class="difficulty-badge" title="Categoría"><?= h($it['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3><?= h($it['common_name']) ?></h3>
                            <?php if (!empty($it['description'])): ?>
                            <p class="excerpt"><small><?= h($it['description']) ?></small></p>
                            <?php endif; ?>
                        </div>
                    </a>
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
</script>
<?php include 'includes/footer.php'; ?>
