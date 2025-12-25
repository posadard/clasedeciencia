<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../../config.php';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$manual_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$kit_id = isset($_GET['kit_id']) ? intval($_GET['kit_id']) : 0;
$error_msg = '';
$success_msg = '';

$manual = null;
if ($manual_id > 0) {
  $stmt = $pdo->prepare('SELECT * FROM kit_manuals WHERE id = ? LIMIT 1');
  $stmt->execute([$manual_id]);
  $manual = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($manual) $kit_id = intval($manual['kit_id']);
}

$kit = null;
if ($kit_id > 0) {
  $stmtK = $pdo->prepare('SELECT id, nombre, codigo, slug, seguridad FROM kits WHERE id = ? LIMIT 1');
  $stmtK->execute([$kit_id]);
  $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
}

// Detect optional column 'render_mode' in kit_manuals
$has_render_mode_column = false;
$has_tipo_manual_column = false;
$has_ambito_column = false;
$has_item_id_column = false;
$has_resumen_column = false;
try {
  $pdo->query('SELECT render_mode FROM kit_manuals LIMIT 1');
  $has_render_mode_column = true;
  echo '<script>console.log("🔍 [ManualsEdit] Column render_mode: presente");</script>';
} catch (PDOException $e) {
  echo '<script>console.log("⚠️ [ManualsEdit] Column render_mode: ausente");</script>';
}
try { $pdo->query('SELECT tipo_manual FROM kit_manuals LIMIT 1'); $has_tipo_manual_column = true; echo '<script>console.log("🔍 [ManualsEdit] Column tipo_manual: presente");</script>'; } catch(PDOException $e){ echo '<script>console.log("⚠️ [ManualsEdit] Column tipo_manual: ausente");</script>'; }
try { $pdo->query('SELECT ambito FROM kit_manuals LIMIT 1'); $has_ambito_column = true; echo '<script>console.log("🔍 [ManualsEdit] Column ambito: presente");</script>'; } catch(PDOException $e){ echo '<script>console.log("⚠️ [ManualsEdit] Column ambito: ausente");</script>'; }
try { $pdo->query('SELECT item_id FROM kit_manuals LIMIT 1'); $has_item_id_column = true; echo '<script>console.log("🔍 [ManualsEdit] Column item_id: presente");</script>'; } catch(PDOException $e){ echo '<script>console.log("⚠️ [ManualsEdit] Column item_id: ausente");</script>'; }
try { $pdo->query('SELECT resumen FROM kit_manuals LIMIT 1'); $has_resumen_column = true; echo '<script>console.log("🔍 [ManualsEdit] Column resumen: presente");</script>'; } catch(PDOException $e){ echo '<script>console.log("⚠️ [ManualsEdit] Column resumen: ausente");</script>'; }

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [ManualsEdit] CSRF inválido");</script>';
  } else {
    $kit_id = intval($_POST['kit_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $version = trim($_POST['version'] ?? '1.0');
    $status_raw = trim($_POST['status'] ?? 'draft');
    $allowed_statuses = ['draft','approved','published','discontinued'];
    $status = in_array($status_raw, $allowed_statuses, true) ? $status_raw : 'draft';
    $idioma = trim($_POST['idioma'] ?? 'es-CO');
    $time_minutes = ($_POST['time_minutes'] !== '' ? intval($_POST['time_minutes']) : null);
    $dificultad = trim($_POST['dificultad_ensamble'] ?? '');
    // Manual type and scope
    $allowed_tipos = ['seguridad','armado','calibracion','uso','mantenimiento','teoria','experimento','solucion','evaluacion','docente','referencia'];
    $tipo_manual = trim($_POST['tipo_manual'] ?? 'armado');
    if (!in_array($tipo_manual, $allowed_tipos, true)) { $tipo_manual = 'armado'; }
    $ambito = trim($_POST['ambito'] ?? 'kit');
    $ambito = ($ambito === 'componente') ? 'componente' : 'kit';
    $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== '' ? intval($_POST['item_id']) : null;
    // Exclusividad: si es componente → kit_id NULL; si es kit → item_id NULL
    if ($ambito === 'componente') { $kit_id = 0; }
    else { $item_id = null; }
    $resumen = isset($_POST['resumen']) ? trim((string)$_POST['resumen']) : '';
    if ($resumen !== '') { $resumen = mb_substr($resumen, 0, 255, 'UTF-8'); }
    $pasos_json = trim($_POST['pasos_json'] ?? '');
    $herr_json = trim($_POST['herramientas_json'] ?? '');
    $seg_json = trim($_POST['seguridad_json'] ?? '');
    $html = $_POST['html'] ?? null;
    $ui_mode = ($_POST['ui_mode'] ?? '') === 'fullhtml' ? 'fullhtml' : 'legacy';
    $render_mode_post = ($_POST['render_mode'] ?? '') === 'fullhtml' ? 'fullhtml' : 'legacy';

    // Basic validations
    if ($kit_id <= 0) {
      $error_msg = 'Kit requerido.';
    } elseif ($slug === '') {
      $error_msg = 'Slug requerido.';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
      $error_msg = 'Slug inválido: usa a-z, 0-9 y guiones.';
    }

    // Deterministic slug build: manual-{tipo}-{entidad}-{dd-mm-yy}-V{version}
    if (!$error_msg) {
      // Normalize version: keep digits and dots; convert dots to underscores and prefix with 'V'
      $ver_clean = strtolower(preg_replace('/[^0-9\.]+/', '', (string)$version));
      $ver_norm_underscore = $ver_clean !== '' ? str_replace('.', '_', $ver_clean) : '';
      $ver_part = $ver_norm_underscore !== '' ? ('V' . $ver_norm_underscore) : '';
      // Date dd-mm-yy from published_at if present, else now
      $date_src = null;
      if ($manual_id > 0 && $manual && !empty($manual['published_at'])) { $date_src = $manual['published_at']; }
      if (!$date_src && $status === 'published' && !empty($published_at)) { $date_src = $published_at; }
      if (!$date_src) { $date_src = date('Y-m-d H:i:s'); }
      $date_part = date('d-m-y', strtotime($date_src));
      // Entity slug
      $entity_slug = '';
      if ($ambito === 'componente' && $item_id) {
        try {
          $qs = $pdo->prepare('SELECT slug, nombre_comun FROM kit_items WHERE id = ? LIMIT 1');
          $qs->execute([$item_id]);
          $row = $qs->fetch(PDO::FETCH_ASSOC);
          $entity_slug = (string)($row['slug'] ?? '');
          if ($entity_slug === '' && !empty($row['nombre_comun'])) {
            $tmp = strtolower(preg_replace('/[^a-z0-9\-]+/', '-', (string)$row['nombre_comun']));
            $entity_slug = preg_replace('/-+/', '-', trim($tmp, '-'));
          }
        } catch (PDOException $e) { $entity_slug = ''; }
      } else if ($kit && !empty($kit['slug'])) {
        $entity_slug = (string)$kit['slug'];
      }
      // Build parts (order): manual-{tipo}-{entidad}-{dd-mm-yy}-{Vversion}
      $parts = ['manual', $tipo_manual];
      if ($entity_slug !== '') { $parts[] = $entity_slug; }
      $parts[] = $date_part;
      if ($ver_part !== '') { $parts[] = $ver_part; }
      $built = implode('-', array_filter($parts));
      // Sanitize
      $built = strtolower($built);
      $built = preg_replace('/[^a-z0-9\-]+/', '-', $built);
      $built = preg_replace('/-+/', '-', $built);
      $built = trim($built, '-');
      // Ensure single manual- prefix
      $built = preg_replace('/^(?:manual-)+/', 'manual-', $built);
      if ($built === 'manual') { $built = 'manual-'; }
      $slug = $built;
      echo '<script>console.log("🔍 [ManualsEdit] Slug ensamblado:", ' . json_encode($slug) . ');</script>';
    }

    // Verificar unicidad por entidad (kit o componente) + idioma + slug
    if (!$error_msg && $slug !== '') {
      try {
        if ($ambito === 'componente' && $item_id) {
          if ($manual_id > 0) {
            $chk = $pdo->prepare('SELECT COUNT(*) FROM kit_manuals WHERE item_id = ? AND idioma = ? AND slug = ? AND id <> ?');
            $chk->execute([$item_id, $idioma, $slug, $manual_id]);
          } else {
            $chk = $pdo->prepare('SELECT COUNT(*) FROM kit_manuals WHERE item_id = ? AND idioma = ? AND slug = ?');
            $chk->execute([$item_id, $idioma, $slug]);
          }
          $exists = (int)$chk->fetchColumn();
          if ($exists > 0) {
            $error_msg = 'El slug ya existe para este componente e idioma. Elige otro.';
            echo '<script>console.log("⚠️ [ManualsEdit] Slug duplicado para item_id=' . (int)$item_id . ' idioma=' . htmlspecialchars($idioma, ENT_QUOTES, 'UTF-8') . '");</script>';
          }
        } else {
          // ámbito kit
          $kid = ($kit_id > 0 ? $kit_id : null);
          if ($kid !== null) {
            if ($manual_id > 0) {
              $chk = $pdo->prepare('SELECT COUNT(*) FROM kit_manuals WHERE kit_id = ? AND idioma = ? AND slug = ? AND id <> ?');
              $chk->execute([$kid, $idioma, $slug, $manual_id]);
            } else {
              $chk = $pdo->prepare('SELECT COUNT(*) FROM kit_manuals WHERE kit_id = ? AND idioma = ? AND slug = ?');
              $chk->execute([$kid, $idioma, $slug]);
            }
            $exists = (int)$chk->fetchColumn();
            if ($exists > 0) {
              $error_msg = 'El slug ya existe para este kit e idioma. Elige otro.';
              echo '<script>console.log("⚠️ [ManualsEdit] Slug duplicado para kit_id=' . (int)$kid . ' idioma=' . htmlspecialchars($idioma, ENT_QUOTES, 'UTF-8') . '");</script>';
            }
          }
        }
      } catch (PDOException $e) {
        echo '<script>console.log("⚠️ [ManualsEdit] Error verificando unicidad:", ' . json_encode($e->getMessage()) . ');</script>';
      }
    }

    // Validate JSON fields (optional empty allowed)
    $jsonErrors = [];
    $validateJson = function($raw, $label) use (&$jsonErrors) {
      if ($raw === '') return null; // treat empty as NULL
      $decoded = json_decode($raw, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $jsonErrors[] = $label;
        return null;
      }
      return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    };

    $pasos_json_db = $validateJson($pasos_json, 'pasos_json');
    $herr_json_db = $validateJson($herr_json, 'herramientas_json');
    $seg_json_db = $validateJson($seg_json, 'seguridad_json');

    if (!empty($jsonErrors)) {
      $error_msg = 'JSON inválido en: ' . implode(', ', $jsonErrors);
    }

    if (!$error_msg) {
      try {
        if ($manual_id > 0) {
          $was_published = ($manual && ($manual['status'] ?? '') === 'published');
          $becomes_published = ($status === 'published');
          // Build dynamic UPDATE with available columns
          $setParts = [
            'slug = ?', 'version = ?', 'status = ?', 'idioma = ?', 'time_minutes = ?', 'dificultad_ensamble = ?',
            'pasos_json = ?', 'herramientas_json = ?', 'seguridad_json = ?', 'html = ?'
          ];
          $params = [$slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html];
          // Persist exclusivity of entity
          $setParts[] = 'kit_id = ?'; $params[] = ($ambito === 'componente' ? null : ($kit_id > 0 ? $kit_id : null));
          if ($has_render_mode_column) { $setParts[] = 'render_mode = ?'; $params[] = $render_mode_post; }
          if ($has_tipo_manual_column) { $setParts[] = 'tipo_manual = ?'; $params[] = $tipo_manual; }
          if ($has_ambito_column) { $setParts[] = 'ambito = ?'; $params[] = $ambito; }
          if ($has_item_id_column) { $setParts[] = 'item_id = ?'; $params[] = $item_id; }
          if ($has_resumen_column) { $setParts[] = 'resumen = ?'; $params[] = ($resumen !== '' ? $resumen : null); }
          if ($becomes_published && !$was_published) { $setParts[] = 'published_at = IFNULL(published_at, NOW())'; }
          $sqlU = 'UPDATE kit_manuals SET ' . implode(', ', $setParts) . ' WHERE id = ?';
          $params[] = $manual_id;
          $stmtU = $pdo->prepare($sqlU);
          $stmtU->execute($params);
          $success_msg = 'Manual actualizado.';
          echo '<script>console.log("✅ [ManualsEdit] Actualizado ID=' . $manual_id . '");</script>';
        } else {
          $published_at_insert = ($status === 'published') ? date('Y-m-d H:i:s') : null;
          // Build dynamic INSERT
          $fields = ['kit_id','slug','version','status','idioma','time_minutes','dificultad_ensamble','pasos_json','herramientas_json','seguridad_json','html'];
          $place = array_fill(0, count($fields), '?');
          $vals = [($ambito === 'componente' ? null : ($kit_id > 0 ? $kit_id : null)), $slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html];
          if ($has_render_mode_column) { $fields[]='render_mode'; $place[]='?'; $vals[]=$render_mode_post; }
          if ($has_tipo_manual_column) { $fields[]='tipo_manual'; $place[]='?'; $vals[]=$tipo_manual; }
          if ($has_ambito_column) { $fields[]='ambito'; $place[]='?'; $vals[]=$ambito; }
          if ($has_item_id_column) { $fields[]='item_id'; $place[]='?'; $vals[]=$item_id; }
          if ($has_resumen_column) { $fields[]='resumen'; $place[]='?'; $vals[] = ($resumen !== '' ? $resumen : null); }
          $fields[]='published_at'; $place[]='?'; $vals[]=$published_at_insert; // always safe; column exists in schema
          $sqlI = 'INSERT INTO kit_manuals (' . implode(',', $fields) . ') VALUES (' . implode(',', $place) . ')';
          $stmtI = $pdo->prepare($sqlI);
          $stmtI->execute($vals);
          $manual_id = intval($pdo->lastInsertId());
          $success_msg = 'Manual creado.';
          echo '<script>console.log("✅ [ManualsEdit] Creado ID=' . $manual_id . '");</script>';
        }
      } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate') !== false) {
          $error_msg = 'Slug/Idioma duplicado para este kit.';
        } else {
          $error_msg = 'Error guardando manual: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        }
        echo '<script>console.log("❌ [ManualsEdit] Error: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '");</script>';
      }
    }
  }
  // Refresh manual after save
  if ($manual_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM kit_manuals WHERE id = ? LIMIT 1');
    $stmt->execute([$manual_id]);
    $manual = $stmt->fetch(PDO::FETCH_ASSOC);
    $kit_id = intval($manual['kit_id']);
  }
}

