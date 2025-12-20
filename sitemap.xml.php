<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');
$urls = [
  canonical_url('index.php'),
  canonical_url('catalogo.php'),
  canonical_url('privacy.php'),
  canonical_url('terms.php'),
];
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
 <url><loc><?= h($u) ?></loc></url>
<?php endforeach; ?>
</urlset>