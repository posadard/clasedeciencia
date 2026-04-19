<?php
require_once '../auth.php';
$page_title = 'Contratos';

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
    error_log('Admin audit contratos: ' . $e->getMessage());
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
$departamento = trim($_GET['departamento'] ?? '');
$estados_validos = ['borrador', 'vigente', 'suspendido', 'finalizado', 'cerrado'];
$flash_ok = '';
$flash_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [Contratos] CSRF invalido");</script>';
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
      $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
      if ($id <= 0) {
        $flash_error = 'ID de contrato invalido.';
      } else {
        try {
          $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = ?");
          $stmt->execute([$id]);
          admin_audit($pdo, 'contratos', 'contrato', $id, 'eliminar', ['source' => 'admin/contratos/index.php']);
          $flash_ok = 'Contrato eliminado correctamente.';
          echo '<script>console.log("✅ [Contratos] Contrato eliminado:", ' . (int)$id . ');</script>';
        } catch (PDOException $e) {
          $flash_error = 'No se pudo eliminar el contrato. Puede tener entregas asociadas.';
          echo '<script>console.log("❌ [Contratos] Error al eliminar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        }
      }
    }
  }
}

$params = [];
$where = ['1=1'];

if ($search !== '') {
  $where[] = '(c.numero LIKE ? OR c.entidad_contratante LIKE ? OR c.supervisor LIKE ?)';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
  $params[] = '%' . $search . '%';
}

if (in_array($estado, $estados_validos, true)) {
  $where[] = 'c.estado_contrato = ?';
  $params[] = $estado;
}

if ($departamento !== '') {
  $where[] = 'c.departamento = ?';
  $params[] = $departamento;
}

