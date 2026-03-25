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

$edit_id = isset($_GET['edit']) && ctype_digit((string)$_GET['edit']) ? (int)$_GET['edit'] : 0;
$search = trim($_GET['search'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$departamento = trim($_GET['departamento'] ?? '');

$estados_validos = ['borrador', 'vigente', 'suspendido', 'finalizado', 'cerrado'];
$flash_ok = '';
$flash_error = '';

// Cargar contrato en edicion
$contrato_edit = [
    'id' => 0,
    'numero' => '',
    'entidad_contratante' => '',
    'departamento' => '',
    'municipio' => '',
    'fecha' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'valor' => '0.00',
    'valor_ejecutado' => '0.00',
    'estado_contrato' => 'borrador',
    'supervisor' => '',
    'objeto_contrato' => '',
    'contrato_pdf' => '',
    'observaciones' => ''
];

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contratos WHERE id = ? LIMIT 1");
        $stmt->execute([$edit_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $contrato_edit = array_merge($contrato_edit, $row);
        } else {
            $edit_id = 0;
        }
    } catch (PDOException $e) {
        $flash_error = 'No se pudo cargar el contrato para edicion.';
    }
}

// Guardar / eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $flash_error = 'Token CSRF invalido.';
        echo '<script>console.log("❌ [Contratos] CSRF invalido");</script>';
    } else {
        $action = $_POST['action'] ?? 'save';

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
                    if ($edit_id === $id) {
                        $edit_id = 0;
                    }
                } catch (PDOException $e) {
                    $flash_error = 'No se pudo eliminar el contrato. Puede tener entregas asociadas.';
                    echo '<script>console.log("❌ [Contratos] Error al eliminar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
                }
            }
        } else {
            $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
            $numero = trim($_POST['numero'] ?? '');
            $entidad = trim($_POST['entidad_contratante'] ?? '');
            $dep = trim($_POST['departamento'] ?? '');
            $mun = trim($_POST['municipio'] ?? '');
            $fecha = trim($_POST['fecha'] ?? '');
            $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
            $fecha_fin = trim($_POST['fecha_fin'] ?? '');
            $valor = (float)($_POST['valor'] ?? 0);
            $valor_ejecutado = (float)($_POST['valor_ejecutado'] ?? 0);
            $estado_contrato = trim($_POST['estado_contrato'] ?? 'borrador');
            $supervisor = trim($_POST['supervisor'] ?? '');
            $objeto = trim($_POST['objeto_contrato'] ?? '');
            $contrato_pdf = trim($_POST['contrato_pdf'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($numero === '' || $entidad === '' || $dep === '') {
                $flash_error = 'Numero, entidad y departamento son obligatorios.';
            } elseif (!in_array($estado_contrato, $estados_validos, true)) {
                $flash_error = 'Estado de contrato invalido.';
            } else {
                try {
                    if ($id > 0) {
                        $sql = "UPDATE contratos
                                SET numero = ?, entidad_contratante = ?, departamento = ?, municipio = ?,
                                    fecha = NULLIF(?, ''), fecha_inicio = NULLIF(?, ''), fecha_fin = NULLIF(?, ''),
                                    valor = ?, valor_ejecutado = ?, estado_contrato = ?, supervisor = ?,
                                    objeto_contrato = ?, contrato_pdf = ?, observaciones = ?
                                WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $numero, $entidad, $dep, $mun, $fecha, $fecha_inicio, $fecha_fin,
                            $valor, $valor_ejecutado, $estado_contrato, $supervisor,
                            $objeto, $contrato_pdf, $observaciones, $id
                        ]);
                        admin_audit($pdo, 'contratos', 'contrato', $id, 'editar', [
                          'numero' => $numero,
                          'estado' => $estado_contrato,
                          'valor' => $valor,
                          'valor_ejecutado' => $valor_ejecutado
                        ]);
                        $flash_ok = 'Contrato actualizado correctamente.';
                        echo '<script>console.log("✅ [Contratos] Contrato actualizado:", ' . (int)$id . ');</script>';
                        $edit_id = $id;
                    } else {
                        $sql = "INSERT INTO contratos
                                (numero, entidad_contratante, departamento, municipio, fecha, fecha_inicio, fecha_fin,
                                 valor, valor_ejecutado, estado_contrato, supervisor, objeto_contrato, contrato_pdf, observaciones)
                                VALUES
                                (?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $numero, $entidad, $dep, $mun, $fecha, $fecha_inicio, $fecha_fin,
                            $valor, $valor_ejecutado, $estado_contrato, $supervisor,
                            $objeto, $contrato_pdf, $observaciones
                        ]);
                        $new_id = (int)$pdo->lastInsertId();
                        admin_audit($pdo, 'contratos', 'contrato', $new_id, 'crear', [
                          'numero' => $numero,
                          'estado' => $estado_contrato,
                          'valor' => $valor
                        ]);
                        $flash_ok = 'Contrato creado correctamente.';
                        echo '<script>console.log("✅ [Contratos] Contrato creado:", ' . (int)$new_id . ');</script>';
                        $edit_id = $new_id;
                    }

                    if ($edit_id > 0) {
                        $stmt = $pdo->prepare("SELECT * FROM contratos WHERE id = ? LIMIT 1");
                        $stmt->execute([$edit_id]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $contrato_edit = array_merge($contrato_edit, $row);
                        }
                    }
                } catch (PDOException $e) {
                    $flash_error = 'Error al guardar contrato. Verifica numero unico y formato de datos.';
                    echo '<script>console.log("❌ [Contratos] Error al guardar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
                }
            }
        }
    }
}

