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

function cdc_get_kits($pdo, $search = '', $limit = 12, $offset = 0, $filters = [], $sort = 'recientes') {
    $params = [];
    $where = ["k.activo = 1"];
    $joins = ["LEFT JOIN kits_areas ka ON ka.kit_id = k.id", "LEFT JOIN areas a ON a.id = ka.area_id"]; // for area names and filter

    if ($search !== '') {
        $where[] = "(k.nombre LIKE ? OR k.codigo LIKE ?)";
        $term = '%' . $search . '%';
        $params[] = $term; $params[] = $term;
    }

    // Filtros adicionales
    $edad = isset($filters['edad']) ? (int)$filters['edad'] : null;
    $con_video = !empty($filters['con_video']);
    $con_imagen = !empty($filters['con_imagen']);
    $area_slug = isset($filters['area']) ? trim((string)$filters['area']) : '';
    $areas_multi = (isset($filters['areas']) && is_array($filters['areas'])) ? array_values(array_filter(array_map('strval', $filters['areas']), fn($v)=>$v!=='')) : [];
    // version/date are handled via sort, not filters

    if ($edad !== null && $edad > 0) {
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(k.seguridad, '$.edad_min')) AS UNSIGNED) <= ?";
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(k.seguridad, '$.edad_max')) AS UNSIGNED) >= ?";
        $params[] = $edad; $params[] = $edad;
    }
    if ($con_video) { $where[] = "k.video_portada IS NOT NULL AND k.video_portada <> ''"; }
    if ($con_imagen) { $where[] = "k.imagen_portada IS NOT NULL AND k.imagen_portada <> ''"; }
    if (!empty($areas_multi)) {
        $placeholders = implode(',', array_fill(0, count($areas_multi), '?'));
        $where[] = "a.slug IN (" . $placeholders . ")";
        $params = array_merge($params, $areas_multi);
    } elseif ($area_slug !== '') {
        $where[] = "a.slug = ?"; $params[] = $area_slug;
    }
    // Determine ORDER BY
    $orderBy = "ORDER BY k.updated_at DESC, k.id DESC";
    if ($sort === 'version') {
        $orderBy = "ORDER BY CAST(k.version AS UNSIGNED) DESC, k.updated_at DESC";
    } elseif ($sort === 'clases') {
        $orderBy = "ORDER BY clases_count DESC, k.updated_at DESC";
    } elseif ($sort === 'componentes') {
        $orderBy = "ORDER BY componentes_count DESC, k.updated_at DESC";
    }

    $sql = "SELECT 
                k.id, k.nombre, k.slug, k.codigo, k.version, k.updated_at,
                k.resumen, k.seguridad,
                GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres,
                (SELECT COUNT(*) FROM kit_componentes kc WHERE kc.kit_id = k.id) AS componentes_count,
                (SELECT COUNT(*) FROM clase_kits ck WHERE ck.kit_id = k.id) AS clases_count
            FROM kits k
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY k.id
            " . $orderBy . "
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit; $params[] = (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cdc_count_kits($pdo, $search = '', $filters = []) {
    $params = [];
    $where = ["k.activo = 1"];
    $joins = ["LEFT JOIN kits_areas ka ON ka.kit_id = k.id", "LEFT JOIN areas a ON a.id = ka.area_id"]; // for area filter
    if ($search !== '') {
        $where[] = "(k.nombre LIKE ? OR k.codigo LIKE ?)";
        $term = '%' . $search . '%';
        $params[] = $term; $params[] = $term;
    }
    // Filtros adicionales (idénticos a cdc_get_kits)
    $edad = isset($filters['edad']) ? (int)$filters['edad'] : null;
    $con_video = !empty($filters['con_video']);
    $con_imagen = !empty($filters['con_imagen']);
    $area_slug = isset($filters['area']) ? trim((string)$filters['area']) : '';
    $areas_multi = (isset($filters['areas']) && is_array($filters['areas'])) ? array_values(array_filter(array_map('strval', $filters['areas']), fn($v)=>$v!=='')) : [];
    // version/date are not filters in count

    if ($edad !== null && $edad > 0) {
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(seguridad, '$.edad_min')) AS UNSIGNED) <= ?";
        $where[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(seguridad, '$.edad_max')) AS UNSIGNED) >= ?";
        $params[] = $edad; $params[] = $edad;
    }
    if ($con_video) { $where[] = "video_portada IS NOT NULL AND video_portada <> ''"; }
    if ($con_imagen) { $where[] = "imagen_portada IS NOT NULL AND imagen_portada <> ''"; }
    if (!empty($areas_multi)) {
        $placeholders = implode(',', array_fill(0, count($areas_multi), '?'));
        $where[] = "a.slug IN (" . $placeholders . ")";
        $params = array_merge($params, $areas_multi);
    } elseif ($area_slug !== '') {
        $where[] = "a.slug = ?"; $params[] = $area_slug;
    }
    // no-op for version/date
    $sql = "SELECT COUNT(DISTINCT k.id) AS total FROM kits k " . implode(' ', array_unique($joins)) . " WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// Estado de interfaz
