<?php
/**
 * Admin - Manage Issues
 */

require_once 'auth.php';

$page_title = 'Manage Issues';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $season = $_POST['season'];
        $year = (int)$_POST['year'];
        $description = trim($_POST['description']);
        $published_at = $_POST['published_at'] ?: date('Y-m-d H:i:s');
        
        try {
            $stmt = $pdo->prepare("INSERT INTO issues (title, slug, season, year, description, published_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $season, $year, $description, $published_at]);
            $success = "✅ Issue creado correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $season = $_POST['season'];
        $year = (int)$_POST['year'];
        $description = trim($_POST['description']);
        $published_at = $_POST['published_at'];
        
        try {
            $stmt = $pdo->prepare("UPDATE issues SET title = ?, slug = ?, season = ?, year = ?, description = ?, published_at = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $season, $year, $description, $published_at, $id]);
            $success = "✅ Issue actualizado correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        try {
            $pdo->prepare("DELETE FROM issues WHERE id = ?")->execute([$id]);
            $success = "✅ Issue eliminado correctamente";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all issues
$issues = $pdo->query("
    SELECT * FROM issues 
    ORDER BY year DESC, 
    CASE season 
        WHEN 'Spring' THEN 1
        WHEN 'Summer' THEN 2
        WHEN 'Fall' THEN 3
        WHEN 'Winter' THEN 4
    END
")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-calendar"/></svg> Manage Issues</h2>
    <button class="btn" onclick="showCreateForm()">➕ New Issue</button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- Create Form -->
<div id="create-form" style="display: none;" class="card" style="margin-bottom: 2rem;">
    <h3>Create New Issue</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
            <div class="form-group">
                <label for="title">Issue Title *</label>
                <input type="text" id="title" name="title" required placeholder="Fall 2025">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required placeholder="fall-2025">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="season">Season *</label>
                <select id="season" name="season" required>
                    <option value="">Select...</option>
                    <option value="Spring">Spring</option>
                    <option value="Summer">Summer</option>
                    <option value="Fall">Fall</option>
                    <option value="Winter">Winter</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="year">Year *</label>
                <input type="number" id="year" name="year" required value="<?= date('Y') ?>" min="2020" max="2100">
            </div>
            
            <div class="form-group">
                <label for="published_at">Published Date</label>
                <input type="datetime-local" id="published_at" name="published_at">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Articles and content for Fall 2025"></textarea>
        </div>
        
        <button type="submit" class="btn">💾 Create Issue</button>
        <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
    </form>
</div>

<!-- Issues List -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Season</th>
                <th>Year</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($issues as $issue): ?>
            <tr>
                <td><?= $issue['id'] ?></td>
                <td><strong><?= h($issue['title']) ?></strong></td>
                <td><?= h($issue['season']) ?></td>
                <td><?= $issue['year'] ?></td>
                <td><?= format_date($issue['published_at'], 'M j, Y') ?></td>
                <td class="actions">
                    <button class="btn action-btn" onclick='editIssue(<?= json_encode([
                        'id' => $issue['id'],
                        'title' => $issue['title'],
                        'slug' => $issue['slug'],
                        'season' => $issue['season'],
                        'year' => $issue['year'],
                        'description' => $issue['description'],
                        'published_at' => date('Y-m-d\TH:i', strtotime($issue['published_at']))
                    ]) ?>)'><svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit</button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este issue?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $issue['id'] ?>">
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
    <div style="max-width: 700px; margin: 5% auto; background: white; padding: 2rem; border-radius: 8px;">
        <h3>Edit Issue</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit-title">Issue Title *</label>
                    <input type="text" id="edit-title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-slug">Slug *</label>
                    <input type="text" id="edit-slug" name="slug" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit-season">Season *</label>
                    <select id="edit-season" name="season" required>
                        <option value="Spring">Spring</option>
                        <option value="Summer">Summer</option>
                        <option value="Fall">Fall</option>
                        <option value="Winter">Winter</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-year">Year *</label>
                    <input type="number" id="edit-year" name="year" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-published_at">Published Date</label>
                    <input type="datetime-local" id="edit-published_at" name="published_at">
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit-description">Description</label>
                <textarea id="edit-description" name="description" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn">💾 Update Issue</button>
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

function editIssue(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-title').value = data.title;
    document.getElementById('edit-slug').value = data.slug;
    document.getElementById('edit-season').value = data.season;
    document.getElementById('edit-year').value = data.year;
    document.getElementById('edit-description').value = data.description;
    document.getElementById('edit-published_at').value = data.published_at;
    document.getElementById('edit-modal').style.display = 'block';
}

function hideEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

document.getElementById('title').addEventListener('input', function() {
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
