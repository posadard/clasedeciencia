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

// Seguridad (estructurada) soporte: detectar columna JSON y prellenar
$has_seguridad_col = false;
$cmp_seguridad = ['edad_min' => null, 'edad_max' => null, 'notas' => null];
try {
  $colChk = $pdo->query("SHOW COLUMNS FROM kit_items LIKE 'seguridad'");
  $has_seguridad_col = (bool)$colChk->fetch(PDO::FETCH_ASSOC);
  if ($material && isset($material['seguridad']) && $material['seguridad'] !== null) {
    $tmp = json_decode($material['seguridad'], true);
    if (is_array($tmp)) { $cmp_seguridad = array_merge($cmp_seguridad, $tmp); }
  }
  if (!$has_seguridad_col) {
    echo "<script>console.log('⚠️ [ComponentesEdit] kit_items.seguridad (JSON) no disponible');</script>";
  }
} catch (PDOException $e) {
  echo "<script>console.log('⚠️ [ComponentesEdit] Error verificando columna seguridad:', " . json_encode($e->getMessage()) . ");</script>";
}

$categorias = get_material_categories($pdo);

// Cargar manuales del componente para UI de publicación (solo en edición)
$cmp_manuals = [];
if ($is_edit) {
  try {
    $stmM = $pdo->prepare('SELECT id, slug, version, status, idioma, time_minutes, dificultad_ensamble, updated_at, published_at FROM kit_manuals WHERE item_id = ? ORDER BY idioma, version DESC, id DESC');
    $stmM->execute([$id]);
    $cmp_manuals = $stmM->fetchAll(PDO::FETCH_ASSOC) ?: [];
    echo "<script>console.log('🔍 [ComponentesEdit] Manuales cargados:', " . (int)count($cmp_manuals) . ");</script>";
  } catch (PDOException $e) {
    echo "<script>console.log('❌ [ComponentesEdit] Error cargando manuales:', " . json_encode($e->getMessage()) . ");</script>";
  }
}

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
    // Seguridad estructurada (similar a Kits)
    $seg_edad_min = (isset($_POST['seg_edad_min']) && $_POST['seg_edad_min'] !== '') ? (int)$_POST['seg_edad_min'] : null;
    $seg_edad_max = (isset($_POST['seg_edad_max']) && $_POST['seg_edad_max'] !== '') ? (int)$_POST['seg_edad_max'] : null;
    $seg_notas = isset($_POST['seg_notas']) ? trim((string)$_POST['seg_notas']) : '';
    if ($seg_notas === '') { $seg_notas = null; }
    $seguridad_json = null;
    if ($seg_edad_min !== null || $seg_edad_max !== null || $seg_notas !== null) {
      $seguridad_json = json_encode([
        'edad_min' => $seg_edad_min,
        'edad_max' => $seg_edad_max,
        'notas' => $seg_notas
      ], JSON_UNESCAPED_UNICODE);
    }
    $unidad = trim($_POST['unidad'] ?? 'pcs');
    $descripcion_html = isset($_POST['descripcion_html']) ? (string)$_POST['descripcion_html'] : null;
    $foto_url = trim($_POST['foto_url'] ?? '');

    if ($nombre_comun === '') $errores[] = 'El nombre común es obligatorio';
    if ($categoria_id <= 0) $errores[] = 'La categoría es obligatoria';
    if ($foto_url !== '' && !preg_match('/^https?:\/\//i', $foto_url)) { $errores[] = 'La URL de la foto debe iniciar con http:// o https://'; }
    if ($foto_url !== '' && strlen($foto_url) > 255) { $errores[] = 'La URL de la foto supera 255 caracteres'; }

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
          if ($has_seguridad_col) {
            $sql = "UPDATE kit_items SET nombre_comun = ?, sku = ?, categoria_id = ?, advertencias_seguridad = ?, unidad = ?, descripcion_html = ?, foto_url = ?, seguridad = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null), $seguridad_json, $id]);
          } else {
            $sql = "UPDATE kit_items SET nombre_comun = ?, sku = ?, categoria_id = ?, advertencias_seguridad = ?, unidad = ?, descripcion_html = ?, foto_url = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null), $id]);
            echo "<script>console.log('⚠️ [ComponentesEdit] Seguridad JSON no guardada (columna faltante)');</script>";
          }
        } else {
          if ($has_seguridad_col) {
            $sql = "INSERT INTO kit_items (nombre_comun, sku, categoria_id, advertencias_seguridad, unidad, descripcion_html, foto_url, seguridad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null), $seguridad_json]);
          } else {
            $sql = "INSERT INTO kit_items (nombre_comun, sku, categoria_id, advertencias_seguridad, unidad, descripcion_html, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null)]);
            echo "<script>console.log('⚠️ [ComponentesEdit] Seguridad JSON omitida en INSERT (columna faltante)');</script>";
          }
          $id = (int)$pdo->lastInsertId();
        }
        echo "<script>console.log('✅ [Admin] Componente guardado');</script>";

        // Sincronizar publicación de manuales del componente (dual-list) antes de redirigir
        try {
          $posted_manuals = isset($_POST['manuals_published']) ? (array)$_POST['manuals_published'] : [];
          $selected_ids = array_values(array_filter(array_map(function($v){ return ctype_digit((string)$v) ? (int)$v : null; }, $posted_manuals)));
          echo "<script>console.log('🔍 [ComponentesEdit] manuals_published POST:', " . json_encode($selected_ids) . ");</script>";
          // Obtener todos los manuales actuales del componente
          $stmC = $pdo->prepare('SELECT id FROM kit_manuals WHERE item_id = ?');
          $stmC->execute([$id]);
          $all = $stmC->fetchAll(PDO::FETCH_ASSOC) ?: [];
          $all_ids = array_map(function($r){ return (int)$r['id']; }, $all);
          // Publicar seleccionados
          if (!empty($selected_ids)) {
            $ph = implode(',', array_fill(0, count($selected_ids), '?'));
            $sqlP = "UPDATE kit_manuals SET status = 'published', published_at = IFNULL(published_at, NOW()) WHERE id IN ($ph) AND item_id = ?";
            $params = array_merge($selected_ids, [$id]);
            $pdo->prepare($sqlP)->execute($params);
          }
          // Despublicar los no seleccionados
          $to_unpub = array_values(array_diff($all_ids, $selected_ids));
          if (!empty($to_unpub)) {
            $ph2 = implode(',', array_fill(0, count($to_unpub), '?'));
            $sqlU = "UPDATE kit_manuals SET status = 'discontinued' WHERE id IN ($ph2) AND item_id = ?";
            $params2 = array_merge($to_unpub, [$id]);
            $pdo->prepare($sqlU)->execute($params2);
          }
          echo "<script>console.log('✅ [ComponentesEdit] Manuales sincronizados');</script>";
        } catch (Exception $e) {
          echo "<script>console.log('❌ [ComponentesEdit] Error sincronizando manuales:', " . json_encode($e->getMessage()) . ");</script>";
        }

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

