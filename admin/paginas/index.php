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

<div class="card">
    <?php if (empty($paginas)): ?>
        <p style="padding:1rem;color:#666;">No hay páginas registradas. Verifica que la tabla <code>paginas_estaticas</code> exista y tenga datos.</p>
    <?php else: ?>
        <table class="admin-table">
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
                    <td><?= h($p['titulo']) ?></td>
                    <td>
                        <span class="badge <?= $p['activo'] ? 'badge-ok' : 'badge-off' ?>">
                            <?= $p['activo'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td><?= $p['updated_at'] ? date('d/m/Y H:i', strtotime($p['updated_at'])) : '—' ?></td>
                    <td><?= h($p['updated_by'] ?? '—') ?></td>
                    <td>
                        <a href="/admin/paginas/edit.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-sm">
                            <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg>
                            Editar
                        </a>
                        <a href="/<?= urlencode($p['slug']) ?>.php" target="_blank" class="btn btn-sm btn-secondary">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
