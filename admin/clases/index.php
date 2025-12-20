<?php
require_once '../auth.php';
$page_title = 'Clases';

// Filtros
$ciclo = isset($_GET['ciclo']) ? (int)$_GET['ciclo'] : 0;
$activo = isset($_GET['activo']) ? trim($_GET['activo']) : ''; // '1','0' o ''
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Obtener ciclos válidos
$ciclos_validos = array_column(cdc_get_ciclos($pdo, true), 'numero');

$params = [];
$where = ['1=1'];
if ($ciclo > 0 && in_array($ciclo, $ciclos_validos, true)) { $where[] = 'ciclo = ?'; $params[] = $ciclo; }
if ($activo === '1' || $activo === '0') { $where[] = 'activo = ?'; $params[] = (int)$activo; }
if ($search !== '') { $where[] = '(nombre LIKE ? OR slug LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

try {
    $sql = "SELECT id, nombre, slug, ciclo, activo, destacado, updated_at FROM clases WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clases = [];
}

include '../header.php';
?>
<div class="page-header">
  <h2>Clases</h2>
  <span class="help-text">Gestión básica de clases (CdC).</span>
  <script>
    console.log('✅ [Admin] Clases index cargado');
    console.log('🔍 [Admin] Filtros:', { ciclo: '<?= htmlspecialchars($ciclo, ENT_QUOTES, 'UTF-8') ?>', activo: '<?= htmlspecialchars($activo, ENT_QUOTES, 'UTF-8') ?>', search: '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>' });
    console.log('🔍 [Admin] Total clases:', <?= count($clases) ?>);
  </script>
</div>

<!-- Filtros -->
<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group">
      <label for="ciclo">Ciclo:</label>
      <select name="ciclo" id="ciclo" onchange="this.form.submit()">
        <option value="">Todos</option>
        <?php 
        $ciclos_admin = cdc_get_ciclos($pdo, true);
        foreach ($ciclos_admin as $ca): 
            $sel = ($ciclo == $ca['numero']) ? 'selected' : '';
        ?>
        <option value="<?= (int)$ca['numero'] ?>" <?= $sel ?>><?= h($ca['numero']) ?> - <?= h($ca['nombre']) ?> (<?= h($ca['grados_texto']) ?>)</option>
        <?php endforeach; ?>
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
        <a href="/admin/clases/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado</h3>
  <a href="/admin/clases/edit.php" class="btn">+ Nueva Clase</a>
</div>

<?php if (empty($clases)): ?>
  <div class="message info">No hay clases.</div>
  <p><a href="/admin/clases/edit.php" class="btn">Crear Clase</a></p>
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
      <?php foreach ($clases as $p): ?>
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
          <a href="/admin/clases/edit.php?id=<?= (int)$p['id'] ?>" class="btn action-btn">Editar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<style>
.filters-bar { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
.filters-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
.filter-group label { font-weight: bold; font-size: 0.9rem; }
.filter-group select, .filter-group input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; min-width: 200px; }
.search-group { flex-direction: row; align-items: center; flex: 1; }
.search-group input { flex: 1; }
.btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
</style>

<?php include '../footer.php'; ?>