$contratos = [];
$departamentos = [];
try {
  $sql = "SELECT c.*,
                 (SELECT COUNT(*) FROM entregas e WHERE e.contrato_id = c.id) AS total_entregas,
                 (c.valor - c.valor_ejecutado) AS saldo_pendiente
          FROM contratos c
          WHERE " . implode(' AND ', $where) . "
          ORDER BY c.updated_at DESC, c.id DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $departamentos = $pdo->query("SELECT DISTINCT departamento FROM contratos WHERE departamento IS NOT NULL AND departamento <> '' ORDER BY departamento ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
  $flash_error = $flash_error ?: 'No se pudo cargar el listado de contratos.';
}

$metricas = [
  'total' => 0,
  'vigentes' => 0,
  'con_saldo' => 0,
  'avance_promedio' => 0.0
];
try {
  $stmt = $pdo->query("SELECT
                  COUNT(*) AS total,
                  SUM(CASE WHEN estado_contrato = 'vigente' THEN 1 ELSE 0 END) AS vigentes,
                  SUM(CASE WHEN saldo_pendiente > 0 THEN 1 ELSE 0 END) AS con_saldo,
                  AVG(avance_financiero_pct) AS avance_promedio
                FROM v_admin_contratos_resumen");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $metricas = [
      'total' => (int)($row['total'] ?? 0),
      'vigentes' => (int)($row['vigentes'] ?? 0),
      'con_saldo' => (int)($row['con_saldo'] ?? 0),
      'avance_promedio' => (float)($row['avance_promedio'] ?? 0)
    ];
  }
} catch (PDOException $e) {
  try {
    $stmt = $pdo->query("SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_contrato = 'vigente' THEN 1 ELSE 0 END) AS vigentes,
                    SUM(CASE WHEN (valor - valor_ejecutado) > 0 THEN 1 ELSE 0 END) AS con_saldo,
                    AVG(CASE WHEN valor > 0 THEN (valor_ejecutado / valor) * 100 ELSE 0 END) AS avance_promedio
                  FROM contratos");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $metricas = [
        'total' => (int)($row['total'] ?? 0),
        'vigentes' => (int)($row['vigentes'] ?? 0),
        'con_saldo' => (int)($row['con_saldo'] ?? 0),
        'avance_promedio' => (float)($row['avance_promedio'] ?? 0)
      ];
    }
  } catch (PDOException $ignored) {
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2>Contratos CTeI</h2>
  <span class="help-text">Gestión de contratos, estado de ejecución y trazabilidad administrativa.</span>
  <script>
    console.log('✅ [Admin] Contratos index cargado');
    console.log('🔍 [Admin] Total contratos:', <?= count($contratos) ?>);
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
    <span class="metric-label">Total contratos</span>
    <strong class="metric-value"><?= (int)$metricas['total'] ?></strong>
  </div>
  <div class="metric-card metric-ok">
    <span class="metric-label">Vigentes</span>
    <strong class="metric-value"><?= (int)$metricas['vigentes'] ?></strong>
  </div>
  <div class="metric-card metric-warn">
    <span class="metric-label">Con saldo pendiente</span>
    <strong class="metric-value"><?= (int)$metricas['con_saldo'] ?></strong>
  </div>
  <div class="metric-card metric-info">
    <span class="metric-label">Avance promedio</span>
    <strong class="metric-value"><?= number_format((float)$metricas['avance_promedio'], 1, ',', '.') ?>%</strong>
  </div>
</div>

<div class="filters-bar">
  <form method="GET" class="filters-form">
    <div class="filter-group search-group">
      <label for="search">Buscar:</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Numero, entidad o supervisor..." />
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
      <label for="departamento">Departamento:</label>
      <select id="departamento" name="departamento">
        <option value="">Todos</option>
        <?php foreach ($departamentos as $dep_item): ?>
          <option value="<?= htmlspecialchars($dep_item, ENT_QUOTES, 'UTF-8') ?>" <?= $departamento === $dep_item ? 'selected' : '' ?>><?= htmlspecialchars($dep_item, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group actions-inline">
      <button type="submit" class="btn btn-sm">🔍 Filtrar</button>
      <a href="/admin/contratos/index.php" class="btn btn-sm btn-secondary">Limpiar</a>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado de contratos</h3>
  <a href="/admin/contratos/edit.php" class="btn">+ Nuevo Contrato</a>
</div>

<?php if (empty($contratos)): ?>
  <div class="message info">No hay contratos con los filtros seleccionados.</div>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Numero</th>
        <th>Entidad</th>
        <th>Ubicacion</th>
        <th>Estado</th>
        <th>Valor</th>
        <th>Entregas</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($contratos as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><strong><?= htmlspecialchars((string)$c['numero'], ENT_QUOTES, 'UTF-8') ?></strong></td>
          <td><?= htmlspecialchars((string)$c['entidad_contratante'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)$c['departamento'], ENT_QUOTES, 'UTF-8') ?><?= !empty($c['municipio']) ? ' / ' . htmlspecialchars((string)$c['municipio'], ENT_QUOTES, 'UTF-8') : '' ?></td>
          <td>
            <span class="estado-pill estado-<?= htmlspecialchars((string)$c['estado_contrato'], ENT_QUOTES, 'UTF-8') ?>">
              <?= strtoupper(htmlspecialchars((string)$c['estado_contrato'], ENT_QUOTES, 'UTF-8')) ?>
            </span>
          </td>
          <td>
            <div>$<?= number_format((float)$c['valor'], 0, ',', '.') ?></div>
            <small class="help-text">Ejecutado: $<?= number_format((float)$c['valor_ejecutado'], 0, ',', '.') ?></small>
          </td>
          <td><?= (int)$c['total_entregas'] ?></td>
          <td class="actions">
            <a href="/admin/contratos/edit.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm action-btn">Editar</a>
            <form method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar contrato #<?= (int)$c['id'] ?>? Esta acción no se puede deshacer.');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>" />
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
.estado-borrador { background: #607d8b; }
.estado-vigente { background: #4caf50; }
.estado-suspendido { background: #ff9800; }
.estado-finalizado { background: #3f51b5; }
.estado-cerrado { background: #212121; }

@media (max-width: 1000px) {
  .metrics-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
}
@media (max-width: 700px) {
  .metrics-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>