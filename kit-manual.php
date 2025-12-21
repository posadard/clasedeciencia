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
          <ul>
            <?php foreach ($seg as $s): ?>
              <li><?= h(is_array($s) ? json_encode($s, JSON_UNESCAPED_UNICODE) : $s) ?></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($herr)): ?>
        <section>
          <h2>🔧 Herramientas</h2>
          <ul>
            <?php foreach ($herr as $hitem): ?>
              <li><?= h(is_array($hitem) ? json_encode($hitem, JSON_UNESCAPED_UNICODE) : $hitem) ?></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($pasos)): ?>
        <section>
          <h2>📋 Pasos</h2>
          <ol>
            <?php foreach ($pasos as $idx => $p): ?>
              <li>
                <strong><?= h($p['titulo'] ?? ('Paso ' . ($idx + 1))) ?></strong>
                <?php if (!empty($p['descripcion'])): ?>
                  <p><?= h($p['descripcion']) ?></p>
                <?php elseif (!empty($p['texto'])): ?>
                  <p><?= h($p['texto']) ?></p>
                <?php endif; ?>
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
<?php include 'includes/footer.php'; ?>
