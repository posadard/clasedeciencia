<?php
require_once '../auth.php';

$is_edit    = isset($_GET['id']) && ctype_digit($_GET['id']);
$id         = $is_edit ? (int)$_GET['id'] : null;
$pre_grupo  = isset($_GET['grupo_id']) && ctype_digit($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : null;
$page_title = $is_edit ? 'Editar Enlace' : 'Nuevo Enlace';

$enlace = [
    'grupo_id' => $pre_grupo ?? 0,
    'etiqueta' => '',
    'url'      => '',
    'externo'  => 0,
    'orden'    => 0,
    'activo'   => 1,
];

if ($is_edit) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM footer_enlaces WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $enlace = $row;
        } else {
            header('Location: /admin/footer/index.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Admin footer/enlace-edit GET: ' . $e->getMessage());
    }
}

// Cargar grupos para el select
$grupos = [];
try {
    $grupos = $pdo->query('SELECT id, titulo FROM footer_grupos ORDER BY orden ASC, id ASC')
                  ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Admin footer/enlace-edit grupos: ' . $e->getMessage());
}

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grupo_id = (int)($_POST['grupo_id'] ?? 0);
    $etiqueta = trim($_POST['etiqueta'] ?? '');
    $url      = trim($_POST['url'] ?? '');
    $externo  = isset($_POST['externo']) ? 1 : 0;
    $orden    = (int)($_POST['orden'] ?? 0);
    $activo   = isset($_POST['activo']) ? 1 : 0;

    if ($etiqueta === '' || $url === '' || $grupo_id === 0) {
        $msg = 'Grupo, etiqueta y URL son obligatorios.';
        $msg_type = 'error';
    } else {
        try {
            if ($is_edit) {
                $pdo->prepare(
                    'UPDATE footer_enlaces
                     SET grupo_id = ?, etiqueta = ?, url = ?, externo = ?, orden = ?, activo = ?
                     WHERE id = ?'
                )->execute([$grupo_id, $etiqueta, $url, $externo, $orden, $activo, $id]);
                $msg = '✅ Enlace actualizado.';
                $msg_type = 'ok';
                // Recargar
                $stmt = $pdo->prepare('SELECT * FROM footer_enlaces WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                $enlace = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $pdo->prepare(
                    'INSERT INTO footer_enlaces (grupo_id, etiqueta, url, externo, orden, activo)
                     VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([$grupo_id, $etiqueta, $url, $externo, $orden, $activo]);
                header('Location: /admin/footer/index.php?msg=' . urlencode('✅ Enlace creado.') . '&type=ok');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Admin footer/enlace-edit POST: ' . $e->getMessage());
            $msg = '❌ Error: ' . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

include '../header.php';
?>
<div class="page-header">
    <h2><?= h($page_title) ?></h2>
    <a href="/admin/footer/index.php" class="btn btn-secondary">← Volver</a>
</div>

<?php if ($msg): ?>
<div class="alert" style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:6px;background:<?= $msg_type === 'ok' ? '#d4edda' : '#f8d7da' ?>;color:<?= $msg_type === 'ok' ? '#155724' : '#721c24' ?>;">
    <?= h($msg) ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label for="grupo_id">Grupo (columna del footer) <span style="color:red">*</span></label>
            <select id="grupo_id" name="grupo_id" class="form-control" required>
                <option value="">— Selecciona un grupo —</option>
                <?php foreach ($grupos as $g): ?>
                <option value="<?= (int)$g['id'] ?>"
                    <?= ((int)$enlace['grupo_id'] === (int)$g['id']) ? 'selected' : '' ?>>
                    <?= h($g['titulo']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="etiqueta">Texto del enlace <span style="color:red">*</span></label>
            <input type="text" id="etiqueta" name="etiqueta" class="form-control"
                   value="<?= h($enlace['etiqueta']) ?>" required maxlength="120"
                   placeholder="Ej: Ministerio de Educación (Colombia)">
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="url">URL <span style="color:red">*</span></label>
            <input type="text" id="url" name="url" class="form-control"
                   value="<?= h($enlace['url']) ?>" required maxlength="512"
                   placeholder="Ej: /clases  o  https://www.mineducacion.gov.co/">
            <small style="color:#666;">Links internos: usar ruta relativa <code>/pagina</code>. Links externos: URL completa con <code>https://</code>.</small>
        </div>

        <div class="form-group" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:1.5rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="externo" name="externo" value="1"
                       <?= $enlace['externo'] ? 'checked' : '' ?>>
                <label for="externo" style="margin:0;font-weight:400;">
                    Enlace externo
                    <small style="color:#666;">(agrega <code>target="_blank" rel="noopener"</code>)</small>
                </label>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="activo" name="activo" value="1"
                       <?= $enlace['activo'] ? 'checked' : '' ?>>
                <label for="activo" style="margin:0;font-weight:400;">Enlace activo</label>
            </div>
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="orden">Orden dentro del grupo (menor = antes)</label>
            <input type="number" id="orden" name="orden" class="form-control"
                   value="<?= (int)$enlace['orden'] ?>" min="0" max="99" style="max-width:120px;">
        </div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="btn">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-save"/></svg>
                <?= $is_edit ? 'Guardar cambios' : 'Crear enlace' ?>
            </button>
        </div>
    </form>
</div>

<?php include '../footer.php'; ?>
