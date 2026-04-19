<?php
require_once '../auth.php';
$page_title = 'Editar Lote';

function admin_audit(PDO $pdo, string $modulo, string $entidad, int $entidad_id, string $accion, array $detalle = []): void {
  try {
    $stmt = $pdo->prepare("INSERT INTO auditoria_admin (modulo, entidad, entidad_id, accion, usuario, detalle_json, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $modulo,
      $entidad,
      $entidad_id,
      $accion,
      (string)($_SESSION['admin_username'] ?? 'admin'),
      json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      (string)($_SERVER['REMOTE_ADDR'] ?? ''),
      mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)
    ]);
  } catch (Exception $e) {
    error_log('Admin audit lotes: ' . $e->getMessage());
  }
}

if (!isset($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  } catch (Exception $e) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
  }
}

$estados_validos = ['activo', 'bloqueado', 'agotado', 'cerrado'];
$edit_id = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : 0;
$flash_ok = isset($_GET['saved']) ? 'Lote guardado correctamente.' : '';
$flash_error = '';

$kits = [];
$contratos = [];
try {
  $kits = $pdo->query("SELECT id, nombre, codigo FROM kits ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
  $contratos = $pdo->query("SELECT id, numero FROM contratos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $kits = [];
  $contratos = [];
}

$lote_edit = [
  'id' => 0,
  'codigo_lote' => '',
  'kit_id' => '',
  'contrato_id' => '',
  'cantidad_total' => 0,
  'cantidad_disponible' => 0,
  'cantidad_asignada' => 0,
  'cantidad_entregada' => 0,
  'fecha_fabricacion' => '',
  'fecha_caducidad' => '',
  'estado_lote' => 'activo',
  'ubicacion' => '',
  'observaciones' => ''
];

if ($edit_id > 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM lotes WHERE id = ? LIMIT 1");
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $lote_edit = array_merge($lote_edit, $row);
    } else {
      $flash_error = 'No se encontró el lote solicitado.';
      $edit_id = 0;
    }
  } catch (PDOException $e) {
    $flash_error = 'No se pudo cargar el lote para edición.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [LotesEdit] CSRF invalido");</script>';
  } else {
    $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
    $codigo_lote = trim($_POST['codigo_lote'] ?? '');
    $kit_id = isset($_POST['kit_id']) && ctype_digit((string)$_POST['kit_id']) ? (int)$_POST['kit_id'] : 0;
    $contrato_id = isset($_POST['contrato_id']) && ctype_digit((string)$_POST['contrato_id']) ? (int)$_POST['contrato_id'] : null;
    $cantidad_total = max(0, (int)($_POST['cantidad_total'] ?? 0));
    $cantidad_disponible = max(0, (int)($_POST['cantidad_disponible'] ?? 0));
    $cantidad_asignada = max(0, (int)($_POST['cantidad_asignada'] ?? 0));
    $cantidad_entregada = max(0, (int)($_POST['cantidad_entregada'] ?? 0));
    $fecha_fabricacion = trim($_POST['fecha_fabricacion'] ?? '');
    $fecha_caducidad = trim($_POST['fecha_caducidad'] ?? '');
    $estado_lote = trim($_POST['estado_lote'] ?? 'activo');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    $lote_edit = array_merge($lote_edit, [
      'id' => $id,
      'codigo_lote' => $codigo_lote,
      'kit_id' => $kit_id,
      'contrato_id' => $contrato_id,
      'cantidad_total' => $cantidad_total,
      'cantidad_disponible' => $cantidad_disponible,
      'cantidad_asignada' => $cantidad_asignada,
      'cantidad_entregada' => $cantidad_entregada,
      'fecha_fabricacion' => $fecha_fabricacion,
      'fecha_caducidad' => $fecha_caducidad,
      'estado_lote' => $estado_lote,
      'ubicacion' => $ubicacion,
      'observaciones' => $observaciones
    ]);

    if ($codigo_lote === '' || $kit_id <= 0) {
      $flash_error = 'Codigo de lote y kit son obligatorios.';
    } elseif (!in_array($estado_lote, $estados_validos, true)) {
      $flash_error = 'Estado de lote invalido.';
    } elseif (($cantidad_disponible + $cantidad_asignada + $cantidad_entregada) > $cantidad_total && $cantidad_total > 0) {
      $flash_error = 'La suma disponible + asignada + entregada no puede superar la cantidad total.';
    } else {
      try {
        if ($id > 0) {
          $sql = "UPDATE lotes
                  SET codigo_lote = ?, kit_id = ?, contrato_id = ?,
                      cantidad_total = ?, cantidad_disponible = ?, cantidad_asignada = ?, cantidad_entregada = ?,
                      fecha_fabricacion = NULLIF(?, ''), fecha_caducidad = NULLIF(?, ''),
                      estado_lote = ?, ubicacion = ?, observaciones = ?
                  WHERE id = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([
            $codigo_lote, $kit_id, $contrato_id,
            $cantidad_total, $cantidad_disponible, $cantidad_asignada, $cantidad_entregada,
            $fecha_fabricacion, $fecha_caducidad,
            $estado_lote, $ubicacion, $observaciones,
            $id
          ]);
          admin_audit($pdo, 'lotes', 'lote', $id, 'editar', [
            'codigo_lote' => $codigo_lote,
            'estado' => $estado_lote,
            'cantidad_total' => $cantidad_total,
            'cantidad_disponible' => $cantidad_disponible
          ]);
          header('Location: /admin/lotes/edit.php?id=' . (int)$id . '&saved=1');
          exit;
        }

        $sql = "INSERT INTO lotes
                (codigo_lote, kit_id, contrato_id, cantidad_total, cantidad_disponible, cantidad_asignada, cantidad_entregada,
                 fecha_fabricacion, fecha_caducidad, estado_lote, ubicacion, observaciones)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          $codigo_lote, $kit_id, $contrato_id,
          $cantidad_total, $cantidad_disponible, $cantidad_asignada, $cantidad_entregada,
          $fecha_fabricacion, $fecha_caducidad,
          $estado_lote, $ubicacion, $observaciones
        ]);
        $new_id = (int)$pdo->lastInsertId();
        admin_audit($pdo, 'lotes', 'lote', $new_id, 'crear', [
          'codigo_lote' => $codigo_lote,
          'estado' => $estado_lote,
          'cantidad_total' => $cantidad_total
        ]);
        header('Location: /admin/lotes/edit.php?id=' . $new_id . '&saved=1');
        exit;
      } catch (PDOException $e) {
        $flash_error = 'Error al guardar lote. Verifica codigo unico y relaciones.';
        echo '<script>console.log("❌ [LotesEdit] Error al guardar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
      }
    }
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= $edit_id > 0 ? 'Editar lote' : 'Nuevo lote' ?></h2>
  <span class="help-text">Formulario de creación y edición de lotes.</span>
  <script>
    console.log('✅ [Admin] Lotes edit cargado');
    console.log('🔍 [Admin] Lote ID:', <?= (int)$edit_id ?>);
  </script>