$q = trim($_GET['q'] ?? '');
// Nuevos filtros
$filters = [
    'edad' => (isset($_GET['edad']) && trim((string)$_GET['edad']) !== '') ? (int)$_GET['edad'] : null,
    'con_video' => isset($_GET['con_video']) && $_GET['con_video'] === '1',
    'con_imagen' => isset($_GET['con_imagen']) && $_GET['con_imagen'] === '1',
];
// Soporta áreas múltiples: area[]=slug o area=a,b
$parse_multi = function($key) {
    if (!isset($_GET[$key])) return [];
    $raw = $_GET[$key];
    if (is_array($raw)) return array_values(array_filter(array_map('strval', $raw), fn($v)=>$v!==''));
    $str = (string)$raw; if ($str==='') return [];
    return array_values(array_filter(array_map('trim', explode(',', $str)), fn($v)=>$v!==''));
};
$areas_in = $parse_multi('area');
if (!empty($areas_in)) { $filters['areas'] = $areas_in; }
elseif (isset($_GET['area']) && $_GET['area'] !== '') { $filters['area'] = trim((string)$_GET['area']); }
$sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'recientes';
$current_page = get_current_page();
$offset = get_offset($current_page);

$page_title = 'Kits';
$page_description = 'Explora los kits de Clase de Ciencia con sus componentes y clases relacionadas.';
$canonical_url = SITE_URL . '/kits';

$kits = cdc_get_kits($pdo, $q, POSTS_PER_PAGE, $offset, $filters, $sort);
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
            <?php $areas = cdc_get_areas($pdo); ?>
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
                    <label class="filter-title" style="display:block; margin-bottom:6px;">Área</label>
                    <div id="cdc-area-taginput" class="tag-input" data-name="area[]"></div>
                    <small class="help-text" style="color: var(--color-text-muted);">Escribe para buscar áreas y presiona Enter.</small>
                </div>
                <div class="filter-group">
                    <label for="sort">Ordenar por</label>
                    <select id="sort" name="sort">
                        <option value="recientes" <?= $sort==='recientes'?'selected':'' ?>>Recientes (fecha)</option>
                        <option value="version" <?= $sort==='version'?'selected':'' ?>>Versión (mayor primero)</option>
                        <option value="clases" <?= $sort==='clases'?'selected':'' ?>>Más clases vinculadas</option>
                        <option value="componentes" <?= $sort==='componentes'?'selected':'' ?>>Más componentes</option>
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
                                <?php $area_label = !empty($k['areas_nombres']) ? $k['areas_nombres'] : ''; ?>
                                <?php if ($area_label): ?><span class="area">Área: <?= h($area_label) ?></span><?php endif; ?>
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
console.log('🔀 [kits] Sort:', <?= json_encode($sort) ?>);
if (<?= json_encode($filters['edad'] === null) ?>) { console.log('⚠️ [kits] Filtro edad vacío, no aplicado'); }

