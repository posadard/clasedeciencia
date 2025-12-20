<?php
// Catalogo - Lista de proyectos con filtros (CdC)
require_once 'config.php';
require_once 'includes/functions.php';

// Nota: usamos funciones locales mientras adaptamos includes/db-functions.php a CdC
function cdc_get_areas($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug, color FROM areas ORDER BY nombre");
    return $stmt->fetchAll();
}

function cdc_get_competencias($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, tipo FROM competencias ORDER BY id");
    return $stmt->fetchAll();
}

function cdc_get_proyectos($pdo, $filters = [], $limit = 12, $offset = 0) {
    $params = [];
    $where = ["p.activo = 1"];
    $joins = [];

    if (!empty($filters['ciclo'])) {
        $where[] = "p.ciclo = ?";
        $params[] = $filters['ciclo'];
    }
    if (!empty($filters['grado'])) {
        // grados es JSON: usamos JSON_CONTAINS
        $where[] = "JSON_CONTAINS(p.grados, ? )";
        $params[] = json_encode([(int)$filters['grado']]);
    }
    if (!empty($filters['area'])) {
        // areas como JSON ids o tabla puente proyecto_areas
        $joins[] = "LEFT JOIN proyecto_areas pa ON pa.proyecto_id = p.id";
        if (is_numeric($filters['area'])) {
            $where[] = "pa.area_id = ?";
            $params[] = (int)$filters['area'];
        } else {
            $joins[] = "LEFT JOIN areas a ON a.id = pa.area_id";
            $where[] = "a.slug = ?";
            $params[] = $filters['area'];
        }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN proyecto_competencias pc ON pc.proyecto_id = p.id";
        if (is_numeric($filters['competencia'])) {
            $where[] = "pc.competencia_id = ?";
            $params[] = (int)$filters['competencia'];
        }
    }
    if (!empty($filters['dificultad'])) {
        $where[] = "p.dificultad = ?";
        $params[] = $filters['dificultad'];
    }

    $sql = "SELECT p.*
            FROM proyectos p
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY p.id
            ORDER BY p.destacado DESC, p.orden_popularidad DESC, p.updated_at DESC
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cdc_count_proyectos($pdo, $filters = []) {
    $params = [];
    $where = ["p.activo = 1"];
    $joins = [];

    if (!empty($filters['ciclo'])) {
        $where[] = "p.ciclo = ?";
        $params[] = $filters['ciclo'];
    }
    if (!empty($filters['grado'])) {
        $where[] = "JSON_CONTAINS(p.grados, ? )";
        $params[] = json_encode([(int)$filters['grado']]);
    }
    if (!empty($filters['area'])) {
        $joins[] = "LEFT JOIN proyecto_areas pa ON pa.proyecto_id = p.id";
        if (is_numeric($filters['area'])) {
            $where[] = "pa.area_id = ?";
            $params[] = (int)$filters['area'];
        } else {
            $joins[] = "LEFT JOIN areas a ON a.id = pa.area_id";
            $where[] = "a.slug = ?";
            $params[] = $filters['area'];
        }
    }
    if (!empty($filters['competencia'])) {
        $joins[] = "LEFT JOIN proyecto_competencias pc ON pc.proyecto_id = p.id";
        if (is_numeric($filters['competencia'])) {
            $where[] = "pc.competencia_id = ?";
            $params[] = (int)$filters['competencia'];
        }
    }
    if (!empty($filters['dificultad'])) {
        $where[] = "p.dificultad = ?";
        $params[] = $filters['dificultad'];
    }

    $sql = "SELECT COUNT(DISTINCT p.id) AS total
            FROM proyectos p
            " . implode(' ', array_unique($joins)) . "
            WHERE " . implode(' AND ', $where);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

// Obtener filtros
$filters = [];
if (isset($_GET['ciclo']) && in_array($_GET['ciclo'], ['1','2','3'])) $filters['ciclo'] = $_GET['ciclo'];
if (isset($_GET['grado'])) $filters['grado'] = $_GET['grado'];
if (isset($_GET['area'])) $filters['area'] = $_GET['area'];
if (isset($_GET['competencia'])) $filters['competencia'] = $_GET['competencia'];
if (isset($_GET['dificultad'])) $filters['dificultad'] = $_GET['dificultad'];

$page_title = 'Catálogo de Proyectos';
$page_description = 'Explora proyectos científicos por ciclo, grado, área y competencias MEN.';
$canonical_url = SITE_URL . '/catalogo.php';

$current_page = get_current_page();
$offset = get_offset($current_page);
$areas = cdc_get_areas($pdo);
$competencias = cdc_get_competencias($pdo);
$proyectos = cdc_get_proyectos($pdo, $filters, POSTS_PER_PAGE, $offset);
$total = cdc_count_proyectos($pdo, $filters);

// Schema.org ItemList
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Catálogo de Proyectos Científicos',
    'description' => $page_description,
    'url' => $canonical_url,
    'numberOfItems' => $total
];
if (!empty($proyectos)) {
    $pos = ($current_page - 1) * POSTS_PER_PAGE + 1;
    $schema['itemListElement'] = array_map(function($p) use (&$pos) {
        $item = [
            '@type' => 'CreativeWork',
            'name' => $p['nombre'],
            'url' => SITE_URL . '/proyecto.php?slug=' . $p['slug'],
            'description' => $p['resumen']
        ];
        return ['@type' => 'ListItem', 'position' => $pos++, 'item' => $item];
    }, $proyectos);
}
$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

include 'includes/header.php';
?>
<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Catálogo</strong>
    </div>
    <h1>Catálogo de Proyectos</h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Filtros</h2>
            <form method="get" action="/catalogo.php" class="filters-form">
                <div class="filter-group">
                    <label>Ciclo</label>
                    <select name="ciclo">
                        <option value="">Todos</option>
                        <option value="1" <?= isset($filters['ciclo']) && $filters['ciclo']==='1'?'selected':'' ?>>Exploración (6°-7°)</option>
                        <option value="2" <?= isset($filters['ciclo']) && $filters['ciclo']==='2'?'selected':'' ?>>Experimentación (8°-9°)</option>
                        <option value="3" <?= isset($filters['ciclo']) && $filters['ciclo']==='3'?'selected':'' ?>>Análisis (10°-11°)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Grado</label>
                    <select name="grado">
                        <option value="">Todos</option>
                        <?php for($g=6;$g<=11;$g++): ?>
                        <option value="<?= $g ?>" <?= isset($filters['grado']) && (int)$filters['grado']===$g?'selected':'' ?>><?= $g ?>°</option>
                        <?php endfor; ?>
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
                <p class="results-count">
                    Mostrando <?= count($proyectos) ?> de <?= $total ?> proyectos
                    <?php if ($total > POSTS_PER_PAGE): ?>
                        (Página <?= $current_page ?> de <?= ceil($total / POSTS_PER_PAGE) ?>)
                    <?php endif; ?>
                </p>
            </div>
            <?php if (empty($proyectos)): ?>
            <div class="no-results">
                <p>No hay proyectos con los filtros seleccionados.</p>
                <a href="/catalogo.php" class="btn btn-secondary">Ver todos</a>
            </div>
            <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($proyectos as $p): ?>
                <article class="article-card" data-href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                    <a class="card-link" href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                        <div class="card-content">
                            <div class="card-meta">
                                <span class="section-badge">Ciclo <?= h($p['ciclo']) ?></span>
                                <span class="difficulty-badge"><?= h(ucfirst($p['dificultad'])) ?></span>
                            </div>
                            <h3><?= h($p['nombre']) ?></h3>
                            <p class="excerpt"><?= h($p['resumen']) ?></p>
                            <div class="card-footer">
                                <span class="read-time"><?= (int)$p['duracion_minutos'] ?> min</span>
                                <span class="date">Actualizado <?= format_date($p['updated_at'], 'M j, Y') ?></span>
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
console.log('✅ [catalogo] Proyectos cargados:', <?= count($proyectos) ?>);
</script>
<?php include 'includes/footer.php'; ?>
