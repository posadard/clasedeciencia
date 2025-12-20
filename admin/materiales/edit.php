<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$page_title = $is_new ? 'Nuevo Material' : 'Editar Material';
include __DIR__ . '/../header.php';
$material = null;
if (!$is_new) {
  $stmt = $pdo->prepare("SELECT * FROM materiales WHERE id = ?");
  $stmt->execute([$id]);
  $material = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo "<script>console.log('🔍 [Admin] Guardando material...')</script>";
  if (!validate_csrf($_POST['csrf_token'] ?? '')) { echo '<p class="error">CSRF inválido</p>'; }
  else {
    $nombre = trim($_POST['nombre_comun'] ?? '');
    $categoria = (int)($_POST['categoria_id'] ?? 0);
    $advertencias = trim($_POST['advertencias_seguridad'] ?? '');
    try {
      if ($is_new) {
        $stmt = $pdo->prepare("INSERT INTO materiales (nombre_comun, categoria_id, advertencias_seguridad) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $categoria, $advertencias]);
      } else {
        $stmt = $pdo->prepare("UPDATE materiales SET nombre_comun=?, categoria_id=?, advertencias_seguridad=? WHERE id=?");
        $stmt->execute([$nombre, $categoria, $advertencias, $id]);
      }
      header('Location: index.php'); exit;
    } catch (PDOException $e) { error_log($e->getMessage()); echo '<p class="error">Error al guardar</p>'; }
  }
}
?>
<h1><?= h($page_title) ?></h1>
<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
  <div class="form-row">
    <div class="form-group">
      <label>Nombre común *</label>
      <input type="text" name="nombre_comun" required value="<?= h($material['nombre_comun'] ?? '') ?>" />
    </div>
    <div class="form-group">
      <label>Categoría ID</label>
      <input type="number" name="categoria_id" value="<?= h($material['categoria_id'] ?? 0) ?>" />
    </div>
    <div class="form-group" style="grid-column:1/-1">
      <label>Advertencias de seguridad</label>
      <textarea name="advertencias_seguridad" rows="4" placeholder="Precauciones, equipo de protección, etc."><?= h($material['advertencias_seguridad'] ?? '') ?></textarea>
    </div>
  </div>
  <div class="admin-actions">
    <button class="btn-primary" type="submit">Guardar</button>
    <a href="index.php">Volver</a>
  </div>
</form>
<script>console.log('🔍 [Admin] Edit material <?= $is_new?'nuevo':'ID '.$id ?>');</script>
<?php include __DIR__ . '/../footer.php'; ?>