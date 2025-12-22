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
                include 'clase.php';
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
    $sql = "SELECT numero, nombre, slug, grados_texto, edad_min, edad_max, activo, orden FROM ciclos ";
    if ($activo_only) $sql .= "WHERE activo = 1 ";
    $sql .= "ORDER BY orden ASC, numero ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// Limitar a N palabras y añadir "..."
function cdc_word_limit($text, $max_words = 10) {
    $t = trim(strip_tags((string)$text));
    if ($t === '') return '';
    $words = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    if (!$words || count($words) <= (int)$max_words) return $t;
    $slice = array_slice($words, 0, (int)$max_words);
    return implode(' ', $slice) . '...';
}

function cdc_get_proyectos($pdo, $filters = [], $limit = 12, $offset = 0) {
    $params = [];
    $where = ["c.activo = 1"];
    $joins = [
        "LEFT JOIN clase_areas ca ON ca.clase_id = c.id",
        "LEFT JOIN areas a ON a.id = ca.area_id"
    ];

    // Ciclo: soporta uno o varios
    if (!empty($filters['ciclos']) && is_array($filters['ciclos'])) {
        $ciclos = array_values(array_filter(array_map('intval', $filters['ciclos']), function($v){ return in_array($v, [1,2,3], true); }));
        if (!empty($ciclos)) {
            $placeholders = implode(',', array_fill(0, count($ciclos), '?'));
            $where[] = "c.ciclo IN (" . $placeholders . ")";
            $params = array_merge($params, $ciclos);
        }
    } elseif (!empty($filters['ciclo'])) {
        $where[] = "c.ciclo = ?"; $params[] = (int)$filters['ciclo'];
    }

    // Grado (único)
    if (!empty($filters['grado'])) { $where[] = "JSON_CONTAINS(c.grados, ? )"; $params[] = json_encode([(int)$filters['grado']]); }

    // Áreas: soporta uno o varios slugs (OR)
    if (!empty($filters['areas']) && is_array($filters['areas'])) {
        $areas = array_values(array_filter(array_map('strval', $filters['areas']), function($v){ return $v !== ''; }));
        if (!empty($areas)) {
            $placeholders = implode(',', array_fill(0, count($areas), '?'));
            // joins ya incluyen ca y a
            $where[] = "a.slug IN (" . $placeholders . ")";
            $params = array_merge($params, $areas);
        }
    } elseif (!empty($filters['area'])) {
        // compatibilidad con único parámetro
        $where[] = "a.slug = ?"; $params[] = (string)$filters['area'];
    }

    // Competencia (única por ahora)
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) { $where[] = "cc.competencia_id = ?"; $params[] = (int)$filters['competencia']; }
    }

    // Dificultad: soporta uno o varios slugs (OR)
    if (!empty($filters['dificultades']) && is_array($filters['dificultades'])) {
        $difs = array_values(array_filter(array_map('strval', $filters['dificultades']), function($v){ return in_array($v, ['facil','medio','dificil'], true); }));
        if (!empty($difs)) {
            $placeholders = implode(',', array_fill(0, count($difs), '?'));
            $where[] = "c.dificultad IN (" . $placeholders . ")";
            $params = array_merge($params, $difs);
        }
    } elseif (!empty($filters['dificultad'])) {
        $where[] = "c.dificultad = ?"; $params[] = (string)$filters['dificultad'];
    }

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

    // Ciclo multi
    if (!empty($filters['ciclos']) && is_array($filters['ciclos'])) {
        $ciclos = array_values(array_filter(array_map('intval', $filters['ciclos']), function($v){ return in_array($v, [1,2,3], true); }));
        if (!empty($ciclos)) {
            $placeholders = implode(',', array_fill(0, count($ciclos), '?'));
            $where[] = "c.ciclo IN (" . $placeholders . ")";
            $params = array_merge($params, $ciclos);
        }
    } elseif (!empty($filters['ciclo'])) {
        $where[] = "c.ciclo = ?"; $params[] = (int)$filters['ciclo'];
    }

    if (!empty($filters['grado'])) { $where[] = "JSON_CONTAINS(c.grados, ? )"; $params[] = json_encode([(int)$filters['grado']]); }

    // Áreas multi (OR)
    if (!empty($filters['areas']) && is_array($filters['areas'])) {
        $areas = array_values(array_filter(array_map('strval', $filters['areas']), function($v){ return $v !== ''; }));
        if (!empty($areas)) {
            $placeholders = implode(',', array_fill(0, count($areas), '?'));
            $where[] = "a.slug IN (" . $placeholders . ")";
            $params = array_merge($params, $areas);
        }
    } elseif (!empty($filters['area'])) {
        $where[] = "a.slug = ?"; $params[] = (string)$filters['area'];
    }

    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) { $where[] = "cc.competencia_id = ?"; $params[] = (int)$filters['competencia']; }
    }

    // Dificultad multi
    if (!empty($filters['dificultades']) && is_array($filters['dificultades'])) {
        $difs = array_values(array_filter(array_map('strval', $filters['dificultades']), function($v){ return in_array($v, ['facil','medio','dificil'], true); }));
        if (!empty($difs)) {
            $placeholders = implode(',', array_fill(0, count($difs), '?'));
            $where[] = "c.dificultad IN (" . $placeholders . ")";
            $params = array_merge($params, $difs);
        }
    } elseif (!empty($filters['dificultad'])) {
        $where[] = "c.dificultad = ?"; $params[] = (string)$filters['dificultad'];
    }

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

