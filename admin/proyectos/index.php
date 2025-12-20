<?php
require_once '../auth.php';
$page_title = 'Proyectos';

// Filtros
$ciclo = isset($_GET['ciclo']) ? trim($_GET['ciclo']) : ''; // '1','2','3' o ''
$activo = isset($_GET['activo']) ? trim($_GET['activo']) : ''; // '1','0' o ''
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [];
$where = ['1=1'];
if (in_array($ciclo, ['1','2','3'], true)) { $where[] = 'ciclo = ?'; $params[] = $ciclo; }
if ($activo === '1' || $activo === '0') { $where[] = 'activo = ?'; $params[] = (int)$activo; }
if ($search !== '') { $where[] = '(nombre LIKE ? OR slug LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

try {
    $sql = "SELECT id, nombre, slug, ciclo, activo, destacado, updated_at FROM proyectos WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $proyectos = [];
}

include '../header.php';
?>
<div class="page-header">
  <h2>Proyectos</h2>
  <span class="help-text">Gestión básica de proyectos (CdC).</span>
  <script>
    console.log('✅ [Admin] Proyectos index cargado');
    console.log('🔍 [Admin] Filtros:', { ciclo: '<?= htmlspecialchars($ciclo, ENT_QUOTES, 'UTF-8') ?>', activo: '<?= htmlspecialchars($activo, ENT_QUOTES, 'UTF-8') ?>', search: '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>' });
    console.log('🔍 [Admin] Total proyectos:', <?= count($proyectos) ?>);
  </script>
</div>

<!-- Filtros (alineado al estilo del admin de materiales) -->
<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group">
      <label for="ciclo">Ciclo:</label>
      <select name="ciclo" id="ciclo" onchange="this.form.submit()">
        <option value="">Todos</option>
        <option value="1" <?= $ciclo==='1'?'selected':'' ?>>1 (6°-7°)</option>
        <option value="2" <?= $ciclo==='2'?'selected':'' ?>>2 (8°-9°)</option>
        <option value="3" <?= $ciclo==='3'?'selected':'' ?>>3 (10°-11°)</option>
      </select>
    </div>
    <div class="filter-group">
      <label for="activo">Estado:</label>
      <select name="activo" id="activo" onchange="this.form.submit()">
        <option value="">Todos</option>
        <option value="1" <?= $activo==='1'?'selected':'' ?>>Activos</option>
        <option value="0" <?= $activo==='0'?'selected':'' ?>>Inactivos</option>
      </select>
    </div>
    <div class="filter-group search-group">
      <label for="search">Buscar:</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre o slug..." />
      <button type="submit" class="btn btn-sm">🔍 Buscar</button>
      <?php if ($ciclo || $activo || $search): ?>
        <a href="/admin/proyectos/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado</h3>
  <a href="/admin/proyectos/edit.php" class="btn">+ Nuevo Proyecto</a>
</div>

<?php if (empty($proyectos)): ?>
  <div class="message info">
    No hay proyectos.
  </div>
  <p><a href="/admin/proyectos/edit.php" class="btn">Crear Proyecto</a></p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Ciclo</th>
        <th>Estado</th>
        <th>Actualizado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($proyectos as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><strong><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="help-text"><?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?></small></td>
        <td><?= htmlspecialchars($p['ciclo'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>
          <span style="padding:0.25rem 0.5rem;background:<?= ((int)$p['activo']) ? '#4caf50' : '#ff9800' ?>;color:#fff;font-size:0.75rem;font-weight:600;">
            <?= ((int)$p['activo']) ? 'ACTIVO' : 'INACTIVO' ?><?= ((int)$p['destacado']) ? ' · ★' : '' ?>
          </span>
        </td>
        <td><?= htmlspecialchars(date('Y-m-d', strtotime($p['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
        <td class="actions">
          <a href="/proyecto.php?slug=<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-secondary action-btn">Ver</a>
          <a href="/admin/proyectos/edit.php?id=<?= (int)$p['id'] ?>" class="btn action-btn">Editar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<!-- Inline styles to mirror materials admin search UI -->
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
.search-group input {
  flex: 1;
}
.btn-sm {
  padding: 0.4rem 0.8rem;
  font-size: 0.875rem;
}
</style>

<?php include '../footer.php'; ?>
