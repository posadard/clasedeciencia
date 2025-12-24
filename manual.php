<?php
// Manual detail (public) for kits and components
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$kit_slug = isset($_GET['kit']) ? trim($_GET['kit']) : '';
$manual_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$comp_slug = isset($_GET['comp']) ? trim($_GET['comp']) : '';

// Allow resolution via component slug when kit slug is absent
if ($kit_slug === '' && $manual_slug !== '' && $comp_slug !== '') {
  try {
    $stmtI = $pdo->prepare('SELECT id FROM kit_items WHERE slug = ? LIMIT 1');
    $stmtI->execute([$comp_slug]);
    $comp_id = (int)($stmtI->fetchColumn() ?: 0);
  } catch (Exception $e) { $comp_id = 0; }
  if ($comp_id > 0) {
    try {
      $stmtM = $pdo->prepare("SELECT * FROM kit_manuals WHERE slug = ? AND item_id = ? AND status = 'published' LIMIT 1");
      $stmtM->execute([$manual_slug, $comp_id]);
      $manual = $stmtM->fetch(PDO::FETCH_ASSOC);
      if ($manual) {
        $stmtK = $pdo->prepare('SELECT id, nombre, slug, codigo, version, resumen, contenido_html, imagen_portada, video_portada, time_minutes, dificultad_ensamble, seguridad, seo_title, seo_description, activo, updated_at FROM kits WHERE id = ? AND activo = 1 LIMIT 1');
        $stmtK->execute([(int)$manual['kit_id']]);
        $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
        if ($kit) { $kit_slug = (string)$kit['slug']; }
      }
    } catch (Exception $e) { /* no-op */ }
  }
}

if ($kit_slug === '' || $manual_slug === '') {
    header('HTTP/1.1 302 Found');
    header('Location: /');
    exit;
}

$kit = cdc_get_kit_by_slug($pdo, $kit_slug);
if (!$kit) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Kit no encontrado';
    $page_description = 'El kit solicitado no existe o no está activo.';
    include 'includes/header.php';
    echo '<div class="container"><h1>Kit no encontrado</h1></div>';
    include 'includes/footer.php';
    exit;
}

$manual = cdc_get_kit_manual_by_slug($pdo, (int)$kit['id'], $manual_slug, true);
if (!$manual) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Manual no encontrado';
    $page_description = 'El manual solicitado no existe o no está publicado.';
    include 'includes/header.php';
    echo '<div class="container"><div class="breadcrumb"><a href="/">Inicio</a> / <a href="/kit.php?slug=' . h($kit['slug']) . '">Kit</a> / <strong>Manual</strong></div><h1>Manual no encontrado</h1></div>';
    include 'includes/footer.php';
    exit;
}

// Título y descripción usando Tipo, Versión y Entidad (Kit/Componente)
$entity_name = ($ambito === 'componente' && $comp && !empty($comp['nombre_comun']))
  ? (string)$comp['nombre_comun']
  : (string)$kit['nombre'];
$version_text = !empty($manual['version']) ? (' ' . (string)$manual['version']) : '';
$page_title = 'Manual: ' . h($tipo_label) . $version_text . ' ' . h($entity_name);
$page_description = 'Manual ' . h($tipo_label) . $version_text . ' de ' . h($entity_name);

// Tipo/Ambito/Icono y componente vinculado si aplica
$tipo_map = [
  'seguridad' => ['emoji' => '🛡️', 'label' => 'Seguridad'],
  'armado' => ['emoji' => '🛠️', 'label' => 'Armado'],
  'calibracion' => ['emoji' => '🎛️', 'label' => 'Calibración'],
  'uso' => ['emoji' => '▶️', 'label' => 'Uso'],
  'mantenimiento' => ['emoji' => '🧰', 'label' => 'Mantenimiento'],
  'teoria' => ['emoji' => '📘', 'label' => 'Teoría'],
  'experimento' => ['emoji' => '🧪', 'label' => 'Experimento'],
  'solucion' => ['emoji' => '🩺', 'label' => 'Solución'],
  'evaluacion' => ['emoji' => '✅', 'label' => 'Evaluación'],
  'docente' => ['emoji' => '👩‍🏫', 'label' => 'Docente'],
  'referencia' => ['emoji' => '📚', 'label' => 'Referencia']
];
$tipo_key = isset($manual['tipo_manual']) ? strtolower((string)$manual['tipo_manual']) : '';
$tipo_emoji = '📘';
$tipo_label = 'Manual';
if ($tipo_key && isset($tipo_map[$tipo_key])) { $tipo_emoji = $tipo_map[$tipo_key]['emoji']; $tipo_label = $tipo_map[$tipo_key]['label']; }
elseif (strpos(strtolower($manual['slug']), 'arm') !== false) { $tipo_emoji = '🛠️'; $tipo_label = 'Armado'; }

