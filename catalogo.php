<?php
// Catalogo (DEPRECADO): redirige a clases.php
header('Location: /clases.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;

// Catalogo - Lista de proyectos con filtros (CdC)
require_once 'config.php';
require_once 'includes/functions.php';

// ==================================================================
// DETECCIÓN DINÁMICA DE SLUGS: ciclo o área
// ==================================================================
if (isset($_GET['slug_dinamico']) && !empty($_GET['slug_dinamico'])) {
    $slug_dinamico = trim($_GET['slug_dinamico']);
    
    // Intentar encontrar ciclo por slug
    $stmt = $pdo->prepare("SELECT numero FROM ciclos WHERE slug = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$slug_dinamico]);
    $ciclo_encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ciclo_encontrado) {
        // Es un ciclo - establecer parámetro ciclo
        $_GET['ciclo'] = $ciclo_encontrado['numero'];
        unset($_GET['slug_dinamico']);
    } else {
        // Intentar encontrar área por slug
        $stmt = $pdo->prepare("SELECT slug FROM areas WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug_dinamico]);
        $area_encontrada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($area_encontrada) {
            // Es un área - establecer parámetro area
            $_GET['area'] = $area_encontrada['slug'];
            unset($_GET['slug_dinamico']);
        } else {
            // No es ciclo ni área - es un proyecto, incluir proyecto.php
            $_GET['slug'] = $slug_dinamico;
            unset($_GET['slug_dinamico']);
            include 'proyecto.php';
            exit;
        }
    }
}
// ==================================================================

// Nota: usamos funciones locales mientras adaptamos includes/db-functions.php a CdC
function cdc_get_areas($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug FROM areas ORDER BY nombre");
    return $stmt->fetchAll();
}

function cdc_get_competencias($pdo) {
    $stmt = $pdo->query("SELECT id, codigo, nombre FROM competencias ORDER BY id");
    return $stmt->fetchAll();
}

function cdc_get_ciclos($pdo, $activo_only = true) {
    try {
        $sql = "SELECT numero, nombre, slug, edad_min, edad_max, grados, grados_texto, 
                       proposito, explicacion, nivel_educativo, isced_level, activo, orden 
                FROM ciclos ";
        if ($activo_only) {
            $sql .= "WHERE activo = 1 ";
        }
        $sql .= "ORDER BY orden ASC, numero ASC";
        
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        
        // Crear resumen del propósito (primera oración)
        foreach ($rows as &$row) {
            $proposito = $row['proposito'] ?? '';
            if (!empty($proposito)) {
                $sentences = preg_split('/(?<=[.!?])\s+/', $proposito, 2);
                $row['proposito_corto'] = $sentences[0] ?? '';
            } else {
                $row['proposito_corto'] = '';
            }
        }
        
        return $rows;
    } catch (Exception $e) {
        error_log('Error en cdc_get_ciclos: ' . $e->getMessage());
        return [];
    }
}

function cdc_get_proyectos($pdo, $filters = [], $limit = 12, $offset = 0) {
    $params = [];
    $where = ["c.activo = 1"];
    $joins = [
        "LEFT JOIN clase_areas ca ON ca.clase_id = c.id",
        "LEFT JOIN areas a ON a.id = ca.area_id"
    ];

    if (!empty($filters['ciclo'])) {
        $where[] = "c.ciclo = ?";
        $params[] = $filters['ciclo'];
    }
    if (!empty($filters['grado'])) {
        // grados es JSON: usamos JSON_CONTAINS
        $where[] = "JSON_CONTAINS(c.grados, ? )";
        $params[] = json_encode([(int)$filters['grado']]);
    }
    if (!empty($filters['area'])) {
        // filtro por área vía tabla puente clase_areas
        $joins[] = "LEFT JOIN clase_areas ca ON ca.clase_id = c.id";
        if (is_numeric($filters['area'])) {
            $where[] = "ca.area_id = ?";
            $params[] = (int)$filters['area'];
        } else {
            $joins[] = "LEFT JOIN areas a ON a.id = ca.area_id";
            $where[] = "a.slug = ?";
            $params[] = $filters['area'];
        }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) {
            $where[] = "cc.competencia_id = ?";
            $params[] = (int)$filters['competencia'];
        }
    }
    if (!empty($filters['dificultad'])) {
        $where[] = "c.dificultad = ?";
        $params[] = $filters['dificultad'];
    }
    
    // Búsqueda por texto
    if (!empty($filters['busqueda'])) {
        $busqueda = '%' . $filters['busqueda'] . '%';
        // Normalizada sin acentos para coincidencias más amplias
        $normalize = function($s){
            $s = mb_strtolower($s, 'UTF-8');
            $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
            return $s;
        };
        $busqueda_norm = '%' . $normalize($filters['busqueda']) . '%';
        $where[] = "(c.nombre LIKE ? OR c.resumen LIKE ? OR c.objetivo_aprendizaje LIKE ? OR a.nombre LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?
                     OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE ?)";
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda_norm;
        $params[] = $busqueda_norm;
    }

    // Determinar ordenamiento según sort
    $sort = $filters['sort'] ?? 'recomendados';
    if ($sort === 'recientes') {
        $order_by = "ORDER BY c.updated_at DESC, c.destacado DESC";
    } elseif ($sort === 'grado') {
        $order_by = "ORDER BY JSON_EXTRACT(c.grados, '$[0]') ASC, c.destacado DESC";
    } else {
        $order_by = "ORDER BY c.destacado DESC, c.orden_popularidad DESC, c.updated_at DESC";
    }

    $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres
            FROM clases c
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY c.id
            " . $order_by . "
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;

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

    if (!empty($filters['ciclo'])) {
        $where[] = "c.ciclo = ?";
        $params[] = $filters['ciclo'];
    }
    if (!empty($filters['grado'])) {
        $where[] = "JSON_CONTAINS(c.grados, ? )";
        $params[] = json_encode([(int)$filters['grado']]);
    }
    if (!empty($filters['area'])) {
        $joins[] = "LEFT JOIN clase_areas ca ON ca.clase_id = c.id";
        if (is_numeric($filters['area'])) {
            $where[] = "ca.area_id = ?";
            $params[] = (int)$filters['area'];
        } else {
            $joins[] = "LEFT JOIN areas a ON a.id = ca.area_id";
            $where[] = "a.slug = ?";
            $params[] = $filters['area'];
        }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN clase_competencias cc ON cc.clase_id = c.id";
        if (is_numeric($filters['competencia'])) {
            $where[] = "cc.competencia_id = ?";
            $params[] = (int)$filters['competencia'];
        }
    }
    if (!empty($filters['dificultad'])) {
        $where[] = "c.dificultad = ?";
        $params[] = $filters['dificultad'];
    }
    
    // Búsqueda por texto
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
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda_norm;
        $params[] = $busqueda_norm;
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

// Obtener filtros
$filters = [];
// Validar ciclo contra BD
$ciclos_validos = array_column(cdc_get_ciclos($pdo, true), 'numero');
if (isset($_GET['ciclo']) && in_array((int)$_GET['ciclo'], $ciclos_validos, true)) $filters['ciclo'] = (int)$_GET['ciclo'];
if (isset($_GET['grado'])) $filters['grado'] = $_GET['grado'];
if (isset($_GET['area'])) $filters['area'] = $_GET['area'];
if (isset($_GET['competencia'])) $filters['competencia'] = $_GET['competencia'];
if (isset($_GET['busqueda']) && trim($_GET['busqueda']) !== '') $filters['busqueda'] = trim($_GET['busqueda']);

// Fallback: derivar filtros desde busqueda si faltan y hay una consulta
if (!empty($filters['busqueda'])) {
    $raw_q = $filters['busqueda'];
    $normalize = function($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $replacements = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u'
        ];
        $text = strtr($text, $replacements);
        $text = preg_replace('/[^a-z0-9°\s]+/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    };
    $qn = $normalize($raw_q);

    // Solo si no vienen explícitos, intentar detectar
    if (empty($filters['grado'])) {
        $grado = null;
        if (preg_match('/grado\s*(\d{1,2})/u', $qn, $m)) {
            $grado = (int)$m[1];
        } elseif (preg_match('/(\d{1,2})\s*°/u', $qn, $m)) {
            $grado = (int)$m[1];
        } else {
            $map = [
                'primero'=>1,'primer'=>1,'segundo'=>2,'tercero'=>3,'cuarto'=>4,'quinto'=>5,
                'sexto'=>6,'septimo'=>7,'septimo'=>7,'octavo'=>8,'noveno'=>9,
                'decimo'=>10,'decimo'=>10,'once'=>11,'undecimo'=>11
            ];
            foreach (explode(' ', $qn) as $t) { if (isset($map[$t])) { $grado = $map[$t]; break; } }
        }
        if ($grado && $grado >= 1 && $grado <= 11) { $filters['grado'] = $grado; }
    }

    if (empty($filters['ciclo'])) {
        $ciclo = null;
        if (preg_match('/ciclo\s*(\d)/u', $qn, $m)) { $ciclo = (int)$m[1]; }
        if (!$ciclo) {
            $tokens = explode(' ', $qn);
            if (in_array('exploracion', $tokens, true)) $ciclo = 1;
            elseif (in_array('experimentacion', $tokens, true)) $ciclo = 2;
            elseif (in_array('analisis', $tokens, true)) $ciclo = 3;
        }
        if ($ciclo && in_array($ciclo, [1,2,3], true)) { $filters['ciclo'] = $ciclo; }
    }

    if (empty($filters['dificultad'])) {
        $tokens = explode(' ', $qn);
        if (in_array('facil', $tokens, true)) $filters['dificultad'] = 'facil';
        elseif (in_array('medio', $tokens, true) || in_array('media', $tokens, true) || in_array('intermedio', $tokens, true)) $filters['dificultad'] = 'medio';
        elseif (in_array('dificil', $tokens, true) || in_array('avanzado', $tokens, true)) $filters['dificultad'] = 'dificil';
    }

    if (empty($filters['area'])) {
        $tokens = explode(' ', $qn);
        $areaSlugs = array_map(function($a){ return $a['slug']; }, $areas ?? []);
        $areaMap = ['fisica','quimica','biologia','ambiental','tecnologia'];
        foreach ($tokens as $t) {
            if (in_array($t, $areaMap, true)) { $filters['area'] = $t; break; }
            if (in_array($t, $areaSlugs, true)) { $filters['area'] = $t; break; }
        }
    }
}
if (isset($_GET['dificultad'])) $filters['dificultad'] = $_GET['dificultad'];
if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];

$page_title = 'Clases disponibles';
$page_description = 'Explora clases científicas por ciclo, grado, área y competencias MEN.';
$canonical_url = SITE_URL . '/catalogo.php';

$current_page = get_current_page();
$offset = get_offset($current_page);
$areas = cdc_get_areas($pdo);
$competencias = cdc_get_competencias($pdo);
$proyectos = cdc_get_proyectos($pdo, $filters, POSTS_PER_PAGE, $offset);
$total = cdc_count_proyectos($pdo, $filters);

// Construir descripción de filtros activos
$filtros_activos = [];
if (!empty($filters['ciclo'])) {
    $ciclo_info = array_filter(cdc_get_ciclos($pdo, true), fn($c) => $c['numero'] == $filters['ciclo']);
    if ($ciclo_info) {
        $ciclo_info = reset($ciclo_info);
        $filtros_activos[] = $ciclo_info['nombre'];
    }
}
if (!empty($filters['area'])) {
    $area_info = array_filter($areas, fn($a) => $a['slug'] == $filters['area'] || $a['id'] == $filters['area']);
    if ($area_info) {
        $area_info = reset($area_info);
        $filtros_activos[] = $area_info['nombre'];
    }
}
if (!empty($filters['dificultad'])) {
    $filtros_activos[] = ucfirst($filters['dificultad']);
}
if (!empty($filters['busqueda'])) {
    $filtros_activos[] = 'Búsqueda: "' . $filters['busqueda'] . '"';
}
$filtros_texto = !empty($filtros_activos) ? ' de ' . implode(' - ', $filtros_activos) : '';

// Schema.org ItemList
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Clases disponibles',
    'description' => $page_description,
    'url' => $canonical_url,
    'numberOfItems' => $total
];
if (!empty($proyectos)) {
    $pos = ($current_page - 1) * POSTS_PER_PAGE + 1;
    $schema['itemListElement'] = array_map(function($p) use (&$pos) {
        $desc = trim(($p['objetivo_aprendizaje'] ?? '') . ' — ' . ($p['resumen'] ?? ''));
        $item = [
            '@type' => 'CreativeWork',
            'name' => $p['nombre'],
            'url' => SITE_URL . '/proyecto.php?slug=' . $p['slug'],
            'description' => $desc
        ];
        return ['@type' => 'ListItem', 'position' => $pos++, 'item' => $item];
    }, $proyectos);
}
$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

