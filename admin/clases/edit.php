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
  'ciclo' => '',
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
  'canonical_url' => '',
  'activo' => 1,
  'destacado' => 0,
  'orden_popularidad' => 0,
  'status' => 'draft',
  'published_at' => null,
  'autor' => '',
  'contenido_html' => '',
  'seccion_id' => null
];

if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT id, nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, imagen_portada, video_portada, seguridad, seo_title, seo_description, canonical_url, activo, destacado, orden_popularidad, status, published_at, autor, contenido_html, seccion_id FROM clases WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { $clase = $row; } else { $is_edit = false; $id = null; }
  } catch (PDOException $e) {}
}

// Cargar listas para relaciones
$areas = [];
$competencias = [];
$secciones = [];
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
  $secciones = $pdo->query('SELECT id, nombre, slug FROM secciones ORDER BY nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
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
    $ciclo = isset($_POST['ciclo']) ? trim($_POST['ciclo']) : '';
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
    $canonical_url = isset($_POST['canonical_url']) ? trim($_POST['canonical_url']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $orden_popularidad = isset($_POST['orden_popularidad']) ? (int)$_POST['orden_popularidad'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
    $published_at_input = isset($_POST['published_at']) ? trim($_POST['published_at']) : '';
    $published_at = $published_at_input !== '' ? date('Y-m-d H:i:s', strtotime($published_at_input)) : null;
    if ($status === 'published' && !$published_at) { $published_at = date('Y-m-d H:i:s'); }
    $autor = isset($_POST['autor']) ? trim($_POST['autor']) : '';
    $contenido_html = isset($_POST['contenido_html']) ? $_POST['contenido_html'] : '';
    $seccion_id = isset($_POST['seccion_id']) && ctype_digit($_POST['seccion_id']) ? (int)$_POST['seccion_id'] : null;
    $areas_sel = isset($_POST['areas']) && is_array($_POST['areas']) ? array_map('intval', $_POST['areas']) : [];
    $comp_sel = isset($_POST['competencias']) && is_array($_POST['competencias']) ? array_map('intval', $_POST['competencias']) : [];
    $tags_input = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    $tags_list = array_values(array_filter(array_map(function($t){ return trim($t); }, explode(',', $tags_input))));

    if ($slug === '' && $nombre !== '') {
      $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
      $slug = trim($slug, '-');
    }

    if ($nombre === '' || $slug === '' || !in_array($ciclo, ['1','2','3'], true)) {
      $error_msg = 'Completa nombre, ciclo y slug válidos.';
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
          if ($canonical_url === '') { $canonical_url = '/proyecto.php?slug=' . $slug; }
          echo '<script>console.log("🔍 [SEO] auto title:", ' . json_encode($seo_title) . ', "auto desc:", ' . json_encode($seo_description) . ', "auto canon:", ' . json_encode($canonical_url) . ');</script>';
          // Transacción para clase + relaciones
          $pdo->beginTransaction();
          if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE clases SET nombre=?, slug=?, ciclo=?, grados=?, dificultad=?, duracion_minutos=?, resumen=?, objetivo_aprendizaje=?, imagen_portada=?, video_portada=?, seguridad=?, seo_title=?, seo_description=?, canonical_url=?, activo=?, destacado=?, orden_popularidad=?, status=?, published_at=?, autor=?, contenido_html=?, seccion_id=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$nombre, $slug, $ciclo, $grados_json, $dificultad ?: null, $duracion_minutos, $resumen, $objetivo, $imagen_portada ?: null, $video_portada ?: null, $seguridad_json, $seo_title, $seo_description, $canonical_url, $activo, $destacado, $orden_popularidad, $status, $published_at, $autor ?: null, $contenido_html, $seccion_id, $id]);
            // Limpiar relaciones
            $pdo->prepare('DELETE FROM clase_areas WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_competencias WHERE clase_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clase_tags WHERE clase_id = ?')->execute([$id]);
          } else {
            $stmt = $pdo->prepare('INSERT INTO clases (nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, imagen_portada, video_portada, seguridad, seo_title, seo_description, canonical_url, activo, destacado, orden_popularidad, status, published_at, autor, contenido_html, seccion_id, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
            $stmt->execute([$nombre, $slug, $ciclo, $grados_json, $dificultad ?: null, $duracion_minutos, $resumen, $objetivo, $imagen_portada ?: null, $video_portada ?: null, $seguridad_json, $seo_title, $seo_description, $canonical_url, $activo, $destacado, $orden_popularidad, $status, $published_at, $autor ?: null, $contenido_html, $seccion_id]);
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
    <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($clase['slug'], ENT_QUOTES, 'UTF-8') ?>" placeholder="auto si se deja vacío" />
  </div>
  <div class="form-group">
    <label for="ciclo">Ciclo</label>
    <select id="ciclo" name="ciclo" required>
      <option value="">Selecciona</option>
      <option value="1" <?= $clase['ciclo']==='1'?'selected':'' ?>>1 (6°-7°)</option>
      <option value="2" <?= $clase['ciclo']==='2'?'selected':'' ?>>2 (8°-9°)</option>
      <option value="3" <?= $clase['ciclo']==='3'?'selected':'' ?>>3 (10°-11°)</option>
    </select>
  </div>
  <div class="form-group">
    <label>Grados</label>
    <div class="checkbox-grid">
      <?php foreach ([6,7,8,9,10,11] as $g): $has = false; $gj = $clase['grados'] ?: '[]'; $arr = json_decode($gj, true); $has = is_array($arr) && in_array($g, $arr); ?>
        <label><input type="checkbox" name="grados[]" value="<?= $g ?>" <?= $has ? 'checked' : '' ?>> <?= $g ?>°</label>
      <?php endforeach; ?>
    </div>
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
    <div class="form-group">
      <label for="seccion_id">Sección</label>
      <select id="seccion_id" name="seccion_id">
        <option value="">(ninguna)</option>
        <?php foreach ($secciones as $sec): ?>
          <option value="<?= (int)$sec['id'] ?>" <?= ((int)($clase['seccion_id'] ?? 0) === (int)$sec['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sec['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
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
  <!-- Seguridad -->
  <?php $seg = $clase['seguridad'] ? json_decode($clase['seguridad'], true) : null; ?>
  <div class="form-row">
    <div class="form-group">
      <label for="seg_edad_min">Edad mínima</label>
      <input type="number" id="seg_edad_min" name="seg_edad_min" value="<?= htmlspecialchars((string)($seg['edad_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label for="seg_edad_max">Edad máxima</label>
      <input type="number" id="seg_edad_max" name="seg_edad_max" value="<?= htmlspecialchars((string)($seg['edad_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="seg_notas">Notas de seguridad</label>
    <textarea id="seg_notas" name="seg_notas" rows="3"><?= htmlspecialchars($seg['notas'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <!-- SEO -->
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
  <div class="form-group">
    <label for="canonical_url">Canonical URL</label>
    <input type="text" id="canonical_url" name="canonical_url" value="<?= htmlspecialchars($clase['canonical_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <!-- SEO Auto Preview + Override Toggle -->
  <div class="form-section">
    <label><input type="checkbox" id="seo_override_toggle"> Editar SEO manualmente</label>
    <div class="seo-preview">
      <p><strong>Preview Title:</strong> <span id="seo_preview_title"></span></p>
      <p><strong>Preview Description:</strong> <span id="seo_preview_desc"></span></p>
      <p><strong>Preview Canonical:</strong> <span id="seo_preview_canon"></span></p>
      <small class="help-text">Si no defines SEO manualmente, se usarán estos valores.</small>
    </div>
  </div>
  <style>
    .seo-preview { background:#f7f7f7; padding:8px; border-radius:6px; }
    #seo-manual { display:none; }
  </style>
  <div id="seo-manual">
    <!-- Los campos SEO arriba funcionan como override cuando este panel está activo -->
  </div>
  <!-- Estado/Publicación -->
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
  <!-- Contenido HTML -->
  <div class="form-group">
    <label for="contenido_html">Contenido (HTML)</label>
    <textarea id="contenido_html" name="contenido_html" rows="12"><?= htmlspecialchars($clase['contenido_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="help-text">Puedes editar como HTML; se validará en el frontend.</small>
  </div>
  <!-- Áreas y Competencias -->
  <div class="form-section">
    <h3>Áreas</h3>
    <div class="checkbox-grid">
      <?php foreach ($areas as $a): ?>
        <label class="checkbox-label"><input type="checkbox" name="areas[]" value="<?= (int)$a['id'] ?>" <?= in_array($a['id'], $existing_area_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="form-section">
    <h3>Competencias MEN</h3>
    <div class="checkbox-grid">
      <?php foreach ($competencias as $c): ?>
        <label class="checkbox-label"><input type="checkbox" name="competencias[]" value="<?= (int)$c['id'] ?>" <?= in_array($c['id'], $existing_comp_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8') ?>)</label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="form-group">
    <label for="tags">Tags (separados por coma)</label>
    <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(implode(', ', $existing_tags), ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="actions" style="margin-top:1rem;">
    <button type="submit" class="btn">Guardar</button>
    <a href="/admin/clases/index.php" class="btn btn-secondary">Cancelar</a>
    <?php if ($is_edit): ?>
      <a href="/proyecto.php?slug=<?= htmlspecialchars($clase['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn">Ver público</a>
    <?php endif; ?>
  </div>
</form>

<script>
  const nombreInput = document.getElementById('nombre');
  const slugInput = document.getElementById('slug');
  const resumenInput = document.getElementById('resumen');
  const seoTitleInput = document.getElementById('seo_title');
  const seoDescInput = document.getElementById('seo_description');
  const canonInput = document.getElementById('canonical_url');
  const seoPrevTitle = document.getElementById('seo_preview_title');
  const seoPrevDesc = document.getElementById('seo_preview_desc');
  const seoPrevCanon = document.getElementById('seo_preview_canon');
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
        if (window.tinymce) { descSrc = textFromHtml(tinymce.get('contenido_html')?.getContent() || ''); }
        else { const ta = document.getElementById('contenido_html'); descSrc = textFromHtml(ta ? ta.value : ''); }
      } catch(e) { descSrc = ''; }
    }
    const autoDesc = shortenAtWord(descSrc, 160);
    const slugVal = (slugInput && slugInput.value.trim()) ? slugInput.value.trim() : '';
    const autoCanon = '/proyecto.php?slug=' + slugVal;
    // Render preview
    if (seoPrevTitle) seoPrevTitle.textContent = autoTitle;
    if (seoPrevDesc) seoPrevDesc.textContent = autoDesc;
    if (seoPrevCanon) seoPrevCanon.textContent = autoCanon;
    // If manual not enabled and inputs are empty, mirror preview into inputs (for visibility but still overrideable)
    if (!seoToggle?.checked) {
      if (seoTitleInput && !seoTitleInput.value) seoTitleInput.value = autoTitle;
      if (seoDescInput && !seoDescInput.value) seoDescInput.value = autoDesc;
      if (canonInput && !canonInput.value) canonInput.value = autoCanon;
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
  if (slugInput) slugInput.addEventListener('input', computeSeo);
  if (resumenInput) resumenInput.addEventListener('input', computeSeo);
  // Validación simple de SEO
  const seoTitle = document.getElementById('seo_title');
  const seoDesc = document.getElementById('seo_description');
  if (seoTitle) seoTitle.addEventListener('input', ()=>{ if (seoTitle.value.length>160) console.log('⚠️ [ClasesEdit] SEO title >160'); });
  if (seoDesc) seoDesc.addEventListener('input', ()=>{ if (seoDesc.value.length>255) console.log('⚠️ [ClasesEdit] SEO description >255'); });

  // Integración TinyMCE estilo article-edit (content)
  (function initTiny() {
    const src = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js';
    const s = document.createElement('script');
    s.src = src; s.referrerPolicy = 'origin';
    s.onload = () => {
      console.log('✅ [ClasesEdit] TinyMCE cargado');
      try {
        tinymce.init({
          selector: '#contenido_html',
          height: 500,
          menubar: true,
          plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
          toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | table | code preview fullscreen',
          content_style: 'body { font-family:Inter, Arial, sans-serif; font-size:14px; }',
          branding: false,
          convert_urls: false,
          image_caption: true,
          placeholder: 'Escribe el contenido de la guía aquí...',
          setup: (editor) => {
            editor.on('change keyup', () => computeSeo());
          }
        });
      } catch (e) {
        console.log('❌ [ClasesEdit] Error iniciando TinyMCE:', e.message);
      }
    };
    s.onerror = () => console.log('⚠️ [ClasesEdit] No se pudo cargar TinyMCE, usando textarea simple');
    document.head.appendChild(s);
  })();

  // Asegurar sincronización del editor antes de enviar
  const formEl = document.querySelector('form');
  if (formEl) {
    formEl.addEventListener('submit', function() {
      try { if (window.tinymce) { tinymce.triggerSave(); console.log('✅ [ClasesEdit] TinyMCE contenido sincronizado'); } }
      catch(e) { console.log('⚠️ [ClasesEdit] No se pudo sincronizar TinyMCE:', e.message); }
      computeSeo();
    });
  }
  // Inicializa preview al cargar
  computeSeo();
</script>
<?php include '../footer.php'; ?>