// Helpers de parseo multi-valor (admite array o CSV)
$parse_multi = function($key) {
    if (!isset($_GET[$key])) return [];
    $raw = $_GET[$key];
    if (is_array($raw)) return array_values(array_filter(array_map('strval', $raw), fn($v)=>$v!==''));
    $str = (string)$raw;
    if ($str === '') return [];
    return array_values(array_filter(array_map('trim', explode(',', $str)), fn($v)=>$v!==''));
};

$ciclos_validos = array_column(cdc_get_ciclos($pdo, true), 'numero');
// ciclos multi (ciclo[] o ciclo=1,2)
$ciclos_in = array_values(array_filter(array_map('intval', $parse_multi('ciclo')), function($v) use ($ciclos_validos){ return in_array($v, $ciclos_validos, true); }));
if (!empty($ciclos_in)) { $filters['ciclos'] = $ciclos_in; }
elseif (isset($_GET['ciclo']) && $_GET['ciclo'] !== '' && in_array((int)$_GET['ciclo'], $ciclos_validos, true)) { $filters['ciclo'] = (int)$_GET['ciclo']; }

if (isset($_GET['grado']) && $_GET['grado'] !== '') $filters['grado'] = $_GET['grado'];

// áreas multi (area[] o area=a,b)
$areas_in = $parse_multi('area');
if (!empty($areas_in)) { $filters['areas'] = $areas_in; }
elseif (isset($_GET['area']) && $_GET['area'] !== '') { $filters['area'] = $_GET['area']; }

if (isset($_GET['competencia']) && $_GET['competencia'] !== '') $filters['competencia'] = $_GET['competencia'];

// dificultad multi (dificultad[] o dificultad=a,b)
$difs_in = array_values(array_filter(array_map(function($v){
    $v = strtolower((string)$v);
    if ($v === 'media') $v = 'medio';
    return in_array($v, ['facil','medio','dificil'], true) ? $v : '';
}, $parse_multi('dificultad'))));
if (!empty($difs_in)) { $filters['dificultades'] = $difs_in; }
elseif (isset($_GET['dificultad']) && $_GET['dificultad'] !== '') { 
    $dv = strtolower((string)$_GET['dificultad']); if ($dv==='media') $dv='medio'; $filters['dificultad'] = $dv; 
}

if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];

$page_title = 'Clases';
$page_description = 'Explora o busca clases científicas por ciclo, grado y área.';
// Canonical: prefer friendly segments for filters and search
if (!empty($q) && empty($filters)) {
    $canonical_url = SITE_URL . ('/clases/buscar/' . rawurlencode($q));
} else {
    $segments = [];
    // Áreas
    if (!empty($filters['areas']) && is_array($filters['areas'])) {
        $segments[] = 'areas/' . rawurlencode(implode(',', array_map('strval', $filters['areas'])));
    } elseif (!empty($filters['area'])) {
        $segments[] = 'areas/' . rawurlencode((string)$filters['area']);
    }
    // Ciclos
    if (!empty($filters['ciclos']) && is_array($filters['ciclos'])) {
        $segments[] = 'ciclos/' . rawurlencode(implode(',', array_map('strval', $filters['ciclos'])));
    } elseif (!empty($filters['ciclo'])) {
        $segments[] = 'ciclos/' . rawurlencode((string)$filters['ciclo']);
    }
    // Grado
    if (!empty($filters['grado'])) {
        $segments[] = 'grado/' . rawurlencode((string)$filters['grado']);
    }
    // Dificultad
    if (!empty($filters['dificultades']) && is_array($filters['dificultades'])) {
        $segments[] = 'dificultad/' . rawurlencode(implode(',', array_map('strval', $filters['dificultades'])));
    } elseif (!empty($filters['dificultad'])) {
        $segments[] = 'dificultad/' . rawurlencode((string)$filters['dificultad']);
    }
    // Competencia
    if (!empty($filters['competencia'])) {
        $segments[] = 'competencia/' . rawurlencode((string)$filters['competencia']);
    }
    // Sort
    $sortVal = (!empty($filters['sort']) && is_string($filters['sort'])) ? trim($filters['sort']) : '';
    if ($sortVal !== '') {
        $segments[] = 'orden/' . rawurlencode($sortVal);
    }
    $canonical_url = SITE_URL . (empty($segments) ? '/clases' : ('/clases/' . implode('/', $segments)));
}

