<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id && $pdo) {
  try {
    $pdo->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id]);
  } catch (PDOException $e) {
    error_log($e->getMessage());
  }
}
header('Location: index.php');
exit;