<form method="POST" id="cmp-form" class="compact-form">
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
  <div class="card" style="margin:12px 0; padding:12px;">
    <h3>Seguridad (estructurada)</h3>
    <?php if (!$has_seguridad_col): ?>
      <small class="help-text">Requiere columna <strong>seguridad (JSON)</strong> en <em>kit_items</em>. Ejecuta el ALTER TABLE antes de usar estos campos.</small>
    <?php else: ?>
      <small class="help-text">Campos similares al editor de Kits (edad mínima/máxima y notas).</small>
    <?php endif; ?>
    <div class="field-inline">
      <div class="form-group">
        <label for="seg_edad_min">Edad mínima</label>
        <input type="number" id="seg_edad_min" name="seg_edad_min" min="0" value="<?= htmlspecialchars(($cmp_seguridad['edad_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>
      <div class="form-group">
        <label for="seg_edad_max">Edad máxima</label>
        <input type="number" id="seg_edad_max" name="seg_edad_max" min="0" value="<?= htmlspecialchars(($cmp_seguridad['edad_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>
    </div>
    <div class="form-group">
      <label for="seg_notas">Notas de seguridad</label>
      <textarea id="seg_notas" name="seg_notas" rows="3" placeholder="Advertencias y recomendaciones adicionales del componente"><?= htmlspecialchars(($cmp_seguridad['notas'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <script>
      console.log('🔍 [ComponentesEdit] Seguridad JSON soportada:', <?= $has_seguridad_col ? 'true' : 'false' ?>);
    </script>
  </div>
  <div class="form-group">
    <label for="unidad">Unidad</label>
    <input type="text" id="unidad" name="unidad" value="<?= htmlspecialchars($material['unidad'] ?? 'pcs', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej: pcs, g, ml" />
  </div>
  <div class="form-group">
    <label for="descripcion_html">Descripción HTML</label>
    <textarea id="descripcion_html" name="descripcion_html" rows="6" placeholder="Se renderiza como HTML en la página del componente."><?= htmlspecialchars($material['descripcion_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="help-text">Usa etiquetas básicas; se mostrará tal cual.</small>
  </div>
  <div class="form-group">
    <label for="foto_url">URL de la foto</label>
    <input type="text" id="foto_url" name="foto_url" value="<?= htmlspecialchars($material['foto_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="https://..." />
    <small class="help-text">Enlace http(s) a la imagen representativa del componente.</small>
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
<div class="card mt-xl">
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
      <div class="attr-actions" style="margin-top:6px;">
        <button type="button" class="btn btn-secondary" id="btn_create_attr_cmp">➕ Crear atributo</button>
      </div>
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

<!-- Manuales del Componente (Dual-list publicar/despublicar) -->
<div class="card mt-xl">
  <h3>Manuales del Componente</h3>
  <small class="help-text">Publica o despublica manuales; crea nuevos desde aquí.</small>
  <div class="inline-row" style="flex-wrap: wrap; gap:8px; margin:8px 0;">
    <a class="btn" href="/admin/kits/manuals/edit.php?item_id=<?= (int)$id ?>">+ Nuevo Manual</a>
    <a class="btn btn-secondary" href="/admin/kits/manuals/index.php?item_id=<?= (int)$id ?>">Ver todos</a>
  </div>
  <div class="dual-listbox-container">
    <div class="listbox-panel">
      <div class="listbox-header"><strong>Disponibles</strong> <span id="man-available-count" class="counter">(0)</span></div>
      <input type="text" id="search-manuales" class="listbox-search" placeholder="🔍 Buscar manuales...">
      <div class="listbox-content" id="available-manuales">
        <?php foreach ($cmp_manuals as $m): ?>
          <?php if (strtolower($m['status'] ?? '') !== 'published'): ?>
            <div class="competencia-item" data-id="<?= (int)$m['id'] ?>" data-slug="<?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?>" data-idioma="<?= htmlspecialchars($m['idioma'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-status="<?= htmlspecialchars($m['status'] ?? 'draft', ENT_QUOTES, 'UTF-8') ?>" onclick="selectManualItem(this)">
              <span class="comp-nombre"><?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comp-codigo">
                <?= htmlspecialchars(($m['idioma'] ?? 'es') . ' · v' . ($m['version'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>
                <em class="status-badge status-<?= htmlspecialchars(strtolower($m['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($m['status'] ?? 'draft', ENT_QUOTES, 'UTF-8') ?></em>
              </span>
              <a class="edit-component" title="Editar" href="/admin/kits/manuals/edit.php?id=<?= (int)$m['id'] ?>" onclick="event.stopPropagation();">✏️</a>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="listbox-buttons">
      <button type="button" onclick="moveAllManuales(true)" title="Publicar todos">➡️</button>
      <button type="button" onclick="moveAllManuales(false)" title="Quitar publicación de todos">⬅️</button>
    </div>
    <div class="listbox-panel">
      <div class="listbox-header"><strong>Publicados</strong> <span id="man-selected-count" class="counter">(0)</span></div>
      <div class="listbox-content" id="selected-manuales">
        <?php foreach ($cmp_manuals as $m): ?>
          <?php if (strtolower($m['status'] ?? '') === 'published'): ?>
            <div class="competencia-item selected" data-id="<?= (int)$m['id'] ?>" data-slug="<?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?>" data-idioma="<?= htmlspecialchars($m['idioma'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-status="published" onclick="deselectManualItem(this)">
              <span class="comp-nombre"><?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comp-codigo"><?= htmlspecialchars(($m['idioma'] ?? 'es') . ' · v' . ($m['version'] ?? '1'), ENT_QUOTES, 'UTF-8') ?></span>
              <button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectManualItem(this.parentElement)">×</button>
              <a class="edit-component" title="Editar" href="/admin/kits/manuals/edit.php?id=<?= (int)$m['id'] ?>" onclick="event.stopPropagation();">✏️</a>
              <?php if (!empty($material['slug'])): ?>
                <a class="edit-component" title="Ver público" target="_blank" href="/<?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?>" onclick="event.stopPropagation();">🔗</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <small class="hint">Haz clic para quitar publicación. Usa × para quitar.</small>
    </div>
    <!-- Hidden inputs (fuera del form) apuntan a cmp-form -->
    <div id="manuales-hidden">
      <?php foreach ($cmp_manuals as $m): if (strtolower($m['status'] ?? '') === 'published'): ?>
        <input type="hidden" name="manuals_published[]" value="<?= (int)$m['id'] ?>" form="cmp-form">
      <?php endif; endforeach; ?>
    </div>
  </div>
  <script>
    function updateManualCounts(){
      const a = document.querySelectorAll('#available-manuales .competencia-item:not(.hidden)').length;
      const s = document.querySelectorAll('#selected-manuales .competencia-item').length;
      document.getElementById('man-available-count').textContent = `(${a})`;
      document.getElementById('man-selected-count').textContent = `(${s})`;
      console.log('🔍 [ManualesCmp] Disponibles:', a, 'Publicados:', s);
    }
    function addManualHidden(id){
      const wrap = document.getElementById('manuales-hidden');
      if (!wrap.querySelector(`input[name="manuals_published[]"][value="${id}"]`)){
        const i = document.createElement('input'); i.type='hidden'; i.name='manuals_published[]'; i.value=id; i.setAttribute('form','cmp-form'); wrap.appendChild(i);
      }
    }
    function removeManualHidden(id){
      const wrap = document.getElementById('manuales-hidden');
      wrap.querySelectorAll(`input[name="manuals_published[]"][value="${id}"]`).forEach(n => n.remove());
    }
    function removeStatusBadge(el){ const code = el.querySelector('.comp-codigo'); const b = code && code.querySelector('.status-badge'); if (b) b.remove(); }
    function addStatusBadge(el, status){ const code = el.querySelector('.comp-codigo'); if (!code) return; let b = code.querySelector('.status-badge'); if (!b){ b=document.createElement('em'); b.className='status-badge'; code.appendChild(b);} b.className='status-badge status-'+status; b.textContent=status; }
    function selectManualItem(el){
      const id = el.getAttribute('data-id');
      el.classList.add('selected'); el.dataset.status='published';
      el.onclick = function(){ deselectManualItem(el); };
      const rm = document.createElement('button'); rm.type='button'; rm.className='remove-btn'; rm.textContent='×'; rm.onclick=function(ev){ ev.stopPropagation(); deselectManualItem(el); }; el.appendChild(rm);
      removeStatusBadge(el);
      document.getElementById('selected-manuales').appendChild(el);
      addManualHidden(id);
      updateManualCounts();
      console.log('✅ [ManualesCmp] Publicado manual', id);
    }
    function deselectManualItem(el){
      const id = el.getAttribute('data-id');
      el.classList.remove('selected'); el.dataset.status='discontinued';
      el.querySelectorAll('.remove-btn').forEach(b => b.remove());
      el.onclick = function(){ selectManualItem(el); };
      addStatusBadge(el, 'discontinued');
      document.getElementById('available-manuales').appendChild(el);
      removeManualHidden(id);
      updateManualCounts();
      console.log('⚠️ [ManualesCmp] Despublicado manual', id);
    }
    function moveAllManuales(add){
      const from = add ? document.querySelectorAll('#available-manuales .competencia-item:not(.hidden)') : document.querySelectorAll('#selected-manuales .competencia-item');
      Array.from(from).forEach(el => add ? selectManualItem(el) : deselectManualItem(el));
      console.log(add ? '✅ [ManualesCmp] Publicados todos' : '⚠️ [ManualesCmp] Despublicados todos');
    }
    document.addEventListener('DOMContentLoaded', function(){
      const search = document.getElementById('search-manuales');
      if (search){
        search.addEventListener('input', function(){
          const q = (this.value||'').toLowerCase();
          document.querySelectorAll('#available-manuales .competencia-item').forEach(el => {
            const txt = (el.dataset.slug + ' ' + (el.dataset.idioma||'')).toLowerCase();
            el.classList.toggle('hidden', !!q && !txt.includes(q));
          });
          updateManualCounts();
        });
      }
      updateManualCounts();
    });
  </script>
</div>

<!-- Modal Editar Atributo (Componente) -->
<div class="modal-overlay" id="modalEditAttrCmp">
     <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalEditAttrCmpTitle">
    <div class="modal-header">
      <h4 id="modalEditAttrCmpTitle">Editar atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalEditAttrCmp">✖</button>
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
<div class="modal-overlay" id="modalAddAttrCmp">
     <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalAddAttrCmpTitle">
    <div class="modal-header">
      <h4 id="modalAddAttrCmpTitle">Agregar atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalAddAttrCmp">✖</button>
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

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddAttrCmp">Cancelar</button>
        <button type="submit" class="btn">Agregar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Crear Definición de Atributo (Componente) -->
<div class="modal-overlay" id="modalCreateAttrCmp">
         <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalCreateAttrCmpTitle">
    <div class="modal-header">
      <h4 id="modalCreateAttrCmpTitle">Crear nuevo atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalCreateAttrCmp">✖</button>
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

<script>
  // Utilidades de modal (compartidas)
  function openModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.add('active'); console.log('🔍 [ComponentesEdit] Abre modal', sel); }
  }
  function closeModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.remove('active'); console.log('🔍 [ComponentesEdit] Cierra modal', sel); }
  }
  document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const t = e.currentTarget.getAttribute('data-target');
      if (t) closeModal(t);
    });
  });
    document.querySelectorAll('.modal-overlay').forEach(b => {
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

    // Botón para crear atributo directamente
    const btnCreate = document.getElementById('btn_create_attr_cmp');
    if (btnCreate) {
      btnCreate.addEventListener('click', () => {
        try {
          const val = (input && input.value ? input.value.trim() : '');
          document.getElementById('create_etiqueta_cmp').value = val;
          document.getElementById('create_clave_cmp').value = '';
          document.getElementById('create_tipo_cmp').value = 'string';
          document.getElementById('create_card_cmp').value = 'one';
          document.getElementById('create_unidad_cmp').value = '';
          document.getElementById('create_unidades_cmp').value = '';
          openModal('#modalCreateAttrCmp');
          setTimeout(() => { try { document.getElementById('create_etiqueta_cmp')?.focus(); } catch(_e){} }, 50);
          console.log('🔍 [ComponentesEdit] Abrir crear atributo (botón)', val);
        } catch(e) { console.log('❌ [ComponentesEdit] Error abrir crear atributo (botón):', e && e.message); }
      });
    }

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
  // Fallback binder to ensure the create-attribute button opens the modal
  (function bindCreateAttrButtonCmp(){
    const btn = document.getElementById('btn_create_attr_cmp');
    if (!btn) { console.log('⚠️ [ComponentesEdit] Botón crear atributo no encontrado'); return; }
    btn.addEventListener('click', function(){
      try {
        const q = (document.getElementById('attr_search_cmp')?.value || '').trim();
        const et = document.getElementById('create_etiqueta_cmp');
        const cl = document.getElementById('create_clave_cmp');
        const tp = document.getElementById('create_tipo_cmp');
        const cd = document.getElementById('create_card_cmp');
        const ud = document.getElementById('create_unidad_cmp');
        const ups = document.getElementById('create_unidades_cmp');
        if (et) et.value = q;
        if (cl) cl.value = '';
        if (tp) tp.value = 'string';
        if (cd) cd.value = 'one';
        if (ud) ud.value = '';
        if (ups) ups.value = '';
        openModal('#modalCreateAttrCmp');
        setTimeout(() => { try { et?.focus(); } catch(_e){} }, 50);
        console.log('✅ [ComponentesEdit] Modal crear atributo abierto');
      } catch(e) { console.log('❌ [ComponentesEdit] Error abrir modal crear atributo:', e && e.message); }
    });
  })();
</script>
<!-- Editor: CKEditor 4 for Componente descripcion_html (match Kits) -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  (function initCKEComponent(){
    try {
      if (window.CKEDITOR) {
        CKEDITOR.replace('descripcion_html', {
          height: 500,
          removePlugins: 'elementspath',
          resize_enabled: true,
          contentsCss: ['/assets/css/style.css', '/assets/css/article-content.css'],
          bodyClass: 'article-body'
        });
        console.log('✅ [ComponentesEdit] CKEditor 4 cargado');
      } else {
        console.log('⚠️ [ComponentesEdit] CKEditor no disponible, usando textarea simple');
      }
    } catch(e) {
      console.log('❌ [ComponentesEdit] Error iniciando CKEditor:', e && e.message);
    }
  })();
  (function hideCkeWarningsCss(){
    try {
      const style = document.createElement('style');
      style.setAttribute('data-cke-warn-hide','1');
      style.textContent = `
        .cke_notification.cke_notification_warning,
        .cke_upgrade_notice,
        .cke_browser_warning,
        .cke_panel_warning,
        .cke_warning { display: none !important; }
      `;
      document.head.appendChild(style);
      console.log('✅ [ComponentesEdit] CKEditor warnings ocultos por CSS');
    } catch(e) {
      console.log('⚠️ [ComponentesEdit] No se pudo inyectar CSS para warnings:', e && e.message);
    }
  })();
</script>
<?php endif; ?>

<?php include '../footer.php'; ?>
