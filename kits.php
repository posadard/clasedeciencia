<?php
// Página de listado de Kits (similar a clases.php pero simplificada)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

// Helpers locales para listar kits con búsqueda y paginación
// Limitar a N palabras y añadir "..."
function cdc_word_limit($text, $max_words = 10) {
    $t = trim(strip_tags((string)$text));
    if ($t === '') return '';
    $words = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    if (!$words || count($words) <= (int)$max_words) return $t;
    $slice = array_slice($words, 0, (int)$max_words);
    return implode(' ', $slice) . '...';
}

function cdc_get_kits($pdo, $search = '', $limit = 12, $offset = 0, $filters = []) {
    $params = [];
    $where = ["k.activo = 1"];

    if ($search !== '') {
        $where[] = "(k.nombre LIKE ? OR k.codigo LIKE ?)";
        $term = '%' . $search . '%';
        $params[] = $term; $params[] = $term;
    }

    // Filtros adicionales
    $edad = isset($filters['edad']) ? (int)$filters['edad'] : null;
    $con_video = !empty($filters['con_video']);
    $con_imagen = !empty($filters['con_imagen']);
    $version_min = isset($filters['version_min']) && $filters['version_min'] !== '' ? (int)$filters['version_min'] : null;
    $updated_days = isset($filters['updated_days']) && $filters['updated_days'] !== '' ? (int)$filters['updated_days'] : null;

    if ($edad !== null && $edad > 0) {
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(k.seguridad, '$.edad_min')) AS UNSIGNED) <= ?";
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(k.seguridad, '$.edad_max')) AS UNSIGNED) >= ?";
        $params[] = $edad; $params[] = $edad;
    }
    if ($con_video) { $where[] = "k.video_portada IS NOT NULL AND k.video_portada <> ''"; }
    if ($con_imagen) { $where[] = "k.imagen_portada IS NOT NULL AND k.imagen_portada <> ''"; }
    if ($version_min !== null) { $where[] = "CAST(k.version AS UNSIGNED) >= ?"; $params[] = $version_min; }
    if ($updated_days !== null && $updated_days > 0) { $where[] = "k.updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"; $params[] = $updated_days; }

    $sql = "SELECT 
                k.id, k.nombre, k.slug, k.codigo, k.version, k.updated_at,
                k.resumen, k.seguridad,
                (SELECT COUNT(*) FROM kit_componentes kc WHERE kc.kit_id = k.id) AS componentes_count,
                (SELECT COUNT(*) FROM clase_kits ck WHERE ck.kit_id = k.id) AS clases_count
            FROM kits k
            WHERE " . implode(' AND ', $where) . "
            ORDER BY k.updated_at DESC, k.id DESC
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit; $params[] = (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cdc_count_kits($pdo, $search = '', $filters = []) {
    $params = [];
    $where = ["activo = 1"];
    if ($search !== '') {
        $where[] = "(nombre LIKE ? OR codigo LIKE ?)";
        $term = '%' . $search . '%';
        $params[] = $term; $params[] = $term;
    }
    // Filtros adicionales (idénticos a cdc_get_kits)
    $edad = isset($filters['edad']) ? (int)$filters['edad'] : null;
    $con_video = !empty($filters['con_video']);
    $con_imagen = !empty($filters['con_imagen']);
    $version_min = isset($filters['version_min']) && $filters['version_min'] !== '' ? (int)$filters['version_min'] : null;
    $updated_days = isset($filters['updated_days']) && $filters['updated_days'] !== '' ? (int)$filters['updated_days'] : null;

    if ($edad !== null && $edad > 0) {
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(seguridad, '$.edad_min')) AS UNSIGNED) <= ?";
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(seguridad, '$.edad_max')) AS UNSIGNED) >= ?";
        $params[] = $edad; $params[] = $edad;
    }
    if ($con_video) { $where[] = "video_portada IS NOT NULL AND video_portada <> ''"; }
    if ($con_imagen) { $where[] = "imagen_portada IS NOT NULL AND imagen_portada <> ''"; }
    if ($version_min !== null) { $where[] = "CAST(version AS UNSIGNED) >= ?"; $params[] = $version_min; }
    if ($updated_days !== null && $updated_days > 0) { $where[] = "updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"; $params[] = $updated_days; }
    $sql = "SELECT COUNT(*) AS total FROM kits WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// Estado de interfaz
$q = trim($_GET['q'] ?? '');
// Nuevos filtros
$filters = [
    'edad' => isset($_GET['edad']) ? (int)$_GET['edad'] : null,
    'con_video' => isset($_GET['con_video']) && $_GET['con_video'] === '1',
    'con_imagen' => isset($_GET['con_imagen']) && $_GET['con_imagen'] === '1',
    'version_min' => isset($_GET['version_min']) ? (int)$_GET['version_min'] : null,
    'updated_days' => isset($_GET['updated_days']) ? (int)$_GET['updated_days'] : null,
];
$current_page = get_current_page();
$offset = get_offset($current_page);

$page_title = 'Kits';
$page_description = 'Explora los kits de Clase de Ciencia con sus componentes y clases relacionadas.';
$canonical_url = SITE_URL . '/kits';

$kits = cdc_get_kits($pdo, $q, POSTS_PER_PAGE, $offset, $filters);
$total = cdc_count_kits($pdo, $q, $filters);

