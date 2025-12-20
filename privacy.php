<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Política de Privacidad';
$page_description = 'Privacidad de Clase de Ciencia';
$canonical_url = canonical_url('privacy.php');
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <p>Respetamos tu privacidad. Sitio sin registro ni seguimiento invasivo.</p>
</main>
<script>console.log('🔍 [Privacy] Loaded');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>