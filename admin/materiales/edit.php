<?php
require_once '../auth.php';
require_once __DIR__ . '/../../includes/materials-functions.php';
$page_title = 'Materiales - Editar';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null;

// Cargar material si edición
$material = null;
if ($is_edit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM materiales WHERE id = ?");
        $stmt->execute([$id]);
        $material = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $material = null;
    }
}

// Categorías
$categorias = get_material_categories($pdo);

// Guardar
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_comun = trim($_POST['nombre_comun'] ?? '');
    $nombre_tecnico = trim($_POST['nombre_tecnico'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $advertencias_seguridad = trim($_POST['advertencias_seguridad'] ?? '');
    $manejo_recomendado = trim($_POST['manejo_recomendado'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

    if ($nombre_comun === '') $errores[] = 'El nombre común es obligatorio';
    if ($categoria_id <= 0) $errores[] = 'La categoría es obligatoria';
    if ($descripcion === '') $errores[] = 'La descripción es obligatoria';

    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9-]+/i', '-', $nombre_comun));
        $slug = trim($slug, '-');
    }

    // Validar slug único
    try {
        if ($is_edit) {
            $stmt = $pdo->prepare("SELECT id FROM materiales WHERE slug = ? AND id <> ?");
            $stmt->execute([$slug, $id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM materiales WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un material con este slug';
        }
    } catch (PDOException $e) {
        $errores[] = 'Error validando slug: ' . $e->getMessage();
    }

    if (empty($errores)) {
        try {
            if ($is_edit) {
                $sql = "UPDATE materiales SET nombre_comun = ?, nombre_tecnico = ?, slug = ?, categoria_id = ?, descripcion = ?, advertencias_seguridad = ?, manejo_recomendado = ?, imagen = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_comun, $nombre_tecnico, $slug, $categoria_id, $descripcion, $advertencias_seguridad, $manejo_recomendado, $imagen, $id]);
            } else {
                $sql = "INSERT INTO materiales (nombre_comun, nombre_tecnico, slug, categoria_id, descripcion, advertencias_seguridad, manejo_recomendado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_comun, $nombre_tecnico, $slug, $categoria_id, $descripcion, $advertencias_seguridad, $manejo_recomendado, $imagen]);
                $id = (int)$pdo->lastInsertId();
            }
            echo "<script>console.log('✅ [Admin] Material guardado');</script>";
            header('Location: /admin/materiales/index.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= $is_edit ? 'Editar Material' : 'Nuevo Material' ?></h2>
  <span class="help-text">Campos mínimos del esquema CdC.</span>
  <script>
    console.log('✅ [Admin] Materiales edit cargado');
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

<form method="POST">
  <div class="form-group">
    <label for="nombre_comun">Nombre común *</label>
    <input type="text" id="nombre_comun" name="nombre_comun" required value="<?= htmlspecialchars($material['nombre_comun'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-group">
    <label for="nombre_tecnico">Nombre técnico</label>
    <input type="text" id="nombre_tecnico" name="nombre_tecnico" value="<?= htmlspecialchars($material['nombre_tecnico'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($material['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
    <small class="help-text">Si se deja vacío, se genera automáticamente.</small>
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
    <label for="descripcion">Descripción *</label>
    <textarea id="descripcion" name="descripcion" rows="6" required><?= htmlspecialchars($material['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="advertencias_seguridad">Advertencias de seguridad</label>
    <textarea id="advertencias_seguridad" name="advertencias_seguridad" rows="4"><?= htmlspecialchars($material['advertencias_seguridad'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="manejo_recomendado">Manejo recomendado</label>
    <textarea id="manejo_recomendado" name="manejo_recomendado" rows="4"><?= htmlspecialchars($material['manejo_recomendado'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="form-group">
    <label for="imagen">Imagen (URL)</label>
    <input type="url" id="imagen" name="imagen" value="<?= htmlspecialchars($material['imagen'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  <div class="form-actions">
    <button type="submit" class="btn"><?= $is_edit ? 'Actualizar' : 'Crear' ?></button>
    <a href="/admin/materiales/index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include '../footer.php'; ?>
