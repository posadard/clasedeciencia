<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = 'Material';
$page_description = 'Detalle del material';
$canonical_url = canonical_url('material.php?id=' . $id);

try { $material = $id ? get_material_por_id($pdo, $id) : null; } catch (PDOException $e) { error_log($e->getMessage()); $material = null; }

include __DIR__ . '/includes/header.php';
?>
<main class="container">
<?php if ($material): ?>
  <h1><?= h($material['nombre_comun']) ?></h1>
  <p>Categoría: <?= h($material['categoria_id']) ?></p>
  <?php if (!empty($material['advertencias_seguridad'])): ?>
    <section class="explicacion">
      <h2>Advertencias de seguridad</h2>
      <p>⚠️ <?= h($material['advertencias_seguridad']) ?></p>
    </section>
  <?php endif; ?>
<?php else: ?>
  <h1>Material no encontrado</h1>
<?php endif; ?>
</main>
<script>
console.log('🔍 [Material] id:', <?= $id ?>);
console.log('✅ [Material] Cargado:', <?= $material ? 'true' : 'false' ?>);
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>