include 'includes/header.php';
?>
<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Clases</strong>
    </div>
    <h1>Clases disponibles</h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Filtros</h2>
            <form method="get" action="/catalogo.php" class="filters-form">
                <div class="filter-group">
                    <label>Ciclo</label>
                    <select name="ciclo">
                        <option value="">Todos</option>
                        <?php 
                        $ciclos_filtro = cdc_get_ciclos($pdo, true);
                        foreach ($ciclos_filtro as $cf): 
                            $selected = isset($filters['ciclo']) && $filters['ciclo'] == $cf['numero'] ? 'selected' : '';
                        ?>
                        <option value="<?= h($cf['numero']) ?>" <?= $selected ?>><?= h($cf['nombre']) ?> (<?= h($cf['grados_texto']) ?>)</option>
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
                    <a href="/catalogo.php" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </aside>
        <div class="library-content">
            <div class="results-header">
                <?php if (!empty($filters['busqueda'])): ?>
                <div class="search-active-banner">
                    <span class="search-term">🔍 Resultados para: <strong><?= h($filters['busqueda']) ?></strong></span>
                    <a href="/catalogo.php" class="clear-search">✕ Limpiar búsqueda</a>
                </div>
                <?php endif; ?>
                
                <p class="results-count">
                    Mostrando <?= count($proyectos) ?> de <?= $total ?> clases<?= $filtros_texto ?>
                    <?php if ($total > POSTS_PER_PAGE): ?>
                        (Página <?= $current_page ?> de <?= ceil($total / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
                <div class="sort-selector">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" onchange="this.form ? this.form.submit() : updateSort(this.value)">
                        <option value="recomendados" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'recomendados') ? 'selected' : '' ?>>📌 Recomendados primero</option>
                        <option value="recientes" <?= (isset($_GET['sort']) && $_GET['sort'] === 'recientes') ? 'selected' : '' ?>>🕐 Más recientes</option>
                        <option value="grado" <?= (isset($_GET['sort']) && $_GET['sort'] === 'grado') ? 'selected' : '' ?>>🎓 Por Grado (1° a 11°)</option>
                    </select>
                </div>
            </div>
            <?php if (empty($proyectos)): ?>
            <div class="no-results">
                <p>No hay clases con los filtros seleccionados.</p>
                <a href="/catalogo.php" class="btn btn-secondary">Ver todas</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($proyectos as $p): ?>
                <article class="article-card" data-href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                    <a class="card-link" href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                        <div class="card-content">
                            <div class="card-meta">
                                <?php if (!empty($p['destacado'])): ?>
                                <span class="badge badge-destacado" title="Recomendado">⭐</span>
                                <?php endif; ?>
                                <span class="section-badge">Ciclo <?= h($p['ciclo']) ?></span>
                                <?php 
                                // Mostrar grados como badges individuales
                                if (!empty($p['grados'])) {
                                    $grados = json_decode($p['grados'], true);
                                    if (is_array($grados) && count($grados) > 0) {
                                        foreach ($grados as $grado) {
                                            echo '<span class="grade-badge">' . (int)$grado . '°</span>';
                                        }
                                    }
                                }
                                ?>
                                <span class="difficulty-badge"><?= h(ucfirst($p['dificultad'])) ?></span>
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
                                $edad_label = '';
                                if (!empty($p['seguridad'])) {
                                    $seg = json_decode($p['seguridad'], true);
                                    if (is_array($seg) && isset($seg['edad_min'], $seg['edad_max'])) {
                                        $edad_label = 'Edad ' . (int)$seg['edad_min'] . '–' . (int)$seg['edad_max'];
                                    }
                                }
                                if ($edad_label === '' && !empty($p['grados'])) {
                                    $gr = json_decode($p['grados'], true);
                                    if (is_array($gr) && count($gr) > 0) {
                                        $minG = min($gr); $maxG = max($gr);
                                        $edad_label = 'Grados ' . (int)$minG . '°–' . (int)$maxG . '°';
                                    }
                                }
                                $area_label = !empty($p['areas_nombres']) ? $p['areas_nombres'] : '';
                                ?>
                                <?php if ($area_label): ?><span class="area">Área: <?= h($area_label) ?></span><?php endif; ?>
                                <?php if ($edad_label): ?><span class="age"><?= h($edad_label) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($total > POSTS_PER_PAGE): ?>
                <?= pagination($total, $current_page, '/catalogo.php') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
console.log('🔍 [catalogo] Filtros activos:', <?= json_encode($filters) ?>);
console.log('✅ [catalogo] Clases cargadas:', <?= count($proyectos) ?>);

function updateSort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
<?php include 'includes/footer.php'; ?>
