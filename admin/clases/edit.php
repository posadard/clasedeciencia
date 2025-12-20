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
}

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
          // Autogenerar SEO si vienen vacíos
          if ($seo_title === '') { $seo_title = $nombre; }
          $desc_source = $resumen !== '' ? $resumen : strip_tags($contenido_html);
          $desc_source = preg_replace('/\s+/', ' ', $desc_source);
          if ($seo_description === '') {
            $seo_description = (strlen($desc_source) > 160)
              ? preg_replace('/\s+\S*$/', '', substr($desc_source, 0, 160))
              : $desc_source;
          }
          if ($canonical_url === '') { $canonical_url = '/' . $slug; }
          echo '<script>console.log("🔍 [SEO] auto title:", ' . json_encode($seo_title) . ', "auto desc:", ' . json_encode($seo_description) . ', "auto canon:", ' . json_encode($canonical_url) . ');</script>';
          // Transacción para clase + relaciones
          $pdo->beginTransaction();
          if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE clases SET nombre=?, slug=?, ciclo=?, grados=?, dificultad=?, duracion_minutos=?, resumen=?, objetivo_aprendizaje=?, imagen_portada=?, video_portada=?, seguridad=?, seo_title=?, seo_description=?, activo=?, destacado=?, orden_popularidad=?, status=?, published_at=?, autor=?, contenido_html=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$nombre, $slug, $ciclo, $grados_json, $dificultad ?: null, $duracion_minutos, $resumen, $objetivo, $imagen_portada ?: null, $video_portada ?: null, $seguridad_json, $seo_title, $seo_description, $activo, $destacado, $orden_popularidad, $status, $published_at, $autor ?: null, $contenido_html, $id]);
            // Limpiar relaciones
            $pdo->prepare('DELETE FROM clase_areas WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_competencias WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_tags WHERE clase_id = ?')->execute([$id]);
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
    <textarea id="resumen" name="resumen" rows="3" placeholder="Descripción corta..."><?= htmlspecialchars($clase['resumen'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="objetivo_aprendizaje">Objetivo de aprendizaje</label>
    <textarea id="objetivo_aprendizaje" name="objetivo_aprendizaje" rows="4" placeholder="Competencias MEN y objetivos..."><?= htmlspecialchars($clase['objetivo_aprendizaje'], ENT_QUOTES, 'UTF-8') ?></textarea>
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
    <textarea id="seg_notas" name="seg_notas" rows="3"><?= htmlspecialchars($seg['notas'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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
    
    <h3 style="margin-top:1.5rem">Competencias MEN</h3>
    <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">Selecciona las competencias desarrolladas en esta clase. Pasa el cursor sobre cada competencia para ver su descripción.</p>
    
    <?php 
    // Agrupar competencias por subcategoría
    $competencias_agrupadas = [];
    foreach ($competencias as $c) {
        $subcat = $c['subcategoria'] ?? 'Otras';
        if (!isset($competencias_agrupadas[$subcat])) {
            $competencias_agrupadas[$subcat] = [];
        }
        $competencias_agrupadas[$subcat][] = $c;
    }
    ?>
    
    <?php foreach ($competencias_agrupadas as $subcategoria => $comps): ?>
      <div class="competencia-group">
        <h4 class="competencia-group-title"><?= htmlspecialchars($subcategoria, ENT_QUOTES, 'UTF-8') ?></h4>
        <div class="competencia-chips">
          <?php foreach ($comps as $c): ?>
            <label class="competencia-chip" title="<?= htmlspecialchars($c['explicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <input type="checkbox" name="competencias[]" value="<?= (int)$c['id'] ?>" <?= in_array($c['id'], $existing_comp_ids) ? 'checked' : '' ?>>
              <span class="chip-content">
                <span class="chip-codigo"><?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="chip-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    
  <div class="form-group" style="margin-top: 1.5rem;">
    <label for="tags">Tags (separados por coma)</label>
    <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(implode(', ', $existing_tags), ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  </div>
  <!-- SEO -->
  <div class="form-section">
    <h2>SEO</h2>
  <div class="form-row">
    <div class="form-group">
      <label for="seo_title">SEO Title (≤160)</label>
      <input type="text" id="seo_title" name="seo_title" maxlength="160" value="<?= htmlspecialchars($clase['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label for="seo_description">SEO Description (≤255)</label>
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
    .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="url"], .form-group input[type="datetime-local"], .form-group select, .form-group textarea { width:100%; padding:0.4rem; border:1px solid #d1d5db; border-radius:4px; font-family:inherit; font-size:0.92rem; }
    .form-group small { display:block; margin-top:0.25rem; color:#6b7280; font-size:0.82rem; }
    .checkbox-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:0.4rem; }
    
    /* Competencias agrupadas por subcategoría */
    .competencia-group { margin-bottom: 1.5rem; }
    .competencia-group-title { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; padding-bottom: 0.25rem; border-bottom: 2px solid #e5e7eb; }
    .competencia-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .competencia-chip { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-size: 0.875rem; position: relative; }
    .competencia-chip:hover { background: #e5e7eb; border-color: #9ca3af; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .competencia-chip input[type="checkbox"] { width: auto; margin: 0; }
    .competencia-chip input[type="checkbox"]:checked + .chip-content { font-weight: 600; }
    .competencia-chip:has(input:checked) { background: #dbeafe; border-color: #3b82f6; }
    .chip-content { display: flex; flex-direction: column; gap: 0.125rem; }
    .chip-codigo { font-size: 0.75rem; color: #6b7280; font-weight: 500; }
    .chip-nombre { color: #111827; line-height: 1.3; }
    .competencia-chip:has(input:checked) .chip-codigo { color: #2563eb; }
    .competencia-chip:has(input:checked) .chip-nombre { color: #1e40af; }
    /* Tooltip nativo mejorado */
    .competencia-chip[title]:hover::after { content: attr(title); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 0.5rem; padding: 0.75rem; background: #1f2937; color: white; font-size: 0.75rem; line-height: 1.4; border-radius: 6px; white-space: normal; max-width: 300px; width: max-content; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.2); pointer-events: none; }
    .competencia-chip[title]:hover::before { content: ''; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: -0.25rem; border: 6px solid transparent; border-top-color: #1f2937; z-index: 1000; }
    
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
  function computeSeo() {
    const autoTitle = (nombreInput && nombreInput.value) ? nombreInput.value.trim() : '';
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
    const autoDesc = shortenAtWord(descSrc, 160);
    // Render preview
    if (seoPrevTitle) seoPrevTitle.textContent = autoTitle;
    if (seoPrevDesc) seoPrevDesc.textContent = autoDesc;
    // If manual not enabled and inputs are empty, mirror preview into inputs (for visibility but still overrideable)
    if (!seoToggle?.checked) {
      if (seoTitleInput && !seoTitleInput.value) seoTitleInput.value = autoTitle;
      if (seoDescInput && !seoDescInput.value) seoDescInput.value = autoDesc;
      console.log('🔍 [SEO] autogenerados (preview)');
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
  
  console.log('ℹ️ [ClasesEdit] Campo Sección ocultado (no requerido)');
  if (slugInput) slugInput.addEventListener('input', computeSeo);
  if (resumenInput) resumenInput.addEventListener('input', computeSeo);
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
</script>
<?php include '../footer.php'; ?>
