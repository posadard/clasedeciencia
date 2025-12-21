<?php
// Página global de resultados de búsqueda (Clases, Kits, Componentes)
require_once 'config.php';
require_once 'includes/functions.php';

$q = trim($_GET['q'] ?? '');
$page_title = $q !== '' ? ('Resultados: ' . $q) : 'Resultados de búsqueda';
$page_description = 'Resultados de búsqueda de clases, kits y componentes en Clase de Ciencia.';
$canonical_url = SITE_URL . '/buscar.php' . ($q !== '' ? ('?q=' . urlencode($q)) : '');

// Normalizador sin acentos
$normalize = function ($text) {
    $text = strtolower((string)$text);
    $text = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'],
        ['a','e','i','o','u','a','e','i','o','u','n','n','u','u'],
        $text
    );
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
};

$qn = $normalize($q);
$resultados = [
    'clases' => [],
    'kits' => [],
    'componentes' => []
];

try {
    if (!isset($pdo)) { throw new Exception('Conexión no disponible'); }

    // CLASES (similar a api/clases-data.php)
    $stmtC = $pdo->query("\n        SELECT \n            c.id, c.nombre, c.slug, c.ciclo, c.grados, c.dificultad, c.duracion_minutos,\n            c.resumen, c.objetivo_aprendizaje, c.imagen_portada, c.destacado,\n            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,\n            GROUP_CONCAT(DISTINCT comp.nombre ORDER BY comp.nombre SEPARATOR ' | ') AS competencias,\n            GROUP_CONCAT(DISTINCT ct.tag ORDER BY ct.tag SEPARATOR ', ') AS tags\n        FROM clases c\n        LEFT JOIN clase_areas ca ON ca.clase_id = c.id\n        LEFT JOIN areas a ON a.id = ca.area_id\n        LEFT JOIN clase_competencias cc ON cc.clase_id = c.id\n        LEFT JOIN competencias comp ON comp.id = cc.competencia_id\n        LEFT JOIN clase_tags ct ON ct.clase_id = c.id\n        WHERE c.activo = 1\n        GROUP BY c.id\n        ORDER BY c.destacado DESC, c.orden_popularidad DESC\n    ");
    $clases = $stmtC->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $ciclos_nombres = [1=>'Ciclo 1: Exploración',2=>'Ciclo 2: Experimentación',3=>'Ciclo 3: Análisis'];
    $dmap = ['facil'=>'Fácil','media'=>'Media','dificil'=>'Difícil'];
    foreach ($clases as $cl) {
        $grados_array = json_decode($cl['grados'] ?? '[]', true) ?: [];
        $grados_texto = $grados_array ? (implode('°, ', $grados_array) . '°') : '';
        $ciclo_nombre = $ciclos_nombres[(int)($cl['ciclo'] ?? 0)] ?? ('Ciclo ' . (int)($cl['ciclo'] ?? 0));
        $dificultad = $dmap[$cl['dificultad'] ?? ''] ?? ucfirst((string)($cl['dificultad'] ?? ''));

        $keywords = [];
        foreach ($grados_array as $g) { $keywords[] = 'grado ' . $g; $keywords[] = $g . ' grado'; $keywords[] = 'grado' . $g; }
        $keywords[] = 'ciclo ' . ($cl['ciclo'] ?? '');
        $keywords[] = 'ciclo' . ($cl['ciclo'] ?? '');

        if (!empty($cl['competencias'])) {
            foreach (explode(' | ', $cl['competencias']) as $comp) {
                $t = $normalize($comp);
                if (strpos($t,'indagacion')!==false || strpos($t,'pregunta')!==false) { $keywords = array_merge($keywords,['indagacion','preguntas','investigacion']); }
                if (strpos($t,'explicacion')!==false || strpos($t,'explico')!==false) { $keywords = array_merge($keywords,['explicacion','explicar','razonamiento']); }
                if (strpos($t,'uso')!==false || strpos($t,'aplico')!==false) { $keywords = array_merge($keywords,['aplicacion','practica','cotidiano']); }
                if (strpos($t,'observo')!==false || strpos($t,'registro')!==false) { $keywords = array_merge($keywords,['observacion','datos','registro']); }
                if (strpos($t,'modelo')!==false) { $keywords = array_merge($keywords,['modelado','representacion']); }
                if (strpos($t,'calculo')!==false || strpos($t,'medicion')!==false) { $keywords = array_merge($keywords,['medicion','calculo','matematicas']); }
            }
        }

        $search_parts = [
            $cl['nombre'] ?? '', $cl['resumen'] ?? '', $cl['objetivo_aprendizaje'] ?? '',
            $cl['areas'] ?? '', $cl['tags'] ?? '', $ciclo_nombre, $grados_texto, $dificultad,
            implode(' ', array_unique($keywords))
        ];
        $st = $normalize(implode(' ', $search_parts));
        if ($qn === '' || ($st !== '' && strpos($st, $qn) !== false)) {
            $resultados['clases'][] = [
                'type' => 'clase',
                'title' => $cl['nombre'] ?? '',
                'url' => '/' . ($cl['slug'] ?? ''),
                'description' => $cl['resumen'] ?? '',
                'ciclo_nombre' => $ciclo_nombre,
                'grados' => $grados_texto,
                'difficulty' => $dificultad
            ];
        }
    }

    // KITS (similar a api/kits-data.php)
    $stmtK = $pdo->query("SELECT k.id, k.nombre, k.slug, k.codigo FROM kits k WHERE k.activo = 1 ORDER BY k.updated_at DESC, k.id DESC");
    $kits = $stmtK->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($kits as $k) {
        $st = $normalize(($k['nombre'] ?? '') . ' ' . ($k['codigo'] ?? '') . ' kit');
        if ($qn === '' || ($st !== '' && strpos($st, $qn) !== false)) {
            $resultados['kits'][] = [
                'type' => 'kit',
                'title' => $k['nombre'] ?? '',
                'url' => '/' . ($k['slug'] ?? ''),
                'description' => !empty($k['codigo']) ? ('Código: ' . $k['codigo']) : ''
            ];
        }
    }

    // COMPONENTES (similar a api/componentes-data.php)
    $stmtM = $pdo->query("\n        SELECT m.id, m.slug, m.nombre_comun, m.advertencias_seguridad,\n               c.nombre AS categoria_nombre\n        FROM kit_items m\n        LEFT JOIN categorias_items c ON c.id = m.categoria_id\n        ORDER BY c.nombre ASC, m.nombre_comun ASC\n    ");
    $items = $stmtM->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($items as $it) {
        $st = $normalize(($it['nombre_comun'] ?? '') . ' ' . ($it['categoria_nombre'] ?? '') . ' ' . ($it['advertencias_seguridad'] ?? '') . ' componente');
        if ($qn === '' || ($st !== '' && strpos($st, $qn) !== false)) {
            $resultados['componentes'][] = [
                'type' => 'componente',
                'title' => $it['nombre_comun'] ?? '',
                'url' => '/' . ($it['slug'] ?? ''),
                'description' => $it['advertencias_seguridad'] ?? '',
                'categoria' => $it['categoria_nombre'] ?? ''
            ];
        }
    }
} catch (Throwable $e) {
    error_log('buscar.php error: ' . $e->getMessage());
}

