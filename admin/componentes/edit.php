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
$media_primary = [
  'titulo' => '',
  'descripcion' => '',
  'schema_role' => 'primary',
  'mime_type' => 'image/webp',
  'width' => '',
  'height' => '',
  'upload_date' => '',
  'creator_name' => '',
  'in_language' => 'es-CO'
];
if ($is_edit) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM kit_items WHERE id = ?");
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $material = null;
  }

  try {
    $stRm = $pdo->prepare("SELECT * FROM recursos_multimedia WHERE item_id = ? AND schema_role = 'primary' ORDER BY sort_order ASC, id ASC LIMIT 1");
    $stRm->execute([$id]);
    $rm = $stRm->fetch(PDO::FETCH_ASSOC);
    if ($rm) {
      $media_primary['titulo'] = (string)($rm['titulo'] ?? '');
      $media_primary['descripcion'] = (string)($rm['descripcion'] ?? '');
      $media_primary['schema_role'] = (string)($rm['schema_role'] ?? 'primary');
      $media_primary['mime_type'] = (string)($rm['mime_type'] ?? 'image/webp');
      $media_primary['width'] = isset($rm['width']) ? (string)$rm['width'] : '';
      $media_primary['height'] = isset($rm['height']) ? (string)$rm['height'] : '';
      $media_primary['upload_date'] = !empty($rm['upload_date']) ? (string)$rm['upload_date'] : '';
      $media_primary['creator_name'] = (string)($rm['creator_name'] ?? '');
      $media_primary['in_language'] = (string)($rm['in_language'] ?? 'es-CO');
    }
  } catch (PDOException $e) {
    // DB antigua sin columnas extendidas
  }
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
    $unidad = trim($_POST['unidad'] ?? 'pcs');
    $descripcion_html = isset($_POST['descripcion_html']) ? (string)$_POST['descripcion_html'] : null;
    $foto_url = trim($_POST['foto_url'] ?? '');
    $media_titulo = isset($_POST['media_titulo']) ? trim((string)$_POST['media_titulo']) : '';
    $media_descripcion = isset($_POST['media_descripcion']) ? trim((string)$_POST['media_descripcion']) : '';
    $media_schema_role = isset($_POST['media_schema_role']) ? trim((string)$_POST['media_schema_role']) : 'primary';
    $media_mime_type = isset($_POST['media_mime_type']) ? trim((string)$_POST['media_mime_type']) : '';
    $media_width = (isset($_POST['media_width']) && $_POST['media_width'] !== '') ? (int)$_POST['media_width'] : null;
    $media_height = (isset($_POST['media_height']) && $_POST['media_height'] !== '') ? (int)$_POST['media_height'] : null;
    $media_upload_date = isset($_POST['media_upload_date']) ? trim((string)$_POST['media_upload_date']) : '';
    $media_creator_name = isset($_POST['media_creator_name']) ? trim((string)$_POST['media_creator_name']) : '';
    $media_in_language = isset($_POST['media_in_language']) ? trim((string)$_POST['media_in_language']) : 'es-CO';

    if ($nombre_comun === '') $errores[] = 'El nombre común es obligatorio';
    if ($categoria_id <= 0) $errores[] = 'La categoría es obligatoria';
    if ($foto_url !== '' && !preg_match('/^(https?:\/\/|\/assets\/images\/uploads\/)/i', $foto_url)) { $errores[] = 'La URL de la foto debe iniciar con http://, https:// o /assets/images/uploads/'; }
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
          $sql = "UPDATE kit_items SET nombre_comun = ?, sku = ?, categoria_id = ?, advertencias_seguridad = ?, unidad = ?, descripcion_html = ?, foto_url = ? WHERE id = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null), $id]);
        } else {
          $sql = "INSERT INTO kit_items (nombre_comun, sku, categoria_id, advertencias_seguridad, unidad, descripcion_html, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $descripcion_html, ($foto_url !== '' ? $foto_url : null)]);
          $id = (int)$pdo->lastInsertId();
        }
        echo "<script>console.log('✅ [Admin] Componente guardado');</script>";

        if (!empty($foto_url)) {
          try {
            $role = in_array($media_schema_role, ['primary','gallery','tutorial','download','external'], true) ? $media_schema_role : 'primary';
            $upload_dt = null;
            if ($media_upload_date !== '') {
              $ts = strtotime($media_upload_date);
              if ($ts) { $upload_dt = date('Y-m-d H:i:s', $ts); }
            }
            $sel = $pdo->prepare("SELECT id FROM recursos_multimedia WHERE item_id = ? AND schema_role = 'primary' ORDER BY sort_order ASC, id ASC LIMIT 1");
            $sel->execute([$id]);
            $rm_id = (int)$sel->fetchColumn();
            if ($rm_id > 0) {
              $upd = $pdo->prepare("UPDATE recursos_multimedia SET tipo='imagen', url=?, titulo=?, descripcion=?, sort_order=0, schema_role=?, mime_type=?, width=?, height=?, upload_date=?, in_language=?, creator_name=? WHERE id=?");
              $upd->execute([$foto_url, ($media_titulo !== '' ? $media_titulo : $nombre_comun), ($media_descripcion !== '' ? $media_descripcion : ''), $role, ($media_mime_type !== '' ? $media_mime_type : 'image/webp'), $media_width, $media_height, $upload_dt, ($media_in_language !== '' ? $media_in_language : 'es-CO'), ($media_creator_name !== '' ? $media_creator_name : 'Clase de Ciencia'), $rm_id]);
            } else {
              $insRm = $pdo->prepare("INSERT INTO recursos_multimedia (item_id, tipo, url, titulo, descripcion, sort_order, schema_role, mime_type, width, height, upload_date, in_language, creator_name) VALUES (?, 'imagen', ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?)");
              $insRm->execute([$id, $foto_url, ($media_titulo !== '' ? $media_titulo : $nombre_comun), ($media_descripcion !== '' ? $media_descripcion : ''), $role, ($media_mime_type !== '' ? $media_mime_type : 'image/webp'), $media_width, $media_height, $upload_dt, ($media_in_language !== '' ? $media_in_language : 'es-CO'), ($media_creator_name !== '' ? $media_creator_name : 'Clase de Ciencia')]);
            }
          } catch (PDOException $e) {
            try {
              $sel = $pdo->prepare("SELECT id FROM recursos_multimedia WHERE item_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
              $sel->execute([$id]);
              $rm_id = (int)$sel->fetchColumn();
              if ($rm_id > 0) {
                $upd = $pdo->prepare("UPDATE recursos_multimedia SET tipo='imagen', url=?, titulo=?, descripcion=?, sort_order=0 WHERE id=?");
                $upd->execute([$foto_url, ($media_titulo !== '' ? $media_titulo : $nombre_comun), ($media_descripcion !== '' ? $media_descripcion : ''), $rm_id]);
              } else {
                $insRm = $pdo->prepare("INSERT INTO recursos_multimedia (clase_id, tipo, url, titulo, descripcion, sort_order) VALUES (NULL, 'imagen', ?, ?, ?, 0)");
                $insRm->execute([$foto_url, ($media_titulo !== '' ? $media_titulo : $nombre_comun), ($media_descripcion !== '' ? $media_descripcion : '')]);
              }
            } catch (PDOException $e2) {
              echo '<script>console.log("⚠️ [ComponentesEdit] No se pudo guardar metadata multimedia:",' . json_encode($e2->getMessage()) . ');</script>';
            }
          }
        }

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
  <div class="page-header-actions" style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
    <button type="button" class="btn ia-assistant-btn" id="btn_open_ia_componente_modal">Asistente <span class="ia-word">IA</span> de componente</button>
  </div>
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

<style>
  .ia-class-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1200;
    padding: 20px;
  }
  .ia-class-modal.active { display: flex; }
  .ia-class-dialog {
    width: min(980px, 96vw);
    max-height: 92vh;
    overflow: hidden;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 12px 45px rgba(2, 6, 23, 0.35);
    display: grid;
    grid-template-rows: auto 1fr auto;
  }
  .ia-class-head {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ia-class-head h3 { margin: 0; font-size: 18px; }
  .ia-class-body {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    min-height: 420px;
  }
  .ia-class-chat {
    border-right: 1px solid #e5e7eb;
    display: grid;
    grid-template-rows: 1fr auto;
    min-height: 0;
  }
  .ia-class-messages {
    overflow-y: auto;
    padding: 14px;
    background: #f8fafc;
  }
  .ia-msg {
    margin-bottom: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    line-height: 1.35;
    white-space: pre-wrap;
    font-size: 14px;
  }
  .ia-msg.user { background: #dbeafe; margin-left: 12%; }
  .ia-msg.assistant { background: #eef2ff; margin-right: 10%; }
  .ia-msg.system { background: #ecfeff; border: 1px solid #bae6fd; }
  .ia-class-input {
    border-top: 1px solid #e5e7eb;
    padding: 12px;
    display: grid;
    gap: 8px;
  }
  .ia-class-input textarea {
    width: 100%;
    min-height: 70px;
    resize: vertical;
  }
  .ia-class-input-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .ia-class-side {
    padding: 14px;
    overflow-y: auto;
    background: #fff;
  }
  .ia-class-side h4 { margin: 0 0 10px 0; }
  .ia-class-side p { margin: 0 0 10px 0; color: #475569; }
  .ia-suggestion-json {
    font-family: Consolas, monospace;
    font-size: 12px;
    background: #0f172a;
    color: #e2e8f0;
    padding: 10px;
    border-radius: 8px;
    min-height: 170px;
    max-height: 280px;
    overflow: auto;
    white-space: pre;
  }
  .ia-class-foot {
    border-top: 1px solid #e5e7eb;
    padding: 12px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    flex-wrap: wrap;
  }
  .ia-content-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
  }
  .ia-content-preview {
    font-family: Consolas, monospace;
    font-size: 12px;
    background: #020617;
    color: #e2e8f0;
    padding: 10px;
    border-radius: 8px;
    min-height: 170px;
    max-height: 260px;
    overflow: auto;
    white-space: pre;
  }
  .ia-content-settings {
    margin-top: 10px;
    border-top: 1px dashed #cbd5e1;
    padding-top: 10px;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
  }
  @media (max-width: 900px) {
    .ia-class-body { grid-template-columns: 1fr; }
    .ia-class-chat { border-right: none; border-bottom: 1px solid #e5e7eb; }
  }
</style>

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
  <div class="form-group">
    <label for="unidad">Unidad</label>
    <input type="text" id="unidad" name="unidad" value="<?= htmlspecialchars($material['unidad'] ?? 'pcs', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej: pcs, g, ml" />
  </div>
  <div class="form-group">
    <label for="descripcion_html" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <span>Descripción HTML</span>
      <button type="button" class="btn btn-secondary ia-assistant-btn" id="btn_open_ia_componente_content_modal">Asistente <span class="ia-word">IA</span> de contenido</button>
    </label>
    <textarea id="descripcion_html" name="descripcion_html" rows="6" placeholder="Se renderiza como HTML en la página del componente."><?= htmlspecialchars($material['descripcion_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="help-text">Usa etiquetas básicas; se mostrará tal cual.</small>
  </div>
  <div class="form-group">
    <label for="foto_url">URL de la foto</label>
    <div class="image-field-row">
      <input type="text" id="foto_url" name="foto_url" value="<?= htmlspecialchars($material['foto_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="https://..." />
      <button type="button" class="btn btn-secondary js-image-picker-trigger" data-target-input="foto_url" data-preset="componente-thumb" data-entity="componente" data-meta-title-input="media_titulo" data-meta-description-input="media_descripcion" data-meta-mime-input="media_mime_type" data-meta-width-input="media_width" data-meta-height-input="media_height" data-meta-upload-date-input="media_upload_date" data-meta-role-input="media_schema_role" data-meta-creator-input="media_creator_name" data-meta-language-input="media_in_language" data-meta-title-source="nombre_comun" data-meta-description-source="descripcion_html">📷 Subir y editar</button>
    </div>
    <?php $role_val = $media_primary['schema_role'] ?: 'primary'; ?>
    <input type="hidden" id="media_titulo" name="media_titulo" value="<?= htmlspecialchars($media_primary['titulo'] ?: ($material['nombre_comun'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_descripcion" name="media_descripcion" value="<?= htmlspecialchars($media_primary['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_schema_role" name="media_schema_role" value="<?= htmlspecialchars($role_val, ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_mime_type" name="media_mime_type" value="<?= htmlspecialchars($media_primary['mime_type'] ?: 'image/webp', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_width" name="media_width" value="<?= htmlspecialchars((string)$media_primary['width'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_height" name="media_height" value="<?= htmlspecialchars((string)$media_primary['height'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_upload_date" name="media_upload_date" value="<?= htmlspecialchars((string)$media_primary['upload_date'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_creator_name" name="media_creator_name" value="<?= htmlspecialchars((string)$media_primary['creator_name'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" id="media_in_language" name="media_in_language" value="<?= htmlspecialchars((string)($media_primary['in_language'] ?: 'es-CO'), ENT_QUOTES, 'UTF-8') ?>" />
    <small class="help-text">Enlace http(s) a la imagen representativa del componente.</small>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn" onclick="this.form.action.value='';"><?= $is_edit ? 'Actualizar' : 'Crear' ?></button>
    <a href="/admin/componentes/index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<div class="ia-class-modal" id="ia_componente_modal" aria-hidden="true">
  <div class="ia-class-dialog" role="dialog" aria-modal="true" aria-labelledby="ia_componente_modal_title">
    <div class="ia-class-head">
      <h3 id="ia_componente_modal_title">Asistente IA: creador de componente</h3>
      <button type="button" class="btn btn-secondary" id="ia_componente_modal_close_top">Cerrar</button>
    </div>
    <div class="ia-class-body">
      <div class="ia-class-chat">
        <div class="ia-class-messages" id="ia_componente_messages"></div>
        <div class="ia-class-input">
          <textarea id="ia_componente_user_input" placeholder="Ejemplo: Necesito un componente para medir pH, seguro para grado 8°, con uso en laboratorio escolar."></textarea>
          <div class="ia-class-input-actions">
            <button type="button" class="btn" id="ia_componente_send">Enviar a IA</button>
            <button type="button" class="btn btn-secondary" id="ia_componente_quick_brief">Generar borrador completo</button>
          </div>
        </div>
      </div>
      <aside class="ia-class-side">
        <h4>Borrador estructurado</h4>
        <p>Cuando la IA devuelva JSON válido, podrás aplicarlo a los campos del componente.</p>
        <pre class="ia-suggestion-json" id="ia_componente_json_preview">Esperando sugerencia...</pre>
      </aside>
    </div>
    <div class="ia-class-foot">
      <button type="button" class="btn btn-secondary" id="ia_componente_reset">Limpiar borrador</button>
      <button type="button" class="btn" id="ia_componente_apply" disabled>Aplicar al formulario</button>
    </div>
  </div>
</div>

<div class="ia-class-modal" id="ia_componente_content_modal" aria-hidden="true">
  <div class="ia-class-dialog" role="dialog" aria-modal="true" aria-labelledby="ia_componente_content_modal_title">
    <div class="ia-class-head">
      <h3 id="ia_componente_content_modal_title">Asistente IA: contenido de componente</h3>
      <button type="button" class="btn btn-secondary" id="ia_componente_content_modal_close_top">Cerrar</button>
    </div>
    <div class="ia-class-body">
      <div class="ia-class-chat">
        <div class="ia-class-messages" id="ia_componente_content_messages"></div>
        <div class="ia-class-input">
          <textarea id="ia_componente_content_user_input" placeholder="Ejemplo: mejora la sección de seguridad y agrega mantenimiento preventivo."></textarea>
          <div class="ia-content-chip-row">
            <button type="button" class="btn btn-secondary ia-componente-content-quick" data-focus="estructura completa">Estructura completa</button>
            <button type="button" class="btn btn-secondary ia-componente-content-quick" data-focus="uso en clase">Uso en clase</button>
            <button type="button" class="btn btn-secondary ia-componente-content-quick" data-focus="seguridad y cuidados">Seguridad</button>
            <button type="button" class="btn btn-secondary ia-componente-content-quick" data-focus="mantenimiento y almacenamiento">Mantenimiento</button>
            <button type="button" class="btn btn-secondary ia-componente-content-quick" data-focus="preguntas frecuentes">FAQ</button>
          </div>
          <div class="ia-class-input-actions">
            <button type="button" class="btn" id="ia_componente_content_send">Generar contenido</button>
          </div>
          <div class="ia-content-settings">
            <label><input type="checkbox" id="ia_componente_content_update_seguridad"> Actualizar advertencias de seguridad</label>
          </div>
        </div>
      </div>
      <aside class="ia-class-side">
        <h4>Vista previa</h4>
        <p>La IA debe devolver JSON con descripcion_html y opcionalmente advertencias_seguridad.</p>
        <pre class="ia-content-preview" id="ia_componente_content_json_preview">Esperando propuesta de contenido...</pre>
      </aside>
    </div>
    <div class="ia-class-foot">
      <button type="button" class="btn btn-secondary" id="ia_componente_content_reset">Limpiar propuesta</button>
      <button type="button" class="btn" id="ia_componente_content_apply_replace" disabled>Reemplazar descripción</button>
      <button type="button" class="btn" id="ia_componente_content_apply_append" disabled>Anexar a descripción</button>
    </div>
  </div>
</div>

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

<script>
  // ========================================================
  // MODAL IA DE COMPONENTE (AUTOFILL FORMULARIO)
  // ========================================================
  (function initIaComponenteBuilder() {
    const modal = document.getElementById('ia_componente_modal');
    const btnOpen = document.getElementById('btn_open_ia_componente_modal');
    const btnClose = document.getElementById('ia_componente_modal_close_top');
    const btnSend = document.getElementById('ia_componente_send');
    const btnQuick = document.getElementById('ia_componente_quick_brief');
    const btnApply = document.getElementById('ia_componente_apply');
    const btnReset = document.getElementById('ia_componente_reset');
    const input = document.getElementById('ia_componente_user_input');
    const messages = document.getElementById('ia_componente_messages');
    const preview = document.getElementById('ia_componente_json_preview');

    if (!modal || !btnOpen || !btnSend || !btnApply || !input || !messages || !preview) {
      console.log('⚠️ [IA Componente Modal] No se inicializa por elementos faltantes');
      return;
    }

    let lastSuggestion = null;
    let isBusy = false;

    function addMsg(role, text) {
      const el = document.createElement('div');
      el.className = 'ia-msg ' + role;
      el.textContent = text;
      messages.appendChild(el);
      messages.scrollTop = messages.scrollHeight;
    }

    function openModal() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      if (!messages.dataset.welcome) {
        addMsg('system', '🧪 Describe el componente y yo te devuelvo un JSON aplicable al formulario.');
        messages.dataset.welcome = '1';
      }
      input.focus();
      console.log('✅ [IA Componente Modal] abierto');
    }

    function closeModal() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      console.log('🔍 [IA Componente Modal] cerrado');
    }

    function normalizeSku(val) {
      return String(val || '').toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function safeJsonParse(text) {
      if (!text) return null;
      const raw = String(text).trim();
      try { return JSON.parse(raw); } catch (_e) {}
      const start = raw.indexOf('{');
      const end = raw.lastIndexOf('}');
      if (start >= 0 && end > start) {
        const maybe = raw.slice(start, end + 1);
        try { return JSON.parse(maybe); } catch (_e) {}
      }
      return null;
    }

    function extractStringField(rawText, key) {
      const text = String(rawText || '');
      const escKey = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const rx = new RegExp('"' + escKey + '"\\s*:\\s*"((?:\\\\.|[^"\\\\])*)"');
      const m = text.match(rx);
      if (!m || !m[1]) return '';
      return m[1].replace(/\\n/g, '\n').replace(/\\r/g, '').replace(/\\t/g, '\t').replace(/\\"/g, '"').replace(/\\\\/g, '\\').trim();
    }

    function extractDescripcionHtmlFallback(rawText) {
      const text = String(rawText || '');
      if (!text) return '';
      const key = '"descripcion_html":"';
      const idx = text.indexOf(key);
      if (idx < 0) return '';

      let chunk = text.slice(idx + key.length);
      const endCandidates = ['","advertencias_seguridad"', '","unidad"', '","foto_url"', '","notas_autor"', '"}'];
      let endAt = -1;
      for (const marker of endCandidates) {
        const p = chunk.indexOf(marker);
        if (p >= 0 && (endAt < 0 || p < endAt)) endAt = p;
      }
      if (endAt >= 0) chunk = chunk.slice(0, endAt);

      chunk = chunk.replace(/\\n/g, '\n').replace(/\\r/g, '').replace(/\\t/g, '\t').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
      chunk = chunk.replace(/\s+$/g, '').replace(/\\+$/g, '');

      const trimmed = chunk.trim();
      if (trimmed.length < 30 || trimmed.indexOf('<h2>') === -1) return '';
      return trimmed;
    }

    function extractPartialSuggestion(rawText) {
      const partial = {};
      const nombre = extractStringField(rawText, 'nombre_comun');
      const sku = extractStringField(rawText, 'sku');
      const unidad = extractStringField(rawText, 'unidad');
      const seguridad = extractStringField(rawText, 'advertencias_seguridad');
      const foto = extractStringField(rawText, 'foto_url');
      const categoriaNombre = extractStringField(rawText, 'categoria_nombre');
      const descripcion = extractDescripcionHtmlFallback(rawText);

      if (nombre) partial.nombre_comun = nombre;
      if (sku) partial.sku = sku;
      if (unidad) partial.unidad = unidad;
      if (seguridad) partial.advertencias_seguridad = seguridad;
      if (foto) partial.foto_url = foto;
      if (categoriaNombre) partial.categoria_nombre = categoriaNombre;
      if (descripcion) partial.descripcion_html = descripcion;

      return Object.keys(partial).length ? partial : null;
    }

    function getDescripcionEditorHtml() {
      try {
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.descripcion_html) {
          return String(CKEDITOR.instances.descripcion_html.getData() || '');
        }
      } catch (_e) {}
      return String(document.getElementById('descripcion_html')?.value || '');
    }

    function collectFormSnapshot() {
      const categorySelect = document.getElementById('categoria_id');
      const options = categorySelect ? Array.from(categorySelect.options).map((o) => ({ id: parseInt(o.value || '0', 10) || 0, nombre: String(o.textContent || '').trim() })).filter((x) => x.id > 0) : [];
      const categoriaActualId = categorySelect ? parseInt(categorySelect.value || '0', 10) || 0 : 0;
      const categoriaActualNombre = (categorySelect && categorySelect.selectedOptions && categorySelect.selectedOptions[0]) ? String(categorySelect.selectedOptions[0].textContent || '').trim() : '';
      const descripcionActual = getDescripcionEditorHtml();
      const descripcionRecortada = descripcionActual.length > 2400
        ? (descripcionActual.slice(0, 2400) + '\n...[DESCRIPCION ACTUAL RECORTADA PARA CONTEXTO]...')
        : descripcionActual;

      return {
        nombre_comun: (document.getElementById('nombre_comun')?.value || '').trim(),
        sku: (document.getElementById('slug')?.value || '').trim(),
        categoria_id: categoriaActualId,
        categoria_nombre: categoriaActualNombre,
        unidad: (document.getElementById('unidad')?.value || '').trim(),
        advertencias_seguridad: (document.getElementById('advertencias_seguridad')?.value || '').trim(),
        foto_url: (document.getElementById('foto_url')?.value || '').trim(),
        descripcion_html: descripcionRecortada,
        categorias_disponibles: options
      };
    }

    function buildIaPrompt(userMessage) {
      const snapshot = collectFormSnapshot();
      return [
        'Eres asistente experto en redaccion tecnica y pedagogica de componentes para kits educativos de ciencias en Colombia.',
        'Devuelve SOLO JSON valido, sin markdown, sin comentarios, sin texto fuera del JSON.',
        'Schema obligatorio exacto:',
        '{"nombre_comun":"","sku":"","categoria_id":0,"categoria_nombre":"","unidad":"pcs","advertencias_seguridad":"","descripcion_html":"","foto_url":"","notas_autor":"","plantilla_estructura":{"secciones":["Que es este componente","Para que sirve en clase","Uso recomendado paso a paso","Seguridad y cuidados","Mantenimiento y almacenamiento","Preguntas frecuentes"]}}',
        'Reglas obligatorias para descripcion_html:',
        '- Debe incluir h2 con este orden exacto: Que es este componente; Para que sirve en clase; Uso recomendado paso a paso; Seguridad y cuidados; Mantenimiento y almacenamiento; Preguntas frecuentes.',
        '- Usar solo etiquetas seguras: h2, p, ul, ol, li, strong, em.',
        '- Escribir en tono claro, didactico y seguro.',
        '- No inventar instrucciones peligrosas ni uso de sustancias de riesgo.',
        '- sku debe quedar en MAYUSCULAS y con guiones (ejemplo: SENSOR-PH-01).',
        'Contexto actual del formulario: ' + JSON.stringify(snapshot),
        'Solicitud del usuario: ' + userMessage
      ].join('\n');
    }

    function setCategoriaByData(data) {
      const sel = document.getElementById('categoria_id');
      if (!sel) return;

      const byId = parseInt(String(data.categoria_id || '0'), 10) || 0;
      if (byId > 0 && sel.querySelector('option[value="' + byId + '"]')) {
        sel.value = String(byId);
        sel.dispatchEvent(new Event('change', { bubbles: true }));
        return;
      }

      const catName = String(data.categoria_nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      if (!catName) return;
      const match = Array.from(sel.options).find((opt) => String(opt.textContent || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').indexOf(catName) !== -1);
      if (match) {
        sel.value = String(match.value);
        sel.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    function applySuggestion(data) {
      if (!data || typeof data !== 'object') return;
      const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = val == null ? '' : String(val);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      };

      if (data.nombre_comun) setVal('nombre_comun', data.nombre_comun);
      if (data.sku || data.nombre_comun) setVal('slug', normalizeSku(data.sku || data.nombre_comun));
      if (data.unidad) setVal('unidad', data.unidad);
      if (data.advertencias_seguridad) setVal('advertencias_seguridad', data.advertencias_seguridad);
      if (data.foto_url) setVal('foto_url', data.foto_url);
      setCategoriaByData(data);

      if (data.descripcion_html) {
        const html = String(data.descripcion_html);
        const ta = document.getElementById('descripcion_html');
        if (ta) ta.value = html;
        try {
          if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.descripcion_html) {
            CKEDITOR.instances.descripcion_html.setData(html);
          }
        } catch (_e) {}
      }

      console.log('✅ [IA Componente Modal] Campos autollenados');
    }

    async function askIa(userText) {
      if (isBusy) return;
      const text = String(userText || '').trim() || 'Genera un borrador completo usando el contexto actual del componente cargado.';

      isBusy = true;
      btnSend.disabled = true;
      addMsg('user', text);
      input.value = '';
      console.log('🔍 [IA Componente Modal] Enviando solicitud IA');

      try {
        async function callIaWithPrompt(promptText) {
          const payload = {
            instancia: 'backend',
            contexto_scope: 'admin_componentes_builder',
            contexto_pagina: 'componentes',
            entidad_tipo: 'componente',
            entidad_id: <?= $is_edit ? (int)$id : 'null' ?>,
            pregunta: promptText
          };
          const res = await fetch('/api/ia-consulta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
          return res.json();
        }

        const firstPrompt = buildIaPrompt(text);
        let json = await callIaWithPrompt(firstPrompt);
        if (!json || !json.ok) {
          const msg = (json && json.error) ? json.error : 'No se pudo generar el borrador.';
          addMsg('assistant', '❌ ' + msg);
          console.log('❌ [IA Componente Modal] Error API:', msg);
          return;
        }

        let respuesta = String(json.respuesta || '').trim();
        let parsed = safeJsonParse(respuesta);
        if (!respuesta || !parsed || typeof parsed !== 'object') {
          addMsg('system', '⚠️ Primera respuesta no válida. Reintentando con formato de rescate...');
          console.log('⚠️ [IA Componente Modal] Primera respuesta vacía/no JSON, reintento rescate');

          const rescuePrompt = [
            'RESPUESTA DE RESCATE OBLIGATORIA.',
            'Devuelve SOLO JSON válido en UNA sola línea.',
            'Sin markdown. Sin texto extra. Sin explicación.',
            'Schema exacto:',
            '{"nombre_comun":"","sku":"","categoria_id":0,"categoria_nombre":"","unidad":"pcs","advertencias_seguridad":"","descripcion_html":"","foto_url":"","notas_autor":"","plantilla_estructura":{"secciones":["Que es este componente","Para que sirve en clase","Uso recomendado paso a paso","Seguridad y cuidados","Mantenimiento y almacenamiento","Preguntas frecuentes"]}}',
            'descripcion_html debe incluir al menos 6 secciones h2 con contenido real. No truncar respuesta.',
            'Solicitud original del usuario: ' + text
          ].join('\n');

          json = await callIaWithPrompt(rescuePrompt);
          if (!json || !json.ok) {
            const msg = (json && json.error) ? json.error : 'No se pudo generar el borrador (reintento).';
            addMsg('assistant', '❌ ' + msg);
            return;
          }

          respuesta = String(json.respuesta || '').trim();
          parsed = safeJsonParse(respuesta);
        }

        addMsg('assistant', respuesta || 'Respuesta vacía de IA.');
        if (parsed && typeof parsed === 'object') {
          lastSuggestion = parsed;
          preview.textContent = JSON.stringify(parsed, null, 2);
          btnApply.disabled = false;
          addMsg('system', '✅ Borrador estructurado detectado. Puedes aplicar al formulario.');
          console.log('✅ [IA Componente Modal] JSON estructurado listo');
        } else {
          const partial = extractPartialSuggestion(respuesta);
          if (partial) {
            lastSuggestion = partial;
            preview.textContent = JSON.stringify(partial, null, 2);
            btnApply.disabled = false;
            addMsg('system', '⚠️ JSON incompleto: se recuperó un borrador parcial. Revisa antes de aplicar.');
            console.log('⚠️ [IA Componente Modal] Fallback parcial aplicado');
          } else {
            addMsg('system', '⚠️ No se detectó JSON válido. Pídele que responda solo JSON.');
            console.log('⚠️ [IA Componente Modal] Respuesta sin JSON parseable');
          }
        }
      } catch (err) {
        addMsg('assistant', '❌ Error de red o de procesamiento.');
        console.log('❌ [IA Componente Modal] Excepción:', err && err.message ? err.message : err);
      } finally {
        isBusy = false;
        btnSend.disabled = false;
      }
    }

    btnOpen.addEventListener('click', openModal);
    btnClose?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    btnSend.addEventListener('click', () => askIa(input.value));
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askIa(input.value);
      }
    });

    btnQuick?.addEventListener('click', () => {
      askIa('Genera un borrador completo del componente para publicar en Clase de Ciencia.');
    });

    btnApply.addEventListener('click', () => {
      if (!lastSuggestion) {
        addMsg('system', '⚠️ No hay borrador para aplicar.');
        return;
      }
      applySuggestion(lastSuggestion);
      addMsg('system', '✅ Datos aplicados al formulario. Revisa y guarda.');
    });

    btnReset?.addEventListener('click', () => {
      lastSuggestion = null;
      preview.textContent = 'Esperando sugerencia...';
      btnApply.disabled = true;
      input.value = '';
      messages.innerHTML = '';
      delete messages.dataset.welcome;
      addMsg('system', '🧹 Borrador limpiado.');
    });
  })();

  // ========================================================
  // MODAL IA DE CONTENIDO DEL COMPONENTE
  // ========================================================
  (function initIaComponenteContentBuilder() {
    const modal = document.getElementById('ia_componente_content_modal');
    const btnOpen = document.getElementById('btn_open_ia_componente_content_modal');
    const btnClose = document.getElementById('ia_componente_content_modal_close_top');
    const btnSend = document.getElementById('ia_componente_content_send');
    const btnApplyReplace = document.getElementById('ia_componente_content_apply_replace');
    const btnApplyAppend = document.getElementById('ia_componente_content_apply_append');
    const btnReset = document.getElementById('ia_componente_content_reset');
    const input = document.getElementById('ia_componente_content_user_input');
    const messages = document.getElementById('ia_componente_content_messages');
    const preview = document.getElementById('ia_componente_content_json_preview');
    const cbSeguridad = document.getElementById('ia_componente_content_update_seguridad');
    const quickButtons = Array.from(document.querySelectorAll('.ia-componente-content-quick'));

    if (!modal || !btnOpen || !messages || !preview || !input || !btnSend || !btnApplyReplace || !btnApplyAppend) {
      console.log('⚠️ [IA Componente Content Modal] No se inicializa por elementos faltantes');
      return;
    }

    let lastSuggestion = null;
    let busy = false;
    let currentFocus = '';

    function addMsg(role, text) {
      const el = document.createElement('div');
      el.className = 'ia-msg ' + role;
      el.textContent = text;
      messages.appendChild(el);
      messages.scrollTop = messages.scrollHeight;
    }

    function openModal() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      if (!messages.dataset.welcome) {
        addMsg('system', '🧪 Genera la descripción del componente por bloques o completa.');
        messages.dataset.welcome = '1';
      }
      input.focus();
      console.log('✅ [IA Componente Content Modal] abierto');
    }

    function closeModal() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      console.log('🔍 [IA Componente Content Modal] cerrado');
    }

    function getEditorHtml() {
      try {
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.descripcion_html) {
          return String(CKEDITOR.instances.descripcion_html.getData() || '');
        }
      } catch (_e) {}
      return String(document.getElementById('descripcion_html')?.value || '');
    }

    function safeJsonParse(rawText) {
      const text = String(rawText || '').trim();
      if (!text) return null;
      try { return JSON.parse(text); } catch (_e) {}
      const start = text.indexOf('{');
      const end = text.lastIndexOf('}');
      if (start >= 0 && end > start) {
        const maybe = text.slice(start, end + 1);
        try { return JSON.parse(maybe); } catch (_e) {}
      }
      return null;
    }

    function extractDescripcionHtmlFallback(rawText) {
      const text = String(rawText || '');
      if (!text) return '';
      const key = '"descripcion_html":"';
      const idx = text.indexOf(key);
      if (idx < 0) return '';

      let chunk = text.slice(idx + key.length);
      const endCandidates = ['","advertencias_seguridad"', '","notas_autor"', '"}'];
      let endAt = -1;
      for (const marker of endCandidates) {
        const p = chunk.indexOf(marker);
        if (p >= 0 && (endAt < 0 || p < endAt)) endAt = p;
      }
      if (endAt >= 0) chunk = chunk.slice(0, endAt);

      chunk = chunk.replace(/\\n/g, '\n').replace(/\\r/g, '').replace(/\\t/g, '\t').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
      chunk = chunk.replace(/\s+$/g, '').replace(/\\+$/g, '');

      const trimmed = chunk.trim();
      if (trimmed.length < 30 || trimmed.indexOf('<h2>') === -1) return '';
      return trimmed;
    }

    function buildPrompt(userText) {
      const nombre = (document.getElementById('nombre_comun')?.value || '').trim();
      const sku = (document.getElementById('slug')?.value || '').trim();
      const unidad = (document.getElementById('unidad')?.value || '').trim();
      const advertencias = (document.getElementById('advertencias_seguridad')?.value || '').trim();
      const categoriaSelect = document.getElementById('categoria_id');
      const categoria = (categoriaSelect && categoriaSelect.selectedOptions && categoriaSelect.selectedOptions[0]) ? String(categoriaSelect.selectedOptions[0].textContent || '').trim() : '';
      const descripcionActual = getEditorHtml();
      const descripcionResumida = descripcionActual.length > 2400
        ? (descripcionActual.slice(0, 2400) + '\n...[DESCRIPCION ACTUAL RECORTADA PARA CONTEXTO]...')
        : descripcionActual;

      return [
        'Eres editor experto en fichas de componentes para kits educativos de ciencias en Colombia.',
        'Devuelve SOLO JSON valido, sin markdown y sin texto fuera del JSON.',
        'Schema obligatorio:',
        '{"descripcion_html":"","advertencias_seguridad":"","notas_autor":"","plantilla_validada":true,"secciones_detectadas":["Que es este componente","Para que sirve en clase","Uso recomendado paso a paso","Seguridad y cuidados","Mantenimiento y almacenamiento","Preguntas frecuentes"]}',
        'Reglas:',
        '- descripcion_html debe usar SOLO etiquetas seguras: h2, p, ul, ol, li, strong, em.',
        '- Estructura obligatoria con h2 y en este orden: Que es este componente; Para que sirve en clase; Uso recomendado paso a paso; Seguridad y cuidados; Mantenimiento y almacenamiento; Preguntas frecuentes.',
        '- Mantener lenguaje claro, didactico y seguro para docentes y estudiantes.',
        '- Incluir advertencias y recomendaciones de uso responsable.',
        '- No devolver placeholders vacios; cada sección debe tener contenido real.',
        'Contexto componente: ' + JSON.stringify({ nombre_comun: nombre, sku, unidad, categoria, advertencias_seguridad: advertencias }),
        'Descripción actual: ' + descripcionResumida,
        'Foco solicitado: ' + (currentFocus || 'sin foco específico'),
        'Solicitud del usuario: ' + userText
      ].join('\n');
    }

    function setEditorHtml(html, mode) {
      const incoming = String(html || '').trim();
      if (!incoming) return;
      const ta = document.getElementById('descripcion_html');
      const current = getEditorHtml();
      const finalHtml = mode === 'append' && current ? (current + '\n\n' + incoming) : incoming;

      if (ta) ta.value = finalHtml;
      try {
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.descripcion_html) {
          CKEDITOR.instances.descripcion_html.setData(finalHtml);
        }
      } catch (_e) {}
      console.log('✅ [IA Componente Content Modal] descripcion_html actualizada en modo:', mode);
    }

    function applySuggestion(mode) {
      if (!lastSuggestion || typeof lastSuggestion !== 'object') {
        addMsg('system', '⚠️ No hay propuesta de contenido para aplicar.');
        return;
      }
      if (lastSuggestion.descripcion_html) {
        setEditorHtml(lastSuggestion.descripcion_html, mode);
      }
      if (cbSeguridad?.checked && lastSuggestion.advertencias_seguridad) {
        const el = document.getElementById('advertencias_seguridad');
        if (el) {
          el.value = String(lastSuggestion.advertencias_seguridad);
          el.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }
      addMsg('system', '✅ Contenido aplicado. Revisa antes de guardar.');
    }

    async function askContentIa(messageText) {
      const message = String(messageText || '').trim();
      if (!message || busy) return;

      busy = true;
      btnSend.disabled = true;
      addMsg('user', message);
      input.value = '';
      console.log('🔍 [IA Componente Content Modal] Enviando solicitud IA con foco:', currentFocus || '(general)');

      try {
        async function callIaWithPrompt(promptText) {
          const payload = {
            instancia: 'backend',
            contexto_scope: 'admin_componentes_content_builder',
            contexto_pagina: 'componentes',
            entidad_tipo: 'componente',
            entidad_id: <?= $is_edit ? (int)$id : 'null' ?>,
            pregunta: promptText
          };
          const res = await fetch('/api/ia-consulta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
          return res.json();
        }

        const primaryPrompt = buildPrompt(message);
        let json = await callIaWithPrompt(primaryPrompt);
        if (!json || !json.ok) {
          const msg = (json && json.error) ? json.error : 'No se pudo generar contenido.';
          addMsg('assistant', '❌ ' + msg);
          return;
        }

        let respuesta = String(json.respuesta || '').trim();
        let parsed = safeJsonParse(respuesta);

        if (!respuesta || !(parsed && typeof parsed === 'object' && parsed.descripcion_html)) {
          console.log('⚠️ [IA Componente Content Modal] Primera respuesta vacía/no válida, activando reintento de rescate');
          addMsg('system', '⚠️ La primera respuesta no fue utilizable. Reintentando con formato de rescate...');

          const rescuePrompt = [
            'RESPUESTA DE RESCATE OBLIGATORIA.',
            'Devuelve SOLO JSON válido en UNA sola línea.',
            'Sin markdown. Sin texto extra. Sin explicación.',
            'Schema exacto:',
            '{"descripcion_html":"","advertencias_seguridad":"","notas_autor":"","plantilla_validada":true,"secciones_detectadas":["Que es este componente","Para que sirve en clase","Uso recomendado paso a paso","Seguridad y cuidados","Mantenimiento y almacenamiento","Preguntas frecuentes"]}',
            'La clave descripcion_html es obligatoria y debe traer HTML real con al menos 6 bloques h2.',
            'Solicitud original del usuario: ' + message
          ].join('\n');

          json = await callIaWithPrompt(rescuePrompt);
          if (!json || !json.ok) {
            const msg = (json && json.error) ? json.error : 'No se pudo generar contenido (reintento).';
            addMsg('assistant', '❌ ' + msg);
            return;
          }

          respuesta = String(json.respuesta || '').trim();
          parsed = safeJsonParse(respuesta);
        }

        addMsg('assistant', respuesta || 'Respuesta vacía.');
        if (parsed && typeof parsed === 'object' && parsed.descripcion_html) {
          lastSuggestion = parsed;
          preview.textContent = JSON.stringify(parsed, null, 2);
          btnApplyReplace.disabled = false;
          btnApplyAppend.disabled = false;
          addMsg('system', '✅ Propuesta de contenido lista para aplicar.');
          console.log('✅ [IA Componente Content Modal] JSON parseado correctamente');
        } else {
          const rescuedHtml = extractDescripcionHtmlFallback(respuesta);
          if (rescuedHtml) {
            lastSuggestion = {
              descripcion_html: rescuedHtml,
              advertencias_seguridad: '',
              notas_autor: 'fallback_from_truncated_json'
            };
            preview.textContent = JSON.stringify(lastSuggestion, null, 2);
            btnApplyReplace.disabled = false;
            btnApplyAppend.disabled = false;
            addMsg('system', '⚠️ JSON truncado: se recuperó descripcion_html de forma parcial. Revisa antes de aplicar.');
            console.log('⚠️ [IA Componente Content Modal] Se aplicó fallback por JSON truncado');
          } else {
            addMsg('system', '⚠️ La respuesta no trae JSON válido con descripcion_html.');
            console.log('⚠️ [IA Componente Content Modal] Respuesta no parseable como JSON de contenido');
          }
        }
      } catch (err) {
        addMsg('assistant', '❌ Error al consultar IA para contenido.');
        console.log('❌ [IA Componente Content Modal] Excepción:', err && err.message ? err.message : err);
      } finally {
        busy = false;
        btnSend.disabled = false;
      }
    }

    btnOpen?.addEventListener('click', openModal);
    btnClose?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    btnSend.addEventListener('click', () => askContentIa(input.value));
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askContentIa(input.value);
      }
    });

    quickButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        currentFocus = btn.getAttribute('data-focus') || '';
        askContentIa('Genera una propuesta enfocada en: ' + currentFocus + '.');
      });
    });

    btnApplyReplace.addEventListener('click', () => applySuggestion('replace'));
    btnApplyAppend.addEventListener('click', () => applySuggestion('append'));
    btnReset?.addEventListener('click', () => {
      lastSuggestion = null;
      preview.textContent = 'Esperando propuesta de contenido...';
      btnApplyReplace.disabled = true;
      btnApplyAppend.disabled = true;
      input.value = '';
      messages.innerHTML = '';
      delete messages.dataset.welcome;
      currentFocus = '';
      addMsg('system', '🧹 Propuesta limpiada.');
    });
  })();
</script>

<script src="/assets/js/admin-image-editor.js"></script>

<?php include '../footer.php'; ?>
