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

    if ($slug === '' && $nombre !== '') {
      $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
      $slug = trim($slug, '-');
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
    <small class="hint">URL amigable. Ejemplo: radio-de-cristal</small>
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
    <label><input type="checkbox" name="activo" <?= ((int)$clase['activo']) ? 'checked' : '' ?> /> Activo</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="destacado" <?= ((int)$clase['destacado']) ? 'checked' : '' ?> /> Destacado</label>
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
  <!-- Taxonomías -->
  <div class="form-section">
    <h2>Taxonomías</h2>
    <h3>Áreas</h3>
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
    
  <div class="form-group">
    <label for="tags">Tags (separados por coma)</label>
    <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(implode(', ', $existing_tags), ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  
  <!-- Kits de Materiales (Autocomplete con Chips) -->
  <div class="form-group">
    <label for="kit_search">Kits de Materiales</label>
    <div class="kit-selector-container">
      <div class="selected-kits" id="selected-kits">
        <?php 
        // Renderizar kits ya seleccionados
        foreach ($existing_kit_ids as $kit_id) {
          foreach ($all_kits as $kit) {
            if ($kit['id'] == $kit_id) {
              echo '<div class="kit-chip" data-kit-id="' . $kit['id'] . '">';
              echo '<span>' . htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') . '</span>';
              echo '<button type="button" class="remove-kit" onclick="removeKit(this)">×</button>';
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
  <style>
    .seo-preview { background:#f7f7f7; padding:8px; border-radius:6px; }
    #seo-manual { display:none; }
    /* Ocultar banners/avisos de CKEditor sobre versiones inseguras (como en article-edit) */
    .cke .cke_warning,
    .cke .cke_panel_warning,
    .cke .cke_browser_warning,
    .cke .cke_upgrade_notice,
    .cke_warning,
    .cke_panel_warning { display: none !important; }
    .cke_notification.cke_notification_warning,
    .cke_notification.cke_notification_warning .cke_notification_message,
    .cke_notification.cke_notification_warning .cke_notification_close {
      display: none !important;
      visibility: hidden !important;
      height: 0 !important;
      width: 0 !important;
      overflow: hidden !important;
    }
    /* Reference layout styles (compact) */
    .article-form { background:#fff; padding:1rem; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,0.06); width:100%; max-width:100%; box-sizing:border-box; }
    .form-section { margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid #e9ecef; }
    .form-section:last-of-type { border-bottom:none; }
    .form-section h2 { margin-bottom:0.5rem; font-size:1.05rem; color:#111; }
    .form-row { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:0.5rem; }
    .form-group { margin-bottom:0.6rem; }
    .form-group label { display:block; margin-bottom:0.25rem; font-weight:600; color:#374151; font-size:0.95rem; }
    
    /* Kits Autocomplete Styles */
    .kit-selector-container {
      position: relative;
      width: 100%;
      max-width: 600px;
    }
    .selected-kits {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-bottom: 8px;
      min-height: 32px;
    }
    .kit-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 8px 4px 12px;
      background: linear-gradient(135deg, #1f3c88 0%, #2e7d32 100%);
      color: white;
      border-radius: 16px;
      font-size: 0.85rem;
      font-weight: 500;
      box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .kit-chip .remove-kit {
      background: rgba(255,255,255,0.3);
      border: none;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: white;
      font-size: 14px;
      line-height: 1;
      padding: 0;
      transition: background 0.2s;
    }
    .kit-chip .remove-kit:hover {
      background: rgba(255,255,255,0.5);
    }
    #kit_search {
      width: 100%;
      padding: 8px 12px;
      border: 2px solid #d1d5db;
      border-radius: 6px;
      font-size: 0.92rem;
      transition: border-color 0.2s;
    }
    #kit_search:focus {
      outline: none;
      border-color: #1f3c88;
      box-shadow: 0 0 0 3px rgba(31,60,136,0.1);
    }
    .autocomplete-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      margin-top: 4px;
      max-height: 250px;
      overflow-y: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 1000;
      display: none;
    }
    .autocomplete-item {
      padding: 10px 12px;
      cursor: pointer;
      transition: background 0.15s;
      border-bottom: 1px solid #f3f4f6;
    }
    .autocomplete-item:last-child {
      border-bottom: none;
    }
    .autocomplete-item:hover {
      background: #f9fafb;
    }
    .autocomplete-item strong {
      display: block;
      color: #111;
      margin-bottom: 2px;
    }
    .autocomplete-item .kit-code {
      color: #6b7280;
      font-size: 0.82rem;
    }
    .autocomplete-no-results {
      padding: 12px;
      text-align: center;
      color: #6b7280;
      font-size: 0.9rem;
    }
    
    .form-group label { display:block; margin-bottom:0.25rem; font-weight:600; color:#374151; font-size:0.95rem; }
    .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="url"], .form-group input[type="datetime-local"], .form-group select, .form-group textarea { width:100%; padding:0.4rem; border:1px solid #d1d5db; border-radius:4px; font-family:inherit; font-size:0.92rem; }
    .form-group small { display:block; margin-top:0.25rem; color:#6b7280; font-size:0.82rem; }
    .checkbox-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:0.4rem; }
    .checkbox-label { display:flex; align-items:center; gap:0.5rem; padding:0.5rem; background:#f9fafb; border-radius:4px; cursor:pointer; transition:background 0.2s; }
    .checkbox-label:hover { background:#f3f4f6; }
    .form-actions { display:flex; gap:0.5rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #eef2f5; }
  </style>
  <div id="seo-manual">
    <!-- Los campos SEO arriba funcionan como override cuando este panel está activo -->
  </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn">Guardar</button>
    <a href="/admin/clases/index.php" class="btn btn-secondary">Cancelar</a>
    <?php if ($is_edit): ?>
      <a href="/proyecto.php?slug=<?= htmlspecialchars($clase['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn">Ver público</a>
    <?php endif; ?>
  </div>
</form>

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
  nombreInput.addEventListener('blur', () => {
    console.log('🔍 [ClasesEdit] blur nombre');
    if (!slugInput.value && nombreInput.value) {
      const s = nombreInput.value.toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '');
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
      const s = nombreVal.toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '');
      slugInput.value = s;
      console.log('⚡ [ClasesEdit] slug generado con botón:', s);
      computeSeo();
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
      <button type="button" class="remove-kit" onclick="removeKit(this)">×</button>
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

<?php include '../footer.php'; ?>