include 'includes/header.php';
?>
<div class="container library-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong>Resultados</strong>
    </div>
    <h1>Resultados de búsqueda</h1>

    <div class="results-header">
        <div class="search-active-banner">
            <span class="search-term">🔍 Búsqueda: <strong><?= h($q) ?></strong></span>
        </div>
        <p class="results-count">
            <?= count($resultados['clases']) + count($resultados['kits']) + count($resultados['componentes']) ?> resultados totales
            (Clases: <?= count($resultados['clases']) ?> · Kits: <?= count($resultados['kits']) ?> · Componentes: <?= count($resultados['componentes']) ?>)
        </p>
    </div>

    <?php
    $renderSection = function ($titulo, $items, $type) {
        if (empty($items)) return;
        echo '<section class="search-section">';
        echo '<div class="search-section-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">';
        echo '<h2 style="margin:0;">' . h($titulo) . ' (' . count($items) . ')</h2>';
        $btnText = $type === 'clase' ? 'Ver en Clases' : ($type === 'kit' ? 'Ver en Kits' : 'Ver en Componentes');
        echo '<button type="button" class="btn btn-secondary" onclick="window.cdcSearchRedirect(\'' . h($type) . '\')">' . h($btnText) . '</button>';
        echo '</div>';
        echo '<div class="articles-grid">';
        foreach ($items as $it) {
            echo '<article class="article-card" data-href="' . h($it['url']) . '">';
            echo '<a class="card-link" href="' . h($it['url']) . '">';
            echo '<div class="card-content">';
            echo '<div class="card-meta">';
            echo '<span class="section-badge">' . h(ucfirst($type)) . '</span>';
            echo '</div>';
            echo '<h3>' . h($it['title']) . '</h3>';
            if (!empty($it['description'])) {
                echo '<p class="excerpt"><small>' . h($it['description']) . '</small></p>';
            }
            echo '</div>';
            echo '</a>';
            echo '</article>';
        }
        echo '</div>';
        echo '</section>';
    };
    ?>

    <?php $renderSection('Clases', $resultados['clases'], 'clase'); ?>
    <?php $renderSection('Kits', $resultados['kits'], 'kit'); ?>
    <?php $renderSection('Componentes', $resultados['componentes'], 'componente'); ?>
