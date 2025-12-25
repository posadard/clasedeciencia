<?php
// Manual detail (public) for kits and components
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$kit_slug = isset($_GET['kit']) ? trim($_GET['kit']) : '';
$manual_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$comp_slug = isset($_GET['comp']) ? trim($_GET['comp']) : '';

// Resolver por slug del manual únicamente
if ($manual_slug === '') {
  header('HTTP/1.1 302 Found');
  header('Location: /');
  exit;
}

try {
  $stmtM = $pdo->prepare("SELECT * FROM kit_manuals WHERE slug = ? AND status IN ('published','discontinued') LIMIT 1");
  $stmtM->execute([$manual_slug]);
  $manual = $stmtM->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { $manual = false; }

if (!$manual) {
  header('HTTP/1.0 404 Not Found');
  $page_title = 'Manual no encontrado';
  $page_description = 'El manual solicitado no existe o no está publicado ni descontinuado.';
  include 'includes/header.php';
  echo '<div class="container"><div class="breadcrumb"><a href="/">Inicio</a> / <strong>Manual</strong></div><h1>Manual no encontrado</h1></div>';
  include 'includes/footer.php';
  exit;
}

// Derivar kit desde el manual o, si es ámbito componente y no hay kit_id, mediante relación de componente
$kit = null;
if (!empty($manual['kit_id'])) {
  try {
    $stmtK = $pdo->prepare('SELECT id, nombre, slug, codigo, version, resumen, contenido_html, imagen_portada, video_portada, time_minutes, dificultad_ensamble, seguridad, seo_title, seo_description, activo, updated_at FROM kits WHERE id = ? AND activo = 1 LIMIT 1');
    $stmtK->execute([(int)$manual['kit_id']]);
    $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { $kit = null; }
}
// Precisión de relación: no derivar kit desde item_id cuando el manual es de componente.
// Para ámbito 'kit', solo se usa kits.id = kit_manuals.kit_id; para ámbito 'componente', kit puede ser NULL.
if (!$kit) {
  // Si es ámbito componente y hay item_id, continuamos sin kit (manual anclado al componente)
  $is_component_scope = (isset($manual['ambito']) && $manual['ambito'] === 'componente' && !empty($manual['item_id']));
  if (!$is_component_scope) {
    // Sin kit activo asociado y no es ámbito componente: 404 amigable
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Kit no encontrado';
    $page_description = 'No se pudo determinar el kit asociado a este manual.';
    include 'includes/header.php';
    echo '<div class="container"><div class="breadcrumb"><a href="/">Inicio</a> / <strong>Manual</strong></div><h1>Kit no encontrado</h1></div>';
    include 'includes/footer.php';
    exit;
  }
}

// Delay display title build until ambito, tipo_label and comp are resolved
// (moved below after ambito & tipo calculation)

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
// Robust fallback: derive tipo from slug (manual-{tipo}-...) when DB field is missing
if ($tipo_key === '' && !empty($manual['slug'])) {
  $slug_low = strtolower((string)$manual['slug']);
  $parts = explode('-', $slug_low);
  if (!empty($parts) && $parts[0] === 'manual' && isset($parts[1]) && $parts[1] !== '') {
    $tipo_key = $parts[1];
  }
}
$tipo_emoji = '📘';
$tipo_label = 'Manual';
if ($tipo_key && isset($tipo_map[$tipo_key])) {
  $tipo_emoji = $tipo_map[$tipo_key]['emoji'];
  $tipo_label = $tipo_map[$tipo_key]['label'];
} elseif ($tipo_key !== '') {
  // Fallback: use slug-derived tipo as label, capitalized
  $tipo_label = ucfirst($tipo_key);
  if (strpos($tipo_key, 'arm') !== false) { $tipo_emoji = '🛠️'; }
}

$ambito = isset($manual['ambito']) && $manual['ambito'] === 'componente' ? 'componente' : 'kit';
$comp = null;
// Pre-parse component slug from manual slug for fallback display/linking
$entitySlugFromManual = null;
if (!empty($manual['slug'])) {
  $slug_low_pre = strtolower((string)$manual['slug']);
  $parts_pre = explode('-', $slug_low_pre);
  if (count($parts_pre) >= 4 && $parts_pre[0] === 'manual' && $parts_pre[2] === 'componente') {
    $entitySlugFromManual = $parts_pre[3];
  }
}
if ($ambito === 'componente' && !empty($manual['item_id'])) {
  try {
    $stmtC = $pdo->prepare('SELECT id, nombre_comun, slug, sku, imagen_portada, advertencias_seguridad FROM kit_items WHERE id = ? LIMIT 1');
    $stmtC->execute([(int)$manual['item_id']]);
    $comp = $stmtC->fetch(PDO::FETCH_ASSOC) ?: null;
  } catch (Exception $e) { $comp = null; }
}
// Fallback: derive component from slug if ambito=componente but item_id missing
if ($ambito === 'componente' && (empty($manual['item_id']) || (int)$manual['item_id'] <= 0) && !$comp && !empty($manual['slug'])) {
  $slug_low = strtolower((string)$manual['slug']);
  $parts = explode('-', $slug_low);
  // Expect: manual-{tipo}-componente-{entidad}-{fecha}-V{ver}
  if (count($parts) >= 4 && $parts[0] === 'manual' && $parts[2] === 'componente') {
    $entity_slug = $parts[3];
    try {
      $stmtC2 = $pdo->prepare('SELECT id, nombre_comun, slug, sku, imagen_portada, advertencias_seguridad FROM kit_items WHERE slug = ? LIMIT 1');
      $stmtC2->execute([$entity_slug]);
      $comp = $stmtC2->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) { /* ignore */ }
  }
}

// Build friendly display title now that tipo, ambito and entity are known
if ($ambito === 'componente') {
  if ($comp && !empty($comp['nombre_comun'])) {
    $entity_name_raw = (string)$comp['nombre_comun'];
  } else {
    // Prefer item_id authority: if item_id exists but no DB match, avoid slug fallback
    if (!empty($manual['item_id'])) {
      $entity_name_raw = 'Componente';
    } else {
      $slug_human = '';
      if (!empty($entitySlugFromManual)) {
        $slug_human = ucwords(str_replace('-', ' ', $entitySlugFromManual));
      }
      $entity_name_raw = $slug_human !== '' ? $slug_human : 'Componente';
    }
  }
} else {
  $entity_name_raw = (string)$kit['nombre'];
}
$version_text_raw = !empty($manual['version']) ? ('versión ' . (string)$manual['version']) : '';
// Estado al final si no es publicado
$status_key = strtolower((string)($manual['status'] ?? ''));
$status_labels = ['draft' => 'Borrador', 'approved' => 'Aprobado', 'published' => 'Publicado', 'discontinued' => 'Descontinuado'];
$status_label = $status_labels[$status_key] ?? '';
$state_text_raw = ($status_key !== '' && $status_key !== 'published') ? ('(' . $status_label . ')') : '';
// Formato: Manual de [Tipo] [Entidad] [versión] [(Estado no publicado)]
$display_title_raw = 'Manual de ' . $tipo_label . ' ' . $entity_name_raw
  . ($version_text_raw ? (' ' . $version_text_raw) : '')
  . ($state_text_raw ? (' ' . $state_text_raw) : '');
$page_title = $display_title_raw;
$page_description = 'Guía/Manual: ' . $display_title_raw;

// Canonical: usar solo el slug del manual (ya incluye entidad y fecha)
$canonical_url = SITE_URL . '/' . urlencode($manual['slug']);

include 'includes/header.php';
?>
<div class="container">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / 
    <?php if ($ambito === 'componente'): ?>
      <?php if ($comp && !empty($comp['slug'])): ?>
        <a href="/<?= h($comp['slug']) ?>"><?= h($comp['nombre_comun']) ?></a> / 
      <?php elseif (empty($manual['item_id']) && !empty($entitySlugFromManual)): ?>
        <a href="/<?= h($entitySlugFromManual) ?>"><?= h(ucwords(str_replace('-', ' ', $entitySlugFromManual))) ?></a> / 
      <?php endif; ?>
    <?php elseif (!empty($kit) && !empty($kit['slug'])): ?>
      <a href="/kit.php?slug=<?= urlencode($kit['slug']) ?>"><?= h($kit['nombre']) ?></a> / 
    <?php endif; ?>
    <strong><?= h($display_title_raw) ?></strong>
  </div>

  <header class="manual-header">
    <div class="manual-head">
      <div class="manual-emoji" aria-hidden="true"><?= $tipo_emoji ?></div>
      <div class="manual-title-wrap">
        <h1><?= h($display_title_raw) ?></h1>
        <div class="manual-meta">
          <span class="badge"><?= h($tipo_label) ?></span>
          <span class="badge">Versión <?= h($manual['version']) ?></span>
          <?php if (!empty($manual['idioma'])): ?><span class="badge">🌐 <?= h($manual['idioma']) ?></span><?php endif; ?>
          <?php if ($status_key === 'discontinued'): ?><span class="badge badge-danger">⚠️ Descontinuado</span><?php endif; ?>
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
      <?php if ($status_key === 'discontinued'): ?>
        <section class="safety-info">
          <h2>⚠️ Seguridad</h2>
          <div class="badge badge-danger">⚠️ Este manual ha sido descontinuado</div>
        </section>
      <?php endif; ?>
      <?= $manual['html'] ?>
    <?php else: ?>
      <?php
        $pasos = [];
        $herr = [];
        $seg = [];
        // Kit safety (solo aplica en ámbito kit)
        $kitSeg = null;
        if ($ambito === 'kit' && !empty($kit['seguridad'])) {
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
      <?php /* Advertencias del componente: ahora se integran dentro del bloque de seguridad */ ?>
      <?php
        // Compute effective safety by merging manual directives with kit safety (age + free-text notes)
        $hasManualSafety = !empty($manualSegRaw);
        $useCompSafety = false;
        $useKitSafety = false;
        $effectiveAge = ['min' => null, 'max' => null];
        $manualNotes = [];
        $kitNotesText = '';
        if ($hasManualSafety) {
            $isAssoc = is_array($manualSegRaw) && array_keys($manualSegRaw) !== range(0, count($manualSegRaw)-1);
            if ($isAssoc) {
                if (isset($manualSegRaw['usar_seguridad_kit'])) { $useKitSafety = !!$manualSegRaw['usar_seguridad_kit']; }
              if (isset($manualSegRaw['usar_seguridad_componente'])) { $useCompSafety = !!$manualSegRaw['usar_seguridad_componente']; }
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
        // If manual age not set, use kit age if available (solo ámbito kit)
        if ($ambito === 'kit' && ($effectiveAge['min'] === null || $effectiveAge['max'] === null) && $kitSeg) {
          if ($effectiveAge['min'] === null && !empty($kitSeg['edad_min'])) $effectiveAge['min'] = (int)$kitSeg['edad_min'];
          if ($effectiveAge['max'] === null && !empty($kitSeg['edad_max'])) $effectiveAge['max'] = (int)$kitSeg['edad_max'];
        }
        // If manual age not set and component flag is on, use component age when available
        if ($ambito === 'componente' && ($effectiveAge['min'] === null || $effectiveAge['max'] === null) && $useCompSafety && $comp && !empty($comp['advertencias_seguridad'])) {
          try {
            $cw = json_decode((string)$comp['advertencias_seguridad'], true);
            if (is_array($cw) && array_keys($cw) !== range(0, count($cw)-1)) {
              if ($effectiveAge['min'] === null && isset($cw['edad_min']) && $cw['edad_min'] !== '') $effectiveAge['min'] = (int)$cw['edad_min'];
              if ($effectiveAge['max'] === null && isset($cw['edad_max']) && $cw['edad_max'] !== '') $effectiveAge['max'] = (int)$cw['edad_max'];
            }
          } catch(Exception $e) {}
        }
        // Kit notes are free text; include if directive says so (solo ámbito kit)
        if ($ambito === 'kit' && $useKitSafety && $kitSeg && !empty($kitSeg['notas'])) { $kitNotesText = (string)$kitSeg['notas']; }
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
            // Derivar texto del paso (priorizar contenido) y truncar a 10 palabras
            $raw = '';
            if (is_array($p)) {
              if (!empty($p['html'])) { $raw = strip_tags((string)$p['html']); }
              elseif (!empty($p['descripcion'])) { $raw = (string)$p['descripcion']; }
              elseif (!empty($p['texto'])) { $raw = (string)$p['texto']; }
            }
            if ($raw === '') { $raw = $titulo; }
            $words = preg_split('/\s+/u', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
            $preview = '';
            if ($words && count($words) > 0) {
              $slice = array_slice($words, 0, 10);
              $preview = implode(' ', $slice);
              if (count($words) > 10) { $preview .= '…'; }
            } else {
              $preview = $titulo;
            }
            $toc_items[] = [ 'id' => 'paso-' . ($idx + 1), 'title' => $titulo, 'preview' => $preview ];
          }
        }
      ?>
      <?php if (!empty($toc_items) || $hasAnySafety || $status_key === 'discontinued' || ($ambito === 'componente' && $comp && !empty($comp['advertencias_seguridad']))): ?>
        <div class="manual-toc-row" style="display:flex; align-items:flex-start; gap:12px;">
          <aside class="manual-toc-aside">
            <?php if ($ambito === 'componente'): ?>
              <?php if ($comp): ?>
              <?php $img_id = 'comp-' . (int)$manual['item_id']; ?>
              <?php if (!empty($comp['imagen_portada'])): ?>
                <img id="<?= h($img_id) ?>"
                     src="<?= h($comp['imagen_portada']) ?>"
                     alt="Imagen del componente <?= h($comp['nombre_comun']) ?>"
                     class="manual-toc-image"
                     onerror="this.onerror=null; console.log('❌ [Manual] Imagen portada componente falló'); var p=document.createElement('div'); p.id='<?= h($img_id) ?>'; p.className='manual-toc-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔧'; p.appendChild(s); this.replaceWith(p);" />
                <div class="manual-toc-caption"><?= h($comp['nombre_comun']) ?></div>
                <script>console.log('✅ [Manual] Imagen portada componente mostrada junto al índice');</script>
              <?php else: ?>
                <div id="<?= h($img_id) ?>" class="manual-toc-placeholder" title="Sin imagen de componente"><span class="placeholder-icon">🔧</span></div>
                <div class="manual-toc-caption"><?= h($comp['nombre_comun']) ?></div>
                <script>console.log('⚠️ [Manual] Componente sin imagen, usando placeholder en índice');</script>
              <?php endif; ?>
              <?php else: ?>
                <?php $img_id = 'comp-unknown'; ?>
                  <div id="<?= h($img_id) ?>" class="manual-toc-placeholder" title="Componente"><span class="placeholder-icon">🔧</span></div>
                  <div class="manual-toc-caption"><?php
                    if (!empty($manual['item_id'])) {
                      echo h('Componente');
                    } else {
                      echo h(!empty($entitySlugFromManual) ? ucwords(str_replace('-', ' ', $entitySlugFromManual)) : 'Componente');
                    }
                  ?></div>
                  <script>console.log('ℹ️ [Manual] Índice componente sin DB; item_id:', <?= json_encode(!empty($manual['item_id'])) ?>);</script>
              <?php endif; ?>
            <?php else: ?>
              <?php $img_id = 'kit-' . (int)$kit['id']; ?>
              <?php if (!empty($kit['imagen_portada'])): ?>
                <img id="<?= h($img_id) ?>"
                     src="<?= h($kit['imagen_portada']) ?>"
                     alt="Imagen del kit <?= h($kit['nombre']) ?>"
                     class="manual-toc-image"
                     onerror="this.onerror=null; console.log('❌ [Manual] Imagen portada kit falló'); var p=document.createElement('div'); p.id='<?= h($img_id) ?>'; p.className='manual-toc-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='📦'; p.appendChild(s); this.replaceWith(p);" />
                <div class="manual-toc-caption"><?= h($kit['nombre']) ?></div>
                <script>console.log('✅ [Manual] Imagen portada kit mostrada junto al índice');</script>
              <?php else: ?>
                <div id="<?= h($img_id) ?>" class="manual-toc-placeholder" title="Sin imagen de kit"><span class="placeholder-icon">📦</span></div>
                <div class="manual-toc-caption"><?= h($kit['nombre']) ?></div>
                <script>console.log('⚠️ [Manual] Kit sin imagen, usando placeholder en índice');</script>
              <?php endif; ?>
            <?php endif; ?>
          </aside>
          <div class="manual-toc-right" style="flex:1; min-width:0;">
            <?php if ($hasAnySafety || $status_key === 'discontinued' || ($ambito === 'componente' && $comp && !empty($comp['advertencias_seguridad']))): ?>
              <section class="safety-info">
                <h2><?= ($ambito === 'componente' ? '⚠️ Seguridad del Componente' : '⚠️ Seguridad') ?></h2>
                <?php if ($status_key === 'discontinued'): ?>
                  <div class="badge badge-danger" style="margin:4px 0;">⚠️ Este manual ha sido descontinuado</div>
                <?php endif; ?>
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
                <?php if ($ambito === 'componente' && $comp && !empty($comp['advertencias_seguridad'])): ?>
                  <?php
                    $compWarnRaw = (string)$comp['advertencias_seguridad'];
                    $compWarnObj = null;
                    $compWarnIsJson = false;
                    if ($compWarnRaw !== '' && ($compWarnRaw[0] === '{' || $compWarnRaw[0] === '[')) {
                      try { $tmpCW = json_decode($compWarnRaw, true); if (is_array($tmpCW)) { $compWarnObj = $tmpCW; $compWarnIsJson = true; } } catch(Exception $e) { $compWarnObj = null; $compWarnIsJson = false; }
                    }
                    // Derive component age into effectiveAge if manual age missing
                    if ($compWarnIsJson && is_array($compWarnObj) && array_keys($compWarnObj) !== range(0, count($compWarnObj)-1)) {
                      if (($effectiveAge['min'] === null || $effectiveAge['max'] === null)) {
                        if (isset($compWarnObj['edad_min']) && $compWarnObj['edad_min'] !== '') { $effectiveAge['min'] = (int)$compWarnObj['edad_min']; }
                        if (isset($compWarnObj['edad_max']) && $compWarnObj['edad_max'] !== '') { $effectiveAge['max'] = (int)$compWarnObj['edad_max']; }
                      }
                    }
                  ?>
                  <div class="component-warning-inline">
                    <?php if ($compWarnIsJson && is_array($compWarnObj) && array_keys($compWarnObj) !== range(0, count($compWarnObj)-1)): ?>
                      <?php if (!empty($compWarnObj['notas']) && is_array($compWarnObj['notas'])): ?>
                        <ul class="security-list">
                          <?php foreach ($compWarnObj['notas'] as $nota): ?>
                            <?php $cat = is_array($nota) ? ($nota['categoria'] ?? '') : ''; $icon = '⚠️'; ?>
                            <li>
                              <span class="sec-note"><?= h(is_array($nota) ? ($nota['nota'] ?? '') : $nota) ?></span>
                              <?php if (!empty($cat)): ?><span class="sec-cat"><span class="emoji"><?= $icon ?></span> <?= h($cat) ?></span><?php endif; ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php elseif (!empty($compWarnObj['notas']) && is_string($compWarnObj['notas'])): ?>
                        <div class="component-warning-text"><?= nl2br(h($compWarnObj['notas'])) ?></div>
                      <?php else: ?>
                        <div class="component-warning-text"><span class="muted">(El componente no tiene notas de seguridad textuales)</span></div>
                      <?php endif; ?>
                    <?php else: ?>
                      <div class="component-warning-text"><?= nl2br(h($compWarnRaw)) ?></div>
                    <?php endif; ?>
                  </div>
                  <script>console.log('🔧 [Manual] Advertencias del componente integradas (JSON:', <?= json_encode($compWarnIsJson) ?>, ')');</script>
                <?php endif; ?>
                <?php
                  // Concatenar notas de seguridad de componentes y herramientas (10 palabras) con el nombre
                  $wp = function($text, $limit = 10){
                    $t = trim(strip_tags((string)$text));
                    if ($t === '') return '';
                    $parts = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
                    if (!$parts || !count($parts)) return '';
                    if (count($parts) <= (int)$limit) return implode(' ', $parts);
                    return implode(' ', array_slice($parts, 0, (int)$limit)) . '…';
                  };
                  $components_line = '';
                  $tools_line = '';
                  try {
                    $comp_list = ($ambito === 'componente' || empty($kit) || empty($kit['id'])) ? [] : cdc_get_kit_componentes($pdo, (int)$kit['id']);
                  } catch (Exception $e) { $comp_list = []; }
                  $comp_pairs = [];
                  if (!empty($comp_list)) {
                    foreach ($comp_list as $cm) {
                      if (!empty($cm['advertencias_seguridad'])) {
                        $name = (string)($cm['nombre_comun'] ?? '');
                        $prev = $wp($cm['advertencias_seguridad'], 10);
                        if ($name !== '' && $prev !== '') { $comp_pairs[] = $name . ': ' . $prev; }
                      }
                    }
                  }
                  if (!empty($comp_pairs)) { $components_line = implode(' - ', $comp_pairs); }
                  $tool_pairs = [];
                  if (!empty($herr)) {
                    foreach ($herr as $ht) {
                      if (is_array($ht) && !empty($ht['seguridad'])) {
                        $tname = (string)($ht['nombre'] ?? '');
                        $tprev = $wp($ht['seguridad'], 10);
                        if ($tname !== '' && $tprev !== '') { $tool_pairs[] = $tname . ': ' . $tprev; }
                      }
                    }
                  }
                  if (!empty($tool_pairs)) { $tools_line = implode(' - ', $tool_pairs); }
                ?>
                <?php if ($ambito === 'kit' && ($components_line !== '' || $tools_line !== '')): ?>
                  <div class="safety-concat" aria-label="Notas adicionales de seguridad">
                    <?php if ($components_line !== ''): ?><div class="safety-concat-line"><?= h($components_line) ?></div><?php endif; ?>
                    <?php if ($tools_line !== ''): ?><div class="safety-concat-line"><?= h($tools_line) ?></div><?php endif; ?>
                  </div>
                  <script>console.log('🧪 [Manual] Notas seguridad concat (kit):', { componentes: <?= json_encode($comp_pairs) ?>, herramientas: <?= json_encode($tool_pairs) ?> });</script>
                <?php endif; ?>
              </section>
              <script>console.log('🔍 [Manual] Bloque de seguridad mostrado (gating):', { hasAnySafety: <?= json_encode($hasAnySafety) ?>, discontinued: <?= json_encode($status_key === 'discontinued') ?>, ambito: '<?= h($ambito) ?>', compWarn: <?= json_encode(($ambito === 'componente' && $comp && !empty($comp['advertencias_seguridad']))) ?> });</script>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($toc_items)): ?>
        <nav class="manual-toc" aria-label="Índice de pasos" style="margin-top:10px;">
          <h2>🧭 Índice</h2>
          <ol>
            <?php foreach ($toc_items as $ti): ?>
              <li><a href="#<?= h($ti['id']) ?>"><strong><?= h($ti['title']) ?></strong>: <?= h($ti['preview']) ?></a></li>
            <?php endforeach; ?>
          </ol>
        </nav>
        <script>console.log('🔁 [Manual] Índice fuera del bloque de imagen/seguridad, títulos en negrilla');</script>
      <?php endif; ?>
      <?php
        // Bloque de componentes del kit: mostrar después del índice y antes de herramientas
        try {
          $man_components = ($ambito === 'componente' || empty($kit) || empty($kit['id'])) ? [] : cdc_get_kit_componentes($pdo, (int)$kit['id']);
        } catch (Exception $e) { $man_components = []; }
      ?>
      <?php if (!empty($man_components)): ?>
        <section class="kits-section">
          <div class="kit-card">
            <h4>Componentes necesarios</h4>
            <ul class="materials-list">
              <?php foreach ($man_components as $m): ?>
                <?php
                  $item_slug = !empty($m['slug']) ? '/' . $m['slug'] : '';
                  $li_attrs = !empty($item_slug) ? ' data-href="' . h($item_slug) . '" tabindex="0"' : '';
                ?>
                <li<?= $li_attrs ?> class="material-item">
                  <span class="material-name"><?= h($m['nombre_comun']) ?></span>
                  <?php if (!empty($m['advertencias_seguridad'])): ?>
                    <small class="material-warning">⚠️ <?= h($m['advertencias_seguridad']) ?></small>
                  <?php endif; ?>
                  <?php if (!empty($m['cantidad'])): ?>
                    <span class="badge"><?= h($m['cantidad']) ?> <?= h($m['unidad'] ?? '') ?></span>
                  <?php endif; ?>
                  <?php if (isset($m['es_incluido_kit'])): ?>
                    <?php if ((int)$m['es_incluido_kit'] === 1): ?>
                      <span class="badge badge-success">✓ Incluido</span>
                    <?php else: ?>
                      <span class="badge badge-danger">No incluido</span>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (!empty($m['notas'])): ?>
                    <small class="material-notes"><?= h($m['notas']) ?></small>
                  <?php endif; ?>
                  <?php if (!empty($m['slug'])): ?>
                    <span class="icon-decor" aria-hidden="true" style="margin-left:6px">🔎</span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
            <script>
              // 🔍 [Manual] Activar click en toda la tarjeta del material (igual que kit.php)
              (function() {
                try {
                  var items = document.querySelectorAll('.kits-section .materials-list li[data-href]');
                  console.log('🔍 [Manual] Componentes clicables:', items.length);
                  items.forEach(function(li) {
                    var href = li.getAttribute('data-href');
                    if (!href) return;
                    li.addEventListener('click', function(ev) {
                      var target = ev.target;
                      if (target && target.closest('a')) { return; }
                      console.log('✅ [Manual] Click en componente →', href);
                      window.location.href = href;
                    });
                    li.addEventListener('keypress', function(ev) {
                      if (ev.key === 'Enter' || ev.key === ' ') {
                        console.log('✅ [Manual] Keypress en componente →', href);
                        window.location.href = href;
                        ev.preventDefault();
                      }
                    });
                  });
                } catch (e) {
                  console.log('❌ [Manual] Error activando componentes clicables:', e && e.message ? e.message : e);
                }
              })();
            </script>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($herr)): ?>
        <section class="kits-section manual-tools">
          <div class="kit-card">
            <h4>Herramientas (no incluido)</h4>
            <?php // Helper local para truncar a 10 palabras con '…'
            $words_preview = function($text, $limit = 10){
              $t = trim(strip_tags((string)$text));
              if ($t === '') return '';
              $parts = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
              if (!$parts) return '';
              if (count($parts) <= (int)$limit) return implode(' ', $parts);
              return implode(' ', array_slice($parts, 0, (int)$limit)) . '…';
            }; ?>
            <ul class="materials-list">
              <?php foreach ($herr as $hitem): ?>
                <?php if (is_array($hitem) && (isset($hitem['nombre']) || isset($hitem['cantidad']) || isset($hitem['nota']) || isset($hitem['seguridad']))): ?>
                  <?php
                    $tool_name = $hitem['nombre'] ?? '(sin nombre)';
                    $tool_qty = (isset($hitem['cantidad']) && $hitem['cantidad'] !== '' && $hitem['cantidad'] !== null)
                      ? (is_numeric($hitem['cantidad']) ? (string)(int)$hitem['cantidad'] : (string)$hitem['cantidad'])
                      : '';
                    $tool_note_full = isset($hitem['nota']) ? (string)$hitem['nota'] : '';
                    $tool_sec_full = isset($hitem['seguridad']) ? (string)$hitem['seguridad'] : '';
                    $tool_note_prev = $words_preview($tool_note_full, 10);
                    $tool_sec_prev = $words_preview($tool_sec_full, 10);
                  ?>
                  <li class="material-item"
                      data-name="<?= h($tool_name, ENT_QUOTES, 'UTF-8') ?>"
                      data-cantidad="<?= h($tool_qty, ENT_QUOTES, 'UTF-8') ?>"
                      data-notafull="<?= h($tool_note_full, ENT_QUOTES, 'UTF-8') ?>"
                      data-seguridadfull="<?= h($tool_sec_full, ENT_QUOTES, 'UTF-8') ?>"
                      tabindex="0">
                    <span class="material-name">
                      <?= h($tool_name) ?>
                      <?php if ($tool_qty !== ''): ?>
                        <span class="badge" style="margin-left:6px;"><?= h($tool_qty) ?></span>
                      <?php endif; ?>
                    </span>
                    <?php if ($tool_sec_prev !== ''): ?>
                      <small class="material-warning" style="margin-left:8px;">⚠️ <?= h($tool_sec_prev) ?></small>
                    <?php endif; ?>
                    <?php if ($tool_note_prev !== ''): ?>
                      <small class="material-notes"><?= h($tool_note_prev) ?></small>
                    <?php endif; ?>
                  </li>
                <?php else: ?>
                  <li class="material-item" tabindex="0"
                      data-name="<?= h(is_array($hitem) ? json_encode($hitem, JSON_UNESCAPED_UNICODE) : (string)$hitem, ENT_QUOTES, 'UTF-8') ?>"
                      data-cantidad=""
                      data-notafull=""
                      data-seguridadfull="">
                    <span class="material-name"><?= h(is_array($hitem) ? json_encode($hitem, JSON_UNESCAPED_UNICODE) : $hitem) ?></span>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
            <script>
              (function(){
                try {
                  var items = document.querySelectorAll('.manual-tools .materials-list li.material-item');
                  console.log('🔧 [Manual] Herramientas clicables:', items.length);
                  // Añadir icono de lupa a cada elemento de herramienta
                  items.forEach(function(li){
                    if (!li.querySelector('.card-magnify')) {
                      var magnify = document.createElement('span');
                      magnify.className = 'card-magnify';
                      magnify.textContent = '🔎';
                      magnify.setAttribute('aria-hidden', 'true');
                      li.appendChild(magnify);
                      console.log('🔍 [Manual] Lupa añadida a herramienta →', li.getAttribute('data-name'));
                    }
                  });
                  items.forEach(function(li){
                    function openModal(){
                      var name = li.getAttribute('data-name') || '';
                      var qty = li.getAttribute('data-cantidad') || '';
                      var noteFull = li.getAttribute('data-notafull') || '';
                      var secFull = li.getAttribute('data-seguridadfull') || '';
                      window.cdcToolModalOpen({ name: name, qty: qty, note: noteFull, sec: secFull });
                    }
                    li.addEventListener('click', function(ev){
                      var target = ev.target;
                      if (target && target.closest('a')) return;
                      console.log('✅ [Manual] Abrir modal herramienta →', li.getAttribute('data-name'));
                      openModal();
                    });
                    li.addEventListener('keypress', function(ev){
                      if (ev.key === 'Enter' || ev.key === ' '){
                        console.log('✅ [Manual] Abrir modal herramienta (tecla) →', li.getAttribute('data-name'));
                        openModal();
                        ev.preventDefault();
                      }
                    });
                  });
                } catch(e) {
                  console.log('❌ [Manual] Error preparando modal de herramientas:', e && e.message ? e.message : e);
                }
              })();
            </script>
          </div>
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
  <div class="article-byline">
    <span class="author">✍️ <?= h(!empty($manual['autor']) ? $manual['autor'] : 'Clase de Ciencia SAS') ?></span>
    <?php if (!empty($manual['published_at'])): ?>
      <span class="date">📅 Publicado: <?= h(date('d/m/Y', strtotime($manual['published_at']))) ?></span>
    <?php endif; ?>
    <?php if (!empty($manual['updated_at'])): ?>
      <span class="updated">🔄 Actualizado: <?= h(date('d/m/Y', strtotime($manual['updated_at']))) ?></span>
    <?php endif; ?>
    <span class="ficha">🧪 Tipo: <?= h($tipo_label) ?> · Ámbito: <?= h($ambito === 'componente' ? 'Componente' : 'Kit') ?><?php if (!empty($manual['idioma'])): ?> · Idioma: <?= h($manual['idioma']) ?><?php endif; ?><?php if (!empty($manual['version'])): ?> · v<?= h($manual['version']) ?><?php endif; ?></span>
  </div>
  <script>console.log('✅ [Manual] Byline renderizada');</script>
  <?php
    // Tarjeta del Kit o Componente y Clases relacionadas (al final)
    try {
      $clases = (!empty($kit) && !empty($kit['id'])) ? cdc_get_kit_clases($pdo, (int)$kit['id']) : [];
    } catch (Exception $e) { $clases = []; }
  ?>

  <?php if ($ambito === 'componente'): ?>
    <section class="manual-entity">
      <h2>🔧 Componente vinculado</h2>
      <?php if ($comp && !empty($comp['slug'])): ?>
        <a href="/<?= h($comp['slug']) ?>" class="related-card" title="Ver componente <?= h($comp['nombre_comun'] ?? 'Componente') ?>">
          <?php if (!empty($comp['imagen_portada'])): ?>
            <img src="<?= h($comp['imagen_portada']) ?>" alt="<?= h($comp['nombre_comun']) ?>" class="related-thumbnail" onerror="this.onerror=null; console.log('❌ [Manual] Miniatura componente falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔧'; p.appendChild(s); this.replaceWith(p);" />
          <?php else: ?>
            <div class="thumbnail-placeholder"><span class="placeholder-icon">🔧</span></div>
            <script>console.log('⚠️ [Manual] Componente sin imagen, usando placeholder');</script>
          <?php endif; ?>
          <div class="related-info">
            <h4><?= h($comp['nombre_comun'] ?? 'Componente') ?></h4>
            <div class="related-meta">
              <?php if (!empty($comp['sku'])): ?><span class="badge">SKU <?= h($comp['sku']) ?></span><?php endif; ?>
            </div>
          </div>
          <span class="card-magnify" aria-hidden="true">🔎</span>
        </a>
        <script>console.log('✅ [Manual] Tarjeta componente renderizada:', '<?= h($comp['slug']) ?>');</script>
      <?php elseif (!empty($entitySlugFromManual)): ?>
        <a href="/<?= h($entitySlugFromManual) ?>" class="related-card" title="Ver componente <?= h(ucwords(str_replace('-', ' ', $entitySlugFromManual))) ?>">
          <div class="thumbnail-placeholder"><span class="placeholder-icon">🔧</span></div>
          <div class="related-info">
            <h4><?= h(ucwords(str_replace('-', ' ', $entitySlugFromManual))) ?></h4>
          </div>
          <span class="card-magnify" aria-hidden="true">🔎</span>
        </a>
        <script>console.log('ℹ️ [Manual] Tarjeta componente por slug derivado:', '<?= h($entitySlugFromManual) ?>');</script>
      <?php else: ?>
        <div class="related-card" title="Componente">
          <div class="thumbnail-placeholder"><span class="placeholder-icon">🔧</span></div>
          <div class="related-info">
            <h4>Componente</h4>
          </div>
          <span class="card-magnify" aria-hidden="true">🔎</span>
        </div>
        <script>console.log('⚠️ [Manual] Componente vinculado sin datos disponibles');</script>
      <?php endif; ?>
    </section>
  <?php else: ?>
    <section class="manual-entity">
      <h2>📦 Kit vinculado</h2>
      <a href="/kit.php?slug=<?= urlencode($kit['slug']) ?>" class="related-card" title="Ver kit <?= h($kit['nombre']) ?>">
        <?php if (!empty($kit['imagen_portada'])): ?>
          <img src="<?= h($kit['imagen_portada']) ?>" alt="<?= h($kit['nombre']) ?>" class="related-thumbnail" onerror="this.onerror=null; console.log('❌ [Manual] Miniatura kit falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='📦'; p.appendChild(s); this.replaceWith(p);" />
        <?php else: ?>
          <div class="thumbnail-placeholder"><span class="placeholder-icon">📦</span></div>
          <script>console.log('⚠️ [Manual] Kit sin imagen, usando placeholder');</script>
        <?php endif; ?>
        <div class="related-info">
          <h4><?= h($kit['nombre']) ?></h4>
          <div class="related-meta">
            <?php if (!empty($kit['codigo'])): ?><span class="badge">Código <?= h($kit['codigo']) ?></span><?php endif; ?>
            <?php if (!empty($kit['version'])): ?><span class="badge">v<?= h($kit['version']) ?></span><?php endif; ?>
          </div>
          <?php if (!empty($kit['resumen'])): ?>
            <p class="related-excerpt"><?= h(mb_substr($kit['resumen'], 0, 100)) ?>...</p>
          <?php endif; ?>
        </div>
        <span class="card-magnify" aria-hidden="true">🔎</span>
      </a>
      <script>console.log('✅ [Manual] Tarjeta kit renderizada:', '<?= h($kit['slug']) ?>');</script>
    </section>
  <?php endif; ?>

  <?php if (!empty($clases)): ?>
    <section class="kit-classes">
      <h2>📚 Clases Relacionadas</h2>
      <div class="related-grid">
        <?php foreach ($clases as $c): ?>
          <a href="/<?= h($c['slug']) ?>" class="related-card">
            <?php if (!empty($c['imagen_portada'])): ?>
              <img src="<?= h($c['imagen_portada']) ?>" alt="<?= h($c['nombre']) ?>" class="related-thumbnail" onerror="this.onerror=null; console.log('❌ [Manual] Miniatura clase falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
            <?php else: ?>
              <div class="thumbnail-placeholder"><span class="placeholder-icon">🔬</span></div>
              <script>console.log('⚠️ [Manual] Clase sin imagen, usando placeholder');</script>
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
            <span class="card-magnify" aria-hidden="true">🔎</span>
          </a>
        <?php endforeach; ?>
      </div>
      <script>console.log('📚 [Manual] Clases vinculadas:', <?= count($clases) ?>);</script>
    </section>
  <?php endif; ?>
</div>
<script>
var KIT_INFO = <?= json_encode(!empty($kit) ? ['id'=>$kit['id'],'slug'=>$kit['slug'],'nombre'=>$kit['nombre']] : null) ?>;
var COMP_INFO = <?= json_encode(($ambito === 'componente' && $comp) ? ['id'=>$comp['id'],'slug'=>$comp['slug'],'nombre'=>$comp['nombre_comun']] : null) ?>;
if (KIT_INFO) console.log('🔍 [Manual] Kit:', KIT_INFO);
if (COMP_INFO) console.log('🔍 [Manual] Componente:', COMP_INFO);
console.log('🔍 [Manual] Slug manual:', '<?= h($manual_slug) ?>');
console.log('✅ [Manual] Cargado:', <?= json_encode(['id'=>$manual['id'],'version'=>$manual['version'],'idioma'=>$manual['idioma'],'status'=>$manual['status']]) ?>);
console.log('🔍 [Manual] Pasos:', <?= (isset($pasos) && is_array($pasos)) ? count($pasos) : 0 ?>);
</script>
<script>
// Añadir etiqueta "Descontinuado" a cada título h2 del manual cuando corresponda
(function(){
  try {
    var isDisc = <?= json_encode($status_key === 'discontinued') ?>;
    if (!isDisc) return;
    var headers = document.querySelectorAll('.manual-content h2');
    headers.forEach(function(h){
      var tag = document.createElement('span');
      tag.className = 'badge badge-danger';
      tag.style.marginLeft = '8px';
      tag.textContent = '⚠️ Descontinuado';
      h.appendChild(tag);
    });
    console.log('⚠️ [Manual] Etiquetas descontinuado añadidas a', headers.length, 'títulos');
  } catch (e) {
    console.log('❌ [Manual] Error añadiendo etiquetas descontinuado:', e && e.message ? e.message : e);
  }
})();
</script>
<style>
/* Manual steps - align rhythm with clase.php */
.manual-steps { padding-left: 22px; margin: var(--spacing-lg) 0 var(--spacing-md) 0; list-style: decimal; }
.manual-step { background:#fff; border:1px solid var(--color-border-light); border-radius: var(--border-radius-sm); padding: var(--spacing-sm) var(--spacing-md); margin-bottom: var(--spacing-sm); box-shadow: var(--shadow-sm); transition: box-shadow .15s ease, border-color .15s ease, transform .15s ease; }
.manual-step:hover { box-shadow: var(--shadow-md); border-color: var(--color-border); transform: translateY(-1px); }
.manual-step .step-head { margin-bottom: 6px; }
.manual-step .step-head strong { font-weight: 600; }
.manual-step .step-body p { margin: 0; color: var(--color-text); }
.kit-security-chip { display:inline-block; background:#fffbe6; border:1px solid #ffe58f; color:#8c6d1f; padding:4px 8px; border-radius:6px; margin:4px 0; }
/* Tools list styles */
.tools-list { padding-left: 18px; }
.tool-line { display:flex; gap:6px; align-items:baseline; }
.tool-note, .tool-sec { color:#555; margin-left: 4px; }
/* Security note list */
.security-list { padding-left: 18px; }
.sec-note { color:#333; }
.safety-concat { margin-top:6px; }
.safety-concat-line { font-size: 0.9rem; color:#555; }
.manual-head { display:flex; gap:12px; align-items:center; }
.manual-emoji { font-size:60px; line-height:1; filter: drop-shadow(0 1px 0 rgba(0,0,0,0.06)); }
.manual-title-wrap h1 { margin: 0 0 4px; }
.manual-meta { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
.manual-resumen { font-size:1.05rem; color:#444; margin-top:8px; }
.manual-toc { background:#f7f9fc; border:1px solid #e3e8f3; border-radius:8px; padding:10px 12px; }
.component-warnings { background:#fff7f7; border:1px solid #ffd6d6; color:#7a2d2d; border-radius:8px; padding:10px 12px; margin:12px 0; }
.component-warnings h2 { margin-bottom:6px; }
.component-warning-text { white-space:pre-wrap; }
.component-warning-inline { background:#fff7f7; border:1px solid #ffd6d6; color:#7a2d2d; border-radius:8px; padding:8px 10px; margin-top:8px; }
.manual-content { margin-bottom: var(--spacing-lg); }
/* Byline (similar a clase.php) */
.article-byline { display:flex; flex-wrap:wrap; gap:8px; align-items:center; color:#555; border-top:1px solid var(--color-border-light); padding-top: var(--spacing-sm); margin-top: var(--spacing-lg); margin-bottom: var(--spacing-md); }
.article-byline .author, .article-byline .date, .article-byline .updated, .article-byline .ficha { font-size:0.95rem; }
/* Entity and classes spacing */
.manual-entity { margin-top: var(--spacing-lg); background: var(--color-bg-alt); padding: var(--spacing-xl); border-radius: var(--border-radius); }
.kit-classes { margin-top: var(--spacing-lg); }
.manual-entity + .kit-classes { margin-top: var(--spacing-lg); }
/* Related cards nicer hover/shadow */
.related-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:12px; }
.related-card { display:block; border:1px solid var(--color-border-light); border-radius:8px; background:#fff; overflow:hidden; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; position:relative; }
.related-card:hover, .related-card:focus-within { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--color-accent); }
.related-thumbnail { width:100%; height:140px; object-fit:cover; display:block; }
.thumbnail-placeholder { height:140px; display:flex; align-items:center; justify-content:center; background:#f5f7fb; border-bottom:1px solid var(--color-border-light); }
.placeholder-icon { font-size:36px; }
.related-info { padding:10px 12px; }
.related-excerpt { color:#555; margin-top:4px; }
.related-meta .badge { margin-right:6px; }
@media print {
  .manual-step { page-break-inside: avoid; }
  .kit-security-chip { background:#fff; border-color:#aaa; color:#000; }
}
</style>
<div id="toolModal" class="cdc-modal" aria-hidden="true" style="display:none;">
  <div class="cdc-modal-backdrop"></div>
  <div class="cdc-modal-content" role="dialog" aria-modal="true" aria-labelledby="toolModalTitle">
    <button type="button" class="cdc-modal-close" aria-label="Cerrar">×</button>
    <h3 id="toolModalTitle">Herramienta</h3>
    <div class="cdc-modal-body">
      <p><strong>Cantidad:</strong> <span id="toolModalQty"></span></p>
      <div id="toolModalSafety"></div>
      <div id="toolModalNote"></div>
    </div>
  </div>
</div>
<script>
// Modal sencillo para mostrar información completa de herramientas
(function(){
  var modal = document.getElementById('toolModal');
  if (!modal) return;
  var backdrop = modal.querySelector('.cdc-modal-backdrop');
  var closeBtn = modal.querySelector('.cdc-modal-close');
  var titleEl = document.getElementById('toolModalTitle');
  var qtyEl = document.getElementById('toolModalQty');
  var safetyEl = document.getElementById('toolModalSafety');
  var noteEl = document.getElementById('toolModalNote');

  function close(){ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }
  function open(){ modal.style.display = 'block'; modal.setAttribute('aria-hidden','false'); }
  function esc(e){ if (e.key === 'Escape') { close(); } }

  window.cdcToolModalOpen = function(data){
    try {
      titleEl.textContent = data.name || 'Herramienta';
      qtyEl.textContent = data.qty || '—';
      safetyEl.innerHTML = data.sec ? ('<p><strong>Seguridad:</strong> ' + data.sec.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>') : '';
      noteEl.innerHTML = data.note ? ('<p><strong>Nota:</strong> ' + data.note.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>') : '';
      open();
      console.log('✅ [Manual] Modal herramienta abierto:', data.name);
    } catch(e) {
      console.log('❌ [Manual] Error abriendo modal herramienta:', e && e.message ? e.message : e);
    }
  };
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);
  window.addEventListener('keydown', esc);
})();
</script>
<style>
.cdc-modal-backdrop{ position:fixed; inset:0; background:rgba(0,0,0,0.35); }
.cdc-modal-content{ position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); background:#fff; border:1px solid #e3e8f3; border-radius:10px; box-shadow:var(--shadow-lg); width: min(560px, 90vw); max-height: 80vh; overflow:auto; padding:14px 16px; }
.cdc-modal-close{ position:absolute; right:10px; top:8px; background:transparent; border:none; font-size:22px; line-height:1; cursor:pointer; }
.cdc-modal-body p{ margin:8px 0; }
</style>
<?php include 'includes/footer.php'; ?>
