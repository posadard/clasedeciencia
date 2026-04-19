<?php
require_once '../auth.php';
$page_title = 'Editar Contrato';

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

$estados_validos = ['borrador', 'vigente', 'suspendido', 'finalizado', 'cerrado'];
$edit_id = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : 0;
$flash_ok = isset($_GET['saved']) ? 'Contrato guardado correctamente.' : '';
$flash_error = '';

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
      $flash_error = 'No se encontró el contrato solicitado.';
      $edit_id = 0;
    }
  } catch (PDOException $e) {
    $flash_error = 'No se pudo cargar el contrato para edición.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $flash_error = 'Token CSRF invalido.';
    echo '<script>console.log("❌ [ContratosEdit] CSRF invalido");</script>';
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

    $contrato_edit = array_merge($contrato_edit, [
      'id' => $id,
      'numero' => $numero,
      'entidad_contratante' => $entidad,
      'departamento' => $dep,
      'municipio' => $mun,
      'fecha' => $fecha,
      'fecha_inicio' => $fecha_inicio,
      'fecha_fin' => $fecha_fin,
      'valor' => (string)$valor,
      'valor_ejecutado' => (string)$valor_ejecutado,
      'estado_contrato' => $estado_contrato,
      'supervisor' => $supervisor,
      'objeto_contrato' => $objeto,
      'contrato_pdf' => $contrato_pdf,
      'observaciones' => $observaciones
    ]);

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
          header('Location: /admin/contratos/edit.php?id=' . (int)$id . '&saved=1');
          exit;
        }

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
        header('Location: /admin/contratos/edit.php?id=' . $new_id . '&saved=1');
        exit;
      } catch (PDOException $e) {
        $flash_error = 'Error al guardar contrato. Verifica numero unico y formato de datos.';
        echo '<script>console.log("❌ [ContratosEdit] Error al guardar:", ' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) . ');</script>';
      }
    }
  }
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= $edit_id > 0 ? 'Editar contrato' : 'Nuevo contrato' ?></h2>
  <span class="help-text">Formulario de creación y edición de contratos CTeI.</span>
  <script>
    console.log('✅ [Admin] Contratos edit cargado');
    console.log('🔍 [Admin] Contrato ID:', <?= (int)$edit_id ?>);
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
      <a href="/admin/contratos/index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
  </form>
</div>

<style>
.admin-form-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group input, .form-group select, .form-group textarea { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.full-width { grid-column: 1 / -1; }
.form-actions { display: flex; gap: 0.5rem; }

@media (max-width: 1000px) {
  .admin-form-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
}
@media (max-width: 700px) {
  .admin-form-grid { grid-template-columns: 1fr; }
}
</style>
<?php include '../footer.php'; ?>