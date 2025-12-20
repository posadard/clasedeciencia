<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id && $pdo) {
  try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM guias WHERE proyecto_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM proyectos WHERE id = ?")->execute([$id]);
    $pdo->commit();
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $pdo->rollBack();
  }
}
header('Location: index.php');
exit;