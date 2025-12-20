<?php
/**
 * Admin - Manage Articles
 */

require_once 'auth.php';

$page_title = 'Manage Articles';

// Get all articles
$articles = $pdo->query("
    SELECT a.*, s.name as section_name 
    FROM articles a 
    LEFT JOIN sections s ON a.section_id = s.id 
    ORDER BY a.created_at DESC
")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-article"/></svg> Manage Articles</h2>
    <a href="article-edit.php" class="btn">➕ New Article</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ Artículo guardado correctamente</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">✅ Artículo eliminado correctamente</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">❌ Error: <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- Articles List -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Section</th>
                <th>Status</th>
                <th>Format</th>
                <th>Difficulty</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= $article['id'] ?></td>
                <td><strong><?= h($article['title']) ?></strong></td>
                <td><?= h($article['section_name']) ?></td>
                <td>
                    <span style="padding: 0.25rem 0.5rem; background: <?= $article['status'] === 'published' ? '#4caf50' : '#ff9800' ?>; color: white; font-size: 0.75rem; font-weight: 600; border-radius: 3px;">
                        <?= strtoupper($article['status']) ?>
                    </span>
                </td>
                <td><?= h(ucfirst($article['format'])) ?></td>
                <td><?= h(ucfirst($article['difficulty'])) ?></td>
                <td><?= format_date($article['published_at'], 'M j, Y') ?></td>
                <td class="actions">
                    <a href="article-edit.php?id=<?= $article['id'] ?>" class="btn action-btn"><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit</a>
                    <a href="/article.php?slug=<?= h($article['slug']) ?>" target="_blank" class="btn action-btn btn-secondary">👁️ View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}
</style>

<?php include 'footer.php'; ?>
