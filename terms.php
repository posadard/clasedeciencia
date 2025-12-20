<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Términos de Servicio';
$page_description = 'Términos de Clase de Ciencia';
$canonical_url = canonical_url('terms.php');
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <p>Uso educativo. Experimentos bajo supervisión docente.</p>
</main>
<script>console.log('🔍 [Terms] Loaded');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>