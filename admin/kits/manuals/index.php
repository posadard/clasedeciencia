<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../../config.php';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$kit_id = isset($_GET['kit_id']) ? intval($_GET['kit_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$error_msg = '';
$success_msg = '';

// Handle POST actions: delete, status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [ManualsIndex] CSRF inválido");</script>';
  } else {
    $action = $_POST['action'] ?? '';
    $manual_id = isset($_POST['manual_id']) ? intval($_POST['manual_id']) : 0;
    try {
      if ($action === 'delete' && $manual_id > 0) {
        $stmt = $pdo->prepare('DELETE FROM kit_manuals WHERE id = ?');
        $stmt->execute([$manual_id]);
        $success_msg = 'Manual eliminado correctamente.';
        echo '<script>console.log("✅ [ManualsIndex] Manual eliminado ID=' . $manual_id . '");</script>';
      } elseif ($action === 'toggle_status' && $manual_id > 0) {
        $stmt = $pdo->prepare('SELECT status FROM kit_manuals WHERE id = ? LIMIT 1');
        $stmt->execute([$manual_id]);
        $curr = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($curr) {
          $newStatus = ($curr['status'] === 'published') ? 'draft' : 'published';
          $pubAt = ($newStatus === 'published') ? date('Y-m-d H:i:s') : null;
          $stmtU = $pdo->prepare('UPDATE kit_manuals SET status = ?, published_at = ? WHERE id = ?');
          $stmtU->execute([$newStatus, $pubAt, $manual_id]);
          $success_msg = 'Estado actualizado a ' . $newStatus . '.';
          echo '<script>console.log("✅ [ManualsIndex] Estado manual ID=' . $manual_id . ' -> ' . $newStatus . '");</script>';
        }
      }
    } catch (PDOException $e) {
      $error_msg = 'Error al aplicar acción: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
      echo '<script>console.log("❌ [ManualsIndex] Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
    }
  }
}

$kit = null;
if ($kit_id > 0) {
  $stmtK = $pdo->prepare('SELECT id, nombre, codigo, slug FROM kits WHERE id = ? LIMIT 1');
  $stmtK->execute([$kit_id]);
  $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
}

$manuals = [];
try {
  if ($kit_id > 0) {
    $params = [$kit_id];
    $sql = 'SELECT km.*, k.nombre AS kit_nombre, ki.nombre_comun AS item_nombre 
            FROM kit_manuals km 
            LEFT JOIN kits k ON k.id = km.kit_id 
            LEFT JOIN kit_items ki ON ki.id = km.item_id 
            WHERE km.kit_id = ?';
    if ($search !== '') {
      $sql .= ' AND (km.slug LIKE ? OR km.resumen LIKE ? OR km.idioma LIKE ? OR km.tipo_manual LIKE ?)';
      $term = '%' . $search . '%';
      array_push($params, $term, $term, $term, $term);
    }
    $sql .= ' ORDER BY km.idioma, km.version DESC, km.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
  } else {
    $params = [];
    $sql = 'SELECT km.*, k.nombre AS kit_nombre, ki.nombre_comun AS item_nombre 
            FROM kit_manuals km 
            LEFT JOIN kits k ON k.id = km.kit_id 
            LEFT JOIN kit_items ki ON ki.id = km.item_id';
    if ($search !== '') {
      $sql .= ' WHERE (km.slug LIKE ? OR km.resumen LIKE ? OR km.idioma LIKE ? OR km.tipo_manual LIKE ?)';
      $term = '%' . $search . '%';
      array_push($params, $term, $term, $term, $term);
    }
    $sql .= ' ORDER BY km.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
  }
  $manuals = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo '<script>console.log("🔍 [ManualsIndex] Cargados ' . count($manuals) . ' manuales");</script>';
} catch (PDOException $e) {
  $error_msg = 'Error cargando manuales: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
  echo '<script>console.log("❌ [ManualsIndex] Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
}
?>
<div class="container">
  <h1>Manuales de Kits</h1>
  <?php if ($kit): ?>
    <p>Kit: <strong><?= htmlspecialchars($kit['nombre']) ?></strong> (ID <?= (int)$kit['id'] ?>)</p>
    <p><a href="/admin/kits/edit.php?id=<?= (int)$kit['id'] ?>">Volver al Kit</a></p>
  <?php endif; ?>

  <?php if ($error_msg): ?><div class="message error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
  <?php if ($success_msg): ?><div class="message success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

  <div class="filters-bar">
    <form method="GET" class="filters-form">
      <?php if ($kit_id > 0): ?>
        <input type="hidden" name="kit_id" value="<?= (int)$kit_id ?>" />
      <?php endif; ?>
      <div class="filter-group search-group">
        <label for="search">Buscar:</label>
        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Slug, resumen, idioma o tipo..." />
        <button type="submit" class="btn btn-sm">🔍 Buscar</button>
        <?php if ($search !== ''): ?>
          <a href="/admin/kits/manuals/index.php<?= $kit_id ? ('?kit_id=' . (int)$kit_id) : '' ?>" class="btn btn-sm btn-secondary">Limpiar</a>
        <?php endif; ?>
      </div>
    </form>
    <script>
      console.log('🔍 [ManualsIndex] Filtro búsqueda:', '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>');
    </script>
  </div>

  <div style="margin: 12px 0;">
    <a class="btn" href="/admin/kits/manuals/edit.php?<?= $kit ? 'kit_id=' . (int)$kit['id'] : '' ?>">+ Nuevo Manual</a>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>Entidad</th>
        <th>Idioma</th>
        <th>Tipo de Manual</th>
        <th>Versión</th>
        <th>Status</th>
        <th>Actualizado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($manuals as $m): ?>
        <tr>
          <td>
            <?php
              $ambito = $m['ambito'] ?? 'kit';
              $entidad = '';
              if ($ambito === 'componente') {
                $entidad = $m['item_nombre'] ?? '';
              } else {
                // Prefer kit context name if available
                $entidad = $kit ? ($kit['nombre'] ?? '') : ($m['kit_nombre'] ?? '');
              }
              echo htmlspecialchars($entidad, ENT_QUOTES, 'UTF-8');
            ?>
          </td>
          <td><?= htmlspecialchars($m['idioma']) ?></td>
          <td><span class="badge"><?= htmlspecialchars($m['tipo_manual']) ?></span></td>
          <td><?= htmlspecialchars($m['version']) ?></td>
          <td><?= htmlspecialchars($m['status']) ?></td>
          <td><?= htmlspecialchars($m['updated_at']) ?></td>
          <td>
            <a class="btn btn-sm" href="/admin/kits/manuals/edit.php?id=<?= (int)$m['id'] ?>">Editar</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar manual?');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="manual_id" value="<?= (int)$m['id'] ?>" />
              <input type="hidden" name="action" value="delete" />
              <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
            </form>
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="manual_id" value="<?= (int)$m['id'] ?>" />
              <input type="hidden" name="action" value="toggle_status" />
              <button class="btn btn-sm" type="submit">Alternar Estado</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>
<script>
console.log('🔍 [ManualsIndex] Kit ID:', <?= $kit ? (int)$kit['id'] : 0 ?>);
</script>

<style>
.filters-bar { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin: 1rem 0 2rem; }
.filters-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
.filter-group label { font-weight: bold; font-size: 0.9rem; }
.filter-group select, .filter-group input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; min-width: 200px; }
.search-group { flex-direction: row; align-items: center; flex: 1; }
.search-group input { flex: 1; }
.btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
.badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold; background: #e7e7e7; color: #333; }
</style>