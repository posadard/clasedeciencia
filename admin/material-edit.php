<?php
/**
 * Admin - Material Edit/Create
 * Form with dynamic fields based on category selection
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
$is_edit = $material_id !== null;
$pageTitle = $is_edit ? 'Edit Material' : 'Add New Material';

// Load material data if editing
$material = null;
if ($is_edit) {
    $material = get_material_by_id($pdo, $material_id);
    if (!$material) {
        $_SESSION['message'] = "Material not found.";
        $_SESSION['message_type'] = "error";
        header('Location: materials.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Required fields
    if (empty($_POST['common_name'])) {
        $errors[] = "Common name is required";
    }
    if (empty($_POST['category_id'])) {
        $errors[] = "Category is required";
    }
    if (empty($_POST['description'])) {
        $errors[] = "Description is required";
    }
    
    // Generate slug if not provided
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s-]/', '', $_POST['common_name'])));
    
    // Check for duplicate slug
    if ($is_edit) {
        $stmt = $pdo->prepare("SELECT id FROM materials WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $material_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM materials WHERE slug = ?");
        $stmt->execute([$slug]);
    }
    if ($stmt->fetch()) {
        $errors[] = "A material with this slug already exists";
    }
    
    if (empty($errors)) {
        // Prepare data
        $data = [
            'slug' => $slug,
            'common_name' => $_POST['common_name'],
            'technical_name' => $_POST['technical_name'] ?? null,
            'other_names' => !empty($_POST['other_names']) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['other_names'])))) : null,
            'chemical_formula' => $_POST['chemical_formula'] ?? null,
            'cas_number' => $_POST['cas_number'] ?? null,
            'category_id' => $_POST['category_id'],
            'subcategory_id' => !empty($_POST['subcategory_id']) ? $_POST['subcategory_id'] : null,
            'description' => $_POST['description'],
            'traditional_uses' => $_POST['traditional_uses'] ?? null,
            'modern_applications' => $_POST['modern_applications'] ?? null,
            'safety_notes' => $_POST['safety_notes'] ?? null,
            'storage_instructions' => $_POST['storage_instructions'] ?? null,
            'maintenance_care' => $_POST['maintenance_care'] ?? null,
            'specifications' => !empty($_POST['specifications']) ? $_POST['specifications'] : null, // Store as JSON string
            'image_url' => $_POST['image_url'] ?? null,
            'gallery_images' => !empty($_POST['gallery_images']) ? json_encode(array_filter(array_map('trim', explode("\n", $_POST['gallery_images'])))) : null,
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'essential' => isset($_POST['essential']) ? 1 : 0,
            'difficulty_level' => $_POST['difficulty_level'] ?? 'beginner',
            'purchase_url' => $_POST['purchase_url'] ?? null,
            'abantecart_embed_code' => $_POST['abantecart_embed_code'] ?? null,
            'seo_title' => $_POST['seo_title'] ?? null,
            'seo_description' => $_POST['seo_description'] ?? null,
            'canonical_url' => $_POST['canonical_url'] ?? null,
            'status' => $_POST['status'] ?? 'draft',
            'published_at' => $_POST['status'] === 'published' && empty($material['published_at']) ? date('Y-m-d H:i:s') : ($material['published_at'] ?? null)
        ];
        
        try {
            if ($is_edit) {
                update_material($pdo, $material_id, $data);
                $_SESSION['message'] = "Material updated successfully!";
            } else {
                $material_id = create_material($pdo, $data);
                $_SESSION['message'] = "Material created successfully!";
            }
            $_SESSION['message_type'] = "success";
            header('Location: materials.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Get categories and subcategories
$categories = get_material_categories($pdo);
$all_subcategories = get_all_subcategories($pdo);

include 'header.php';
?>

<div class="admin-header">
    <h1>
    <span class="icon" aria-hidden="true"><?= $is_edit ? '<svg class="admin-icon" width="14" height="14"><use xlink:href="#icon-edit"/></svg>' : '<svg class="admin-icon" width="14" height="14"><use xlink:href="#icon-plus"/></svg>' ?></span>
        <?= $is_edit ? 'Edit Material' : 'Add New Material' ?>
    </h1>
    <a href="materials.php" class="btn btn-secondary" style="background:#333;color:#fff;border-color:#333;">← Back to Materials</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <strong>Please fix the following errors:</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" class="material-form">
    
    <!-- Basic Information Section -->
    <div class="form-section">
        <h2>Basic Information</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label for="common_name">Common Name <span class="required">*</span></label>
                <input type="text" id="common_name" name="common_name" required
                       value="<?= htmlspecialchars($material['common_name'] ?? '') ?>"
                       placeholder="e.g., Lye, pH Meter, Glass Jar">
                <small>The everyday name people use</small>
            </div>
            
            <div class="form-group">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" pattern="[a-z0-9-]+"
                       value="<?= htmlspecialchars($material['slug'] ?? '') ?>"
                       placeholder="auto-generated from common name">
                <small>Leave blank to auto-generate</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="technical_name">Technical/Scientific Name</label>
                <input type="text" id="technical_name" name="technical_name"
                       value="<?= htmlspecialchars($material['technical_name'] ?? '') ?>"
                       placeholder="e.g., Sodium Hydroxide, Digital pH Tester">
                <small>Official or scientific name (if applicable)</small>
            </div>
            
            <div class="form-group">
                <label for="other_names">Other Names (comma-separated)</label>
                <input type="text" id="other_names" name="other_names"
                       value="<?= $material ? implode(', ', json_decode($material['other_names'] ?? '[]', true)) : '' ?>"
                       placeholder="caustic soda, soda lye">
                <small>Alternative names, separated by commas</small>
            </div>
        </div>
    </div>
    
    <!-- Category & Classification -->
    <div class="form-section">
        <h2>Category & Classification</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category <span class="required">*</span></label>
                <select id="category_id" name="category_id" required onchange="updateSubcategories()">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($material['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subcategory_id">Subcategory</label>
                <select id="subcategory_id" name="subcategory_id">
                    <option value="">-- Select Subcategory --</option>
                    <?php foreach ($all_subcategories as $subcat): ?>
                    <option value="<?= $subcat['id'] ?>" 
                            data-category="<?= $subcat['category_id'] ?>"
                            <?= ($material['subcategory_id'] ?? '') == $subcat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subcat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="difficulty_level">Difficulty Level</label>
                <select id="difficulty_level" name="difficulty_level">
                    <option value="beginner" <?= ($material['difficulty_level'] ?? 'beginner') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= ($material['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= ($material['difficulty_level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?= !empty($material['featured']) ? 'checked' : '' ?>>
                    <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-star"/></svg> Featured (show on homepage)
                </label>
                <label>
                    <input type="checkbox" name="essential" value="1" <?= !empty($material['essential']) ? 'checked' : '' ?>>
                    <svg class="admin-icon" width="12" height="12" aria-hidden="true"><use xlink:href="#icon-check"/></svg> Essential (core material)
                <?php
                // Redirigir al módulo CdC
                header('Location: /admin/materiales/edit.php' . (isset($_GET['id']) ? ('?id=' . urlencode($_GET['id'])) : ''));
                exit;
                <div class="specs-rows">
                    <!-- Rows will be rendered by JavaScript -->
                </div>
                <div class="specs-actions">
                    <button type="button" id="add-spec-row" class="btn btn-secondary">+ Add row</button>
                    <small class="specs-help">Enter a label and value for each specification. Examples: capacity: 32 oz, material: borosilicate glass</small>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="maintenance_care">Maintenance & Care Instructions</label>
            <textarea id="maintenance_care" name="maintenance_care" rows="4" placeholder="How to clean, calibrate, and maintain this equipment..."><?= htmlspecialchars($material['maintenance_care'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="form-section">
        <h2>Description & Uses</h2>
        
        <div class="form-group">
            <label for="description">Description <span class="required">*</span></label>
            <textarea id="description" name="description" rows="6" required placeholder="What is this material and what does it do? Write clearly for homesteaders and beginners..."><?= htmlspecialchars($material['description'] ?? '') ?></textarea>
            <small>Main description of the material (required)</small>
        </div>
        
        <div class="form-group">
            <label for="traditional_uses">Traditional Uses</label>
            <textarea id="traditional_uses" name="traditional_uses" rows="4" placeholder="Historical homesteading applications..."><?= htmlspecialchars($material['traditional_uses'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="modern_applications">Modern Applications</label>
            <textarea id="modern_applications" name="modern_applications" rows="4" placeholder="Current uses and applications..."><?= htmlspecialchars($material['modern_applications'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Safety & Storage -->
    <div class="form-section">
        <h2>Safety & Storage</h2>
        
        <div class="form-group">
            <label for="safety_notes">Safety Notes & Warnings</label>
            <textarea id="safety_notes" name="safety_notes" rows="4" placeholder="Safety precautions, hazards, protective equipment needed..."><?= htmlspecialchars($material['safety_notes'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="storage_instructions">Storage Instructions</label>
            <textarea id="storage_instructions" name="storage_instructions" rows="3" placeholder="How to store properly..."><?= htmlspecialchars($material['storage_instructions'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Purchase Information -->
    <div class="form-section">
        <h2>Purchase Information</h2>
        
        <div class="form-group">
            <label for="purchase_url">Purchase Link</label>
            <input type="url" id="purchase_url" name="purchase_url"
                   value="<?= htmlspecialchars($material['purchase_url'] ?? '') ?>"
                   placeholder="https://shop.chemicalstore.com/product-name">
            <small>Direct link where customers can buy this material</small>
        </div>
        
        <div class="form-group">
            <label for="abantecart_embed_code">AbanteCart Embed Code (Complete HTML)</label>
            <textarea id="abantecart_embed_code" name="abantecart_embed_code" rows="10" placeholder="<script>...</script><div class='abantecart-widget'>...</div>"><?= htmlspecialchars($material['abantecart_embed_code'] ?? '') ?></textarea>
            <small>Paste the complete AbanteCart widget HTML code here (optional)</small>
        </div>
    </div>
    
    <!-- Media -->
    <div class="form-section">
        <h2>Images</h2>
        
        <div class="form-group">
            <label for="image_url">Main Image URL</label>
            <input type="url" id="image_url" name="image_url"
                   value="<?= htmlspecialchars($material['image_url'] ?? '') ?>"
                   placeholder="https://...">
        </div>
        
        <div class="form-group">
            <label for="gallery_images">Gallery Images (one URL per line)</label>
            <textarea id="gallery_images" name="gallery_images" rows="4" placeholder="https://...&#10;https://...&#10;https://..."><?= $material ? implode("\n", json_decode($material['gallery_images'] ?? '[]', true)) : '' ?></textarea>
        </div>
    </div>
    
    <!-- SEO -->
    <div class="form-section">
        <h2>SEO Settings</h2>
        
        <div class="form-group">
            <label for="seo_title">SEO Title (leave blank to use common name)</label>
            <input type="text" id="seo_title" name="seo_title" maxlength="255"
                   value="<?= htmlspecialchars($material['seo_title'] ?? '') ?>">
            <small>Max 60 characters recommended</small>
        </div>
        
        <div class="form-group">
            <label for="seo_description">SEO Description</label>
            <textarea id="seo_description" name="seo_description" rows="3" maxlength="255"><?= htmlspecialchars($material['seo_description'] ?? '') ?></textarea>
            <small>Max 160 characters recommended</small>
        </div>
        
        <div class="form-group">
            <label for="canonical_url">Canonical URL (optional)</label>
            <input type="url" id="canonical_url" name="canonical_url"
                   value="<?= htmlspecialchars($material['canonical_url'] ?? '') ?>">
        </div>
    </div>
    
    <!-- Publishing -->
    <div class="form-section">
        <h2>Publishing</h2>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= ($material['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($material['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="discontinued" <?= ($material['status'] ?? '') === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
            </select>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $is_edit ? '💾 Update Material' : '➕ Create Material' ?>
        </button>
        <a href="materials.php" class="btn btn-secondary">Cancel</a>
        <?php if ($is_edit): ?>
        <a href="material-delete.php?id=<?= $material_id ?>" class="btn btn-danger" 
           onclick="return confirm('Are you sure you want to delete this material?')">
            <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-trash"/></svg> Delete Material
        </a>
        <?php endif; ?>
    </div>
    
</form>

<script>
// Category-specific field visibility
function updateSubcategories() {
    const categoryId = document.getElementById('category_id').value;
    const subcategorySelect = document.getElementById('subcategory_id');
    
    // Show/hide subcategories based on selected category
    const options = subcategorySelect.querySelectorAll('option');
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
            return;
        }
        const optionCategory = option.getAttribute('data-category');
        option.style.display = (optionCategory === categoryId) ? 'block' : 'none';
    });
    
    // Reset subcategory selection if current selection doesn't match category
    const currentSubcat = subcategorySelect.value;
    if (currentSubcat) {
        const currentOption = subcategorySelect.querySelector(`option[value="${currentSubcat}"]`);
        if (currentOption && currentOption.getAttribute('data-category') !== categoryId) {
            subcategorySelect.value = '';
        }
    }
    
    // Show/hide category-specific fields
    updateCategoryFields(categoryId);
}

function updateCategoryFields(categoryId) {
    const categorySlug = getCategorySlug(categoryId);
    
    // Hide all category-specific sections
    document.querySelectorAll('.category-specific').forEach(el => {
        el.style.display = 'none';
    });
    
    // Show relevant sections
    if (categorySlug === 'substance') {
        document.getElementById('substance-fields').style.display = 'block';
    }
    
    if (['equipment', 'tool', 'container'].includes(categorySlug)) {
        document.getElementById('equipment-fields').style.display = 'block';
    }
}

function getCategorySlug(categoryId) {
    const categories = <?= json_encode(array_column($categories, 'slug', 'id')) ?>;
    return categories[categoryId] || '';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSubcategories();
});

// Specifications editor: key/value UI
(function() {
    const specsEditor = document.getElementById('specs-editor');
    if (!specsEditor) return;

    const hiddenInput = document.getElementById('specifications');
    const rowsContainer = specsEditor.querySelector('.specs-rows');
    const addBtn = document.getElementById('add-spec-row');

    function createRow(key = '', value = '') {
        const row = document.createElement('div');
        row.className = 'spec-row';
        row.innerHTML = `
            <input type="text" class="spec-key" placeholder="Label (e.g. capacity)" value="${escapeHtml(key)}" />
            <input type="text" class="spec-value" placeholder="Value (e.g. 32 oz)" value="${escapeHtml(value)}" />
            <button type="button" class="btn btn-danger remove-spec">✕</button>
        `;
        row.querySelector('.remove-spec').addEventListener('click', () => { row.remove(); syncToHidden(); });
        row.querySelectorAll('input').forEach(inp => inp.addEventListener('input', syncToHidden));
        return row;
    }

    function renderInitial() {
        const data = JSON.parse(specsEditor.getAttribute('data-initial') || '{}');
        rowsContainer.innerHTML = '';
        if (Object.keys(data).length === 0) {
            rowsContainer.appendChild(createRow('',''));
        } else {
            Object.keys(data).forEach(k => rowsContainer.appendChild(createRow(k, data[k])));
        }
        syncToHidden();
    }

    function syncToHidden() {
        const rows = rowsContainer.querySelectorAll('.spec-row');
        const out = {};
        rows.forEach(r => {
            const k = r.querySelector('.spec-key').value.trim();
            const v = r.querySelector('.spec-value').value.trim();
            if (k) out[k] = v;
        });
        hiddenInput.value = JSON.stringify(out);
    }

    addBtn.addEventListener('click', () => { rowsContainer.appendChild(createRow('','')); });

    // Escape helper for values inserted into innerHTML
    function escapeHtml(str) {
        return (str+'').replace(/[&<>"']/g, function(s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[s];
        });
    }

    // Ensure serialization before form submit
    const form = document.querySelector('.material-form');
    form.addEventListener('submit', function() { syncToHidden(); });

    renderInitial();
})();

// Auto-generate slug from common name
document.getElementById('common_name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value || slugField.dataset.autoGenerated) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugField.value = slug;
        slugField.dataset.autoGenerated = 'true';
    }
});

document.getElementById('slug').addEventListener('input', function() {
    if (this.value) {
        this.dataset.autoGenerated = 'false';
    }
});
</script>

<style>
/* Compact admin form styles: reduced paddings, gaps and font sizes to save vertical space */
.material-form {
    max-width: 1100px;
    margin: 0 auto;
}

