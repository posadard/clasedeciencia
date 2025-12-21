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
  'slug' => '',
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
    $stmt = $pdo->prepare('SELECT id, clase_id, nombre, slug, codigo, version, activo FROM kits WHERE id = ?');
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

    if ($action === 'save_attrs' && $is_edit) {
      // Guardar atributos técnicos (atributos_contenidos)
      try {
        $defs_stmt = $pdo->prepare('SELECT d.*, m.orden, m.ui_hint FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
        $defs_stmt->execute(['kit']);
        $defs = $defs_stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo->beginTransaction();
        foreach ($defs as $def) {
          $attr_id = (int)$def['id'];
          $tipo = $def['tipo_dato'];
          $card = $def['cardinalidad'];
          $perm_units = [];
          if (!empty($def['unidades_permitidas_json'])) {
            $decoded = json_decode($def['unidades_permitidas_json'], true);
            if (is_array($decoded)) { $perm_units = $decoded; }
          }

          // Obtener valores del POST
          $values = [];
          $units = [];
          if ($card === 'many') {
            $raw = isset($_POST['attr_' . $attr_id]) ? $_POST['attr_' . $attr_id] : '';
            if (is_array($raw)) {
              $values = $raw;
            } else {
              $values = array_filter(array_map('trim', preg_split('/[\n,]+/', (string)$raw)));
            }
            $units = isset($_POST['unit_' . $attr_id]) ? (array)$_POST['unit_' . $attr_id] : [];
          } else {
            $v = isset($_POST['attr_' . $attr_id]) ? trim((string)$_POST['attr_' . $attr_id]) : '';
            if ($v !== '') { $values = [$v]; }
            $u = isset($_POST['unit_' . $attr_id]) ? trim((string)$_POST['unit_' . $attr_id]) : '';
            if ($u !== '') { $units = [$u]; }
          }

          // Borrar existentes
          $del = $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?');
          $del->execute(['kit', $id, $attr_id]);

          // Insertar nuevos
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
                  $val_numero = $num;
                  break;
                case 'integer':
                  $int = is_numeric($valRaw) ? (int)$valRaw : null;
                  if ($int === null) { continue 2; }
                  $val_entero = $int;
                  break;
                case 'boolean':
                  $val_bool = ($valRaw === '1' || strtolower($valRaw) === 'true' || strtolower($valRaw) === 'sí' || strtolower($valRaw) === 'si') ? 1 : 0;
                  break;
                case 'date':
                  $val_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $valRaw) ? $valRaw : null;
                  if ($val_fecha === null) { continue 2; }
                  break;
                case 'datetime':
                  $val_dt = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $valRaw) ? str_replace('T', ' ', $valRaw) . ':00' : null;
                  if ($val_dt === null) { continue 2; }
                  break;
                case 'json':
                  $decoded = json_decode($valRaw, true);
                  if ($decoded === null && strtolower(trim($valRaw)) !== 'null') { continue 2; }
                  $val_json = json_encode($decoded);
                  break;
                case 'string':
                default:
                  $val_string = mb_substr((string)$valRaw, 0, 2000, 'UTF-8');
                  break;
              }
            } catch (Exception $e) {
              continue;
            }

            $ins->execute([
              'kit', $id, $attr_id,
              $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json,
              $unidad_codigo, 'es-CO', $orden++, 'manual'
            ]);
          }
        }
        $pdo->commit();
        $action_msg = 'Ficha técnica guardada.';
        echo '<script>console.log("✅ [KitsEdit] Ficha técnica guardada para kit ' . (int)$id . '");</script>';
      } catch (PDOException $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error_msg = 'Error guardando atributos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<script>console.log("❌ [KitsEdit] Error guardando atributos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
      }
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
        $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['kit', $id, $def_id]);
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
          $ins->execute(['kit', $id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, ($unidad ?: ($def['unidad_defecto'] ?? null)), 'es-CO', $orden++, 'manual']);
        }
        $pdo->commit();
        $action_msg = 'Atributo agregado.';
        echo '<script>console.log("✅ [KitsEdit] add_attr guardado");</script>';
      } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error_msg = 'Error agregando atributo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<script>console.log("❌ [KitsEdit] add_attr error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
      }
    } else if ($action === 'update_attr' && $is_edit) {
      try {
        $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
        $valor = isset($_POST['valor']) ? (string)$_POST['valor'] : '';
        $unidad = isset($_POST['unidad']) ? trim((string)$_POST['unidad']) : '';
        if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
        $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['kit', $id, $def_id]);
        // reusar add_attr
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
          $ins->execute(['kit', $id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, ($unidad ?: ($def['unidad_defecto'] ?? null)), 'es-CO', $orden++, 'manual']);
        }
        $pdo->commit();
        $action_msg = 'Atributo actualizado.';
        echo '<script>console.log("✅ [KitsEdit] update_attr guardado");</script>';
      } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error_msg = 'Error actualizando atributo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<script>console.log("❌ [KitsEdit] update_attr error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
      }
    } else if ($action === 'delete_attr' && $is_edit) {
      try {
        $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
        if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
        $stmt = $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?');
        $stmt->execute(['kit', $id, $def_id]);
        $action_msg = 'Atributo eliminado.';
        echo '<script>console.log("✅ [KitsEdit] delete_attr ejecutado");</script>';
      } catch (PDOException $e) {
        $error_msg = 'Error eliminando atributo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<script>console.log("❌ [KitsEdit] delete_attr error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
      }
    } else if ($action === 'save') {
      // Clases seleccionadas (transfer list)
      $clases_sel = isset($_POST['clases']) && is_array($_POST['clases']) ? array_map('intval', $_POST['clases']) : [];
      $principal_clase_id = !empty($clases_sel) ? (int)$clases_sel[0] : 0;
      // Mantener compatibilidad si vienen datos legacy de clase_id
      $legacy_clase_id = isset($_POST['clase_id']) && ctype_digit($_POST['clase_id']) ? (int)$_POST['clase_id'] : 0;
      if ($principal_clase_id === 0 && $legacy_clase_id > 0) { $principal_clase_id = $legacy_clase_id; }
      $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
      $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
      $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
      $version = isset($_POST['version']) ? trim($_POST['version']) : '1';
      $activo = isset($_POST['activo']) ? 1 : 0;

      // Autogenerar/normalizar slug con prefijo kit-
      if ($slug === '' && $nombre !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
        $slug = trim($slug, '-');
      }
      if ($slug !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $slug));
        $slug = trim($slug, '-');
        // Eliminar prefijos repetidos 'kit-' y forzar uno solo al inicio
        $slug = preg_replace('/^(?:kit-)+/i', '', $slug);
        $slug = 'kit-' . ltrim($slug, '-');
      }

      if ($principal_clase_id <= 0 || $nombre === '' || $codigo === '' || $slug === '') {
        $error_msg = 'Selecciona al menos una clase y completa nombre, código y slug.';
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
            // Validar slug único
            if ($is_edit) {
              $checkS = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE slug = ? AND id <> ?');
              $checkS->execute([$slug, $id]);
            } else {
              $checkS = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE slug = ?');
              $checkS->execute([$slug]);
            }
            $slugExists = (int)$checkS->fetchColumn();
            if ($slugExists > 0) {
              $error_msg = 'El slug ya existe. Elige otro.';
            } else {
            $pdo->beginTransaction();
            if ($is_edit) {
              $stmt = $pdo->prepare('UPDATE kits SET clase_id=?, nombre=?, slug=?, codigo=?, version=?, activo=?, updated_at=NOW() WHERE id=?');
              $stmt->execute([$principal_clase_id, $nombre, $slug, $codigo, $version, $activo, $id]);
            } else {
              $stmt = $pdo->prepare('INSERT INTO kits (clase_id, nombre, slug, codigo, version, activo, updated_at) VALUES (?,?,?,?,?,?,NOW())');
              $stmt->execute([$principal_clase_id, $nombre, $slug, $codigo, $version, $activo]);
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
    <label for="slug">Slug</label>
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($kit['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="se genera automáticamente" style="flex:1;" />
      <button type="button" id="btn_generar_slug" style="padding:8px 16px; background:#0066cc; color:white; border:none; border-radius:4px; cursor:pointer; white-space:nowrap;">⚡ Generar</button>
    </div>
    <small class="hint">URL amigable. Ejemplo: kit-carro-solar</small>
  </div>
  <div class="form-group">
    <label for="codigo">Código</label>
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($kit['codigo'], ENT_QUOTES, 'UTF-8') ?>" placeholder="p.ej. KIT-PLANTA-LUZ-01" required />
      <span id="codigo_status" style="font-size:0.85rem;color:#666;"></span>
    </div>
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

  <?php
  // Definiciones y valores actuales de atributos del Kit (para UI tipo chips)
  $attr_defs = [];
  $attr_vals = [];
  if ($is_edit) {
    try {
      $st = $pdo->prepare('SELECT d.* FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
      $st->execute(['kit']);
      $attr_defs = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $attr_defs = []; }
    try {
      $sv = $pdo->prepare('SELECT * FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? ORDER BY atributo_id ASC, orden ASC');
      $sv->execute(['kit', $id]);
      $rows = $sv->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        $aid = (int)$r['atributo_id'];
        if (!isset($attr_vals[$aid])) $attr_vals[$aid] = [];
        $attr_vals[$aid][] = $r;
      }
    } catch (PDOException $e) {}
  }
  ?>
  <?php if ($is_edit): ?>
  <div class="card" style="margin-top:2rem;">
    <h3>Ficha técnica</h3>
    <div class="form-group">
      <label for="attr_search">Agregar atributo</label>
      <div class="component-selector-container">
        <div class="selected-components" id="selected-attrs">
          <?php foreach ($attr_defs as $def):
            $aid = (int)$def['id'];
            $values = $attr_vals[$aid] ?? [];
            if (empty($values)) continue;
            // Render resumen
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
            <button type="button" class="edit-component js-edit-attr" title="Editar"
              data-attr-id="<?= $aid ?>"
              data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
              data-tipo="<?= htmlspecialchars($def['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>"
              data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
              data-units="<?= htmlspecialchars($def['unidades_permitidas_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
              data-unidad_def="<?= htmlspecialchars($def['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-values="<?= htmlspecialchars(json_encode($values), ENT_QUOTES, 'UTF-8') ?>"
            >✏️</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este atributo del kit?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete_attr" />
              <input type="hidden" name="def_id" value="<?= $aid ?>" />
              <button type="submit" class="remove-component" title="Remover">×</button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <input type="text" id="attr_search" placeholder="Escribir para buscar atributo..." autocomplete="off" />
        <datalist id="attrs_list">
          <?php foreach ($attr_defs as $def): ?>
            <option value="<?= (int)$def['id'] ?>" data-name="<?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?>" data-clave="<?= htmlspecialchars($def['clave'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($def['grupo'] ?? 'ficha', ENT_QUOTES, 'UTF-8') ?>)
            </option>
          <?php endforeach; ?>
        </datalist>
        <div class="autocomplete-dropdown" id="attr_autocomplete_dropdown"></div>
      </div>
      <small>Escribe para buscar atributos. Al seleccionar, edita su valor en el modal.</small>
    </div>
  </div>

  <!-- Modal Editar Atributo -->
  <div class="modal-backdrop" id="modalEditAttr">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalEditAttrTitle">
      <div class="modal-header">
        <h4 id="modalEditAttrTitle">Editar atributo</h4>
        <button type="button" class="btn-plain js-close-modal" data-target="#modalEditAttr">✖</button>
      </div>
      <form method="POST" id="formEditAttr">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="update_attr" />
        <input type="hidden" name="def_id" id="edit_def_id" />
        <div class="modal-body">
          <div class="muted" id="editAttrInfo"></div>
          <div class="form-group">
            <label for="edit_valor">Valor</label>
            <textarea id="edit_valor" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
          </div>
          <div class="form-group">
            <label for="edit_unidad">Unidad (si aplica)</label>
            <select id="edit_unidad" name="unidad"></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditAttr">Cancelar</button>
          <button type="submit" class="btn">Guardar</button>
        </div>
      </form>
    </div>
   </div>

  <!-- Modal Agregar Atributo -->
  <div class="modal-backdrop" id="modalAddAttr">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalAddAttrTitle">
      <div class="modal-header">
        <h4 id="modalAddAttrTitle">Agregar atributo</h4>
        <button type="button" class="btn-plain js-close-modal" data-target="#modalAddAttr">✖</button>
      </div>
      <form method="POST" id="formAddAttr">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="add_attr" />
        <input type="hidden" name="def_id" id="add_def_id" />
        <div class="modal-body">
          <div class="muted" id="addAttrInfo"></div>
          <div class="form-group">
            <label for="add_valor">Valor</label>
            <textarea id="add_valor" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
          </div>
          <div class="form-group">
            <label for="add_unidad">Unidad (si aplica)</label>
            <select id="add_unidad" name="unidad"></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddAttr">Cancelar</button>
          <button type="submit" class="btn">Agregar</button>
        </div>
      </form>
    </div>
   </div>

  <script>
    // Autocomplete + modal para atributos (similar a componentes)
    (function initAttrUI(){
      const dropdown = document.getElementById('attr_autocomplete_dropdown');
      const input = document.getElementById('attr_search');
      const selectedWrap = document.getElementById('selected-attrs');
      if (!dropdown || !input || !selectedWrap) { console.log('⚠️ [KitsEdit] UI atributos no inicializada'); return; }

      const defs = [
        <?php foreach ($attr_defs as $d): ?>
        { id: <?= (int)$d['id'] ?>, label: '<?= htmlspecialchars($d['etiqueta'], ENT_QUOTES, 'UTF-8') ?>', tipo: '<?= htmlspecialchars($d['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>', card: '<?= htmlspecialchars($d['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>', units: <?= $d['unidades_permitidas_json'] ? $d['unidades_permitidas_json'] : '[]' ?>, unitDef: '<?= htmlspecialchars($d['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>' },
        <?php endforeach; ?>
      ];

      function normalize(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
      function render(list){
        if (!list.length){ dropdown.innerHTML = '<div class="autocomplete-item"><span class="cmp-code">Sin resultados</span></div>'; dropdown.style.display='block'; return; }
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
        console.log('🔍 [KitsEdit] Buscar atributo:', q, '→', out.length);
        render(out);
      }
      function onChoose(def){
        try {
          document.getElementById('add_def_id').value = String(def.id);
          document.getElementById('addAttrInfo').textContent = def.label;
          const sel = document.getElementById('add_unidad');
          sel.innerHTML = '';
          const opt0 = document.createElement('option');
          opt0.value = ''; opt0.textContent = def.unitDef ? `(por defecto: ${def.unitDef})` : '(sin unidad)'; sel.appendChild(opt0);
          if (Array.isArray(def.units)) { def.units.forEach(u => { const o = document.createElement('option'); o.value = u; o.textContent = u; sel.appendChild(o); }); }
          openModal('#modalAddAttr');
          setTimeout(() => { try { document.getElementById('add_valor')?.focus(); } catch(_e){} }, 50);
        } catch (e) {
          console.log('❌ [KitsEdit] Error preparar modal atributo:', e && e.message);
        }
        dropdown.style.display = 'none';
      }
      input.addEventListener('focus', () => filter(input.value));
      input.addEventListener('input', () => filter(input.value));
      document.addEventListener('click', (e) => { if (!dropdown.contains(e.target) && e.target !== input) dropdown.style.display = 'none'; });

      // Editar chip
      document.querySelectorAll('.js-edit-attr').forEach(btn => {
        btn.addEventListener('click', () => {
          const defId = btn.getAttribute('data-attr-id');
          const label = btn.getAttribute('data-label');
          const tipo = btn.getAttribute('data-tipo');
          const unitsJson = btn.getAttribute('data-units');
          const unitDef = btn.getAttribute('data-unidad_def') || '';
          const vals = JSON.parse(btn.getAttribute('data-values') || '[]');
          document.getElementById('edit_def_id').value = defId;
          document.getElementById('editAttrInfo').textContent = label;
          const inputEl = document.getElementById('edit_valor');
          const unitSel = document.getElementById('edit_unidad');
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
          const opt0 = document.createElement('option'); opt0.value=''; opt0.textContent = unitDef ? `(por defecto: ${unitDef})` : '(sin unidad)'; unitSel.appendChild(opt0);
          try { const units = JSON.parse(unitsJson || '[]'); if (Array.isArray(units)) units.forEach(u => { const o=document.createElement('option'); o.value=u; o.textContent=u; unitSel.appendChild(o); }); } catch(_e){}
          openModal('#modalEditAttr');
        });
      });
    })();
  </script>
  <?php endif; ?>
<script>
  console.log('🔍 [KitsEdit] Clases cargadas:', <?= count($clases) ?>);
  console.log('🔍 [KitsEdit] Items disponibles:', <?= count($items) ?>);
  // Verificación de unicidad en vivo para código de kit
  (function initCodigoCheck(){
    const codigoInput = document.getElementById('codigo');
    const statusEl = document.getElementById('codigo_status');
    const saveBtn = document.querySelector('#kit-form button[type="submit"]');
    const isEdit = <?= $is_edit ? 'true' : 'false' ?>;
    const currentId = <?= $is_edit ? (int)$id : 0 ?>;
    if (!codigoInput || !statusEl || !saveBtn) { console.log('⚠️ [KitsEdit] Código checker no inicializado'); return; }

    let lastVal = '';
    let timer = null;

    async function check(val){
      if (!val) { statusEl.textContent = ''; saveBtn.disabled = false; return; }
      statusEl.textContent = 'Verificando…'; statusEl.style.color = '#666';
      try {
        const resp = await fetch('/api/kits-validate-codigo.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ codigo: val, exclude_id: isEdit ? currentId : 0 })
        });
        console.log('📡 [KitsEdit] Check codigo status:', resp.status);
        const data = await resp.json();
        if (data && data.ok && data.unique !== null) {
          if (data.unique) {
            statusEl.textContent = 'Disponible ✅';
            statusEl.style.color = '#2e7d32';
            saveBtn.disabled = false;
            console.log('✅ [KitsEdit] Código disponible');
          } else {
            statusEl.textContent = 'En uso ❌';
            statusEl.style.color = '#c62828';
            saveBtn.disabled = true;
            console.log('❌ [KitsEdit] Código duplicado');
          }
        } else {
          statusEl.textContent = 'No se pudo verificar ⚠️';
          statusEl.style.color = '#b26a00';
          saveBtn.disabled = false;
          console.log('⚠️ [KitsEdit] Respuesta inválida en verificación');
        }
      } catch (e) {
        statusEl.textContent = 'Error al verificar ⚠️';
        statusEl.style.color = '#b26a00';
        saveBtn.disabled = false;
        console.log('❌ [KitsEdit] Error verificación:', e && e.message);
      }
    }

    function debounced(){
      const val = codigoInput.value.trim();
      if (val === lastVal) return;
      lastVal = val;
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => check(val), 350);
    }

    codigoInput.addEventListener('input', debounced);
    codigoInput.addEventListener('blur', () => check(codigoInput.value.trim()));
    if (codigoInput.value.trim()) {
      // Verificar inicial si existe valor
      check(codigoInput.value.trim());
    }
  })();
  // Generador de slug (similar a clases)
  (function initSlugGenerator(){
    const nombreInput = document.getElementById('nombre');
    const slugInput = document.getElementById('slug');
    const btnGenerar = document.getElementById('btn_generar_slug');
    function normalizeBase(str){ return (str || '').toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, ''); }
    function withKitPrefix(val){
      const base = normalizeBase(val).replace(/^kit-+/,'');
      return base.startsWith('kit-') ? base : ('kit-' + base);
    }
    if (btnGenerar && nombreInput && slugInput) {
      btnGenerar.addEventListener('click', function(){
        const v = nombreInput.value.trim();
        if (!v) { alert('Por favor ingresa el nombre del kit primero'); nombreInput.focus(); return; }
        const s = withKitPrefix(v);
        slugInput.value = s;
        console.log('⚡ [KitsEdit] slug generado con botón:', s);
      });
    }
    if (slugInput) {
      slugInput.addEventListener('blur', function(){
        if (slugInput.value) {
          const fixed = withKitPrefix(slugInput.value);
          if (fixed !== slugInput.value) {
            console.log('⚠️ [KitsEdit] corrigiendo slug con prefijo:', fixed);
            slugInput.value = fixed;
          }
        }
      });
    }
  })();
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
<?php
// Ficha técnica (después de modales) para mantener la página ordenada
$attrs_defs = [];
$attrs_vals = [];
if ($is_edit) {
  try {
    $defs_stmt = $pdo->prepare('SELECT d.*, m.orden, m.ui_hint FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
    $defs_stmt->execute(['kit']);
    $attrs_defs = $defs_stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) { $attrs_defs = []; }
  try {
    $vals_stmt = $pdo->prepare('SELECT * FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? ORDER BY orden ASC, id ASC');
    $vals_stmt->execute(['kit', $id]);
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
  <h3>Ficha técnica</h3>
  <p class="hint">Atributos técnicos mapeados para el tipo Kit.</p>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="save_attrs" />
    <div class="form-grid">
      <?php foreach ($attrs_defs as $def):
        $aid = (int)$def['id'];
        $tipo = $def['tipo_dato'];
        $card = $def['cardinalidad'];
        $label = $def['etiqueta'];
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
          <?php if ($tipo === 'boolean'): ?>
            <small>Use 1 o 0 separados por comas.</small>
            <input type="text" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej: 1, 0, 1" />
          <?php elseif ($tipo === 'json'): ?>
            <textarea name="attr_<?= $aid ?>" rows="3" placeholder='["valor1","valor2"]'><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></textarea>
          <?php else: ?>
            <input type="text" name="attr_<?= $aid ?>" value="<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>" placeholder="Separar múltiples con comas" />
          <?php endif; ?>
          <?php if (!empty($perms) || $unidad_def): ?>
            <small>Unidad (aplica a valores cuantitativos):</small>
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
        <?php if (!empty($def['descripcion'])): ?>
          <small class="help-text"><?= htmlspecialchars($def['descripcion'], ENT_QUOTES, 'UTF-8') ?></small>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="actions" style="margin-top:8px;">
      <button type="submit" class="btn">Guardar ficha</button>
    </div>
  </form>
  <script>console.log('🔍 [KitsEdit] Atributos cargados:', <?= isset($attrs_defs) ? count($attrs_defs) : 0 ?>);</script>
  <style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
    @media (max-width: 720px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</div>
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
