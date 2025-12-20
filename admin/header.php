<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($page_title ?? 'Admin') ?></title>
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="brand" href="/admin/dashboard.php">Admin</a>
    <nav class="nav">
      <a href="/admin/proyectos/index.php">Proyectos</a>
      <a href="/admin/materiales/index.php">Materiales</a>
    </nav>
  </div>
</header>
<div class="container">