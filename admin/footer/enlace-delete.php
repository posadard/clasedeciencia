<?php
require_once '../auth.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /admin/footer/index.php');
    exit;
}

try {
    $pdo->prepare('DELETE FROM footer_enlaces WHERE id = ?')->execute([$id]);
    header('Location: /admin/footer/index.php?msg=' . urlencode('🗑 Enlace eliminado.') . '&type=ok');
} catch (PDOException $e) {
    error_log('Admin footer/enlace-delete: ' . $e->getMessage());
    header('Location: /admin/footer/index.php?msg=' . urlencode('❌ Error al eliminar.') . '&type=error');
}
exit;
