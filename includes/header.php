<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($page_title ?? SITE_NAME) ?></title>
<meta name="description" content="<?= h($page_description ?? '') ?>">
<link rel="canonical" href="<?= h($canonical_url ?? SITE_URL) ?>">
<link rel="stylesheet" href="/assets/css/main.css">
<!-- TGA visual alignment styles -->
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/article-content.css">
<link rel="stylesheet" href="/assets/css/print.css" media="print">
<script>
  console.log('✅ [Styles] TGA styles linked: style.css, article-content.css, print.css');
</script>
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="brand" href="/"><?= h(SITE_NAME) ?></a>
    <nav class="nav">
      <a href="/catalogo.php">Catálogo</a>
      <a href="/materials.php">Materiales</a>
      <a href="/search.php">Buscar</a>
      <a href="/privacy.php">Privacidad</a>
      <a href="/terms.php">Términos</a>
    </nav>
  </div>
</header>