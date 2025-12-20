<?php
require_once '../auth.php';
require_once __DIR__ . '/../../includes/materials-functions.php';
$page_title = 'Componentes - Editar';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null;

// Cargar material si edición
$material = null;
if ($is_edit) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM kit_items WHERE id = ?");
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
      $sku = trim($_POST['slug'] ?? '');
      $categoria_id = (int)($_POST['categoria_id'] ?? 0);
      $advertencias_seguridad = trim($_POST['advertencias_seguridad'] ?? '');
      $unidad = trim($_POST['unidad'] ?? 'pcs');

    if ($nombre_comun === '') $errores[] = 'El nombre común es obligatorio';
    if ($categoria_id <= 0) $errores[] = 'La categoría es obligatoria';
    // descripción ya no es obligatoria en kit_items (usamos advertencias_seguridad opcional)

    if ($sku === '') {
      $sku = strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', $nombre_comun));
      $sku = trim($sku, '-');
    }

    // Validar slug único
    try {
        if ($is_edit) {
          $stmt = $pdo->prepare("SELECT id FROM kit_items WHERE sku = ? AND id <> ?");
          $stmt->execute([$sku, $id]);
        } else {
          $stmt = $pdo->prepare("SELECT id FROM kit_items WHERE sku = ?");
          $stmt->execute([$sku]);
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
              $sql = "UPDATE kit_items SET nombre_comun = ?, sku = ?, categoria_id = ?, advertencias_seguridad = ?, unidad = ? WHERE id = ?";
              $stmt = $pdo->prepare($sql);
              $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad, $id]);
            } else {
              $sql = "INSERT INTO kit_items (nombre_comun, sku, categoria_id, advertencias_seguridad, unidad) VALUES (?, ?, ?, ?, ?)";
              $stmt = $pdo->prepare($sql);
              $stmt->execute([$nombre_comun, $sku, $categoria_id, $advertencias_seguridad, $unidad]);
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
  <h2><?= $is_edit ? 'Editar Componente' : 'Nuevo Componente' ?></h2>
  <span class="help-text">Campos mínimos del esquema CdC (kit_items).</span>
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
  <div class="form-actions">
    <button type="submit" class="btn"><?= $is_edit ? 'Actualizar' : 'Crear' ?></button>
    <a href="/admin/materiales/index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include '../footer.php'; ?>
