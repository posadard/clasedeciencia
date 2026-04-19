<?php
require_once '../auth.php';
$page_title = 'Entregas';

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
    error_log('Admin audit entregas: ' . $e->getMessage());
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
$contrato_filtro = isset($_GET['contrato_id']) && ctype_digit((string)$_GET['contrato_id']) ? (int)$_GET['contrato_id'] : 0;
$estados_validos = ['programada', 'en_transito', 'entregada', 'rechazada', 'reprogramada'];
$flash_ok = '';
$flash_error = '';

$contratos = [];
try {
  $contratos = $pdo->query("SELECT id, numero, entidad_contratante, departamento FROM contratos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $contratos = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [Entregas] CSRF invalido");</script>';
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
      $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
      if ($id <= 0) {
        $flash_error = 'ID de entrega invalido.';
      } else {
        try {
          $pdo->prepare("DELETE FROM entregas WHERE id = ?")->execute([$id]);
          admin_audit($pdo, 'entregas', 'entrega', $id, 'eliminar', ['source' => 'admin/entregas/index.php']);
          $flash_ok = 'Entrega eliminada correctamente.';
          echo '<script>console.log("✅ [Entregas] Entrega eliminada:", ' . (int)$id . ');</script>';
        } catch (PDOException $e) {
          $flash_error = 'No se pudo eliminar la entrega.';
          echo '<script>console.log("❌ [Entregas] Error al eliminar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        }
      }
    }
  }
}

$params = [];
$where = ['1=1'];

if ($search !== '') {
  $where[] = '(e.codigo_entrega LIKE ? OR e.institucion_educativa LIKE ? OR c.numero LIKE ?)';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
}
if (in_array($estado, $estados_validos, true)) {
  $where[] = 'e.estado_entrega = ?';
  $params[] = $estado;
}
if ($contrato_filtro > 0) {
  $where[] = 'e.contrato_id = ?';
  $params[] = $contrato_filtro;
}

$entregas = [];
try {
  $sql = "SELECT e.*, c.numero AS contrato_numero, c.entidad_contratante
          FROM entregas e
          JOIN contratos c ON c.id = e.contrato_id
          WHERE " . implode(' AND ', $where) . "
          ORDER BY e.updated_at DESC, e.id DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $flash_error = $flash_error ?: 'No se pudo cargar el listado de entregas.';
}

