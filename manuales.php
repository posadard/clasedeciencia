<?php
// Manuales list (public)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = 'Manuales publicados';
$page_description = 'Listado de manuales de kits y componentes publicados';
$canonical_url = SITE_URL . '/manuales.php';

// Filters
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$ambito = isset($_GET['ambito']) ? trim($_GET['ambito']) : '';
$idioma = isset($_GET['idioma']) ? trim($_GET['idioma']) : '';

$items = [];
try {
    $where = ["k.activo = 1"];
    $params = [];
    // Mostrar publicados y descontinuados
    $where[] = "m.status IN ('published','discontinued')";
    if ($tipo !== '') { $where[] = 'm.tipo_manual = ?'; $params[] = $tipo; }
    if ($ambito !== '' && in_array($ambito, ['kit','componente'])) { $where[] = 'm.ambito = ?'; $params[] = $ambito; }
    if ($idioma !== '') { $where[] = 'm.idioma = ?'; $params[] = $idioma; }
      $sql = "SELECT m.id, m.slug, m.version, m.idioma, m.time_minutes, m.dificultad_ensamble, m.updated_at, m.published_at, m.status,
             m.tipo_manual, m.ambito, m.item_id,
             k.id AS kit_id, k.nombre AS kit_nombre, k.slug AS kit_slug,
             i.slug AS item_slug
           FROM kit_manuals m
           JOIN kits k ON k.id = m.kit_id
           LEFT JOIN kit_items i ON i.id = m.item_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY k.nombre ASC, m.slug ASC, m.version DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log('Error manuales list: ' . $e->getMessage());
    $items = [];
}

// Tipo mapping
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

include 'includes/header.php';
?>
<div class="container library-page">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / <strong>Manuales</strong>
  </div>

  <h1>Manuales publicados</h1>
  <div class="library-layout">
    <aside class="filters-sidebar">
      <h2>Filtrar</h2>
      <form method="get" action="/manuales.php" class="filters-form">
        <div class="filter-group">
          <label class="filter-title" for="tipo">Tipo</label>
          <select id="tipo" name="tipo">
            <option value="">(Todos)</option>
            <?php foreach ($tipo_map as $key => $def): ?>
              <option value="<?= h($key) ?>" <?= $tipo === $key ? 'selected' : '' ?>><?= h($def['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-title" for="ambito">Ámbito</label>
          <select id="ambito" name="ambito">
            <option value="">(Todos)</option>
            <option value="kit" <?= $ambito === 'kit' ? 'selected' : '' ?>>Kit</option>
            <option value="componente" <?= $ambito === 'componente' ? 'selected' : '' ?>>Componente</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-title" for="idioma">Idioma</label>
          <input type="text" id="idioma" name="idioma" value="<?= h($idioma) ?>" placeholder="ES, EN..." />
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="margin-right:6px;">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
            Filtrar
          </button>
          <a href="/manuales.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </aside>

    <div class="library-content">
      <div class="results-header">
        <p class="results-count">
          Mostrando <?= count($items) ?> manuales
        </p>
      </div>

      <?php if (empty($items)): ?>
        <div class="no-results">
          <p>No hay manuales publicados con estos filtros.</p>
          <a href="/manuales.php" class="btn btn-secondary">Ver todos</a>
        </div>
      <?php else: ?>
        <div class="articles-grid">
          <?php foreach ($items as $m): ?>
          <?php
            $tk = strtolower((string)($m['tipo_manual'] ?? ''));
            $emoji = '📘'; $label = 'Manual';
            if ($tk && isset($tipo_map[$tk])) { $emoji = $tipo_map[$tk]['emoji']; $label = $tipo_map[$tk]['label']; }
            elseif (strpos(strtolower($m['slug']), 'arm') !== false) { $emoji = '🛠️'; $label = 'Armado'; }
            $href = '/' . h($m['slug']);
            $is_disc = isset($m['status']) && strtolower((string)$m['status']) === 'discontinued';
          ?>
          <article class="article-card" data-href="<?= h($href) ?>">
            <a class="card-link" href="<?= h($href) ?>">
              <div class="card-content">
                <div class="card-meta">
                  <span class="section-badge">Manual</span>
                  <span class="badge"><?= h($label) ?></span>
                  <?php if (!empty($m['idioma'])): ?><span class="badge"><?= h($m['idioma']) ?></span><?php endif; ?>
                  <?php if ($is_disc): ?><span class="badge badge-danger">⚠️ Descontinuado</span><?php endif; ?>
                </div>
                <h3><?= h(ucwords(str_replace('-', ' ', (string)$m['slug']))) ?></h3>
                <div class="card-footer">
                  <span class="area">📦 <?= h($m['kit_nombre']) ?></span>
                  <?php if (!empty($m['time_minutes'])): ?><span class="age">⏱️ <?= (int)$m['time_minutes'] ?> min</span><?php endif; ?>
                  <?php if (!empty($m['version'])): ?><span class="badge">v<?= h($m['version']) ?></span><?php endif; ?>
                  <?php if (!empty($m['dificultad_ensamble'])): ?><span class="badge">🛠️ <?= h($m['dificultad_ensamble']) ?></span><?php endif; ?>
                  <?php if (!empty($m['published_at'])): ?><span class="muted">Publicado: <?= h(date('d/m/Y', strtotime($m['published_at']))) ?></span><?php endif; ?>
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
console.log('🔍 [Manuales] Total:', <?= count($items) ?>);
console.log('🔍 [Manuales] Filtros:', <?= json_encode(['tipo'=>$tipo, 'ambito'=>$ambito, 'idioma'=>$idioma]) ?>);
</script>
<?php include 'includes/footer.php'; ?>
