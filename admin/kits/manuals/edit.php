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
  $stmtK = $pdo->prepare('SELECT id, nombre, codigo, slug FROM kits WHERE id = ? LIMIT 1');
  $stmtK->execute([$kit_id]);
  $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [ManualsEdit] CSRF inválido");</script>';
  } else {
    $kit_id = intval($_POST['kit_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $version = trim($_POST['version'] ?? '1.0');
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $idioma = trim($_POST['idioma'] ?? 'es-CO');
    $time_minutes = ($_POST['time_minutes'] !== '' ? intval($_POST['time_minutes']) : null);
    $dificultad = trim($_POST['dificultad_ensamble'] ?? '');
    $pasos_json = trim($_POST['pasos_json'] ?? '');
    $herr_json = trim($_POST['herramientas_json'] ?? '');
    $seg_json = trim($_POST['seguridad_json'] ?? '');
    $html = $_POST['html'] ?? null;

    // Basic validations
    if ($kit_id <= 0) {
      $error_msg = 'Kit requerido.';
    } elseif ($slug === '') {
      $error_msg = 'Slug requerido.';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
      $error_msg = 'Slug inválido: usa a-z, 0-9 y guiones.';
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
          $stmtU = $pdo->prepare('UPDATE kit_manuals SET slug = ?, version = ?, status = ?, idioma = ?, time_minutes = ?, dificultad_ensamble = ?, pasos_json = ?, herramientas_json = ?, seguridad_json = ?, html = ? WHERE id = ?');
          $stmtU->execute([$slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html, $manual_id]);
          $success_msg = 'Manual actualizado.';
          echo '<script>console.log("✅ [ManualsEdit] Actualizado ID=' . $manual_id . '");</script>';
        } else {
          $stmtI = $pdo->prepare('INSERT INTO kit_manuals (kit_id, slug, version, status, idioma, time_minutes, dificultad_ensamble, pasos_json, herramientas_json, seguridad_json, html) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
          $stmtI->execute([$kit_id, $slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html]);
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

// Load kits for selector if needed
$kits = [];
if (!$kit) {
  $stmtKs = $pdo->query('SELECT id, nombre FROM kits ORDER BY nombre ASC');
  $kits = $stmtKs->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="container">
  <h1><?= $manual ? 'Editar Manual' : 'Nuevo Manual' ?></h1>
  <?php if ($kit): ?>
    <p>Kit: <strong><?= htmlspecialchars($kit['nombre']) ?></strong> (ID <?= (int)$kit['id'] ?>)</p>
    <p><a href="/admin/kits/manuals/index.php?kit_id=<?= (int)$kit['id'] ?>">Volver a Manuales</a></p>
  <?php endif; ?>

  <?php if ($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
  <?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />

    <div class="form-group">
      <label>Kit</label>
      <?php if ($kit): ?>
        <input type="hidden" name="kit_id" value="<?= (int)$kit['id'] ?>" />
        <input type="text" value="<?= htmlspecialchars($kit['nombre']) ?>" disabled />
      <?php else: ?>
        <select name="kit_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($kits as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= ($kit_id == (int)$k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label>Slug</label>
      <input type="text" name="slug" value="<?= htmlspecialchars($manual['slug'] ?? '') ?>" required placeholder="ej. armado-basico" />
      <small>Usa a-z, 0-9 y guiones.</small>
    </div>

    <div class="form-group">
      <label>Idioma</label>
      <input type="text" name="idioma" value="<?= htmlspecialchars($manual['idioma'] ?? 'es-CO') ?>" />
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Versión</label>
        <input type="text" name="version" value="<?= htmlspecialchars($manual['version'] ?? '1.0') ?>" />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <?php $st = $manual['status'] ?? 'draft'; ?>
          <option value="draft" <?= ($st === 'draft') ? 'selected' : '' ?>>Borrador</option>
          <option value="published" <?= ($st === 'published') ? 'selected' : '' ?>>Publicado</option>
        </select>
      </div>
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
      <label>Pasos (JSON)</label>
      <textarea name="pasos_json" rows="8" placeholder='[ {"orden":1, "titulo":"Paso 1", "descripcion":"..."} ]'><?= htmlspecialchars($manual['pasos_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Herramientas (JSON)</label>
      <textarea name="herramientas_json" rows="4" placeholder='[ "tijeras", "pegante" ]'><?= htmlspecialchars($manual['herramientas_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Seguridad (JSON)</label>
      <textarea name="seguridad_json" rows="4" placeholder='[ "Usa gafas de seguridad" ]'><?= htmlspecialchars($manual['seguridad_json'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Contenido HTML</label>
      <textarea name="html" rows="10" placeholder="Contenido enriquecido del manual (opcional)"><?= htmlspecialchars($manual['html'] ?? '') ?></textarea>
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
</script>