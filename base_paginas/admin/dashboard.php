<?php
/**
 * Admin Dashboard
 */

require_once 'auth.php';

$page_title = 'Dashboard';

// Get statistics
$stats = [
    'articles' => $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn(),
    'articles_draft' => $pdo->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn(),
    'tags' => $pdo->query("SELECT COUNT(*) FROM tags")->fetchColumn(),
    'sections' => $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn(),
    'issues' => $pdo->query("SELECT COUNT(*) FROM issues")->fetchColumn(),
];

// Get recent articles
$recent_articles = $pdo->query("
    SELECT a.*, s.name as section_name 
    FROM articles a 
    LEFT JOIN sections s ON a.section_id = s.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $stats['articles'] ?></h3>
        <p>Published Articles</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['articles_draft'] ?></h3>
        <p>Draft Articles</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['tags'] ?></h3>
        <p>Tags</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['sections'] ?></h3>
        <p>Sections</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['issues'] ?></h3>
        <p>Issues</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h3>Quick Actions</h3>
    <div class="actions">
        <a href="/admin/articles.php?action=new" class="btn">+ New Article</a>
    </div>
</div>

<!-- Recent Articles -->
<div class="card">
    <h3>Recent Articles</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Section</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_articles as $article): ?>
            <tr>
                <td><?= h($article['title']) ?></td>
                <td><?= h($article['section_name']) ?></td>
                <td>
                    <span style="padding: 0.25rem 0.5rem; background: <?= $article['status'] === 'published' ? '#4caf50' : '#ff9800' ?>; color: white; font-size: 0.75rem; font-weight: 600;">
                        <?= strtoupper($article['status']) ?>
                    </span>
                </td>
                <td><?= format_date($article['created_at'], 'M j, Y') ?></td>
                <td class="actions">
                    <a href="/article.php?slug=<?= h($article['slug']) ?>" target="_blank" class="btn action-btn btn-secondary">View</a>
                    <a href="/admin/articles.php?action=edit&id=<?= $article['id'] ?>" class="btn action-btn">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Important Info -->
<div class="card">
    <h3>⚠️ Important Information</h3>
    <ul style="line-height: 1.8;">
        <li><strong>Database:</strong> toys2000_green</li>
        <li><strong>ChemicalStore Integration:</strong> Products embedded with widgets (no local inventory)</li>
        <li><strong>SEO:</strong> Every article generates Schema.org JSON-LD markup</li>
        <li><strong>Print:</strong> All articles are optimized for printing</li>
        <li><strong>Focus:</strong> Pure almanac content - Articles organized by Sections and Seasons</li>
    </ul>
</div>

<!-- Security Note -->
<div class="message info">
    <strong>🔒 Security Note:</strong> Remember to change the default admin password in <code>/admin/index.php</code> before going to production!
</div>

<?php include 'footer.php'; ?>
