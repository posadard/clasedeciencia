<?php
require_once '../auth.php';
$page_title = 'Kits';

$ciclo = isset($_GET['ciclo']) ? trim($_GET['ciclo']) : '';
$activo = isset($_GET['activo']) ? trim($_GET['activo']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [];
$where = ['1=1'];
if (in_array($ciclo, ['1','2','3'], true)) { $where[] = 'c.ciclo = ?'; $params[] = $ciclo; }
if ($activo === '1' || $activo === '0') { $where[] = 'k.activo = ?'; $params[] = (int)$activo; }
if ($search !== '') { $where[] = '(k.nombre LIKE ? OR k.codigo LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

try {
  $sql = "SELECT k.id, k.nombre, k.codigo, k.version, k.activo, k.updated_at, k.seo_title, k.seo_description, c.id as clase_id, c.nombre as clase_nombre, c.ciclo
      FROM kits k
      LEFT JOIN clases c ON c.id = k.clase_id
      WHERE " . implode(' AND ', $where) . "
      ORDER BY k.updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kits = [];
}

include '../header.php';
?>
<div class="page-header">
  <h2>Kits</h2>
  <span class="help-text">Gestión de kits asociados a clases.</span>
  <script>
    console.log('✅ [Admin] Kits index cargado');
    console.log('🔍 [Admin] Filtros:', { ciclo: '<?= htmlspecialchars($ciclo, ENT_QUOTES, 'UTF-8') ?>', activo: '<?= htmlspecialchars($activo, ENT_QUOTES, 'UTF-8') ?>', search: '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>' });
    console.log('🔍 [Admin] Total kits:', <?= count($kits) ?>);
  </script>
</div>

<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group">
      <label for="ciclo">Ciclo (clase):</label>
      <select name="ciclo" id="ciclo" onchange="this.form.submit()">
        <option value="">Todos</option>
        <?php 
        $ciclos_kits = cdc_get_ciclos($pdo, true);
        foreach ($ciclos_kits as $ck): 
            $sel = ($ciclo == $ck['numero']) ? 'selected' : '';
        ?>
        <option value="<?= (int)$ck['numero'] ?>" <?= $sel ?>><?= h($ck['numero']) ?> (<?= h($ck['grados_texto']) ?>)</option>
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
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre o código..." />
      <button type="submit" class="btn btn-sm">🔍 Buscar</button>
      <?php if ($ciclo || $activo || $search): ?>
        <a href="/admin/kits/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado</h3>
  <a href="/admin/kits/edit.php" class="btn">+ Nuevo Kit</a>
</div>

<?php if (empty($kits)): ?>
  <div class="message info">No hay kits.</div>
  <p><a href="/admin/kits/edit.php" class="btn">Crear Kit</a></p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Kit</th>
        <th>Código</th>
        <th>Versión</th>
        <th>Clase</th>
        <th>Ciclo</th>
        <th>SEO</th>
        <th>Estado</th>
        <th>Actualizado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kits as $k): ?>
      <tr>
        <td><?= (int)$k['id'] ?></td>
        <td><strong><?= htmlspecialchars($k['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
        <td><code><?= htmlspecialchars($k['codigo'], ENT_QUOTES, 'UTF-8') ?></code></td>
        <td><?= htmlspecialchars($k['version'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(($k['clase_nombre'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(($k['ciclo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        <td>
          <?php 
            $seoT = isset($k['seo_title']) ? (string)$k['seo_title'] : '';
            $seoD = isset($k['seo_description']) ? (string)$k['seo_description'] : '';
            $hasSeo = ($seoT !== '' || $seoD !== '');
            $tShort = $seoT !== '' ? (mb_strlen($seoT,'UTF-8')>60 ? mb_substr($seoT,0,57,'UTF-8').'…' : $seoT) : '—';
            $dShort = $seoD !== '' ? (mb_strlen($seoD,'UTF-8')>80 ? mb_substr($seoD,0,77,'UTF-8').'…' : $seoD) : '—';
          ?>
          <div class="seo-cell">
            <div class="seo-title"><?= htmlspecialchars($tShort, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="seo-desc"><?= htmlspecialchars($dShort, ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!$hasSeo): ?><small class="hint">Sin SEO definido</small><?php endif; ?>
          </div>
        </td>
        <td>
          <span style="padding:0.25rem 0.5rem;background:<?= ((int)$k['activo']) ? '#4caf50' : '#ff9800' ?>;color:#fff;font-size:0.75rem;font-weight:600;">
            <?= ((int)$k['activo']) ? 'ACTIVO' : 'INACTIVO' ?>
          </span>
        </td>
        <td><?= htmlspecialchars(date('Y-m-d', strtotime($k['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
        <td class="actions">
          <a href="/admin/kits/edit.php?id=<?= (int)$k['id'] ?>" class="btn action-btn">Editar</a>
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
.seo-cell { max-width: 320px; }
.seo-title { font-weight: 600; font-size: 0.85rem; }
.seo-desc { color:#666; font-size:0.8rem; }
</style>

<?php include '../footer.php'; ?>
