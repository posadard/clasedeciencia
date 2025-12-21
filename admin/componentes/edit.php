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
  } else if ($action === 'add_attr' && $is_edit) {
    try {
      $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
      $valor = isset($_POST['valor']) ? (string)$_POST['valor'] : '';
      $unidad = isset($_POST['unidad']) ? trim((string)$_POST['unidad']) : '';
      if ($def_id <= 0 || $valor === '') { throw new Exception('Datos inválidos'); }
      $defS = $pdo->prepare('SELECT * FROM atributos_definiciones WHERE id = ?');
      $defS->execute([$def_id]);
      $def = $defS->fetch(PDO::FETCH_ASSOC);
      if (!$def) { throw new Exception('Atributo no existe'); }
      $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['componente', $id, $def_id]);
      $pdo->beginTransaction();
      $ins = $pdo->prepare('INSERT INTO atributos_contenidos (tipo_entidad, entidad_id, atributo_id, valor_string, valor_numero, valor_entero, valor_booleano, valor_fecha, valor_datetime, valor_json, unidad_codigo, lang, orden, fuente, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
      $card = $def['cardinalidad'];
      $tipo = $def['tipo_dato'];
      $vals = $card === 'many' ? array_filter(array_map('trim', preg_split('/[\n,]+/', $valor))) : [$valor];
      $orden = 1;
      foreach ($vals as $v) {
        $val_string = $val_numero = $val_entero = $val_bool = $val_fecha = $val_dt = $val_json = null;
        switch ($tipo) {
          case 'number':
            $num = is_numeric(str_replace(',', '.', $v)) ? (float)str_replace(',', '.', $v) : null; if ($num === null) continue 2; $val_numero = $num; break;
          case 'integer':
            $int = is_numeric($v) ? (int)$v : null; if ($int === null) continue 2; $val_entero = $int; break;
          case 'boolean':
            $val_bool = ($v === '1' || strtolower($v) === 'true' || strtolower($v) === 'sí' || strtolower($v) === 'si') ? 1 : 0; break;
          case 'date':
            $val_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; if ($val_fecha === null) continue 2; break;
          case 'datetime':
            $val_dt = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v) ? (str_replace('T', ' ', $v) . ':00') : null; if ($val_dt === null) continue 2; break;
          case 'json':
            $decoded = json_decode($v, true); if ($decoded === null && strtolower(trim($v)) !== 'null') continue 2; $val_json = json_encode($decoded); break;
          case 'string':
          default:
            $val_string = mb_substr((string)$v, 0, 2000, 'UTF-8'); break;
        }
        $ins->execute(['componente', $id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, ($unidad ?: ($def['unidad_defecto'] ?? null)), 'es-CO', $orden++, 'manual']);
      }
      $pdo->commit();
      echo "<script>console.log('✅ [ComponentesEdit] add_attr guardado');</script>";
    } catch (Exception $e) {
      if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
      $errores[] = 'Error agregando atributo: ' . $e->getMessage();
      echo "<script>console.log('❌ [ComponentesEdit] add_attr error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
    }
  } else if ($action === 'update_attr' && $is_edit) {
    try {
      $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
      $valor = isset($_POST['valor']) ? (string)$_POST['valor'] : '';
      $unidad = isset($_POST['unidad']) ? trim((string)$_POST['unidad']) : '';
      if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
      $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['componente', $id, $def_id]);
      $defS = $pdo->prepare('SELECT * FROM atributos_definiciones WHERE id = ?');
      $defS->execute([$def_id]);
      $def = $defS->fetch(PDO::FETCH_ASSOC);
      if (!$def) { throw new Exception('Atributo no existe'); }
      $pdo->beginTransaction();
      $ins = $pdo->prepare('INSERT INTO atributos_contenidos (tipo_entidad, entidad_id, atributo_id, valor_string, valor_numero, valor_entero, valor_booleano, valor_fecha, valor_datetime, valor_json, unidad_codigo, lang, orden, fuente, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
      $card = $def['cardinalidad'];
      $tipo = $def['tipo_dato'];
      $vals = $card === 'many' ? array_filter(array_map('trim', preg_split('/[\n,]+/', $valor))) : [$valor];
      $orden = 1;
      foreach ($vals as $v) {
        $val_string = $val_numero = $val_entero = $val_bool = $val_fecha = $val_dt = $val_json = null;
        switch ($tipo) {
          case 'number':
            $num = is_numeric(str_replace(',', '.', $v)) ? (float)str_replace(',', '.', $v) : null; if ($num === null) continue 2; $val_numero = $num; break;
          case 'integer':
            $int = is_numeric($v) ? (int)$v : null; if ($int === null) continue 2; $val_entero = $int; break;
          case 'boolean':
            $val_bool = ($v === '1' || strtolower($v) === 'true' || strtolower($v) === 'sí' || strtolower($v) === 'si') ? 1 : 0; break;
          case 'date':
            $val_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; if ($val_fecha === null) continue 2; break;
          case 'datetime':
            $val_dt = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v) ? (str_replace('T', ' ', $v) . ':00') : null; if ($val_dt === null) continue 2; break;
          case 'json':
            $decoded = json_decode($v, true); if ($decoded === null && strtolower(trim($v)) !== 'null') continue 2; $val_json = json_encode($decoded); break;
          case 'string':
          default:
            $val_string = mb_substr((string)$v, 0, 2000, 'UTF-8'); break;
        }
        $ins->execute(['componente', $id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, ($unidad ?: ($def['unidad_defecto'] ?? null)), 'es-CO', $orden++, 'manual']);
      }
      $pdo->commit();
      echo "<script>console.log('✅ [ComponentesEdit] update_attr guardado');</script>";
    } catch (Exception $e) {
      if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
      $errores[] = 'Error actualizando atributo: ' . $e->getMessage();
      echo "<script>console.log('❌ [ComponentesEdit] update_attr error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
    }
  } else if ($action === 'delete_attr' && $is_edit) {
    try {
      $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
      if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
      $stmt = $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?');
      $stmt->execute(['componente', $id, $def_id]);
      echo "<script>console.log('✅ [ComponentesEdit] delete_attr ejecutado');</script>";
    } catch (PDOException $e) {
      $errores[] = 'Error eliminando atributo: ' . $e->getMessage();
      echo "<script>console.log('❌ [ComponentesEdit] delete_attr error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
    }
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
  } else if ($action === 'create_attr_def' && $is_edit) {
    // Crear nueva definición de atributo y mapearla al tipo Componente
    try {
      $etiqueta = isset($_POST['etiqueta']) ? trim((string)$_POST['etiqueta']) : '';
      $clave = isset($_POST['clave']) ? trim((string)$_POST['clave']) : '';
      $tipo = isset($_POST['tipo_dato']) ? trim((string)$_POST['tipo_dato']) : 'string';
      $card = isset($_POST['cardinalidad']) ? trim((string)$_POST['cardinalidad']) : 'one';
      $unidad_def = isset($_POST['unidad_defecto']) ? trim((string)$_POST['unidad_defecto']) : '';
      $unidades_raw = isset($_POST['unidades_permitidas']) ? (string)$_POST['unidades_permitidas'] : '';

      if ($etiqueta === '') { throw new Exception('Etiqueta requerida'); }
      if ($clave === '') {
        $clave = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $etiqueta));
        $clave = trim($clave, '_');
      } else {
        $clave = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $clave));
        $clave = trim($clave, '_');
      }
      $tipos_validos = ['string','number','integer','boolean','date','datetime','json'];
      $cards_validas = ['one','many'];
      if (!in_array($tipo, $tipos_validos, true)) { $tipo = 'string'; }
      if (!in_array($card, $cards_validas, true)) { $card = 'one'; }

      $unidades = array_filter(array_map(function($v){ return trim($v); }, preg_split('/[,\n]+/', $unidades_raw)));
      $unidades_json = !empty($unidades) ? json_encode(array_values($unidades)) : null;

      $pdo->beginTransaction();
      $def_id = null;
      $st = $pdo->prepare('SELECT id FROM atributos_definiciones WHERE clave = ?');
      $st->execute([$clave]);
      $def_id = (int)$st->fetchColumn();
      if ($def_id <= 0) {
        $ins = $pdo->prepare('INSERT INTO atributos_definiciones (clave, etiqueta, tipo_dato, cardinalidad, unidad_defecto, unidades_permitidas_json, aplica_a_json) VALUES (?,?,?,?,?,?,?)');
        $aplica = json_encode(['componente']);
        $ins->execute([$clave, $etiqueta, $tipo, $card, ($unidad_def !== '' ? $unidad_def : null), $unidades_json, $aplica]);
        $def_id = (int)$pdo->lastInsertId();
      }
      $chk = $pdo->prepare('SELECT COUNT(*) FROM atributos_mapeo WHERE atributo_id = ? AND tipo_entidad = ?');
      $chk->execute([$def_id, 'componente']);
      if ((int)$chk->fetchColumn() === 0) {
        $nextOrdStmt = $pdo->prepare('SELECT COALESCE(MAX(orden),0)+1 AS nextOrd FROM atributos_mapeo WHERE tipo_entidad = ?');
        $nextOrdStmt->execute(['componente']);
        $next = (int)$nextOrdStmt->fetchColumn();
        $mp = $pdo->prepare('INSERT INTO atributos_mapeo (atributo_id, tipo_entidad, visible, orden) VALUES (?,?,?,?)');
        $mp->execute([$def_id, 'componente', 1, $next]);
      }
      $pdo->commit();
      echo "<script>console.log('✅ [ComponentesEdit] create_attr_def listo: " . htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') . "');</script>";
    } catch (Exception $e) {
      if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
      $errores[] = 'Error creando atributo: ' . $e->getMessage();
      echo "<script>console.log('❌ [ComponentesEdit] create_attr_def error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
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
<?php if ($is_edit): ?>
<div class="card" style="margin-top:2rem;">
  <h3>Ficha técnica (chips)</h3>
  <div class="form-group">
    <label for="attr_search_cmp">Agregar atributo</label>
    <div class="component-selector-container">
      <div class="selected-components" id="selected-attrs-cmp">
        <?php foreach ($attrs_defs as $def):
          $aid = (int)$def['id'];
          $values = $attrs_vals[$aid] ?? [];
          if (empty($values)) continue;
          $label = $def['etiqueta'];
          $tipo = $def['tipo_dato'];
          $unit = $values[0]['unidad_codigo'] ?? '';
          $display = [];
          foreach ($values as $v) {
            if ($tipo === 'number') { $display[] = ($v['valor_numero'] !== null ? rtrim(rtrim((string)$v['valor_numero'], '0'), '.') : ''); }
            else if ($tipo === 'integer') { $display[] = (string)$v['valor_entero']; }
            else if ($tipo === 'boolean') { $display[] = ((int)$v['valor_booleano'] === 1 ? 'Sí' : 'No'); }
            else if ($tipo === 'date') { $display[] = $v['valor_fecha']; }
            else if ($tipo === 'datetime') { $display[] = $v['valor_datetime']; }
            else if ($tipo === 'json') { $display[] = $v['valor_json']; }
            else { $display[] = $v['valor_string']; }
          }
          $text = htmlspecialchars(implode(', ', array_filter($display)), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="component-chip" data-attr-id="<?= $aid ?>">
          <span class="name"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="meta">· <strong><?= $text ?></strong><?= $unit ? ' ' . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') : '' ?></span>
          <button type="button" class="edit-component js-edit-attr-cmp" title="Editar"
            data-attr-id="<?= $aid ?>"
            data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
            data-tipo="<?= htmlspecialchars($def['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>"
            data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
            data-units='<?= $def['unidades_permitidas_json'] ? $def['unidades_permitidas_json'] : "[]" ?>'
            data-unidad_def="<?= htmlspecialchars($def['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-values='<?= htmlspecialchars(json_encode($values), ENT_QUOTES, "UTF-8") ?>'
          >✏️</button>
          <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este atributo del componente?')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
            <input type="hidden" name="action" value="delete_attr" />
            <input type="hidden" name="def_id" value="<?= $aid ?>" />
            <button type="submit" class="remove-component" title="Remover">×</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <input type="text" id="attr_search_cmp" placeholder="Escribir para buscar atributo..." autocomplete="off" />
      <datalist id="attrs_list_cmp">
        <?php foreach ($attrs_defs as $def): ?>
          <option value="<?= (int)$def['id'] ?>" data-name="<?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?>" data-clave="<?= htmlspecialchars($def['clave'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($def['grupo'] ?? 'ficha', ENT_QUOTES, 'UTF-8') ?>)
          </option>
        <?php endforeach; ?>
      </datalist>
      <div class="autocomplete-dropdown" id="attr_autocomplete_dropdown_cmp"></div>
    </div>
    <small>Escribe para buscar atributos. Al seleccionar, edita su valor en el modal.</small>
  </div>
</div>

<!-- Modal Editar Atributo (Componente) -->
<div class="modal-backdrop" id="modalEditAttrCmp">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalEditAttrCmpTitle">
    <div class="modal-header">
      <h4 id="modalEditAttrCmpTitle">Editar atributo</h4>
      <button type="button" class="btn-plain js-close-modal" data-target="#modalEditAttrCmp">✖</button>
    </div>
    <form method="POST" id="formEditAttrCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="update_attr" />
      <input type="hidden" name="def_id" id="edit_def_id_cmp" />
      <div class="modal-body">
        <div class="muted" id="editAttrCmpInfo"></div>
        <div class="form-group">
          <label for="edit_valor_cmp">Valor</label>
          <textarea id="edit_valor_cmp" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
        </div>
        <div class="form-group" id="edit_unidad_cmp_group">
          <label for="edit_unidad_cmp">Unidad (si aplica)</label>
          <select id="edit_unidad_cmp" name="unidad"></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditAttrCmp">Cancelar</button>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Agregar Atributo (Componente) -->
<div class="modal-backdrop" id="modalAddAttrCmp">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalAddAttrCmpTitle">
    <div class="modal-header">
      <h4 id="modalAddAttrCmpTitle">Agregar atributo</h4>
      <button type="button" class="btn-plain js-close-modal" data-target="#modalAddAttrCmp">✖</button>
    </div>
    <form method="POST" id="formAddAttrCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="add_attr" />
      <input type="hidden" name="def_id" id="add_def_id_cmp" />
      <div class="modal-body">
        <div class="muted" id="addAttrCmpInfo"></div>
        <div class="form-group">
          <label for="add_valor_cmp">Valor</label>
          <textarea id="add_valor_cmp" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
        </div>
        <div class="form-group" id="add_unidad_cmp_group">
          <label for="add_unidad_cmp">Unidad (si aplica)</label>
          <select id="add_unidad_cmp" name="unidad"></select>
        </div>
      </div>

        <!-- Modal Crear Definición de Atributo (Componente) -->
        <div class="modal-backdrop" id="modalCreateAttrCmp">
          <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalCreateAttrCmpTitle">
            <div class="modal-header">
              <h4 id="modalCreateAttrCmpTitle">Crear nuevo atributo</h4>
              <button type="button" class="btn-plain js-close-modal" data-target="#modalCreateAttrCmp">✖</button>
            </div>
            <form method="POST" id="formCreateAttrCmp">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="create_attr_def" />
              <div class="modal-body">
                <div class="form-group"><label for="create_etiqueta_cmp">Etiqueta</label><input type="text" id="create_etiqueta_cmp" name="etiqueta" required /></div>
                <div class="form-group"><label for="create_clave_cmp">Clave</label><input type="text" id="create_clave_cmp" name="clave" placeholder="auto desde etiqueta si se deja vacío" /></div>
                <div class="field-inline">
                  <div class="form-group"><label for="create_tipo_cmp">Tipo</label>
                    <select id="create_tipo_cmp" name="tipo_dato">
                      <option value="string">string</option>
                      <option value="number">number</option>
                      <option value="integer">integer</option>
                      <option value="boolean">boolean</option>
                      <option value="date">date</option>
                      <option value="datetime">datetime</option>
                      <option value="json">json</option>
                    </select>
                  </div>
                  <div class="form-group"><label for="create_card_cmp">Cardinalidad</label>
                    <select id="create_card_cmp" name="cardinalidad">
                      <option value="one">one</option>
                      <option value="many">many</option>
                    </select>
                  </div>
                </div>
                <div class="field-inline">
                  <div class="form-group"><label for="create_unidad_cmp">Unidad por defecto</label><input type="text" id="create_unidad_cmp" name="unidad_defecto" placeholder="opcional" /></div>
                  <div class="form-group"><label for="create_unidades_cmp">Unidades permitidas</label><input type="text" id="create_unidades_cmp" name="unidades_permitidas" placeholder="separa por comas" /></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalCreateAttrCmp">Cancelar</button>
                <button type="submit" class="btn">Crear</button>
              </div>
            </form>
          </div>
         </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddAttrCmp">Cancelar</button>
        <button type="submit" class="btn">Agregar</button>
      </div>
    </form>
  </div>
 </div>

<script>
  // Utilidades de modal (compartidas)
  function openModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.add('show'); console.log('🔍 [ComponentesEdit] Abre modal', sel); }
  }
  function closeModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.remove('show'); console.log('🔍 [ComponentesEdit] Cierra modal', sel); }
  }
  document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const t = e.currentTarget.getAttribute('data-target');
      if (t) closeModal(t);
    });
  });
  document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', (e) => { if (e.target === b) closeModal('#' + b.id); });
  });

  // Autocomplete + modal para atributos de componentes
  (function initAttrUICmp(){
    const dropdown = document.getElementById('attr_autocomplete_dropdown_cmp');
    const input = document.getElementById('attr_search_cmp');
    const selectedWrap = document.getElementById('selected-attrs-cmp');
    if (!dropdown || !input || !selectedWrap) { console.log('⚠️ [ComponentesEdit] UI atributos no inicializada'); return; }

    const defs = [
      <?php foreach ($attrs_defs as $d): ?>
      { id: <?= (int)$d['id'] ?>, label: '<?= htmlspecialchars($d['etiqueta'], ENT_QUOTES, 'UTF-8') ?>', tipo: '<?= htmlspecialchars($d['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>', card: '<?= htmlspecialchars($d['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>', units: <?= $d['unidades_permitidas_json'] ? $d['unidades_permitidas_json'] : '[]' ?>, unitDef: '<?= htmlspecialchars($d['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>' },
      <?php endforeach; ?>
    ];

    function normalize(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
    function render(list){
      if (!list.length){ dropdown.innerHTML = '<div class="autocomplete-item"><span class="cmp-code">Sin resultados</span></div><div class="autocomplete-item create-item" id="attr_create_item_cmp"><strong>➕ Crear nuevo atributo</strong></div>'; dropdown.style.display='block'; const ci=document.getElementById('attr_create_item_cmp'); if(ci){ ci.addEventListener('click', onCreateNewCmp); } return; }
      dropdown.innerHTML = '';
      list.slice(0, 20).forEach(def => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML = `<strong>${def.label}</strong><span class="cmp-code">${def.tipo}${def.unitDef? ' · '+def.unitDef:''}</span>`;
        div.addEventListener('click', () => onChoose(def));
        dropdown.appendChild(div);
      });
      dropdown.style.display = 'block';
    }
    function filter(q){
      const nq = normalize(q);
      const out = defs.filter(d => normalize(d.label).includes(nq));
      console.log('🔍 [ComponentesEdit] Buscar atributo:', q, '→', out.length);
      render(out);
    }
    function onChoose(def){
      try {
        document.getElementById('add_def_id_cmp').value = String(def.id);
        document.getElementById('addAttrCmpInfo').textContent = def.label;
        const sel = document.getElementById('add_unidad_cmp');
        const selGroup = document.getElementById('add_unidad_cmp_group');
        sel.innerHTML = '';
        const hasUnits = Array.isArray(def.units) && def.units.length > 0;
        const hasDefault = !!def.unitDef;
        if (hasUnits || hasDefault) {
          const opt0 = document.createElement('option');
          opt0.value = ''; opt0.textContent = def.unitDef ? `(por defecto: ${def.unitDef})` : '(sin unidad)'; sel.appendChild(opt0);
          if (hasUnits) { def.units.forEach(u => { const o = document.createElement('option'); o.value = u; o.textContent = u; sel.appendChild(o); }); }
          if (selGroup) selGroup.style.display = '';
          console.log('🔍 [ComponentesEdit] Unidad visible (aplica)');
        } else {
          if (selGroup) selGroup.style.display = 'none';
          console.log('🔍 [ComponentesEdit] Unidad oculta (no aplica)');
        }
        openModal('#modalAddAttrCmp');
        setTimeout(() => { try { document.getElementById('add_valor_cmp')?.focus(); } catch(_e){} }, 50);
      } catch (e) {
        console.log('❌ [ComponentesEdit] Error preparar modal atributo:', e && e.message);
      }
      dropdown.style.display = 'none';
    }
    function onCreateNewCmp(){
      try {
        const val = (input.value || '').trim();
        document.getElementById('create_etiqueta_cmp').value = val;
        document.getElementById('create_clave_cmp').value = '';
        document.getElementById('create_tipo_cmp').value = 'string';
        document.getElementById('create_card_cmp').value = 'one';
        document.getElementById('create_unidad_cmp').value = '';
        document.getElementById('create_unidades_cmp').value = '';
        openModal('#modalCreateAttrCmp');
        setTimeout(() => { try { document.getElementById('create_etiqueta_cmp')?.focus(); } catch(_e){} }, 50);
        console.log('🔍 [ComponentesEdit] Crear atributo desde búsqueda:', val);
      } catch(e){ console.log('❌ [ComponentesEdit] Error preparar crear atributo:', e && e.message); }
      dropdown.style.display='none';
    }
    input.addEventListener('focus', () => filter(input.value));
    input.addEventListener('input', () => filter(input.value));
    document.addEventListener('click', (e) => { if (!dropdown.contains(e.target) && e.target !== input) dropdown.style.display = 'none'; });

    // Editar chip existente
    document.querySelectorAll('.js-edit-attr-cmp').forEach(btn => {
      btn.addEventListener('click', () => {
        const defId = btn.getAttribute('data-attr-id');
        const label = btn.getAttribute('data-label');
        const tipo = btn.getAttribute('data-tipo');
        const unitsJson = btn.getAttribute('data-units');
        const unitDef = btn.getAttribute('data-unidad_def') || '';
        const vals = JSON.parse(btn.getAttribute('data-values') || '[]');
        document.getElementById('edit_def_id_cmp').value = defId;
        document.getElementById('editAttrCmpInfo').textContent = label;
        const inputEl = document.getElementById('edit_valor_cmp');
        const unitSel = document.getElementById('edit_unidad_cmp');
        const unitGroup = document.getElementById('edit_unidad_cmp_group');
        inputEl.value = '';
        unitSel.innerHTML = '';
        if (Array.isArray(vals) && vals.length) {
          const parts = vals.map(v => {
            if (tipo === 'number') return v.valor_numero;
            if (tipo === 'integer') return v.valor_entero;
            if (tipo === 'boolean') return (parseInt(v.valor_booleano,10)===1?'1':'0');
            if (tipo === 'date') return v.valor_fecha;
            if (tipo === 'datetime') return v.valor_datetime;
            if (tipo === 'json') return v.valor_json;
            return v.valor_string;
          }).filter(Boolean);
          inputEl.value = parts.join(', ');
        }
        let units = [];
        try { const parsed = JSON.parse(unitsJson || '[]'); if (Array.isArray(parsed)) units = parsed; } catch(_e){ units = []; }
        const hasUnits = Array.isArray(units) && units.length > 0;
        const hasDefault = !!unitDef;
        if (hasUnits || hasDefault) {
          const opt0 = document.createElement('option'); opt0.value=''; opt0.textContent = unitDef ? `(por defecto: ${unitDef})` : '(sin unidad)'; unitSel.appendChild(opt0);
          if (hasUnits) units.forEach(u => { const o=document.createElement('option'); o.value=u; o.textContent=u; unitSel.appendChild(o); });
          if (unitGroup) unitGroup.style.display = '';
          console.log('🔍 [ComponentesEdit] Unidad visible (aplica)');
        } else {
          if (unitGroup) unitGroup.style.display = 'none';
          console.log('🔍 [ComponentesEdit] Unidad oculta (no aplica)');
        }
        openModal('#modalEditAttrCmp');
      });
    });
  })();

  // Logs de envío de formularios
  document.getElementById('formEditAttrCmp')?.addEventListener('submit', () => console.log('📡 [ComponentesEdit] Enviando update_attr...'));
  document.getElementById('formAddAttrCmp')?.addEventListener('submit', () => console.log('📡 [ComponentesEdit] Enviando add_attr...'));
</script>
<style>
  .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
  .modal-backdrop.show { display: flex; }
  .modal { background: #fff; border-radius: 8px; max-width: 520px; width: 95%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
  .modal-header { padding: 12px 16px; border-bottom: 1px solid #eee; display:flex; align-items:center; justify-content: space-between; }
  .modal-body { padding: 16px; }
  .modal-footer { padding: 12px 16px; border-top: 1px solid #eee; display:flex; gap: 8px; justify-content: flex-end; }
  .modal .form-group { margin-bottom: 12px; }
  .btn-plain { background: transparent; border: none; font-size: 18px; cursor: pointer; }
  .muted { color: #666; font-size: 0.9rem; }
  /* Chips container reuses existing classes from kits UI */
</style>
<?php endif; ?>

<?php include '../footer.php'; ?>
