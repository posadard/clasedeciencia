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

// Relaciones existentes: clases asignadas a este kit (via clase_kits)
$existing_clase_ids = [];
if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT clase_id FROM clase_kits WHERE kit_id = ? ORDER BY es_principal DESC, sort_order ASC');
    $stmt->execute([$id]);
    $existing_clase_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($existing_clase_ids) && !empty($kit['clase_id'])) {
      $existing_clase_ids = [(int)$kit['clase_id']];
    }
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
      // Clases seleccionadas (transfer list)
      $clases_sel = isset($_POST['clases']) && is_array($_POST['clases']) ? array_map('intval', $_POST['clases']) : [];
      $principal_clase_id = !empty($clases_sel) ? (int)$clases_sel[0] : 0;
      // Mantener compatibilidad si vienen datos legacy de clase_id
      $legacy_clase_id = isset($_POST['clase_id']) && ctype_digit($_POST['clase_id']) ? (int)$_POST['clase_id'] : 0;
      if ($principal_clase_id === 0 && $legacy_clase_id > 0) { $principal_clase_id = $legacy_clase_id; }
      $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
      $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
      $version = isset($_POST['version']) ? trim($_POST['version']) : '1';
      $activo = isset($_POST['activo']) ? 1 : 0;

      if ($principal_clase_id <= 0 || $nombre === '' || $codigo === '') {
        $error_msg = 'Selecciona al menos una clase, y completa nombre y código.';
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
            $pdo->beginTransaction();
            if ($is_edit) {
              $stmt = $pdo->prepare('UPDATE kits SET clase_id=?, nombre=?, codigo=?, version=?, activo=?, updated_at=NOW() WHERE id=?');
              $stmt->execute([$principal_clase_id, $nombre, $codigo, $version, $activo, $id]);
            } else {
              $stmt = $pdo->prepare('INSERT INTO kits (clase_id, nombre, codigo, version, activo, updated_at) VALUES (?,?,?,?,?,NOW())');
              $stmt->execute([$principal_clase_id, $nombre, $codigo, $version, $activo]);
              $id = (int)$pdo->lastInsertId();
              $is_edit = true;
            }

            // Actualizar relaciones en clase_kits
            try {
              $pdo->prepare('DELETE FROM clase_kits WHERE kit_id = ?')->execute([$id]);
              if (!empty($clases_sel)) {
                $ins = $pdo->prepare('INSERT INTO clase_kits (clase_id, kit_id, sort_order, es_principal) VALUES (?,?,?,?)');
                $sort = 1;
                foreach ($clases_sel as $cid) {
                  $es_principal = ($sort === 1) ? 1 : 0;
                  $ins->execute([(int)$cid, $id, $sort++, $es_principal]);
                }
              } else if ($principal_clase_id > 0) {
                // Fallback: al menos principal
                $pdo->prepare('INSERT INTO clase_kits (clase_id, kit_id, sort_order, es_principal) VALUES (?,?,?,1)')
                    ->execute([$principal_clase_id, $id, 1]);
              }
              $pdo->commit();
              echo '<script>console.log("✅ [KitsEdit] Kit y relaciones clase_kits guardados");</script>';
            } catch (PDOException $e) {
              if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
              throw $e;
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
      $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
      $orden = isset($_POST['orden']) && ctype_digit($_POST['orden']) ? (int)$_POST['orden'] : 0;
      if ($item_id <= 0 || $cantidad <= 0) {
        $error_msg = 'Selecciona un componente y cantidad válida.';
      } else {
        try {
          // Ajuste al schema: usar sort_order en vez de orden
          if ($notas !== '') { $notas = mb_substr($notas, 0, 255, 'UTF-8'); } else { $notas = null; }
          $stmt = $pdo->prepare('INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order) VALUES (?,?,?,?,?,?)');
          $stmt->execute([$id, $item_id, $cantidad, 1, $notas, $orden]);
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
    } else if ($action === 'update_item' && $is_edit) {
      // Actualizar cantidad, notas y orden (sort_order) para un item existente
      $kc_item_id = isset($_POST['kc_item_id']) && ctype_digit($_POST['kc_item_id']) ? (int)$_POST['kc_item_id'] : 0;
      $cantidad = isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) ? (float)$_POST['cantidad'] : 0;
      $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
      $orden = isset($_POST['orden']) && is_numeric($_POST['orden']) ? (int)$_POST['orden'] : 0;
      if ($kc_item_id <= 0 || $cantidad <= 0) {
        $error_msg = 'Selecciona un componente válido y cantidad positiva.';
        echo '<script>console.log("❌ [KitsEdit] update_item inválido");</script>';
      } else {
        try {
          if ($notas !== '') { $notas = mb_substr($notas, 0, 255, 'UTF-8'); } else { $notas = null; }
          $stmt = $pdo->prepare('UPDATE kit_componentes SET cantidad = ?, notas = ?, sort_order = ? WHERE kit_id = ? AND item_id = ?');
          $stmt->execute([$cantidad, $notas, $orden, $id, $kc_item_id]);
          $action_msg = 'Componente actualizado.';
          echo '<script>console.log("✅ [KitsEdit] update_item guardado");</script>';
        } catch (PDOException $e) {
          $error_msg = 'Error al actualizar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
          echo '<script>console.log("❌ [KitsEdit] update_item error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
        }
      }
    }
  }
}

