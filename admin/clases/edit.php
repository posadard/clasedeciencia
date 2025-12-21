<?php
require_once '../auth.php';
/** @var \PDO $pdo */

$is_edit = isset($_GET['id']) && ctype_digit($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : null;

$page_title = $is_edit ? 'Editar Clase' : 'Nueva Clase';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

// Cargar datos si es edición
$clase = [
  'nombre' => '',
  'slug' => '',
  'ciclo' => null,
  'grados' => '[]',
  'dificultad' => '',
  'duracion_minutos' => '',
  'resumen' => '',
  'objetivo_aprendizaje' => '',
  'imagen_portada' => '',
  'video_portada' => '',
  'seguridad' => null,
  'seo_title' => '',
  'seo_description' => '',
  'activo' => 1,
  'destacado' => 0,
  'orden_popularidad' => 0,
  'status' => 'draft',
  'published_at' => null,
  'autor' => '',
  'contenido_html' => ''
];

if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT id, nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, imagen_portada, video_portada, seguridad, seo_title, seo_description, activo, destacado, orden_popularidad, status, published_at, autor, contenido_html FROM clases WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { $clase = $row; } else { $is_edit = false; $id = null; }
  } catch (PDOException $e) {}
}

// Cargar listas para relaciones
$areas = [];
$competencias = [];
$ciclos_list = [];
$existing_area_ids = [];
$existing_comp_ids = [];
$existing_tags = [];
$all_kits = [];
$existing_kit_ids = [];
try {
  $areas = $pdo->query('SELECT id, nombre, slug FROM areas ORDER BY nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
try {
  $competencias = $pdo->query('SELECT id, codigo, nombre FROM competencias ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
try {
  $ciclos_list = cdc_get_ciclos($pdo, true); // Solo ciclos activos
} catch (Exception $e) {}
if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT area_id FROM clase_areas WHERE clase_id = ?');
    $stmt->execute([$id]);
    $existing_area_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {}
  try {
    $stmt = $pdo->prepare('SELECT competencia_id FROM clase_competencias WHERE clase_id = ?');
    $stmt->execute([$id]);
    $existing_comp_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {}
  try {
    $stmt = $pdo->prepare('SELECT tag FROM clase_tags WHERE clase_id = ?');
    $stmt->execute([$id]);
    $tags_rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $existing_tags = $tags_rows ?: [];
  } catch (PDOException $e) {}
  try {
    $stmt = $pdo->prepare('SELECT kit_id FROM clase_kits WHERE clase_id = ? ORDER BY es_principal DESC, sort_order ASC');
    $stmt->execute([$id]);
    $existing_kit_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {}
}

// Cargar todos los kits disponibles
try {
  $all_kits = $pdo->query('SELECT id, nombre, codigo, activo FROM kits ORDER BY nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Guardar
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [ClasesEdit] CSRF inválido");</script>';
  } else {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    // Handlers for ficha técnica attributes on Clase
    if ($is_edit && in_array($action, ['add_attr','update_attr','delete_attr','create_attr_def'], true)) {
      try {
        if ($action === 'delete_attr') {
          $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
          if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
          $stmt = $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?');
          $stmt->execute(['clase', $id, $def_id]);
          echo '<script>console.log("✅ [ClasesEdit] delete_attr ejecutado");</script>';
        } else if ($action === 'create_attr_def') {
          $etiqueta = isset($_POST['etiqueta']) ? trim((string)$_POST['etiqueta']) : '';
          $clave = isset($_POST['clave']) ? trim((string)$_POST['clave']) : '';
          $tipo = isset($_POST['tipo_dato']) ? trim((string)$_POST['tipo_dato']) : 'string';
          $card = isset($_POST['cardinalidad']) ? trim((string)$_POST['cardinalidad']) : 'one';
          $unidad_def = isset($_POST['unidad_defecto']) ? trim((string)$_POST['unidad_defecto']) : '';
          $unidades_raw = isset($_POST['unidades_permitidas']) ? (string)$_POST['unidades_permitidas'] : '';
          if ($etiqueta === '') { throw new Exception('Etiqueta requerida'); }
          if ($clave === '') { $clave = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $etiqueta)); $clave = trim($clave, '_'); }
          else { $clave = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $clave)); $clave = trim($clave, '_'); }
          $tipos_validos = ['string','number','integer','boolean','date','datetime','json'];
          $cards_validas = ['one','many'];
          if (!in_array($tipo, $tipos_validos, true)) { $tipo = 'string'; }
          if (!in_array($card, $cards_validas, true)) { $card = 'one'; }
          $unidades = array_filter(array_map(function($v){ return trim($v); }, preg_split('/[\n,]+/', $unidades_raw)));
          $unidades_json = !empty($unidades) ? json_encode(array_values($unidades)) : null;
          $pdo->beginTransaction();
          $st = $pdo->prepare('SELECT id FROM atributos_definiciones WHERE clave = ?');
          $st->execute([$clave]);
          $def_id = (int)$st->fetchColumn();
          if ($def_id <= 0) {
            $ins = $pdo->prepare('INSERT INTO atributos_definiciones (clave, etiqueta, tipo_dato, cardinalidad, unidad_defecto, unidades_permitidas_json, aplica_a_json) VALUES (?,?,?,?,?,?,?)');
            $aplica = json_encode(['clase']);
            $ins->execute([$clave, $etiqueta, $tipo, $card, ($unidad_def !== '' ? $unidad_def : null), $unidades_json, $aplica]);
            $def_id = (int)$pdo->lastInsertId();
          }
          $chk = $pdo->prepare('SELECT COUNT(*) FROM atributos_mapeo WHERE atributo_id = ? AND tipo_entidad = ?');
          $chk->execute([$def_id, 'clase']);
          if ((int)$chk->fetchColumn() === 0) {
            $nextOrdStmt = $pdo->prepare('SELECT COALESCE(MAX(orden),0)+1 AS nextOrd FROM atributos_mapeo WHERE tipo_entidad = ?');
            $nextOrdStmt->execute(['clase']);
            $next = (int)$nextOrdStmt->fetchColumn();
            $mp = $pdo->prepare('INSERT INTO atributos_mapeo (atributo_id, tipo_entidad, visible, orden) VALUES (?,?,?,?)');
            $mp->execute([$def_id, 'clase', 1, $next]);
          }
          $pdo->commit();
          echo '<script>console.log("✅ [ClasesEdit] create_attr_def listo: ' . htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') . '");</script>';
        } else {
          // add_attr / update_attr share logic: delete then insert
          $def_id = isset($_POST['def_id']) && ctype_digit($_POST['def_id']) ? (int)$_POST['def_id'] : 0;
          $valor = isset($_POST['valor']) ? (string)$_POST['valor'] : '';
          $unidad = isset($_POST['unidad']) ? trim((string)$_POST['unidad']) : '';
          if ($def_id <= 0) { throw new Exception('Atributo inválido'); }
          $defS = $pdo->prepare('SELECT * FROM atributos_definiciones WHERE id = ?');
          $defS->execute([$def_id]);
          $def = $defS->fetch(PDO::FETCH_ASSOC);
          if (!$def) { throw new Exception('Atributo no existe'); }
          $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['clase', $id, $def_id]);
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
            $ins->execute(['clase', $id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, ($unidad ?: ($def['unidad_defecto'] ?? null)), 'es-CO', $orden++, 'manual']);
          }
          $pdo->commit();
          echo '<script>console.log("✅ [ClasesEdit] ' . ($action === 'add_attr' ? 'add' : 'update') . '_attr guardado");</script>';
        }
      } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error_msg = 'Error en atributos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<script>console.log("❌ [ClasesEdit] attr error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
      }
      // Skip the rest of save handling for attribute actions
    } else {
      $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $ciclo = isset($_POST['ciclo']) && $_POST['ciclo'] !== '' ? (int)$_POST['ciclo'] : null;
    echo '<script>console.log("🔍 [ClasesEdit] POST ciclo:", ' . json_encode($ciclo) . ', "tipo:", typeof ' . json_encode($ciclo) . ');</script>';
    $grados_sel = isset($_POST['grados']) && is_array($_POST['grados']) ? array_values(array_filter($_POST['grados'])) : [];
    $grados_json = json_encode(array_map('intval', $grados_sel));
    $dificultad = isset($_POST['dificultad']) ? trim($_POST['dificultad']) : '';
    $duracion_minutos = isset($_POST['duracion_minutos']) ? (int)$_POST['duracion_minutos'] : null;
    $resumen = isset($_POST['resumen']) ? trim($_POST['resumen']) : '';
    $objetivo = isset($_POST['objetivo_aprendizaje']) ? trim($_POST['objetivo_aprendizaje']) : '';
    $imagen_portada = isset($_POST['imagen_portada']) ? trim($_POST['imagen_portada']) : '';
    $video_portada = isset($_POST['video_portada']) ? trim($_POST['video_portada']) : '';
    $seg_edad_min = isset($_POST['seg_edad_min']) ? (int)$_POST['seg_edad_min'] : null;
    $seg_edad_max = isset($_POST['seg_edad_max']) ? (int)$_POST['seg_edad_max'] : null;
    $seg_notas = isset($_POST['seg_notas']) ? trim($_POST['seg_notas']) : '';
    $seguridad_json = ($seg_edad_min || $seg_edad_max || $seg_notas !== '') ? json_encode(['edad_min'=>$seg_edad_min,'edad_max'=>$seg_edad_max,'notas'=>$seg_notas]) : null;
    $seo_title = isset($_POST['seo_title']) ? trim($_POST['seo_title']) : '';
    $seo_description = isset($_POST['seo_description']) ? trim($_POST['seo_description']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $orden_popularidad = isset($_POST['orden_popularidad']) ? (int)$_POST['orden_popularidad'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
    $published_at_input = isset($_POST['published_at']) ? trim($_POST['published_at']) : '';
    $published_at = $published_at_input !== '' ? date('Y-m-d H:i:s', strtotime($published_at_input)) : null;
    if ($status === 'published' && !$published_at) { $published_at = date('Y-m-d H:i:s'); }
    $autor = isset($_POST['autor']) ? trim($_POST['autor']) : '';
    $contenido_html = isset($_POST['contenido_html']) ? $_POST['contenido_html'] : '';
    $areas_sel = isset($_POST['areas']) && is_array($_POST['areas']) ? array_map('intval', $_POST['areas']) : [];
    $comp_sel = isset($_POST['competencias']) && is_array($_POST['competencias']) ? array_map('intval', $_POST['competencias']) : [];
    $tags_input = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    $tags_list = array_values(array_filter(array_map(function($t){ return trim($t); }, explode(',', $tags_input))));
    $kits_sel = isset($_POST['kits']) && is_array($_POST['kits']) ? array_map('intval', $_POST['kits']) : [];

    // Normalizar y prefijar slug para clases
    if ($slug === '' && $nombre !== '') {
      $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
      $slug = trim($slug, '-');
    }
    if ($slug !== '') {
      $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $slug));
      $slug = trim($slug, '-');
      // Eliminar prefijos repetidos 'clase-' y forzar uno solo al inicio
      $slug = preg_replace('/^(?:clase-)+/i', '', $slug);
      $slug = 'clase-' . ltrim($slug, '-');
    }

    // Validar ciclo contra ciclos activos
    $ciclos_validos = array_column(cdc_get_ciclos($pdo, true), 'numero');
    if ($nombre === '' || $slug === '' || !in_array($ciclo, $ciclos_validos, true)) {
      $error_msg = 'Completa nombre, ciclo válido y slug.';
    } else {
      try {
        // Validar slug único
        if ($is_edit) {
          $check = $pdo->prepare('SELECT COUNT(*) FROM clases WHERE slug = ? AND id <> ?');
          $check->execute([$slug, $id]);
        } else {
          $check = $pdo->prepare('SELECT COUNT(*) FROM clases WHERE slug = ?');
          $check->execute([$slug]);
        }
        $exists = (int)$check->fetchColumn();
        if ($exists > 0) {
          $error_msg = 'El slug ya existe. Elige otro.';
        } else {
          // Autogenerar SEO educativo si vienen vacíos
          if ($seo_title === '') {
            // Formato simple y claro: "Clase de Ciencia - [Área]: [Nombre]" o "Clase de Ciencia - [Nombre] | [Área]"
            $area_nombre = '';
            
            // Obtener nombre de la primera área seleccionada
            if (!empty($areas_sel) && !empty($areas)) {
              foreach ($areas as $area) {
                if (in_array($area['id'], $areas_sel)) {
                  $area_nombre = $area['nombre']; // "Física", "Química", etc.
                  break;
                }
              }
            }
            
            // Construir título con límite de 60 chars
            $base = 'Clase de Ciencia - ';
            if ($area_nombre !== '') {
              // Intentar formato: "Clase de Ciencia - [Área]: [Nombre]"
              $formato1 = $base . $area_nombre . ': ' . $nombre;
              if (strlen($formato1) <= 60) {
                $seo_title = $formato1;
              } else {
                // Si no cabe, formato alternativo: "Clase de Ciencia - [Nombre corto] | [Área]"
                $separador = ' | ' . $area_nombre;
                $max_nombre = 60 - strlen($base) - strlen($separador);
                $nombre_corto = strlen($nombre) > $max_nombre ? substr($nombre, 0, $max_nombre-3) . '...' : $nombre;
                $seo_title = $base . $nombre_corto . $separador;
              }
            } else {
              // Fallback sin área: "Clase de Ciencia - [Nombre]"
              $max_nombre = 60 - strlen($base);
              $nombre_corto = strlen($nombre) > $max_nombre ? substr($nombre, 0, $max_nombre-3) . '...' : $nombre;
              $seo_title = $base . $nombre_corto;
            }
          }
          
          // SEO Description con límite 160 - incluir ciclo/grados para mejor contexto
          $desc_source = $resumen !== '' ? $resumen : strip_tags($contenido_html);
          $desc_source = preg_replace('/\s+/', ' ', $desc_source);
          if ($seo_description === '') {
            // Agregar prefijo con ciclo y grados
            $ciclo_info = '';
            if ($ciclo && !empty($ciclos_list)) {
              foreach ($ciclos_list as $c) {
                if ((int)$c['numero'] === (int)$ciclo) {
                  $ciclo_info = 'Ciclo ' . $c['numero'] . ' (' . $c['grados_texto'] . '): ';
                  break;
                }
              }
            }
            $max_desc = 160 - strlen($ciclo_info);
            $desc_truncada = (strlen($desc_source) > $max_desc)
              ? preg_replace('/\s+\S*$/', '', substr($desc_source, 0, $max_desc))
              : $desc_source;
            $seo_description = $ciclo_info . $desc_truncada;
          }
          echo '<script>console.log("🔍 [SEO] auto title:", ' . json_encode($seo_title) . ', "auto desc:", ' . json_encode($seo_description) . ');</script>';
          // Transacción para clase + relaciones
          $pdo->beginTransaction();
          if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE clases SET nombre=?, slug=?, ciclo=?, grados=?, dificultad=?, duracion_minutos=?, resumen=?, objetivo_aprendizaje=?, imagen_portada=?, video_portada=?, seguridad=?, seo_title=?, seo_description=?, activo=?, destacado=?, orden_popularidad=?, status=?, published_at=?, autor=?, contenido_html=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$nombre, $slug, $ciclo, $grados_json, $dificultad ?: null, $duracion_minutos, $resumen, $objetivo, $imagen_portada ?: null, $video_portada ?: null, $seguridad_json, $seo_title, $seo_description, $activo, $destacado, $orden_popularidad, $status, $published_at, $autor ?: null, $contenido_html, $id]);
            // Limpiar relaciones
            $pdo->prepare('DELETE FROM clase_areas WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_competencias WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_tags WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_kits WHERE clase_id = ?')->execute([$id]);
          } else {
            $stmt = $pdo->prepare('INSERT INTO clases (nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, imagen_portada, video_portada, seguridad, seo_title, seo_description, activo, destacado, orden_popularidad, status, published_at, autor, contenido_html, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
            $stmt->execute([$nombre, $slug, $ciclo, $grados_json, $dificultad ?: null, $duracion_minutos, $resumen, $objetivo, $imagen_portada ?: null, $video_portada ?: null, $seguridad_json, $seo_title, $seo_description, $activo, $destacado, $orden_popularidad, $status, $published_at, $autor ?: null, $contenido_html]);
            $id = (int)$pdo->lastInsertId();
            $is_edit = true;
          }
          // Insertar relaciones
          if (!empty($areas_sel)) {
            $ins = $pdo->prepare('INSERT INTO clase_areas (clase_id, area_id) VALUES (?, ?)');
            foreach ($areas_sel as $aid) { $ins->execute([$id, (int)$aid]); }
          }
          if (!empty($comp_sel)) {
            $ins = $pdo->prepare('INSERT INTO clase_competencias (clase_id, competencia_id) VALUES (?, ?)');
            foreach ($comp_sel as $cid) { $ins->execute([$id, (int)$cid]); }
          }
          if (!empty($tags_list)) {
            $ins = $pdo->prepare('INSERT INTO clase_tags (clase_id, tag) VALUES (?, ?)');
            foreach ($tags_list as $tg) { if ($tg !== '') { $ins->execute([$id, $tg]); } }
          }
          if (!empty($kits_sel)) {
            $ins = $pdo->prepare('INSERT INTO clase_kits (clase_id, kit_id, sort_order, es_principal) VALUES (?, ?, ?, ?)');
            $sort = 1;
            foreach ($kits_sel as $kid) { 
              $es_principal = ($sort === 1) ? 1 : 0; // Primer kit es principal
              $ins->execute([$id, (int)$kid, $sort++, $es_principal]); 
            }
          }
          $pdo->commit();
          echo '<script>console.log("✅ [ClasesEdit] Clase guardada con relaciones");</script>';
          header('Location: /admin/clases/index.php');
          exit;
        }
      } catch (PDOException $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error_msg = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
      }
    }
    }
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
  <span class="help-text">Completa los campos de la clase y asigna áreas/competencias.</span>
  <script>
    console.log('✅ [Admin] Clases edit cargado');
    console.log('🔍 [Admin] Edit mode:', <?= $is_edit ? 'true' : 'false' ?>);
    console.log('🔍 [Admin] Clase ID:', <?= $is_edit ? (int)$id : 'null' ?>);
    console.log('🔍 [Admin] Ciclo desde BD:', <?= json_encode($clase['ciclo']) ?>, 'tipo:', typeof <?= json_encode($clase['ciclo']) ?>);
  </script>
