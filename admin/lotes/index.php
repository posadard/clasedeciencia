<?php
require_once '../auth.php';
$page_title = 'Lotes';

function admin_audit(PDO $pdo, string $modulo, string $entidad, int $entidad_id, string $accion, array $detalle = []): void {
  try {
    $stmt = $pdo->prepare("INSERT INTO auditoria_admin (modulo, entidad, entidad_id, accion, usuario, detalle_json, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $modulo,
      $entidad,
      $entidad_id,
      $accion,
      (string)($_SESSION['admin_username'] ?? 'admin'),
      json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      (string)($_SERVER['REMOTE_ADDR'] ?? ''),
      mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)
    ]);
  } catch (Exception $e) {
    error_log('Admin audit lotes: ' . $e->getMessage());
  }
}

if (!isset($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  } catch (Exception $e) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
  }
}

$search = trim($_GET['search'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$kit_filtro = isset($_GET['kit_id']) && ctype_digit((string)$_GET['kit_id']) ? (int)$_GET['kit_id'] : 0;
$estados_validos = ['activo', 'bloqueado', 'agotado', 'cerrado'];
$flash_ok = '';
$flash_error = '';

$kits = [];
$contratos = [];
try {
  $kits = $pdo->query("SELECT id, nombre, codigo FROM kits ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
  $contratos = $pdo->query("SELECT id, numero FROM contratos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $kits = [];
  $contratos = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [Lotes] CSRF invalido");</script>';
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
      $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
      if ($id <= 0) {
        $flash_error = 'ID de lote invalido.';
      } else {
        try {
          $pdo->prepare("DELETE FROM lotes WHERE id = ?")->execute([$id]);
          admin_audit($pdo, 'lotes', 'lote', $id, 'eliminar', ['source' => 'admin/lotes/index.php']);
          $flash_ok = 'Lote eliminado correctamente.';
          echo '<script>console.log("✅ [Lotes] Lote eliminado:", ' . (int)$id . ');</script>';
        } catch (PDOException $e) {
          $flash_error = 'No se pudo eliminar el lote. Puede tener entregas asociadas.';
          echo '<script>console.log("❌ [Lotes] Error al eliminar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        }
      }
    }
  }
}

$params = [];
$where = ['1=1'];

if ($search !== '') {
  $where[] = '(l.codigo_lote LIKE ? OR l.ubicacion LIKE ? OR k.nombre LIKE ?)';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
}
if (in_array($estado, $estados_validos, true)) {
  $where[] = 'l.estado_lote = ?';
  $params[] = $estado;
}
if ($kit_filtro > 0) {
  $where[] = 'l.kit_id = ?';
  $params[] = $kit_filtro;
}

$lotes = [];
try {
  $sql = "SELECT l.*, k.nombre AS kit_nombre, k.codigo AS kit_codigo, c.numero AS contrato_numero
          FROM lotes l
          JOIN kits k ON k.id = l.kit_id
          LEFT JOIN contratos c ON c.id = l.contrato_id
          WHERE " . implode(' AND ', $where) . "
          ORDER BY l.updated_at DESC, l.id DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $flash_error = $flash_error ?: 'No se pudo cargar el listado de lotes.';
}

$metricas = [
  'total' => 0,
  'activos' => 0,
  'sin_disponible' => 0,
  'stock_promedio' => 0.0
];
try {
  $stmt = $pdo->query("SELECT
                  COUNT(*) AS total,
                  SUM(CASE WHEN estado_lote = 'activo' THEN 1 ELSE 0 END) AS activos,
                  SUM(CASE WHEN cantidad_disponible = 0 THEN 1 ELSE 0 END) AS sin_disponible,
                  AVG(stock_disponible_pct) AS stock_promedio
                FROM v_admin_lotes_resumen");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $metricas = [
      'total' => (int)($row['total'] ?? 0),
      'activos' => (int)($row['activos'] ?? 0),
      'sin_disponible' => (int)($row['sin_disponible'] ?? 0),
      'stock_promedio' => (float)($row['stock_promedio'] ?? 0)
    ];
  }
} catch (PDOException $e) {
  try {
    $stmt = $pdo->query("SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_lote = 'activo' THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN cantidad_disponible = 0 THEN 1 ELSE 0 END) AS sin_disponible,
                    AVG(CASE WHEN cantidad_total > 0 THEN (cantidad_disponible / cantidad_total) * 100 ELSE 0 END) AS stock_promedio
                  FROM lotes");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $metricas = [
        'total' => (int)($row['total'] ?? 0),
        'activos' => (int)($row['activos'] ?? 0),
        'sin_disponible' => (int)($row['sin_disponible'] ?? 0),
        'stock_promedio' => (float)($row['stock_promedio'] ?? 0)
      ];
    }
  } catch (PDOException $ignored) {
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2>Lotes de Kits</h2>
  <span class="help-text">Gestión de inventario por lote, kit y contrato.</span>
  <script>
    console.log('✅ [Admin] Lotes index cargado');
    console.log('🔍 [Admin] Total lotes:', <?= count($lotes) ?>);
  </script>
</div>

<?php if ($flash_ok): ?>
  <div class="message success"><?= htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
  <div class="message error"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="metrics-grid">
  <div class="metric-card">
    <span class="metric-label">Total lotes</span>
    <strong class="metric-value"><?= (int)$metricas['total'] ?></strong>
  </div>
  <div class="metric-card metric-ok">
    <span class="metric-label">Activos</span>
    <strong class="metric-value"><?= (int)$metricas['activos'] ?></strong>
  </div>
  <div class="metric-card metric-risk">
    <span class="metric-label">Sin disponible</span>
    <strong class="metric-value"><?= (int)$metricas['sin_disponible'] ?></strong>
  </div>
  <div class="metric-card metric-info">
    <span class="metric-label">Stock promedio</span>
    <strong class="metric-value"><?= number_format((float)$metricas['stock_promedio'], 1, ',', '.') ?>%</strong>
  </div>
</div>

<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group search-group">
      <label for="search">Buscar:</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Codigo de lote, kit o ubicación..." />
    </div>
    <div class="filter-group">
      <label for="estado">Estado:</label>
      <select id="estado" name="estado">
        <option value="">Todos</option>
        <?php foreach ($estados_validos as $ev): ?>
          <option value="<?= htmlspecialchars($ev, ENT_QUOTES, 'UTF-8') ?>" <?= $estado === $ev ? 'selected' : '' ?>><?= strtoupper(htmlspecialchars($ev, ENT_QUOTES, 'UTF-8')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label for="kit_id">Kit:</label>
      <select id="kit_id" name="kit_id">
        <option value="">Todos</option>
        <?php foreach ($kits as $k): ?>
          <option value="<?= (int)$k['id'] ?>" <?= $kit_filtro === (int)$k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$k['nombre'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group actions-inline">
      <button type="submit" class="btn btn-sm">🔍 Filtrar</button>
      <a href="/admin/lotes/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado de lotes</h3>
  <a href="/admin/lotes/edit.php" class="btn">+ Nuevo Lote</a>
</div>

<?php if (empty($lotes)): ?>
  <div class="message info">No hay lotes con los filtros seleccionados.</div>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Lote</th>
        <th>Kit</th>
        <th>Contrato</th>
        <th>Stock</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lotes as $l): ?>
        <tr>
          <td><?= (int)$l['id'] ?></td>
          <td><strong><?= htmlspecialchars((string)$l['codigo_lote'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="help-text"><?= htmlspecialchars((string)$l['ubicacion'], ENT_QUOTES, 'UTF-8') ?></small></td>
          <td><?= htmlspecialchars((string)$l['kit_nombre'], ENT_QUOTES, 'UTF-8') ?><br><small class="help-text"><?= htmlspecialchars((string)$l['kit_codigo'], ENT_QUOTES, 'UTF-8') ?></small></td>
          <td><?= htmlspecialchars((string)($l['contrato_numero'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <small class="help-text">Total: <?= (int)$l['cantidad_total'] ?></small><br>
            <small class="help-text">Disp: <?= (int)$l['cantidad_disponible'] ?> | Asig: <?= (int)$l['cantidad_asignada'] ?> | Ent: <?= (int)$l['cantidad_entregada'] ?></small>
          </td>
          <td>
            <span class="estado-pill estado-<?= htmlspecialchars((string)$l['estado_lote'], ENT_QUOTES, 'UTF-8') ?>">
              <?= strtoupper(htmlspecialchars((string)$l['estado_lote'], ENT_QUOTES, 'UTF-8')) ?>
            </span>
          </td>
          <td class="actions">
            <a href="/admin/lotes/edit.php?id=<?= (int)$l['id'] ?>" class="btn btn-sm action-btn">Editar</a>
            <form method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar lote #<?= (int)$l['id'] ?>?');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>" />
              <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<style>
.metrics-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)); gap: 0.75rem; margin: 0 0 1rem; }
.metric-card { background: #f5f8fb; border: 1px solid #d8e0e8; border-radius: 8px; padding: 0.75rem; }
.metric-label { display: block; font-size: 0.8rem; color: #54606c; }
.metric-value { font-size: 1.35rem; color: #1f2a37; }
.metric-ok { background: #eef9f1; border-color: #cfe8d5; }
.metric-risk { background: #fff0f0; border-color: #f2cccc; }
.metric-info { background: #eef4ff; border-color: #d1ddff; }

.filters-bar { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
.filters-form { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.35rem; }
.filter-group input, .filter-group select { min-width: 180px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.search-group { flex: 1; }
.search-group input { min-width: 260px; }
.actions-inline { flex-direction: row; gap: 0.5rem; align-items: center; }

.inline-form { display: inline-block; margin: 0; }
.actions { white-space: nowrap; }
.estado-pill { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; color: #fff; }
.estado-activo { background: #4caf50; }
.estado-bloqueado { background: #ff9800; }
.estado-agotado { background: #d32f2f; }
.estado-cerrado { background: #424242; }

@media (max-width: 1000px) {
  .metrics-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
}
@media (max-width: 700px) {
  .metrics-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>