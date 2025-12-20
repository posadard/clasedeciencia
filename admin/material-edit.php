<?php
// Legacy admin/material-edit.php -> Redirect to new CdC materials editor
require_once 'auth.php';
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /admin/materiales/edit.php' . $qs);
exit;
