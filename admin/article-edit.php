<?php
require_once '../config.php';
require_once 'auth.php';

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $article_id === 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $excerpt = trim($_POST['excerpt']);
    $section_id = (int)$_POST['section_id'];
    $format = $_POST['format'];
    $difficulty = $_POST['difficulty'];
    $read_time_min = (int)$_POST['read_time_min'];
    $author = trim($_POST['author']);
    $body = $_POST['body'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $published_at = $_POST['published_at'] ?: date('Y-m-d H:i:s');
    
    // Tags, seasons
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    $seasons = isset($_POST['seasons']) ? $_POST['seasons'] : [];
    
    // Materials
    $materials = isset($_POST['materials']) ? $_POST['materials'] : [];
    $material_quantities = isset($_POST['material_quantity']) ? $_POST['material_quantity'] : [];
    $material_optional = isset($_POST['material_optional']) ? $_POST['material_optional'] : [];
    $material_notes = isset($_POST['material_notes']) ? $_POST['material_notes'] : [];
    
    try {
        $pdo->beginTransaction();
        
        if ($is_new) {
            // Insert new article
            $stmt = $pdo->prepare("
                INSERT INTO articles (title, slug, excerpt, section_id, format, difficulty, read_time_min, author, body, status, published_at, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $slug, $excerpt, $section_id, $format, $difficulty, $read_time_min, $author, $body, $status, $published_at, $featured]);
            $article_id = $pdo->lastInsertId();
        } else {
            // Update existing article
            $stmt = $pdo->prepare("
                UPDATE articles 
                SET title = ?, slug = ?, excerpt = ?, section_id = ?, format = ?, difficulty = ?, 
                    read_time_min = ?, author = ?, body = ?, status = ?, published_at = ?, featured = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $slug, $excerpt, $section_id, $format, $difficulty, $read_time_min, $author, $body, $status, $published_at, $featured, $article_id]);
            
            // Delete existing relationships
            $pdo->prepare("DELETE FROM article_tags WHERE article_id = ?")->execute([$article_id]);
            $pdo->prepare("DELETE FROM article_seasons WHERE article_id = ?")->execute([$article_id]);
            $pdo->prepare("DELETE FROM article_materials WHERE article_id = ?")->execute([$article_id]);
        }
        
        // Insert tags
        foreach ($tags as $tag_id) {
            $stmt = $pdo->prepare("INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$article_id, $tag_id]);
        }
        
        // Insert seasons
        foreach ($seasons as $season) {
            $stmt = $pdo->prepare("INSERT INTO article_seasons (article_id, season) VALUES (?, ?)");
            $stmt->execute([$article_id, $season]);
        }
        
        // Insert materials
        $sort_order = 1;
        foreach ($materials as $index => $material_id) {
            if (!empty($material_id)) {
                $quantity = !empty($material_quantities[$index]) ? $material_quantities[$index] : null;
                $optional = isset($material_optional[$index]) ? 1 : 0;
                $notes = !empty($material_notes[$index]) ? $material_notes[$index] : null;
                
                $stmt = $pdo->prepare("
                    INSERT INTO article_materials (article_id, material_id, quantity, optional, notes, sort_order) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$article_id, $material_id, $quantity, $optional, $notes, $sort_order++]);
            }
        }
        
        $pdo->commit();
        header("Location: articles.php?success=1");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error saving article: " . $e->getMessage();
    }
}

// Load existing article data
$article = null;
$existing_tags = [];
$existing_seasons = [];
$existing_materials = [];