// Listado
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

include '../header.php';
?>
<div class="page-header">
  <h2>Contratos CTeI</h2>
  <span class="help-text">Gestión de contratos, estado de ejecución y trazabilidad administrativa.</span>
  <script>
    console.log('✅ [Admin] Contratos index cargado');
    console.log('🔍 [Admin] Total contratos:', <?= count($contratos) ?>);
    console.log('🔍 [Admin] Editando contrato ID:', <?= (int)$edit_id ?>);
  </script>
</div>

<?php if ($flash_ok): ?>
  <div class="message success"><?= htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
  <div class="message error"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

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

<div class="card" style="margin-bottom:1rem;">
  <h3><?= $edit_id > 0 ? 'Editar contrato' : 'Nuevo contrato' ?></h3>
  <form method="POST" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?= (int)$contrato_edit['id'] ?>" />

    <div class="form-group">
      <label>Numero contrato *</label>
      <input type="text" name="numero" required maxlength="64" value="<?= htmlspecialchars((string)$contrato_edit['numero'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Entidad contratante *</label>
      <input type="text" name="entidad_contratante" required maxlength="255" value="<?= htmlspecialchars((string)$contrato_edit['entidad_contratante'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Departamento *</label>
      <input type="text" name="departamento" required maxlength="120" value="<?= htmlspecialchars((string)$contrato_edit['departamento'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Municipio</label>
      <input type="text" name="municipio" maxlength="120" value="<?= htmlspecialchars((string)$contrato_edit['municipio'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>

    <div class="form-group">
      <label>Fecha contrato</label>
      <input type="date" name="fecha" value="<?= htmlspecialchars((string)$contrato_edit['fecha'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha inicio</label>
      <input type="date" name="fecha_inicio" value="<?= htmlspecialchars((string)$contrato_edit['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha fin</label>
      <input type="date" name="fecha_fin" value="<?= htmlspecialchars((string)$contrato_edit['fecha_fin'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Estado</label>
      <select name="estado_contrato">
        <?php foreach ($estados_validos as $ev): ?>
          <option value="<?= htmlspecialchars($ev, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)$contrato_edit['estado_contrato'] === $ev) ? 'selected' : '' ?>><?= strtoupper(htmlspecialchars($ev, ENT_QUOTES, 'UTF-8')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Valor total</label>
      <input type="number" step="0.01" min="0" name="valor" value="<?= htmlspecialchars((string)$contrato_edit['valor'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Valor ejecutado</label>
      <input type="number" step="0.01" min="0" name="valor_ejecutado" value="<?= htmlspecialchars((string)$contrato_edit['valor_ejecutado'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Supervisor</label>
      <input type="text" maxlength="180" name="supervisor" value="<?= htmlspecialchars((string)$contrato_edit['supervisor'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>PDF contrato (URL o ruta)</label>
      <input type="text" maxlength="255" name="contrato_pdf" value="<?= htmlspecialchars((string)$contrato_edit['contrato_pdf'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>

    <div class="form-group full-width">
      <label>Objeto del contrato</label>
      <textarea name="objeto_contrato" rows="3"><?= htmlspecialchars((string)$contrato_edit['objeto_contrato'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <div class="form-group full-width">
      <label>Observaciones</label>
      <textarea name="observaciones" rows="3"><?= htmlspecialchars((string)$contrato_edit['observaciones'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="form-actions full-width">
      <button type="submit" class="btn"><?= $edit_id > 0 ? 'Guardar cambios' : 'Crear contrato' ?></button>
      <?php if ($edit_id > 0): ?>
        <a href="/admin/contratos/index.php" class="btn btn-secondary">Cancelar edicion</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado de contratos</h3>
  <span class="help-text">Total: <?= count($contratos) ?></span>
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
            <a href="/admin/contratos/index.php?edit=<?= (int)$c['id'] ?>" class="btn btn-sm action-btn">Editar</a>
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
.filters-bar { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
.filters-form { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.35rem; }
.filter-group input, .filter-group select { min-width: 180px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.search-group { flex: 1; }
.search-group input { min-width: 260px; }
.actions-inline { flex-direction: row; gap: 0.5rem; align-items: center; }

.admin-form-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group input, .form-group select, .form-group textarea { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.full-width { grid-column: 1 / -1; }
.form-actions { display: flex; gap: 0.5rem; }

.inline-form { display: inline-block; margin: 0; }
.actions { white-space: nowrap; }
.estado-pill { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; color: #fff; }
.estado-borrador { background: #607d8b; }
.estado-vigente { background: #4caf50; }
.estado-suspendido { background: #ff9800; }
.estado-finalizado { background: #3f51b5; }
.estado-cerrado { background: #212121; }

@media (max-width: 1000px) {
  .admin-form-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
}
@media (max-width: 700px) {
  .admin-form-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>
