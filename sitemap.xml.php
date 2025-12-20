<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');
// URLs base
$urls = [
  canonical_url('index.php'),
  canonical_url('catalogo.php'),
  canonical_url('materials.php'),
  canonical_url('privacy.php'),
  canonical_url('terms.php'),
];
// Agregar proyectos activos
try {
  if (isset($pdo) && $pdo) {
    $stmt = $pdo->query("SELECT slug FROM proyectos WHERE activo = 1 ORDER BY id DESC LIMIT 1000");
    foreach ($stmt->fetchAll() as $row) {
      $urls[] = canonical_url('proyecto.php?slug=' . $row['slug']);
    }
  }
} catch (PDOException $e) { error_log($e->getMessage()); }
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
 <url><loc><?= h($u) ?></loc></url>
<?php endforeach; ?>
</urlset>