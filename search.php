<?php
/**
 * Página de Resultados de Búsqueda (Clases)
 * - Mismo layout que el catálogo de clases
 * - Solo muestra resultados de "q"
 * - Al usar filtros/orden, redirige a catalogo.php
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$q = trim($_GET['q'] ?? '');

$page_title = $q ? 'Resultados de búsqueda' : 'Búsqueda';
$page_description = $q ? 'Resultados de búsqueda para: ' . h($q) : 'Busca clases de ciencia por tema, ciclo y grado';
$canonical_url = SITE_URL . '/search.php' . ($q ? ('?q=' . urlencode($q)) : '');

// Helpers locales (simplificados) para sidebar
function cdc_get_areas_local($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug FROM areas ORDER BY nombre");
    return $stmt->fetchAll();
}
function cdc_get_ciclos_local($pdo, $activo_only = true) {
    $sql = "SELECT numero, nombre, slug, grados_texto, activo, orden FROM ciclos ";
    if ($activo_only) $sql .= "WHERE activo = 1 ";
    $sql .= "ORDER BY orden ASC, numero ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// Consulta de clases con búsqueda acento-insensible y áreas
$proyectos = [];
if ($q !== '') {
    try {
        $like = '%' . $q . '%';
        // Normalización en SQL para acentos (sin cambiar collation)
        $q_norm = mb_strtolower($q, 'UTF-8');
        $q_norm = strtr($q_norm, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
        $like_norm = '%' . $q_norm . '%';

        $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') AS areas_nombres
                FROM clases c
                LEFT JOIN clase_areas ca ON ca.clase_id = c.id
                LEFT JOIN areas a ON a.id = ca.area_id
                WHERE c.activo = 1 AND (
                    c.nombre LIKE :like OR c.resumen LIKE :like OR c.objetivo_aprendizaje LIKE :like OR a.nombre LIKE :like
                    OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE :like_norm
                    OR LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.nombre,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n')) LIKE :like_norm
                )
                GROUP BY c.id
                ORDER BY c.destacado DESC, c.orden_popularidad DESC, c.updated_at DESC
                LIMIT 30";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['like' => $like, 'like_norm' => $like_norm]);
        $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error en búsqueda de clases: ' . $e->getMessage());
        $proyectos = [];
    }
}

include 'includes/header.php';
?>

<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Resultados</strong>
    </div>
    <h1>Resultados de búsqueda</h1>

    <div class="library-layout">
        <aside class="filters-sidebar">
            <h2>Filtros</h2>
            <form method="get" action="/catalogo.php" class="filters-form">
                <div class="filter-group">
                    <label>Ciclo</label>
                    <select name="ciclo">
                        <option value="">Todos</option>
                        <?php $ciclos = cdc_get_ciclos_local($pdo, true); foreach ($ciclos as $cf): ?>
                        <option value="<?= h($cf['numero']) ?>"><?= h($cf['nombre']) ?> (<?= h($cf['grados_texto']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Área</label>
                    <select name="area">
                        <option value="">Todas</option>
                        <?php $areas = cdc_get_areas_local($pdo); foreach ($areas as $a): ?>
                        <option value="<?= h($a['slug']) ?>"><?= h($a['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Dificultad</label>
                    <select name="dificultad">
                        <option value="">Todas</option>
                        <option value="facil">Fácil</option>
                        <option value="medio">Medio</option>
                        <option value="dificil">Difícil</option>
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
                <?php if ($q !== ''): ?>
                <div class="search-active-banner">
                    <span class="search-term">🔍 Resultados para: <strong><?= h($q) ?></strong></span>
                    <a href="/catalogo.php" class="clear-search">✕ Ir al catálogo</a>
                </div>
                <?php endif; ?>
                <p class="results-count">
                    Mostrando <?= count($proyectos) ?> clases<?php if ($q !== ''): ?> de búsqueda<?php endif; ?>
                </p>
                <div class="sort-selector">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" onchange="redirectSort(this.value)">
                        <option value="recomendados" selected>📌 Recomendados primero</option>
                        <option value="recientes">🕐 Más recientes</option>
                        <option value="grado">🎓 Por Grado (1° a 11°)</option>
                    </select>
                </div>
            </div>

            <?php if (empty($proyectos)): ?>
            <div class="no-results">
                <p>No hay clases para este término de búsqueda.</p>
                <a href="/catalogo.php" class="btn btn-secondary">Ver catálogo</a>
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
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
console.log('🔍 [search] Query:', <?= json_encode($q) ?>);
console.log('✅ [search] Clases encontradas:', <?= count($proyectos) ?>);

function redirectSort(sortValue) {
    const url = new URL(window.location.origin + '/catalogo.php');
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
<?php include 'includes/footer.php'; ?>
