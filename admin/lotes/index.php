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

$edit_id = isset($_GET['edit']) && ctype_digit((string)$_GET['edit']) ? (int)$_GET['edit'] : 0;
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

$lote_edit = [
    'id' => 0,
    'codigo_lote' => '',
    'kit_id' => '',
    'contrato_id' => '',
    'cantidad_total' => 0,
    'cantidad_disponible' => 0,
    'cantidad_asignada' => 0,
    'cantidad_entregada' => 0,
    'fecha_fabricacion' => '',
    'fecha_caducidad' => '',
    'estado_lote' => 'activo',
    'ubicacion' => '',
    'observaciones' => ''
];

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lotes WHERE id = ? LIMIT 1");
        $stmt->execute([$edit_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $lote_edit = array_merge($lote_edit, $row);
        } else {
            $edit_id = 0;
        }
    } catch (PDOException $e) {
        $flash_error = 'No se pudo cargar el lote en edicion.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $flash_error = 'Token CSRF invalido.';
        echo '<script>console.log("❌ [Lotes] CSRF invalido");</script>';
    } else {
        $action = $_POST['action'] ?? 'save';

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
                    if ($edit_id === $id) {
                        $edit_id = 0;
                    }
                } catch (PDOException $e) {
                    $flash_error = 'No se pudo eliminar el lote. Puede tener entregas asociadas.';
                    echo '<script>console.log("❌ [Lotes] Error al eliminar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
                }
            }
        } else {
            $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
            $codigo_lote = trim($_POST['codigo_lote'] ?? '');
            $kit_id = isset($_POST['kit_id']) && ctype_digit((string)$_POST['kit_id']) ? (int)$_POST['kit_id'] : 0;
            $contrato_id = isset($_POST['contrato_id']) && ctype_digit((string)$_POST['contrato_id']) ? (int)$_POST['contrato_id'] : null;
            $cantidad_total = max(0, (int)($_POST['cantidad_total'] ?? 0));
            $cantidad_disponible = max(0, (int)($_POST['cantidad_disponible'] ?? 0));
            $cantidad_asignada = max(0, (int)($_POST['cantidad_asignada'] ?? 0));
            $cantidad_entregada = max(0, (int)($_POST['cantidad_entregada'] ?? 0));
            $fecha_fabricacion = trim($_POST['fecha_fabricacion'] ?? '');
            $fecha_caducidad = trim($_POST['fecha_caducidad'] ?? '');
            $estado_lote = trim($_POST['estado_lote'] ?? 'activo');
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($codigo_lote === '' || $kit_id <= 0) {
                $flash_error = 'Codigo de lote y kit son obligatorios.';
            } elseif (!in_array($estado_lote, $estados_validos, true)) {
                $flash_error = 'Estado de lote invalido.';
            } elseif (($cantidad_disponible + $cantidad_asignada + $cantidad_entregada) > $cantidad_total && $cantidad_total > 0) {
                $flash_error = 'La suma disponible + asignada + entregada no puede superar la cantidad total.';
            } else {
                try {
                    if ($id > 0) {
                        $sql = "UPDATE lotes
                                SET codigo_lote = ?, kit_id = ?, contrato_id = ?,
                                    cantidad_total = ?, cantidad_disponible = ?, cantidad_asignada = ?, cantidad_entregada = ?,
                                    fecha_fabricacion = NULLIF(?, ''), fecha_caducidad = NULLIF(?, ''),
                                    estado_lote = ?, ubicacion = ?, observaciones = ?
                                WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $codigo_lote, $kit_id, $contrato_id,
                            $cantidad_total, $cantidad_disponible, $cantidad_asignada, $cantidad_entregada,
                            $fecha_fabricacion, $fecha_caducidad,
                            $estado_lote, $ubicacion, $observaciones,
                            $id
                        ]);
                        admin_audit($pdo, 'lotes', 'lote', $id, 'editar', [
                          'codigo_lote' => $codigo_lote,
                          'estado' => $estado_lote,
                          'cantidad_total' => $cantidad_total,
                          'cantidad_disponible' => $cantidad_disponible
                        ]);
                        $edit_id = $id;
                        $flash_ok = 'Lote actualizado correctamente.';
                        echo '<script>console.log("✅ [Lotes] Lote actualizado:", ' . (int)$id . ');</script>';
                    } else {
                        $sql = "INSERT INTO lotes
                                (codigo_lote, kit_id, contrato_id, cantidad_total, cantidad_disponible, cantidad_asignada, cantidad_entregada,
                                 fecha_fabricacion, fecha_caducidad, estado_lote, ubicacion, observaciones)
                                VALUES
                                (?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $codigo_lote, $kit_id, $contrato_id,
                            $cantidad_total, $cantidad_disponible, $cantidad_asignada, $cantidad_entregada,
                            $fecha_fabricacion, $fecha_caducidad,
                            $estado_lote, $ubicacion, $observaciones
                        ]);
                        $edit_id = (int)$pdo->lastInsertId();
                        admin_audit($pdo, 'lotes', 'lote', $edit_id, 'crear', [
                          'codigo_lote' => $codigo_lote,
                          'estado' => $estado_lote,
                          'cantidad_total' => $cantidad_total
                        ]);
                        $flash_ok = 'Lote creado correctamente.';
                        echo '<script>console.log("✅ [Lotes] Lote creado:", ' . (int)$edit_id . ');</script>';
                    }

                    $stmt = $pdo->prepare("SELECT * FROM lotes WHERE id = ? LIMIT 1");
                    $stmt->execute([$edit_id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $lote_edit = array_merge($lote_edit, $row);
                    }
                } catch (PDOException $e) {
                    $flash_error = 'Error al guardar lote. Verifica codigo unico y relaciones.';
                    echo '<script>console.log("❌ [Lotes] Error al guardar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
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

include '../header.php';
?>
<div class="page-header">
  <h2>Lotes de Kits</h2>
  <span class="help-text">Gestión de inventario por lote, kit y contrato.</span>
  <script>
    console.log('✅ [Admin] Lotes index cargado');
    console.log('🔍 [Admin] Total lotes:', <?= count($lotes) ?>);
    console.log('🔍 [Admin] Editando lote ID:', <?= (int)$edit_id ?>);
  </script>
</div>

<div class="card" style="margin-bottom:1rem;">
  <h3>Asistente IA Administrativo - Lotes</h3>
  <p class="help-text">Consulta stock crítico, disponibilidad y riesgos operativos por lote con contexto en tiempo real.</p>
  <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:280px;">
      <label for="ia-admin-pregunta-lotes">Pregunta</label>
      <textarea id="ia-admin-pregunta-lotes" rows="2" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;" placeholder="Ej: ¿Qué lotes tienen riesgo de quiebre esta semana?"></textarea>
    </div>
    <button type="button" id="ia-admin-btn-lotes" class="btn">Consultar IA</button>
  </div>
  <div id="ia-admin-respuesta-lotes" style="margin-top:0.75rem;padding:0.75rem;border:1px solid #ddd;border-radius:6px;background:#fafafa;white-space:pre-wrap;min-height:48px;">Escribe una pregunta y presiona Consultar IA.</div>
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

<div class="card" style="margin-bottom:1rem;">
  <h3><?= $edit_id > 0 ? 'Editar lote' : 'Nuevo lote' ?></h3>
  <form method="POST" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?= (int)$lote_edit['id'] ?>" />

    <div class="form-group">
      <label>Codigo lote *</label>
      <input type="text" maxlength="64" required name="codigo_lote" value="<?= htmlspecialchars((string)$lote_edit['codigo_lote'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Kit *</label>
      <select name="kit_id" required>
        <option value="">Seleccionar...</option>
        <?php foreach ($kits as $k): ?>
          <option value="<?= (int)$k['id'] ?>" <?= (int)$lote_edit['kit_id'] === (int)$k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$k['nombre'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Contrato (opcional)</label>
      <select name="contrato_id">
        <option value="">Sin contrato</option>
        <?php foreach ($contratos as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$lote_edit['contrato_id'] === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$c['numero'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Estado</label>
      <select name="estado_lote">
        <?php foreach ($estados_validos as $ev): ?>
          <option value="<?= htmlspecialchars($ev, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)$lote_edit['estado_lote'] === $ev) ? 'selected' : '' ?>><?= strtoupper(htmlspecialchars($ev, ENT_QUOTES, 'UTF-8')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Cantidad total</label>
      <input type="number" min="0" name="cantidad_total" value="<?= (int)$lote_edit['cantidad_total'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad disponible</label>
      <input type="number" min="0" name="cantidad_disponible" value="<?= (int)$lote_edit['cantidad_disponible'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad asignada</label>
      <input type="number" min="0" name="cantidad_asignada" value="<?= (int)$lote_edit['cantidad_asignada'] ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad entregada</label>
      <input type="number" min="0" name="cantidad_entregada" value="<?= (int)$lote_edit['cantidad_entregada'] ?>" />
    </div>

    <div class="form-group">
      <label>Fecha fabricación</label>
      <input type="date" name="fecha_fabricacion" value="<?= htmlspecialchars((string)$lote_edit['fecha_fabricacion'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha caducidad</label>
      <input type="date" name="fecha_caducidad" value="<?= htmlspecialchars((string)$lote_edit['fecha_caducidad'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group full-width">
      <label>Ubicación</label>
      <input type="text" maxlength="180" name="ubicacion" value="<?= htmlspecialchars((string)$lote_edit['ubicacion'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group full-width">
      <label>Observaciones</label>
      <textarea name="observaciones" rows="3"><?= htmlspecialchars((string)$lote_edit['observaciones'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="form-actions full-width">
      <button type="submit" class="btn"><?= $edit_id > 0 ? 'Guardar cambios' : 'Crear lote' ?></button>
      <?php if ($edit_id > 0): ?>
        <a href="/admin/lotes/index.php" class="btn btn-secondary">Cancelar edicion</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
  <h3 style="margin:0;">Listado de lotes</h3>
  <span class="help-text">Total: <?= count($lotes) ?></span>
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
            <a href="/admin/lotes/index.php?edit=<?= (int)$l['id'] ?>" class="btn btn-sm action-btn">Editar</a>
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
.estado-activo { background: #4caf50; }
.estado-bloqueado { background: #ff9800; }
.estado-agotado { background: #d32f2f; }
.estado-cerrado { background: #424242; }

@media (max-width: 1000px) {
  .admin-form-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
}
@media (max-width: 700px) {
  .admin-form-grid { grid-template-columns: 1fr; }
}
</style>
<script>
(function(){
  const btn = document.getElementById('ia-admin-btn-lotes');
  const input = document.getElementById('ia-admin-pregunta-lotes');
  const out = document.getElementById('ia-admin-respuesta-lotes');
  if (!btn || !input || !out) return;

  btn.addEventListener('click', async function(){
    const pregunta = (input.value || '').trim();
    if (!pregunta) {
      out.textContent = 'Escribe una pregunta antes de consultar.';
      return;
    }

    btn.disabled = true;
    out.textContent = 'Consultando...';
    console.log('🔍 [IA Admin Lotes] Consulta enviada');
    try {
      const payload = {
        instancia: 'backend',
        contexto_pagina: 'lotes',
        pregunta: pregunta
      };

      const resp = await fetch('/api/ia-consulta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await resp.json();
      if (!resp.ok || !data.ok) {
        out.textContent = (data && (data.error || data.respuesta)) ? (data.error || data.respuesta) : 'No fue posible obtener respuesta.';
        console.log('❌ [IA Admin Lotes] Error:', data);
      } else {
        out.textContent = data.respuesta || 'Sin respuesta.';
        console.log('✅ [IA Admin Lotes] Respuesta recibida');
      }
    } catch (err) {
      out.textContent = 'Error de red o servidor al consultar IA.';
      console.log('❌ [IA Admin Lotes] Excepción:', err && err.message);
    } finally {
      btn.disabled = false;
    }
  });
})();
</script>
<?php include '../footer.php'; ?>