</div>
<script>
console.log('🔍 [buscar] término:', <?= json_encode($q) ?>);
console.log('✅ [buscar] conteos:', {
  clases: <?= (int)count($resultados['clases']) ?>,
  kits: <?= (int)count($resultados['kits']) ?>,
  componentes: <?= (int)count($resultados['componentes']) ?>
});

// Botones de búsqueda relacionada por tipo con parseo inteligente del query
(function(){
    const query = <?= json_encode($q) ?> || '';
    const normalize = (text) => (text||'')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9°\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const parseIntent = (q) => {
        const qn = normalize(q);
        const tokens = qn.split(' ').filter(Boolean);
        const out = { grado:null, ciclo:null, dificultad:null, area:null };

        // grado
        const gradoWords = { 'primero':1, 'primer':1, 'segundo':2, 'tercero':3, 'cuarto':4, 'quinto':5, 'sexto':6, 'septimo':7, 'séptimo':7, 'octavo':8, 'noveno':9, 'decimo':10, 'décimo':10, 'once':11, 'undecimo':11, 'undécimo':11 };
        let grado = null;
        const m1 = qn.match(/grado\s*(\d{1,2})/); if (m1) grado = parseInt(m1[1],10);
        if (!grado){ const m2 = qn.match(/(\d{1,2})\s*°/); if (m2) grado = parseInt(m2[1],10); }
        if (!grado){ for (const t of tokens){ if (gradoWords[t]) { grado = gradoWords[t]; break; } } }
        if (grado && grado>=1 && grado<=11) out.grado = grado;

        // ciclo
        let ciclo = null; const mC = qn.match(/ciclo\s*(\d)/); if (mC) ciclo = parseInt(mC[1],10);
        if (!ciclo){ if (tokens.includes('exploracion')) ciclo=1; else if (tokens.includes('experimentacion')) ciclo=2; else if (tokens.includes('analisis')) ciclo=3; }
        if (ciclo && [1,2,3].includes(ciclo)) out.ciclo = ciclo;

        // dificultad
        if (tokens.includes('facil')) out.dificultad='facil';
        if (tokens.includes('medio')||tokens.includes('media')||tokens.includes('intermedio')) out.dificultad='medio';
        if (tokens.includes('dificil')||tokens.includes('avanzado')) out.dificultad='dificil';

        // area
        const areaMap = { 'fisica':'fisica','quimica':'quimica','biologia':'biologia','ambiental':'ambiental','tecnologia':'tecnologia' };
        for (const t of tokens){ if (areaMap[t]) { out.area = areaMap[t]; break; } }

        return out;
    };

    const intent = parseIntent(query);
    console.log('🔍 [buscar] Intent parse:', intent);

    const buildUrl = (base, params) => {
        const usp = new URLSearchParams();
        Object.entries(params).forEach(([k,v])=>{ if (v!==null && v!=='' && v!==undefined) usp.set(k,String(v)); });
        const qs = usp.toString();
        return qs ? `${base}?${qs}` : base;
    };

    // Redirección global usada por botones en cada sección
    window.cdcSearchRedirect = function(type) {
        if (type === 'clase') {
            const params = { busqueda: query };
            if (intent.grado) params.grado = intent.grado;
            if (intent.ciclo) params.ciclo = intent.ciclo;
            if (intent.dificultad) params.dificultad = intent.dificultad;
            if (intent.area) params.area = intent.area;
            const url = buildUrl('/clases', params);
            console.log('✅ [buscar] Redirigiendo a Clases:', url);
            window.location.href = url;
            return;
        }
        if (type === 'kit') {
            const url = buildUrl('/kits', { q: query });
            console.log('✅ [buscar] Redirigiendo a Kits:', url);
            window.location.href = url;
            return;
        }
        if (type === 'componente') {
            const params = { q: query };
            const url = buildUrl('/componentes', params);
            console.log('✅ [buscar] Redirigiendo a Componentes:', url);
            window.location.href = url;
            return;
        }
    };

    // Los botones superiores se han eliminado y ahora se ubican en cada sección mediante cdcSearchRedirect()
})();
</script>
<?php include 'includes/footer.php'; ?>
