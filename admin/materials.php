<?php
// Legacy admin/materials.php -> Redirect to new CdC module
require_once 'auth.php';
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /admin/materiales/index.php' . $qs);
exit;
