<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Admin - Proyectos';
$page_description = 'Listado de proyectos';
include __DIR__ . '/../header.php';

$proyectos = [];
try {
  if ($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, slug, ciclo, dificultad, duracion_minutos, COALESCE(activo,1) AS activo FROM proyectos ORDER BY id DESC LIMIT 200");
    $proyectos = $stmt->fetchAll();
  }
} catch (PDOException $e) { error_log($e->getMessage()); }
?>
<h1>Proyectos</h1>
<div class="admin-actions">
  <a class="btn-primary" href="edit.php">Nuevo proyecto</a>
</div>
<table class="admin-table">
  <thead><tr><th>ID</th><th>Nombre</th><th>Ciclo</th><th>Dificultad</th><th>Duración</th><th>Estado</th><th>Acciones</th></tr></thead>
  <tbody>
    <?php foreach ($proyectos as $p): ?>
      <tr>
        <td><?= h($p['id']) ?></td>
        <td><a href="/proyecto.php?slug=<?= h($p['slug']) ?>" target="_blank"><?= h($p['nombre']) ?></a></td>
        <td><?= h($p['ciclo']) ?></td>
        <td><?= h($p['dificultad']) ?></td>
        <td><?= h($p['duracion_minutos']) ?> min</td>
        <td><?= $p['activo']?'<span class="badge">Activo</span>':'Inactivo' ?></td>
        <td>
          <a href="edit.php?id=<?= h($p['id']) ?>">Editar</a>
          <a href="delete.php?id=<?= h($p['id']) ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<script>console.log('✅ [Admin] Proyectos listados: <?= count($proyectos) ?>');</script>
<?php include __DIR__ . '/../footer.php'; ?>