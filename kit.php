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

$page_title = h(($kit['nombre'] ?? 'Kit') . ' - Clase de Ciencia');
$page_description = 'Componentes, clases relacionadas y manuales del kit ' . h($kit['nombre'] ?? '');
$canonical_url = SITE_URL . '/kit-' . urlencode($kit['slug']);

include 'includes/header.php';
?>
<div class="container">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / <strong><?= h($kit['nombre']) ?></strong>
  </div>

  <div class="kit-summary-card">
    <div class="summary-header">
      <h1><?= h($kit['nombre']) ?></h1>
      <span class="badge">Versión <?= h($kit['version']) ?></span>
      <span class="badge">Código <?= h($kit['codigo']) ?></span>
    </div>
    <p class="muted">Explora los componentes incluidos, clases relacionadas y manuales de ensamble/uso.</p>
  </div>

  <?php if (!empty($componentes)): ?>
  <section class="kit-components">
    <h2>📦 Componentes del Kit</h2>
    <ul class="materials-list">
      <?php foreach ($componentes as $m): ?>
        <li>
          <span class="material-name"><?= h($m['nombre_comun']) ?></span>
          <?php if (!empty($m['slug'])): ?>
            <a href="/<?= h($m['slug']) ?>" class="icon-link" title="Ver componente" aria-label="Ver componente <?= h($m['nombre_comun']) ?>" style="margin-left:6px; text-decoration:none;">🔎</a>
          <?php endif; ?>
          <?php if (!empty($m['advertencias_seguridad'])): ?>
            <small class="material-warning">⚠️ <?= h($m['advertencias_seguridad']) ?></small>
          <?php endif; ?>
          <?php if (!empty($m['cantidad'])): ?>
            <span class="badge"><?= h($m['cantidad']) ?> <?= h($m['unidad'] ?? '') ?></span>
          <?php endif; ?>
          <?php if (!empty($m['notas'])): ?>
            <small class="material-notes"><?= h($m['notas']) ?></small>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!empty($clases)): ?>
  <section class="kit-classes">
    <h2>📚 Clases Relacionadas</h2>
    <div class="related-grid">
      <?php foreach ($clases as $c): ?>
        <a href="/proyecto.php?slug=<?= h($c['slug']) ?>" class="related-card">
          <?php if (!empty($c['imagen_portada'])): ?>
            <img src="<?= h($c['imagen_portada']) ?>" alt="<?= h($c['nombre']) ?>" class="related-thumbnail" />
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

  <section class="kit-manuals">
    <h2>🛠️ Manuales Disponibles</h2>
    <?php if (!empty($manuales)): ?>
      <ul>
        <?php foreach ($manuales as $man): ?>
          <li>
            <a href="/kit-manual.php?kit=<?= urlencode($kit['slug']) ?>&slug=<?= urlencode($man['slug']) ?>">
              <?= h($man['slug']) ?> (v<?= h($man['version']) ?>)
            </a>
            <small class="muted">Idioma: <?= h($man['idioma']) ?><?= $man['time_minutes'] ? ' · ' . (int)$man['time_minutes'] . ' min' : '' ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Aún no hay manuales publicados para este kit.</p>
    <?php endif; ?>
  </section>
</div>
<script>
console.log('🔍 [Kit] Slug:', '<?= h($slug) ?>');
console.log('✅ [Kit] Cargado:', <?= json_encode(['id'=>$kit['id'],'nombre'=>$kit['nombre'],'codigo'=>$kit['codigo']]) ?>);
console.log('📦 [Kit] Componentes:', <?= count($componentes) ?>);
console.log('📚 [Kit] Clases vinculadas:', <?= count($clases) ?>);
console.log('🛠️ [Kit] Manuales:', <?= count($manuales) ?>);
</script>
<?php include 'includes/footer.php'; ?>
