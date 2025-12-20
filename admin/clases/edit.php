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
  'activo' => 1,
  'destacado' => 0,
  'resumen' => '',
  'objetivo_aprendizaje' => ''
];

if ($is_edit) {
  try {
    $stmt = $pdo->prepare('SELECT id, nombre, slug, ciclo, activo, destacado, resumen, objetivo_aprendizaje FROM clases WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { $clase = $row; } else { $is_edit = false; $id = null; }
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
    $activo = isset($_POST['activo']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $resumen = isset($_POST['resumen']) ? trim($_POST['resumen']) : '';
    $objetivo = isset($_POST['objetivo_aprendizaje']) ? trim($_POST['objetivo_aprendizaje']) : '';

    if ($slug === '' && $nombre !== '') {
      $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
      $slug = trim($slug, '-');
    }

    if ($nombre === '' || $slug === '' || !in_array($ciclo, ['1','2','3'], true)) {
      $error_msg = 'Completa nombre, ciclo y slug válidos.';
    } else {
      try {
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
          if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE clases SET nombre=?, slug=?, ciclo=?, activo=?, destacado=?, resumen=?, objetivo_aprendizaje=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$nombre, $slug, $ciclo, $activo, $destacado, $resumen, $objetivo, $id]);
          } else {
            $stmt = $pdo->prepare('INSERT INTO clases (nombre, slug, ciclo, activo, destacado, resumen, objetivo_aprendizaje, updated_at) VALUES (?,?,?,?,?,?,?,NOW())');
            $stmt->execute([$nombre, $slug, $ciclo, $activo, $destacado, $resumen, $objetivo]);
            $id = (int)$pdo->lastInsertId();
            $is_edit = true;
          }
          header('Location: /admin/clases/index.php');
          exit;
        }
      } catch (PDOException $e) {
        $error_msg = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
      }
    }
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
  <span class="help-text">Completa los campos de la clase.</span>
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
    <label><input type="checkbox" name="activo" <?= ((int)$clase['activo']) ? 'checked' : '' ?> /> Activo</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="destacado" <?= ((int)$clase['destacado']) ? 'checked' : '' ?> /> Destacado</label>
  </div>
  <div class="form-group">
    <label for="resumen">Resumen</label>
    <textarea id="resumen" name="resumen" rows="3" placeholder="Descripción corta..."><?= htmlspecialchars($clase['resumen'], ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="objetivo_aprendizaje">Objetivo de aprendizaje</label>
    <textarea id="objetivo_aprendizaje" name="objetivo_aprendizaje" rows="4" placeholder="Competencias MEN y objetivos..."><?= htmlspecialchars($clase['objetivo_aprendizaje'], ENT_QUOTES, 'UTF-8') ?></textarea>
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
  nombreInput.addEventListener('blur', () => {
    console.log('🔍 [ClasesEdit] blur nombre');
    if (!slugInput.value && nombreInput.value) {
      const s = nombreInput.value.toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '');
      slugInput.value = s;
      console.log('✅ [ClasesEdit] slug generado:', s);
    }
  });
</script>
<?php include '../footer.php'; ?>
