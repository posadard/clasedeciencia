<?php
require_once '../auth.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('Location: /admin/paginas/index.php');
    exit;
}

// Cargar registro
$pagina = false;
try {
    $stmt = $pdo->prepare('SELECT * FROM paginas_estaticas WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $pagina = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Admin paginas/edit GET error: ' . $e->getMessage());
}

if (!$pagina) {
    header('Location: /admin/paginas/index.php');
    exit;
}

$page_title = 'Editar: ' . $pagina['titulo'];
$msg = '';
$msg_type = '';

// Procesar guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo           = trim($_POST['titulo'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $contenido_html   = $_POST['contenido_html'] ?? '';   // HTML de confianza editado por Admin (CKEditor)
    $activo           = isset($_POST['activo']) ? 1 : 0;
    $updated_by       = $_SESSION['admin_username'] ?? 'admin';

    if ($titulo === '') {
        $msg = 'El título es obligatorio.';
        $msg_type = 'error';
    } else {
        try {
            $upd = $pdo->prepare(
                'UPDATE paginas_estaticas
                 SET titulo = ?, meta_description = ?, contenido_html = ?,
                     activo = ?, updated_by = ?
                 WHERE slug = ?'
            );
            $upd->execute([$titulo, $meta_description, $contenido_html, $activo, $updated_by, $slug]);

            // Recargar datos frescos
            $stmt = $pdo->prepare('SELECT * FROM paginas_estaticas WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
            $pagina = $stmt->fetch(PDO::FETCH_ASSOC);

            $msg = '✅ Página actualizada correctamente.';
            $msg_type = 'ok';
            console_log_inline('✅ [Admin] Página guardada: ' . $slug);
        } catch (PDOException $e) {
            error_log('Admin paginas/edit POST error: ' . $e->getMessage());
            $msg = '❌ Error al guardar: ' . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

function console_log_inline($msg) { /* solo para referencia; usamos JS inline */ }

include '../header.php';
?>
<div class="page-header">
    <h2><?= h($page_title) ?></h2>
    <a href="/admin/paginas/index.php" class="btn btn-secondary">← Volver</a>
    <script>
        console.log('🔍 [Admin] Editando página estática: "<?= h($slug) ?>"');
    </script>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type === 'ok' ? 'success' : 'error' ?>" style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:6px;background:<?= $msg_type === 'ok' ? '#d4edda' : '#f8d7da' ?>;color:<?= $msg_type === 'ok' ? '#155724' : '#721c24' ?>;">
    <?= h($msg) ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="POST" id="form-pagina">
        <div class="form-group">
            <label for="titulo">Título de la página <span style="color:red">*</span></label>
            <input type="text" id="titulo" name="titulo" class="form-control"
                   value="<?= h($pagina['titulo']) ?>" required maxlength="255">
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="meta_description">
                Meta descripción
                <small style="color:#666;font-weight:400;">(máximo 320 caracteres — para SEO)</small>
            </label>
            <textarea id="meta_description" name="meta_description" class="form-control"
                      maxlength="320" rows="3"><?= h($pagina['meta_description'] ?? '') ?></textarea>
            <small id="meta-counter" style="color:#999;">
                <?= mb_strlen($pagina['meta_description'] ?? '') ?>/320 caracteres
            </small>
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="contenido_html">Contenido</label>
            <textarea id="contenido_html" name="contenido_html" rows="12"><?= htmlspecialchars($pagina['contenido_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group" style="margin-top:1rem;display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" id="activo" name="activo" value="1"
                   <?= $pagina['activo'] ? 'checked' : '' ?>>
            <label for="activo" style="margin:0;font-weight:400;">Página activa (visible en el sitio)</label>
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:0.75rem;align-items:center;">
            <button type="submit" class="btn">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-save"/></svg>
                Guardar cambios
            </button>
            <a href="/<?= urlencode($slug) ?>.php" target="_blank" class="btn btn-secondary">Ver página →</a>
            <span style="color:#999;font-size:0.85rem;">
                Última edición: <?= $pagina['updated_at'] ? date('d/m/Y H:i', strtotime($pagina['updated_at'])) : '—' ?>
                <?= $pagina['updated_by'] ? 'por ' . h($pagina['updated_by']) : '' ?>
            </span>
        </div>
    </form>
</div>

<!-- CKEditor 4 (mismo que clases/edit.php y kits/edit.php) -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
(function initCKEPaginas() {
    try {
        if (window.CKEDITOR) {
            CKEDITOR.replace('contenido_html', {
                height: 500,
                removePlugins: 'elementspath',
                resize_enabled: true,
                contentsCss: ['/assets/css/style.css', '/assets/css/article-content.css'],
                bodyClass: 'article-body'
            });
            console.log('✅ [PaginasEdit] CKEditor 4 cargado para página: "<?= h($slug) ?>"');
        } else {
            console.log('⚠️ [PaginasEdit] CKEditor no disponible, usando textarea simple');
        }
    } catch(e) {
        console.log('❌ [PaginasEdit] Error iniciando CKEditor:', e && e.message);
    }
})();

// Ocultar avisos de versión de CKEditor
(function hideCkeWarnings(){
    try {
        const style = document.createElement('style');
        style.textContent = '.cke_notification.cke_notification_warning,.cke_upgrade_notice { display:none !important; }';
        document.head.appendChild(style);
    } catch(e) {}
})();

// Contador de caracteres meta_description
document.getElementById('meta_description').addEventListener('input', function() {
    document.getElementById('meta-counter').textContent = this.value.length + '/320 caracteres';
});
</script>

<?php include '../footer.php'; ?>
