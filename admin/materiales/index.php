<?php
require_once '../auth.php';
require_once __DIR__ . '/../../includes/materials-functions.php';
$page_title = 'Componentes';

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
  <h2>Componentes</h2>
  <span class="help-text">Gestión de componentes del catálogo (kit_items).</span>
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
  <a href="/admin/materiales/edit.php" class="btn">+ Nuevo Componente</a>
</div>

<?php if (empty($materiales)): ?>
  <div class="empty-state">
    <p>No hay componentes.</p>
    <a href="/admin/materiales/edit.php" class="btn btn-primary">Crear Componente</a>
  </div>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Componente</th>
        <th>Categoría</th>
        <th>SKU</th>
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

<!-- Inline styles para alinear el cuadro de búsqueda al admin base de materiales -->
<style>
.filters-bar {
  background: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.filters-form {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.filter-group label {
  font-weight: bold;
  font-size: 0.9rem;
}
.filter-group select,
.filter-group input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-width: 200px;
}
.search-group {
  flex-direction: row;
  align-items: center;
  flex: 1;
}
.search-group input { flex: 1; }
.btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
.text-muted { color: #666; }
.badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold; background: #e7e7e7; color: #333; }
.actions { white-space: nowrap; }
.actions .btn { margin-right: 0.5rem; }
.btn-edit { background: #007bff; color: #fff; }
.btn-edit:hover { background: #0056b3; }
.empty-state { text-align: center; padding: 3rem; color: #666; }
</style>

<?php include '../footer.php'; ?>