.form-section {
    background: white;
    border: 1px solid #e6e6e6;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.form-section h2 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    color: #2c5f2d;
    font-size: 1.1rem;
    font-weight: 600;
    border-bottom: 1px solid #e6e6e6;
    padding-bottom: 0.375rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 0.75rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.35rem;
    color: #333;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    font-size: 0.95rem;
    font-family: inherit;
}

.form-group textarea { resize: vertical; }

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.825rem;
}

.required { color: #dc3545; }

.checkbox-group { display: flex; flex-direction: row; gap: 1rem; align-items: center; }
.checkbox-group label { display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; }

.form-actions { display: flex; gap: 0.75rem; padding: 0.75rem; background: #fafafa; border-radius: 6px; margin-bottom: 1rem; }

.alert { padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; }
.alert-error { background: #fff0f0; border: 1px solid #f5c6cb; color: #721c24; }
.alert ul { margin: 0.25rem 0 0 1.25rem; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .checkbox-group { flex-direction: column; align-items: flex-start; }
}

/* Specs editor styling (compact) */
.specs-rows { display: grid; gap: 0.4rem; margin-bottom: 0.5rem; }
.spec-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.4rem; align-items: center; }
.spec-row input { padding: 0.4rem; border: 1px solid #e6e6e6; border-radius: 4px; }
.specs-actions { display: flex; gap: 0.5rem; align-items: center; }
.specs-help { color: #666; font-size: 0.8rem; }

@media (max-width: 600px) {
    .spec-row { grid-template-columns: 1fr; }
    .spec-row .remove-spec { justify-self: start; }
}
</style>

<?php include 'footer.php'; ?>