if (!$is_new) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header("Location: articles.php");
        exit;
    }
    
    // Load existing relationships
    $stmt = $pdo->prepare("SELECT tag_id FROM article_tags WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $existing_tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("SELECT season FROM article_seasons WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $existing_seasons = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("
        SELECT am.*, m.common_name, m.abantecart_embed_code
        FROM article_materials am
        JOIN materials m ON am.material_id = m.id
        WHERE am.article_id = ? 
        ORDER BY am.sort_order
    ");
    $stmt->execute([$article_id]);
    $existing_materials = $stmt->fetchAll();
}

// Load all sections, tags, and materials
$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();
$all_tags = $pdo->query("SELECT * FROM tags ORDER BY name")->fetchAll();
$all_materials = $pdo->query("
    SELECT id, common_name, technical_name, category_id, abantecart_embed_code
    FROM materials 
    WHERE status = 'published' 
    ORDER BY common_name
")->fetchAll();

require_once 'header.php';
?>

            <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-article"/></svg> Content</h2>
    <div class="page-header">
        <h1><?php if ($is_new): ?><svg class="admin-icon" aria-hidden="true" width="18" height="18"><use xlink:href="#icon-plus"/></svg> New Article<?php else: ?><svg class="admin-icon" aria-hidden="true" width="18" height="18"><use xlink:href="#icon-edit"/></svg> Edit Article<?php endif; ?></h1>
        <a href="articles.php" class="btn btn-secondary"><svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-list"/></svg> Back to Articles</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="article-form">
        
        <!-- Basic Info -->
        <div class="form-section">
            <h2><svg class="admin-icon" aria-hidden="true" width="16" height="16"><use xlink:href="#icon-list"/></svg> Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?= $article ? htmlspecialchars($article['title']) : '' ?>"
                           oninput="generateSlug()">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug (URL) *</label>
                    <input type="text" id="slug" name="slug" required 
                           value="<?= $article ? htmlspecialchars($article['slug']) : '' ?>">
                    <small>Example: restoring-rusty-tools-washing-soda</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="excerpt">Excerpt (max 160 characters) *</label>
                <textarea id="excerpt" name="excerpt" required maxlength="160" rows="1"
                          oninput="updateCharCount()"><?= $article ? htmlspecialchars($article['excerpt']) : '' ?></textarea>
                <small id="char-count">0/160 chars</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="section_id">Section *</label>
                    <select id="section_id" name="section_id" required>
                        <option value="">Select...</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['id'] ?>" 
                                <?= $article && $article['section_id'] == $section['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="format">Format *</label>
                    <select id="format" name="format" required>
                        <option value="howto" <?= $article && $article['format'] === 'howto' ? 'selected' : '' ?>>How-To</option>
                        <option value="reference" <?= $article && $article['format'] === 'reference' ? 'selected' : '' ?>>Reference</option>
                        <option value="story" <?= $article && $article['format'] === 'story' ? 'selected' : '' ?>>Story</option>
                        <option value="recipe" <?= $article && $article['format'] === 'recipe' ? 'selected' : '' ?>>Recipe</option>
                    </select>
                    <small class="help-text">
                        <strong>How-To:</strong> Step-by-step tutorials and practical guides<br>
                        <strong>Reference:</strong> Technical articles and documentation<br>
                        <strong>Story:</strong> Narratives and case studies<br>
                        <strong>Recipe:</strong> Formulas and mixtures
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="difficulty">Difficulty *</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="basic" <?= $article && $article['difficulty'] === 'basic' ? 'selected' : '' ?>>Basic</option>
                        <option value="intermediate" <?= $article && $article['difficulty'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= $article && $article['difficulty'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="read_time_min">Read time (min) *</label>
                    <input type="number" id="read_time_min" name="read_time_min" required min="1"
                           value="<?= $article ? $article['read_time_min'] : 5 ?>">
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required
                           value="<?= $article ? htmlspecialchars($article['author']) : 'Staff' ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="draft" <?= $article && $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $article && $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="published_at">Published at</label>
                    <input type="datetime-local" id="published_at" name="published_at"
                           value="<?= $article && $article['published_at'] ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="featured">Featured</label>
                    <label style="display:inline-flex;align-items:center;gap:.5rem"><input type="checkbox" id="featured" name="featured" value="1" <?= (!empty($article['featured'] ?? 0)) ? 'checked' : '' ?>> Show on homepage / featured lists</label>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="form-section">
            <h2><svg class="admin-icon" aria-hidden="true" width="16" height="16"><use xlink:href="#icon-article"/></svg> Content</h2>
            <div class="form-group">
                <label for="body">Article body (HTML) *</label>
                <div id="editor-container">
                    <div id="editor-toolbar">
                        <button type="button" onclick="formatText('bold')"><b>B</b></button>
                        <button type="button" onclick="formatText('italic')"><i>I</i></button>
                        <button type="button" onclick="formatText('underline')"><u>U</u></button>
                        <button type="button" onclick="formatText('h2')">H2</button>
                        <button type="button" onclick="formatText('h3')">H3</button>
                        <button type="button" onclick="formatText('insertUnorderedList')">• List</button>
                        <button type="button" onclick="formatText('insertOrderedList')">1. List</button>
                        <button type="button" onclick="formatText('justifyLeft')" aria-label="Align left">Left</button>
                        <button type="button" onclick="formatText('justifyCenter')" aria-label="Align center">Center</button>
                        <button type="button" onclick="formatText('justifyRight')" aria-label="Align right">Right</button>
                        <button type="button" onclick="insertLink()"><svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-list"/></svg> Link</button>
                        <button type="button" onclick="toggleHTML()" id="toggle-html-btn"><svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-list"/></svg> HTML</button>
                    </div>
                    <div id="editor-content" contenteditable="true" class="html-editor" style="display: block;"><?= $article ? $article['body'] : '' ?></div>
                    <textarea id="body" name="body" required style="display: none; width: 100% !important;"><?= $article ? htmlspecialchars($article['body']) : '' ?></textarea>
                </div>
                <small id="editor-mode-hint">Write visually or edit HTML</small>
            </div>
        </div>
        
        <!-- Taxonomies -->
        <div class="form-section">
            <h2><svg class="admin-icon" aria-hidden="true" width="16" height="16"><use xlink:href="#icon-tag"/></svg> Taxonomies</h2>
            
            <div class="form-group">
                <label>Tags</label>
                <div class="checkbox-grid">
                    <?php foreach ($all_tags as $tag): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                <?= in_array($tag['id'], $existing_tags) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Seasons</label>
                <div class="checkbox-grid">
                    <?php 
                    $seasons_list = ['Spring', 'Summer', 'Fall', 'Winter'];
                    foreach ($seasons_list as $season): 
                    ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="seasons[]" value="<?= $season ?>"
                                <?= in_array($season, $existing_seasons) ? 'checked' : '' ?>>
                            <?= $season ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Materials -->
        <div class="form-section">
            <h2><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-flask"/></svg> Materials / Products</h2>
            <p style="color: #6b7280; margin-bottom: 1rem;">
                Select the materials to appear in this article. AbanteCart embed code will be inserted from the material settings.
            </p>
            <div id="materials-container">
                <?php if (!empty($existing_materials)): ?>
                    <?php foreach ($existing_materials as $index => $mat): ?>
                        <div class="material-row">
                            <select name="materials[]">
                                <option value="">Select material...</option>
                                <?php foreach ($all_materials as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $mat['material_id'] == $m['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['common_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                <input type="text" name="material_quantity[]" placeholder="Quantity (e.g. 500g, 2 cups)" 
                                   value="<?= htmlspecialchars($mat['quantity'] ?? '') ?>">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="material_optional[]" value="<?= $index ?>"
                                       <?= $mat['optional'] ? 'checked' : '' ?>>
                                Optional
                            </label>
                <input type="text" name="material_notes[]" placeholder="Additional notes" 
                    value="<?= htmlspecialchars($mat['notes'] ?? '') ?>">
                            <button type="button" class="btn-remove-material" onclick="removeMaterial(this)"><svg class="admin-icon" aria-hidden="true" width="12" height="12"><use xlink:href="#icon-close"/></svg></button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="material-row">
                        <select name="materials[]">
                            <option value="">Select material...</option>
                            <?php foreach ($all_materials as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= htmlspecialchars($m['common_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="material_quantity[]" placeholder="Quantity (e.g. 500g, 2 cups)">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="material_optional[]" value="0">
                            Optional
                        </label>
                        <input type="text" name="material_notes[]" placeholder="Additional notes">
                        <button type="button" class="btn-remove-material" onclick="removeMaterial(this)"><svg class="admin-icon" aria-hidden="true" width="12" height="12"><use xlink:href="#icon-close"/></svg></button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-secondary" onclick="addMaterial()"><svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-plus"/></svg> Add Material</button>
        </div>
        
        <!-- Actions -->
        <input type="hidden" name="after_save" id="after_save" value="" />
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('after_save').value=''">
                <svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-save"/></svg> Save Article
            </button>
            <button type="submit" class="btn btn-secondary" onclick="document.getElementById('after_save').value='create_material'">
                <svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-plus"/></svg> Save & Create Material
            </button>
            <a href="articles.php" class="btn btn-secondary">Cancel</a>
            <?php if (!$is_new): ?>
                <a href="article-delete.php?id=<?= $article_id ?>" class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this article?')"><svg class="admin-icon" aria-hidden="true" width="14" height="14"><use xlink:href="#icon-trash"/></svg> Delete</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
.article-form {
    background: white;
    padding: 1rem; /* tighter */
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.form-section {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    margin-bottom: 0.5rem;
    font-size: 1.05rem;
    color: #111;
}

.admin-icon {
    display: inline-block;
    vertical-align: -0.125em;
    margin-right: 0.35rem;
    width: 0.95em;
    height: 0.95em;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.5rem;
}

.form-group {
    margin-bottom: 0.6rem;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="url"],
.form-group input[type="datetime-local"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.4rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.92rem;
}

.form-group textarea {
    font-family: 'Courier New', monospace;
    resize: vertical;
}

/* Compact excerpt field */
#excerpt {
    min-height: 2.2rem;
    max-height: 4rem;
    padding: 0.25rem;
    line-height: 1.2;
}

/* Override para el editor HTML - NO aplicar estilos de textarea genérico */
.form-group textarea#body {
    font-family: 'Courier New', monospace;
    padding: 0;
    border: none;
    border-radius: 0;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.82rem;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.4rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.checkbox-label:hover {
    background: #f3f4f6;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
}

.material-row {
    display: grid;
    grid-template-columns: 1.6fr 0.9fr auto 1.2fr auto;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
    align-items: center;
    padding: 0.45rem;
    background: #f8fafc;
    border-radius: 4px;
    border: 1px solid #eef2f5;
}

.material-row select,
.material-row input[type="text"] {
    padding: 0.4rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.88rem;
}

.checkbox-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
    padding: 0.5rem;
}

.checkbox-inline input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.btn-remove-material {
    padding: 0.35rem 0.6rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
    font-size: 0.9rem;
}

.btn-remove-material:hover {
    background: #dc2626;
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eef2f5;
}

.btn-danger {
    background: #ef4444;
    margin-left: auto;
}

.btn-danger:hover {
    background: #dc2626;
}

.alert-error {
    padding: 1rem;
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
    border-radius: 4px;
    margin-bottom: 1rem;
}

/* HTML Editor */
#editor-container {
    width: 100%;
    max-width: 100%;
    position: relative;
    overflow: visible;
}

#editor-toolbar {
    background: #fbfbfb;
    border: 1px solid #e6e6e6;
    border-bottom: none;
    padding: 0.25rem;
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
    width: 100%;
    box-sizing: border-box;
}

#editor-toolbar button {
    padding: 0.25rem 0.5rem;
    background: white;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.12s;
}

#editor-toolbar button:hover {
    background: #f3f4f6;
    border-color: #cfcfcf;
}

#editor-toolbar button:active {
    background: #eef0f2;
}

.html-editor,
#body {
    display: block;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    min-height: 300px;
    max-height: 500px;
    padding: 0.6rem !important;
    border: 1px solid #dfe6ea !important;
    border-radius: 0 0 4px 4px !important;
    background: white;
    overflow-y: auto;
    box-sizing: border-box !important;
    margin: 0 !important;
}

.html-editor {
    font-family: Georgia, serif !important;
    font-size: 0.95rem;
    line-height: 1.5;
}

#body {
    font-family: 'Courier New', monospace !important;
    font-size: 0.88rem !important;
    line-height: 1.4;
    resize: vertical !important;
}

.html-editor:focus,
#body:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

.html-editor h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1rem 0 0.75rem 0;
    color: #111;
}

.html-editor h3 {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0.9rem 0 0.5rem 0;
    color: #111;
}

.html-editor p {
    margin-bottom: 0.75rem;
}

.html-editor ul, .html-editor ol {
    margin-left: 1.25rem;
    margin-bottom: 0.75rem;
}

.html-editor li {
    margin-bottom: 0.4rem;
}

.html-editor a {
    color: #2563eb;
    text-decoration: underline;
}

.html-editor strong {
    font-weight: 600;
}

.html-editor em {
    font-style: italic;
}
/* Hide CKEditor upgrade/security banners that may be injected by CDN builds */
.cke .cke_warning,
.cke .cke_panel_warning,
.cke .cke_browser_warning,
.cke .cke_upgrade_notice,
.cke_warning,
.cke_panel_warning {
    display: none !important;
}

/* Specifically hide the CKEditor notification element that warns about insecure versions */
.cke_notification.cke_notification_warning,
.cke_notification.cke_notification_warning .cke_notification_message,
.cke_notification.cke_notification_warning .cke_notification_close {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    overflow: hidden !important;
}
</style>

<!-- Load CKEditor 4 Classic from CDN (no API key required) -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>

<script>
function generateSlug() {
    const title = document.getElementById('title').value;
    const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
}

function updateCharCount() {
    const excerpt = document.getElementById('excerpt').value;
    document.getElementById('char-count').textContent = excerpt.length + '/160 chars';
}

function addMaterial() {
    const container = document.getElementById('materials-container');
    const currentRows = container.querySelectorAll('.material-row').length;
    const row = document.createElement('div');
    row.className = 'material-row';
    row.innerHTML = `
        <select name="materials[]">
            <option value="">Select material...</option>
            <?php foreach ($all_materials as $m): ?>
                <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['common_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="material_quantity[]" placeholder="Cantidad (ej: 500g, 2 cups)">
        <label class="checkbox-inline">
            <input type="checkbox" name="material_optional[]" value="${currentRows}">
            Opcional
        </label>
        <input type="text" name="material_notes[]" placeholder="Notas adicionales">
        <button type="button" class="btn-remove-material" onclick="removeMaterial(this)">✕</button>
    `;
    container.appendChild(row);
}
function removeMaterial(button) {
    const row = button.closest('.material-row');
    if (row) row.remove();
}

// HTML Editor Functions
let isHTMLMode = false;

function formatText(command) {
    if (isHTMLMode) return; // Do not format in HTML mode
    document.execCommand(command, false, null);
    document.getElementById('editor-content').focus();
}

function insertLink() {
    if (isHTMLMode) return; // Do not insert links in HTML mode
    const url = prompt('Enter the URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
    document.getElementById('editor-content').focus();
}

function toggleHTML() {
    const editor = document.getElementById('editor-content');
    const textarea = document.getElementById('body');
    const toggleBtn = document.getElementById('toggle-html-btn');
    const modeHint = document.getElementById('editor-mode-hint');
    
    if (!isHTMLMode) {
        // Switch to HTML mode
        syncEditorContent();
        editor.style.display = 'none';
        textarea.style.display = 'block';
        textarea.style.width = '100%';
        toggleBtn.textContent = 'Visual';
        toggleBtn.style.background = '#dbeafe';
        modeHint.textContent = 'HTML mode - edit code directly';
        isHTMLMode = true;
    } else {
        // Switch to visual mode
        editor.innerHTML = textarea.value;
        textarea.style.display = 'none';
        editor.style.display = 'block';
        editor.style.width = '100%';
        toggleBtn.textContent = 'HTML';
        toggleBtn.style.background = 'white';
        modeHint.textContent = 'Write visually or edit HTML';
        isHTMLMode = false;
    }
}

function syncEditorContent() {
    const editor = document.getElementById('editor-content');
    const textarea = document.getElementById('body');
    
    if (!isHTMLMode) {
        textarea.value = editor.innerHTML;
    } else {
        // Si estamos en modo HTML, el valor ya está en el textarea
        // No hacer nada
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCharCount();

    // Initialize CKEditor 4 Classic on the textarea
    var ckeditorLoaded = false;
    try {
        if (window.CKEDITOR) {
            CKEDITOR.replace('body', {
                height: 480,
                removePlugins: 'elementspath',
                resize_enabled: true,
                contentsCss: ['/assets/css/style.css', '/assets/css/article-content.css'],
                bodyClass: 'article-body'
            });
            ckeditorLoaded = true;

            // hide legacy visual editor toolbar and contenteditable area
            const toolbar = document.getElementById('editor-toolbar');
            const ed = document.getElementById('editor-content');
            if (toolbar) toolbar.style.display = 'none';
            if (ed) ed.style.display = 'none';
        }
    } catch (e) {
        // CKEditor failed to load — we'll keep the legacy editor
        console.warn('CKEditor not available, falling back to legacy editor.', e);
        ckeditorLoaded = false;
    }

    // Sync editor content before form submission. If CKEditor is active, get its data into the textarea; otherwise use the legacy sync.
    document.querySelector('form').addEventListener('submit', function(e) {
        if (ckeditorLoaded && window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.body) {
            CKEDITOR.instances.body.updateElement(); // copies editor content into textarea
        } else {
            syncEditorContent();
        }
    });

    // If legacy contenteditable is present, keep syncing for backward compatibility when CKEditor isn't used
    const legacyEditor = document.getElementById('editor-content');
    if (legacyEditor && !ckeditorLoaded) {
        legacyEditor.addEventListener('input', syncEditorContent);
        legacyEditor.addEventListener('blur', syncEditorContent);
    }
    
        // Hide/remove CKEditor upgrade/security warning nodes if present (best-effort)
        function removeCKEditorWarnings() {
            try {
                // Look for CKEditor UI containers
                const candidates = document.querySelectorAll('.cke, .cke_top, .cke_bottom, .cke_inner');
                candidates.forEach(node => {
                    // Remove nodes that contain typical upgrade/security text
                    const text = (node.textContent || '').toLowerCase();
                    if (text.includes('secure') || text.includes('insecure') || text.includes('upgrade') || text.includes('paid') || text.includes('license')) {
                        node.remove();
                    }
                });
            } catch (e) {
                // swallow errors — this is best-effort
                console.debug('removeCKEditorWarnings error', e);
            }
        }
    
        // Run once after a short delay to allow CDN scripts to inject banners
        setTimeout(removeCKEditorWarnings, 800);

        // Also observe the document for any inserted CKEditor notification elements and remove them quickly
        const observer = new MutationObserver((mutations, obs) => {
            let removed = false;
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node instanceof HTMLElement) {
                        if (node.matches && node.matches('.cke_notification.cke_notification_warning')) {
                            node.remove();
                            removed = true;
                        } else {
                            const found = node.querySelector && node.querySelector('.cke_notification.cke_notification_warning');
                            if (found) { found.remove(); removed = true; }
                        }
                    }
                }
            }
            // If we've removed at least once, disconnect to avoid extra work
            if (removed) obs.disconnect();
        });
        observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
});
</script>

<?php require_once 'footer.php'; ?>
