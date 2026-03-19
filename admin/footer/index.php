<?php
require_once '../auth.php';
$page_title = 'Footer del Sitio';

// Mensajes de feedback de operaciones POST
$msg = $_GET['msg'] ?? '';
$msg_type = $_GET['type'] ?? 'ok';

// Cargar grupos con sus enlaces
try {
    $stmt_grupos = $pdo->query(
        'SELECT id, titulo, orden, activo FROM footer_grupos ORDER BY orden ASC, id ASC'
    );
    $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_enlaces = $pdo->query(
        'SELECT id, grupo_id, etiqueta, url, externo, orden, activo
         FROM footer_enlaces ORDER BY grupo_id ASC, orden ASC'
    );
    $all_enlaces = $stmt_enlaces->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar enlaces por grupo_id
    $enlaces_por_grupo = [];
    foreach ($all_enlaces as $e) {
        $enlaces_por_grupo[$e['grupo_id']][] = $e;
    }
} catch (PDOException $e) {
    $grupos = [];
    $enlaces_por_grupo = [];
    error_log('Admin footer/index error: ' . $e->getMessage());
}

include '../header.php';
?>
<div class="page-header">
    <h2>Footer del Sitio</h2>
    <span class="help-text">Gestiona los grupos y enlaces que aparecen en el pie de página.</span>
    <a href="/admin/footer/grupo-edit.php" class="btn">+ Nuevo Grupo</a>
    <script>
        console.log('✅ [Admin] Footer index cargado, grupos:', <?= count($grupos) ?>);
    </script>
</div>

<?php if ($msg): ?>
<div class="alert" style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:6px;background:<?= $msg_type === 'ok' ? '#d4edda' : '#f8d7da' ?>;color:<?= $msg_type === 'ok' ? '#155724' : '#721c24' ?>;">
    <?= h($msg) ?>
</div>
<?php endif; ?>

<!-- Nota: columna "Acerca de" es texto puro gestionado en Configuración del Sitio -->
<div class="card" style="margin-bottom:0.5rem;padding:0.6rem 1rem;background:#f0f4ff;border-left:4px solid #1f3c88;">
    <small>
        💡 La columna <strong>"Acerca de"</strong> del footer contiene un párrafo de texto, no links.
        Se edita en <a href="/admin/sitio/config.php">Configuración del Sitio</a> → <em>footer_texto_sobre</em>.
    </small>
</div>

<?php if (empty($grupos)): ?>
<div class="card">
    <p style="padding:1rem;color:#666;">No hay grupos de footer. Crea el primero con el botón "+ Nuevo Grupo".</p>
</div>
<?php else: ?>

<?php foreach ($grupos as $grupo): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;flex-wrap:wrap;gap:0.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <span style="background:#1f3c88;color:#fff;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.8rem;">
                Orden <?= (int)$grupo['orden'] ?>
            </span>
            <strong style="font-size:1rem;"><?= h($grupo['titulo']) ?></strong>
            <span class="badge <?= $grupo['activo'] ? 'badge-ok' : 'badge-off' ?>">
                <?= $grupo['activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <a href="/admin/footer/grupo-edit.php?id=<?= (int)$grupo['id'] ?>" class="btn btn-sm">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                Editar grupo
            </a>
            <a href="/admin/footer/enlace-edit.php?grupo_id=<?= (int)$grupo['id'] ?>" class="btn btn-sm">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
                + Enlace
            </a>
        </div>
    </div>

    <?php $enlaces = $enlaces_por_grupo[$grupo['id']] ?? []; ?>
    <?php if (empty($enlaces)): ?>
        <p style="color:#999;font-size:0.9rem;padding:0.25rem 0;">Sin enlaces en este grupo.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Etiqueta</th>
                    <th>URL</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enlaces as $enlace): ?>
                <tr <?= !$enlace['activo'] ? 'style="opacity:0.5;"' : '' ?>>
                    <td><?= (int)$enlace['orden'] ?></td>
                    <td><?= h($enlace['etiqueta']) ?></td>
                    <td style="word-break:break-all;font-size:0.85rem;">
                        <a href="<?= h($enlace['url']) ?>" target="_blank" rel="noopener"><?= h($enlace['url']) ?></a>
                    </td>
                    <td>
                        <?php if ($enlace['externo']): ?>
                            <span title="Abre en nueva pestaña">🔗 Externo</span>
                        <?php else: ?>
                            <span>Interno</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $enlace['activo'] ? 'badge-ok' : 'badge-off' ?>">
                            <?= $enlace['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/footer/enlace-edit.php?id=<?= (int)$enlace['id'] ?>" class="btn btn-sm">
                            <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                            Editar
                        </a>
                        <a href="/admin/footer/enlace-delete.php?id=<?= (int)$enlace['id'] ?>"
                           class="btn btn-sm btn-secondary"
                           onclick="return confirm('¿Eliminar el enlace \'<?= h(addslashes($enlace['etiqueta'])) ?>\'?')">
                            🗑 Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php include '../footer.php'; ?>
