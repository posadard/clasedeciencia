<?php
require_once '../auth.php';
$page_title = 'Editar Entrega';

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

function sync_lote_stock(PDO $pdo, int $lote_id): void {
  try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad_asignada),0) AS a, COALESCE(SUM(cantidad_entregada),0) AS e FROM entrega_lotes WHERE lote_id = ?");
    $stmt->execute([$lote_id]);
    $tot = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['a' => 0, 'e' => 0];
    $stmtUp = $pdo->prepare("UPDATE lotes
                 SET cantidad_asignada = ?,
                   cantidad_entregada = ?,
                   cantidad_disponible = GREATEST(cantidad_total - ? - ?, 0)
                 WHERE id = ?");
    $stmtUp->execute([(int)$tot['a'], (int)$tot['e'], (int)$tot['a'], (int)$tot['e'], $lote_id]);
  } catch (Exception $e) {
    error_log('Sync lote stock error: ' . $e->getMessage());
  }
}

function validate_lote_assignment(PDO $pdo, int $entrega_id, int $lote_id, int $new_asignada, int $new_entregada): array {
  try {
    $stmt = $pdo->prepare("SELECT cantidad_total FROM lotes WHERE id = ? LIMIT 1");
    $stmt->execute([$lote_id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lote) {
      return ['ok' => false, 'msg' => 'El lote seleccionado no existe.'];
    }

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad_asignada),0) AS suma_asignada, COALESCE(SUM(cantidad_entregada),0) AS suma_entregada
                 FROM entrega_lotes WHERE lote_id = ?");
    $stmt->execute([$lote_id]);
    $totales = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['suma_asignada' => 0, 'suma_entregada' => 0];

    $stmt = $pdo->prepare("SELECT cantidad_asignada, cantidad_entregada FROM entrega_lotes WHERE entrega_id = ? AND lote_id = ? LIMIT 1");
    $stmt->execute([$entrega_id, $lote_id]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cantidad_asignada' => 0, 'cantidad_entregada' => 0];

    if ($new_entregada > $new_asignada) {
      return ['ok' => false, 'msg' => 'La cantidad entregada no puede ser mayor que la cantidad asignada para ese lote.'];
    }

    $new_sum_asignada = (int)$totales['suma_asignada'] - (int)$actual['cantidad_asignada'] + $new_asignada;
    $new_sum_entregada = (int)$totales['suma_entregada'] - (int)$actual['cantidad_entregada'] + $new_entregada;
    $total = (int)$lote['cantidad_total'];

    if (($new_sum_asignada + $new_sum_entregada) > $total) {
      return [
        'ok' => false,
        'msg' => 'La asignacion supera la capacidad del lote. Revisa cantidades asignadas/entregadas en otras entregas.'
      ];
    }

    return ['ok' => true, 'msg' => 'ok'];
  } catch (Exception $e) {
    error_log('Validate lote assignment error: ' . $e->getMessage());
    return ['ok' => false, 'msg' => 'No se pudo validar la asignacion de lote.'];
  }
}

if (!isset($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  } catch (Exception $e) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
  }
}

$edit_id = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : 0;
$estados_validos = ['programada', 'en_transito', 'entregada', 'rechazada', 'reprogramada'];
$flash_ok = isset($_GET['saved']) ? 'Entrega guardada correctamente.' : '';
$flash_error = '';