// Load kits for selector (always)
$kits = [];
try {
  $stmtKs = $pdo->query('SELECT id, nombre, slug FROM kits ORDER BY nombre ASC');
  $kits = $stmtKs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $kits = []; }
?>
<div class="container">
  <h1><?= $manual ? 'Editar Manual' : 'Nuevo Manual' ?></h1>
  <?php if ($kit): ?>
    <p>Kit actual: <strong><?= htmlspecialchars($kit['nombre']) ?></strong> (ID <?= (int)$kit['id'] ?>)</p>
    <p><a href="/admin/kits/manuals/index.php?kit_id=<?= (int)$kit['id'] ?>">Volver a Manuales</a></p>
  <?php endif; ?>

  <?php if ($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
  <?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />


    <div class="form-group">
      <label>Slug</label>
      <div style="display:flex; gap:8px; align-items:center;">
        <input type="text" name="slug" id="manual-slug" value="<?= htmlspecialchars($manual['slug'] ?? '') ?>" required placeholder="se genera automáticamente" style="flex:1;" readonly />
      </div>
      <small>Se actualiza automáticamente: <strong>manual-{tipo}-{entidad}-{dd-mm-yy}-V{version}</strong>. No editable.</small>
    </div>

    

    <?php
    // Load kit items for component scope selector (prefer items in this kit)
    $kit_items = [];
    if ($kit_id > 0) {
      try {
        $q = $pdo->prepare('SELECT ki.id, ki.nombre_comun, ki.slug, ki.sku FROM kit_componentes kc JOIN kit_items ki ON ki.id = kc.item_id WHERE kc.kit_id = ? ORDER BY ki.nombre_comun ASC');
        $q->execute([$kit_id]);
        $kit_items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
      } catch (PDOException $e) { $kit_items = []; }
    }
    if (empty($kit_items)) {
      try {
        $q = $pdo->query('SELECT id, nombre_comun, slug, sku FROM kit_items ORDER BY nombre_comun ASC');
        $kit_items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
      } catch (PDOException $e) { $kit_items = []; }
    }
    $amb_val = $manual['ambito'] ?? 'kit';
    $item_val = isset($manual['item_id']) ? (int)$manual['item_id'] : 0;
    $tipo_val = $manual['tipo_manual'] ?? 'armado';
    ?>

    <div class="form-row">
      <div class="form-group">
        <label>Tipo de manual</label>
        <select name="tipo_manual" <?= $has_tipo_manual_column ? '' : 'disabled' ?>>
          <?php
            $tipos = [
              'seguridad' => '🛡️ Seguridad',
              'armado' => '🛠️ Armado',
              'calibracion' => '🎛️ Calibración',
              'uso' => '▶️ Uso',
              'mantenimiento' => '🧰 Mantenimiento',
              'teoria' => '📘 Teoría',
              'experimento' => '🧪 Experimento',
              'solucion' => '🩺 Solución de problemas',
              'evaluacion' => '✅ Evaluación',
              'docente' => '👩‍🏫 Docente',
              'referencia' => '📚 Referencia'
            ];
            foreach ($tipos as $k => $label) {
              $sel = ($tipo_val === $k) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '" ' . $sel . '>' . $label . '</option>';
            }
          ?>
        </select>
        <?php if (!$has_tipo_manual_column): ?>
          <small class="help-note">Ejecuta la migración para habilitar el tipo.</small>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Ámbito</label>
        <select name="ambito" <?= $has_ambito_column ? '' : 'disabled' ?>>
          <option value="kit" <?= ($amb_val === 'kit') ? 'selected' : '' ?>>Kit</option>
          <option value="componente" <?= ($amb_val === 'componente') ? 'selected' : '' ?>>Componente</option>
        </select>
        <?php if (!$has_ambito_column): ?>
          <small class="help-note">Ejecuta la migración para habilitar el ámbito.</small>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group" id="ambito-kit-wrap">
      <label>Kit</label>
      <select name="kit_id" id="kit-select">
        <option value="">-- Selecciona --</option>
        <?php foreach ($kits as $k): ?>
          <option value="<?= (int)$k['id'] ?>" data-slug="<?= htmlspecialchars($k['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= ($kit_id == (int)$k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <small class="help-note">Solo se guardará cuando el ámbito sea Kit.</small>
    </div>

    <div class="form-group" id="ambito-item-wrap" style="min-width:280px; display:none;">
      <label>Componente (si ámbito = componente)</label>
      <select name="item_id" <?= $has_item_id_column ? '' : 'disabled' ?>>
        <option value="">-- Selecciona --</option>
        <?php foreach ($kit_items as $it): ?>
          <option value="<?= (int)$it['id'] ?>" data-nombre="<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>" data-slug="<?= htmlspecialchars($it['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= ($item_val === (int)$it['id']) ? 'selected' : '' ?>><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)</option>
        <?php endforeach; ?>
      </select>
      <?php if (!$has_item_id_column): ?>
        <small class="help-note">Ejecuta la migración para habilitar el vínculo con componentes.</small>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Idioma</label>
        <input type="text" name="idioma" value="<?= htmlspecialchars($manual['idioma'] ?? 'es-CO') ?>" />
      </div>
      <div class="form-group">
        <label>Versión</label>
        <input type="text" name="version" value="<?= htmlspecialchars($manual['version'] ?? '1.0') ?>" />
      </div>
      <div class="form-group">
        <label>Tiempo (minutos)</label>
        <input type="number" name="time_minutes" value="<?= htmlspecialchars($manual['time_minutes'] ?? '') ?>" />
      </div>
      <div class="form-group">
        <label>Dificultad de Ensamble</label>
        <input type="text" name="dificultad_ensamble" value="<?= htmlspecialchars($manual['dificultad_ensamble'] ?? '') ?>" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <?php $st = $manual['status'] ?? 'draft'; ?>
          <option value="draft" <?= ($st === 'draft') ? 'selected' : '' ?>>Borrador</option>
          <option value="approved" <?= ($st === 'approved') ? 'selected' : '' ?>>Aprobado</option>
          <option value="published" <?= ($st === 'published') ? 'selected' : '' ?>>Publicado</option>
          <option value="discontinued" <?= ($st === 'discontinued') ? 'selected' : '' ?>>Descontinuado</option>
        </select>
        <small>Usa Aprobado cuando esté listo para publicar; Publicado lo hace visible en la web.</small>
      </div>
      <div class="form-group">
        <label>Manual visible en la web</label>
        <?php $rm = isset($manual['render_mode']) ? $manual['render_mode'] : ((!empty($manual['html'])) ? 'fullhtml' : 'legacy'); ?>
        <select name="render_mode">
          <option value="legacy" <?= ($rm === 'legacy') ? 'selected' : '' ?>>Estructurado</option>
          <option value="fullhtml" <?= ($rm === 'fullhtml') ? 'selected' : '' ?>>HTML completo</option>
        </select>
        <small>Elige qué manual verá el público; el otro se conserva.</small>
      </div>
    </div>

    <div class="form-group">
      <label>Resumen (opcional)</label>
      <input type="text" name="resumen" maxlength="255" value="<?= htmlspecialchars($manual['resumen'] ?? '') ?>" <?= $has_resumen_column ? '' : 'disabled' ?> />
      <small class="help-note">Breve extracto (≤255). Útil para tarjetas en el frontend.</small>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tiempo (minutos)</label>
        <input type="number" name="time_minutes" value="<?= htmlspecialchars($manual['time_minutes'] ?? '') ?>" />
      </div>
      <div class="form-group">
        <label>Dificultad de Ensamble</label>
        <input type="text" name="dificultad_ensamble" value="<?= htmlspecialchars($manual['dificultad_ensamble'] ?? '') ?>" />
      </div>
    </div>

    <div class="form-group">
      <label>Manual a editar</label>
      <div class="mode-toggle">
        <label><input type="radio" name="ui_mode" value="legacy" checked /> Manual estructurado (Seguridad/Herramientas/Pasos)</label>
        <label><input type="radio" name="ui_mode" value="fullhtml" /> Manual HTML (bloque único)</label>
      </div>
      <div id="mode-warning" class="help-note"></div>
    </div>

    <div class="form-group">
      <label>Pasos</label>
      <div id="steps-builder">
        <div class="steps-toolbar">
          <button type="button" class="btn btn-sm btn-primary" id="add-step-btn" title="Añadir Paso">+ Añadir Paso</button>
        </div>
        <ul id="steps-list" class="steps-list"></ul>
        <p class="help-note">Los pasos se guardan como bloques HTML ordenados. Se serializan a JSON antes de enviar.</p>
      </div>
      <textarea name="pasos_json" id="pasos_json" rows="8" style="display:none;" placeholder='[ {"orden":1, "titulo":"Paso 1", "html":"<p>...</p>"} ]'><?= htmlspecialchars($manual['pasos_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Herramientas</label>
      <div id="tools-builder">
        <div class="tools-toolbar">
          <button type="button" class="btn btn-sm btn-primary" id="add-tool-btn" title="Añadir Herramienta">+ Añadir Herramienta</button>
        </div>
        <ul id="tools-list" class="tools-list"></ul>
        <p class="help-note">Añade herramientas una por una. Se guardan como objetos con nombre, cantidad y notas. Se serializan a JSON antes de enviar.</p>
      </div>
      <textarea name="herramientas_json" id="herramientas_json" rows="4" style="display:none;" placeholder='[ {"nombre":"tijeras","cantidad":1,"nota":"pequeñas","seguridad":"Usar con cuidado"} ]'><?= htmlspecialchars($manual['herramientas_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Seguridad</label>
      <div id="security-builder">
        <?php
          $kit_seg_obj = null;
          if (!empty($kit['seguridad'])) {
            try { $tmp = json_decode($kit['seguridad'], true); if (is_array($tmp)) { $kit_seg_obj = $tmp; } } catch(Exception $e) {}
          }
        ?>
        <div id="kit-safety-panel" class="kit-safety-panel<?= $kit_seg_obj ? '' : ' muted' ?>">
          <div class="kit-safety-head"><strong>Medidas del kit</strong></div>
          <div class="kit-safety-body">
            <div id="kit-security-chip" class="kit-security-chip" style="display: <?= (!empty($kit_seg_obj['edad_min']) || !empty($kit_seg_obj['edad_max'])) ? '' : 'none' ?>;">
              Edad del kit: <?= !empty($kit_seg_obj['edad_min']) ? (int)$kit_seg_obj['edad_min'] : '?' ?>–<?= !empty($kit_seg_obj['edad_max']) ? (int)$kit_seg_obj['edad_max'] : '?' ?> años
            </div>
            <div id="kit-safety-notes" class="kit-safety-notes">
              <?php if ($kit_seg_obj && !empty($kit_seg_obj['notas'])): ?>
                <?= nl2br(h($kit_seg_obj['notas'])) ?>
              <?php else: ?>
                <span class="muted">(El kit no tiene notas de seguridad textuales)</span>
              <?php endif; ?>
            </div>
          </div>
          <label class="kit-safety-choose"><input type="checkbox" id="use-kit-safety" /> Incluir seguridad del kit en este manual</label>
          <div class="help-note">Si la incluyes, puedes además añadir notas específicas del manual y una edad propia.</div>
        </div>
        <div class="security-age">
          <strong>Edad segura (opcional)</strong>
          <div class="age-fields">
            <div>
              <label>Mín</label>
              <input type="number" id="sec-age-min" min="0" />
            </div>
            <div>
              <label>Máx</label>
              <input type="number" id="sec-age-max" min="0" />
            </div>
          </div>
        </div>
        <div class="security-toolbar">
          <button type="button" class="btn btn-sm btn-primary" id="add-sec-note-btn" title="Añadir Medida">+ Añadir Medida</button>
        </div>
        <ul id="security-list" class="security-list"></ul>
        <p class="help-note">Añade notas de seguridad una por una. Si defines edad segura, se guardará junto a las notas.</p>
      </div>
      <textarea name="seguridad_json" id="seguridad_json" rows="4" style="display:none;" placeholder='{"edad":{"min":10,"max":14},"notas":[{"nota":"Usar gafas","categoria":"protección"}]}' ><?= htmlspecialchars($manual['seguridad_json'] ?? '') ?></textarea>
    </div>


    <div class="form-group" id="html-group">
      <label>Contenido HTML</label>
      <textarea name="html" id="html-textarea" rows="10" placeholder="Contenido enriquecido del manual (opcional)"><?= htmlspecialchars($manual['html'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:12px;">
      <button type="submit" class="btn btn-primary">Guardar</button>
      <?php if ($manual): ?>
        <a class="btn" href="/admin/kits/manuals/index.php?kit_id=<?= (int)$kit_id ?>">Cancelar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>
<script>
console.log('🔍 [ManualsEdit] Manual ID:', <?= (int)$manual_id ?>, 'Kit ID:', <?= (int)$kit_id ?>);
// Publicado en (si existe) para generar fecha dd-mm-yy
var MANUAL_PUBLISHED_AT = <?= json_encode(isset($manual['published_at']) ? $manual['published_at'] : null) ?>;
// Slug del kit para sufijo automático
var KIT_SLUG = <?= json_encode(isset($kit['slug']) ? $kit['slug'] : null) ?>;
// Kit safety data for merge
var KIT_SAFETY = <?= json_encode(isset($kit_seg_obj) ? $kit_seg_obj : null, JSON_UNESCAPED_UNICODE) ?>;
console.log('🔍 [ManualsEdit] KIT_SAFETY:', KIT_SAFETY ? 'sí' : 'no');

// --- Step Builder (CKEditor via CDN, no installs) ---
(function(){
  // Toggle ambito → item selector
  const ambSel = document.querySelector('select[name="ambito"]');
  const itemWrap = document.getElementById('ambito-item-wrap');
  const kitWrap = document.getElementById('ambito-kit-wrap');
  function applyAmb(){
    if (!ambSel || !itemWrap) return;
    const v = ambSel.value;
    itemWrap.style.display = (v === 'componente') ? '' : 'none';
    if (kitWrap) { kitWrap.style.display = (v === 'componente') ? 'none' : ''; }
    console.log('🔍 [ManualsEdit] Ámbito:', v);
  }
  if (ambSel) { ambSel.addEventListener('change', applyAmb); applyAmb(); }
})();

// --- Slug Generator & Normalizer ---
(function(){
  const slugInput = document.getElementById('manual-slug');
  const genBtn = document.getElementById('btn-generar-slug');
  const tipoSel = document.querySelector('select[name="tipo_manual"]');
  const ambSel = document.querySelector('select[name="ambito"]');
  const itemSel = document.querySelector('select[name="item_id"]');
  const verInput = document.querySelector('input[name="version"]');
  const statusSel = document.querySelector('select[name="status"]');
  let LAST_AUTO_SLUG = (slugInput ? slugInput.value : '');

  function baseSlugify(str){
    if (!str) return '';
    try { str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch(e) {}
    return String(str)
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function normalizeManualSlug(raw){
    // Siempre devolver con prefijo único 'manual-'. Si el cuerpo queda vacío → 'manual-'
    let s = String(raw || '');
    // Pre-slugify to clean characters
    s = s.toLowerCase().replace(/[^a-z0-9-]+/g, '-').replace(/-+/g, '-');
    // Remove any leading repetitions of manual-
    s = s.replace(/^(?:manual-)+/, '');
    // Body slug
    const body = s.replace(/^-+|-+$/g, '');
    const finalSlug = body ? ('manual-' + body) : 'manual-';
    return finalSlug;
  }

  function getItemNombre(){
    if (!itemSel) return '';
    const opt = itemSel.options[itemSel.selectedIndex];
    if (!opt) return '';
    const nombre = opt.getAttribute('data-nombre') || opt.textContent || '';
    return nombre.replace(/\s*\(SKU.*\)$/i, '').trim();
  }

  function getItemSlug(){
    if (!itemSel) return '';
    const opt = itemSel.options[itemSel.selectedIndex];
    if (!opt) return '';
    const s = opt.getAttribute('data-slug') || '';
    if (s) return s;
    // Fallback: derive from name
    return baseSlugify(getItemNombre());
  }

  function buildSuggestion(){
    const tipo = (tipoSel ? tipoSel.value : 'armado') || 'armado';
    const verInput = document.querySelector('input[name="version"]');
    const rawVer = (verInput ? verInput.value : '') || '';
    // Mantener solo números y puntos; puntos → guiones bajos y prefijo 'V'
    const verClean = rawVer.toLowerCase().replace(/[^0-9.]+/g, '');
    const verNormUnderscore = verClean ? verClean.replace(/\./g, '_') : '';
    const verPart = verNormUnderscore ? ('V' + verNormUnderscore) : '';
    // Fecha dd-mm-yy: usar MANUAL_PUBLISHED_AT si existe; si no, hoy
    let dateStr = '';
    (function(){
      let d;
      if (typeof MANUAL_PUBLISHED_AT === 'string' && MANUAL_PUBLISHED_AT) {
        const t = Date.parse(MANUAL_PUBLISHED_AT);
        d = isNaN(t) ? new Date() : new Date(t);
      } else {
        d = new Date();
      }
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yy = String(d.getFullYear()).slice(-2);
      dateStr = dd + '-' + mm + '-' + yy;
    })();

    // manual-{tipo}-{entidad}-{dd-mm-yy}-{Vversion}
    const parts = ['manual', tipo];
    // Append kit or component slug
    let entitySlug = '';
    const amb = (ambSel ? ambSel.value : 'kit') || 'kit';
    if (amb === 'componente') {
      entitySlug = getItemSlug();
    } else {
      entitySlug = KIT_SLUG || '';
    }
    if (entitySlug) parts.push(entitySlug);
    parts.push(dateStr);
    if (verPart) parts.push(verPart);
    const base = parts.join('-');
    const s = normalizeManualSlug(base);
    console.log('🔍 [ManualsEdit] Sugerencia de slug:', base, '→', s);
    return s;
  }

  function applySuggestionIfEmpty(){
    if (!slugInput) return;
    const cur = (slugInput.value || '').trim();
    if (cur === '') {
      slugInput.value = buildSuggestion();
    }
  }

  if (genBtn) {
    genBtn.addEventListener('click', function(){
      if (!slugInput) return;
      const s = buildSuggestion();
      slugInput.value = s;
      LAST_AUTO_SLUG = s;
      console.log('✅ [ManualsEdit] Slug generado:', s);
    });
  }

  // Autogenerar cuando cambie tipo/ámbito/componente si el campo está vacío
  function autoUpdate(reason){
    if (!slugInput) return;
    const s = buildSuggestion();
    slugInput.value = s;
    LAST_AUTO_SLUG = s;
    console.log('✅ [ManualsEdit] Slug auto-actualizado por', reason, '→', s);
  }

  if (tipoSel) tipoSel.addEventListener('change', function(){ autoUpdate('tipo'); });
  if (ambSel) ambSel.addEventListener('change', function(){ autoUpdate('ambito'); });
  if (itemSel) itemSel.addEventListener('change', function(){ autoUpdate('componente'); });
  if (verInput) verInput.addEventListener('input', function(){ autoUpdate('version'); });
  if (statusSel) statusSel.addEventListener('change', function(){ autoUpdate('status'); });

  // Normalizar mientras escribe (suave): al perder foco
  if (slugInput) {
    // Prefill on focus if empty
    slugInput.addEventListener('focus', function(){
      if ((slugInput.value || '').trim() === '') {
        slugInput.value = 'manual-';
        console.log('ℹ️ [ManualsEdit] Prefill slug con manual-');
      }
    });
    // Enforce prefix and normalization on input without duplicating manual-
    slugInput.addEventListener('input', function(){
      const val = slugInput.value || '';
      if (!val.toLowerCase().startsWith('manual-')) {
        const norm = normalizeManualSlug(val);
        if (norm !== slugInput.value) {
          slugInput.value = norm;
          console.log('ℹ️ [ManualsEdit] Forzando prefijo manual-');
        }
      }
    });
    // Final normalization on blur
    slugInput.addEventListener('blur', function(){
      const norm = normalizeManualSlug(slugInput.value);
      if (norm !== slugInput.value) {
        console.log('ℹ️ [ManualsEdit] Normalizando slug a:', norm);
        slugInput.value = norm;
      }
    });
  }
})();

(function(){
  // Mode toggle logic
  const modeRadios = Array.from(document.querySelectorAll('input[name="ui_mode"]'));
  const htmlGroup = document.getElementById('html-group');
  const htmlTextarea = document.getElementById('html-textarea');
  const modeWarning = document.getElementById('mode-warning');
  const blocks = [document.getElementById('steps-builder'), document.getElementById('tools-builder'), document.getElementById('security-builder')];

  function applyMode(mode) {
    if (mode === 'fullhtml') {
      modeWarning.textContent = 'ℹ️ Editor HTML activo: ocultamos Seguridad, Herramientas y Pasos. Nada se elimina.';
      blocks.forEach(b => { if (b) b.classList.add('hidden-block'); });
      htmlGroup.classList.remove('hidden-block');
      console.log('ℹ️ [ManualsEdit] Editor: html');
    } else {
      modeWarning.textContent = 'ℹ️ Editor estructurado activo: aquí editas Seguridad, Herramientas y Pasos. El HTML no se usa en este editor.';
      blocks.forEach(b => { if (b) b.classList.remove('hidden-block'); });
      htmlGroup.classList.add('hidden-block');
      console.log('ℹ️ [ManualsEdit] Editor: estructurado');
    }
  }

  modeRadios.forEach(r => r.addEventListener('change', () => applyMode(r.value)));
  // Initial mode: if HTML has content, default to fullhtml
  const initialMode = <?= json_encode(isset($manual['render_mode']) ? ($manual['render_mode'] === 'fullhtml' ? 'fullhtml' : 'legacy') : ((!empty($manual['html'])) ? 'fullhtml' : 'legacy')) ?>;
  modeRadios.forEach(r => { r.checked = (r.value === initialMode); });
  applyMode(initialMode);

  const pasosTextarea = document.getElementById('pasos_json');
  const stepsList = document.getElementById('steps-list');
  const addBtn = document.getElementById('add-step-btn');

  let steps = [];
  let editorInstance = null;
  let modalState = { mode: 'create', index: -1 };

  function safeParseJSON(raw) {
    try { return raw ? JSON.parse(raw) : []; } catch (e) { console.log('⚠️ [ManualsEdit] JSON pasos inválido, se reinicia:', e.message); return []; }
  }

  function ensureModal() {
    let modal = document.getElementById('step-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'step-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Editar Paso</h3>
          <label>Título</label>
          <input type="text" id="modal-step-title" />
          <label>Contenido (HTML enriquecido)</label>
          <textarea id="modal-step-html" rows="10"></textarea>
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="modal-save-btn">Guardar</button>
            <button type="button" class="btn" id="modal-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      const saveBtn = document.getElementById('modal-save-btn');
      const cancelBtn = document.getElementById('modal-cancel-btn');
      saveBtn.addEventListener('click', function(){ saveEditorModal(); });
      cancelBtn.addEventListener('click', function(){ closeEditorModal(); });
      console.log('✅ [ManualsEdit] Modal creado');
    }
    return modal;
  }

  function normalizeStep(step, idx) {
    const orden = (typeof step.orden === 'number' ? step.orden : (idx + 1));
    const titulo = (step.titulo && String(step.titulo).trim()) || ('Paso ' + orden);
    // Map posible "descripcion" a "html"
    let html = step.html || '';
    if (!html && step.descripcion) {
      html = '<p>' + String(step.descripcion).replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>';
    }
    return { orden, titulo, html };
  }

  function sortByOrden(a, b) { return (a.orden || 0) - (b.orden || 0); }

  function renumber() {
    steps.forEach((s, i) => { s.orden = i + 1; });
  }

  function renderSteps() {
    steps.sort(sortByOrden);
    stepsList.innerHTML = '';
    steps.forEach((s, i) => {
      const li = document.createElement('li');
      li.className = 'step-item';
      li.setAttribute('data-index', String(i));

      const header = document.createElement('div');
      header.className = 'step-header';
      header.innerHTML = `
        <span class="step-order">#${s.orden}</span>
        <span class="step-title">${escapeHTML(s.titulo)}</span>
        <div class="step-actions">
          <button type="button" class="btn btn-sm" data-action="up" title="Mover arriba">↑</button>
          <button type="button" class="btn btn-sm" data-action="down" title="Mover abajo">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit" title="Editar">✏️</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete" title="Eliminar">🗑️</button>
        </div>
      `;

      li.appendChild(header);
      stepsList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizados', steps.length, 'pasos');
  }

  function escapeHTML(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function openEditorModal(initialTitle, initialHTML, mode, index) {
    modalState = { mode, index };
    ensureModal();
    document.getElementById('modal-step-title').value = initialTitle || '';
    const ta = document.getElementById('modal-step-html');
    ta.value = initialHTML || '';
    if (editorInstance) { try { editorInstance.destroy(); } catch(e) {} editorInstance = null; }
    if (window.CKEDITOR) {
      editorInstance = CKEDITOR.replace('modal-step-html');
      console.log('🔍 [ManualsEdit] CKEditor inicializado (modo:', mode, ', idx:', index, ')');
    } else {
      console.log('⚠️ [ManualsEdit] CKEditor no cargado aún');
    }
    document.getElementById('step-modal').style.display = 'flex';
    console.log('✅ [ManualsEdit] Step modal abierto (display:flex)');
  }

  function closeEditorModal() {
    document.getElementById('step-modal').style.display = 'none';
    if (editorInstance) { try { editorInstance.destroy(); } catch(e) {} editorInstance = null; }
  }

  function saveEditorModal() {
    const title = document.getElementById('modal-step-title').value.trim() || 'Paso';
    let html = document.getElementById('modal-step-html').value;
    if (editorInstance && editorInstance.getData) {
      html = editorInstance.getData();
    }
    if (modalState.mode === 'create') {
      steps.push({ orden: steps.length + 1, titulo: title, html });
      console.log('✅ [ManualsEdit] Paso creado');
    } else if (modalState.mode === 'edit' && modalState.index >= 0) {
      steps[modalState.index].titulo = title;
      steps[modalState.index].html = html;
      console.log('✅ [ManualsEdit] Paso actualizado idx', modalState.index);
    }
    renumber();
    renderSteps();
    closeEditorModal();
  }

  stepsList.addEventListener('click', function(ev) {
    const btn = ev.target.closest('button');
    if (!btn) return;
    const li = ev.target.closest('.step-item');
    const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) {
      const tmp = steps[idx-1]; steps[idx-1] = steps[idx]; steps[idx] = tmp; renumber(); renderSteps();
      console.log('🔍 [ManualsEdit] Paso movido arriba idx', idx);
    } else if (action === 'down' && idx < steps.length - 1) {
      const tmp = steps[idx+1]; steps[idx+1] = steps[idx]; steps[idx] = tmp; renumber(); renderSteps();
      console.log('🔍 [ManualsEdit] Paso movido abajo idx', idx);
    } else if (action === 'delete') {
      if (confirm('¿Eliminar este paso?')) { steps.splice(idx, 1); renumber(); renderSteps(); console.log('✅ [ManualsEdit] Paso eliminado idx', idx); }
    } else if (action === 'edit') {
      openEditorModal(steps[idx].titulo, steps[idx].html, 'edit', idx);
    }
  });

  addBtn.addEventListener('click', function() { openEditorModal('', '', 'create', -1); });

  // Before submit: serialize steps -> textarea
  const form = document.querySelector('form');
  form.addEventListener('submit', function() {
    const payload = steps.map((s, i) => ({ orden: i+1, titulo: s.titulo, html: s.html }));
    pasosTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado pasos_json bytes:', pasosTextarea.value.length);
  });

  // Initialize from existing JSON
  steps = safeParseJSON(pasosTextarea.value).map(normalizeStep);
  renumber();
  renderSteps();
})();

// --- Security Builder ---
(function(){
  const secTextarea = document.getElementById('seguridad_json');
  const secList = document.getElementById('security-list');
  const addBtn = document.getElementById('add-sec-note-btn');
  const ageMinInput = document.getElementById('sec-age-min');
  const ageMaxInput = document.getElementById('sec-age-max');
  const useKitSafety = document.getElementById('use-kit-safety');
  const kitSafetyPanel = document.getElementById('kit-safety-panel');
  const kitSafetyChip = document.getElementById('kit-security-chip');
  const kitSafetyNotes = document.getElementById('kit-safety-notes');
  const kitSelect = document.querySelector('select[name="kit_id"]');
  const securityAgeWrap = document.querySelector('.security-age');

  function toSafetyObj(raw){
    try {
      if (!raw) return null;
      if (typeof raw === 'string') return JSON.parse(raw);
      if (typeof raw === 'object') return raw;
      return null;
    } catch(e) {
      console.log('⚠️ [ManualsEdit] Error parse seguridad kit:', e.message);
      return null;
    }
  }

  function renderKitSafetyPanel(obj){
    KIT_SAFETY = toSafetyObj(obj);
    if (!kitSafetyPanel) return;
    if (!KIT_SAFETY) {
      kitSafetyPanel.classList.add('muted');
      if (kitSafetyChip) kitSafetyChip.style.display = 'none';
      if (kitSafetyNotes) kitSafetyNotes.innerHTML = '<span class="muted">(El kit no tiene notas de seguridad textuales)</span>';
      console.log('⚠️ [ManualsEdit] Panel seguridad kit: vacío');
      return;
    }
    kitSafetyPanel.classList.remove('muted');
    const min = (typeof KIT_SAFETY.edad_min !== 'undefined') ? parseInt(KIT_SAFETY.edad_min,10) : null;
    const max = (typeof KIT_SAFETY.edad_max !== 'undefined') ? parseInt(KIT_SAFETY.edad_max,10) : null;
    if (kitSafetyChip) {
      if (min !== null || max !== null) {
        kitSafetyChip.style.display = '';
        kitSafetyChip.textContent = 'Edad del kit: ' + (min !== null ? min : '?') + '–' + (max !== null ? max : '?') + ' años';
      } else {
        kitSafetyChip.style.display = 'none';
      }
    }
    if (kitSafetyNotes) {
      const notas = (KIT_SAFETY.notas ? String(KIT_SAFETY.notas) : '');
      kitSafetyNotes.innerHTML = notas ? notas.replace(/\n/g,'<br>') : '<span class="muted">(El kit no tiene notas de seguridad textuales)</span>';
    }
    console.log('✅ [ManualsEdit] Panel seguridad kit actualizado');
    updateAgeVisibility();
  }

  function hasKitAge(){
    if (!KIT_SAFETY) return false;
    const hasMin = typeof KIT_SAFETY.edad_min !== 'undefined' && KIT_SAFETY.edad_min !== null && String(KIT_SAFETY.edad_min) !== '';
    const hasMax = typeof KIT_SAFETY.edad_max !== 'undefined' && KIT_SAFETY.edad_max !== null && String(KIT_SAFETY.edad_max) !== '';
    return !!(hasMin || hasMax);
  }

  function updateAgeVisibility(){
    const inherit = !!(useKitSafety && useKitSafety.checked);
    const kitHas = hasKitAge();
    if (inherit && kitHas) {
      if (securityAgeWrap) securityAgeWrap.classList.add('hidden-block');
      if (ageMinInput) ageMinInput.disabled = true;
      if (ageMaxInput) ageMaxInput.disabled = true;
      console.log('ℹ️ [ManualsEdit] Usando edad del kit: ocultando campos de edad propia');
    } else {
      if (securityAgeWrap) securityAgeWrap.classList.remove('hidden-block');
      if (ageMinInput) ageMinInput.disabled = false;
      if (ageMaxInput) ageMaxInput.disabled = false;
      console.log('ℹ️ [ManualsEdit] Editar edad propia: campos visibles');
    }
  }

  async function fetchKitSafetyById(id){
    try {
      const res = await fetch('/api/kit-get.php?id=' + encodeURIComponent(String(id)));
      if (!res.ok) { console.log('❌ [ManualsEdit] Fetch kit-get status:', res.status); return null; }
      const data = await res.json();
      console.log('📡 [ManualsEdit] kit-get respuesta:', data);
      if (data && data.ok && data.kit) {
        const obj = toSafetyObj(data.kit.seguridad || null);
        return obj;
      }
      return null;
    } catch (e) {
      console.log('❌ [ManualsEdit] Error fetch kit-get:', e.message);
      return null;
    }
  }

  if (kitSelect) {
    kitSelect.addEventListener('change', async function(){
      const id = this.value ? parseInt(this.value, 10) : 0;
      // Update KIT_SLUG for slug suggestion
      const opt = this.options[this.selectedIndex];
      const optSlug = opt ? (opt.getAttribute('data-slug') || '') : '';
      KIT_SLUG = optSlug || KIT_SLUG;
      console.log('🔍 [ManualsEdit] KIT_SLUG actualizado:', KIT_SLUG || '(vacío)');
      // Regenerar slug si campo está vacío
      const slugInput = document.getElementById('manual-slug');
      if (slugInput && (slugInput.value || '').trim() === '') {
        slugInput.value = (typeof buildSuggestion === 'function') ? buildSuggestion() : slugInput.value;
        console.log('✅ [ManualsEdit] Slug regenerado tras cambio de kit:', slugInput.value);
      }
      if (!id) { renderKitSafetyPanel(null); return; }
      const seg = await fetchKitSafetyById(id);
      renderKitSafetyPanel(seg);
    });
    console.log('🔍 [ManualsEdit] Observando cambios de kit_id');
  }

  if (useKitSafety) {
    useKitSafety.addEventListener('change', function(){
      updateAgeVisibility();
    });
  }

  let notes = [];

  function safeParse(raw){ try { return raw ? JSON.parse(raw) : null; } catch(e){ console.log('⚠️ [ManualsEdit] JSON seguridad inválido:', e.message); return null; } }
  function escapeHTML(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function normalizeNote(n){
    if (typeof n === 'string') return { nota: n, categoria: '' };
    if (n && typeof n === 'object') return { nota: (n.nota ? String(n.nota) : ''), categoria: (n.categoria ? String(n.categoria) : '') };
    return { nota: '', categoria: '' };
  }

  function render(){
    secList.innerHTML = '';
    notes.forEach((n, i) => {
      const li = document.createElement('li');
      li.className = 'sec-item';
      li.setAttribute('data-index', String(i));
      const header = document.createElement('div');
      header.className = 'sec-header';
      header.innerHTML = `
        <span class="sec-title">${escapeHTML(n.nota || '(sin texto)')}</span>
        ${n.categoria ? `<span class="badge sec-cat">${escapeHTML(n.categoria)}</span>` : ''}
        <div class="sec-actions">
          <button type="button" class="btn btn-sm" data-action="up" title="Mover arriba">↑</button>
          <button type="button" class="btn btn-sm" data-action="down" title="Mover abajo">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit" title="Editar">✏️</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete" title="Eliminar">🗑️</button>
        </div>`;
      li.appendChild(header);
      secList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizadas', notes.length, 'notas de seguridad');
  }

  function openModal(initial, mode, index){
    ensureSecModal();
    document.getElementById('sec-note-text').value = initial?.nota || '';
    const catSelect = document.getElementById('sec-note-cat-select');
    const customWrap = document.getElementById('sec-note-cat-custom-wrap');
    const catInput = document.getElementById('sec-note-cat');
    const catVal = (initial?.categoria || '').toLowerCase();
    const options = Array.from(catSelect.options).map(o => o.value);
    if (options.includes(catVal)) {
      catSelect.value = catVal;
      customWrap.style.display = (catVal === 'otro') ? '' : 'none';
      if (catVal !== 'otro') catInput.value = '';
    } else if (catVal) {
      catSelect.value = 'otro';
      customWrap.style.display = '';
      catInput.value = initial?.categoria || '';
    } else {
      catSelect.value = '';
      customWrap.style.display = 'none';
      catInput.value = '';
    }
    const modal = document.getElementById('sec-modal');
    modal.dataset.mode = mode;
    modal.dataset.index = String(index);
    modal.style.display = 'flex';
  }
  function closeModal(){ document.getElementById('sec-modal').style.display = 'none'; }
  function saveModal(){
    const text = document.getElementById('sec-note-text').value.trim();
    const catSelect = document.getElementById('sec-note-cat-select');
    const catInput = document.getElementById('sec-note-cat');
    let cat = '';
    if (catSelect.value === 'otro') {
      cat = catInput.value.trim();
    } else {
      cat = catSelect.value.trim();
    }
    if (!text) { alert('Texto de nota requerido'); return; }
    const modal = document.getElementById('sec-modal');
    const mode = modal.dataset.mode;
    const idx = parseInt(modal.dataset.index, 10);
    const obj = { nota: text, categoria: cat };
    if (mode === 'create') { notes.push(obj); console.log('✅ [ManualsEdit] Nota de seguridad creada'); }
    else if (mode === 'edit' && idx >= 0) { notes[idx] = obj; console.log('✅ [ManualsEdit] Nota de seguridad actualizada idx', idx); }
    render();
    closeModal();
  }

  secList.addEventListener('click', function(ev){
    const btn = ev.target.closest('button'); if (!btn) return;
    const li = ev.target.closest('.sec-item'); const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) { const tmp = notes[idx-1]; notes[idx-1] = notes[idx]; notes[idx] = tmp; render(); }
    else if (action === 'down' && idx < notes.length - 1) { const tmp = notes[idx+1]; notes[idx+1] = notes[idx]; notes[idx] = tmp; render(); }
    else if (action === 'edit') { openModal(notes[idx], 'edit', idx); }
    else if (action === 'delete') { if (confirm('¿Eliminar nota?')) { notes.splice(idx,1); render(); } }
  });

  addBtn.addEventListener('click', function(){ openModal({ nota:'', categoria:'' }, 'create', -1); });

  const form = document.querySelector('form');
  form.addEventListener('submit', function(){
    const minRaw = ageMinInput.value.trim(); const maxRaw = ageMaxInput.value.trim();
    const min = minRaw === '' ? null : parseInt(minRaw,10);
    const max = maxRaw === '' ? null : parseInt(maxRaw,10);
    const extras = notes.map(n => ({ nota: n.nota, categoria: n.categoria }));
    let payload = null;
    if (useKitSafety && useKitSafety.checked) {
      payload = { usar_seguridad_kit: true };
      const kitHas = hasKitAge();
      if (!kitHas && (min !== null || max !== null)) {
        payload.edad = {};
        if (min !== null) payload.edad.min = min;
        if (max !== null) payload.edad.max = max;
        console.log('ℹ️ [ManualsEdit] Merge: kit sin edad, usando edad propia');
      } else if (kitHas) {
        console.log('ℹ️ [ManualsEdit] Merge: edad del kit presente, no se serializa edad propia');
      }
      if (extras.length) { payload.notas_extra = extras; }
      console.log('ℹ️ [ManualsEdit] Merge: incluir seguridad del kit');
    } else {
      if (min !== null || max !== null) {
        payload = { edad: { }, notas: extras };
        if (min !== null) payload.edad.min = min;
        if (max !== null) payload.edad.max = max;
        console.log('ℹ️ [ManualsEdit] Seguridad: edad + notas propias');
      } else {
        payload = extras;
        console.log('ℹ️ [ManualsEdit] Seguridad: solo notas propias');
      }
    }
    secTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado seguridad_json bytes:', secTextarea.value.length);
  });

  // Initialize from existing JSON
  (function init(){
    const raw = safeParse(secTextarea.value);
    if (raw && typeof raw === 'object' && !Array.isArray(raw)) {
      if (typeof raw.usar_seguridad_kit !== 'undefined') {
        if (useKitSafety) useKitSafety.checked = !!raw.usar_seguridad_kit;
      }
      if (raw.edad) {
        if (typeof raw.edad.min !== 'undefined') ageMinInput.value = String(raw.edad.min);
        if (typeof raw.edad.max !== 'undefined') ageMaxInput.value = String(raw.edad.max);
      }
      const ns = Array.isArray(raw.notas_extra) ? raw.notas_extra : (Array.isArray(raw.notas) ? raw.notas : []);
      notes = ns.map(normalizeNote);
    } else {
      const arr = Array.isArray(raw) ? raw : [];
      notes = arr.map(normalizeNote);
    }
    render();
    updateAgeVisibility();
  })();

  function ensureSecModal(){
    let modal = document.getElementById('sec-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'sec-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Nota de Seguridad</h3>
          <label>Texto</label>
          <input type="text" id="sec-note-text" />
          <label>Tipo de riesgo</label>
          <select id="sec-note-cat-select">
            <option value="">-- Seleccionar --</option>
            <option value="protección personal">Protección personal</option>
            <option value="corte">Corte</option>
            <option value="químico">Químico</option>
            <option value="eléctrico">Eléctrico</option>
            <option value="calor/fuego">Calor/Fuego</option>
            <option value="biológico">Biológico</option>
            <option value="presión/golpe">Presión/Golpe</option>
            <option value="entorno/ventilación">Entorno/Ventilación</option>
            <option value="supervisión adulta">Supervisión adulta</option>
            <option value="residuos/reciclaje">Residuos/Reciclaje</option>
            <option value="otro">Otro…</option>
          </select>
          <div id="sec-note-cat-custom-wrap" style="display:none;">
            <label>Otro (especifica)</label>
            <input type="text" id="sec-note-cat" />
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="sec-save-btn">Guardar</button>
            <button type="button" class="btn" id="sec-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      document.getElementById('sec-save-btn').addEventListener('click', saveModal);
      document.getElementById('sec-cancel-btn').addEventListener('click', closeModal);
      const catSelect = document.getElementById('sec-note-cat-select');
      const customWrap = document.getElementById('sec-note-cat-custom-wrap');
      catSelect.addEventListener('change', function(){
        customWrap.style.display = (catSelect.value === 'otro') ? '' : 'none';
      });
      console.log('✅ [ManualsEdit] Security modal creado');
    }
    return modal;
  }
})();

// --- Tools Builder ---
(function(){
  const toolsTextarea = document.getElementById('herramientas_json');
  const toolsList = document.getElementById('tools-list');
  const addToolBtn = document.getElementById('add-tool-btn');
  let tools = [];
  let toolModalState = { mode: 'create', index: -1 };

  function safeParseTools(raw){
    try { return raw ? JSON.parse(raw) : []; } catch(e){ console.log('⚠️ [ManualsEdit] JSON herramientas inválido, se reinicia:', e.message); return []; }
  }

  function isAssocArray(a){ return Array.isArray(a) ? (Object.keys(a).some(k => isNaN(parseInt(k,10)))) : false; }

  function escapeHTML(str){
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function normalizeTool(t){
    if (typeof t === 'string') return { nombre: t, cantidad: 1 };
    if (Array.isArray(t)) return { nombre: JSON.stringify(t), cantidad: 1 };
    if (t && typeof t === 'object') {
      return {
        nombre: (t.nombre ? String(t.nombre) : ''),
        cantidad: (t.cantidad !== undefined && t.cantidad !== null ? t.cantidad : ''),
        nota: (t.nota ? String(t.nota) : ''),
        seguridad: (t.seguridad ? String(t.seguridad) : '')
      };
    }
    return { nombre: '', cantidad: '', nota: '', seguridad: '' };
  }

  function renderTools(){
    toolsList.innerHTML = '';
    tools.forEach((t, i) => {
      const li = document.createElement('li');
      li.className = 'tool-item';
      li.setAttribute('data-index', String(i));
      const header = document.createElement('div');
      header.className = 'tool-header';
      header.innerHTML = `
        <span class="tool-title">${escapeHTML(t.nombre || '(sin nombre)')}</span>
        <div class="tool-actions">
          <button type="button" class="btn btn-sm" data-action="up" title="Mover arriba">↑</button>
          <button type="button" class="btn btn-sm" data-action="down" title="Mover abajo">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit" title="Editar">✏️</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete" title="Eliminar">🗑️</button>
        </div>`;
      li.appendChild(header);
      toolsList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizadas', tools.length, 'herramientas');
  }

  function openToolModal(initial, mode, index){
    toolModalState = { mode, index };
    ensureToolModal();
    document.getElementById('tool-name').value = initial?.nombre || '';
    document.getElementById('tool-qty').value = (initial?.cantidad !== undefined && initial?.cantidad !== null) ? String(initial.cantidad) : '';
    document.getElementById('tool-note').value = initial?.nota || '';
    document.getElementById('tool-sec').value = initial?.seguridad || '';
    document.getElementById('tool-modal').style.display = 'flex';
  }

  function closeToolModal(){ document.getElementById('tool-modal').style.display = 'none'; }

  function saveToolModal(){
    const nombre = document.getElementById('tool-name').value.trim();
    const cantidadRaw = document.getElementById('tool-qty').value.trim();
    const nota = document.getElementById('tool-note').value.trim();
    const seguridad = document.getElementById('tool-sec').value.trim();
    const cantidad = (cantidadRaw === '' ? '' : (/^\d+$/.test(cantidadRaw) ? parseInt(cantidadRaw,10) : cantidadRaw));
    const obj = { nombre, cantidad, nota, seguridad };
    if (!nombre) { alert('Nombre requerido'); return; }
    if (toolModalState.mode === 'create') {
      tools.push(obj);
      console.log('✅ [ManualsEdit] Herramienta creada');
    } else if (toolModalState.mode === 'edit' && toolModalState.index >= 0) {
      tools[toolModalState.index] = obj;
      console.log('✅ [ManualsEdit] Herramienta actualizada idx', toolModalState.index);
    }
    renderTools();
    closeToolModal();
  }

  toolsList.addEventListener('click', function(ev){
    const btn = ev.target.closest('button');
    if (!btn) return;
    const li = ev.target.closest('.tool-item');
    const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) { const tmp = tools[idx-1]; tools[idx-1] = tools[idx]; tools[idx] = tmp; renderTools(); console.log('🔍 [ManualsEdit] Herramienta arriba idx', idx); }
    else if (action === 'down' && idx < tools.length - 1) { const tmp = tools[idx+1]; tools[idx+1] = tools[idx]; tools[idx] = tmp; renderTools(); console.log('🔍 [ManualsEdit] Herramienta abajo idx', idx); }
    else if (action === 'delete') { if (confirm('¿Eliminar herramienta?')) { tools.splice(idx, 1); renderTools(); console.log('✅ [ManualsEdit] Herramienta eliminada idx', idx); } }
    else if (action === 'edit') { openToolModal(tools[idx], 'edit', idx); }
  });

  addToolBtn.addEventListener('click', function(){ openToolModal({ nombre:'', cantidad:'', nota:'', seguridad:'' }, 'create', -1); });

  // Serialize on submit
  const form = document.querySelector('form');
  form.addEventListener('submit', function(){
    const payload = tools.map(t => ({ nombre: t.nombre, cantidad: t.cantidad, nota: t.nota, seguridad: t.seguridad }));
    toolsTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado herramientas_json bytes:', toolsTextarea.value.length);
  });

  // Initialize
  tools = safeParseTools(toolsTextarea.value).map(normalizeTool);
  renderTools();

  function ensureToolModal(){
    let modal = document.getElementById('tool-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'tool-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Herramienta</h3>
          <label>Nombre</label>
          <input type="text" id="tool-name" />
          <label>Cantidad (número o texto)</label>
          <input type="text" id="tool-qty" />
          <label>Nota</label>
          <input type="text" id="tool-note" />
          <label>Nota de Seguridad</label>
          <input type="text" id="tool-sec" />
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="tool-save-btn">Guardar</button>
            <button type="button" class="btn" id="tool-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      document.getElementById('tool-save-btn').addEventListener('click', saveToolModal);
      document.getElementById('tool-cancel-btn').addEventListener('click', closeToolModal);
      console.log('✅ [ManualsEdit] Tool modal creado');
    }
    return modal;
  }
})();

// Modal se crea bajo demanda por ensureModal()

// CKEditor CDN
(function(){
  const s = document.createElement('script');
  s.src = 'https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js';
  s.onload = function(){ console.log('✅ [ManualsEdit] CKEditor cargado'); };
  s.onerror = function(){ console.log('❌ [ManualsEdit] Error cargando CKEditor CDN'); };
  document.head.appendChild(s);
})();
</script>

<style>
/* Step Builder styles - compact, admin-friendly */
.steps-toolbar { display:flex; gap:4px; margin-bottom:4px; }
.steps-list { list-style:none; padding:0; margin:0; }
.step-item { border:1px solid #ddd; margin-bottom:6px; border-radius:6px; overflow:hidden; }
.step-header { display:flex; align-items:center; justify-content:space-between; background:#f7f7f7; padding:4px 6px; }
.step-order { font-weight:bold; margin-right:8px; }
.step-title { flex:1; }
.step-actions { display:flex; gap:6px; }
.step-body { padding:6px; background:#fff; }
.help-note { color:#666; font-size:11px; margin-top:4px; }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:9999; }
.modal-content { background:#fff; width:min(900px, 92vw); max-height:90vh; overflow:auto; padding:16px; border-radius:8px; }
.modal-actions { display:flex; gap:8px; margin-top:8px; }

/* Tools Builder styles */
.tools-toolbar { display:flex; gap:4px; margin-bottom:4px; }
.tools-list { list-style:none; padding:0; margin:0; }
.tool-item { border:1px solid #ddd; margin-bottom:6px; border-radius:6px; overflow:hidden; }
.tool-header { display:flex; align-items:center; justify-content:space-between; background:#f7f7f7; padding:4px 6px; }
.tool-title { flex:1; }
.tool-actions { display:flex; gap:6px; }
.tool-body { padding:6px; background:#fff; color:#444; }
.mode-toggle { display:flex; gap:8px; align-items:center; }
.disabled-block { opacity:0.5; pointer-events:none; }
.hidden-block { display:none; }
.kit-safety-panel { border:1px solid #ddd; padding:6px; border-radius:6px; background:#f9fafb; margin-bottom:6px; }
.kit-safety-head { font-weight:600; margin-bottom:4px; }
.kit-safety-notes { color:#444; }
.sec-item { border:1px solid #ddd; margin-bottom:6px; border-radius:6px; overflow:hidden; }
.sec-header { display:flex; align-items:center; justify-content:space-between; background:#f7f7f7; padding:4px 6px; }
.sec-actions { display:flex; gap:6px; }
.sec-body { padding:6px; background:#fff; color:#444; }
.badge { display:inline-block; padding:0.2rem 0.5rem; border-radius:10px; font-size:0.8rem; font-weight:600; background:#e7e7e7; color:#333; margin-left:6px; }
</style>
<style>
/* Compact form controls */
.form-group { margin-bottom:8px; }
.form-row { display:flex; flex-wrap:wrap; gap:8px; }
.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
textarea { padding: 6px; font-size: 0.9rem; }

/* Compact buttons */
.btn-sm { padding: 0.3rem 0.6rem; font-size: 0.85rem; line-height: 1.1; }
</style>
</script>