$ambito = isset($manual['ambito']) && $manual['ambito'] === 'componente' ? 'componente' : 'kit';
$comp = null;
if ($ambito === 'componente' && !empty($manual['item_id'])) {
  try {
    $stmtC = $pdo->prepare('SELECT id, nombre_comun, slug, sku, imagen_portada FROM kit_items WHERE id = ? LIMIT 1');
    $stmtC->execute([(int)$manual['item_id']]);
    $comp = $stmtC->fetch(PDO::FETCH_ASSOC) ?: null;
  } catch (Exception $e) { $comp = null; }
}

// Canonical: usar solo el slug del manual (ya incluye entidad y fecha)
$canonical_url = SITE_URL . '/' . urlencode($manual['slug']);

include 'includes/header.php';
?>
<div class="container">
  <div class="breadcrumb">
    <strong>Manual:</strong> <?= h($tipo_label) ?><?= !empty($manual['version']) ? ' ' . h($manual['version']) : '' ?> <?= h($entity_name) ?>
  </div>

  <header class="manual-header">
    <div class="manual-head">
      <div class="manual-emoji" aria-hidden="true"><?= $tipo_emoji ?></div>
      <div class="manual-title-wrap">
        <h1>Manual: <?= h($tipo_label) ?><?= !empty($manual['version']) ? ' ' . h($manual['version']) : '' ?> <?= h($entity_name) ?></h1>
        <div class="manual-meta">
          <span class="badge"><?= h($tipo_label) ?></span>
          <span class="badge">Versión <?= h($manual['version']) ?></span>
          <?php if (!empty($manual['idioma'])): ?><span class="badge">🌐 <?= h($manual['idioma']) ?></span><?php endif; ?>
          <?php 
            $kit_time = isset($kit['time_minutes']) ? (int)$kit['time_minutes'] : null; 
            $kit_diff = isset($kit['dificultad_ensamble']) ? (string)$kit['dificultad_ensamble'] : null; 
            $eff_time = !empty($manual['time_minutes']) ? (int)$manual['time_minutes'] : $kit_time; 
            $eff_diff = !empty($manual['dificultad_ensamble']) ? (string)$manual['dificultad_ensamble'] : $kit_diff; 
          ?>
          <?php if (!empty($eff_time)): ?><span class="badge">⏱️ <?= (int)$eff_time ?> min</span><?php endif; ?>
          <?php if (!empty($eff_diff)): ?><span class="badge">📊 <?= h($eff_diff) ?></span><?php endif; ?>
          <?php if (!empty($manual['published_at'])): ?><span class="badge">🗓️ Publicado <?= h(date('d/m/Y', strtotime($manual['published_at']))) ?></span><?php endif; ?>
          <?php if (!empty($manual['updated_at'])): ?><span class="badge">🔄 Actualizado <?= h(date('d/m/Y', strtotime($manual['updated_at']))) ?></span><?php endif; ?>
          <?php if ($ambito === 'componente'): ?>
            <?php if ($comp && !empty($comp['slug'])): ?>
              <span class="badge">🔧 Para componente: <a href="/<?= h($comp['slug']) ?>" title="Ver componente <?= h($comp['nombre_comun']) ?>"><?= h($comp['nombre_comun']) ?></a></span>
            <?php else: ?>
              <span class="badge">🔧 Ámbito: Componente</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge">📦 Ámbito: Kit</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php if (!empty($manual['resumen'])): ?>
      <p class="manual-resumen"><?= h($manual['resumen']) ?></p>
    <?php endif; ?>
  </header>

  <article class="manual-content">
    <?php
      $mode = isset($manual['render_mode']) ? $manual['render_mode'] : ((!empty($manual['html'])) ? 'fullhtml' : 'legacy');
    ?>
    <?php if ($mode === 'fullhtml' && !empty($manual['html'])): ?>
      <?= $manual['html'] ?>
    <?php else: ?>
      <?php
        $pasos = [];
        $herr = [];
        $seg = [];
        // Kit safety
        $kitSeg = null;
        if (!empty($kit['seguridad'])) {
            try { $tmpKit = json_decode($kit['seguridad'], true); if (is_array($tmpKit)) { $kitSeg = $tmpKit; } } catch(Exception $e) {}
        }
        if (!empty($manual['pasos_json'])) {
            $tmp = json_decode($manual['pasos_json'], true);
            if (is_array($tmp)) { $pasos = $tmp; }
        }
        if (!empty($manual['herramientas_json'])) {
            $tmp = json_decode($manual['herramientas_json'], true);
            if (is_array($tmp)) { $herr = $tmp; }
        }
        $manualSegRaw = null;
        if (!empty($manual['seguridad_json'])) {
            $tmp = json_decode($manual['seguridad_json'], true);
            if (is_array($tmp)) { $seg = $tmp; $manualSegRaw = $tmp; }
        }
      ?>
      <?php if ($ambito === 'componente' && $comp && !empty($comp['advertencias_seguridad'])): ?>
        <section class="component-warnings">
          <h2>⚠️ Advertencias del Componente</h2>
          <div class="component-warning-text"><?= nl2br(h($comp['advertencias_seguridad'])) ?></div>
        </section>
      <?php endif; ?>
      <?php
        // Compute effective safety by merging manual directives with kit safety (age + free-text notes)
        $hasManualSafety = !empty($manualSegRaw);
        $useKitSafety = false;
        $effectiveAge = ['min' => null, 'max' => null];
        $manualNotes = [];
        $kitNotesText = '';
        if ($hasManualSafety) {
            $isAssoc = is_array($manualSegRaw) && array_keys($manualSegRaw) !== range(0, count($manualSegRaw)-1);
            if ($isAssoc) {
                if (isset($manualSegRaw['usar_seguridad_kit'])) { $useKitSafety = !!$manualSegRaw['usar_seguridad_kit']; }
                if (!empty($manualSegRaw['edad']) && is_array($manualSegRaw['edad'])) {
                    $effectiveAge['min'] = isset($manualSegRaw['edad']['min']) ? (int)$manualSegRaw['edad']['min'] : null;
                    $effectiveAge['max'] = isset($manualSegRaw['edad']['max']) ? (int)$manualSegRaw['edad']['max'] : null;
                }
                if (!empty($manualSegRaw['notas_extra']) && is_array($manualSegRaw['notas_extra'])) { $manualNotes = $manualSegRaw['notas_extra']; }
                elseif (!empty($manualSegRaw['notas']) && is_array($manualSegRaw['notas'])) { $manualNotes = $manualSegRaw['notas']; }
            } else {
                // Old shapes: array of notes or [{edad,notas}]
                if (is_array($manualSegRaw) && isset($manualSegRaw[0]) && is_array($manualSegRaw[0]) && (isset($manualSegRaw[0]['edad']) || isset($manualSegRaw[0]['notas']))) {
                    $obj = $manualSegRaw[0];
                    if (!empty($obj['edad'])) {
                        $effectiveAge['min'] = isset($obj['edad']['min']) ? (int)$obj['edad']['min'] : null;
                        $effectiveAge['max'] = isset($obj['edad']['max']) ? (int)$obj['edad']['max'] : null;
                    }
                    if (!empty($obj['notas']) && is_array($obj['notas'])) { $manualNotes = $obj['notas']; }
                } else {
                    $manualNotes = $manualSegRaw;
                }
            }
        }
        // If manual age not set, use kit age if available
        if (($effectiveAge['min'] === null || $effectiveAge['max'] === null) && $kitSeg) {
            if ($effectiveAge['min'] === null && !empty($kitSeg['edad_min'])) $effectiveAge['min'] = (int)$kitSeg['edad_min'];
            if ($effectiveAge['max'] === null && !empty($kitSeg['edad_max'])) $effectiveAge['max'] = (int)$kitSeg['edad_max'];
        }
        // Kit notes are free text; include if directive says so
        if ($useKitSafety && $kitSeg && !empty($kitSeg['notas'])) { $kitNotesText = (string)$kitSeg['notas']; }
        $hasAnySafety = $useKitSafety || !empty($manualNotes) || ($effectiveAge['min'] !== null || $effectiveAge['max'] !== null);
      ?>
      <?php
        $toc_items = [];
        if (!empty($pasos)) {
          foreach ($pasos as $idx => $p) {
            $titulo = '';
            if (is_array($p)) {
              $titulo = !empty($p['titulo']) ? (string)$p['titulo'] : ('Paso ' . ($idx + 1));
            } else {
              $titulo = 'Paso ' . ($idx + 1);
            }
            $toc_items[] = [ 'id' => 'paso-' . ($idx + 1), 'titulo' => $titulo ];
          }
        }
      ?>
      <?php if (!empty($toc_items)): ?>
        <div class="manual-toc-row">
          <aside class="manual-toc-aside">
            <?php $img_id = ($ambito === 'componente' && !empty($manual['item_id'])) ? ('comp-' . (int)$manual['item_id']) : ('kit-' . (int)$kit['id']); ?>
            <?php if (!empty($kit['imagen_portada'])): ?>
              <img id="<?= h($img_id) ?>"
                   src="<?= h($kit['imagen_portada']) ?>"
                   alt="Imagen del kit <?= h($kit['nombre']) ?>"
                   class="manual-toc-image"
                   onerror="this.onerror=null; console.log('❌ [Manual] Imagen portada kit falló'); var p=document.createElement('div'); p.id='<?= h($img_id) ?>'; p.className='manual-toc-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='📦'; p.appendChild(s); this.replaceWith(p);" />
              <div class="manual-toc-caption"><?= h($kit['nombre']) ?></div>
              <script>console.log('✅ [Manual] Imagen portada mostrada junto al índice');</script>
            <?php else: ?>
              <div id="<?= h($img_id) ?>" class="manual-toc-placeholder" title="Sin imagen de kit"><span class="placeholder-icon">📦</span></div>
              <div class="manual-toc-caption"><?= h($kit['nombre']) ?></div>
              <script>console.log('⚠️ [Manual] Kit sin imagen, usando placeholder en índice');</script>
            <?php endif; ?>
          </aside>
          <nav class="manual-toc" aria-label="Índice de pasos">
            <h2>🧭 Índice</h2>
            <ol>
              <?php foreach ($toc_items as $ti): ?>
                <li><a href="#<?= h($ti['id']) ?>"><?= h($ti['titulo']) ?></a></li>
              <?php endforeach; ?>
            </ol>
          </nav>
        </div>
      <?php endif; ?>
      <?php if ($hasAnySafety): ?>
        <section class="safety-info">
          <h2>⚠️ Seguridad</h2>
          <?php if ($effectiveAge['min'] !== null || $effectiveAge['max'] !== null): ?>
            <div class="kit-security-chip">Edad segura: <?= ($effectiveAge['min'] !== null ? (int)$effectiveAge['min'] : '?') ?>–<?= ($effectiveAge['max'] !== null ? (int)$effectiveAge['max'] : '?') ?> años</div>
          <?php endif; ?>
          <?php if (!empty($kitNotesText)): ?>
            <div class="kit-safety-notes-public"><?= nl2br(h($kitNotesText)) ?></div>
          <?php endif; ?>
          <?php if (!empty($manualNotes)): ?>
            <ul class="security-list">
              <?php foreach ($manualNotes as $nota): ?>
                <?php
                  $cat = is_array($nota) ? ($nota['categoria'] ?? '') : '';
                  $catLower = mb_strtolower($cat);
                  $icon = '⚠️';
                  switch ($catLower) {
                    case 'protección personal': $icon = '🥽'; break;
                    case 'corte': $icon = '✂️'; break;
                    case 'químico': $icon = '⚗️'; break;
                    case 'eléctrico': $icon = '⚡'; break;
                    case 'calor/fuego': $icon = '🔥'; break;
                    case 'biológico': $icon = '🧪'; break;
                    case 'presión/golpe': $icon = '💥'; break;
                    case 'entorno/ventilación': $icon = '🌬️'; break;
                    case 'supervisión adulta': $icon = '👨‍🏫'; break;
                    case 'residuos/reciclaje': $icon = '♻️'; break;
                  }
                ?>
                <li>
                  <span class="sec-note"><?= h(is_array($nota) ? ($nota['nota'] ?? '') : $nota) ?></span>
                  <?php if (!empty($cat)): ?>
                    <span class="sec-cat"><span class="emoji"><?= $icon ?></span> <?= h($cat) ?></span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if (!empty($herr)): ?>
        <section>
          <h2>🔧 Herramientas</h2>
          <ul class="tools-list">
            <?php foreach ($herr as $hitem): ?>
              <?php if (is_array($hitem) && (isset($hitem['nombre']) || isset($hitem['cantidad']) || isset($hitem['nota']) || isset($hitem['seguridad']))): ?>
                <li>
                  <div class="tool-line">
                    <strong><?= h($hitem['nombre'] ?? '(sin nombre)') ?></strong>
                    <?php if (isset($hitem['cantidad']) && $hitem['cantidad'] !== '' && $hitem['cantidad'] !== null): ?>
                      <span class="muted">(<?= h(is_numeric($hitem['cantidad']) ? (int)$hitem['cantidad'] : $hitem['cantidad']) ?>)</span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($hitem['nota'])): ?><div class="tool-note">Nota: <?= h($hitem['nota']) ?></div><?php endif; ?>
                  <?php if (!empty($hitem['seguridad'])): ?><div class="tool-sec">⚠️ Seguridad: <?= h($hitem['seguridad']) ?></div><?php endif; ?>
                </li>
              <?php else: ?>
                <li><?= h(is_array($hitem) ? json_encode($hitem, JSON_UNESCAPED_UNICODE) : $hitem) ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($pasos)): ?>
        <section>
          <h2>📋 Pasos</h2>
          <ol class="manual-steps">
            <?php foreach ($pasos as $idx => $p): ?>
              <li class="manual-step" id="paso-<?= (int)($idx + 1) ?>">
                <div class="step-head"><strong><?= h($p['titulo'] ?? ('Paso ' . ($idx + 1))) ?></strong></div>
                <div class="step-body">
                  <?php if (!empty($p['html'])): ?>
                    <?= $p['html'] ?>
                  <?php elseif (!empty($p['descripcion'])): ?>
                    <p><?= h($p['descripcion']) ?></p>
                  <?php elseif (!empty($p['texto'])): ?>
                    <p><?= h($p['texto']) ?></p>
                  <?php else: ?>
                    <p class="muted">(Sin contenido)</p>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </section>
      <?php else: ?>
        <p class="muted">Este manual aún no tiene contenido HTML ni pasos definidos.</p>
      <?php endif; ?>
    <?php endif; ?>
  </article>