$contratos = [];
try {
  $contratos = $pdo->query("SELECT id, numero, entidad_contratante, departamento FROM contratos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $contratos = [];
}

$entrega_edit = [
  'id' => 0,
  'codigo_entrega' => '',
  'contrato_id' => '',
  'institucion_educativa' => '',
  'departamento' => '',
  'municipio' => '',
  'fecha_programada' => '',
  'fecha' => '',
  'estado_entrega' => 'programada',
  'responsable_entrega' => '',
  'responsable_recepcion' => '',
  'cantidad_kits' => 0,
  'recibido_ok' => 0,
  'acta_pdf' => '',
  'novedad' => ''
];

if ($edit_id > 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE id = ? LIMIT 1");
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $entrega_edit = array_merge($entrega_edit, $row);
    } else {
      $flash_error = 'No se encontró la entrega solicitada.';
      $edit_id = 0;
    }
  } catch (PDOException $e) {
    $flash_error = 'No se pudo cargar la entrega en edición.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [EntregasEdit] CSRF invalido");</script>';
  } else {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'assign_lote' || $action === 'update_lote' || $action === 'remove_lote') {
      $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
      $lote_id = isset($_POST['lote_id']) && ctype_digit((string)$_POST['lote_id']) ? (int)$_POST['lote_id'] : 0;
      if ($id <= 0 || $lote_id <= 0) {
        $flash_error = 'Entrega o lote invalido.';
      } else {
        try {
          if ($action === 'remove_lote') {
            $pdo->prepare("DELETE FROM entrega_lotes WHERE entrega_id = ? AND lote_id = ?")->execute([$id, $lote_id]);
            sync_lote_stock($pdo, $lote_id);
            admin_audit($pdo, 'entregas', 'entrega', $id, 'editar', [
              'accion_secundaria' => 'remove_lote',
              'lote_id' => $lote_id
            ]);
            $flash_ok = 'Asignación de lote eliminada.';
          } else {
            $cantidad_asignada = max(0, (int)($_POST['cantidad_asignada_lote'] ?? 0));
            $cantidad_entregada = max(0, (int)($_POST['cantidad_entregada_lote'] ?? 0));
            $obs_lote = trim($_POST['observaciones_lote'] ?? '');
            $valid = validate_lote_assignment($pdo, $id, $lote_id, $cantidad_asignada, $cantidad_entregada);
            if (!$valid['ok']) {
              throw new RuntimeException($valid['msg']);
            }

            if ($action === 'assign_lote') {
              $stmt = $pdo->prepare("INSERT INTO entrega_lotes (entrega_id, lote_id, cantidad_asignada, cantidad_entregada, observaciones)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE
                             cantidad_asignada = VALUES(cantidad_asignada),
                             cantidad_entregada = VALUES(cantidad_entregada),
                             observaciones = VALUES(observaciones)");
              $stmt->execute([$id, $lote_id, $cantidad_asignada, $cantidad_entregada, $obs_lote]);
            } else {
              $stmt = $pdo->prepare("UPDATE entrega_lotes
                           SET cantidad_asignada = ?, cantidad_entregada = ?, observaciones = ?
                           WHERE entrega_id = ? AND lote_id = ?");
              $stmt->execute([$cantidad_asignada, $cantidad_entregada, $obs_lote, $id, $lote_id]);
              if ($stmt->rowCount() <= 0) {
                throw new RuntimeException('No existe una asignación previa para ese lote en esta entrega.');
              }
            }

            sync_lote_stock($pdo, $lote_id);
            admin_audit($pdo, 'entregas', 'entrega', $id, 'editar', [
              'accion_secundaria' => $action,
              'lote_id' => $lote_id,
              'cantidad_asignada' => $cantidad_asignada,
              'cantidad_entregada' => $cantidad_entregada
            ]);
            $flash_ok = 'Asignación de lote guardada correctamente.';
          }
          $edit_id = $id;
        } catch (PDOException $e) {
          $flash_error = 'No se pudo guardar la asignación de lote.';
          echo '<script>console.log("❌ [EntregasEdit] Error lote:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        } catch (RuntimeException $e) {
          $flash_error = $e->getMessage();
          echo '<script>console.log("⚠️ [EntregasEdit] Validación lote:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        }
      }
    } else {
      $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
      $codigo_entrega = trim($_POST['codigo_entrega'] ?? '');
      $contrato_id = isset($_POST['contrato_id']) && ctype_digit((string)$_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;
      $institucion = trim($_POST['institucion_educativa'] ?? '');
      $dep = trim($_POST['departamento'] ?? '');
      $mun = trim($_POST['municipio'] ?? '');
      $fecha_programada = trim($_POST['fecha_programada'] ?? '');
      $fecha_entrega = trim($_POST['fecha'] ?? '');
      $estado_entrega = trim($_POST['estado_entrega'] ?? 'programada');
      $responsable_entrega = trim($_POST['responsable_entrega'] ?? '');
      $responsable_recepcion = trim($_POST['responsable_recepcion'] ?? '');
      $cantidad_kits = max(0, (int)($_POST['cantidad_kits'] ?? 0));
      $recibido_ok = isset($_POST['recibido_ok']) ? 1 : 0;
      $acta_pdf = trim($_POST['acta_pdf'] ?? '');
      $novedad = trim($_POST['novedad'] ?? '');

      $entrega_edit = array_merge($entrega_edit, [
        'id' => $id,
        'codigo_entrega' => $codigo_entrega,
        'contrato_id' => $contrato_id,
        'institucion_educativa' => $institucion,
        'departamento' => $dep,
        'municipio' => $mun,
        'fecha_programada' => $fecha_programada,
        'fecha' => $fecha_entrega,
        'estado_entrega' => $estado_entrega,
        'responsable_entrega' => $responsable_entrega,
        'responsable_recepcion' => $responsable_recepcion,
        'cantidad_kits' => $cantidad_kits,
        'recibido_ok' => $recibido_ok,
        'acta_pdf' => $acta_pdf,
        'novedad' => $novedad
      ]);

      if ($contrato_id <= 0 || $institucion === '') {
        $flash_error = 'Contrato e institución educativa son obligatorios.';
      } elseif (!in_array($estado_entrega, $estados_validos, true)) {
        $flash_error = 'Estado de entrega invalido.';
      } else {
        try {
          if ($codigo_entrega === '') {
            $codigo_entrega = $id > 0 ? ('ENT-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT)) : '';
          }

          if ($id > 0) {
            $sql = "UPDATE entregas
                    SET codigo_entrega = NULLIF(?, ''),
                        contrato_id = ?, institucion_educativa = ?, departamento = ?, municipio = ?,
                        fecha_programada = NULLIF(?, ''), fecha = NULLIF(?, ''), estado_entrega = ?,
                        responsable_entrega = ?, responsable_recepcion = ?, cantidad_kits = ?,
                        recibido_ok = ?, acta_pdf = NULLIF(?, ''), novedad = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              $codigo_entrega, $contrato_id, $institucion, $dep, $mun,
              $fecha_programada, $fecha_entrega, $estado_entrega,
              $responsable_entrega, $responsable_recepcion, $cantidad_kits,
              $recibido_ok, $acta_pdf, $novedad, $id
            ]);
            admin_audit($pdo, 'entregas', 'entrega', $id, 'editar', [
              'estado' => $estado_entrega,
              'cantidad_kits' => $cantidad_kits,
              'institucion' => $institucion
            ]);
            if ($codigo_entrega === '' || strpos($codigo_entrega, 'ENT-') !== 0) {
              $pdo->prepare("UPDATE entregas SET codigo_entrega = CONCAT('ENT-', LPAD(id, 6, '0')) WHERE id = ?")->execute([$id]);
            }
            header('Location: /admin/entregas/edit.php?id=' . (int)$id . '&saved=1');
            exit;
          }

          $sql = "INSERT INTO entregas
                  (codigo_entrega, contrato_id, institucion_educativa, departamento, municipio,
                   fecha_programada, fecha, estado_entrega, responsable_entrega, responsable_recepcion,
                   cantidad_kits, recibido_ok, acta_pdf, novedad)
                  VALUES
                  (NULLIF(?, ''), ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, ?, ?, NULLIF(?, ''), ?)";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([
            $codigo_entrega, $contrato_id, $institucion, $dep, $mun,
            $fecha_programada, $fecha_entrega, $estado_entrega,
            $responsable_entrega, $responsable_recepcion, $cantidad_kits,
            $recibido_ok, $acta_pdf, $novedad
          ]);

          $new_id = (int)$pdo->lastInsertId();
          admin_audit($pdo, 'entregas', 'entrega', $new_id, 'crear', [
            'estado' => $estado_entrega,
            'cantidad_kits' => $cantidad_kits,
            'institucion' => $institucion
          ]);
          $pdo->prepare("UPDATE entregas SET codigo_entrega = CONCAT('ENT-', LPAD(id, 6, '0')) WHERE id = ? AND (codigo_entrega IS NULL OR codigo_entrega = '')")
            ->execute([$new_id]);

          header('Location: /admin/entregas/edit.php?id=' . $new_id . '&saved=1');
          exit;
        } catch (PDOException $e) {
          $flash_error = 'Error al guardar entrega. Verifica codigo unico y datos obligatorios.';
          echo '<script>console.log("❌ [EntregasEdit] Error al guardar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
        }
      }
    }
  }
}

