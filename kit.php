<?php
// Kit detail page (public)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('HTTP/1.1 302 Found');
    header('Location: /');
    exit;
}

// Normalize input lightly (server already enforces exact prefix)
$kit = cdc_get_kit_by_slug($pdo, $slug);
if (!$kit) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Kit no encontrado';
    $page_description = 'El kit solicitado no existe o no está activo.';
    include 'includes/header.php';
    echo '<div class="container"><h1>Kit no encontrado</h1><p>El kit solicitado no existe o no está activo.</p></div>';
    include 'includes/footer.php';
    exit;
}

$componentes = cdc_get_kit_componentes($pdo, (int)$kit['id']);
$clases = cdc_get_kit_clases($pdo, (int)$kit['id']);
$manuales = cdc_get_kit_manuals($pdo, (int)$kit['id'], true);
// Áreas del kit (múltiples)
$kit_areas = cdc_get_kit_areas($pdo, (int)$kit['id']);

// Ficha técnica del kit (precompute inline summary for reuse)
$ficha_inline = '';
try {
  $stmt = $pdo->prepare("SELECT c.atributo_id, c.valor_string, c.valor_numero, c.valor_entero, c.valor_booleano, c.valor_fecha, c.valor_datetime, c.valor_json, c.unidad_codigo, c.orden,
                                 d.etiqueta, d.tipo_dato, d.unidad_defecto,
                                 COALESCE(m.orden, 9999) AS map_orden
                            FROM atributos_contenidos c
                            JOIN atributos_definiciones d ON d.id = c.atributo_id
                            LEFT JOIN atributos_mapeo m ON m.atributo_id = c.atributo_id AND m.tipo_entidad = 'kit'
                           WHERE c.tipo_entidad = 'kit' AND c.entidad_id = ?
                           ORDER BY map_orden ASC, c.atributo_id ASC, c.orden ASC, c.id ASC");
  $stmt->execute([(int)$kit['id']]);
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
  error_log('Error ficha tecnica kit (precompute): ' . $e->getMessage());
}

$page_title = !empty($kit['seo_title']) ? h($kit['seo_title']) : h(($kit['nombre'] ?? 'Kit') . ' - Clase de Ciencia');
$page_description = !empty($kit['seo_description']) ? h($kit['seo_description']) : ( !empty($kit['resumen']) ? h($kit['resumen']) : ('Componentes, clases relacionadas y manuales del kit ' . h($kit['nombre'] ?? '')) );
$canonical_url = SITE_URL . '/' . urlencode($kit['slug']);

include 'includes/header.php';
?>
<div class="container article-page">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / <a href="/kits">Kits</a> / <strong><?= h($kit['nombre']) ?></strong>
  </div>

  <!-- Card de Resumen del Kit (mismo layout que clase) -->
  <div class="clase-summary-card">
    <div class="summary-content">
      <div class="summary-left">
        <?php if (!empty($kit['imagen_portada'])): ?>
          <img src="<?= h($kit['imagen_portada']) ?>" alt="<?= h($kit['nombre']) ?>" class="summary-image" onerror="this.onerror=null; console.log('❌ [Kit] Imagen portada falló'); var p=document.createElement('div'); p.className='summary-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='📦'; p.appendChild(s); this.replaceWith(p);" />
        <?php else: ?>
          <div class="summary-placeholder">
            <span class="placeholder-icon">📦</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="summary-right">
        <div class="summary-header">
          <h1 class="summary-title"><?= h($kit['nombre']) ?></h1>
        </div>
        <div class="summary-specs">
          <div class="spec-item">
            <span class="spec-label">🏷️ Código</span>
            <span class="spec-value"><?= h($kit['codigo'] ?? '') ?></span>
          </div>
          <?php if (!empty($kit['version']) || !empty($kit['updated_at'])): ?>
          <div class="spec-duo">
            <?php if (!empty($kit['version'])): ?>
            <div class="spec-item">
              <span class="spec-label">🔢 Versión</span>
              <span class="spec-value"><?= h($kit['version'] ?? '') ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($kit['updated_at'])): ?>
            <div class="spec-item">
              <span class="spec-label">🔄 Actualizado</span>
              <span class="spec-value"><?= date('d/m/Y', strtotime($kit['updated_at'])) ?></span>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php 
          // Edad recomendada resumida desde seguridad JSON
          $seg_summary = null;
          if (!empty($kit['seguridad'])) {
            try {
              $seg_obj = json_decode($kit['seguridad'], true);
              if (is_array($seg_obj) && !empty($seg_obj['edad_min']) && !empty($seg_obj['edad_max'])) {
                $seg_summary = [(int)$seg_obj['edad_min'], (int)$seg_obj['edad_max']];
              }
            } catch(Exception $e) { /* no-op */ }
          }
          ?>
          <?php if (!empty($kit['time_minutes']) || $seg_summary): ?>
          <div class="spec-duo">
            <?php if (!empty($kit['time_minutes'])): ?>
            <div class="spec-item">
              <span class="spec-label">⏱️ Tiempo</span>
              <span class="spec-value"><?= (int)$kit['time_minutes'] ?> min</span>
            </div>
            <?php endif; ?>
            <?php if ($seg_summary): ?>
            <div class="spec-item">
              <span class="spec-label">👥 Edad</span>
              <span class="spec-value"><?= (int)$seg_summary[0] ?>–<?= (int)$seg_summary[1] ?> años</span>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($kit['dificultad_ensamble'])): ?>
          <div class="spec-item">
            <span class="spec-label">🛠️ Dificultad</span>
            <span class="spec-value"><?= h(ucfirst($kit['dificultad_ensamble'])) ?></span>
          </div>
          <?php endif; ?>
          <?php if (!empty($ficha_inline)): ?>
          <div class="spec-item spec-item-full">
            <span class="spec-label">🧪 Ficha</span>
            <?php 
              $ficha_short = $ficha_inline;
              if (mb_strlen($ficha_short) > 100) { $ficha_short = mb_substr($ficha_short, 0, 100) . '…'; }
            ?>
            <span class="spec-value"><?= h($ficha_short) ?></span>
          </div>
          <?php endif; ?>
          <?php if (!empty($kit_areas)): ?>
          <div class="spec-item spec-item-full">
            <span class="spec-label">🔬 Áreas</span>
            <span class="spec-value">
              <?php foreach ($kit_areas as $idx => $area): ?>
                <a href="/kits?area=<?= h($area['slug']) ?>" class="area-link"><?= h($area['nombre']) ?></a><?= $idx < count($kit_areas) - 1 ? ', ' : '' ?>
              <?php endforeach; ?>
            </span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <article>
    <?php if (!empty($kit['resumen'])): ?>
    <div class="resumen-section">
      <p class="lead"><?= h($kit['resumen']) ?></p>
    </div>
    <?php endif; ?>

    <?php 
    // Preparar flags de seguridad y video
    $has_seguridad = false;
    $seguridad = null;
    if (!empty($kit['seguridad'])) {
      $seguridad = json_decode($kit['seguridad'], true);
      $has_seguridad = is_array($seguridad) && (!empty($seguridad['edad_min']) || !empty($seguridad['notas']) || !empty($seguridad['edad_max']));
    }
    $has_video = !empty($kit['video_portada']);

    // Recolectar advertencias de seguridad de materiales (kit_items)
    $kit_warnings = [];
    if (!empty($componentes) && is_array($componentes)) {
      foreach ($componentes as $m) {
        if (!empty($m['advertencias_seguridad'])) {
          $kit_warnings[] = [
            'nombre' => $m['nombre_comun'] ?? 'Material',
            'advertencia' => $m['advertencias_seguridad'],
            'slug' => $m['slug'] ?? ''
          ];
        }
      }
    }
    $has_kit_warnings = count($kit_warnings) > 0;
    ?>

    <?php if ($has_seguridad && $has_video): ?>
    <div class="intro-row">
    <section class="video-portada-section">
      <h2>🎥 Video Introductorio</h2>
      <div class="video-wrapper">
      <iframe src="<?= h($kit['video_portada']) ?>" title="Video de <?= h($kit['nombre']) ?>" allowfullscreen></iframe>
      </div>
    </section>
    <section class="safety-info">
      <h2 class="safety-title">⚠️ Información de Seguridad</h2>
      <div class="safety-content">
      <?php if (!empty($seguridad['edad_min']) && !empty($seguridad['edad_max'])): ?>
        <p class="edad-recomendada"><strong>👥 Edad recomendada:</strong> <?= (int)$seguridad['edad_min'] ?> a <?= (int)$seguridad['edad_max'] ?> años</p>
      <?php endif; ?>
      <?php if (!empty($seguridad['notas'])): ?>
        <div class="safety-notes"><?= nl2br(h($seguridad['notas'])) ?></div>
      <?php endif; ?>
      <?php if ($has_kit_warnings): ?>
        <div class="safety-kits-inline">
          <h3 class="safety-subtitle">🧪 Advertencias de materiales</h3>
          <ul class="safety-kit-list">
            <?php foreach ($kit_warnings as $kw): ?>
              <li>
                <?php if (!empty($kw['slug'])): ?>
                  <a href="/<?= h($kw['slug']) ?>" title="Ver componente" aria-label="Ver componente <?= h($kw['nombre']) ?>"><?= h($kw['nombre']) ?></a>
                <?php else: ?>
                  <strong><?= h($kw['nombre']) ?></strong>
                <?php endif; ?>
                <span>— <?= nl2br(h($kw['advertencia'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <p class="safety-note"><strong>Nota:</strong> Requiere supervisión permanente de un adulto responsable.</p>
      </div>
    </section>
    </div>
    <?php else: ?>
    <?php if ($has_seguridad): ?>
    <section class="safety-info">
      <h2 class="safety-title">⚠️ Información de Seguridad</h2>
      <div class="safety-content">
      <?php if (!empty($seguridad['edad_min']) && !empty($seguridad['edad_max'])): ?>
        <p class="edad-recomendada"><strong>👥 Edad recomendada:</strong> <?= (int)$seguridad['edad_min'] ?> a <?= (int)$seguridad['edad_max'] ?> años</p>
      <?php endif; ?>
      <?php if (!empty($seguridad['notas'])): ?>
        <div class="safety-notes"><?= nl2br(h($seguridad['notas'])) ?></div>
      <?php endif; ?>
      <?php if ($has_kit_warnings): ?>
        <div class="safety-kits-inline">
          <h3 class="safety-subtitle">🧪 Advertencias de materiales</h3>
          <ul class="safety-kit-list">
            <?php foreach ($kit_warnings as $kw): ?>
              <li>
                <?php if (!empty($kw['slug'])): ?>
                  <a href="/<?= h($kw['slug']) ?>" title="Ver componente" aria-label="Ver componente <?= h($kw['nombre']) ?>"><?= h($kw['nombre']) ?></a>
                <?php else: ?>
                  <strong><?= h($kw['nombre']) ?></strong>
                <?php endif; ?>
                <span>— <?= nl2br(h($kw['advertencia'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <p class="safety-note"><strong>Nota:</strong> Requiere supervisión permanente de un adulto responsable.</p>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($has_video): ?>
    <div class="video-portada-section">
      <h2>🎥 Video Introductorio</h2>
      <div class="video-wrapper">
      <iframe src="<?= h($kit['video_portada']) ?>" title="Video de <?= h($kit['nombre']) ?>" allowfullscreen></iframe>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($componentes)): ?>
    <section class="kits-section">
      <div class="kit-card">
        <h4>Componentes necesarios</h4>
        <ul class="materials-list">
          <?php foreach ($componentes as $m): ?>
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
          // 🔍 [Kit] Activar click en toda la tarjeta del material
          (function() {
            try {
              var items = document.querySelectorAll('.materials-list li[data-href]');
              console.log('🔍 [Kit] Materiales clicables:', items.length);
              items.forEach(function(li) {
                var href = li.getAttribute('data-href');
                if (!href) return;
                // Evitar doble navegación si se hace click en enlaces internos
                li.addEventListener('click', function(ev) {
                  var target = ev.target;
                  if (target && target.closest('a')) { return; }
                  console.log('✅ [Kit] Click en material →', href);
                  window.location.href = href;
                });
                li.addEventListener('keypress', function(ev) {
                  if (ev.key === 'Enter' || ev.key === ' ') {
                    console.log('✅ [Kit] Keypress en material →', href);
                    window.location.href = href;
                    ev.preventDefault();
                  }
                });
              });
            } catch (e) {
              console.log('❌ [Kit] Error activando materiales clicables:', e && e.message ? e.message : e);
            }
          })();
        </script>
      </div>
    </section>
    <?php endif; ?>

    <section class="kit-manuals">
      <h2>🛠️ Manuales Disponibles</h2>
      <?php if (!empty($manuales)): ?>
        <?php foreach ($manuales as $man):
          $href = '/kit-manual.php?kit=' . urlencode($kit['slug']) . '&slug=' . urlencode($man['slug']);
          $man_title = str_replace('-', ' ', (string)$man['slug']);
          $man_title = mb_convert_case($man_title, MB_CASE_TITLE, 'UTF-8');
          $idioma = !empty($man['idioma']) ? $man['idioma'] : 'es';
          $tiempo = isset($man['time_minutes']) && $man['time_minutes'] ? ((int)$man['time_minutes']) . 'm' : null;
          $version = !empty($man['version']) ? (string)$man['version'] : null;
          $dif = !empty($man['dificultad_ensamble']) ? (string)$man['dificultad_ensamble'] : null;
          // Icono por tipo de manual (si existe) o heurística por slug
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
          // Extracto del manual: resumen -> primer paso -> HTML plano
          $excerpt = '';
          try {
            $full = cdc_get_kit_manual_by_slug($pdo, (int)$kit['id'], (string)$man['slug'], true);
            if ($full) {
              if (!empty($full['resumen'])) {
                $excerpt = (string)$full['resumen'];
              } else {
                // Pasos JSON
                $firstText = '';
                if (!empty($full['pasos_json'])) {
                  $tmp = json_decode($full['pasos_json'], true);
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
                if ($firstText === '' && !empty($full['html'])) {
                  $firstText = strip_tags($full['html']);
                }
                if ($firstText !== '') {
                  $excerpt = mb_substr(trim(preg_replace('/\s+/', ' ', $firstText)), 0, 160);
                  if (mb_strlen($firstText) > 160) { $excerpt .= '…'; }
                }
              }
            }
          } catch (Exception $e) {
            error_log('Error excerpt manual ' . (string)$man['slug'] . ': ' . $e->getMessage());
          }
        ?>
          <section class="kit-inline-card" role="link" tabindex="0" aria-label="Manual <?= h($man['slug']) ?>"
                   onclick="if(!event.target.closest('a')){ console.log('📘 [Kit] Click manual →','<?= h($man['slug']) ?>'); window.location.href='<?= h($href) ?>'; }"
                   onkeypress="if(event.key==='Enter' || event.key===' '){ if(!event.target.closest('a')){ window.location.href='<?= h($href) ?>'; event.preventDefault(); } }">
            <div class="kit-inline-wrap">
              <div class="kit-inline-left">
                <div class="manual-type-emoji" aria-hidden="true"><?= $icon ?></div>
              </div>
              <div class="kit-inline-right">
                <h3 class="kit-inline-title">
                  <span><?= h($man_title) ?></span>
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
        <script>console.log('✅ [Kit] Manuales: <?= count($manuales) ?> cards renderizadas');</script>
      <?php else: ?>
        <p class="muted">Aún no hay manuales publicados para este kit.</p>
      <?php endif; ?>
    </section>

    <section class="article-content">
    <?php if (!empty($kit['contenido_html'])): ?>
      <div class="article-body">
      <?= $kit['contenido_html'] ?>
      </div>
    <?php endif; ?>
    </section>

    <?php // Ficha técnica ya precomputada como $ficha_inline arriba ?>
    <?php if ($ficha_inline !== '' || !empty($kit['updated_at'])): ?>
    <div class="article-byline">
      <?php if (!empty($kit['updated_at'])): ?>
        <span class="updated">🔄 Actualizado: <?= date('d/m/Y', strtotime($kit['updated_at'])) ?></span>
      <?php endif; ?>
      <?php if ($ficha_inline !== ''): ?>
        <span class="ficha">🧪 <?= h($ficha_inline) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  

    <?php if (!empty($clases)): ?>
    <section class="kit-classes">
      <h2>📚 Clases Relacionadas</h2>
      <div class="related-grid">
        <?php foreach ($clases as $c): ?>
          <a href="/<?= h($c['slug']) ?>" class="related-card">
            <?php if (!empty($c['imagen_portada'])): ?>
              <img src="<?= h($c['imagen_portada']) ?>" alt="<?= h($c['nombre']) ?>" class="related-thumbnail" onerror="this.onerror=null; console.log('❌ [Kit] Miniatura relacionada falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
            <?php else: ?>
              <div class="thumbnail-placeholder">
                <span class="placeholder-icon">🔬</span>
              </div>
              <script>console.log('⚠️ [Kit] Miniatura relacionada sin imagen, usando placeholder');</script>
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
  </article>
</div>
<script>
console.log('🔍 [Kit] Slug:', '<?= h($slug) ?>');
console.log('🔍 [Kit] Tiempo y dificultad:', {
  time_minutes: <?= isset($kit['time_minutes']) && $kit['time_minutes'] !== null ? (int)$kit['time_minutes'] : 'null' ?>,
  dificultad_ensamble: '<?= h($kit['dificultad_ensamble'] ?? '') ?>'
});
console.log('✅ [Kit] Cargado:', <?= json_encode(['id'=>$kit['id'],'nombre'=>$kit['nombre'],'codigo'=>$kit['codigo']]) ?>);
console.log('📦 [Kit] Componentes:', <?= count($componentes) ?>);
console.log('⚙️ [Kit] Orden: componentes antes de contenido_html');
console.log('✅ [Kit] Componentes estilo clase.php aplicados');
console.log('🧩 [Kit] Componentes: título "Componentes necesarios"');
console.log('🛠️ [Kit] Manuales movidos después de contenido_html');
console.log('🛡️ [Kit] Nota de seguridad aplicada por defecto');
console.log('📚 [Kit] Clases vinculadas:', <?= count($clases) ?>);
console.log('🛠️ [Kit] Manuales:', <?= count($manuales) ?>);
</script>
<style>
/* Bigger emoji instead of blue placeholder box for manual type */
.kit-inline-card .kit-inline-wrap { display:flex; gap:12px; align-items:center; }
.kit-inline-card .kit-inline-left { flex: 0 0 72px; width:72px; display:flex; align-items:center; justify-content:center; }
.kit-inline-card .manual-type-emoji { font-size:56px; line-height:1; filter: drop-shadow(0 1px 0 rgba(0,0,0,0.06)); }
@media (max-width: 600px) {
  .kit-inline-card .kit-inline-left { flex-basis:56px; width:56px; }
  .kit-inline-card .manual-type-emoji { font-size:44px; }
}
</style>
<?php include 'includes/footer.php'; ?>
