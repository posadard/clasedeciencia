<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';
$ciclo = isset($_GET['ciclo']) ? (int)$_GET['ciclo'] : 0;
$page_title = 'Ciclo ' . h($ciclo);
$page_description = 'Proyectos del ciclo ' . h($ciclo);
$canonical_url = canonical_url('ciclo.php?ciclo=' . $ciclo);
try { $proyectos = $ciclo ? get_proyectos($pdo, ['ciclo'=>$ciclo]) : []; } catch (PDOException $e) { error_log($e->getMessage()); $proyectos = []; }
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <section class="grid">
    <?php foreach ($proyectos as $p): ?>
      <article class="proyecto-card">
        <h2><a href="/proyecto.php?slug=<?= h($p['slug']) ?>"><?= h($p['nombre']) ?></a></h2>
        <p>Ciclo <?= h($p['ciclo']) ?> · <?= h($p['dificultad']) ?> · <?= h($p['duracion_minutos']) ?> min</p>
      </article>
    <?php endforeach; ?>
    <?php if (empty($proyectos)): ?><p>Sin resultados.</p><?php endif; ?>
  </section>
</main>
<script>console.log('🔍 [Ciclo] <?= h($ciclo) ?> proyectos: <?= count($proyectos) ?>');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>