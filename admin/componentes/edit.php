<?php
require_once '../auth.php';
require_once __DIR__ . '/../../includes/materials-functions.php';
$page_title = 'Componentes - Editar';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null;

if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$material = null;
if ($is_edit) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM kit_items WHERE id = ?");
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $material = null;
  }
}

$categorias = get_material_categories($pdo);

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $errores[] = 'Token CSRF inválido.';
    echo "<script>console.log('❌ [ComponentesEdit] CSRF inválido');</script>";
  } else if ($action === 'save_attrs' && $is_edit) {
    // Guardar ficha técnica del componente
    try {
      $defs_stmt = $pdo->prepare('SELECT d.*, m.orden FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
      $defs_stmt->execute(['componente']);
      $defs = $defs_stmt->fetchAll(PDO::FETCH_ASSOC);
      $pdo->beginTransaction();
      foreach ($defs as $def) {
        $attr_id = (int)$def['id'];
        $tipo = $def['tipo_dato'];
        $card = $def['cardinalidad'];
        $perm_units = [];
        if (!empty($def['unidades_permitidas_json'])) { $tmp = json_decode($def['unidades_permitidas_json'], true); if (is_array($tmp)) $perm_units = $tmp; }

        $values = [];
        $units = [];
        if ($card === 'many') {
          $raw = isset($_POST['attr_' . $attr_id]) ? $_POST['attr_' . $attr_id] : '';
          if (is_array($raw)) { $values = $raw; }
          else { $values = array_filter(array_map('trim', preg_split('/[\n,]+/', (string)$raw))); }
          $units = isset($_POST['unit_' . $attr_id]) ? (array)$_POST['unit_' . $attr_id] : [];
        } else {
          $v = isset($_POST['attr_' . $attr_id]) ? trim((string)$_POST['attr_' . $attr_id]) : '';
          if ($v !== '') { $values = [$v]; }
          $u = isset($_POST['unit_' . $attr_id]) ? trim((string)$_POST['unit_' . $attr_id]) : '';
          if ($u !== '') { $units = [$u]; }
        }

        $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['componente', $id, $attr_id]);
        $ins = $pdo->prepare('INSERT INTO atributos_contenidos (tipo_entidad, entidad_id, atributo_id, valor_string, valor_numero, valor_entero, valor_booleano, valor_fecha, valor_datetime, valor_json, unidad_codigo, lang, orden, fuente, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
        $orden = 1;
        foreach ($values as $idx => $valRaw) {
          if ($valRaw === '' || $valRaw === null) { continue; }
          $unidad_codigo = null;
          if (!empty($perm_units) || !empty($def['unidad_defecto'])) {
            $unidad_sel = $card === 'many' ? ($units[$idx] ?? '') : ($units[0] ?? '');
            if ($unidad_sel === '' && !empty($def['unidad_defecto'])) { $unidad_sel = $def['unidad_defecto']; }
            if ($unidad_sel !== '') { $unidad_codigo = $unidad_sel; }
          }
          $val_string = $val_numero = $val_entero = $val_bool = $val_fecha = $val_dt = $val_json = null;
          try {
            switch ($tipo) {
              case 'number':
                $num = is_numeric(str_replace(',', '.', $valRaw)) ? (float)str_replace(',', '.', $valRaw) : null;
                if ($num === null) { continue 2; }
                $val_numero = $num; break;
              case 'integer':
                $int = is_numeric($valRaw) ? (int)$valRaw : null;
                if ($int === null) { continue 2; }
                $val_entero = $int; break;
              case 'boolean':
                $val_bool = ($valRaw === '1' || strtolower($valRaw) === 'true' || strtolower($valRaw) === 'sí' || strtolower($valRaw) === 'si') ? 1 : 0; break;
              case 'date':
                $val_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $valRaw) ? $valRaw : null; if ($val_fecha === null) { continue 2; } break;
              case 'datetime':
                $val_dt = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $valRaw) ? str_replace('T', ' ', $valRaw) . ':00' : null; if ($val_dt === null) { continue 2; } break;
              case 'json':
                $decoded = json_decode($valRaw, true); if ($decoded === null && strtolower(trim($valRaw)) !== 'null') { continue 2; } $val_json = json_encode($decoded); break;
              case 'string':
              default:
                $val_string = mb_substr((string)$valRaw, 0, 2000, 'UTF-8'); break;
            }
          } catch (Exception $e) { continue; }

          $ins->execute(['componente', $id, $attr_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, $unidad_codigo, 'es-CO', $orden++, 'manual']);
        }
      }
      $pdo->commit();
      echo "<script>console.log('✅ [ComponentesEdit] Ficha técnica guardada para componente #$id');</script>";
    } catch (PDOException $e) {
      if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
      $errores[] = 'Error guardando atributos: ' . $e->getMessage();
      echo "<script>console.log('❌ [ComponentesEdit] Error guardando atributos: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
    }
  } else {
    // Guardar campos básicos del componente
    $nombre_comun = trim($_POST['nombre_comun'] ?? '');
    $sku = trim($_POST['slug'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $advertencias_seguridad = trim($_POST['advertencias_seguridad'] ?? '');
    $unidad = trim($_POST['unidad'] ?? 'pcs');

    if ($nombre_comun === '') $errores[] = 'El nombre común es obligatorio';
    if ($categoria_id <= 0) $errores[] = 'La categoría es obligatoria';

    if ($sku === '') {
      $sku = strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', $nombre_comun));
      $sku = trim($sku, '-');
    }

    try {
      if ($is_edit) {
        $stmt = $pdo->prepare("SELECT id FROM kit_items WHERE sku = ? AND id <> ?");
        $stmt->execute([$sku, $id]);
      } else {
        $stmt = $pdo->prepare("SELECT id FROM kit_items WHERE sku = ?");
        $stmt->execute([$sku]);
      }
      if ($stmt->fetch()) { $errores[] = 'Ya existe un componente con este SKU'; }
    } catch (PDOException $e) {
      $errores[] = 'Error validando SKU: ' . $e->getMessage();
    }

    if (empty($errores)) {
      try {
        if ($is_edit) {
          $sql = "UPDATE kit_items SET nombre_comun = ?, sku = ?, categoria_id = ?, advertencias_seguridad = ?, unidad = ? WHERE id = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $id]);
        } else {
          $sql = "INSERT INTO kit_items (nombre_comun, sku, categoria_id, advertencias_seguridad, unidad) VALUES (?, ?, ?, ?, ?)";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad]);
          $id = (int)$pdo->lastInsertId();
        }
        echo "<script>console.log('✅ [Admin] Componente guardado');</script>";
        header('Location: /admin/componentes/index.php');
        exit;
      } catch (PDOException $e) {
        $errores[] = 'Error de base de datos: ' . $e->getMessage();
      }
    }
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= $is_edit ? 'Editar Componente' : 'Nuevo Componente' ?></h2>
  <span class="help-text">Campos mínimos del esquema CdC (kit_items).</span>
  <script>
    console.log('✅ [Admin] Componentes edit cargado');
    console.log('🔍 [Admin] Modo:', '<?= $is_edit ? 'edit' : 'create' ?>');
  </script>
</div>

<?php if (!empty($errores)): ?>
<div class="message error">
  <strong>Corrige los siguientes errores:</strong>
  <ul>
    <?php foreach ($errores as $e): ?>
      <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <input type="hidden" name="action" value="" />
  <div class="form-group">
    <label for="nombre_comun">Nombre común *</label>
    <input type="text" id="nombre_comun" name="nombre_comun" required value="<?= htmlspecialchars($material['nombre_comun'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-group">
    <label for="slug">SKU</label>
    <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($material['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    <small class="help-text">Identificador único del componente; se autogenera si se deja vacío.</small>
  </div>
  <div class="form-group">
    <label for="categoria_id">Categoría *</label>
    <select id="categoria_id" name="categoria_id" required>
      <option value="">Seleccione...</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>" <?= (($material['categoria_id'] ?? 0) == (int)$cat['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars(($cat['icon'] ?? '') . ' ' . ($cat['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label for="advertencias_seguridad">Advertencias de seguridad</label>
    <textarea id="advertencias_seguridad" name="advertencias_seguridad" rows="4"><?= htmlspecialchars($material['advertencias_seguridad'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="unidad">Unidad</label>
    <input type="text" id="unidad" name="unidad" value="<?= htmlspecialchars($material['unidad'] ?? 'pcs', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej: pcs, g, ml" />
  </div>
  <div class="form-actions">
    <button type="submit" class="btn" onclick="this.form.action.value='';"><?= $is_edit ? 'Actualizar' : 'Crear' ?></button>
    <a href="/admin/componentes/index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php
// Ficha técnica para componente
$attrs_defs = [];
$attrs_vals = [];
if ($is_edit) {
  try {
    $defs_stmt = $pdo->prepare('SELECT d.*, m.orden FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
    $defs_stmt->execute(['componente']);
    $attrs_defs = $defs_stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) { $attrs_defs = []; }
  try {
    $vals_stmt = $pdo->prepare('SELECT * FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? ORDER BY orden ASC, id ASC');
    $vals_stmt->execute(['componente', $id]);
    $rows = $vals_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
      $aid = (int)$r['atributo_id'];
      if (!isset($attrs_vals[$aid])) { $attrs_vals[$aid] = []; }
      $attrs_vals[$aid][] = $r;
    }
  } catch (PDOException $e) {}
}
?>
<?php if ($is_edit && !empty($attrs_defs)): ?>
<div class="card" style="margin-top:2rem;">
  <h3>Ficha técnica del componente</h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="save_attrs" />
    <div class="form-grid">
      <?php foreach ($attrs_defs as $def):
        $aid = (int)$def['id'];
        $tipo = $def['tipo_dato'];
        $card = $def['cardinalidad'];
        $unidad_def = $def['unidad_defecto'] ?? '';
        $perms = [];
        if (!empty($def['unidades_permitidas_json'])) { $tmp = json_decode($def['unidades_permitidas_json'], true); if (is_array($tmp)) $perms = $tmp; }
        $current = $attrs_vals[$aid] ?? [];
        $curr_values = [];
        $curr_units = [];
        foreach ($current as $row) {
          if ($tipo === 'number') { $curr_values[] = $row['valor_numero']; }
          else if ($tipo === 'integer') { $curr_values[] = $row['valor_entero']; }
          else if ($tipo === 'boolean') { $curr_values[] = (string)(int)$row['valor_booleano']; }
          else if ($tipo === 'date') { $curr_values[] = $row['valor_fecha']; }
          else if ($tipo === 'datetime') { $curr_values[] = $row['valor_datetime'] ? str_replace(' ', 'T', substr($row['valor_datetime'], 0, 16)) : ''; }
          else if ($tipo === 'json') { $curr_values[] = $row['valor_json']; }
          else { $curr_values[] = $row['valor_string']; }
          $curr_units[] = $row['unidad_codigo'];
        }
      ?>
      <div class="form-group">
        <label><?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?></label>
        <?php if ($card === 'many'): ?>
          <?php $text = !empty($curr_values) ? implode(', ', array_map(static function($v){ return (string)$v; }, $curr_values)) : ''; ?>
          <input type="text" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>" placeholder="Separar múltiples con comas" />
          <?php if (!empty($perms) || $unidad_def): ?>
            <small>Unidad (si aplica):</small>
            <select name="unit_<?= $aid ?>[]">
              <option value="">(por defecto<?= $unidad_def ? ': '.$unidad_def : '' ?>)</option>
              <?php foreach ($perms as $u): ?>
                <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        <?php else: ?>
          <?php if ($tipo === 'number'): ?>
            <input type="number" step="0.001" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <?php elseif ($tipo === 'integer'): ?>
            <input type="number" step="1" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <?php elseif ($tipo === 'boolean'): ?>
            <select name="attr_<?= $aid ?>">
              <option value="">(sin definir)</option>
              <option value="1" <?= (isset($curr_values[0]) && (string)$curr_values[0] === '1') ? 'selected' : '' ?>>Sí</option>
              <option value="0" <?= (isset($curr_values[0]) && (string)$curr_values[0] === '0') ? 'selected' : '' ?>>No</option>
            </select>
          <?php elseif ($tipo === 'date'): ?>
            <input type="date" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <?php elseif ($tipo === 'datetime'): ?>
            <input type="datetime-local" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <?php elseif ($tipo === 'json'): ?>
            <textarea name="attr_<?= $aid ?>" rows="3"><?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          <?php else: ?>
            <input type="text" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($curr_values[0] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <?php endif; ?>
          <?php if (!empty($perms) || $unidad_def): ?>
            <select name="unit_<?= $aid ?>">
              <option value="">(por defecto<?= $unidad_def ? ': '.$unidad_def : '' ?>)</option>
              <?php foreach ($perms as $u): ?>
                <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>" <?= (!empty($curr_units[0]) && $curr_units[0] === $u) ? 'selected' : '' ?>><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn">Guardar ficha</button>
    </div>
  </form>
  <style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
    @media (max-width: 720px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</div>
<?php endif; ?>

<?php include '../footer.php'; ?>
