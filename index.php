<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Inicio';
$page_description = 'Plataforma educativa de proyectos científicos.';
$canonical_url = canonical_url('index.php');

try {
    $proyectos = get_proyectos($pdo, ['ciclo' => 1]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    $proyectos = [];
}

include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <section class="grid">
    <?php foreach ($proyectos as $p): ?>
      <article class="proyecto-card">
        <h2><a href="/proyecto.php?slug=<?= h($p['slug']) ?>"><?= h($p['nombre']) ?></a></h2>
        <p>Dificultad: <?= h($p['dificultad']) ?> · Duración: <?= h($p['duracion_minutos']) ?> min</p>
      </article>
    <?php endforeach; ?>
    <?php if (empty($proyectos)): ?>
      <p>No hay proyectos disponibles aún.</p>
    <?php endif; ?>
  </section>
</main>
<script>
console.log('🔍 [Index] Loaded with <?= count($proyectos) ?> items');
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>