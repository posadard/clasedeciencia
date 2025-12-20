<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$page_title = 'Proyecto';
$page_description = 'Guía paso a paso del proyecto';
$canonical_url = canonical_url('proyecto.php?slug=' . $slug);

try { $proyecto = $slug ? get_proyecto_por_slug($pdo, $slug) : null; } catch (PDOException $e) { error_log($e->getMessage()); $proyecto = null; }

include __DIR__ . '/includes/header.php';
?>
<main class="container">
<?php if ($proyecto): ?>
  <div class="article-body">
    <h1><?= h($proyecto['nombre']) ?></h1>
    <p>Dificultad: <?= h($proyecto['dificultad']) ?> · Duración: <?= h($proyecto['duracion_minutos']) ?> min</p>
    <?php 
    $pasos = [];
    if (!empty($proyecto['pasos'])) {
        $decoded = json_decode($proyecto['pasos'], true);
        if (is_array($decoded)) $pasos = $decoded;
    }
    ?>
    <div class="steps">
      <?php foreach ($pasos as $i => $paso): ?>
        <div class="step"><p><?= h($paso) ?></p></div>
      <?php endforeach; ?>
    </div>
    <section class="explicacion">
      <h2>Explicación Científica</h2>
      <p><?= h($proyecto['explicacion_cientifica'] ?? 'Pendiente') ?></p>
    </section>
    <div class="print-instructions">
      <p>Imprime esta guía o guarda como PDF.</p>
      <button class="btn-pdf" onclick="try{window.print();console.log('✅ [Print] Iniciado');}catch(e){console.log('❌ [Print] Error:', e.message);}">Imprimir / Guardar PDF</button>
    </div>
  </div>
<?php else: ?>
  <h1>Proyecto no encontrado</h1>
  <p>Verifica el enlace o visita el catálogo.</p>
<?php endif; ?>
</main>
<script>
console.log('🔍 [Proyecto] Slug:', '<?= h($slug) ?>');
console.log('✅ [Proyecto] Cargado:', <?= $proyecto ? 'true' : 'false' ?>);
console.log('🔍 [Proyecto] Pasos:', <?= isset($pasos) ? count($pasos) : 0 ?>);
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>