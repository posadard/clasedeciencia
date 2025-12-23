<?php
// Kit Manual detail (public)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$kit_slug = isset($_GET['kit']) ? trim($_GET['kit']) : '';
$manual_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

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

$page_title = 'Manual: ' . h($manual['slug']) . ' - ' . h($kit['nombre']);
$page_description = 'Guía/Manual del kit ' . h($kit['nombre']) . ' (' . h($manual['slug']) . ')';
$canonical_url = SITE_URL . '/kit-manual.php?kit=' . urlencode($kit['slug']) . '&slug=' . urlencode($manual['slug']);

include 'includes/header.php';
?>
<div class="container">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / 
    <a href="/kit.php?slug=<?= urlencode($kit['slug']) ?>"><?= h($kit['nombre']) ?></a> / 
    <strong>Manual: <?= h($manual['slug']) ?></strong>
  </div>

  <header class="manual-header">
    <h1>🛠️ Manual: <?= h($manual['slug']) ?></h1>
    <div class="manual-meta">
      <span class="badge">Versión <?= h($manual['version']) ?></span>
      <?php if (!empty($manual['idioma'])): ?><span class="badge">Idioma <?= h($manual['idioma']) ?></span><?php endif; ?>
      <?php if (!empty($manual['time_minutes'])): ?><span class="badge">⏱️ <?= (int)$manual['time_minutes'] ?> min</span><?php endif; ?>
      <?php if (!empty($manual['dificultad_ensamble'])): ?><span class="badge">📊 <?= h($manual['dificultad_ensamble']) ?></span><?php endif; ?>
    </div>
  </header>

  <article class="manual-content">
    <?php if (!empty($manual['html'])): ?>
      <?= $manual['html'] ?>
    <?php else: ?>
      <?php
        $pasos = [];
        $herr = [];
        $seg = [];
        if (!empty($manual['pasos_json'])) {
            $tmp = json_decode($manual['pasos_json'], true);
            if (is_array($tmp)) { $pasos = $tmp; }
        }
        if (!empty($manual['herramientas_json'])) {
            $tmp = json_decode($manual['herramientas_json'], true);
            if (is_array($tmp)) { $herr = $tmp; }
        }
        if (!empty($manual['seguridad_json'])) {
            $tmp = json_decode($manual['seguridad_json'], true);
            if (is_array($tmp)) { $seg = $tmp; }
        }
      ?>
      <?php if (!empty($seg)): ?>
        <section class="safety-info">
          <h2>⚠️ Seguridad</h2>
          <?php
            $rendered = false;
            $segIsAssoc = is_array($seg) && array_keys($seg) !== range(0, count($seg)-1);
            $segObj = null;
            if ($segIsAssoc) { $segObj = $seg; }
            elseif (is_array($seg) && isset($seg[0]) && is_array($seg[0]) && (isset($seg[0]['edad']) || isset($seg[0]['notas']))) { $segObj = $seg[0]; }
            if ($segObj && (isset($segObj['edad']) || isset($segObj['notas']))):
              $edadMin = isset($segObj['edad']['min']) ? (int)$segObj['edad']['min'] : null;
              $edadMax = isset($segObj['edad']['max']) ? (int)$segObj['edad']['max'] : null;
          ?>
              <div class="kit-security-chip">Edad segura: <?= ($edadMin !== null ? $edadMin : '?') ?>–<?= ($edadMax !== null ? $edadMax : '?') ?> años</div>
              <?php if (!empty($segObj['notas']) && is_array($segObj['notas'])): ?>
                <ul class="security-list">
                  <?php foreach ($segObj['notas'] as $nota): ?>
                    <?php if (is_array($nota)): ?>
                      <li><span class="sec-note"><?= h($nota['nota'] ?? '') ?></span><?php if (!empty($nota['categoria'])): ?> <span class="muted">(<?= h($nota['categoria']) ?>)</span><?php endif; ?></li>
                    <?php else: ?>
                      <li><span class="sec-note"><?= h($nota) ?></span></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <?php $rendered = true; endif; ?>
          <?php if (!$rendered): ?>
            <ul class="security-list">
              <?php foreach ($seg as $s): ?>
                <?php if (is_array($s) && (isset($s['nota']) || isset($s['categoria']))): ?>
                  <li><span class="sec-note"><?= h($s['nota'] ?? '') ?></span><?php if (!empty($s['categoria'])): ?> <span class="muted">(<?= h($s['categoria']) ?>)</span><?php endif; ?></li>
                <?php else: ?>
                  <li><span class="sec-note"><?= h(is_array($s) ? json_encode($s, JSON_UNESCAPED_UNICODE) : $s) ?></span></li>
                <?php endif; ?>
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
              <li class="manual-step">
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
console.log('🔍 [KitManual] Kit:', <?= json_encode(['id'=>$kit['id'],'slug'=>$kit['slug'],'nombre'=>$kit['nombre']]) ?>);
console.log('🔍 [KitManual] Slug manual:', '<?= h($manual_slug) ?>');
console.log('✅ [KitManual] Cargado:', <?= json_encode(['id'=>$manual['id'],'version'=>$manual['version'],'idioma'=>$manual['idioma'],'status'=>$manual['status']]) ?>);
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
@media print {
  .manual-step { page-break-inside: avoid; }
  .kit-security-chip { background:#fff; border-color:#aaa; color:#000; }
}
</style>
<?php include 'includes/footer.php'; ?>