// Tag Input for Áreas (multi-select autocomplete + chips)
(function(){
    const container = document.getElementById('cdc-area-taginput');
    if (!container) return;
    const nameAttr = container.getAttribute('data-name') || 'area[]';
    const OPTIONS = <?= json_encode(array_map(function($a){ return ['slug'=>$a['slug'], 'nombre'=>$a['nombre']]; }, $areas)) ?>;
    const SELECTED = <?= json_encode($filters['areas'] ?? (isset($filters['area'])?[(string)$filters['area']]:[])) ?>;

    console.log('🔍 [kits area-taginput] Opciones:', OPTIONS.length);
    console.log('🔍 [kits area-taginput] Seleccionadas iniciales:', SELECTED);

    const normalize = (s) => (s||'').toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
        .replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,' ').trim();

    container.classList.add('ti');
    container.innerHTML = '<div class="ti-chips"></div><input type="text" class="ti-input" placeholder="Escribe un área..." autocomplete="off" /><div class="ti-suggestions" style="display:none;"></div>';
    const chipsEl = container.querySelector('.ti-chips');
    const inputEl = container.querySelector('.ti-input');
    const suggEl = container.querySelector('.ti-suggestions');

    const hiddenInputs = new Map(); // slug -> input
    const selectedSet = new Set();

    function renderChip(slug, label){
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.textContent = label;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'chip-remove';
        close.setAttribute('aria-label','Quitar área');
        close.textContent = '×';
        close.addEventListener('click', () => removeValue(slug));
        chip.appendChild(close);
        chipsEl.appendChild(chip);
    }

    function addValue(slug){
        if (selectedSet.has(slug)) return;
        const opt = OPTIONS.find(o=>o.slug===slug);
        if (!opt) return;
        selectedSet.add(slug);
        const hi = document.createElement('input');
        hi.type = 'hidden'; hi.name = nameAttr; hi.value = slug;
        container.appendChild(hi);
        hiddenInputs.set(slug, hi);
        renderChip(slug, opt.nombre);
        console.log('✅ [kits area-taginput] Añadida:', slug);
    }

    function removeValue(slug){
        if (!selectedSet.has(slug)) return;
        selectedSet.delete(slug);
        const hi = hiddenInputs.get(slug); if (hi) { hi.remove(); hiddenInputs.delete(slug); }
        chipsEl.querySelectorAll('.chip').forEach(ch=>{
            if (ch.firstChild && ch.firstChild.nodeType===3 && normalize(ch.firstChild.textContent) === normalize(OPTIONS.find(o=>o.slug===slug)?.nombre||'')) ch.remove();
        });
        console.log('⚠️ [kits area-taginput] Removida:', slug);
    }

    function showSuggestions(list){
        if (!list.length){ suggEl.style.display='none'; suggEl.innerHTML=''; return; }
        suggEl.innerHTML = list.map(o=>`<div class="ti-item" data-slug="${o.slug}">${o.nombre}</div>`).join('');
        suggEl.style.display='block';
    }

    function filterOptions(q){
        const qn = normalize(q);
        if (!qn) return OPTIONS.filter(o=>!selectedSet.has(o.slug)).slice(0,8);
        return OPTIONS.filter(o=>!selectedSet.has(o.slug) && normalize(o.nombre).includes(qn)).slice(0,8);
    }

    suggEl.addEventListener('click', (e)=>{
        const item = e.target.closest('.ti-item');
        if (!item) return;
        addValue(item.getAttribute('data-slug'));
        inputEl.value=''; suggEl.style.display='none';
        inputEl.focus();
    });

    inputEl.addEventListener('input', ()=>{
        const list = filterOptions(inputEl.value);
        console.log('🔍 [kits area-taginput] Sugerencias:', list.length);
        showSuggestions(list);
    });
    inputEl.addEventListener('keydown', (e)=>{
        if (e.key==='Enter'){
            e.preventDefault();
            const list = filterOptions(inputEl.value);
            if (list.length){ addValue(list[0].slug); inputEl.value=''; showSuggestions([]); }
        } else if (e.key==='Backspace' && !inputEl.value) {
            const last = Array.from(selectedSet).pop();
            if (last) removeValue(last);
        }
    });

    (SELECTED||[]).forEach(s=> addValue(String(s)));
})();
</script>
<?php include 'includes/footer.php'; ?>
