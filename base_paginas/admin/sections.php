<?php
/**
 * Admin - Manage Sections
 */

require_once 'auth.php';

$page_title = 'Manage Sections';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO sections (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            $success = "✅ Sección creada correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description']);
        
        try {
            $stmt = $pdo->prepare("UPDATE sections SET name = ?, slug = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $id]);
            $success = "✅ Sección actualizada correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        try {
            $pdo->prepare("DELETE FROM sections WHERE id = ?")->execute([$id]);
            $success = "✅ Sección eliminada correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all sections
$sections = $pdo->query("
    SELECT s.*, COUNT(a.id) as article_count 
    FROM sections s 
    LEFT JOIN articles a ON s.id = a.section_id 
    GROUP BY s.id 
    ORDER BY s.name
")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Manage Sections</h2>
    <button class="btn" onclick="showCreateForm()">➕ New Section</button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- Create Form (Hidden by default) -->
<div id="create-form" style="display: none;" class="card" style="margin-bottom: 2rem;">
    <h3>Create New Section</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Section Name *</label>
                <input type="text" id="name" name="name" required placeholder="Home Workshop">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required placeholder="home-workshop">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2" placeholder="Articles about home workshop projects"></textarea>
        </div>
        
        <button type="submit" class="btn">💾 Create Section</button>
        <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
    </form>
</div>

<!-- Sections List -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Description</th>
                <th>Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sections as $section): ?>
            <tr>
                <td><?= $section['id'] ?></td>
                <td><strong><?= h($section['name']) ?></strong></td>
                <td><code><?= h($section['slug']) ?></code></td>
                <td><?= h($section['description']) ?></td>
                <td><?= $section['article_count'] ?> artículos</td>
                <td class="actions">
                    <button class="btn action-btn" onclick="editSection(<?= $section['id'] ?>, '<?= addslashes(h($section['name'])) ?>', '<?= addslashes(h($section['slug'])) ?>', '<?= addslashes(h($section['description'])) ?>')"><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit</button>
                    <?php if ($section['article_count'] == 0): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta sección?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $section['id'] ?>">
                            <button type="submit" class="btn action-btn btn-danger"><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="max-width: 600px; margin: 5% auto; background: white; padding: 2rem; border-radius: 8px;">
        <h3>Edit Section</h3>
        <form method="POST" id="edit-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit-name">Section Name *</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-slug">Slug *</label>
                    <input type="text" id="edit-slug" name="slug" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit-description">Description</label>
                <textarea id="edit-description" name="description" rows="2"></textarea>
            </div>
            
            <button type="submit" class="btn">💾 Update Section</button>
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

function editSection(id, name, slug, description) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-slug').value = slug;
    document.getElementById('edit-description').value = description;
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
