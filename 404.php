<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Página no encontrada';
$page_description = '404';
$canonical_url = canonical_url('404.php');
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1>404 - Página no encontrada</h1>
  <p>La página solicitada no existe. Regresa al <a href="/">inicio</a>.</p>
</main>
<script>console.log('⚠️ [404] Not found');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>