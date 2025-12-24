<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../../config.php';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$kit_id = isset($_GET['kit_id']) ? intval($_GET['kit_id']) : 0;
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
    $stmt = $pdo->prepare('SELECT * FROM kit_manuals WHERE kit_id = ? ORDER BY idioma, version DESC, id DESC');
    $stmt->execute([$kit_id]);
  } else {
    $stmt = $pdo->query('SELECT km.*, k.nombre AS kit_nombre FROM kit_manuals km JOIN kits k ON k.id = km.kit_id ORDER BY km.id DESC');
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

  <div style="margin: 12px 0;">
    <a class="btn" href="/admin/kits/manuals/edit.php?<?= $kit ? 'kit_id=' . (int)$kit['id'] : '' ?>">+ Nuevo Manual</a>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <?php if (!$kit): ?><th>Kit</th><?php endif; ?>
        <th>ID</th>
        <th>Slug</th>
        <th>Idioma</th>
        <th>Versión</th>
        <th>Status</th>
        <th>Actualizado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($manuals as $m): ?>
        <tr>
          <?php if (!$kit): ?><td><?= htmlspecialchars($m['kit_nombre'] ?? '') ?></td><?php endif; ?>
          <td><?= (int)$m['id'] ?></td>
          <td><?= htmlspecialchars($m['slug']) ?></td>
          <td><?= htmlspecialchars($m['idioma']) ?></td>
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