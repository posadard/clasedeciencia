<?php
/**
 * Admin - Delete Material
 * Confirmation page with usage warnings
 */

require_once '../config.php';
require_once '../includes/materials-functions.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$material_id = $_GET['id'] ?? null;

if (!$material_id) {
    $_SESSION['message'] = "No material ID provided.";
    $_SESSION['message_type'] = "error";
    header('Location: materials.php');
    exit;
}

// Get material details
$material = get_material_by_id($pdo, $material_id);

if (!$material) {
    $_SESSION['message'] = "Material not found.";
    $_SESSION['message_type'] = "error";
    header('Location: materials.php');
    exit;
}

// Check if material is used in any articles
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug
    FROM article_materials am
    JOIN articles a ON am.article_id = a.id
    WHERE am.material_id = ?
    ORDER BY a.title
");
$stmt->execute([$material_id]);
$used_in_articles = $stmt->fetchAll();

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete material (cascade will handle article_materials)
        delete_material($pdo, $material_id);
        
        $_SESSION['message'] = "Material '{$material['common_name']}' has been deleted.";
        $_SESSION['message_type'] = "success";
        header('Location: materials.php');
        exit;
    } catch (Exception $e) {
        $error = "Error deleting material: " . $e->getMessage();
    }
}

$pageTitle = 'Delete Material';
include 'header.php';
?>

<div class="admin-header">
    <h1><span class="icon" aria-hidden="true"><svg class="admin-icon" width="16" height="16"><use xlink:href="#icon-trash"/></svg></span> Delete Material</h1>
    <a href="materials.php" class="btn btn-secondary">← Back to Materials</a>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-error">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="delete-confirmation">
    <div class="material-preview">
        <h2>You are about to delete:</h2>
        <div class="material-card">
            <h3><?= htmlspecialchars($material['common_name']) ?></h3>
            <?php if ($material['technical_name']): ?>
            <p class="technical-name"><?= htmlspecialchars($material['technical_name']) ?></p>
            <?php endif; ?>
            
            <div class="material-meta">
                <span class="badge badge-<?= htmlspecialchars($material['category_slug'] ?? 'unknown') ?>">
                    <?= $material['category_icon'] ?? '' ?> <?= htmlspecialchars($material['category_name'] ?? 'Uncategorized') ?>
                </span>
                <?php if (!empty($material['subcategory_name'] ?? null)): ?>
                <span class="meta-item"><?= htmlspecialchars($material['subcategory_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($material['chemical_formula'] ?? null)): ?>
                <span class="meta-item formula"><?= htmlspecialchars($material['chemical_formula']) ?></span>
                <?php endif; ?>
            </div>
            
            <?php if ($material['description']): ?>
            <p class="description"><?= htmlspecialchars(substr($material['description'], 0, 200)) ?>...</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($used_in_articles)): ?>
    <div class="warning-box">
        <h3>⚠️ Warning: This material is used in <?= count($used_in_articles) ?> article(s)</h3>
        <p>Deleting this material will remove it from the following articles:</p>
        <ul class="article-list">
            <?php foreach ($used_in_articles as $article): ?>
            <li>
                <a href="../article.php?slug=<?= urlencode($article['slug']) ?>" target="_blank">
                    <?= htmlspecialchars($article['title']) ?>
                </a>
                <small>(ID: <?= $article['id'] ?>)</small>
            </li>
            <?php endforeach; ?>
        </ul>
        <p class="warning-text">
            <strong>This action cannot be undone.</strong> The material will be permanently removed from the database
            and unlinked from all articles.
        </p>
    </div>
    <?php else: ?>
    <div class="info-box">
        <p><svg class="admin-icon" width="12" height="12" aria-hidden="true"><use xlink:href="#icon-check"/></svg> This material is not currently used in any articles. It's safe to delete.</p>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="delete-form">
        <div class="form-actions">
            <button type="submit" name="confirm_delete" class="btn btn-danger btn-lg">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg> Yes, Delete This Material
            </button>
            <a href="materials.php" class="btn btn-secondary btn-lg">Cancel</a>
            <a href="material-edit.php?id=<?= $material_id ?>" class="btn btn-primary btn-lg">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit Instead
            </a>
        </div>
    </form>
</div>

<style>
.delete-confirmation {
    max-width: 800px;
    margin: 0 auto;
}

.material-preview {
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.material-preview h2 {
    margin-top: 0;
    color: #666;
    font-size: 1rem;
    font-weight: normal;
    margin-bottom: 1rem;
}

.material-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
    background: #f8f9fa;
}

.material-card h3 {
    margin: 0 0 0.5rem 0;
    color: #2c5f2d;
    font-size: 1.5rem;
}

.technical-name {
    color: #666;
    font-style: italic;
    margin: 0 0 1rem 0;
}

.material-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.meta-item {
    padding: 0.25rem 0.75rem;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
}

.formula {
    font-family: 'Courier New', monospace;
    color: #2c5f2d;
    font-weight: bold;
}

.description {
    color: #333;
    line-height: 1.6;
    margin: 0;
}

.warning-box {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.warning-box h3 {
    margin-top: 0;
    color: #856404;
}

.article-list {
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 1rem;
    margin: 1rem 0;
}

.article-list li {
    padding: 0.5rem;
    border-bottom: 1px solid #eee;
}

.article-list li:last-child {
    border-bottom: none;
}

.article-list a {
    color: #007bff;
    text-decoration: none;
}

.article-list a:hover {
    text-decoration: underline;
}

.article-list small {
    color: #666;
    margin-left: 0.5rem;
}

.warning-text {
    background: white;
    border-left: 4px solid #dc3545;
    padding: 1rem;
    margin: 1rem 0 0 0;
}

.info-box {
    background: #d4edda;
    border: 2px solid #28a745;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.info-box p {
    margin: 0;
    color: #155724;
    font-size: 1.1rem;
}

.delete-form {
    background: white;
    border: 2px solid #dc3545;
    border-radius: 8px;
    padding: 2rem;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: bold;
}

.badge-substance { background: #d4edda; color: #155724; }
.badge-equipment { background: #d1ecf1; color: #0c5460; }
.badge-tool { background: #fff3cd; color: #856404; }
.badge-container { background: #f8d7da; color: #721c24; }
.badge-safety { background: #f0e68c; color: #8b4513; }
.badge-consumable { background: #e7e7e7; color: #333; }

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<?php include 'footer.php'; ?>