$areas = cdc_get_areas($pdo);
$competencias = cdc_get_competencias($pdo);
// Mapa de edades por ciclo para etiquetas "Edad X–Y años"
$__ciclos_info = cdc_get_ciclos($pdo, true);
$__ciclos_age_map = [];
foreach ($__ciclos_info as $__c) {
    $num = isset($__c['numero']) ? (int)$__c['numero'] : null;
    if ($num !== null) {
        $__ciclos_age_map[$num] = [
            'min' => isset($__c['edad_min']) ? (int)$__c['edad_min'] : null,
            'max' => isset($__c['edad_max']) ? (int)$__c['edad_max'] : null,
        ];
    }
}

// Vista: cards | rows (desktop); en mobile se fuerza rows via CSS
$view = isset($_GET['view']) && in_array($_GET['view'], ['cards','rows'], true) ? $_GET['view'] : 'cards';

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
<div class="container library-page clases-page view-<?= h($view) ?>">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Clases</strong>
    </div>
    <h1><?= $q !== '' && empty($filters) ? 'Resultados de búsqueda' : 'Clases disponibles' ?></h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2 class="filters-title" aria-expanded="true"><span>Filtros</span><span class="chev" aria-hidden="true">▾</span></h2>
            <form method="get" action="/clases" class="filters-form">
                <input type="hidden" name="sort" value="<?= h($filters['sort'] ?? ($_GET['sort'] ?? '')) ?>" />
                <!-- Área (sin toggle), primero -->
                <div class="filter-group">
                    <label class="filter-title" style="display:block; margin-bottom:6px;">Área</label>
                    <div id="cdc-area-taginput" class="tag-input" data-name="area[]"></div>
                    <small class="help-text" style="color: var(--color-text-muted);">Escribe para buscar áreas y presiona Enter.</small>
                </div>

                <!-- Ciclo (compacto, sin toggle) -->
                <div class="filter-group">
                    <label class="filter-title" style="display:block; margin-bottom:6px;">Ciclo</label>
                    <div class="checkbox-list">
                        <?php $ciclos_filtro = cdc_get_ciclos($pdo, true); $selected_ciclos = $filters['ciclos'] ?? (isset($filters['ciclo'])?[(int)$filters['ciclo']]:[]); foreach ($ciclos_filtro as $cf): $checked = in_array((int)$cf['numero'], $selected_ciclos, true); ?>
                        <label>
                            <input type="checkbox" name="ciclo[]" value="<?= h($cf['numero']) ?>" <?= $checked?'checked':'' ?> />
                            <span><?= h($cf['nombre']) ?> (<?= h($cf['grados_texto']) ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Dificultad (compacto, sin toggle) -->
                <div class="filter-group">
                    <label class="filter-title" style="display:block; margin-bottom:6px;">Dificultad</label>
                    <div class="checkbox-list">
                        <?php $selected_difs = $filters['dificultades'] ?? (isset($filters['dificultad'])?[(string)$filters['dificultad']]:[]); ?>
                        <label>
                            <input type="checkbox" name="dificultad[]" value="facil" <?= in_array('facil', $selected_difs, true)?'checked':'' ?> />
                            <span>Fácil</span>
                        </label>
                        <label>
                            <input type="checkbox" name="dificultad[]" value="medio" <?= in_array('medio', $selected_difs, true)?'checked':'' ?> />
                            <span>Medio</span>
                        </label>
                        <label>
                            <input type="checkbox" name="dificultad[]" value="dificil" <?= in_array('dificil', $selected_difs, true)?'checked':'' ?> />
                            <span>Difícil</span>
                        </label>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="margin-right:6px;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        Filtrar
                    </button>
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
                            <p class="objective">
                                <span class="text-trunc"><?= h(cdc_word_limit($p['objetivo_aprendizaje'], 10)) ?></span>
                                <span class="text-full"><?= h($p['objetivo_aprendizaje']) ?></span>
                            </p>
                            <?php endif; ?>
                            <?php if (!empty($p['resumen'])): ?>
                            <p class="excerpt"><small>
                                <span class="text-trunc"><?= h(cdc_word_limit($p['resumen'], 10)) ?></span>
                                <span class="text-full"><?= h($p['resumen']) ?></span>
                            </small></p>
                            <?php endif; ?>
                            <div class="card-footer">
                                <?php
                                $area_label = !empty($p['areas_nombres']) ? $p['areas_nombres'] : ($p['areas'] ?? '');
                                $edad_label = '';
                                // Preferir edad específica por clase si existe en JSON seguridad
                                $emin = null; $emax = null;
                                if (!empty($p['seguridad'])) {
                                    $seg = json_decode($p['seguridad'], true);
                                    if (is_array($seg)) {
                                        if (isset($seg['edad_min']) && is_numeric($seg['edad_min'])) { $emin = (int)$seg['edad_min']; }
                                        if (isset($seg['edad_max']) && is_numeric($seg['edad_max'])) { $emax = (int)$seg['edad_max']; }
                                    }
                                }
                                // Fallback al rango por ciclo
                                if (($emin === null || $emax === null) && isset($p['ciclo'])) {
                                    $cnum = (int)$p['ciclo'];
                                    if (isset($__ciclos_age_map[$cnum])) {
                                        if ($emin === null && isset($__ciclos_age_map[$cnum]['min'])) { $emin = $__ciclos_age_map[$cnum]['min']; }
                                        if ($emax === null && isset($__ciclos_age_map[$cnum]['max'])) { $emax = $__ciclos_age_map[$cnum]['max']; }
                                    }
                                }
                                if ($emin !== null && $emax !== null && $emin > 0 && $emax > 0) {
                                    $edad_label = 'Edad ' . $emin . '–' . $emax . ' años';
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

function updateView(view) {
    const url = new URL(window.location.href);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}

// Collapsible toggles
document.querySelectorAll('.collapsible-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        const content = btn.nextElementSibling;
        if (content) {
            content.style.display = expanded ? 'none' : 'block';
        }
    });
});

