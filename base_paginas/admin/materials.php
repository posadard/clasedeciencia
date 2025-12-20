<?php
/**
 * Admin - Materials Management
 * List all materials with filters and search
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

$pageTitle = 'Manage Materials';

// Handle filters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query
$params = [];
$where = [];
$joins = [];

if ($category_filter) {
    $where[] = "m.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $where[] = "m.status = ?";
    $params[] = $status_filter;
} else {
    // Default: show all except discontinued
    $where[] = "m.status != 'discontinued'";
}

if ($search_query) {
    $where[] = "(m.common_name LIKE ? OR m.technical_name LIKE ? OR m.slug LIKE ?)";
    $searchTerm = '%' . $search_query . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

// Get materials
$sql = "SELECT m.*, 
        mc.name as category_name, mc.slug as category_slug, mc.icon as category_icon,
        ms.name as subcategory_name,
        (SELECT COUNT(*) FROM article_materials WHERE material_id = m.id) as usage_count
        FROM materials m
        LEFT JOIN material_categories mc ON m.category_id = mc.id
        LEFT JOIN material_subcategories ms ON m.subcategory_id = ms.id
        $whereClause
        ORDER BY m.featured DESC, m.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// Get categories for filter
$categories = get_material_categories($pdo);

// Get stats
$stats = get_material_stats($pdo);

include 'header.php';
?>

<div class="admin-header">
    <h1><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-flask"/></svg> Materials Management</h1>
    <a href="material-edit.php" class="btn btn-primary">
        <span class="icon">➕</span> Add New Material
    </a>
</div>

<!-- Stats removed: list-focused view only -->

<!-- Filters -->
<div class="filters-bar">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label for="category">Category:</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="status">Status:</label>
            <select name="status" id="status" onchange="this.form.submit()">
                <option value="">All (except discontinued)</option>
                <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="discontinued" <?= $status_filter === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
            </select>
        </div>
        
        <div class="filter-group search-group">
            <label for="search">Search:</label>
            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search_query) ?>" 
                   placeholder="Material name or slug...">
            <button type="submit" class="btn btn-sm">🔍 Search</button>
        </div>
        
        <?php if ($category_filter || $status_filter || $search_query): ?>
        <a href="materials.php" class="btn btn-sm btn-secondary">Clear Filters</a>
        <?php endif; ?>
    </form>
</div>

<!-- Materials Table -->
<div class="table-container">
    <?php if (empty($materials)): ?>
    <div class="empty-state">
        <p>No materials found.</p>
        <a href="material-edit.php" class="btn btn-primary">Add Your First Material</a>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Used In</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materials as $material): ?>
            <tr>
                <td><?= $material['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($material['common_name']) ?></strong>
                    <?php if ($material['technical_name']): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($material['technical_name']) ?></small>
                    <?php endif; ?>
                    <?php if ($material['chemical_formula']): ?>
                    <br><small class="formula"><?= htmlspecialchars($material['chemical_formula']) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-<?= $material['category_slug'] ?>">
                        <?= $material['category_icon'] ?> <?= htmlspecialchars($material['category_name']) ?>
                    </span>
                </td>
                <td>
                    <?= $material['subcategory_name'] ? htmlspecialchars($material['subcategory_name']) : '-' ?>
                </td>
                <td>
                    <?php if ($material['featured']): ?>
                    <span class="icon" title="Featured" aria-hidden="true"><svg class="admin-icon" width="16" height="16"><use xlink:href="#icon-star"/></svg></span>
                    <?php endif; ?>
                    <?php if ($material['essential']): ?>
                    <span class="icon" title="Essential" aria-hidden="true"><svg class="admin-icon" width="14" height="14"><use xlink:href="#icon-check"/></svg></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge status-<?= $material['status'] ?>">
                        <?= ucfirst($material['status']) ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if ($material['usage_count'] > 0): ?>
                    <a href="#" title="<?= $material['usage_count'] ?> article(s)">
                        <?= $material['usage_count'] ?> 📄
                    </a>
                    <?php else: ?>
                    <span class="text-muted">0</span>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?= date('M j, Y', strtotime($material['updated_at'])) ?></small>
                </td>
                <td class="actions">
                    <a href="material-edit.php?id=<?= $material['id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                        <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-edit"/></svg> Edit
                    </a>
                    <a href="material-delete.php?id=<?= $material['id'] ?>" class="btn btn-sm btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this material?')" title="Delete">
                        <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg> Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

/* Stats removed: styles not needed for list-focused materials admin */

.filters-bar {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.filters-form {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: bold;
    font-size: 0.9rem;
}

.filter-group select,
.filter-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
}

.search-group {
    flex-direction: row;
    align-items: center;
    flex: 1;
}

.search-group input {
    flex: 1;
}

.table-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #2c5f2d;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: bold;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.text-muted {
    color: #666;
}

.formula {
    font-family: 'Courier New', monospace;
    color: #2c5f2d;
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

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
}

.status-published { background: #d4edda; color: #155724; }
.status-draft { background: #fff3cd; color: #856404; }
.status-discontinued { background: #f8d7da; color: #721c24; }

.actions {
    white-space: nowrap;
}

.actions .btn {
    margin-right: 0.5rem;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.text-center {
    text-align: center;
}

.icon {
    display: inline-block;
}
</style>

<?php include 'footer.php'; ?>
