<?php
// Admin diagnostic: DB and tables presence
// No auth to ensure early failures are visible
header('Content-Type: text/html; charset=utf-8');

$messages = [];
function log_msg($m){
  echo '<script>console.log(' . json_encode($m, JSON_UNESCAPED_UNICODE) . ');</script>';
}

try {
  require_once __DIR__ . '/../config.php';
  require_once __DIR__ . '/../includes/db-functions.php';
  $messages[] = '✅ [Diag] Config y helpers cargados';
} catch (Throwable $e) {
  $messages[] = '❌ [Diag] Error cargando config/helpers: ' . $e->getMessage();
}

$db_ok = false; $db_error = '';
try {
  if (isset($pdo) && $pdo instanceof PDO){
    $pdo->query('SELECT 1');
    $db_ok = true;
    $messages[] = '✅ [Diag] DB ping OK';
  } else {
    $db_error = '$pdo no está definido';
    $messages[] = '❌ [Diag] ' . $db_error;
  }
} catch (Throwable $e) {
  $db_error = $e->getMessage();
  $messages[] = '❌ [Diag] DB ping failed: ' . $db_error;
}

function table_exists(PDO $pdo, $table){
  try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    if (!$stmt) return false;
    $stmt->execute([$table]);
    return ((int)$stmt->fetchColumn()) > 0;
  } catch (Throwable $e) {
    return false;
  }
}

$tables = ['proyectos','materiales','contratos','entregas','lotes_kits','ia_logs'];
$presence = [];
if ($db_ok){
  foreach ($tables as $t){
    $presence[$t] = table_exists($pdo, $t);
    log_msg('🔍 [Diag] Tabla ' . $t . ': ' . ($presence[$t] ? 'OK' : 'FALTA'));
  }
}

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Diagnóstico Admin - Clase de Ciencia</title>
  <style>
    body { font-family: system-ui, -apple-system, 'Segoe UI', Arial; padding: 2rem; }
    h1 { margin-bottom: 1rem; }
    table { border-collapse: collapse; width: 100%; max-width: 700px; }
    th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    th { background: #f5f5f5; }
    .ok { color: #2e7d32; font-weight: 600; }
    .bad { color: #c62828; font-weight: 600; }
    .msg { margin: 0.25rem 0; }
  </style>
</head>
<body>
  <h1>Diagnóstico del Admin</h1>
  <p>Abre la consola (F12) para ver detalles. Se registran mensajes con emojis.</p>
  <h2>Estado de Conexión</h2>
  <p class="msg"><strong>DB Ping:</strong> <?php echo $db_ok ? '<span class="ok">OK</span>' : '<span class="bad">FALLÓ</span>'; ?></p>
  <?php if (!$db_ok): ?>
    <p class="msg"><strong>Error:</strong> <?php echo htmlspecialchars($db_error, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>
  <h2>Tablas requeridas</h2>
  <table>
    <thead>
      <tr><th>Tabla</th><th>Presente</th></tr>
    </thead>
    <tbody>
      <?php foreach ($tables as $t): $ok = isset($presence[$t]) ? $presence[$t] : false; ?>
        <tr>
          <td><?php echo htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo $ok ? '<span class="ok">Sí</span>' : '<span class="bad">No</span>'; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <h2>Mensajes</h2>
  <?php foreach ($messages as $m): ?>
    <div class="msg"><?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endforeach; ?>
</body>
</html>
