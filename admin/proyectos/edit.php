<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$page_title = $is_new ? 'Nuevo Proyecto' : 'Editar Proyecto';
include __DIR__ . '/../header.php';

$proyecto = null;
if (!$is_new && $pdo) {
  $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $proyecto = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo "<script>console.log('🔍 [Admin] Procesando formulario...')</script>";
  if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    echo '<p class="error">CSRF inválido</p>';
  } else {
    $nombre = trim($_POST['nombre'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $ciclo = (int)($_POST['ciclo'] ?? 1);
    $grados = isset($_POST['grados']) ? array_map('intval', $_POST['grados']) : [];
    $areas = isset($_POST['areas']) ? $_POST['areas'] : [];
    $dificultad = trim($_POST['dificultad'] ?? 'baja');
    $duracion = (int)($_POST['duracion_minutos'] ?? 30);
    $activo = isset($_POST['activo']) ? 1 : 0;

    $pasos_texto = trim($_POST['pasos'] ?? '');
    $pasos = array_values(array_filter(array_map('trim', explode("\n", $pasos_texto)), fn($x)=>$x!==''));
    $explicacion = trim($_POST['explicacion_cientifica'] ?? '');

    try {
      if ($is_new) {
        $stmt = $pdo->prepare("INSERT INTO proyectos (nombre, slug, ciclo, grados, areas, dificultad, duracion_minutos, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $slug, $ciclo, json_encode($grados, JSON_UNESCAPED_UNICODE), json_encode($areas, JSON_UNESCAPED_UNICODE), $dificultad, $duracion, $activo]);
        $id = (int)$pdo->lastInsertId();
      } else {
        $stmt = $pdo->prepare("UPDATE proyectos SET nombre=?, slug=?, ciclo=?, grados=?, areas=?, dificultad=?, duracion_minutos=?, activo=? WHERE id=?");
        $stmt->execute([$nombre, $slug, $ciclo, json_encode($grados, JSON_UNESCAPED_UNICODE), json_encode($areas, JSON_UNESCAPED_UNICODE), $dificultad, $duracion, $activo, $id]);
      }
      // Upsert guía
      $stmt = $pdo->prepare("SELECT proyecto_id FROM guias WHERE proyecto_id = ? LIMIT 1");
      $stmt->execute([$id]);
      if ($stmt->fetch()) {
        $upd = $pdo->prepare("UPDATE guias SET pasos=?, explicacion_cientifica=? WHERE proyecto_id=?");
        $upd->execute([json_encode($pasos, JSON_UNESCAPED_UNICODE), $explicacion, $id]);
      } else {
        $ins = $pdo->prepare("INSERT INTO guias (proyecto_id, pasos, explicacion_cientifica) VALUES (?, ?, ?)");
        $ins->execute([$id, json_encode($pasos, JSON_UNESCAPED_UNICODE), $explicacion]);
      }
      echo "<script>console.log('✅ [Admin] Guardado proyecto ID: {$id}')</script>";
      header('Location: index.php');
      exit;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      echo '<p class="error">Error al guardar</p>';
    }
  }
}

$areas_list = ['Física','Química','Biología','Tecnología','Ambiental'];
$grados_list = [6,7,8,9,10,11];
$dificultades = ['baja','media','alta'];
$sel_grados = $proyecto && !empty($proyecto['grados']) ? json_decode($proyecto['grados'], true) : [];
$sel_areas = $proyecto && !empty($proyecto['areas']) ? json_decode($proyecto['areas'], true) : [];
?>
<h1><?= h($page_title) ?></h1>
<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
  <div class="form-row">
    <div class="form-group">
      <label>Nombre *</label>
      <input type="text" id="nombre" name="nombre" required value="<?= h($proyecto['nombre'] ?? '') ?>" />
    </div>
    <div class="form-group">
      <label>Slug *</label>
      <input type="text" id="slug" name="slug" required value="<?= h($proyecto['slug'] ?? '') ?>" />
      <small>Se genera automáticamente al escribir el nombre.</small>
    </div>
    <div class="form-group">
      <label>Ciclo *</label>
      <select name="ciclo" required>
        <option value="1" <?= ($proyecto['ciclo'] ?? 1)==1?'selected':'' ?>>Exploración (6°-7°)</option>
        <option value="2" <?= ($proyecto['ciclo'] ?? 1)==2?'selected':'' ?>>Experimentación (8°-9°)</option>
        <option value="3" <?= ($proyecto['ciclo'] ?? 1)==3?'selected':'' ?>>Análisis (10°-11°)</option>
      </select>
    </div>
    <div class="form-group">
      <label>Dificultad *</label>
      <select name="dificultad" required>
        <?php foreach ($dificultades as $d): ?>
          <option value="<?= h($d) ?>" <?= ($proyecto['dificultad'] ?? 'baja')===$d?'selected':'' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Duración (min) *</label>
      <input type="number" name="duracion_minutos" min="1" required value="<?= h($proyecto['duracion_minutos'] ?? 30) ?>" />
    </div>
    <div class="form-group" style="display:flex;align-items:center;gap:.5rem">
      <label>Activo</label>
      <input type="checkbox" name="activo" value="1" <?= !empty($proyecto['activo'])?'checked':'' ?> />
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Grados *</label>
      <div>
        <?php foreach ($grados_list as $g): ?>
          <label style="margin-right:.5rem"><input type="checkbox" name="grados[]" value="<?= $g ?>" <?= in_array($g, $sel_grados??[])?'checked':'' ?> /> <?= $g ?>°</label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="form-group">
      <label>Áreas *</label>
      <div>
        <?php foreach ($areas_list as $a): ?>
          <label style="margin-right:.5rem"><input type="checkbox" name="areas[]" value="<?= h($a) ?>" <?= in_array($a, $sel_areas??[])?'checked':'' ?> /> <?= h($a) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <h2>Guía del Proyecto</h2>
  <div class="form-row">
    <div class="form-group" style="grid-column:1/-1">
      <label>Pasos (uno por línea)</label>
      <?php $pasos_val = ''; if ($proyecto) { $stmt = $pdo->prepare("SELECT pasos, explicacion_cientifica FROM guias WHERE proyecto_id = ?"); $stmt->execute([$proyecto['id']]); $g = $stmt->fetch(); if ($g && !empty($g['pasos'])) { $arr = json_decode($g['pasos'], true); if (is_array($arr)) { $pasos_val = implode("\n", $arr); $exp_val = $g['explicacion_cientifica'] ?? ''; } } } ?>
      <textarea name="pasos" rows="6" placeholder="Ej: \nPreparar materiales\nMontar el circuito\nRegistrar observaciones"><?= h($pasos_val) ?></textarea>
    </div>
    <div class="form-group" style="grid-column:1/-1">
      <label>Explicación científica</label>
      <textarea name="explicacion_cientifica" rows="4" placeholder="Describe el fundamento científico"><?= h($exp_val ?? '') ?></textarea>
    </div>
  </div>
  <div class="admin-actions">
    <button class="btn-primary" type="submit">Guardar</button>
    <a href="index.php">Volver</a>
  </div>
</form>
<script>console.log('🔍 [Admin] Edit proyecto <?= $is_new?'nuevo':'ID '.$id ?>');</script>
<?php include __DIR__ . '/../footer.php'; ?>