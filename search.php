<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Buscar';
$page_description = 'Busca proyectos y materiales';
$canonical_url = canonical_url('search.php');
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <form id="searchForm">
    <input type="text" name="q" id="q" placeholder="Busca proyectos" />
    <button class="btn-primary" type="submit">Buscar</button>
  </form>
  <div id="results" class="grid" style="margin-top:1rem"></div>
</main>
<script src="/assets/js/search.js"></script>
<script>console.log('🔍 [Search] Ready');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>