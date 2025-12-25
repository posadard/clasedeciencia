<?php
// Página de detalle de Componente (kit_items)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/materials-functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('HTTP/1.1 302 Found');
    header('Location: /componentes');
    exit;
}

$material = get_material_by_slug($pdo, $slug);
if (!$material) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Componente no encontrado';
    $page_description = 'El componente solicitado no existe.';
    include 'includes/header.php';
    echo '<div class="container"><h1>Componente no encontrado</h1><p>El componente solicitado no existe.</p></div>';
    include 'includes/footer.php';
    exit;
}

$page_title = $material['common_name'];
// Preferir descripción desde HTML si existe; fallback a advertencias
$raw_desc = '';
if (!empty($material['descripcion_html'])) {
    $raw_desc = strip_tags($material['descripcion_html']);
} elseif (!empty($material['description'])) {
    $raw_desc = (string)$material['description'];
}
$page_description = generate_excerpt($raw_desc, 160);
$canonical_url = SITE_URL . '/' . urlencode($material['slug']);

// Ficha técnica del componente (resumen compacto similar a kit.php)
$ficha_inline = '';
try {
    $stmt = $pdo->prepare("SELECT c.atributo_id, c.valor_string, c.valor_numero, c.valor_entero, c.valor_booleano, c.valor_fecha, c.valor_datetime, c.valor_json, c.unidad_codigo, c.orden,
                                   d.etiqueta, d.tipo_dato, d.unidad_defecto,
                                   COALESCE(m.orden, 9999) AS map_orden
                              FROM atributos_contenidos c
                              JOIN atributos_definiciones d ON d.id = c.atributo_id
                              LEFT JOIN atributos_mapeo m ON m.atributo_id = c.atributo_id AND m.tipo_entidad = 'componente'
                             WHERE c.tipo_entidad = 'componente' AND c.entidad_id = ?
                             ORDER BY map_orden ASC, c.atributo_id ASC, c.orden ASC, c.id ASC");
    $stmt->execute([(int)$material['id']]);
    $ficha_rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $ficha_attrs = [];
    foreach ($ficha_rows as $r) {
        $aid = (int)$r['atributo_id'];
        if (!isset($ficha_attrs[$aid])) {
            $ficha_attrs[$aid] = [
                'label' => $r['etiqueta'],
                'tipo' => $r['tipo_dato'],
                'unidad_def' => $r['unidad_defecto'] ?? '',
                'values' => []
            ];
        }
        $tipo = $r['tipo_dato'];
        $unit = $r['unidad_codigo'] ?: '';
        $val = '';
        if ($tipo === 'number') { $val = $r['valor_numero'] !== null ? rtrim(rtrim((string)$r['valor_numero'], '0'), '.') : ''; }
        elseif ($tipo === 'integer') { $val = $r['valor_entero'] !== null ? (string)$r['valor_entero'] : ''; }
        elseif ($tipo === 'boolean') { $val = ((int)$r['valor_booleano'] === 1 ? 'Sí' : 'No'); }
        elseif ($tipo === 'date') { $val = $r['valor_fecha'] ?: ''; }
        elseif ($tipo === 'datetime') { $val = $r['valor_datetime'] ?: ''; }
        elseif ($tipo === 'json') { $val = $r['valor_json'] ?: ''; }
        else { $val = $r['valor_string'] ?: ''; }
        if ($val === '' || $val === null) continue;
        $ficha_attrs[$aid]['values'][] = [ 'text' => (string)$val, 'unit' => $unit ];
    }
    if (!empty($ficha_attrs)) {
        $parts = [];
        $count = 0; $max = 5;
        foreach ($ficha_attrs as $attr) {
            if ($count >= $max) { break; }
            $vals = $attr['values'];
            $units = array_values(array_unique(array_filter(array_map(function($v){ return $v['unit'] ?? ''; }, $vals))));
            $singleUnit = count($units) === 1 ? $units[0] : '';
            $texts = array_map(function($v) use ($singleUnit){
                $t = (string)$v['text'];
                if ($singleUnit === '' && !empty($v['unit'])) $t .= ' ' . $v['unit'];
                return $t;
            }, $vals);
            $display = implode(', ', $texts);
            if ($singleUnit) { $display .= ' ' . $singleUnit; }
            $parts[] = ($attr['label'] . ': ' . $display);
            $count++;
        }
        if (!empty($parts)) {
            $ficha_inline = implode(' · ', $parts);
            if (count($ficha_attrs) > $max) { $ficha_inline .= '…'; }
        }
    }
} catch (PDOException $e) {
    error_log('Error ficha tecnica componente (precompute): ' . $e->getMessage());
}

include 'includes/header.php';
?>
<div class="container material-detail">
    <div class="breadcrumb">
        <a href="/">Inicio</a> /
        <a href="/componentes">Componentes</a> /
        <?php if (!empty($material['category_slug'])): ?>
            <a href="/componentes?category=<?= urlencode($material['category_slug']) ?>"><?= h($material['category_name']) ?></a> /
        <?php endif; ?>
        <strong><?= h($material['common_name']) ?></strong>
    </div>

        <div class="clase-summary-card">
            <div class="summary-content">
                <div class="summary-left">
                    <?php if (!empty($material['foto_url'])): ?>
                        <img src="<?= h($material['foto_url']) ?>" alt="<?= h($material['common_name']) ?>" class="summary-image"
                                 onerror="this.onerror=null; console.log('❌ [Componente] Imagen falló'); var p=document.createElement('div'); p.className='summary-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='📦'; p.appendChild(s); this.replaceWith(p);" />
                    <?php else: ?>
                        <div class="summary-placeholder">
                            <span class="placeholder-icon">📦</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="summary-right">
                    <div class="summary-header">
                        <h1 class="summary-title"><?= h($material['common_name']) ?></h1>
                    </div>
                    <div class="summary-specs">
                        <div class="spec-item">
                            <span class="spec-label">📂 Categoría</span>
                            <span class="spec-value"><?= h($material['category_name']) ?></span>
                        </div>
                        <?php if (!empty($material['slug'])): ?>
                        <div class="spec-item">
                            <span class="spec-label">🏷️ SKU</span>
                            <span class="spec-value mono"><?= h($material['slug']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ficha_inline)): ?>
                        <div class="spec-item spec-item-full">
                            <span class="spec-label">🧪 Ficha</span>
                            <?php $ficha_short = $ficha_inline; if (mb_strlen($ficha_short) > 100) { $ficha_short = mb_substr($ficha_short, 0, 100) . '…'; } ?>
                            <span class="spec-value"><?= h($ficha_short) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($material['description'])): ?>
                    <section class="safety-info summary-safety">
                        <h2 class="safety-title">⚠️ Información de Seguridad</h2>
                        <div class="safety-content">
                            <div class="safety-notes"><?= nl2br(h($material['description'])) ?></div>
                            <p class="safety-note"><strong>Nota:</strong> Requiere supervisión permanente de un adulto responsable.</p>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>
            </div>
        </div>


    <?php
    // Manuales asociados al componente (ambito = componente)
    $manuales = [];
    try {
        $stmtM = $pdo->prepare("SELECT id, slug, tipo_manual, idioma, time_minutes, dificultad_ensamble, version, status, resumen, pasos_json, html
                                 FROM kit_manuals
                                 WHERE ambito = 'componente' AND item_id = ?
                                 ORDER BY tipo_manual, idioma, version DESC, id DESC");
        $stmtM->execute([(int)$material['id']]);
        $manuales = $stmtM->fetchAll(PDO::FETCH_ASSOC) ?: [];
        echo "<script>console.log('🔍 [Componente] Manuales cargados:', " . (int)count($manuales) . ");</script>";
    } catch (PDOException $e) {
        echo "<script>console.log('❌ [Componente] Error cargando manuales:', " . json_encode($e->getMessage()) . ");</script>";
        $manuales = [];
    }
    ?>

    <section class="kit-manuals">
        <h2>🛠️ Manuales Disponibles</h2>
        <?php if (!empty($manuales)): ?>
            <?php foreach ($manuales as $man):
                $href = '/' . h($man['slug']);
                $man_title = str_replace('-', ' ', (string)$man['slug']);
                $man_title = mb_convert_case($man_title, MB_CASE_TITLE, 'UTF-8');
                $idioma = !empty($man['idioma']) ? $man['idioma'] : 'es';
                $tiempo = isset($man['time_minutes']) && $man['time_minutes'] ? ((int)$man['time_minutes']) . 'm' : null;
                $version = !empty($man['version']) ? (string)$man['version'] : null;
                $dif = !empty($man['dificultad_ensamble']) ? (string)$man['dificultad_ensamble'] : null;
                $is_disc = isset($man['status']) && strtolower((string)$man['status']) === 'discontinued';
                // Icono por tipo
                $icon = '📘';
                $tipo = isset($man['tipo_manual']) ? (string)$man['tipo_manual'] : '';
                $tipo_l = mb_strtolower($tipo);
                $slug_l = mb_strtolower((string)$man['slug']);
                $map = [
                    'seguridad' => '🛡️',
                    'armado' => '🛠️',
                    'calibracion' => '🎛️',
                    'uso' => '▶️',
                    'mantenimiento' => '🧰',
                    'teoria' => '📘',
                    'experimento' => '🧪',
                    'solucion' => '🩺',
                    'evaluacion' => '✅',
                    'docente' => '👩‍🏫',
                    'referencia' => '📚'
                ];
                if (!empty($tipo_l) && isset($map[$tipo_l])) {
                    $icon = $map[$tipo_l];
                } else {
                    if (strpos($slug_l, 'segur') !== false) $icon = '🛡️';
                    elseif (strpos($slug_l, 'arm') !== false) $icon = '🛠️';
                    elseif (strpos($slug_l, 'calib') !== false) $icon = '🎛️';
                    elseif (strpos($slug_l, 'uso') !== false) $icon = '▶️';
                    elseif (strpos($slug_l, 'mant') !== false) $icon = '🧰';
                    elseif (strpos($slug_l, 'teori') !== false) $icon = '📘';
                    elseif (strpos($slug_l, 'exper') !== false) $icon = '🧪';
                    elseif (strpos($slug_l, 'solu') !== false) $icon = '🩺';
                    elseif (strpos($slug_l, 'eval') !== false) $icon = '✅';
                    elseif (strpos($slug_l, 'docen') !== false) $icon = '👩‍🏫';
                    elseif (strpos($slug_l, 'ref') !== false) $icon = '📚';
                }
                // Extracto
                $excerpt = '';
                try {
                    if (!empty($man['resumen'])) {
                        $excerpt = (string)$man['resumen'];
                    } else {
                        $firstText = '';
                        if (!empty($man['pasos_json'])) {
                            $tmp = json_decode($man['pasos_json'], true);
                            if (is_array($tmp)) {
                                foreach ($tmp as $p) {
                                    if (is_array($p)) {
                                        if (!empty($p['html'])) { $firstText = strip_tags($p['html']); }
                                        elseif (!empty($p['descripcion'])) { $firstText = (string)$p['descripcion']; }
                                        elseif (!empty($p['texto'])) { $firstText = (string)$p['texto']; }
                                    } elseif (is_string($p)) {
                                        $firstText = $p;
                                    }
                                    if ($firstText !== '') break;
                                }
                            }
                        }
                        if ($firstText === '' && !empty($man['html'])) {
                            $firstText = strip_tags($man['html']);
                        }
                        if ($firstText !== '') {
                            $excerpt = mb_substr(trim(preg_replace('/\s+/', ' ', $firstText)), 0, 160);
                            if (mb_strlen($firstText) > 160) { $excerpt .= '…'; }
                        }
                    }
                } catch (Exception $e) {
                    error_log('Error excerpt manual componente ' . (string)$man['slug'] . ': ' . $e->getMessage());
                }
            ?>
                <section class="kit-inline-card" role="link" tabindex="0" aria-label="Manual <?= h($man['slug']) ?>"
                         onclick="if(!event.target.closest('a')){ console.log('📘 [Componente] Click manual →','<?= h($man['slug']) ?>'); window.location.href='<?= h($href) ?>'; }"
                         onkeypress="if(event.key==='Enter' || event.key===' '){ if(!event.target.closest('a')){ window.location.href='<?= h($href) ?>'; event.preventDefault(); } }">
                    <div class="kit-inline-wrap">
                        <div class="kit-inline-left">
                            <div class="manual-type-emoji" aria-hidden="true"><?= $icon ?></div>
                        </div>
                        <div class="kit-inline-right">
                            <h3 class="kit-inline-title">
                                <span><?= h($man_title) ?></span>
                                <?php if ($is_disc): ?><span class="badge badge-danger" style="margin-left:8px;">⚠️ Descontinuado</span><?php endif; ?>
                                <span class="kit-inline-byline">
                                    🌐 <?= h($idioma) ?>
                                    <?= $tiempo ? ' · ⏱️ ' . h($tiempo) : '' ?>
                                    <?= $dif ? ' · 🛠️ ' . h(ucfirst($dif)) : '' ?>
                                    <?= $version ? ' · 🔢 v' . h($version) : '' ?>
                                </span>
                            </h3>
                            <?php if ($excerpt !== ''): ?>
                                <p class="man-excerpt"><?= h($excerpt) ?></p>
                            <?php else: ?>
                                <div class="kit-inline-manuales">
                                    <span class="man-label">Abrir:</span>
                                    <div class="man-pills">
                                        <a class="tag-pill" href="<?= h($href) ?>" title="Ver manual <?= h($man['slug']) ?>">
                                            <?= h($man['slug']) ?> · <?= h($idioma) ?><?= $tiempo ? ' · ⏱️ ' . h($tiempo) : '' ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
            <script>
                (function(){
                    var total = <?= count($manuales) ?>;
                    var discontinued = <?= json_encode(array_values(array_filter(array_map(function($m){ return strtolower((string)($m['status'] ?? '')) === 'discontinued'; }, $manuales)))) ?>.length;
                    console.log('✅ [Componente] Manuales renderizados:', total);
                    console.log('⚠️ [Componente] Manuales descontinuados:', discontinued);
                })();
            </script>
        <?php else: ?>
            <p class="muted">Aún no hay manuales publicados para este componente.</p>
        <?php endif; ?>
    </section>

    <?php if (!empty($material['descripcion_html'])): ?>
    <div class="content-section">
        <h2>Descripción</h2>
        <div class="article-body">
            <?= $material['descripcion_html'] ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($ficha_inline !== ''): ?>
    <div class="article-byline">
        <span class="ficha">🧪 <?= h($ficha_inline) ?></span>
    </div>
    <?php endif; ?>



    <?php
    // Kits que incluyen este componente
    $kits_rel = [];
    try {
        $stmtK = $pdo->prepare("SELECT k.id, k.nombre, k.slug, k.codigo, k.version, k.updated_at
                                 FROM kit_componentes kc
                                 JOIN kits k ON k.id = kc.kit_id
                                 WHERE kc.item_id = ? AND k.activo = 1
                                 ORDER BY k.updated_at DESC, k.id DESC");
        $stmtK->execute([(int)$material['id']]);
        $kits_rel = $stmtK->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log('Error componente.kits: ' . $e->getMessage());
        $kits_rel = [];
    }

    // Clases asociadas a esos kits
    $clases_rel = [];
    if (!empty($kits_rel)) {
        $kitIds = array_column($kits_rel, 'id');
        $ph = implode(',', array_fill(0, count($kitIds), '?'));
        try {
            $sqlC = "SELECT DISTINCT c.*
                     FROM clases c
                     JOIN clase_kits ck ON ck.clase_id = c.id
                     WHERE ck.kit_id IN ($ph) AND c.activo = 1
                     ORDER BY c.destacado DESC, c.updated_at DESC";
            $stmtC = $pdo->prepare($sqlC);
            $stmtC->execute($kitIds);
            $clases_rel = $stmtC->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error componente.clases: ' . $e->getMessage());
            $clases_rel = [];
        }
    }
    ?>

    <?php if (!empty($kits_rel)): ?>
    <section class="kit-uses">
        <h2>🧰 Kits que incluyen este componente</h2>
        <div class="articles-grid">
            <?php foreach ($kits_rel as $k): ?>
            <article class="article-card" data-href="/<?= h($k['slug']) ?>">
                <a class="card-link" href="/<?= h($k['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge">Kit</span>
                            <?php if (!empty($k['codigo'])): ?>
                            <span class="difficulty-badge">Código: <?= h($k['codigo']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?= h($k['nombre']) ?>
                            <span title="Ver kit" aria-label="Ver kit" style="margin-left:6px">🔎</span>
                        </h3>
                        <div class="card-footer">
                            <?php if (!empty($k['version'])): ?><span class="area">Versión <?= h($k['version']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($clases_rel)): ?>
    <section class="related-classes">
        <h2>📚 Clases asociadas</h2>
        <div class="related-grid">
            <?php foreach ($clases_rel as $c): ?>
                <a href="/<?= h($c['slug']) ?>" class="related-card">
                    <?php if (!empty($c['imagen_portada'])): ?>
                        <img src="<?= h($c['imagen_portada']) ?>" alt="<?= h($c['nombre']) ?>" class="related-thumbnail" loading="lazy" onerror="this.onerror=null; console.log('❌ [Componente] Miniatura clase falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
                    <?php endif; ?>
                    <div class="related-info">
                        <h4><?= h($c['nombre']) ?></h4>
                        <div class="related-meta">
                            <span class="badge">Ciclo <?= h($c['ciclo']) ?></span>
                            <span class="badge"><?= h(ucfirst($c['dificultad'] ?? '')) ?></span>
                        </div>
                        <?php if (!empty($c['resumen'])): ?>
                            <p class="related-excerpt"><?= h(mb_substr($c['resumen'], 0, 100)) ?>...</p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <a href="/componentes?category=<?= urlencode($material['category_slug']) ?>" class="back-button">← Volver a <?= h($material['category_name']) ?></a>
</div>
<script>
console.log('🔍 [componente] Slug:', <?= json_encode($slug) ?>);
console.log('✅ [componente] Cargado:', <?= json_encode(['slug'=>$material['slug'],'nombre'=>$material['common_name']]) ?>);
console.log('🖼️ [componente] Foto URL:', <?= json_encode($material['foto_url'] ?? null) ?>);
console.log('📝 [componente] HTML presente:', <?= json_encode(!empty($material['descripcion_html'])) ?>);
console.log('🔬 [componente] Ficha inline:', <?= json_encode($ficha_inline !== '' ? true : false) ?>);
console.log('🧰 [componente] Kits relacionados:', <?= isset($kits_rel) ? count($kits_rel) : 0 ?>);
console.log('📚 [componente] Clases relacionadas:', <?= isset($clases_rel) ? count($clases_rel) : 0 ?>);
</script>
<style>
/* Reutilizar estilo de tarjetas de manuales de kit */
.kit-inline-card .kit-inline-wrap { display:flex; gap:12px; align-items:center; }
.kit-inline-card .kit-inline-left { flex: 0 0 72px; width:72px; display:flex; align-items:center; justify-content:center; }
.kit-inline-card .manual-type-emoji { font-size:56px; line-height:1; filter: drop-shadow(0 1px 0 rgba(0,0,0,0.06)); }
.kit-inline-card { border:1px solid var(--color-border-light); border-radius:8px; padding:12px; background:#fff; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
.kit-inline-card:hover, .kit-inline-card:focus-within { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--color-accent); }
.kit-inline-card .manual-type-emoji { transition: transform .15s ease; }
.kit-inline-card:hover .manual-type-emoji, .kit-inline-card:focus-within .manual-type-emoji { transform: scale(1.08) rotate(-2deg); }
.kit-inline-card:hover .kit-inline-title span:first-child, .kit-inline-card:focus-within .kit-inline-title span:first-child { text-decoration: underline; }
@media (max-width: 600px) {
    .kit-inline-card .kit-inline-left { flex-basis:56px; width:56px; }
    .kit-inline-card .manual-type-emoji { font-size:44px; }
}
</style>
<?php include 'includes/footer.php'; ?>
