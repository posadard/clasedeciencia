<?php
// Página unificada: Clases (Catálogo + Búsqueda)
// - Reemplaza catalogo.php y search.php
// - Maneja filtros y búsqueda inteligente en una sola vista
require_once 'config.php';
require_once 'includes/functions.php';

// ================================================================
// DETECCIÓN DINÁMICA DE SLUGS: ciclo, área o proyecto
// ================================================================
if (isset($_GET['slug_dinamico']) && !empty($_GET['slug_dinamico'])) {
    $slug_dinamico = trim($_GET['slug_dinamico']);
    try {
        // Intentar ciclo por slug
        $stmt = $pdo->prepare("SELECT numero FROM ciclos WHERE slug = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$slug_dinamico]);
        $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ciclo) {
            $_GET['ciclo'] = (int)$ciclo['numero'];
            unset($_GET['slug_dinamico']);
        } else {
            // Intentar área por slug
            $stmt = $pdo->prepare("SELECT slug FROM areas WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug_dinamico]);
            $area = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($area) {
                $_GET['area'] = $area['slug'];
                unset($_GET['slug_dinamico']);
            } else {
                // Proyecto
                $_GET['slug'] = $slug_dinamico;
                unset($_GET['slug_dinamico']);
                include 'proyecto.php';
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Error en slug dinámico: ' . $e->getMessage());
    }
}
// ================================================================

// Helpers locales (similar a catalogo.php)
function cdc_get_areas($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug FROM areas ORDER BY nombre");
    return $stmt->fetchAll();
}
function cdc_get_competencias($pdo) {
    $stmt = $pdo->query("SELECT id, codigo, nombre FROM competencias ORDER BY id");
    return $stmt->fetchAll();
}
function cdc_get_ciclos($pdo, $activo_only = true) {
    $sql = "SELECT numero, nombre, slug, grados_texto, activo, orden FROM ciclos ";
    if ($activo_only) $sql .= "WHERE activo = 1 ";
    $sql .= "ORDER BY orden ASC, numero ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function cdc_get_proyectos($pdo, $filters = [], $limit = 12, $offset = 0) {
    $params = [];
    $where = ["c.activo = 1"];
    $joins = [
        "LEFT JOIN clase_areas ca ON ca.clase_id = c.id",
        "LEFT JOIN areas a ON a.id = ca.area_id"
    ];

    if (!empty($filters['ciclo'])) { $where[] = "c.ciclo = ?"; $params[] = $filters['ciclo']; }
    if (!empty($filters['grado'])) { $where[] = "JSON_CONTAINS(c.grados, ? )"; $params[] = json_encode([(int)$filters['grado']]); }
    if (!empty($filters['area'])) {
        $joins[] = "LEFT JOIN clase_areas ca ON ca.clase_id = c.id";
        if (is_numeric($filters['area'])) { $where[] = "ca.area_id = ?"; $params[] = (int)$filters['area']; }
        else { $joins[] = "LEFT JOIN areas a ON a.id = ca.area_id"; $where[] = "a.slug = ?"; $params[] = $filters['area']; }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) { $where[] = "cc.competencia_id = ?"; $params[] = (int)$filters['competencia']; }
    }
    if (!empty($filters['dificultad'])) { $where[] = "c.dificultad = ?"; $params[] = $filters['dificultad']; }

    // Búsqueda por texto (compatibilidad con busqueda)
    if (!empty($filters['busqueda'])) {
        $busqueda = '%' . $filters['busqueda'] . '%';
        $normalize = function($s){
            $s = mb_strtolower($s, 'UTF-8');
            $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
            return $s;
        };
        $busqueda_norm = '%' . $normalize($filters['busqueda']) . '%';
        $where[] = "(c.nombre LIKE ? OR c.resumen LIKE ? OR c.objetivo_aprendizaje LIKE ? OR a.nombre LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?)";
        $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda_norm; $params[] = $busqueda_norm;
    }

    // Orden
    $sort = $filters['sort'] ?? 'recomendados';
    if ($sort === 'recientes') { $order_by = "ORDER BY c.updated_at DESC, c.destacado DESC"; }
    elseif ($sort === 'grado') { $order_by = "ORDER BY JSON_EXTRACT(c.grados, '$[0]') ASC, c.destacado DESC"; }
    else { $order_by = "ORDER BY c.destacado DESC, c.orden_popularidad DESC, c.updated_at DESC"; }

    $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres
            FROM clases c
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY c.id
            " . $order_by . "
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit; $params[] = (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cdc_count_proyectos($pdo, $filters = []) {
    $params = [];
    $where = ["c.activo = 1"];
    $joins = [
        "LEFT JOIN clase_areas ca ON ca.clase_id = c.id",
        "LEFT JOIN areas a ON a.id = ca.area_id"
    ];

    if (!empty($filters['ciclo'])) { $where[] = "c.ciclo = ?"; $params[] = $filters['ciclo']; }
    if (!empty($filters['grado'])) { $where[] = "JSON_CONTAINS(c.grados, ? )"; $params[] = json_encode([(int)$filters['grado']]); }
    if (!empty($filters['area'])) {
        $joins[] = "LEFT JOIN clase_areas ca ON ca.clase_id = c.id";
        if (is_numeric($filters['area'])) { $where[] = "ca.area_id = ?"; $params[] = (int)$filters['area']; }
        else { $joins[] = "LEFT JOIN areas a ON a.id = ca.area_id"; $where[] = "a.slug = ?"; $params[] = $filters['area']; }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) { $where[] = "cc.competencia_id = ?"; $params[] = (int)$filters['competencia']; }
    }
    if (!empty($filters['dificultad'])) { $where[] = "c.dificultad = ?"; $params[] = $filters['dificultad']; }

    if (!empty($filters['busqueda'])) {
        $busqueda = '%' . $filters['busqueda'] . '%';
        $normalize = function($s){ $s = mb_strtolower($s, 'UTF-8'); $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']); return $s; };
        $busqueda_norm = '%' . $normalize($filters['busqueda']) . '%';
        $where[] = "(c.nombre LIKE ? OR c.resumen LIKE ? OR c.objetivo_aprendizaje LIKE ? OR a.nombre LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?)";
        $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda_norm; $params[] = $busqueda_norm;
    }

    $sql = "SELECT COUNT(DISTINCT c.id) AS total
            FROM clases c
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// ========================
// Construir filtros/estado
// ========================
$filters = [];
$q = trim($_GET['q'] ?? ''); // modo búsqueda inteligente
$busqueda = trim($_GET['busqueda'] ?? '');
if ($busqueda !== '') { $filters['busqueda'] = $busqueda; }

$ciclos_validos = array_column(cdc_get_ciclos($pdo, true), 'numero');
if (isset($_GET['ciclo']) && in_array((int)$_GET['ciclo'], $ciclos_validos, true)) $filters['ciclo'] = (int)$_GET['ciclo'];
if (isset($_GET['grado'])) $filters['grado'] = $_GET['grado'];
if (isset($_GET['area'])) $filters['area'] = $_GET['area'];
if (isset($_GET['competencia'])) $filters['competencia'] = $_GET['competencia'];
if (isset($_GET['dificultad'])) $filters['dificultad'] = $_GET['dificultad'];
if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];

$page_title = 'Clases';
$page_description = 'Explora o busca clases científicas por ciclo, grado y área.';
$canonical_url = SITE_URL . ($q ? ('/clases/buscar/' . rawurlencode($q)) : '/clases');

$areas = cdc_get_areas($pdo);
$competencias = cdc_get_competencias($pdo);

// =====================================
// Modo resultados de búsqueda inteligente
// =====================================
$proyectos = [];
$current_page = get_current_page();
$offset = get_offset($current_page);
$total = 0;

if ($q !== '' && empty($filters)) {
    // Búsqueda estilo dropdown: construir search_text y filtrar
    try {
        $stmt = $pdo->query("SELECT c.*, 
            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
            GROUP_CONCAT(DISTINCT comp.nombre ORDER BY comp.nombre SEPARATOR ' | ') AS competencias,
            GROUP_CONCAT(DISTINCT ct.tag ORDER BY ct.tag SEPARATOR ', ') AS tags
            FROM clases c
            LEFT JOIN clase_areas ca ON ca.clase_id = c.id
            LEFT JOIN areas a ON a.id = ca.area_id
            LEFT JOIN clase_competencias cc ON cc.clase_id = c.id
            LEFT JOIN competencias comp ON comp.id = cc.competencia_id
            LEFT JOIN clase_tags ct ON ct.clase_id = c.id
            WHERE c.activo = 1
            GROUP BY c.id
            ORDER BY c.destacado DESC, c.orden_popularidad DESC, c.updated_at DESC");
        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $normalize = function($text) {
            $text = strtolower((string)$text);
            $text = str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], $text);
            return $text;
        };
        $qn = $normalize($q);

        foreach ($clases as $clase) {
            $grados_array = json_decode($clase['grados'] ?? '[]', true) ?: [];
            $grados_texto = !empty($grados_array) ? implode('°, ', $grados_array) . '°' : '';
            $ciclos_nombres = [1 => 'Ciclo 1: Exploración', 2 => 'Ciclo 2: Experimentación', 3 => 'Ciclo 3: Análisis'];
            $ciclo_nombre = $ciclos_nombres[(int)$clase['ciclo']] ?? ('Ciclo ' . (int)$clase['ciclo']);
            $dmap = ['facil'=>'Fácil','media'=>'Media','dificil'=>'Difícil','medio'=>'Medio'];
            $dificultad_label = $dmap[$normalize($clase['dificultad'])] ?? ucfirst($clase['dificultad']);

            $keywords = [];
            foreach ($grados_array as $g) { $keywords[] = 'grado ' . $g; $keywords[] = $g . ' grado'; $keywords[] = 'grado' . $g; }
            $keywords[] = 'ciclo ' . $clase['ciclo']; $keywords[] = 'ciclo' . $clase['ciclo'];

            if (!empty($clase['competencias'])) {
                $comps = explode(' | ', $clase['competencias']);
                foreach ($comps as $comp) {
                    if (stripos($comp, 'indagación') !== false || stripos($comp, 'pregunta') !== false) { $keywords = array_merge($keywords, ['indagacion','preguntas','investigacion']); }
                    if (stripos($comp, 'explicación') !== false || stripos($comp, 'explico') !== false) { $keywords = array_merge($keywords, ['explicacion','explicar','razonamiento']); }
                    if (stripos($comp, 'uso') !== false || stripos($comp, 'aplico') !== false) { $keywords = array_merge($keywords, ['aplicacion','practica','cotidiano']); }
                    if (stripos($comp, 'observo') !== false || stripos($comp, 'registro') !== false) { $keywords = array_merge($keywords, ['observacion','datos','registro']); }
                    if (stripos($comp, 'modelo') !== false) { $keywords = array_merge($keywords, ['modelado','representacion']); }
                    if (stripos($comp, 'cálculo') !== false || stripos($comp, 'medición') !== false) { $keywords = array_merge($keywords, ['medicion','calculo','matematicas']); }
                }
            }

            $search_parts = [
                $clase['nombre'] ?? '',
                $clase['resumen'] ?? '',
                $clase['objetivo_aprendizaje'] ?? '',
                $clase['areas'] ?? '',
                $clase['tags'] ?? '',
                $ciclo_nombre,
                $grados_texto,
                $dificultad_label,
                implode(' ', array_unique($keywords))
            ];
            $search_text = $normalize(implode(' ', $search_parts));

            if ($qn === '' || strpos($search_text, $qn) !== false) {
                $proyectos[] = $clase;
            }
        }
        $total = count($proyectos);
    } catch (PDOException $e) {
        error_log('Error en búsqueda inteligente: ' . $e->getMessage());
        $proyectos = []; $total = 0;
    }
} else {
    // Modo catálogo con filtros
    $current_page = get_current_page();
    $offset = get_offset($current_page);
    $proyectos = cdc_get_proyectos($pdo, $filters, POSTS_PER_PAGE, $offset);
    $total = cdc_count_proyectos($pdo, $filters);
}

include 'includes/header.php';
?>
<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Clases</strong>
    </div>
    <h1><?= $q !== '' && empty($filters) ? 'Resultados de búsqueda' : 'Clases disponibles' ?></h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Filtros</h2>
            <form method="get" action="/clases" class="filters-form">
                <div class="filter-group">
                    <label>Ciclo</label>
                    <select name="ciclo">
                        <option value="">Todos</option>
                        <?php $ciclos_filtro = cdc_get_ciclos($pdo, true); foreach ($ciclos_filtro as $cf): ?>
                        <option value="<?= h($cf['numero']) ?>" <?= isset($filters['ciclo']) && $filters['ciclo']==$cf['numero']?'selected':'' ?>><?= h($cf['nombre']) ?> (<?= h($cf['grados_texto']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Área</label>
                    <select name="area">
                        <option value="">Todas</option>
                        <?php foreach($areas as $a): ?>
                        <option value="<?= h($a['slug']) ?>" <?= isset($filters['area']) && $filters['area']===$a['slug']?'selected':'' ?>><?= h($a['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Dificultad</label>
                    <select name="dificultad">
                        <option value="">Todas</option>
                        <option value="facil" <?= isset($filters['dificultad']) && $filters['dificultad']==='facil'?'selected':'' ?>>Fácil</option>
                        <option value="medio" <?= isset($filters['dificultad']) && $filters['dificultad']==='medio'?'selected':'' ?>>Medio</option>
                        <option value="dificil" <?= isset($filters['dificultad']) && $filters['dificultad']==='dificil'?'selected':'' ?>>Difícil</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    <a href="/clases" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </aside>
        <div class="library-content">
            <div class="results-header">
                <?php if ($q !== '' && empty($filters)): ?>
                <div class="search-active-banner">
                    <span class="search-term">🔍 Resultados para: <strong><?= h($q) ?></strong></span>
                    <a href="/clases" class="clear-search">✕ Ver catálogo</a>
                </div>
                <?php endif; ?>
                <p class="results-count">
                    Mostrando <?= count($proyectos) ?><?= $q !== '' && empty($filters) ? ' clases de búsqueda' : ' de ' . $total . ' clases' ?>
                    <?php if ($total > POSTS_PER_PAGE && empty($q)): ?>
                        (Página <?= get_current_page() ?> de <?= ceil($total / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
                <div class="sort-selector">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" onchange="updateSort(this.value)">
                        <option value="recomendados" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'recomendados') ? 'selected' : '' ?>>📌 Recomendados primero</option>
                        <option value="recientes" <?= (isset($_GET['sort']) && $_GET['sort'] === 'recientes') ? 'selected' : '' ?>>🕐 Más recientes</option>
                        <option value="grado" <?= (isset($_GET['sort']) && $_GET['sort'] === 'grado') ? 'selected' : '' ?>>🎓 Por Grado (1° a 11°)</option>
                    </select>
                </div>
            </div>

            <?php if (empty($proyectos)): ?>
            <div class="no-results">
                <p>No hay clases con los criterios seleccionados.</p>
                <a href="/clases" class="btn btn-secondary">Ver todas</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($proyectos as $p): ?>
                <article class="article-card" data-href="/<?= h($p['slug']) ?>">
                    <a class="card-link" href="/<?= h($p['slug']) ?>">
                        <div class="card-content">
                            <div class="card-meta">
                                <?php if (!empty($p['destacado'])): ?>
                                <span class="badge badge-destacado" title="Recomendado">⭐</span>
                                <?php endif; ?>
                                <span class="section-badge">Ciclo <?= h($p['ciclo']) ?></span>
                                <?php 
                                if (!empty($p['grados'])) {
                                    $grados = json_decode($p['grados'], true);
                                    if (is_array($grados) && count($grados) > 0) {
                                        foreach ($grados as $grado) { echo '<span class="grade-badge">' . (int)$grado . '°</span>'; }
                                    }
                                }
                                ?>
                                <span class="difficulty-badge"><?= h(ucfirst(is_string($p['dificultad']) ? $p['dificultad'] : '')) ?></span>
                            </div>
                            <h3><?= h($p['nombre']) ?></h3>
                            <?php if (!empty($p['objetivo_aprendizaje'])): ?>
                            <p class="objective"><?= h($p['objetivo_aprendizaje']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($p['resumen'])): ?>
                            <p class="excerpt"><small><?= h($p['resumen']) ?></small></p>
                            <?php endif; ?>
                            <div class="card-footer">
                                <?php
                                $area_label = !empty($p['areas_nombres']) ? $p['areas_nombres'] : ($p['areas'] ?? '');
                                $edad_label = '';
                                if (!empty($p['grados'])) {
                                    $gr = json_decode($p['grados'], true);
                                    if (is_array($gr) && count($gr) > 0) {
                                        $minG = min($gr); $maxG = max($gr);
                                        $edad_label = 'Grados ' . (int)$minG . '°–' . (int)$maxG . '°';
                                    }
                                }
                                ?>
                                <?php if ($area_label): ?><span class="area">Área: <?= h($area_label) ?></span><?php endif; ?>
                                <?php if ($edad_label): ?><span class="age"><?= h($edad_label) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($total > POSTS_PER_PAGE && empty($q)): ?>
                <?= pagination($total, get_current_page(), '/clases') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
console.log('🔍 [clases] Query:', <?= json_encode($q) ?>);
console.log('🔍 [clases] Filtros activos:', <?= json_encode($filters) ?>);
console.log('✅ [clases] Clases cargadas:', <?= count($proyectos) ?>);

function updateSort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
<?php include 'includes/footer.php'; ?>