</div>

<?php if ($flash_ok): ?>
  <div class="message success"><?= htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
  <div class="message error"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
  <form method="POST" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="id" value="<?= (int)$lote_edit['id'] ?>" />

    <div class="form-group">
      <label>Codigo lote *</label>
      <input type="text" maxlength="64" required name="codigo_lote" value="<?= htmlspecialchars((string)$lote_edit['codigo_lote'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Kit *</label>
      <select name="kit_id" required>
        <option value="">Seleccionar...</option>
        <?php foreach ($kits as $k): ?>
          <option value="<?= (int)$k['id'] ?>" <?= (int)$lote_edit['kit_id'] === (int)$k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$k['nombre'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Contrato (opcional)</label>
      <select name="contrato_id">
        <option value="">Sin contrato</option>
        <?php foreach ($contratos as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$lote_edit['contrato_id'] === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$c['numero'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Estado</label>
      <select name="estado_lote">
        <?php foreach ($estados_validos as $ev): ?>
          <option value="<?= htmlspecialchars($ev, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)$lote_edit['estado_lote'] === $ev) ? 'selected' : '' ?>><?= strtoupper(htmlspecialchars($ev, ENT_QUOTES, 'UTF-8')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Cantidad total</label>
      <input type="number" min="0" name="cantidad_total" value="<?= (int)$lote_edit['cantidad_total'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad disponible</label>
      <input type="number" min="0" name="cantidad_disponible" value="<?= (int)$lote_edit['cantidad_disponible'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad asignada</label>
      <input type="number" min="0" name="cantidad_asignada" value="<?= (int)$lote_edit['cantidad_asignada'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad entregada</label>
      <input type="number" min="0" name="cantidad_entregada" value="<?= (int)$lote_edit['cantidad_entregada'] ?>" />
    </div>

    <div class="form-group">
      <label>Fecha fabricación</label>
      <input type="date" name="fecha_fabricacion" value="<?= htmlspecialchars((string)$lote_edit['fecha_fabricacion'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha caducidad</label>
      <input type="date" name="fecha_caducidad" value="<?= htmlspecialchars((string)$lote_edit['fecha_caducidad'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group full-width">
      <label>Ubicación</label>
      <input type="text" maxlength="180" name="ubicacion" value="<?= htmlspecialchars((string)$lote_edit['ubicacion'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group full-width">
      <label>Observaciones</label>
      <textarea name="observaciones" rows="3"><?= htmlspecialchars((string)$lote_edit['observaciones'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="form-actions full-width">
      <button type="submit" class="btn"><?= $edit_id > 0 ? 'Guardar cambios' : 'Crear lote' ?></button>
      <a href="/admin/lotes/index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
  </form>
</div>

<style>
.admin-form-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group input, .form-group select, .form-group textarea { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.full-width { grid-column: 1 / -1; }
.form-actions { display: flex; gap: 0.5rem; }

@media (max-width: 1000px) {
  .admin-form-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
}
@media (max-width: 700px) {
  .admin-form-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>