$metricas = [
  'total' => 0,
  'entregadas' => 0,
  'atrasadas' => 0,
  'sin_acta' => 0
];
try {
  $stmt = $pdo->query("SELECT
                  COUNT(*) AS total,
                  SUM(CASE WHEN estado_entrega = 'entregada' THEN 1 ELSE 0 END) AS entregadas,
                  SUM(CASE WHEN entrega_atrasada = 1 THEN 1 ELSE 0 END) AS atrasadas,
                  SUM(CASE WHEN acta_pdf IS NULL OR TRIM(acta_pdf) = '' THEN 1 ELSE 0 END) AS sin_acta
                FROM v_admin_entregas_resumen");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $metricas = [
      'total' => (int)($row['total'] ?? 0),
      'entregadas' => (int)($row['entregadas'] ?? 0),
      'atrasadas' => (int)($row['atrasadas'] ?? 0),
      'sin_acta' => (int)($row['sin_acta'] ?? 0)
    ];
  }
} catch (PDOException $e) {
  try {
    $stmt = $pdo->query("SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_entrega = 'entregada' THEN 1 ELSE 0 END) AS entregadas,
                    SUM(CASE WHEN estado_entrega IN ('programada','reprogramada','en_transito') AND fecha_programada IS NOT NULL AND fecha_programada < CURDATE() THEN 1 ELSE 0 END) AS atrasadas,
                    SUM(CASE WHEN acta_pdf IS NULL OR TRIM(acta_pdf) = '' THEN 1 ELSE 0 END) AS sin_acta
                  FROM entregas");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $metricas = [
        'total' => (int)($row['total'] ?? 0),
        'entregadas' => (int)($row['entregadas'] ?? 0),
        'atrasadas' => (int)($row['atrasadas'] ?? 0),
        'sin_acta' => (int)($row['sin_acta'] ?? 0)
      ];
    }
  } catch (PDOException $ignored) {
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2>Entregas de Kits</h2>
  <span class="help-text">Registro operativo de entregas, estado y evidencia por institución.</span>
  <script>
    console.log('✅ [Admin] Entregas index cargado');
    console.log('🔍 [Admin] Total entregas:', <?= count($entregas) ?>);
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
    <span class="metric-label">Total entregas</span>
    <strong class="metric-value"><?= (int)$metricas['total'] ?></strong>
  </div>
  <div class="metric-card metric-ok">
    <span class="metric-label">Entregadas</span>
    <strong class="metric-value"><?= (int)$metricas['entregadas'] ?></strong>
  </div>
  <div class="metric-card metric-warn">
    <span class="metric-label">Atrasadas</span>
    <strong class="metric-value"><?= (int)$metricas['atrasadas'] ?></strong>
  </div>
  <div class="metric-card metric-risk">
    <span class="metric-label">Sin acta</span>
    <strong class="metric-value"><?= (int)$metricas['sin_acta'] ?></strong>
  </div>
</div>

<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group search-group">
      <label for="search">Buscar:</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Codigo, institución o contrato..." />
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
      <label for="contrato_id">Contrato:</label>
      <select id="contrato_id" name="contrato_id">
        <option value="">Todos</option>
        <?php foreach ($contratos as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $contrato_filtro === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$c['numero'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group actions-inline">
      <button type="submit" class="btn btn-sm">🔍 Filtrar</button>
      <a href="/admin/entregas/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado de entregas</h3>
  <a href="/admin/entregas/edit.php" class="btn">+ Nueva Entrega</a>
</div>

<?php if (empty($entregas)): ?>
  <div class="message info">No hay entregas con los filtros seleccionados.</div>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Codigo</th>
        <th>Contrato</th>
        <th>Institución</th>
        <th>Estado</th>
        <th>Fechas</th>
        <th>Kits</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entregas as $e): ?>
        <tr>
          <td><?= (int)$e['id'] ?></td>
          <td><strong><?= htmlspecialchars((string)$e['codigo_entrega'], ENT_QUOTES, 'UTF-8') ?></strong></td>
          <td>
            <?= htmlspecialchars((string)$e['contrato_numero'], ENT_QUOTES, 'UTF-8') ?><br>
            <small class="help-text"><?= htmlspecialchars((string)$e['entidad_contratante'], ENT_QUOTES, 'UTF-8') ?></small>
          </td>
          <td><?= htmlspecialchars((string)$e['institucion_educativa'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <span class="estado-pill estado-<?= htmlspecialchars((string)$e['estado_entrega'], ENT_QUOTES, 'UTF-8') ?>">
              <?= strtoupper(htmlspecialchars((string)$e['estado_entrega'], ENT_QUOTES, 'UTF-8')) ?>
            </span>
          </td>
          <td>
            <small class="help-text">Prog: <?= htmlspecialchars((string)($e['fecha_programada'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></small><br>
            <small class="help-text">Real: <?= htmlspecialchars((string)($e['fecha'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></small>
          </td>
          <td><?= (int)$e['cantidad_kits'] ?></td>
          <td class="actions">
            <a href="/admin/entregas/edit.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm action-btn">Editar</a>
            <form method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar entrega #<?= (int)$e['id'] ?>?');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="id" value="<?= (int)$e['id'] ?>" />
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
.metric-warn { background: #fff8ea; border-color: #f0dfb1; }
.metric-risk { background: #fff0f0; border-color: #f2cccc; }

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
.estado-programada { background: #607d8b; }
.estado-en_transito { background: #ff9800; }
.estado-entregada { background: #4caf50; }
.estado-rechazada { background: #d32f2f; }
.estado-reprogramada { background: #3f51b5; }

@media (max-width: 1000px) {
  .metrics-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
}
@media (max-width: 700px) {
  .metrics-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>