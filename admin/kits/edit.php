<?php
require_once '../auth.php';
/** @var \PDO $pdo */

$is_edit = isset($_GET['id']) && ctype_digit($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : null;

$page_title = $is_edit ? 'Editar Kit' : 'Nuevo Kit';

if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$kit = [
  'clase_id' => '',
  'nombre' => '',
  'codigo' => '',
  'version' => '1',
  'activo' => 1,
];

try {
  // Clases para el selector
  $clases_stmt = $pdo->query('SELECT id, nombre, ciclo FROM clases ORDER BY nombre ASC');
  $clases = $clases_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $clases = [];
}

if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT id, clase_id, nombre, codigo, version, activo FROM kits WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { $kit = $row; } else { $is_edit = false; $id = null; }
  } catch (PDOException $e) {}
}

$error_msg = '';
$action_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [KitsEdit] CSRF inválido");</script>';
  } else {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';

    if ($action === 'save') {
      $clase_id = isset($_POST['clase_id']) && ctype_digit($_POST['clase_id']) ? (int)$_POST['clase_id'] : 0;
      $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
      $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
      $version = isset($_POST['version']) ? trim($_POST['version']) : '1';
      $activo = isset($_POST['activo']) ? 1 : 0;

      if ($clase_id <= 0 || $nombre === '' || $codigo === '') {
        $error_msg = 'Completa clase, nombre y código válidos.';
      } else {
        try {
          // Enforce unique code
          if ($is_edit) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE codigo = ? AND id <> ?');
            $check->execute([$codigo, $id]);
          } else {
            $check = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE codigo = ?');
            $check->execute([$codigo]);
          }
          $exists = (int)$check->fetchColumn();
          if ($exists > 0) {
            $error_msg = 'El código de kit ya existe. Elige otro.';
          } else {
            if ($is_edit) {
              $stmt = $pdo->prepare('UPDATE kits SET clase_id=?, nombre=?, codigo=?, version=?, activo=?, updated_at=NOW() WHERE id=?');
              $stmt->execute([$clase_id, $nombre, $codigo, $version, $activo, $id]);
            } else {
              $stmt = $pdo->prepare('INSERT INTO kits (clase_id, nombre, codigo, version, activo, updated_at) VALUES (?,?,?,?,?,NOW())');
              $stmt->execute([$clase_id, $nombre, $codigo, $version, $activo]);
              $id = (int)$pdo->lastInsertId();
              $is_edit = true;
            }
            header('Location: /admin/kits/index.php');
            exit;
          }
        } catch (PDOException $e) {
          $error_msg = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    } else if ($action === 'add_item' && $is_edit) {
      $item_id = isset($_POST['item_id']) && ctype_digit($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
      $cantidad = isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) ? (float)$_POST['cantidad'] : 0;
      $orden = isset($_POST['orden']) && ctype_digit($_POST['orden']) ? (int)$_POST['orden'] : 0;
      if ($item_id <= 0 || $cantidad <= 0) {
        $error_msg = 'Selecciona un componente y cantidad válida.';
      } else {
        try {
          // Ajuste al schema: usar sort_order en vez de orden
          $stmt = $pdo->prepare('INSERT INTO kit_componentes (kit_id, item_id, cantidad, sort_order) VALUES (?,?,?,?)');
          $stmt->execute([$id, $item_id, $cantidad, $orden]);
          $action_msg = 'Componente agregado.';
        } catch (PDOException $e) {
          $error_msg = 'Error al agregar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    } else if ($action === 'delete_item' && $is_edit) {
      // El schema no tiene columna id en kit_componentes; borrar por (kit_id, item_id)
      $kc_item_id = isset($_POST['kc_item_id']) && ctype_digit($_POST['kc_item_id']) ? (int)$_POST['kc_item_id'] : 0;
      if ($kc_item_id <= 0) {
        $error_msg = 'Componente inválido.';
      } else {
        try {
          $stmt = $pdo->prepare('DELETE FROM kit_componentes WHERE kit_id = ? AND item_id = ?');
          $stmt->execute([$id, $kc_item_id]);
          $action_msg = 'Componente eliminado.';
        } catch (PDOException $e) {
          $error_msg = 'Error al eliminar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    }
  }
}

// Cargar lista de componentes del kit
$componentes = [];
if ($is_edit) {
  try {
    // Ajuste al schema: no hay kc.id ni kc.orden; usar sort_order como orden
    $stmt = $pdo->prepare('SELECT kc.item_id, kc.cantidad, kc.sort_order AS orden, ki.nombre_comun, ki.sku, ki.unidad FROM kit_componentes kc JOIN kit_items ki ON ki.id = kc.item_id WHERE kc.kit_id = ? ORDER BY kc.sort_order ASC, ki.nombre_comun ASC');
    $stmt->execute([$id]);
    $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {}
}

// Lista de kit_items para agregar
try {
  $items_stmt = $pdo->query('SELECT id, nombre_comun, sku, unidad FROM kit_items ORDER BY nombre_comun ASC');
  $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $items = [];
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
  <span class="help-text">Completa los campos del kit y gestiona sus componentes.</span>
  <script>
    console.log('✅ [Admin] Kits edit cargado');
    console.log('🔍 [Admin] Edit mode:', <?= $is_edit ? 'true' : 'false' ?>);
    console.log('🔍 [Admin] Kit ID:', <?= $is_edit ? (int)$id : 'null' ?>);
  </script>
</div>

<?php if ($error_msg !== ''): ?>
  <div class="message error"><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($action_msg !== ''): ?>
  <div class="message success"><?= htmlspecialchars($action_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <input type="hidden" name="action" value="save" />
  <div class="form-group">
    <label for="clase_id">Clase</label>
    <select id="clase_id" name="clase_id" required>
      <option value="">Selecciona</option>
      <?php foreach ($clases as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ($kit['clase_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?> (Ciclo <?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label for="nombre">Nombre del Kit</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') ?>" required />
  </div>
  <div class="form-group">
    <label for="codigo">Código</label>
    <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($kit['codigo'], ENT_QUOTES, 'UTF-8') ?>" placeholder="p.ej. KIT-PLANTA-LUZ-01" required />
    <small>Debe ser único.</small>
  </div>
  <div class="form-group">
    <label for="version">Versión</label>
    <input type="text" id="version" name="version" value="<?= htmlspecialchars($kit['version'], ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="activo" <?= ((int)$kit['activo']) ? 'checked' : '' ?> /> Activo</label>
  </div>
  <div class="actions" style="margin-top:1rem;">
    <button type="submit" class="btn">Guardar</button>
    <a href="/admin/kits/index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php if ($is_edit): ?>
<div class="card" style="margin-top:2rem;">
  <h3>Componentes del Kit</h3>
  <?php if (empty($componentes)): ?>
    <p class="help-text">No hay componentes agregados a este kit.</p>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Item ID</th>
          <th>Componente</th>
          <th>SKU</th>
          <th>Cantidad</th>
          <th>Unidad</th>
          <th>Orden</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($componentes as $kc): ?>
        <tr>
          <td><?= (int)$kc['item_id'] ?></td>
          <td><?= htmlspecialchars($kc['nombre_comun'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><code><?= htmlspecialchars($kc['sku'], ENT_QUOTES, 'UTF-8') ?></code></td>
          <td><?= htmlspecialchars($kc['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars(($kc['unidad'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($kc['orden'], ENT_QUOTES, 'UTF-8') ?></td>
          <td class="actions">
            <form method="POST" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete_item" />
              <input type="hidden" name="kc_item_id" value="<?= (int)$kc['item_id'] ?>" />
              <button type="submit" class="btn btn-danger action-btn" onclick="return confirm('¿Eliminar componente del kit?')">Eliminar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <form method="POST" style="margin-top:1rem;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="add_item" />
    <div class="form-group">
      <label for="item_id">Agregar componente</label>
      <select id="item_id" name="item_id" required>
        <option value="">Selecciona componente</option>
        <?php foreach ($items as $it): ?>
          <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="cantidad">Cantidad</label>
      <input type="number" step="0.01" id="cantidad" name="cantidad" value="1" required />
    </div>
    <div class="form-group">
      <label for="orden">Orden</label>
      <input type="number" id="orden" name="orden" value="0" />
    </div>
    <div class="actions">
      <button type="submit" class="btn">Agregar</button>
    </div>
  </form>
</div>
<?php endif; ?>

<script>
  console.log('🔍 [KitsEdit] Clases cargadas:', <?= count($clases) ?>);
  console.log('🔍 [KitsEdit] Items disponibles:', <?= count($items) ?>);
</script>
<?php include '../footer.php'; ?>
