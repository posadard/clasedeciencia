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

// Construir resultados como el dropdown del header (mismo algoritmo que api/clases-data.php)
$proyectos = [];
if ($q !== '') {
    try {
        $stmt = $pdo->query("
            SELECT 
                c.id,
                c.nombre,
                c.slug,
                c.ciclo,
                c.grados,
                c.dificultad,
                c.duracion_minutos,
                c.resumen,
                c.objetivo_aprendizaje,
                c.imagen_portada,
                c.destacado,
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
            ORDER BY c.destacado DESC, c.orden_popularidad DESC
        ");
        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Normalizador (quitar acentos)
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

            $dificultad_map = ['facil' => 'Fácil', 'media' => 'Media', 'dificil' => 'Difícil', 'medio' => 'Medio'];
            $dificultad_label = $dificultad_map[$normalize($clase['dificultad'])] ?? ucfirst($clase['dificultad']);

            // Keywords adicionales
            $keywords = [];
            foreach ($grados_array as $g) { $keywords[] = 'grado ' . $g; $keywords[] = $g . ' grado'; $keywords[] = 'grado' . $g; }
            $keywords[] = 'ciclo ' . $clase['ciclo'];
            $keywords[] = 'ciclo' . $clase['ciclo'];

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

            // Filtro: igual al dropdown (substring en texto normalizado)
            if ($qn === '' || strpos($search_text, $qn) !== false) {
                $proyectos[] = [
                    'id' => (int)$clase['id'],
                    'nombre' => $clase['nombre'],
                    'slug' => $clase['slug'],
                    'ciclo' => (int)$clase['ciclo'],
                    'grados' => $clase['grados'],
                    'dificultad' => $normalize($clase['dificultad']),
                    'duracion_minutos' => $clase['duracion_minutos'],
                    'resumen' => $clase['resumen'] ?? '',
                    'objetivo_aprendizaje' => $clase['objetivo_aprendizaje'] ?? '',
                    'imagen_portada' => $clase['imagen_portada'] ?? '',
                    'destacado' => (bool)$clase['destacado'],
                    'areas_nombres' => $clase['areas'] ?? ''
                ];
            }
        }
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
