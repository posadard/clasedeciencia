<?php
/**
 * Admin - Manage Tags
 */

require_once 'auth.php';

$page_title = 'Manage Tags';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $success = "✅ Tag creado correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        
        try {
            $stmt = $pdo->prepare("UPDATE tags SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
            $success = "✅ Tag actualizado correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM article_tags WHERE tag_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([$id]);
            $pdo->commit();
            $success = "✅ Tag eliminado correctamente";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all tags
$tags = $pdo->query("
    SELECT t.*, COUNT(at.article_id) as article_count 
    FROM tags t 
    LEFT JOIN article_tags at ON t.id = at.tag_id 
    GROUP BY t.id 
    ORDER BY t.name
")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-tag"/></svg> Manage Tags</h2>
    <button class="btn" onclick="showCreateForm()">➕ New Tag</button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- Create Form -->
<div id="create-form" style="display: none;" class="card" style="margin-bottom: 2rem;">
    <h3>Create New Tag</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Tag Name *</label>
                <input type="text" id="name" name="name" required placeholder="Cleaning">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required placeholder="cleaning">
            </div>
        </div>
        
        <button type="submit" class="btn">💾 Create Tag</button>
        <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
    </form>
</div>

<!-- Tags List -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Used in Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= $tag['id'] ?></td>
                <td><strong><?= h($tag['name']) ?></strong></td>
                <td><code><?= h($tag['slug']) ?></code></td>
                <td><?= $tag['article_count'] ?> artículos</td>
                <td class="actions">
                    <button class="btn action-btn" onclick="editTag(<?= $tag['id'] ?>, '<?= addslashes(h($tag['name'])) ?>', '<?= addslashes(h($tag['slug'])) ?>')"><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit</button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este tag? Se removerá de <?= $tag['article_count'] ?> artículos.')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                        <button type="submit" class="btn action-btn btn-danger"><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="max-width: 600px; margin: 5% auto; background: white; padding: 2rem; border-radius: 8px;">
        <h3>Edit Tag</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit-name">Tag Name *</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-slug">Slug *</label>
                    <input type="text" id="edit-slug" name="slug" required>
                </div>
            </div>
            
            <button type="submit" class="btn">💾 Update Tag</button>
            <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Cancel</button>
        </form>
    </div>
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
</style>

<script>
function showCreateForm() {
    document.getElementById('create-form').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('create-form').style.display = 'none';
}

function editTag(id, name, slug) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-slug').value = slug;
    document.getElementById('edit-modal').style.display = 'block';
}

function hideEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});
</script>

<?php include 'footer.php'; ?>
