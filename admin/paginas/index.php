<?php
require_once '../auth.php';
$page_title = 'Páginas Estáticas';

try {
    $stmt = $pdo->query(
        'SELECT id, slug, titulo, meta_description, activo, updated_at, updated_by
         FROM paginas_estaticas
         ORDER BY id ASC'
    );
    $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $paginas = [];
    error_log('Admin paginas/index error: ' . $e->getMessage());
}

include '../header.php';
?>
<div class="page-header">
    <h2>Páginas Estáticas</h2>
    <span class="help-text">Gestiona el contenido de las páginas informativas del sitio.</span>
    <script>
        console.log('✅ [Admin] Páginas estáticas cargado, total:', <?= count($paginas) ?>);
    </script>
</div>

<div class="card" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
    <h3 style="margin:0;">Listado</h3>
</div>

<?php if (empty($paginas)): ?>
<div class="empty-state">
    <p>No hay páginas registradas. Verifica que la tabla <code>paginas_estaticas</code> exista y tenga datos.</p>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Slug</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Última edición</th>
            <th>Editor</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($paginas as $p): ?>
        <tr>
            <td><code><?= h($p['slug']) ?></code></td>
            <td><strong><?= h($p['titulo']) ?></strong></td>
            <td>
                <span class="badge <?= $p['activo'] ? 'badge-ok' : 'badge-off' ?>">
                    <?= $p['activo'] ? 'Activa' : 'Inactiva' ?>
                </span>
            </td>
            <td><?= $p['updated_at'] ? date('d/m/Y H:i', strtotime($p['updated_at'])) : '—' ?></td>
            <td><?= h($p['updated_by'] ?? '—') ?></td>
            <td class="actions">
                <a href="/admin/paginas/edit.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-sm btn-edit">
                    <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                    Editar
                </a>
                <a href="/<?= urlencode($p['slug']) ?>.php" target="_blank" class="btn btn-sm btn-secondary">
                    <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-article"/></svg>
                    Ver
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<style>
.badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.82rem; font-weight: 600; background: #e7e7e7; color: #333; }
.badge-ok { background: #d4edda; color: #155724; }
.badge-off { background: #f8d7da; color: #721c24; }
.btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
.btn-edit { background: #007bff; color: #fff; }
.btn-edit:hover { background: #0056b3; }
.actions { white-space: nowrap; }
.actions .btn { margin-right: 0.4rem; }
.empty-state { text-align: center; padding: 3rem; color: #666; }
</style>

<?php include '../footer.php'; ?>