// Tag Input for Áreas (multi-select autocomplete + chips)
(function(){
    const container = document.getElementById('cdc-area-taginput');
    if (!container) return;
    const nameAttr = container.getAttribute('data-name') || 'area[]';
    const OPTIONS = <?= json_encode(array_map(function($a){ return ['slug'=>$a['slug'], 'nombre'=>$a['nombre']]; }, $areas)) ?>;
    const SELECTED = <?= json_encode($filters['areas'] ?? (isset($filters['area'])?[(string)$filters['area']]:[])) ?>;

    console.log('🔍 [area-taginput] Opciones:', OPTIONS.length);
    console.log('🔍 [area-taginput] Seleccionadas iniciales:', SELECTED);

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
        // hidden input
        const hi = document.createElement('input');
        hi.type = 'hidden'; hi.name = nameAttr; hi.value = slug;
        container.appendChild(hi);
        hiddenInputs.set(slug, hi);
        renderChip(slug, opt.nombre);
        console.log('✅ [area-taginput] Añadida:', slug);
    }

    function removeValue(slug){
        if (!selectedSet.has(slug)) return;
        selectedSet.delete(slug);
        const hi = hiddenInputs.get(slug); if (hi) { hi.remove(); hiddenInputs.delete(slug); }
        // remove chip
        chipsEl.querySelectorAll('.chip').forEach(ch=>{ if (ch.firstChild && ch.firstChild.nodeType===3 && normalize(ch.firstChild.textContent) === normalize(OPTIONS.find(o=>o.slug===slug)?.nombre||'')) ch.remove(); });
        console.log('⚠️ [area-taginput] Removida:', slug);
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
        console.log('🔍 [area-taginput] Sugerencias:', list.length);
        showSuggestions(list);
    });
    inputEl.addEventListener('keydown', (e)=>{
        if (e.key==='Enter'){
            e.preventDefault();
            const list = filterOptions(inputEl.value);
            if (list.length){ addValue(list[0].slug); inputEl.value=''; showSuggestions([]); }
        } else if (e.key==='Backspace' && !inputEl.value) {
            // remove last selected
            const last = Array.from(selectedSet).pop();
            if (last) removeValue(last);
        }
    });

    // Init with preselected
    (SELECTED||[]).forEach(s=> addValue(String(s)));
})();

// Mobile: toggle Filters sidebar when clicking the title
(function(){
    const sidebar = document.querySelector('.filters-sidebar');
    const title = sidebar ? sidebar.querySelector('.filters-title') : null;
    if (!sidebar || !title) return;
    const updateAria = () => {
        const collapsed = sidebar.classList.contains('fs-collapsed');
        title.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    };
    title.addEventListener('click', () => {
        // Always toggle; CSS will only collapse on mobile widths
        sidebar.classList.toggle('fs-collapsed');
        updateAria();
        const state = sidebar.classList.contains('fs-collapsed') ? 'collapsed' : 'expanded';
        console.log('✅ [filters] Sidebar toggle:', state);
    });
    updateAria();
})();
</script>
<?php include 'includes/footer.php'; ?>