$lotes_disponibles = [];
$lotes_asignados = [];
if ($edit_id > 0) {
  try {
    $stmt = $pdo->prepare("SELECT l.id, l.codigo_lote, l.cantidad_disponible, l.estado_lote, k.nombre AS kit_nombre
                 FROM lotes l
                 JOIN kits k ON k.id = l.kit_id
                 ORDER BY l.estado_lote ASC, l.codigo_lote ASC");
    $stmt->execute();
    $lotes_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT el.*, l.codigo_lote, l.estado_lote, k.nombre AS kit_nombre
                 FROM entrega_lotes el
                 JOIN lotes l ON l.id = el.lote_id
                 JOIN kits k ON k.id = l.kit_id
                 WHERE el.entrega_id = ?
                 ORDER BY l.codigo_lote ASC");
    $stmt->execute([$edit_id]);
    $lotes_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $lotes_disponibles = [];
    $lotes_asignados = [];
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= $edit_id > 0 ? 'Editar entrega' : 'Nueva entrega' ?></h2>
  <span class="help-text">Formulario operativo de creación y edición de entregas.</span>
  <script>
    console.log('✅ [Admin] Entregas edit cargado');
    console.log('🔍 [Admin] Entrega ID:', <?= (int)$edit_id ?>);
  </script>
</div>

<?php if ($flash_ok): ?>
  <div class="message success"><?= htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
  <div class="message error"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
  <form method="POST" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?= (int)$entrega_edit['id'] ?>" />

    <div class="form-group">
      <label>Codigo entrega</label>
      <input type="text" name="codigo_entrega" maxlength="64" value="<?= htmlspecialchars((string)$entrega_edit['codigo_entrega'], ENT_QUOTES, 'UTF-8') ?>" placeholder="ENT-000001" />
    </div>
    <div class="form-group">
      <label>Contrato *</label>
      <select name="contrato_id" required>
        <option value="">Seleccionar...</option>
        <?php foreach ($contratos as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$entrega_edit['contrato_id'] === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$c['numero'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string)$c['entidad_contratante'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Institución educativa *</label>
      <input type="text" required maxlength="255" name="institucion_educativa" value="<?= htmlspecialchars((string)$entrega_edit['institucion_educativa'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Estado</label>
      <select name="estado_entrega">
        <?php foreach ($estados_validos as $ev): ?>
          <option value="<?= htmlspecialchars($ev, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)$entrega_edit['estado_entrega'] === $ev) ? 'selected' : '' ?>><?= strtoupper(htmlspecialchars($ev, ENT_QUOTES, 'UTF-8')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Departamento</label>
      <input type="text" maxlength="120" name="departamento" value="<?= htmlspecialchars((string)$entrega_edit['departamento'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Municipio</label>
      <input type="text" maxlength="120" name="municipio" value="<?= htmlspecialchars((string)$entrega_edit['municipio'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha programada</label>
      <input type="date" name="fecha_programada" value="<?= htmlspecialchars((string)$entrega_edit['fecha_programada'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Fecha entrega</label>
      <input type="date" name="fecha" value="<?= htmlspecialchars((string)$entrega_edit['fecha'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>

    <div class="form-group">
      <label>Responsable entrega</label>
      <input type="text" maxlength="180" name="responsable_entrega" value="<?= htmlspecialchars((string)$entrega_edit['responsable_entrega'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Responsable recepción</label>
      <input type="text" maxlength="180" name="responsable_recepcion" value="<?= htmlspecialchars((string)$entrega_edit['responsable_recepcion'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="form-group">
      <label>Cantidad kits</label>
      <input type="number" min="0" name="cantidad_kits" value="<?= (int)$entrega_edit['cantidad_kits'] ?>" />
    </div>
    <div class="form-group">
      <label>Acta (ruta/PDF)</label>
      <input type="text" maxlength="255" name="acta_pdf" value="<?= htmlspecialchars((string)$entrega_edit['acta_pdf'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>

    <div class="form-group full-width">
      <label><input type="checkbox" name="recibido_ok" value="1" <?= ((int)$entrega_edit['recibido_ok'] === 1) ? 'checked' : '' ?> /> Recibido conforme</label>
    </div>
    <div class="form-group full-width">
      <label>Novedad / observaciones</label>
      <textarea name="novedad" rows="3"><?= htmlspecialchars((string)$entrega_edit['novedad'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="form-actions full-width">
      <button type="submit" class="btn"><?= $edit_id > 0 ? 'Guardar cambios' : 'Crear entrega' ?></button>
      <a href="/admin/entregas/index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
  </form>
</div>

<?php if ($edit_id > 0): ?>
<div class="card" style="margin-bottom:1rem;">
  <h3>Asignación de lotes a la entrega #<?= (int)$edit_id ?></h3>
  <form method="POST" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="action" value="assign_lote" />
    <input type="hidden" name="id" value="<?= (int)$edit_id ?>" />

    <div class="form-group">
      <label>Lote</label>
      <select name="lote_id" required>
        <option value="">Seleccionar lote...</option>
        <?php foreach ($lotes_disponibles as $ld): ?>
          <option value="<?= (int)$ld['id'] ?>">
            <?= htmlspecialchars((string)$ld['codigo_lote'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string)$ld['kit_nombre'], ENT_QUOTES, 'UTF-8') ?> (disp: <?= (int)$ld['cantidad_disponible'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Cantidad asignada</label>
      <input type="number" min="0" name="cantidad_asignada_lote" value="0" />
    </div>
    <div class="form-group">
      <label>Cantidad entregada</label>
      <input type="number" min="0" name="cantidad_entregada_lote" value="0" />
    </div>
    <div class="form-group">
      <label>Observaciones</label>
      <input type="text" name="observaciones_lote" maxlength="255" value="" />
    </div>
    <div class="form-actions full-width">
      <button type="submit" class="btn">Guardar asignación</button>
    </div>
  </form>

  <?php if (!empty($lotes_asignados)): ?>
    <table class="data-table" style="margin-top:1rem;">
      <thead>
        <tr>
          <th>Lote</th>
          <th>Kit</th>
          <th>Asignada</th>
          <th>Entregada</th>
          <th>Observaciones</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lotes_asignados as $la): ?>
          <tr>
            <td><?= htmlspecialchars((string)$la['codigo_lote'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)$la['kit_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <form method="POST" class="inline-form lote-inline-edit">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
                <input type="hidden" name="action" value="update_lote" />
                <input type="hidden" name="id" value="<?= (int)$edit_id ?>" />
                <input type="hidden" name="lote_id" value="<?= (int)$la['lote_id'] ?>" />
                <input type="number" min="0" name="cantidad_asignada_lote" value="<?= (int)$la['cantidad_asignada'] ?>" />
            </td>
            <td>
                <input type="number" min="0" name="cantidad_entregada_lote" value="<?= (int)$la['cantidad_entregada'] ?>" />
            </td>
            <td>
                <input type="text" maxlength="255" name="observaciones_lote" value="<?= htmlspecialchars((string)($la['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
            </td>
            <td>
                <button type="submit" class="btn btn-sm">Guardar</button>
              </form>
              <form method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar asignación de lote?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
                <input type="hidden" name="action" value="remove_lote" />
                <input type="hidden" name="id" value="<?= (int)$edit_id ?>" />
                <input type="hidden" name="lote_id" value="<?= (int)$la['lote_id'] ?>" />
                <button type="submit" class="btn btn-sm btn-danger">Quitar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="help-text" style="margin-top:0.75rem;">Esta entrega aún no tiene lotes asignados.</p>
  <?php endif; ?>
</div>
<?php endif; ?>

<style>
.admin-form-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group input, .form-group select, .form-group textarea { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.full-width { grid-column: 1 / -1; }
.form-actions { display: flex; gap: 0.5rem; }

.inline-form { display: inline-block; margin: 0; }
.lote-inline-edit { display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap; }
.lote-inline-edit input[type="number"] { width: 90px; }
.lote-inline-edit input[type="text"] { width: 220px; }

@media (max-width: 1000px) {
  .admin-form-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
}
@media (max-width: 700px) {
  .admin-form-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>