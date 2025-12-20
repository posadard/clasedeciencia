<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Admin - Login';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pass = $_POST['password'] ?? '';
  // TODO: Cambiar por autenticación segura (config/DB). Solo para pruebas.
  if ($pass === 'admin123') {
    $_SESSION['admin'] = true;
    header('Location: /admin/dashboard.php'); exit;
  } else { $err = 'Contraseña incorrecta'; }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= h($page_title) ?></title><link rel="stylesheet" href="/assets/css/main.css"></head>
<body>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <?php if ($err): ?><p class="error"><?= h($err) ?></p><?php endif; ?>
  <form method="POST">
    <input type="password" name="password" placeholder="Contraseña" required />
    <button class="btn-primary" type="submit">Entrar</button>
  </form>
  <p>Nota: Esto es temporal para pruebas; se añadirá autenticación segura.</p>
</main>
</body></html>