<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Admin - Dashboard';
$page_description = 'Panel de administración';
$canonical_url = canonical_url('admin/dashboard.php');
include __DIR__ . '/../includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <p>Bienvenido al panel. Funciones se activarán posteriormente.</p>
</main>
<script>console.log('🔍 [Admin] Dashboard');</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>