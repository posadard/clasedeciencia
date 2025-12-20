<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Admin - Materiales';
include __DIR__ . '/../header.php';
$materiales = [];
try { if ($pdo) { $materiales = $pdo->query("SELECT id, nombre_comun, categoria_id FROM materiales ORDER BY nombre_comun ASC LIMIT 500")->fetchAll(); } } catch (PDOException $e) { error_log($e->getMessage()); }
?>
<h1>Materiales</h1>
<div class="admin-actions">
  <a class="btn-primary" href="edit.php">Nuevo material</a>
</div>
<table class="admin-table">
  <thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr></thead>
  <tbody>
    <?php foreach ($materiales as $m): ?>
    <tr>
      <td><?= h($m['id']) ?></td>
      <td><a href="/material.php?id=<?= h($m['id']) ?>" target="_blank"><?= h($m['nombre_comun']) ?></a></td>
      <td><?= h($m['categoria_id']) ?></td>
      <td>
        <a href="edit.php?id=<?= h($m['id']) ?>">Editar</a>
        <a href="delete.php?id=<?= h($m['id']) ?>" onclick="return confirm('¿Eliminar material?')">Eliminar</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<script>console.log('✅ [Admin] Materiales listados: <?= count($materiales) ?>');</script>
<?php include __DIR__ . '/../footer.php'; ?>