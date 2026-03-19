<?php
require_once '../auth.php';

$is_edit = isset($_GET['id']) && ctype_digit($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : null;
$page_title = $is_edit ? 'Editar Grupo de Footer' : 'Nuevo Grupo de Footer';

$grupo = ['titulo' => '', 'orden' => 0, 'activo' => 1];

if ($is_edit) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM footer_grupos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $grupo = $row;
        } else {
            header('Location: /admin/footer/index.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Admin footer/grupo-edit GET: ' . $e->getMessage());
    }
}

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $orden  = (int)($_POST['orden'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($titulo === '') {
        $msg = 'El título es obligatorio.';
        $msg_type = 'error';
    } else {
        try {
            if ($is_edit) {
                $pdo->prepare('UPDATE footer_grupos SET titulo = ?, orden = ?, activo = ? WHERE id = ?')
                    ->execute([$titulo, $orden, $activo, $id]);
                $msg = '✅ Grupo actualizado.';
            } else {
                $pdo->prepare('INSERT INTO footer_grupos (titulo, orden, activo) VALUES (?, ?, ?)')
                    ->execute([$titulo, $orden, $activo]);
                $new_id = (int)$pdo->lastInsertId();
                header('Location: /admin/footer/index.php?msg=' . urlencode('✅ Grupo creado.') . '&type=ok');
                exit;
            }
            $msg_type = 'ok';
            // Recargar
            $stmt = $pdo->prepare('SELECT * FROM footer_grupos WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Admin footer/grupo-edit POST: ' . $e->getMessage());
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
            <label for="titulo">Título del grupo <span style="color:red">*</span></label>
            <input type="text" id="titulo" name="titulo" class="form-control"
                   value="<?= h($grupo['titulo']) ?>" required maxlength="80"
                   placeholder="Ej: Páginas de Interés">
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="orden">Orden de aparición (menor = antes)</label>
            <input type="number" id="orden" name="orden" class="form-control"
                   value="<?= (int)$grupo['orden'] ?>" min="0" max="99" style="max-width:120px;">
        </div>

        <div class="form-group" style="margin-top:1rem;display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" id="activo" name="activo" value="1"
                   <?= $grupo['activo'] ? 'checked' : '' ?>>
            <label for="activo" style="margin:0;font-weight:400;">Grupo activo (visible en footer)</label>
        </div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="btn">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-save"/></svg>
                <?= $is_edit ? 'Guardar cambios' : 'Crear grupo' ?>
            </button>
        </div>
    </form>
</div>

<?php include '../footer.php'; ?>
