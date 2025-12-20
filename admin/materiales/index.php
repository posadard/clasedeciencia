<?php
require_once '../auth.php';
require_once __DIR__ . '/../../includes/materials-functions.php';
$page_title = 'Materiales';

// Filtros
$category = $_GET['category'] ?? ''; // slug de categoría
$search = $_GET['search'] ?? '';

$filters = [];
if ($category !== '') $filters['category'] = $category;
if ($search !== '') $filters['search'] = $search;

// Datos
$categories = get_material_categories($pdo);
$materiales = get_materials($pdo, $filters);

include '../header.php';
?>
<div class="page-header">
  <h2>Materiales</h2>
  <span class="help-text">Gestión de materiales del catálogo (CdC).</span>
  <script>
    console.log('✅ [Admin] Materiales index cargado');
    console.log('🔍 [Admin] Filtros:', { category: '<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>', search: '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>' });
    console.log('🔍 [Admin] Total materiales:', <?= count($materiales) ?>);
  </script>
</div>

<!-- Filtros -->
<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group">
      <label for="category">Categoría:</label>
      <select name="category" id="category" onchange="this.form.submit()">
        <option value="">Todas</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
            <?= htmlspecialchars(($cat['icon'] ?? '') . ' ' . ($cat['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group search-group">
      <label for="search">Buscar:</label>
      <input type="text" name="search" id="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre común, técnico o descripción..." />
      <button type="submit" class="btn btn-sm">🔍 Buscar</button>
      <?php if ($category || $search): ?>
        <a href="/admin/materiales/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado</h3>
  <a href="/admin/materiales/edit.php" class="btn">+ Nuevo Material</a>
</div>

<?php if (empty($materiales)): ?>
  <div class="empty-state">
    <p>No hay materiales.</p>
    <a href="/admin/materiales/edit.php" class="btn btn-primary">Crear Material</a>
  </div>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Material</th>
        <th>Categoría</th>
        <th>Slug</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($materiales as $m): ?>
      <tr>
        <td><?= (int)$m['id'] ?></td>
        <td>
          <strong><?= htmlspecialchars($m['common_name'], ENT_QUOTES, 'UTF-8') ?></strong>
          <?php if (!empty($m['technical_name'])): ?>
          <br><small class="text-muted"><?= htmlspecialchars($m['technical_name'], ENT_QUOTES, 'UTF-8') ?></small>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge">
            <?= htmlspecialchars(($m['category_icon'] ?? '') . ' ' . ($m['category_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
          </span>
        </td>
        <td><?= htmlspecialchars($m['slug'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="actions">
          <a href="/admin/materiales/edit.php?id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-edit">Editar</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php include '../footer.php'; ?>