</div>
<script>
console.log('🔍 [Manual] Kit:', <?= json_encode(['id'=>$kit['id'],'slug'=>$kit['slug'],'nombre'=>$kit['nombre']]) ?>);
console.log('🔍 [Manual] Slug manual:', '<?= h($manual_slug) ?>');
console.log('✅ [Manual] Cargado:', <?= json_encode(['id'=>$manual['id'],'version'=>$manual['version'],'idioma'=>$manual['idioma'],'status'=>$manual['status']]) ?>);
console.log('🔍 [Manual] Pasos:', <?= (isset($pasos) && is_array($pasos)) ? count($pasos) : 0 ?>);
</script>
<style>
/* Print-friendly tweaks for manual steps */
.manual-steps { padding-left: 18px; }
.manual-step { margin-bottom: 12px; }
.manual-step .step-head { margin-bottom: 6px; }
.kit-security-chip { display:inline-block; background:#fffbe6; border:1px solid #ffe58f; color:#8c6d1f; padding:4px 8px; border-radius:6px; margin:4px 0; }
/* Tools list styles */
.tools-list { padding-left: 18px; }
.tool-line { display:flex; gap:6px; align-items:baseline; }
.tool-note, .tool-sec { color:#555; margin-left: 4px; }
/* Security note list */
.security-list { padding-left: 18px; }
.sec-note { color:#333; }
.manual-head { display:flex; gap:12px; align-items:center; }
.manual-emoji { font-size:60px; line-height:1; filter: drop-shadow(0 1px 0 rgba(0,0,0,0.06)); }
.manual-title-wrap h1 { margin: 0 0 4px; }
.manual-meta { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
.manual-resumen { font-size:1.05rem; color:#444; margin-top:8px; }
.manual-toc { background:#f7f9fc; border:1px solid #e3e8f3; border-radius:8px; padding:10px 12px; }
.component-warnings { background:#fff7f7; border:1px solid #ffd6d6; color:#7a2d2d; border-radius:8px; padding:10px 12px; margin:12px 0; }
.component-warnings h2 { margin-bottom:6px; }
.component-warning-text { white-space:pre-wrap; }
@media print {
  .manual-step { page-break-inside: avoid; }
  .kit-security-chip { background:#fff; border-color:#aaa; color:#000; }
}
</style>
<?php include 'includes/footer.php'; ?>