include 'includes/header.php';
?>
<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Kits</strong>
    </div>
    <h1><?= $q !== '' ? 'Resultados de búsqueda de kits' : 'Kits disponibles' ?></h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Búsqueda</h2>
            <form method="get" action="/kits" class="filters-form">
                <div class="filter-group">
                    <label for="q">Nombre o código</label>
                    <input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="Buscar kits..." />
                </div>
                <div class="filter-group">
                    <label for="edad">Edad objetivo</label>
                    <input type="number" id="edad" name="edad" min="1" max="99" value="<?= h((string)($filters['edad'] ?? '')) ?>" placeholder="Ej: 12" />
                </div>
                <div class="filter-group">
                    <label>Medios</label>
                    <div class="checkboxes">
                        <label><input type="checkbox" name="con_video" value="1" <?= !empty($filters['con_video'])?'checked':'' ?> /> Con video</label>
                        <label><input type="checkbox" name="con_imagen" value="1" <?= !empty($filters['con_imagen'])?'checked':'' ?> /> Con imagen</label>
                    </div>
                </div>
                <div class="filter-group">
                    <label for="version_min">Versión mínima</label>
                    <input type="number" id="version_min" name="version_min" min="0" value="<?= h((string)($filters['version_min'] ?? '')) ?>" placeholder="Ej: 2" />
                </div>
                <div class="filter-group">
                    <label for="updated_days">Actualizados en</label>
                    <select id="updated_days" name="updated_days">
                        <?php $ud = (int)($filters['updated_days'] ?? 0); ?>
                        <option value="">Cualquier fecha</option>
                        <option value="30" <?= $ud===30?'selected':'' ?>>Últimos 30 días</option>
                        <option value="90" <?= $ud===90?'selected':'' ?>>Últimos 90 días</option>
                        <option value="180" <?= $ud===180?'selected':'' ?>>Últimos 6 meses</option>
                        <option value="365" <?= $ud===365?'selected':'' ?>>Último año</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="/kits" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </aside>
        <div class="library-content">
            <div class="results-header">
                <?php if ($q !== ''): ?>
                <div class="search-active-banner">
                    <span class="search-term">🔍 Resultados para: <strong><?= h($q) ?></strong></span>
                    <a href="/kits" class="clear-search">✕ Ver todos</a>
                </div>
                <?php endif; ?>
                <p class="results-count">
                    Mostrando <?= count($kits) ?> de <?= $total ?> kits
                    <?php if ($total > POSTS_PER_PAGE): ?>
                        (Página <?= get_current_page() ?> de <?= ceil($total / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
            </div>

            <?php if (empty($kits)): ?>
            <div class="no-results">
                <p>No hay kits con los criterios seleccionados.</p>
                <a href="/kits" class="btn btn-secondary">Ver todos</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($kits as $k): ?>
                <article class="article-card" data-href="/<?= h($k['slug']) ?>">
                    <a class="card-link" href="/<?= h($k['slug']) ?>">
                        <div class="card-content">
                            <div class="card-meta">
                                <span class="section-badge">Kit</span>
                                <?php if (!empty($k['codigo'])): ?>
                                <span class="difficulty-badge">Código: <?= h($k['codigo']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($k['version'])): ?>
                                <span class="badge">v<?= h($k['version']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3><?= h($k['nombre']) ?></h3>
                            <?php if (!empty($k['resumen'])): ?>
                            <p class="excerpt"><small>
                                <span class="text-trunc"><?= h(cdc_word_limit($k['resumen'], 10)) ?></span>
                                <span class="text-full"><?= h($k['resumen']) ?></span>
                            </small></p>
                            <?php endif; ?>
                            <div class="card-footer">
                                <span class="area">Componentes: <?= (int)$k['componentes_count'] ?></span>
                                <span class="age">Clases: <?= (int)$k['clases_count'] ?></span>
                                <?php 
                                // Edad sugerida desde seguridad JSON
                                $edad_label = '';
                                if (!empty($k['seguridad'])) {
                                    try {
                                        $seg = json_decode($k['seguridad'], true);
                                        if (is_array($seg)) {
                                            $emin = isset($seg['edad_min']) && is_numeric($seg['edad_min']) ? (int)$seg['edad_min'] : null;
                                            $emax = isset($seg['edad_max']) && is_numeric($seg['edad_max']) ? (int)$seg['edad_max'] : null;
                                            if ($emin !== null && $emax !== null && $emin > 0 && $emax > 0) {
                                                $edad_label = 'Edad ' . $emin . '–' . $emax . ' años';
                                            }
                                        }
                                    } catch (Exception $e) { /* no-op */ }
                                }
                                ?>
                                <?php if ($edad_label): ?><span class="age"><?= h($edad_label) ?></span><?php endif; ?>
                                <?php if (!empty($k['updated_at'])): ?>
                                    <span class="muted">Actualizado: <?= date('d/m/Y', strtotime($k['updated_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($total > POSTS_PER_PAGE): ?>
                <?= pagination($total, get_current_page(), '/kits') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
console.log('🔍 [kits] Query:', <?= json_encode($q) ?>);
console.log('✅ [kits] Kits cargados:', <?= count($kits) ?>, 'de', <?= (int)$total ?>);
console.log('🔍 [kits] Filtros:', <?= json_encode($filters) ?>);
</script>
<?php include 'includes/footer.php'; ?>