</div>

<?php if ($error_msg !== ''): ?>
  <div class="message error"><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <!-- Información básica -->
  <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
    <label class="switch-label">
      <input type="checkbox" name="activo" class="switch-input" <?= ((int)$clase['activo']) ? 'checked' : '' ?> />
      <span class="switch-slider"></span>
      <span class="switch-text">✓ Activo</span>
    </label>
    <label class="switch-label">
      <input type="checkbox" name="destacado" class="switch-input" <?= ((int)$clase['destacado']) ? 'checked' : '' ?> />
      <span class="switch-slider"></span>
      <span class="switch-text">⭐ Destacado</span>
    </label>
  </div>
  <div class="form-group">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($clase['nombre'], ENT_QUOTES, 'UTF-8') ?>" required />
  </div>
  <div class="form-group">
    <label for="slug">Slug</label>
    <div style="display: flex; gap: 8px; align-items: center;">
      <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($clase['slug'], ENT_QUOTES, 'UTF-8') ?>" placeholder="se genera automáticamente" style="flex: 1;" />
      <button type="button" id="btn_generar_slug" style="padding: 8px 16px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">⚡ Generar</button>
    </div>
    <small class="hint">URL amigable. Ejemplo: clase-radio-de-cristal</small>
  </div>
  <div class="form-group">
    <label for="ciclo">Ciclo</label>
    <select id="ciclo" name="ciclo" required>
      <option value="">Selecciona</option>
      <?php foreach ($ciclos_list as $cl): ?>
      <option value="<?= (int)$cl['numero'] ?>" <?= (int)($clase['ciclo'] ?? 0) === (int)$cl['numero'] ? 'selected' : '' ?>>
        Ciclo <?= htmlspecialchars($cl['numero'], ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($cl['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cl['grados_texto'], ENT_QUOTES, 'UTF-8') ?>)
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label>Grados <small style="color: #666;">(se prellenan según ciclo, pero son editables)</small></label>
    <div class="checkbox-grid">
      <?php 
      // Todos los grados desde 1° hasta 11° (Primaria, Secundaria y Media)
      foreach ([1,2,3,4,5,6,7,8,9,10,11] as $g): 
        $has = false; 
        $gj = $clase['grados'] ?: '[]'; 
        $arr = json_decode($gj, true); 
        $has = is_array($arr) && in_array($g, $arr); 
      ?>
        <label><input type="checkbox" name="grados[]" value="<?= $g ?>" <?= $has ? 'checked' : '' ?>> <?= $g ?>°</label>
      <?php endforeach; ?>
    </div>
    <small style="color: #999;">1°-3°: Ciclo 1 Cimentación | 4°-5°: Ciclo 2 Consolidación | 6°-7°: Ciclo 3 Exploración | 8°-9°: Ciclo 4 Experimentación | 10°-11°: Ciclo 5 Análisis</small>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="dificultad">Dificultad</label>
      <select id="dificultad" name="dificultad">
        <option value="">Selecciona...</option>
        <option value="facil" <?= ($clase['dificultad'] ?? '')==='facil'?'selected':'' ?>>Fácil</option>
        <option value="media" <?= ($clase['dificultad'] ?? '')==='media'?'selected':'' ?>>Media</option>
        <option value="dificil" <?= ($clase['dificultad'] ?? '')==='dificil'?'selected':'' ?>>Difícil</option>
      </select>
    </div>
    <div class="form-group">
      <label for="duracion_minutos">Duración (min)</label>
      <input type="number" id="duracion_minutos" name="duracion_minutos" value="<?= htmlspecialchars((string)($clase['duracion_minutos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" min="1" />
    </div>
  </div>
  <div class="form-group">
    <label for="orden_popularidad">Orden de popularidad</label>
    <input type="number" id="orden_popularidad" name="orden_popularidad" value="<?= htmlspecialchars((string)($clase['orden_popularidad'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-group">
    <label for="resumen">Resumen</label>
    <textarea id="resumen" name="resumen" rows="2" placeholder="Descripción corta..."><?= htmlspecialchars($clase['resumen'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="objetivo_aprendizaje">Objetivo de aprendizaje</label>
    <textarea id="objetivo_aprendizaje" name="objetivo_aprendizaje" rows="2" placeholder="Competencias MEN y objetivos..."><?= htmlspecialchars($clase['objetivo_aprendizaje'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <!-- Multimedia -->
  <div class="form-section">
    <h2>Multimedia</h2>
  <div class="form-row">
    <div class="form-group">
      <label for="imagen_portada">Imagen portada (URL)</label>
      <input type="text" id="imagen_portada" name="imagen_portada" value="<?= htmlspecialchars($clase['imagen_portada'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label for="video_portada">Video portada (URL)</label>
      <input type="text" id="video_portada" name="video_portada" value="<?= htmlspecialchars($clase['video_portada'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    </div>
  </div>
  </div>
  <!-- Seguridad -->
  <div class="form-section">
    <h2>Seguridad</h2>
  <?php $seg = $clase['seguridad'] ? json_decode($clase['seguridad'], true) : null; ?>
  <div class="form-row">
    <div class="form-group">
      <label for="seg_edad_min">Edad mínima <small style="color: #666;">(se prellena según ciclo)</small></label>
      <input type="number" id="seg_edad_min" name="seg_edad_min" value="<?= htmlspecialchars((string)($seg['edad_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label for="seg_edad_max">Edad máxima <small style="color: #666;">(se prellena según ciclo)</small></label>
      <input type="number" id="seg_edad_max" name="seg_edad_max" value="<?= htmlspecialchars((string)($seg['edad_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="seg_notas">Notas de seguridad</label>
    <textarea id="seg_notas" name="seg_notas" rows="2"><?= htmlspecialchars($seg['notas'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  </div>
  <!-- Estado/Publicación -->
  <div class="form-section">
    <h2>Publicación</h2>
  <div class="form-row">
    <div class="form-group">
      <label for="autor">Autor</label>
      <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($clase['autor'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label for="status">Estado</label>
      <select id="status" name="status">
        <option value="draft" <?= ($clase['status'] ?? 'draft')==='draft'?'selected':'' ?>>Borrador</option>
        <option value="published" <?= ($clase['status'] ?? 'draft')==='published'?'selected':'' ?>>Publicado</option>
      </select>
    </div>
    <div class="form-group">
      <label for="published_at">Publicado en</label>
      <input type="datetime-local" id="published_at" name="published_at" value="<?= ($clase['published_at'] ? date('Y-m-d\TH:i', strtotime($clase['published_at'])) : '') ?>" />
    </div>
  </div>
  </div>
  <!-- Contenido HTML -->
  <div class="form-section">
    <h2>Contenido</h2>
  <div class="form-group">
    <label for="contenido_html">Contenido (HTML)</label>
    <textarea id="contenido_html" name="contenido_html" rows="12"><?= htmlspecialchars($clase['contenido_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="help-text">Puedes editar como HTML; se validará en el frontend.</small>
  </div>
  </div>

  <?php
  // Ficha técnica para Clase
  $attrs_defs = [];
  $attrs_vals = [];
  if ($is_edit) {
    try {
      $defs_stmt = $pdo->prepare('SELECT d.*, m.orden FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
      $defs_stmt->execute(['clase']);
      $attrs_defs = $defs_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $attrs_defs = []; }
    try {
      $vals_stmt = $pdo->prepare('SELECT * FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? ORDER BY orden ASC, id ASC');
      $vals_stmt->execute(['clase', $id]);
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
      <label for="attr_search_cls">Agregar atributo</label>
      <div class="component-selector-container">
        <div class="selected-components" id="selected-attrs-cls">
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
            <button type="button" class="edit-component js-edit-attr-cls" title="Editar"
              data-attr-id="<?= $aid ?>"
              data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
              data-tipo="<?= htmlspecialchars($def['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>"
              data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
              data-units='<?= $def['unidades_permitidas_json'] ? $def['unidades_permitidas_json'] : "[]" ?>'
              data-unidad_def="<?= htmlspecialchars($def['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-values='<?= htmlspecialchars(json_encode($values), ENT_QUOTES, "UTF-8") ?>'
            >✏️</button>
            <button type="button" class="remove-component js-delete-attr-cls" data-def-id="<?= $aid ?>" title="Remover">×</button>
          </div>
          <?php endforeach; ?>
        </div>
        <input type="text" id="attr_search_cls" placeholder="Escribir para buscar atributo..." autocomplete="off" />
        <div class="attr-actions" style="margin-top:6px;">
          <button type="button" class="btn btn-secondary" id="btn_create_attr_cls">➕ Crear atributo</button>
        </div>
        <datalist id="attrs_list_cls">
          <?php foreach ($attrs_defs as $def): ?>
            <option value="<?= (int)$def['id'] ?>" data-name="<?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?>" data-clave="<?= htmlspecialchars($def['clave'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($def['grupo'] ?? 'ficha', ENT_QUOTES, 'UTF-8') ?>)
            </option>
          <?php endforeach; ?>
        </datalist>
        <div class="autocomplete-dropdown" id="attr_autocomplete_dropdown_cls"></div>
      </div>
      <small>Escribe para buscar atributos. Al seleccionar, edita su valor en el modal.</small>
    </div>
  </div>

  <?php endif; ?>
  
  <!-- Kits de Materiales (Autocomplete con Chips) -->
  <div class="form-section">
    <h2>Kits de Materiales</h2>
  <div class="form-group">
    <label for="kit_search">Buscar Kits</label>
    <div class="kit-selector-container">
      <div class="selected-kits" id="selected-kits">
        <?php 
        // Renderizar kits ya seleccionados
        foreach ($existing_kit_ids as $kit_id) {
          foreach ($all_kits as $kit) {
            if ($kit['id'] == $kit_id) {
              echo '<div class="kit-chip" data-kit-id="' . $kit['id'] . '">';
              echo '<span>' . htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') . '</span>';
              echo '<button type="button" class="edit-kit" onclick="editKit(' . $kit['id'] . ')" title="Editar kit">✏️</button>';
              echo '<button type="button" class="remove-kit" onclick="removeKit(this)" title="Remover kit">×</button>';
              echo '</div>';
              break;
            }
          }
        }
        ?>
      </div>
      <input type="text" id="kit_search" placeholder="Escribir para buscar kit..." autocomplete="off" />
      <datalist id="kits_list">
        <?php foreach ($all_kits as $kit): ?>
          <?php if ($kit['activo']): ?>
            <option value="<?= $kit['id'] ?>" data-name="<?= htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($kit['codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') ?>
              <?= $kit['codigo'] ? ' (' . htmlspecialchars($kit['codigo'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
            </option>
          <?php endif; ?>
        <?php endforeach; ?>
      </datalist>
      <div class="autocomplete-dropdown" id="autocomplete_dropdown"></div>
    </div>
    <small>Escribe para buscar kits. Puedes seleccionar múltiples. El primero será el kit principal.</small>
    <div id="kits-hidden"></div>
  </div>
  </div>
  
  <!-- Taxonomías -->
  <div class="form-section">
    <h2>Taxonomías</h2>
    
    <!-- Tags -->
    <div class="form-group">
      <label for="tags">Tags (separados por coma)</label>
      <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(implode(', ', $existing_tags), ENT_QUOTES, 'UTF-8') ?>" />
      <small>Palabras clave para búsqueda y categorización</small>
    </div>
    
    <!-- Áreas -->
    <h3 style="margin-top: 1rem;">Áreas</h3>
    <div class="checkbox-grid">
      <?php foreach ($areas as $a): ?>
        <label class="checkbox-label"><input type="checkbox" name="areas[]" value="<?= (int)$a['id'] ?>" <?= in_array($a['id'], $existing_area_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></label>
      <?php endforeach; ?>
    </div>
    
    <!-- Competencias MEN - Dual Listbox -->
    <h3 style="margin-top:.5rem">Competencias MEN</h3>
    <small class="hint" style="display: block; margin-bottom: 10px;">Selecciona las competencias que desarrolla esta clase. Recomendado: 3-7 competencias.</small>
    
    <div class="dual-listbox-container">
      <div class="listbox-panel">
        <div class="listbox-header">
          <strong>Disponibles</strong>
          <span id="available-count" class="counter">(<?= count($competencias) ?>)</span>
        </div>
        <input type="text" id="search-competencias" class="listbox-search" placeholder="🔍 Buscar competencias...">
        
        <div class="listbox-content" id="available-list">
          <?php 
          // Agrupar competencias por categoría principal
          $grupos = [
            'CB' => ['nombre' => 'Ciencias Naturales, Matemáticas y Lenguaje', 'icon' => '🔬', 'items' => []],
            'CC' => ['nombre' => 'Competencias Ciudadanas', 'icon' => '🤝', 'items' => []],
            'CLG' => ['nombre' => 'Competencias Laborales Generales', 'icon' => '💼', 'items' => []],
            'NCP' => ['nombre' => 'Nuevas Competencias 2025', 'icon' => '🆕', 'items' => []],
            'TRANS' => ['nombre' => 'Transversales (Recomendadas)', 'icon' => '⭐', 'items' => []]
          ];
          
          foreach ($competencias as $c) {
            $prefix = explode('-', $c['codigo'])[0];
            if (isset($grupos[$prefix])) {
              $grupos[$prefix]['items'][] = $c;
            }
          }
          
          foreach ($grupos as $key => $grupo):
            if (empty($grupo['items'])) continue;
          ?>
            <div class="competencia-grupo" data-grupo="<?= $key ?>">
              <div class="grupo-header" onclick="toggleGrupo(this)">
                <span class="grupo-toggle">▼</span>
                <?= $grupo['icon'] ?> <strong><?= $grupo['nombre'] ?></strong>
                <span class="grupo-count">(<?= count($grupo['items']) ?>)</span>
              </div>
              <div class="grupo-items">
                <?php foreach ($grupo['items'] as $c): 
                  $isSelected = in_array($c['id'], $existing_comp_ids);
                ?>
                  <div class="competencia-item <?= $isSelected ? 'hidden' : '' ?>" 
                       data-id="<?= $c['id'] ?>"
                       data-codigo="<?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?>"
                       data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                       data-explicacion="<?= htmlspecialchars($c['explicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       onclick="selectCompetencia(this)">
                    <span class="comp-codigo"><?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($c['explicacion'])): ?>
                      <button type="button" class="info-btn" onclick="event.stopPropagation(); showTooltip(this, '<?= htmlspecialchars($c['explicacion'], ENT_QUOTES, 'UTF-8') ?>')" title="Ver explicación">ℹ️</button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <div class="listbox-buttons">
        <button type="button" onclick="moveAll(true)" title="Agregar todas">➡️</button>
        <button type="button" onclick="moveAll(false)" title="Quitar todas">⬅️</button>
      </div>
      
      <div class="listbox-panel">
        <div class="listbox-header">
          <strong>Seleccionadas</strong>
          <span id="selected-count" class="counter">(0)</span>
        </div>
        <div class="listbox-content" id="selected-list">
          <?php foreach ($competencias as $c): 
            if (in_array($c['id'], $existing_comp_ids)):
          ?>
            <div class="competencia-item selected" 
                 data-id="<?= $c['id'] ?>"
                 data-codigo="<?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?>"
                 data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                 onclick="deselectCompetencia(this)">
              <span class="comp-codigo"><?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              <button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectCompetencia(this.parentElement)">×</button>
            </div>
          <?php 
            endif;
          endforeach; 
          ?>
        </div>
        <small class="hint" style="margin-top: 10px; display: block;">Haz clic para quitar</small>
      </div>
      
      <!-- Hidden inputs para enviar al servidor -->
      <div id="competencias-hidden"></div>
    </div>
  </div>
  <!-- SEO -->
  <div class="form-section">
    <h2>SEO</h2>
  <div class="form-row">
    <div class="form-group">
      <label for="seo_title">SEO Title (≤60)</label>
      <div style="display: flex; gap: 8px; align-items: center;">
        <input type="text" id="seo_title" name="seo_title" maxlength="160" value="<?= htmlspecialchars($clase['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="flex: 1;" />
        <button type="button" id="btn_generar_seo" style="padding: 8px 16px; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">⚡ Generar SEO</button>
      </div>
    </div>
    <div class="form-group">
      <label for="seo_description">SEO Description (≤160)</label>
      <input type="text" id="seo_description" name="seo_description" maxlength="255" value="<?= htmlspecialchars($clase['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    </div>
  </div>
  <!-- SEO Auto Preview + Override Toggle -->
  <div class="form-section">
    <label><input type="checkbox" id="seo_override_toggle"> Editar SEO manualmente</label>
    <div class="seo-preview">
      <p><strong>Preview Title:</strong> <span id="seo_preview_title"></span></p>
      <p><strong>Preview Description:</strong> <span id="seo_preview_desc"></span></p>
      <small class="help-text">Si no defines SEO manualmente, se usarán estos valores.</small>
    </div>
  </div>
  
  <div id="seo-manual">
    <!-- Los campos SEO arriba funcionan como override cuando este panel está activo -->
  </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn">Guardar</button>
    <a href="/admin/clases/index.php" class="btn btn-secondary">Cancelar</a>
    <?php if ($is_edit): ?>
      <a href="/<?= htmlspecialchars($clase['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn">Ver público</a>
    <?php endif; ?>
  </div>
</form>

<?php if ($is_edit): ?>
<!-- Hidden delete form for Clase attributes (outside main form) -->
<form method="POST" id="formDeleteAttrCls" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <input type="hidden" name="action" value="delete_attr" />
  <input type="hidden" name="def_id" id="delete_def_id_cls" />
</form>

<!-- Modal Editar Atributo (Clase) - moved outside main form -->
<div class="modal-overlay" id="modalEditAttrCls">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalEditAttrClsTitle">
    <div class="modal-header">
      <h4 id="modalEditAttrClsTitle">Editar atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalEditAttrCls">✖</button>
    </div>
    <form method="POST" id="formEditAttrCls">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="update_attr" />
      <input type="hidden" name="def_id" id="edit_def_id_cls" />
      <div class="modal-body">
        <div class="muted" id="editAttrClsInfo"></div>
        <div class="form-group">
          <label for="edit_valor_cls">Valor</label>
          <textarea id="edit_valor_cls" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
        </div>
        <div class="form-group" id="edit_unidad_cls_group">
          <label for="edit_unidad_cls">Unidad (si aplica)</label>
          <select id="edit_unidad_cls" name="unidad"></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditAttrCls">Cancelar</button>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Agregar Atributo (Clase) - moved outside main form -->
<div class="modal-overlay" id="modalAddAttrCls">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalAddAttrClsTitle">
    <div class="modal-header">
      <h4 id="modalAddAttrClsTitle">Agregar atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalAddAttrCls">✖</button>
    </div>
    <form method="POST" id="formAddAttrCls">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="add_attr" />
      <input type="hidden" name="def_id" id="add_def_id_cls" />
      <div class="modal-body">
        <div class="muted" id="addAttrClsInfo"></div>
        <div class="form-group">
          <label for="add_valor_cls">Valor</label>
          <textarea id="add_valor_cls" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
        </div>
        <div class="form-group" id="add_unidad_cls_group">
          <label for="add_unidad_cls">Unidad (si aplica)</label>
          <select id="add_unidad_cls" name="unidad"></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddAttrCls">Cancelar</button>
        <button type="submit" class="btn">Agregar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Crear Definición de Atributo (Clase) - moved outside main form -->
<div class="modal-overlay" id="modalCreateAttrCls">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalCreateAttrClsTitle">
    <div class="modal-header">
      <h4 id="modalCreateAttrClsTitle">Crear nuevo atributo</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalCreateAttrCls">✖</button>
    </div>
    <form method="POST" id="formCreateAttrCls">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="create_attr_def" />
      <div class="modal-body">
        <div class="form-group"><label for="create_etiqueta_cls">Etiqueta</label><input type="text" id="create_etiqueta_cls" name="etiqueta" required /></div>
        <div class="form-group"><label for="create_clave_cls">Clave</label><input type="text" id="create_clave_cls" name="clave" placeholder="auto desde etiqueta si se deja vacío" /></div>
        <div class="field-inline">
          <div class="form-group"><label for="create_tipo_cls">Tipo</label>
            <select id="create_tipo_cls" name="tipo_dato">
              <option value="string">string</option>
              <option value="number">number</option>
              <option value="integer">integer</option>
              <option value="boolean">boolean</option>
              <option value="date">date</option>
              <option value="datetime">datetime</option>
              <option value="json">json</option>
            </select>
          </div>
          <div class="form-group"><label for="create_card_cls">Cardinalidad</label>
            <select id="create_card_cls" name="cardinalidad">
              <option value="one">one</option>
              <option value="many">many</option>
            </select>
          </div>
        </div>
        <div class="field-inline">
          <div class="form-group"><label for="create_unidad_cls">Unidad por defecto</label><input type="text" id="create_unidad_cls" name="unidad_defecto" placeholder="opcional" /></div>
          <div class="form-group"><label for="create_unidades_cls">Unidades permitidas</label><input type="text" id="create_unidades_cls" name="unidades_permitidas" placeholder="separa por comas" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalCreateAttrCls">Cancelar</button>
        <button type="submit" class="btn">Crear</button>
      </div>
    </form>
  </div>
 </div>
<?php endif; ?>

<!-- Editor: CKEditor 4 (matches article-edit, no API key) -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  const nombreInput = document.getElementById('nombre');
  const slugInput = document.getElementById('slug');
  const resumenInput = document.getElementById('resumen');
  const seoTitleInput = document.getElementById('seo_title');
  const seoDescInput = document.getElementById('seo_description');
  const seoPrevTitle = document.getElementById('seo_preview_title');
  const seoPrevDesc = document.getElementById('seo_preview_desc');
  const seoToggle = document.getElementById('seo_override_toggle');
  const seoManual = document.getElementById('seo-manual');

  function textFromHtml(html) {
    // Quita etiquetas y normaliza espacios
    const tmp = document.createElement('div');
    tmp.innerHTML = html || '';
    const txt = (tmp.textContent || tmp.innerText || '').replace(/\s+/g,' ').trim();
    return txt;
  }
  function shortenAtWord(str, maxLen) {
    if (!str) return '';
    if (str.length <= maxLen) return str;
    const cut = str.slice(0, maxLen);
    return cut.replace(/\s+\S*$/, '').trim();
  }
  function computeSeo(forceRegenerate = false) {
    // Generar SEO Title educativo simple y claro
    let areaNombre = '';
    
    // Extraer área seleccionada
    const areasCheckboxes = document.querySelectorAll('input[name="areas[]"]:checked');
    if (areasCheckboxes.length > 0) {
      const areaLabel = areasCheckboxes[0].closest('label');
      if (areaLabel) {
        areaNombre = areaLabel.textContent.trim();
      }
    }
    
    // Construir título educativo con límite 60 chars
    const base = 'Clase de Ciencia - ';
    const nombreVal = (nombreInput && nombreInput.value) ? nombreInput.value.trim() : '';
    let autoTitle = '';
    
    if (areaNombre !== '') {
      // Intentar formato: "Clase de Ciencia - [Área]: [Nombre]"
      const formato1 = base + areaNombre + ': ' + nombreVal;
      if (formato1.length <= 60) {
        autoTitle = formato1;
      } else {
        // Si no cabe, formato alternativo: "Clase de Ciencia - [Nombre corto] | [Área]"
        const separador = ' | ' + areaNombre;
        const maxNombre = 60 - base.length - separador.length;
        const nombreCorto = nombreVal.length > maxNombre ? nombreVal.substring(0, maxNombre-3) + '...' : nombreVal;
        autoTitle = base + nombreCorto + separador;
      }
    } else {
      const maxNombre = 60 - base.length;
      const nombreCorto = nombreVal.length > maxNombre ? nombreVal.substring(0, maxNombre-3) + '...' : nombreVal;
      autoTitle = base + nombreCorto;
    }
    
    // Generar descripción con ciclo/grados
    let descSrc = (resumenInput && resumenInput.value.trim()) ? resumenInput.value.trim() : '';
    if (!descSrc) {
      try {
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.contenido_html) {
          descSrc = textFromHtml(CKEDITOR.instances.contenido_html.getData() || '');
        } else {
          const ta = document.getElementById('contenido_html');
          descSrc = textFromHtml(ta ? ta.value : '');
        }
      } catch(e) { descSrc = ''; }
    }
    
    // Agregar prefijo con ciclo y grados
    let cicloInfo = '';
    const cicloSelect = document.getElementById('ciclo');
    if (cicloSelect && cicloSelect.value) {
      const selectedOption = cicloSelect.options[cicloSelect.selectedIndex];
      if (selectedOption && selectedOption.text) {
        // Extraer "Ciclo X: Nombre (grados)" del texto de la opción
        const match = selectedOption.text.match(/Ciclo (\d+):.*?\(([^)]+)\)/);
        if (match) {
          cicloInfo = 'Ciclo ' + match[1] + ' (' + match[2] + '): ';
        }
      }
    }
    
    const maxDesc = 160 - cicloInfo.length;
    const descTruncada = shortenAtWord(descSrc, maxDesc);
    const autoDesc = cicloInfo + descTruncada;
    
    // Render preview
    if (seoPrevTitle) seoPrevTitle.textContent = autoTitle;
    if (seoPrevDesc) seoPrevDesc.textContent = autoDesc;
    
    // If manual not enabled and inputs are empty (or forceRegenerate), mirror preview into inputs
    if (!seoToggle?.checked || forceRegenerate) {
      if (seoTitleInput && (!seoTitleInput.value || forceRegenerate)) seoTitleInput.value = autoTitle;
      if (seoDescInput && (!seoDescInput.value || forceRegenerate)) seoDescInput.value = autoDesc;
      console.log('🔍 [SEO] autogenerados:', {area: areaNombre, title: autoTitle.substring(0,50)+'...', forced: forceRegenerate});
    }
  }
  // Toggle manual panel
  if (seoToggle && seoManual) {
    seoToggle.addEventListener('change', ()=>{
      seoManual.style.display = seoToggle.checked ? 'block' : 'none';
      console.log(seoToggle.checked ? '✅ [SEO] override manual activado' : '🔍 [SEO] usando auto');
    });
  }
  function normalizeSlugBase(val){
    return (val || '').toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '');
  }
  function ensureClasePrefix(val){
    const base = normalizeSlugBase(val);
    return base.startsWith('clase-') ? base : ('clase-' + (base.replace(/^clase-+/,'').replace(/^-+/,'')));
  }

  nombreInput.addEventListener('blur', () => {
    console.log('🔍 [ClasesEdit] blur nombre');
    if (!slugInput.value && nombreInput.value) {
      const s = ensureClasePrefix(nombreInput.value);
      slugInput.value = s;
      console.log('✅ [ClasesEdit] slug generado:', s);
    }
    computeSeo();
  });
  
  // Botón generar slug
  const btnGenerarSlug = document.getElementById('btn_generar_slug');
  if (btnGenerarSlug) {
    btnGenerarSlug.addEventListener('click', () => {
      const nombreVal = nombreInput.value.trim();
      if (!nombreVal) {
        alert('Por favor ingresa el nombre de la clase primero');
        nombreInput.focus();
        return;
      }
      const s = ensureClasePrefix(nombreVal);
      slugInput.value = s;
      console.log('⚡ [ClasesEdit] slug generado con botón:', s);
      computeSeo();
    });
  }

  // Asegurar prefijo al editar manualmente el slug
  if (slugInput) {
    slugInput.addEventListener('blur', () => {
      if (slugInput.value) {
        const fixed = ensureClasePrefix(slugInput.value);
        if (fixed !== slugInput.value) {
          console.log('⚠️ [ClasesEdit] corrigiendo slug con prefijo:', fixed);
          slugInput.value = fixed;
        }
      }
    });
  }
  
  // Botón generar SEO
  const btnGenerarSeo = document.getElementById('btn_generar_seo');
  if (btnGenerarSeo) {
    btnGenerarSeo.addEventListener('click', () => {
      if (!nombreInput.value.trim()) {
        alert('Por favor ingresa el nombre de la clase primero');
        nombreInput.focus();
        return;
      }
      const areasChecked = document.querySelectorAll('input[name="areas[]"]:checked');
      const compSelected = document.getElementById('selected-list')?.querySelectorAll('.competencia-item');
      
      if (areasChecked.length === 0) {
        alert('Por favor selecciona al menos un área para generar un SEO educativo óptimo');
        return;
      }
      if (!compSelected || compSelected.length === 0) {
        alert('Por favor asigna al menos una competencia para un SEO más descriptivo');
        return;
      }
      
      computeSeo(true); // Force regenerate
      console.log('⚡ [ClasesEdit] SEO regenerado manualmente');
      
      // Visual feedback
      seoTitleInput.style.background = '#e6f7ff';
      seoDescInput.style.background = '#e6f7ff';
      setTimeout(() => {
        seoTitleInput.style.background = '';
        seoDescInput.style.background = '';
      }, 1000);
    });
  }
  
  console.log('ℹ️ [ClasesEdit] Campo Sección ocultado (no requerido)');
  if (slugInput) slugInput.addEventListener('input', computeSeo);
  if (resumenInput) resumenInput.addEventListener('input', computeSeo);
  
  // Actualizar SEO cuando cambien áreas o competencias
  document.querySelectorAll('input[name="areas[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      console.log('🔍 [SEO] Área cambiada, recalculando...');
      computeSeo();
    });
  });
  
  // Observar cambios en el dual listbox de competencias (cuando se seleccionen/deseleccionen)
  const selectedListDiv = document.getElementById('selected-list');
  if (selectedListDiv) {
    const observer = new MutationObserver(() => {
      console.log('🔍 [SEO] Competencias cambiadas, recalculando...');
      computeSeo();
    });
    observer.observe(selectedListDiv, { childList: true, subtree: true });
  }
  
  // Validación simple de SEO
  const seoTitle = document.getElementById('seo_title');
  const seoDesc = document.getElementById('seo_description');
  if (seoTitle) seoTitle.addEventListener('input', ()=>{ if (seoTitle.value.length>160) console.log('⚠️ [ClasesEdit] SEO title >160'); });
  if (seoDesc) seoDesc.addEventListener('input', ()=>{ if (seoDesc.value.length>255) console.log('⚠️ [ClasesEdit] SEO description >255'); });

  // Integración CKEditor 4 (como article-edit)
  (function initCKE() {
    try {
      if (window.CKEDITOR) {
        CKEDITOR.replace('contenido_html', {
          height: 500,
          // Mantener plugins requeridos; solo ocultar elementspath
          removePlugins: 'elementspath',
          resize_enabled: true
        });
        console.log('✅ [ClasesEdit] CKEditor 4 cargado');
        // Live SEO compute on editor changes
        const instReady = () => {
          const inst = CKEDITOR.instances && CKEDITOR.instances.contenido_html;
          if (inst) {
            inst.on('change', computeSeo);
            inst.on('key', computeSeo);
          }
        };
        // If ready now, attach; otherwise wait
        if (CKEDITOR.instances && CKEDITOR.instances.contenido_html) instReady();
        else CKEDITOR.on('instanceReady', instReady);
      } else {
        console.log('⚠️ [ClasesEdit] CKEditor no disponible, usando textarea simple');
      }
    } catch(e) {
      console.log('❌ [ClasesEdit] Error iniciando CKEditor:', e.message);
    }
  })();

  // Oculta avisos de CKEditor sobre versión insegura (igual a article-edit)
  function removeCKEditorWarnings() {
    try {
      // Remueve únicamente notificaciones específicas, no contenedores
      const selectors = [
        '.cke_notification.cke_notification_warning',
        '.cke_upgrade_notice',
        '.cke_browser_warning',
        '.cke_panel_warning',
        '.cke_warning'
      ];
      selectors.forEach(sel => {
        document.querySelectorAll(sel).forEach(el => el.remove());
      });
      console.log('🔍 [ClasesEdit] CKEditor warnings revisados');
    } catch (e) {
      console.log('⚠️ [ClasesEdit] removeCKEditorWarnings error:', e.message);
    }
  }
  setTimeout(removeCKEditorWarnings, 800);
  const ckeObserver = new MutationObserver((mutations, obs) => {
    let removed = false;
    for (const m of mutations) {
      for (const node of m.addedNodes) {
        if (node instanceof HTMLElement) {
          if (node.matches && node.matches('.cke_notification.cke_notification_warning')) {
            node.remove();
            removed = true;
          } else {
            const found = node.querySelector && node.querySelector('.cke_notification.cke_notification_warning');
            if (found) { found.remove(); removed = true; }
          }
        }
      }
    }
    if (removed) { obs.disconnect(); console.log('✅ [ClasesEdit] CKEditor warning ocultado'); }
  });
  ckeObserver.observe(document.documentElement || document.body, { childList: true, subtree: true });

  // Asegurar sincronización del editor antes de enviar
  const formEl = document.querySelector('form');
  if (formEl) {
    formEl.addEventListener('submit', function() {
      try {
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.contenido_html) {
          CKEDITOR.instances.contenido_html.updateElement();
          console.log('✅ [ClasesEdit] CKEditor contenido sincronizado');
        }
      } catch(e) { console.log('⚠️ [ClasesEdit] No se pudo sincronizar CKEditor:', e.message); }
      computeSeo();
    });
  }
  // Inicializa preview al cargar
  computeSeo();

  // ============================================================
  // PRELLENADO AUTOMÁTICO DE GRADOS Y EDAD SEGÚN CICLO
  // ============================================================
  const ciclosData = <?= json_encode($ciclos_list) ?>;
  const cicloSelect = document.getElementById('ciclo');
  const gradosCheckboxes = document.querySelectorAll('input[name="grados[]"]');
  const edadMinInput = document.getElementById('seg_edad_min');
  const edadMaxInput = document.getElementById('seg_edad_max');

  if (cicloSelect) {
    cicloSelect.addEventListener('change', function() {
      const cicloNumero = parseInt(this.value);
      const cicloInfo = ciclosData.find(c => c.numero === cicloNumero);
      
      if (cicloInfo) {
        console.log('🔍 [ClasesEdit] Ciclo seleccionado:', cicloInfo.nombre);
        
        // Actualizar SEO cuando cambie el ciclo
        computeSeo();
        
        // Prellenar grados (si grados es un array de números)
        try {
          const gradosArray = JSON.parse(cicloInfo.grados);
          
          // Desmarcar todos los checkboxes primero
          gradosCheckboxes.forEach(cb => cb.checked = false);
          
          // Marcar solo los grados del ciclo (si son números)
          if (Array.isArray(gradosArray)) {
            gradosCheckboxes.forEach(cb => {
              const gradoValue = parseInt(cb.value);
              if (gradosArray.includes(gradoValue)) {
                cb.checked = true;
              }
            });
            console.log('✅ [ClasesEdit] Grados prellenados:', gradosArray);
          }
        } catch(e) {
          console.log('⚠️ [ClasesEdit] Error parseando grados:', e.message);
        }
        
        // Prellenar edades
        if (edadMinInput && cicloInfo.edad_min) {
          edadMinInput.value = cicloInfo.edad_min;
          console.log('✅ [ClasesEdit] Edad mínima:', cicloInfo.edad_min);
        }
        if (edadMaxInput && cicloInfo.edad_max) {
          edadMaxInput.value = cicloInfo.edad_max;
          console.log('✅ [ClasesEdit] Edad máxima:', cicloInfo.edad_max);
        }
      }
    });
  }
  
  // ========================================================
  // DUAL LISTBOX - COMPETENCIAS MEN
  // ========================================================
  
  // Inicializar contadores y hidden inputs
  function initCompetencias() {
    updateCounts();
    syncHiddenInputs();
  }
  
  // Toggle grupo colapsable
  window.toggleGrupo = function(header) {
    const grupo = header.parentElement;
    const items = grupo.querySelector('.grupo-items');
    const toggle = header.querySelector('.grupo-toggle');
    
    if (items.style.display === 'none') {
      items.style.display = 'block';
      toggle.textContent = '▼';
    } else {
      items.style.display = 'none';
      toggle.textContent = '▶';
    }
  };
  
  // Seleccionar competencia (mover a seleccionadas)
  window.selectCompetencia = function(item) {
    if (item.classList.contains('hidden')) return;
    
    const selectedList = document.getElementById('selected-list');
    const clone = item.cloneNode(true);
    clone.classList.add('selected');
    clone.classList.remove('hidden');
    clone.onclick = function() { deselectCompetencia(this); };
    
    // Agregar botón X
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-btn';
    removeBtn.textContent = '×';
    removeBtn.onclick = function(e) {
      e.stopPropagation();
      deselectCompetencia(clone);
    };
    
    // Remover botón info si existe
    const infoBtn = clone.querySelector('.info-btn');
    if (infoBtn) infoBtn.remove();
    
    clone.appendChild(removeBtn);
    selectedList.appendChild(clone);
    
    // Ocultar en disponibles
    item.classList.add('hidden');
    
    updateCounts();
    syncHiddenInputs();
    console.log('✅ [Competencias] Agregada:', item.dataset.codigo);
  };
  
  // Deseleccionar competencia (mover a disponibles)
  window.deselectCompetencia = function(item) {
    const id = item.dataset.id;
    
    // Mostrar en disponibles
    const availableItem = document.querySelector(`#available-list .competencia-item[data-id="${id}"]`);
    if (availableItem) {
      availableItem.classList.remove('hidden');
    }
    
    // Remover de seleccionadas
    item.remove();
    
    updateCounts();
    syncHiddenInputs();
    console.log('❌ [Competencias] Removida:', item.dataset.codigo);
  };
  
  // Mover todas
  window.moveAll = function(toSelected) {
    if (toSelected) {
      const available = document.querySelectorAll('#available-list .competencia-item:not(.hidden)');
      available.forEach(item => selectCompetencia(item));
    } else {
      const selected = document.querySelectorAll('#selected-list .competencia-item');
      selected.forEach(item => deselectCompetencia(item));
    }
  };
  
  // Actualizar contadores
  function updateCounts() {
    const availableCount = document.querySelectorAll('#available-list .competencia-item:not(.hidden)').length;
    const selectedCount = document.querySelectorAll('#selected-list .competencia-item').length;
    
    document.getElementById('available-count').textContent = `(${availableCount})`;
    document.getElementById('selected-count').textContent = `(${selectedCount})`;
  }
  
  // Sincronizar hidden inputs para envío
  function syncHiddenInputs() {
    const container = document.getElementById('competencias-hidden');
    container.innerHTML = '';
    
    const selected = document.querySelectorAll('#selected-list .competencia-item');
    selected.forEach(item => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'competencias[]';
      input.value = item.dataset.id;
      container.appendChild(input);
    });
  }
  
  // Búsqueda en tiempo real
  const searchInput = document.getElementById('search-competencias');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const items = document.querySelectorAll('#available-list .competencia-item');
      
      items.forEach(item => {
        if (item.classList.contains('hidden')) return; // Ya seleccionada
        
        const codigo = item.dataset.codigo.toLowerCase();
        const nombre = item.dataset.nombre.toLowerCase();
        const explicacion = item.dataset.explicacion ? item.dataset.explicacion.toLowerCase() : '';
        
        const matches = codigo.includes(query) || nombre.includes(query) || explicacion.includes(query);
        
        if (matches || query === '') {
          item.style.display = 'flex';
        } else {
          item.style.display = 'none';
        }
      });
      
      // Actualizar contador de disponibles (solo visibles)
      const visibleCount = document.querySelectorAll('#available-list .competencia-item:not(.hidden)').length;
      const availableCountSpan = document.getElementById('available-count');
      if (query) {
        const filteredCount = Array.from(document.querySelectorAll('#available-list .competencia-item:not(.hidden)'))
          .filter(item => item.style.display !== 'none').length;
        availableCountSpan.textContent = `(${filteredCount})`;
      } else {
        availableCountSpan.textContent = `(${visibleCount})`;
      }
    });
  }
  
  // Tooltip simple
  window.showTooltip = function(btn, text) {
    const existing = document.querySelector('.competencia-tooltip');
    if (existing) existing.remove();
    
    const tooltip = document.createElement('div');
    tooltip.className = 'competencia-tooltip';
    tooltip.textContent = text;
    document.body.appendChild(tooltip);
    
    const rect = btn.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    tooltip.style.left = rect.left + 'px';
    
    setTimeout(() => tooltip.remove(), 4000);
    
    tooltip.addEventListener('click', () => tooltip.remove());
  };
  
  // Inicializar
  initCompetencias();
  console.log('✅ [Competencias] Dual Listbox inicializado');
  
  // ========================================================
  // AUTOCOMPLETE KITS CON CHIPS
  // ========================================================
  
  const kitSearchInput = document.getElementById('kit_search');
  const autocompleteDropdown = document.getElementById('autocomplete_dropdown');
  const selectedKitsContainer = document.getElementById('selected-kits');
  const kitsHiddenContainer = document.getElementById('kits-hidden');
  
  // Datos de kits desde PHP
  const allKitsData = <?= json_encode($all_kits) ?>;
  let selectedKitIds = [];
  
  // Inicializar con kits ya seleccionados
  document.querySelectorAll('.kit-chip').forEach(chip => {
    const kitId = parseInt(chip.dataset.kitId);
    if (!selectedKitIds.includes(kitId)) {
      selectedKitIds.push(kitId);
    }
  });
  syncKitsHiddenInputs();
  
  // Buscar kits al escribir
  kitSearchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    
    if (query.length < 2) {
      autocompleteDropdown.innerHTML = '';
      autocompleteDropdown.style.display = 'none';
      return;
    }
    
    // Filtrar kits que matchean y que no están seleccionados
    const matches = allKitsData.filter(kit => {
      if (!kit.activo) return false;
      if (selectedKitIds.includes(kit.id)) return false;
      
      const nombre = kit.nombre.toLowerCase();
      const codigo = kit.codigo ? kit.codigo.toLowerCase() : '';
      
      return nombre.includes(query) || codigo.includes(query);
    });
    
    // Mostrar resultados
    if (matches.length > 0) {
      autocompleteDropdown.innerHTML = matches.slice(0, 10).map(kit => 
        `<div class="autocomplete-item" data-kit-id="${kit.id}" data-kit-name="${escapeHtml(kit.nombre)}">
          <strong>${escapeHtml(kit.nombre)}</strong>
          ${kit.codigo ? '<span class="kit-code">(' + escapeHtml(kit.codigo) + ')</span>' : ''}
        </div>`
      ).join('');
      autocompleteDropdown.style.display = 'block';
      
      // Click en resultado
      autocompleteDropdown.querySelectorAll('.autocomplete-item').forEach(item => {
        item.addEventListener('click', function() {
          const kitId = parseInt(this.dataset.kitId);
          const kitName = this.dataset.kitName;
          addKit(kitId, kitName);
          kitSearchInput.value = '';
          autocompleteDropdown.innerHTML = '';
          autocompleteDropdown.style.display = 'none';
        });
      });
    } else {
      autocompleteDropdown.innerHTML = '<div class="autocomplete-no-results">No se encontraron kits</div>';
      autocompleteDropdown.style.display = 'block';
    }
  });
  
  // Cerrar dropdown al hacer click fuera
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.kit-selector-container')) {
      autocompleteDropdown.style.display = 'none';
    }
  });
  
  // Agregar kit seleccionado
  function addKit(kitId, kitName) {
    if (selectedKitIds.includes(kitId)) return;
    
    selectedKitIds.push(kitId);
    
    const chip = document.createElement('div');
    chip.className = 'kit-chip';
    chip.dataset.kitId = kitId;
    chip.innerHTML = `
      <span>${escapeHtml(kitName)}</span>
      <button type="button" class="edit-kit" onclick="editKit(${kitId})" title="Editar kit">✏️</button>
      <button type="button" class="remove-kit" onclick="removeKit(this)" title="Remover kit">×</button>
    `;
    
    selectedKitsContainer.appendChild(chip);
    syncKitsHiddenInputs();
    
    console.log('✅ [Kits] Agregado kit:', kitId, kitName);
  }
  
  // Remover kit (llamado desde HTML onclick)
  window.removeKit = function(button) {
    const chip = button.parentElement;
    const kitId = parseInt(chip.dataset.kitId);
    
    selectedKitIds = selectedKitIds.filter(id => id !== kitId);
    chip.remove();
    syncKitsHiddenInputs();
    
    console.log('❌ [Kits] Removido kit:', kitId);
  };
  
  // Editar kit (llamado desde HTML onclick)
  window.editKit = function(kitId) {
    const url = '/admin/kits/edit.php?id=' + kitId;
    window.open(url, '_blank');
    console.log('✏️ [Kits] Abriendo editor para kit:', kitId);
  };
  
  // Sincronizar hidden inputs para envío
  function syncKitsHiddenInputs() {
    kitsHiddenContainer.innerHTML = '';
    selectedKitIds.forEach(kitId => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'kits[]';
      input.value = kitId;
      kitsHiddenContainer.appendChild(input);
    });
    console.log('🔍 [Kits] Sincronizados:', selectedKitIds);
  }
  
  // Escape HTML helper
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  console.log('✅ [Kits] Autocomplete inicializado');
  
</script>

<?php if ($is_edit): ?>
<script>
  // Utilidades de modal
  function openModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.add('active'); console.log('🔍 [ClasesEdit] Abre modal', sel); }
  }
  function closeModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.remove('active'); console.log('🔍 [ClasesEdit] Cierra modal', sel); }
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

  // Autocomplete + modal para atributos de clase
  (function initAttrUICls(){
    const dropdown = document.getElementById('attr_autocomplete_dropdown_cls');
    const input = document.getElementById('attr_search_cls');
    const selectedWrap = document.getElementById('selected-attrs-cls');
    if (!dropdown || !input || !selectedWrap) { console.log('⚠️ [ClasesEdit] UI atributos no inicializada'); return; }

    const defs = [
      <?php foreach ($attrs_defs as $d): ?>
      { id: <?= (int)$d['id'] ?>, label: '<?= htmlspecialchars($d['etiqueta'], ENT_QUOTES, 'UTF-8') ?>', tipo: '<?= htmlspecialchars($d['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>', card: '<?= htmlspecialchars($d['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>', units: <?= $d['unidades_permitidas_json'] ? $d['unidades_permitidas_json'] : '[]' ?>, unitDef: '<?= htmlspecialchars($d['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>' },
      <?php endforeach; ?>
    ];

    function normalize(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
    function render(list){
      if (!list.length){ dropdown.innerHTML = '<div class="autocomplete-item"><span class="cmp-code">Sin resultados</span></div><div class="autocomplete-item create-item" id="attr_create_item_cls"><strong>➕ Crear nuevo atributo</strong></div>'; dropdown.style.display='block'; const ci=document.getElementById('attr_create_item_cls'); if(ci){ ci.addEventListener('click', onCreateNewCls); } return; }
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
      console.log('🔍 [ClasesEdit] Buscar atributo:', q, '→', out.length);
      render(out);
    }
    function onChoose(def){
      try {
        document.getElementById('add_def_id_cls').value = String(def.id);
        document.getElementById('addAttrClsInfo').textContent = def.label;
        const sel = document.getElementById('add_unidad_cls');
        const selGroup = document.getElementById('add_unidad_cls_group');
        sel.innerHTML = '';
        const hasUnits = Array.isArray(def.units) && def.units.length > 0;
        const hasDefault = !!def.unitDef;
        if (hasUnits || hasDefault) {
          const opt0 = document.createElement('option');
          opt0.value = ''; opt0.textContent = def.unitDef ? `(por defecto: ${def.unitDef})` : '(sin unidad)'; sel.appendChild(opt0);
          if (hasUnits) { def.units.forEach(u => { const o = document.createElement('option'); o.value = u; o.textContent = u; sel.appendChild(o); }); }
          if (selGroup) selGroup.style.display = '';
          console.log('🔍 [ClasesEdit] Unidad visible (aplica)');
        } else {
          if (selGroup) selGroup.style.display = 'none';
          console.log('🔍 [ClasesEdit] Unidad oculta (no aplica)');
        }
        openModal('#modalAddAttrCls');
        setTimeout(() => { try { document.getElementById('add_valor_cls')?.focus(); } catch(_e){} }, 50);
      } catch (e) {
        console.log('❌ [ClasesEdit] Error preparar modal atributo:', e && e.message);
      }
      dropdown.style.display = 'none';
    }
    function onCreateNewCls(){
      try {
        const val = (input.value || '').trim();
        document.getElementById('create_etiqueta_cls').value = val;
        document.getElementById('create_clave_cls').value = '';
        document.getElementById('create_tipo_cls').value = 'string';
        document.getElementById('create_card_cls').value = 'one';
        document.getElementById('create_unidad_cls').value = '';
        document.getElementById('create_unidades_cls').value = '';
        openModal('#modalCreateAttrCls');
        setTimeout(() => { try { document.getElementById('create_etiqueta_cls')?.focus(); } catch(_e){} }, 50);
        console.log('🔍 [ClasesEdit] Crear atributo desde búsqueda:', val);
      } catch(e){ console.log('❌ [ClasesEdit] Error preparar crear atributo:', e && e.message); }
      dropdown.style.display='none';
    }
    input.addEventListener('focus', () => filter(input.value));
    input.addEventListener('input', () => filter(input.value));
    document.addEventListener('click', (e) => { if (!dropdown.contains(e.target) && e.target !== input) dropdown.style.display = 'none'; });

    // Botón para crear atributo directamente
    const btnCreate = document.getElementById('btn_create_attr_cls');
    if (btnCreate) {
      btnCreate.addEventListener('click', () => {
        try {
          const val = (input && input.value ? input.value.trim() : '');
          document.getElementById('create_etiqueta_cls').value = val;
          document.getElementById('create_clave_cls').value = '';
          document.getElementById('create_tipo_cls').value = 'string';
          document.getElementById('create_card_cls').value = 'one';
          document.getElementById('create_unidad_cls').value = '';
          document.getElementById('create_unidades_cls').value = '';
          openModal('#modalCreateAttrCls');
          setTimeout(() => { try { document.getElementById('create_etiqueta_cls')?.focus(); } catch(_e){} }, 50);
          console.log('🔍 [ClasesEdit] Abrir crear atributo (botón)', val);
        } catch(e) { console.log('❌ [ClasesEdit] Error abrir crear atributo (botón):', e && e.message); }
      });
    }

    // Editar chip existente
    document.querySelectorAll('.js-edit-attr-cls').forEach(btn => {
      btn.addEventListener('click', () => {
        const defId = btn.getAttribute('data-attr-id');
        const label = btn.getAttribute('data-label');
        const tipo = btn.getAttribute('data-tipo');
        const unitsJson = btn.getAttribute('data-units');
        const unitDef = btn.getAttribute('data-unidad_def') || '';
        const vals = JSON.parse(btn.getAttribute('data-values') || '[]');
        document.getElementById('edit_def_id_cls').value = defId;
        document.getElementById('editAttrClsInfo').textContent = label;
        const inputEl = document.getElementById('edit_valor_cls');
        const unitSel = document.getElementById('edit_unidad_cls');
        const unitGroup = document.getElementById('edit_unidad_cls_group');
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
          console.log('🔍 [ClasesEdit] Unidad visible (aplica)');
        } else {
          if (unitGroup) unitGroup.style.display = 'none';
          console.log('🔍 [ClasesEdit] Unidad oculta (no aplica)');
        }
        openModal('#modalEditAttrCls');
      });
    });
  })();

  // Logs de envío de formularios
  document.getElementById('formEditAttrCls')?.addEventListener('submit', () => console.log('📡 [ClasesEdit] Enviando update_attr...'));
  document.getElementById('formAddAttrCls')?.addEventListener('submit', () => console.log('📡 [ClasesEdit] Enviando add_attr...'));
  // Delete attribute via hidden form
  document.querySelectorAll('.js-delete-attr-cls').forEach(btn => {
    btn.addEventListener('click', () => {
      const defId = btn.getAttribute('data-def-id');
      if (!defId) return;
      if (!confirm('¿Eliminar este atributo de la clase?')) return;
      const hid = document.getElementById('delete_def_id_cls');
      const form = document.getElementById('formDeleteAttrCls');
      if (hid && form) {
        hid.value = defId;
        console.log('📡 [ClasesEdit] Enviando delete_attr...', defId);
        form.submit();
      }
    });
  });
</script>
<?php endif; ?>

<?php include '../footer.php'; ?>