// Cargar lista de componentes del kit
$componentes = [];
if ($is_edit) {
  try {
    // Ajuste al schema: no hay kc.id ni kc.orden; usar sort_order como orden, incluir notas
    $stmt = $pdo->prepare('SELECT kc.item_id, kc.cantidad, kc.sort_order AS orden, kc.notas, ki.nombre_comun, ki.sku, ki.unidad FROM kit_componentes kc JOIN kit_items ki ON ki.id = kc.item_id WHERE kc.kit_id = ? ORDER BY kc.sort_order ASC, ki.nombre_comun ASC');
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

<form method="POST" id="kit-form">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <input type="hidden" name="action" value="save" />
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

  <!-- estilos de chips y autocompletado se mueven a assets/css/style.css -->

  <div class="form-group">
    <label for="component_search">Buscar Componentes</label>
    <div class="component-selector-container">
      <div class="selected-components" id="selected-components">
        <?php if (!empty($componentes)): foreach ($componentes as $kc): ?>
          <div class="component-chip" data-item-id="<?= (int)$kc['item_id'] ?>" data-orden="<?= (int)$kc['orden'] ?>">
            <span class="name"><?= htmlspecialchars($kc['nombre_comun'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="meta">· <strong><?= htmlspecialchars($kc['cantidad'], ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(($kc['unidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" class="edit-component js-edit-item" title="Editar"
              data-item-id="<?= (int)$kc['item_id'] ?>"
              data-cantidad="<?= htmlspecialchars($kc['cantidad'], ENT_QUOTES, 'UTF-8') ?>"
              data-notas="<?= htmlspecialchars(($kc['notas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              data-orden="<?= htmlspecialchars($kc['orden'], ENT_QUOTES, 'UTF-8') ?>"
              data-nombre="<?= htmlspecialchars($kc['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>"
              data-sku="<?= htmlspecialchars($kc['sku'], ENT_QUOTES, 'UTF-8') ?>"
              data-unidad="<?= htmlspecialchars(($kc['unidad'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>"
            >✏️</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar componente del kit?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete_item" />
              <input type="hidden" name="kc_item_id" value="<?= (int)$kc['item_id'] ?>" />
              <button type="submit" class="remove-component" title="Remover">×</button>
            </form>
          </div>
        <?php endforeach; endif; ?>
      </div>
      <input type="text" id="component_search" placeholder="Escribir para buscar componente..." autocomplete="off" />
      <datalist id="components_list">
        <?php foreach ($items as $it): ?>
          <option value="<?= (int)$it['id'] ?>" data-name="<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)
          </option>
        <?php endforeach; ?>
      </datalist>
      <div class="autocomplete-dropdown" id="cmp_autocomplete_dropdown"></div>
    </div>
    <small>Escribe para buscar componentes. Al seleccionar, completa cantidad y orden en el modal.</small>
  </div>
</div>
<?php endif; ?>

<script>
  console.log('🔍 [KitsEdit] Clases cargadas:', <?= count($clases) ?>);
  console.log('🔍 [KitsEdit] Items disponibles:', <?= count($items) ?>);
</script>
<!-- Clases vinculadas al Kit (Transfer List) -->
<div class="card" style="margin-top:2rem;">
  <h3>Clases vinculadas al Kit</h3>
  <small class="hint" style="display:block; margin-bottom:6px;">Selecciona una o varias clases. La primera será la principal.</small>
  <div class="dual-listbox-container">
    <div class="listbox-panel">
      <div class="listbox-header">
        <strong>Disponibles</strong>
        <span id="clases-available-count" class="counter">(<?= count($clases) ?>)</span>
      </div>
      <input type="text" id="search-clases" class="listbox-search" placeholder="🔍 Buscar clases...">
      <div class="listbox-content" id="available-clases">
        <?php foreach ($clases as $c): 
          $isSelected = in_array($c['id'], $existing_clase_ids);
        ?>
          <div class="competencia-item <?= $isSelected ? 'hidden' : '' ?>" 
               data-id="<?= (int)$c['id'] ?>"
               data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
               data-ciclo="<?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?>"
               onclick="selectClaseItem(this)">
            <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="comp-codigo">Ciclo <?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="listbox-buttons">
      <button type="button" onclick="moveAllClases(true)" title="Agregar todas">➡️</button>
      <button type="button" onclick="moveAllClases(false)" title="Quitar todas">⬅️</button>
    </div>
    <div class="listbox-panel">
      <div class="listbox-header">
        <strong>Seleccionadas</strong>
        <span id="clases-selected-count" class="counter">(<?= count($existing_clase_ids) ?>)</span>
      </div>
      <div class="listbox-content" id="selected-clases">
        <?php foreach ($clases as $c): if (in_array($c['id'], $existing_clase_ids)): ?>
          <div class="competencia-item selected" 
               data-id="<?= (int)$c['id'] ?>"
               data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
               data-ciclo="<?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?>"
               onclick="deselectClaseItem(this)">
            <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="comp-codigo">Ciclo <?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectClaseItem(this.parentElement)">×</button>
          </div>
        <?php endif; endforeach; ?>
      </div>
      <small class="hint" style="margin-top: 10px; display: block;">Haz clic para quitar. Orden superior define el principal.</small>
    </div>
    <!-- Hidden inputs (outside form) must target kit-form -->
    <div id="clases-hidden"></div>
  </div>
</div>
<?php if ($is_edit): ?>
<!-- Modales para editar y agregar componentes -->
<style>
  .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
  /* Mostrar el modal cuando el backdrop tiene la clase show */
  .modal-backdrop.show { display: flex; }
  .modal { background: #fff; border-radius: 8px; max-width: 520px; width: 95%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
  .modal-header { padding: 12px 16px; border-bottom: 1px solid #eee; display:flex; align-items:center; justify-content: space-between; }
  .modal-body { padding: 16px; }
  .modal-footer { padding: 12px 16px; border-top: 1px solid #eee; display:flex; gap: 8px; justify-content: flex-end; }
  .modal .form-group { margin-bottom: 12px; }
  .btn-plain { background: transparent; border: none; font-size: 18px; cursor: pointer; }
  .muted { color: #666; font-size: 0.9rem; }
  .field-inline { display:flex; gap:12px; }
  .field-inline > div { flex:1; }
  /* Combo box styles */
  .combo { position: relative; }
  .combo-input { width: 100%; padding: 8px 32px 8px 10px; border: 1px solid #ccc; border-radius: 4px; }
  .combo-toggle { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: #f4f4f4; border: 1px solid #ccc; border-radius: 4px; width: 24px; height: 24px; display:flex; align-items:center; justify-content:center; cursor: pointer; }
  .combo-list { position: absolute; z-index: 10; left: 0; right: 0; top: calc(100% + 4px); max-height: 220px; overflow: auto; border: 1px solid #ccc; border-radius: 4px; background: #fff; display: none; }
  .combo.open .combo-list { display: block; }
  .combo-item { padding: 8px 10px; cursor: pointer; display:flex; align-items:center; justify-content: space-between; }
  .combo-item:hover, .combo-item.active { background: #eef6ff; }
  .combo-sku { color: #666; font-size: 0.85rem; }
  /* Ocultar select original pero mantenerlo para submit/validación */
  #add_item_id { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
</style>

<!-- Modal Editar Componente -->
<div class="modal-backdrop" id="modalEditCmp">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
    <div class="modal-header">
      <h4 id="modalEditTitle">Editar componente</h4>
      <button type="button" class="btn-plain js-close-modal" data-target="#modalEditCmp">✖</button>
    </div>
    <form method="POST" id="formEditCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="update_item" />
      <input type="hidden" name="kc_item_id" id="edit_kc_item_id" />
      <div class="modal-body">
        <div class="muted" id="editCmpInfo"></div>
        <div class="field-inline">
          <div class="form-group">
            <label for="edit_cantidad">Cantidad</label>
            <input type="number" step="0.01" id="edit_cantidad" name="cantidad" required />
          </div>
          <div class="form-group">
            <label for="edit_orden">Orden</label>
            <input type="number" id="edit_orden" name="orden" />
          </div>
        </div>
        <div class="form-group">
          <label for="edit_notas">Notas</label>
          <input type="text" id="edit_notas" name="notas" maxlength="255" placeholder="p.ej. Indicaciones de uso" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditCmp">Cancelar</button>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Agregar Componente -->
<div class="modal-backdrop" id="modalAddCmp">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
    <div class="modal-header">
      <h4 id="modalAddTitle">Agregar componente</h4>
      <button type="button" class="btn-plain js-close-modal" data-target="#modalAddCmp">✖</button>
    </div>
    <form method="POST" id="formAddCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="add_item" />
      <div class="modal-body">
        <div class="form-group">
          <label for="combo_item_input">Componente</label>
          <div class="combo" id="combo_item">
            <input type="text" id="combo_item_input" class="combo-input" placeholder="Escribe para buscar y selecciona" autocomplete="off" />
            <button type="button" class="combo-toggle" aria-label="Abrir opciones" title="Abrir opciones">▾</button>
            <ul class="combo-list" id="combo_item_list">
              <?php foreach ($items as $it): ?>
                <li class="combo-item" data-value="<?= (int)$it['id'] ?>" data-name="<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>" data-sku="<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>">
                  <span><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="combo-sku">SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <!-- Select original permanece para envío/validación; se oculta por CSS -->
          <select id="add_item_id" name="item_id" required>
            <option value="">Selecciona componente</option>
            <?php foreach ($items as $it): ?>
              <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-inline">
          <div class="form-group">
            <label for="add_cantidad">Cantidad</label>
            <input type="number" step="0.01" id="add_cantidad" name="cantidad" value="1" required />
          </div>
          <div class="form-group">
            <label for="add_orden">Orden</label>
            <input type="number" id="add_orden" name="orden" value="0" />
          </div>
        </div>
        <div class="form-group">
          <label for="add_notas">Notas (opcional)</label>
          <input type="text" id="add_notas" name="notas" maxlength="255" placeholder="p.ej. Indicaciones de uso" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddCmp">Cancelar</button>
        <button type="submit" class="btn">Agregar</button>
      </div>
    </form>
  </div>
 </div>

<script>
  // Utilidades de modal
  function openModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.add('show'); console.log('🔍 [KitsEdit] Abre modal', sel); }
  }
  function closeModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.remove('show'); console.log('🔍 [KitsEdit] Cierra modal', sel); }
  }

  // Abrir modal de agregar
  const btnOpenAdd = document.querySelector('.js-open-add-modal');
  if (btnOpenAdd) {
    btnOpenAdd.addEventListener('click', () => openModal('#modalAddCmp'));
  }

  // Cerrar por botones con data-target
  document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const t = e.currentTarget.getAttribute('data-target');
      if (t) closeModal(t);
    });
  });

  // Cerrar al click en backdrop
  document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', (e) => { if (e.target === b) closeModal('#' + b.id); });
  });

  // Abrir modal de edición y prellenar
  document.querySelectorAll('.js-edit-item').forEach(btn => {
    btn.addEventListener('click', () => {
      const itemId = btn.getAttribute('data-item-id');
      const cantidad = btn.getAttribute('data-cantidad');
      const notas = btn.getAttribute('data-notas') || '';
      const orden = btn.getAttribute('data-orden') || '0';
      const nombre = btn.getAttribute('data-nombre') || '';
      const sku = btn.getAttribute('data-sku') || '';
      const unidad = btn.getAttribute('data-unidad') || '';

      document.getElementById('edit_kc_item_id').value = itemId;
      document.getElementById('edit_cantidad').value = cantidad;
      document.getElementById('edit_notas').value = notas;
      document.getElementById('edit_orden').value = orden;
      document.getElementById('editCmpInfo').textContent = `${nombre} (SKU ${sku}) · Unidad: ${unidad}`;

      console.log('🔍 [KitsEdit] Editar item', { itemId, cantidad, orden });
      openModal('#modalEditCmp');
    });
  });

  // Logs de envío de formularios
  const formEdit = document.getElementById('formEditCmp');
  if (formEdit) {
    formEdit.addEventListener('submit', () => console.log('📡 [KitsEdit] Enviando update_item...'));
  }
  const formAdd = document.getElementById('formAddCmp');
  if (formAdd) {
    formAdd.addEventListener('submit', () => console.log('📡 [KitsEdit] Enviando add_item...'));
  }

  
  // Combo Box para seleccionar componente (input + lista)
  (function initComboBox(){
    const combo = document.getElementById('combo_item');
    const input = document.getElementById('combo_item_input');
    const list = document.getElementById('combo_item_list');
    const selectEl = document.getElementById('add_item_id');
    if (!combo || !input || !list || !selectEl) { console.log('⚠️ [KitsEdit] ComboBox no inicializado'); return; }

    let items = Array.from(list.querySelectorAll('.combo-item')).map((li) => ({
      value: li.getAttribute('data-value'),
      name: li.getAttribute('data-name') || li.textContent.trim(),
      sku: li.getAttribute('data-sku') || '',
      text: li.textContent.trim()
    }));
    let activeIndex = -1;

    function normalize(str){
      return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    function renderList(matches){
      list.innerHTML = '';
      matches.forEach((m, idx) => {
        const li = document.createElement('li');
        li.className = 'combo-item' + (idx === 0 ? ' active' : '');
        li.dataset.value = m.value;
        li.dataset.name = m.name;
        li.dataset.sku = m.sku;
        li.innerHTML = `<span>${m.name}</span><span class="combo-sku">SKU ${m.sku}</span>`;
        li.addEventListener('click', () => selectItem(m));
        list.appendChild(li);
      });
      activeIndex = matches.length ? 0 : -1;
    }
    function open(){ combo.classList.add('open'); }
    function close(){ combo.classList.remove('open'); }
    function selectItem(item){
      input.value = `${item.name}`;
      selectEl.value = item.value;
      console.log('✅ [KitsEdit] Combo select', item);
      close();
    }
    function filter(q){
      const nq = normalize(q);
      const matches = nq ? items.filter(i => {
        return normalize(i.name).includes(nq) || normalize(i.sku).includes(nq) || normalize(i.text).includes(nq);
      }) : items.slice();
      renderList(matches);
      open();
      console.log('🔍 [KitsEdit] Combo filtro:', q, '→', matches.length);
    }

    // Eventos
    input.addEventListener('focus', () => { filter(input.value); });
    input.addEventListener('input', () => { filter(input.value); selectEl.value = ''; });
    combo.querySelector('.combo-toggle').addEventListener('click', () => {
      if (combo.classList.contains('open')) { close(); } else { filter(input.value); }
    });
    input.addEventListener('keydown', (e) => {
      const itemsEl = Array.from(list.querySelectorAll('.combo-item'));
      if (!itemsEl.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, itemsEl.length - 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); }
      else if (e.key === 'Enter') { e.preventDefault(); const li = itemsEl[activeIndex]; if (li) selectItem({ value: li.dataset.value, name: li.dataset.name, sku: li.dataset.sku, text: li.textContent.trim() }); }
      else if (e.key === 'Escape') { close(); }
      itemsEl.forEach((el, idx) => el.classList.toggle('active', idx === activeIndex));
    });

    // Reset al abrir modal
    const btnOpenAdd = document.querySelector('.js-open-add-modal');
    if (btnOpenAdd) {
      btnOpenAdd.addEventListener('click', () => { input.value = ''; selectEl.value = ''; renderList(items.slice()); open(); });
    }

    // Cerrar si clic fuera del combo
    document.addEventListener('click', (e) => {
      if (!combo.contains(e.target) && !e.target.closest('.js-open-add-modal')) close();
    });
  })();

  // Selector de componentes: búsqueda + autocompletado + abrir modal de agregar
  (function initComponentSearch(){
    const input = document.getElementById('component_search');
    const dropdown = document.getElementById('cmp_autocomplete_dropdown');
    const selectedWrap = document.getElementById('selected-components');
    const addSelect = document.getElementById('add_item_id');
    const comboInput = document.getElementById('combo_item_input');
    if (!input || !dropdown || !selectedWrap || !addSelect || !comboInput) { console.log('⚠️ [KitsEdit] Selector de componentes no inicializado'); return; }

    // Construir dataset de items disponibles
    const items = [
      <?php foreach ($items as $it): ?>
      { id: <?= (int)$it['id'] ?>, name: '<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>', sku: '<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>', unidad: '<?= htmlspecialchars($it['unidad'] ?? '', ENT_QUOTES, 'UTF-8') ?>' },
      <?php endforeach; ?>
    ];

    function normalize(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
    function selectedIds(){ return new Set(Array.from(selectedWrap.querySelectorAll('.component-chip')).map(el => parseInt(el.getAttribute('data-item-id'),10)).filter(Boolean)); }
    function nextOrden(){
      const ords = Array.from(selectedWrap.querySelectorAll('.component-chip')).map(el => parseInt(el.getAttribute('data-orden')||'0',10));
      const max = ords.length ? Math.max.apply(null, ords) : 0; return (isFinite(max) ? max : 0) + 1;
    }
    function render(list){
      if (!list.length){ dropdown.innerHTML = '<div class="autocomplete-item"><span class="cmp-code">Sin resultados</span></div>'; dropdown.style.display='block'; return; }
      dropdown.innerHTML = '';
      list.slice(0, 20).forEach(it => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML = `<strong>${it.name}</strong><span class="cmp-code">SKU ${it.sku}${it.unidad? ' · '+it.unidad:''}</span>`;
        div.addEventListener('click', () => onChoose(it));
        dropdown.appendChild(div);
      });
      dropdown.style.display = 'block';
    }
    function filter(q){
      const sel = selectedIds();
      const nq = normalize(q);
      const out = items.filter(it => !sel.has(it.id) && (nq ? (normalize(it.name).includes(nq) || normalize(it.sku).includes(nq)) : true));
      console.log('🔍 [KitsEdit] Buscar componente:', q, '→', out.length);
      render(out);
    }
    function onChoose(it){
      try {
        // Preseleccionar en modal de agregar
        addSelect.value = String(it.id);
        comboInput.value = it.name;
        // Sugerir siguiente orden
        const ordEl = document.getElementById('add_orden');
        if (ordEl) ordEl.value = nextOrden();
        const qtyEl = document.getElementById('add_cantidad');
        if (qtyEl && (!qtyEl.value || Number(qtyEl.value) <= 0)) qtyEl.value = 1;
        console.log('✅ [KitsEdit] Seleccionado para agregar:', it);
        openModal('#modalAddCmp');
        setTimeout(() => { try { document.getElementById('add_cantidad')?.focus(); } catch(_e){} }, 50);
      } catch (e) {
        console.log('❌ [KitsEdit] Error al preparar modal agregar:', e && e.message);
      }
      dropdown.style.display = 'none';
    }

    input.addEventListener('focus', () => filter(input.value));
    input.addEventListener('input', () => filter(input.value));

    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target) && e.target !== input) dropdown.style.display = 'none';
    });
  })();
</script>
<?php endif; ?>
<script>
  // Dual Listbox: Clases vinculadas al kit (siempre activo)
  (function initClasesTransfer(){
    const available = document.getElementById('available-clases');
    const selected = document.getElementById('selected-clases');
    const search = document.getElementById('search-clases');
    const hidden = document.getElementById('clases-hidden');
    const availableCount = document.getElementById('clases-available-count');
    const selectedCount = document.getElementById('clases-selected-count');
    if (!available || !selected || !hidden) { console.log('⚠️ [KitsEdit] Transfer de clases no inicializado'); return; }

    function updateHidden(){
      hidden.innerHTML = '';
      const ids = Array.from(selected.querySelectorAll('.competencia-item')).map(el => parseInt(el.dataset.id, 10)).filter(Boolean);
      ids.forEach((id, idx) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'clases[]';
        input.value = id;
        input.setAttribute('form', 'kit-form'); // ensure submit with main form
        hidden.appendChild(input);
      });
      if (selectedCount) selectedCount.textContent = '(' + ids.length + ')';
      const availVisible = Array.from(available.querySelectorAll('.competencia-item')).filter(el => !el.classList.contains('hidden') && el.style.display !== 'none').length;
      if (availableCount) availableCount.textContent = '(' + availVisible + ')';
      console.log('🔍 [KitsEdit] Clases seleccionadas:', ids);
    }

    window.selectClaseItem = function(el){
      el.classList.add('hidden');
      const id = el.dataset.id;
      const nombre = el.dataset.nombre;
      const ciclo = el.dataset.ciclo;
      const node = document.createElement('div');
      node.className = 'competencia-item selected';
      node.dataset.id = id;
      node.dataset.nombre = nombre;
      node.dataset.ciclo = ciclo;
      node.innerHTML = `<span class="comp-nombre">${nombre}</span><span class="comp-codigo">Ciclo ${ciclo}</span><button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectClaseItem(this.parentElement)">×</button>`;
      node.onclick = function(){ window.deselectClaseItem(node); };
      selected.appendChild(node);
      updateHidden();
    };

    window.deselectClaseItem = function(el){
      const id = el.dataset.id;
      el.remove();
      const avail = available.querySelector(`.competencia-item[data-id="${id}"]`);
      if (avail) avail.classList.remove('hidden');
      updateHidden();
    };

    window.moveAllClases = function(add){
      if (add) {
        const vis = Array.from(available.querySelectorAll('.competencia-item:not(.hidden)')).filter(el => el.style.display !== 'none');
        vis.forEach(el => selectClaseItem(el));
      } else {
        const sel = Array.from(selected.querySelectorAll('.competencia-item'));
        sel.forEach(el => deselectClaseItem(el));
      }
      updateHidden();
    };

    if (search) {
      search.addEventListener('input', () => {
        const q = (search.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        available.querySelectorAll('.competencia-item').forEach(el => {
          const n = (el.dataset.nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
          const c = (el.dataset.ciclo || '').toString();
          const match = n.includes(q) || c.includes(q);
          el.style.display = match ? '' : 'none';
        });
        const visibleCount = Array.from(available.querySelectorAll('.competencia-item')).filter(el => el.style.display !== 'none' && !el.classList.contains('hidden')).length;
        if (availableCount) availableCount.textContent = '(' + visibleCount + ')';
        console.log('🔍 [KitsEdit] Buscar clases:', search.value, '→', visibleCount);
      });
    }

    // Inicializar inputs ocultos con selección actual
    updateHidden();
  })();
</script>
<?php include '../footer.php'; ?>
