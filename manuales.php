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
    $where = ["m.status = 'published'", "k.activo = 1"];
    $params = [];
    if ($tipo !== '') { $where[] = 'm.tipo_manual = ?'; $params[] = $tipo; }
    if ($ambito !== '' && in_array($ambito, ['kit','componente'])) { $where[] = 'm.ambito = ?'; $params[] = $ambito; }
    if ($idioma !== '') { $where[] = 'm.idioma = ?'; $params[] = $idioma; }
        $sql = "SELECT m.id, m.slug, m.version, m.idioma, m.time_minutes, m.dificultad_ensamble, m.updated_at, m.published_at,
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
<div class="container">
  <div class="breadcrumb">
    <a href="/">Inicio</a> / <strong>Manuales</strong>
  </div>

  <header>
    <h1>Manuales publicados</h1>
    <p class="muted">Todos los manuales de kits y componentes disponibles públicamente.</p>
  </header>

  <section class="filters">
    <form method="get" class="filter-form">
      <label>
        Tipo:
        <select name="tipo">
          <option value="">(Todos)</option>
          <?php foreach ($tipo_map as $key => $def): ?>
            <option value="<?= h($key) ?>" <?= $tipo === $key ? 'selected' : '' ?>><?= h($def['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        Ámbito:
        <select name="ambito">
          <option value="">(Todos)</option>
          <option value="kit" <?= $ambito === 'kit' ? 'selected' : '' ?>>Kit</option>
          <option value="componente" <?= $ambito === 'componente' ? 'selected' : '' ?>>Componente</option>
        </select>
      </label>
      <label>
        Idioma:
        <input type="text" name="idioma" value="<?= h($idioma) ?>" placeholder="ES, EN..." />
      </label>
      <button type="submit" class="btn">Filtrar</button>
    </form>
  </section>

  <section class="manuales-grid">
    <?php if (empty($items)): ?>
      <p class="muted">No hay manuales publicados con estos filtros.</p>
    <?php else: ?>
      <div class="grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        <?php foreach ($items as $m): ?>
          <?php
            $tk = strtolower((string)($m['tipo_manual'] ?? ''));
            $emoji = '📘'; $label = 'Manual';
            if ($tk && isset($tipo_map[$tk])) { $emoji = $tipo_map[$tk]['emoji']; $label = $tipo_map[$tk]['label']; }
            elseif (strpos(strtolower($m['slug']), 'arm') !== false) { $emoji = '🛠️'; $label = 'Armado'; }
            $combined = $m['slug'] . '-' . ($m['ambito'] === 'componente' && !empty($m['item_slug']) ? $m['item_slug'] : $m['kit_slug']);
            $href = '/' . h($combined);
          ?>
          <a class="manual-card" href="<?= h($href) ?>" style="display:block; border:1px solid #e3e8f3; border-radius:8px; padding:10px; background:#fff; text-decoration:none;">
            <div style="display:flex; gap:10px; align-items:center;">
              <div style="font-size:36px; line-height:1;"><?= $emoji ?></div>
              <div>
                <div style="font-weight:600; color:#1f3c88;"><?= h(ucwords(str_replace('-', ' ', (string)$m['slug']))) ?></div>
                <div style="color:#5f6368; font-size:0.9rem;">
                  <?= h($label) ?> · <?= h($m['ambito']) ?> · <?= !empty($m['idioma']) ? h($m['idioma']) : '—' ?>
                </div>
                <div style="margin-top:4px; display:flex; gap:8px; flex-wrap:wrap; color:#5f6368; font-size:0.85rem;">
                  <span>📦 <?= h($m['kit_nombre']) ?></span>
                  <?php if (!empty($m['time_minutes'])): ?><span>⏱️ <?= (int)$m['time_minutes'] ?> min</span><?php endif; ?>
                  <?php if (!empty($m['version'])): ?><span>🔢 v<?= h($m['version']) ?></span><?php endif; ?>
                  <?php if (!empty($m['dificultad_ensamble'])): ?><span>🛠️ <?= h($m['dificultad_ensamble']) ?></span><?php endif; ?>
                  <?php if (!empty($m['published_at'])): ?><span>🗓️ <?= h(date('d/m/Y', strtotime($m['published_at']))) ?></span><?php endif; ?>
                </div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<script>
console.log('🔍 [Manuales] Total:', <?= count($items) ?>);
console.log('🔍 [Manuales] Filtros:', <?= json_encode(['tipo'=>$tipo, 'ambito'=>$ambito, 'idioma'=>$idioma]) ?>);
</script>
<?php include 'includes/footer.php'; ?>
