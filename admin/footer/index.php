<?php
require_once '../auth.php';
$page_title = 'Footer del Sitio';

// Mensajes de feedback de operaciones GET redirect
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
    <script>
        console.log('✅ [Admin] Footer index cargado, grupos:', <?= count($grupos) ?>);
    </script>
</div>

<?php if ($msg): ?>
<div class="alert <?= $msg_type === 'ok' ? 'alert-ok' : 'alert-error' ?>" style="margin-bottom:1rem;">
    <?= h($msg) ?>
</div>
<?php endif; ?>

<!-- Nota: columna "Acerca de" es texto puro gestionado en Configuración del Sitio -->
<div class="card info-note">
    <small>
        <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-tag"/></svg>
        La columna <strong>"Acerca de"</strong> del footer contiene un párrafo de texto, no links.
        Se edita en <a href="/admin/sitio/ajustes.php">Configuración del Sitio</a> → <em>footer_texto_sobre</em>.
    </small>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
    <h3 style="margin:0;">Grupos del footer</h3>
    <a href="/admin/footer/grupo-edit.php" class="btn">
        <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
        Nuevo Grupo
    </a>
</div>

<?php if (empty($grupos)): ?>
<div class="empty-state">
    <p>No hay grupos de footer.</p>
    <a href="/admin/footer/grupo-edit.php" class="btn">Crear Grupo</a>
</div>
<?php else: ?>

<?php foreach ($grupos as $grupo): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div class="group-header">
        <div class="group-meta">
            <span class="orden-badge">Orden <?= (int)$grupo['orden'] ?></span>
            <strong><?= h($grupo['titulo']) ?></strong>
            <span class="badge <?= $grupo['activo'] ? 'badge-ok' : 'badge-off' ?>">
                <?= $grupo['activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
        </div>
        <div class="group-actions">
            <a href="/admin/footer/grupo-edit.php?id=<?= (int)$grupo['id'] ?>" class="btn btn-sm btn-edit">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                Editar grupo
            </a>
            <a href="/admin/footer/enlace-edit.php?grupo_id=<?= (int)$grupo['id'] ?>" class="btn btn-sm">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
                Nuevo enlace
            </a>
        </div>
    </div>

    <?php $enlaces = $enlaces_por_grupo[$grupo['id']] ?? []; ?>
    <?php if (empty($enlaces)): ?>
        <p class="text-muted" style="font-size:0.9rem;padding:0.25rem 0;">Sin enlaces en este grupo.</p>
    <?php else: ?>
        <table class="data-table">
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
                <tr <?= !$enlace['activo'] ? 'class="row-inactive"' : '' ?>>
                    <td><?= (int)$enlace['orden'] ?></td>
                    <td><strong><?= h($enlace['etiqueta']) ?></strong></td>
                    <td class="url-cell">
                        <a href="<?= h($enlace['url']) ?>" target="_blank" rel="noopener"><?= h($enlace['url']) ?></a>
                    </td>
                    <td>
                        <?php if ($enlace['externo']): ?>
                            <span class="badge">
                                <svg class="admin-icon" width="12" height="12" aria-hidden="true"><use xlink:href="#icon-tag"/></svg>
                                Externo
                            </span>
                        <?php else: ?>
                            <span class="badge badge-internal">Interno</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $enlace['activo'] ? 'badge-ok' : 'badge-off' ?>">
                            <?= $enlace['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="/admin/footer/enlace-edit.php?id=<?= (int)$enlace['id'] ?>" class="btn btn-sm btn-edit">
                            <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                            Editar
                        </a>
                        <a href="/admin/footer/enlace-delete.php?id=<?= (int)$enlace['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar el enlace \'<?= h(addslashes($enlace['etiqueta'])) ?>\'?')">
                            <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg>
                            Eliminar
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

<style>
.info-note { margin-bottom: 1rem; padding: 0.6rem 1rem; background: #f0f4ff; border-left: 4px solid #1f3c88; }
.group-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem; }
.group-meta { display: flex; align-items: center; gap: 0.75rem; }
.group-actions { display: flex; gap: 0.5rem; }
.orden-badge { background: #1f3c88; color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
.badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.82rem; font-weight: 600; background: #e7e7e7; color: #333; }
.badge-ok { background: #d4edda; color: #155724; }
.badge-off { background: #f8d7da; color: #721c24; }
.badge-internal { background: #e2eaff; color: #1f3c88; }
.btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
.btn-edit { background: #007bff; color: #fff; }
.btn-edit:hover { background: #0056b3; }
.btn-danger { background: #dc3545; color: #fff; }
.btn-danger:hover { background: #b02030; }
.actions { white-space: nowrap; }
.actions .btn { margin-right: 0.4rem; }
.url-cell { word-break: break-all; font-size: 0.85rem; }
.row-inactive { opacity: 0.5; }
.text-muted { color: #666; }
.empty-state { text-align: center; padding: 3rem; color: #666; }
.alert { padding: 0.75rem 1rem; border-radius: 6px; }
.alert-ok { background: #d4edda; color: #155724; }
.alert-error { background: #f8d7da; color: #721c24; }
</style>

<?php include '../footer.php'; ?>
