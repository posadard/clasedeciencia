<?php
// Legacy TGA article page no longer compatible.
// Redirect to catálogo principal.
require_once 'config.php';
$redir = '/clases';
header('Location: ' . $redir, true, 302);
echo '<!doctype html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redir, ENT_QUOTES, 'UTF-8') . '"><title>Redirigiendo…</title></head><body>Redirigiendo al catálogo…</body></html>';
exit;
