<?php
// Legacy TGA section page no longer used.
// Redirect to catálogo with optional area mapping.
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
$redir = '/catalogo.php' . ($slug ? ('?area=' . urlencode($slug)) : '');
header('Location: ' . $redir, true, 302);
echo '<!doctype html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redir, ENT_QUOTES, 'UTF-8') . '"><title>Redirigiendo…</title></head><body>Redirigiendo al catálogo…</body></html>';
exit;
