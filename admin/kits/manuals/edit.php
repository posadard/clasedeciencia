<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../../config.php';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
  try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); } catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16)); }
}

$manual_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$kit_id = isset($_GET['kit_id']) ? intval($_GET['kit_id']) : 0;
$error_msg = '';
$success_msg = '';

$manual = null;
if ($manual_id > 0) {
  $stmt = $pdo->prepare('SELECT * FROM kit_manuals WHERE id = ? LIMIT 1');
  $stmt->execute([$manual_id]);
  $manual = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($manual) $kit_id = intval($manual['kit_id']);
}

$kit = null;
if ($kit_id > 0) {
  $stmtK = $pdo->prepare('SELECT id, nombre, codigo, slug FROM kits WHERE id = ? LIMIT 1');
  $stmtK->execute([$kit_id]);
  $kit = $stmtK->fetch(PDO::FETCH_ASSOC);
}

// Detect optional column 'render_mode' in kit_manuals
$has_render_mode_column = false;
try {
  $pdo->query('SELECT render_mode FROM kit_manuals LIMIT 1');
  $has_render_mode_column = true;
  echo '<script>console.log("🔍 [ManualsEdit] Column render_mode: presente");</script>';
} catch (PDOException $e) {
  echo '<script>console.log("⚠️ [ManualsEdit] Column render_mode: ausente");</script>';
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $error_msg = 'Token CSRF inválido.';
    echo '<script>console.log("❌ [ManualsEdit] CSRF inválido");</script>';
  } else {
    $kit_id = intval($_POST['kit_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $version = trim($_POST['version'] ?? '1.0');
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $idioma = trim($_POST['idioma'] ?? 'es-CO');
    $time_minutes = ($_POST['time_minutes'] !== '' ? intval($_POST['time_minutes']) : null);
    $dificultad = trim($_POST['dificultad_ensamble'] ?? '');
    $pasos_json = trim($_POST['pasos_json'] ?? '');
    $herr_json = trim($_POST['herramientas_json'] ?? '');
    $seg_json = trim($_POST['seguridad_json'] ?? '');
    $html = $_POST['html'] ?? null;
    $ui_mode = ($_POST['ui_mode'] ?? '') === 'fullhtml' ? 'fullhtml' : 'legacy';
    $render_mode_post = ($_POST['render_mode'] ?? '') === 'fullhtml' ? 'fullhtml' : 'legacy';

    // Basic validations
    if ($kit_id <= 0) {
      $error_msg = 'Kit requerido.';
    } elseif ($slug === '') {
      $error_msg = 'Slug requerido.';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
      $error_msg = 'Slug inválido: usa a-z, 0-9 y guiones.';
    }

    // Validate JSON fields (optional empty allowed)
    $jsonErrors = [];
    $validateJson = function($raw, $label) use (&$jsonErrors) {
      if ($raw === '') return null; // treat empty as NULL
      $decoded = json_decode($raw, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $jsonErrors[] = $label;
        return null;
      }
      return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    };

    $pasos_json_db = $validateJson($pasos_json, 'pasos_json');
    $herr_json_db = $validateJson($herr_json, 'herramientas_json');
    $seg_json_db = $validateJson($seg_json, 'seguridad_json');

    if (!empty($jsonErrors)) {
      $error_msg = 'JSON inválido en: ' . implode(', ', $jsonErrors);
    }

    if (!$error_msg) {
      try {
        if ($manual_id > 0) {
          if ($has_render_mode_column) {
            $stmtU = $pdo->prepare('UPDATE kit_manuals SET slug = ?, version = ?, status = ?, idioma = ?, time_minutes = ?, dificultad_ensamble = ?, pasos_json = ?, herramientas_json = ?, seguridad_json = ?, html = ?, render_mode = ? WHERE id = ?');
            $stmtU->execute([$slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html, $render_mode_post, $manual_id]);
          } else {
            $stmtU = $pdo->prepare('UPDATE kit_manuals SET slug = ?, version = ?, status = ?, idioma = ?, time_minutes = ?, dificultad_ensamble = ?, pasos_json = ?, herramientas_json = ?, seguridad_json = ?, html = ? WHERE id = ?');
            $stmtU->execute([$slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html, $manual_id]);
          }
          $success_msg = 'Manual actualizado.';
          echo '<script>console.log("✅ [ManualsEdit] Actualizado ID=' . $manual_id . '");</script>';
        } else {
          if ($has_render_mode_column) {
            $stmtI = $pdo->prepare('INSERT INTO kit_manuals (kit_id, slug, version, status, idioma, time_minutes, dificultad_ensamble, pasos_json, herramientas_json, seguridad_json, html, render_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmtI->execute([$kit_id, $slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html, $render_mode_post]);
          } else {
            $stmtI = $pdo->prepare('INSERT INTO kit_manuals (kit_id, slug, version, status, idioma, time_minutes, dificultad_ensamble, pasos_json, herramientas_json, seguridad_json, html) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmtI->execute([$kit_id, $slug, $version, $status, $idioma, $time_minutes, ($dificultad !== '' ? $dificultad : null), $pasos_json_db, $herr_json_db, $seg_json_db, $html]);
          }
          $manual_id = intval($pdo->lastInsertId());
          $success_msg = 'Manual creado.';
          echo '<script>console.log("✅ [ManualsEdit] Creado ID=' . $manual_id . '");</script>';
        }
      } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate') !== false) {
          $error_msg = 'Slug/Idioma duplicado para este kit.';
        } else {
          $error_msg = 'Error guardando manual: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        }
        echo '<script>console.log("❌ [ManualsEdit] Error: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '");</script>';
      }
    }
  }
  // Refresh manual after save
  if ($manual_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM kit_manuals WHERE id = ? LIMIT 1');
    $stmt->execute([$manual_id]);
    $manual = $stmt->fetch(PDO::FETCH_ASSOC);
    $kit_id = intval($manual['kit_id']);
  }
}

// Load kits for selector if needed
$kits = [];
if (!$kit) {
  $stmtKs = $pdo->query('SELECT id, nombre FROM kits ORDER BY nombre ASC');
  $kits = $stmtKs->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="container">
  <h1><?= $manual ? 'Editar Manual' : 'Nuevo Manual' ?></h1>
  <?php if ($kit): ?>
    <p>Kit: <strong><?= htmlspecialchars($kit['nombre']) ?></strong> (ID <?= (int)$kit['id'] ?>)</p>
    <p><a href="/admin/kits/manuals/index.php?kit_id=<?= (int)$kit['id'] ?>">Volver a Manuales</a></p>
  <?php endif; ?>

  <?php if ($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
  <?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />

    <div class="form-group">
      <label>Kit</label>
      <?php if ($kit): ?>
        <input type="hidden" name="kit_id" value="<?= (int)$kit['id'] ?>" />
        <input type="text" value="<?= htmlspecialchars($kit['nombre']) ?>" disabled />
      <?php else: ?>
        <select name="kit_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($kits as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= ($kit_id == (int)$k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label>Slug</label>
      <input type="text" name="slug" value="<?= htmlspecialchars($manual['slug'] ?? '') ?>" required placeholder="ej. armado-basico" />
      <small>Usa a-z, 0-9 y guiones.</small>
    </div>

    <div class="form-group">
      <label>Idioma</label>
      <input type="text" name="idioma" value="<?= htmlspecialchars($manual['idioma'] ?? 'es-CO') ?>" />
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Versión</label>
        <input type="text" name="version" value="<?= htmlspecialchars($manual['version'] ?? '1.0') ?>" />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <?php $st = $manual['status'] ?? 'draft'; ?>
          <option value="draft" <?= ($st === 'draft') ? 'selected' : '' ?>>Borrador</option>
          <option value="published" <?= ($st === 'published') ? 'selected' : '' ?>>Publicado</option>
        </select>
      </div>
      <div class="form-group">
        <label>Modo de Renderizado (Frontend)</label>
        <?php $rm = isset($manual['render_mode']) ? $manual['render_mode'] : ((!empty($manual['html'])) ? 'fullhtml' : 'legacy'); ?>
        <select name="render_mode">
          <option value="legacy" <?= ($rm === 'legacy') ? 'selected' : '' ?>>Estructurado (legacy)</option>
          <option value="fullhtml" <?= ($rm === 'fullhtml') ? 'selected' : '' ?>>HTML Completo</option>
        </select>
        <small>Define el modo que usará el frontend al renderizar.</small>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tiempo (minutos)</label>
        <input type="number" name="time_minutes" value="<?= htmlspecialchars($manual['time_minutes'] ?? '') ?>" />
      </div>
      <div class="form-group">
        <label>Dificultad de Ensamble</label>
        <input type="text" name="dificultad_ensamble" value="<?= htmlspecialchars($manual['dificultad_ensamble'] ?? '') ?>" />
      </div>
    </div>

    <div class="form-group">
      <label>Modo de Manual</label>
      <div class="mode-toggle">
        <label><input type="radio" name="ui_mode" value="legacy" checked /> Estructurado (Seguridad/Herramientas/Pasos)</label>
        <label><input type="radio" name="ui_mode" value="fullhtml" /> HTML Completo (reemplaza bloques)</label>
      </div>
      <div id="mode-warning" class="help-note"></div>
    </div>

    <div class="form-group">
      <label>Pasos</label>
      <div id="steps-builder">
        <div class="steps-toolbar">
          <button type="button" class="btn btn-primary" id="add-step-btn">+ Añadir Paso</button>
          <button type="button" class="btn" id="expand-all-btn">Expandir todo</button>
          <button type="button" class="btn" id="collapse-all-btn">Colapsar todo</button>
        </div>
        <ul id="steps-list" class="steps-list"></ul>
        <p class="help-note">Los pasos se guardan como bloques HTML ordenados. Se serializan a JSON antes de enviar.</p>
      </div>
      <textarea name="pasos_json" id="pasos_json" rows="8" style="display:none;" placeholder='[ {"orden":1, "titulo":"Paso 1", "html":"<p>...</p>"} ]'><?= htmlspecialchars($manual['pasos_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Herramientas</label>
      <div id="tools-builder">
        <div class="tools-toolbar">
          <button type="button" class="btn btn-primary" id="add-tool-btn">+ Añadir Herramienta</button>
        </div>
        <ul id="tools-list" class="tools-list"></ul>
        <p class="help-note">Añade herramientas una por una. Se guardan como objetos con nombre, cantidad y notas. Se serializan a JSON antes de enviar.</p>
      </div>
      <textarea name="herramientas_json" id="herramientas_json" rows="4" style="display:none;" placeholder='[ {"nombre":"tijeras","cantidad":1,"nota":"pequeñas","seguridad":"Usar con cuidado"} ]'><?= htmlspecialchars($manual['herramientas_json'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Seguridad</label>
      <div id="security-builder">
        <div class="security-age">
          <strong>Edad segura (opcional)</strong>
          <div class="age-fields">
            <div>
              <label>Mín</label>
              <input type="number" id="sec-age-min" min="0" />
            </div>
            <div>
              <label>Máx</label>
              <input type="number" id="sec-age-max" min="0" />
            </div>
          </div>
        </div>
        <div class="security-toolbar">
          <button type="button" class="btn btn-primary" id="add-sec-note-btn">+ Añadir Nota de Seguridad</button>
        </div>
        <ul id="security-list" class="security-list"></ul>
        <p class="help-note">Añade notas de seguridad una por una. Si defines edad segura, se guardará junto a las notas.</p>
      </div>
      <textarea name="seguridad_json" id="seguridad_json" rows="4" style="display:none;" placeholder='{"edad":{"min":10,"max":14},"notas":[{"nota":"Usar gafas","categoria":"protección"}]}' ><?= htmlspecialchars($manual['seguridad_json'] ?? '') ?></textarea>
    </div>


    <div class="form-group" id="html-group">
      <label>Contenido HTML</label>
      <textarea name="html" id="html-textarea" rows="10" placeholder="Contenido enriquecido del manual (opcional)"><?= htmlspecialchars($manual['html'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:12px;">
      <button type="submit" class="btn btn-primary">Guardar</button>
      <?php if ($manual): ?>
        <a class="btn" href="/admin/kits/manuals/index.php?kit_id=<?= (int)$kit_id ?>">Cancelar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>
<script>
console.log('🔍 [ManualsEdit] Manual ID:', <?= (int)$manual_id ?>, 'Kit ID:', <?= (int)$kit_id ?>);

// --- Step Builder (CKEditor via CDN, no installs) ---
(function() {
  // Mode toggle logic
  const modeRadios = Array.from(document.querySelectorAll('input[name="ui_mode"]'));
  const htmlGroup = document.getElementById('html-group');
  const htmlTextarea = document.getElementById('html-textarea');
  const modeWarning = document.getElementById('mode-warning');
  const blocks = [document.getElementById('steps-builder'), document.getElementById('tools-builder'), document.getElementById('security-builder')];

  function applyMode(mode) {
    if (mode === 'fullhtml') {
      modeWarning.textContent = '⚠️ Modo HTML completo activo: se reemplazarán Seguridad, Herramientas y Pasos.';
      blocks.forEach(b => { if (b) b.classList.add('hidden-block'); });
      htmlGroup.classList.remove('hidden-block');
      console.log('⚠️ [ManualsEdit] Modo: fullhtml');
    } else {
      modeWarning.textContent = 'ℹ️ Modo estructurado: el campo HTML será ignorado al renderizar.';
      blocks.forEach(b => { if (b) b.classList.remove('hidden-block'); });
      htmlGroup.classList.add('hidden-block');
      console.log('ℹ️ [ManualsEdit] Modo: legacy');
    }
  }

  modeRadios.forEach(r => r.addEventListener('change', () => applyMode(r.value)));
  // Initial mode: if HTML has content, default to fullhtml
  const initialMode = <?= json_encode(isset($manual['render_mode']) ? ($manual['render_mode'] === 'fullhtml' ? 'fullhtml' : 'legacy') : ((!empty($manual['html'])) ? 'fullhtml' : 'legacy')) ?>;
  modeRadios.forEach(r => { r.checked = (r.value === initialMode); });
  applyMode(initialMode);

  const pasosTextarea = document.getElementById('pasos_json');
  const stepsList = document.getElementById('steps-list');
  const addBtn = document.getElementById('add-step-btn');
  const expandBtn = document.getElementById('expand-all-btn');
  const collapseBtn = document.getElementById('collapse-all-btn');

  let steps = [];
  let editorInstance = null;
  let modalState = { mode: 'create', index: -1 };

  function safeParseJSON(raw) {
    try { return raw ? JSON.parse(raw) : []; } catch (e) { console.log('⚠️ [ManualsEdit] JSON pasos inválido, se reinicia:', e.message); return []; }
  }

  function ensureModal() {
    let modal = document.getElementById('step-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'step-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Editar Paso</h3>
          <label>Título</label>
          <input type="text" id="modal-step-title" />
          <label>Contenido (HTML enriquecido)</label>
          <textarea id="modal-step-html" rows="10"></textarea>
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="modal-save-btn">Guardar Paso</button>
            <button type="button" class="btn" id="modal-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      const saveBtn = document.getElementById('modal-save-btn');
      const cancelBtn = document.getElementById('modal-cancel-btn');
      saveBtn.addEventListener('click', function(){ saveEditorModal(); });
      cancelBtn.addEventListener('click', function(){ closeEditorModal(); });
      console.log('✅ [ManualsEdit] Modal creado');
    }
    return modal;
  }

  function normalizeStep(step, idx) {
    const orden = (typeof step.orden === 'number' ? step.orden : (idx + 1));
    const titulo = (step.titulo && String(step.titulo).trim()) || ('Paso ' + orden);
    // Map posible "descripcion" a "html"
    let html = step.html || '';
    if (!html && step.descripcion) {
      html = '<p>' + String(step.descripcion).replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>';
    }
    return { orden, titulo, html };
  }

  function sortByOrden(a, b) { return (a.orden || 0) - (b.orden || 0); }

  function renumber() {
    steps.forEach((s, i) => { s.orden = i + 1; });
  }

  function renderSteps() {
    steps.sort(sortByOrden);
    stepsList.innerHTML = '';
    steps.forEach((s, i) => {
      const li = document.createElement('li');
      li.className = 'step-item';
      li.setAttribute('data-index', String(i));

      const header = document.createElement('div');
      header.className = 'step-header';
      header.innerHTML = `
        <span class="step-order">#${s.orden}</span>
        <span class="step-title">${escapeHTML(s.titulo)}</span>
        <div class="step-actions">
          <button type="button" class="btn btn-sm" data-action="up">↑</button>
          <button type="button" class="btn btn-sm" data-action="down">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit">Editar</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete">Eliminar</button>
          <button type="button" class="btn btn-sm" data-action="toggle">Mostrar/Ocultar</button>
        </div>
      `;

      const body = document.createElement('div');
      body.className = 'step-body';
      body.innerHTML = s.html || '<p class="muted">(Sin contenido)</p>';

      li.appendChild(header);
      li.appendChild(body);
      stepsList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizados', steps.length, 'pasos');
  }

  function escapeHTML(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function openEditorModal(initialTitle, initialHTML, mode, index) {
    modalState = { mode, index };
    ensureModal();
    document.getElementById('modal-step-title').value = initialTitle || '';
    const ta = document.getElementById('modal-step-html');
    ta.value = initialHTML || '';
    if (editorInstance) { try { editorInstance.destroy(); } catch(e) {} editorInstance = null; }
    if (window.CKEDITOR) {
      editorInstance = CKEDITOR.replace('modal-step-html');
      console.log('🔍 [ManualsEdit] CKEditor inicializado (modo:', mode, ', idx:', index, ')');
    } else {
      console.log('⚠️ [ManualsEdit] CKEditor no cargado aún');
    }
    document.getElementById('step-modal').style.display = 'block';
  }

  function closeEditorModal() {
    document.getElementById('step-modal').style.display = 'none';
    if (editorInstance) { try { editorInstance.destroy(); } catch(e) {} editorInstance = null; }
  }

  function saveEditorModal() {
    const title = document.getElementById('modal-step-title').value.trim() || 'Paso';
    let html = document.getElementById('modal-step-html').value;
    if (editorInstance && editorInstance.getData) {
      html = editorInstance.getData();
    }
    if (modalState.mode === 'create') {
      steps.push({ orden: steps.length + 1, titulo: title, html });
      console.log('✅ [ManualsEdit] Paso creado');
    } else if (modalState.mode === 'edit' && modalState.index >= 0) {
      steps[modalState.index].titulo = title;
      steps[modalState.index].html = html;
      console.log('✅ [ManualsEdit] Paso actualizado idx', modalState.index);
    }
    renumber();
    renderSteps();
    closeEditorModal();
  }

  stepsList.addEventListener('click', function(ev) {
    const btn = ev.target.closest('button');
    if (!btn) return;
    const li = ev.target.closest('.step-item');
    const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) {
      const tmp = steps[idx-1]; steps[idx-1] = steps[idx]; steps[idx] = tmp; renumber(); renderSteps();
      console.log('🔍 [ManualsEdit] Paso movido arriba idx', idx);
    } else if (action === 'down' && idx < steps.length - 1) {
      const tmp = steps[idx+1]; steps[idx+1] = steps[idx]; steps[idx] = tmp; renumber(); renderSteps();
      console.log('🔍 [ManualsEdit] Paso movido abajo idx', idx);
    } else if (action === 'delete') {
      if (confirm('¿Eliminar este paso?')) { steps.splice(idx, 1); renumber(); renderSteps(); console.log('✅ [ManualsEdit] Paso eliminado idx', idx); }
    } else if (action === 'edit') {
      openEditorModal(steps[idx].titulo, steps[idx].html, 'edit', idx);
    } else if (action === 'toggle') {
      const body = li.querySelector('.step-body');
      body.style.display = (body.style.display === 'none') ? '' : 'none';
    }
  });

  addBtn.addEventListener('click', function() { openEditorModal('', '', 'create', -1); });
  expandBtn.addEventListener('click', function(){ document.querySelectorAll('.step-body').forEach(el => el.style.display = ''); });
  collapseBtn.addEventListener('click', function(){ document.querySelectorAll('.step-body').forEach(el => el.style.display = 'none'); });

  // Before submit: serialize steps -> textarea
  const form = document.querySelector('form');
  form.addEventListener('submit', function() {
    const payload = steps.map((s, i) => ({ orden: i+1, titulo: s.titulo, html: s.html }));
    pasosTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado pasos_json bytes:', pasosTextarea.value.length);
  });

  // Initialize from existing JSON
  steps = safeParseJSON(pasosTextarea.value).map(normalizeStep);
  renumber();
  renderSteps();
})();

// --- Security Builder ---
(function(){
  const secTextarea = document.getElementById('seguridad_json');
  const secList = document.getElementById('security-list');
  const addBtn = document.getElementById('add-sec-note-btn');
  const ageMinInput = document.getElementById('sec-age-min');
  const ageMaxInput = document.getElementById('sec-age-max');

  let notes = [];

  function safeParse(raw){ try { return raw ? JSON.parse(raw) : null; } catch(e){ console.log('⚠️ [ManualsEdit] JSON seguridad inválido:', e.message); return null; } }
  function escapeHTML(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function normalizeNote(n){
    if (typeof n === 'string') return { nota: n, categoria: '' };
    if (n && typeof n === 'object') return { nota: (n.nota ? String(n.nota) : ''), categoria: (n.categoria ? String(n.categoria) : '') };
    return { nota: '', categoria: '' };
  }

  function render(){
    secList.innerHTML = '';
    notes.forEach((n, i) => {
      const li = document.createElement('li');
      li.className = 'sec-item';
      li.setAttribute('data-index', String(i));
      const header = document.createElement('div');
      header.className = 'sec-header';
      header.innerHTML = `
        <span class="sec-title">${escapeHTML(n.nota || '(sin texto)')}</span>
        ${n.categoria ? `<span class="muted">(${escapeHTML(n.categoria)})</span>` : ''}
        <div class="sec-actions">
          <button type="button" class="btn btn-sm" data-action="up">↑</button>
          <button type="button" class="btn btn-sm" data-action="down">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit">Editar</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete">Eliminar</button>
        </div>`;
      li.appendChild(header);
      secList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizadas', notes.length, 'notas de seguridad');
  }

  function openModal(initial, mode, index){
    ensureSecModal();
    document.getElementById('sec-note-text').value = initial?.nota || '';
    const catSelect = document.getElementById('sec-note-cat-select');
    const customWrap = document.getElementById('sec-note-cat-custom-wrap');
    const catInput = document.getElementById('sec-note-cat');
    const catVal = (initial?.categoria || '').toLowerCase();
    const options = Array.from(catSelect.options).map(o => o.value);
    if (options.includes(catVal)) {
      catSelect.value = catVal;
      customWrap.style.display = (catVal === 'otro') ? '' : 'none';
      if (catVal !== 'otro') catInput.value = '';
    } else if (catVal) {
      catSelect.value = 'otro';
      customWrap.style.display = '';
      catInput.value = initial?.categoria || '';
    } else {
      catSelect.value = '';
      customWrap.style.display = 'none';
      catInput.value = '';
    }
    const modal = document.getElementById('sec-modal');
    modal.dataset.mode = mode;
    modal.dataset.index = String(index);
    modal.style.display = 'flex';
  }
  function closeModal(){ document.getElementById('sec-modal').style.display = 'none'; }
  function saveModal(){
    const text = document.getElementById('sec-note-text').value.trim();
    const catSelect = document.getElementById('sec-note-cat-select');
    const catInput = document.getElementById('sec-note-cat');
    let cat = '';
    if (catSelect.value === 'otro') {
      cat = catInput.value.trim();
    } else {
      cat = catSelect.value.trim();
    }
    if (!text) { alert('Texto de nota requerido'); return; }
    const modal = document.getElementById('sec-modal');
    const mode = modal.dataset.mode;
    const idx = parseInt(modal.dataset.index, 10);
    const obj = { nota: text, categoria: cat };
    if (mode === 'create') { notes.push(obj); console.log('✅ [ManualsEdit] Nota de seguridad creada'); }
    else if (mode === 'edit' && idx >= 0) { notes[idx] = obj; console.log('✅ [ManualsEdit] Nota de seguridad actualizada idx', idx); }
    render();
    closeModal();
  }

  secList.addEventListener('click', function(ev){
    const btn = ev.target.closest('button'); if (!btn) return;
    const li = ev.target.closest('.sec-item'); const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) { const tmp = notes[idx-1]; notes[idx-1] = notes[idx]; notes[idx] = tmp; render(); }
    else if (action === 'down' && idx < notes.length - 1) { const tmp = notes[idx+1]; notes[idx+1] = notes[idx]; notes[idx] = tmp; render(); }
    else if (action === 'edit') { openModal(notes[idx], 'edit', idx); }
    else if (action === 'delete') { if (confirm('¿Eliminar nota?')) { notes.splice(idx,1); render(); } }
  });

  addBtn.addEventListener('click', function(){ openModal({ nota:'', categoria:'' }, 'create', -1); });

  const form = document.querySelector('form');
  form.addEventListener('submit', function(){
    const minRaw = ageMinInput.value.trim(); const maxRaw = ageMaxInput.value.trim();
    const min = minRaw === '' ? null : parseInt(minRaw,10);
    const max = maxRaw === '' ? null : parseInt(maxRaw,10);
    let payload = null;
    if (min !== null || max !== null) {
      payload = { edad: { }, notas: notes.map(n => ({ nota: n.nota, categoria: n.categoria })) };
      if (min !== null) payload.edad.min = min;
      if (max !== null) payload.edad.max = max;
    } else {
      payload = notes.map(n => ({ nota: n.nota, categoria: n.categoria }));
    }
    secTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado seguridad_json bytes:', secTextarea.value.length);
  });

  // Initialize from existing JSON
  (function init(){
    const raw = safeParse(secTextarea.value);
    if (raw && typeof raw === 'object' && !Array.isArray(raw) && (raw.edad || raw.notas)) {
      if (raw.edad) {
        if (typeof raw.edad.min !== 'undefined') ageMinInput.value = String(raw.edad.min);
        if (typeof raw.edad.max !== 'undefined') ageMaxInput.value = String(raw.edad.max);
      }
      const ns = Array.isArray(raw.notas) ? raw.notas : [];
      notes = ns.map(normalizeNote);
    } else {
      const arr = Array.isArray(raw) ? raw : [];
      notes = arr.map(normalizeNote);
    }
    render();
  })();

  function ensureSecModal(){
    let modal = document.getElementById('sec-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'sec-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Nota de Seguridad</h3>
          <label>Texto</label>
          <input type="text" id="sec-note-text" />
          <label>Tipo de riesgo</label>
          <select id="sec-note-cat-select">
            <option value="">-- Seleccionar --</option>
            <option value="protección personal">Protección personal</option>
            <option value="corte">Corte</option>
            <option value="químico">Químico</option>
            <option value="eléctrico">Eléctrico</option>
            <option value="calor/fuego">Calor/Fuego</option>
            <option value="biológico">Biológico</option>
            <option value="presión/golpe">Presión/Golpe</option>
            <option value="entorno/ventilación">Entorno/Ventilación</option>
            <option value="supervisión adulta">Supervisión adulta</option>
            <option value="residuos/reciclaje">Residuos/Reciclaje</option>
            <option value="otro">Otro…</option>
          </select>
          <div id="sec-note-cat-custom-wrap" style="display:none;">
            <label>Otro (especifica)</label>
            <input type="text" id="sec-note-cat" />
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="sec-save-btn">Guardar</button>
            <button type="button" class="btn" id="sec-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      document.getElementById('sec-save-btn').addEventListener('click', saveModal);
      document.getElementById('sec-cancel-btn').addEventListener('click', closeModal);
      const catSelect = document.getElementById('sec-note-cat-select');
      const customWrap = document.getElementById('sec-note-cat-custom-wrap');
      catSelect.addEventListener('change', function(){
        customWrap.style.display = (catSelect.value === 'otro') ? '' : 'none';
      });
      console.log('✅ [ManualsEdit] Security modal creado');
    }
    return modal;
  }
})();

// --- Tools Builder ---
(function(){
  const toolsTextarea = document.getElementById('herramientas_json');
  const toolsList = document.getElementById('tools-list');
  const addToolBtn = document.getElementById('add-tool-btn');
  let tools = [];
  let toolModalState = { mode: 'create', index: -1 };

  function safeParseTools(raw){
    try { return raw ? JSON.parse(raw) : []; } catch(e){ console.log('⚠️ [ManualsEdit] JSON herramientas inválido, se reinicia:', e.message); return []; }
  }

  function isAssocArray(a){ return Array.isArray(a) ? (Object.keys(a).some(k => isNaN(parseInt(k,10)))) : false; }

  function escapeHTML(str){
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function normalizeTool(t){
    if (typeof t === 'string') return { nombre: t, cantidad: 1 };
    if (Array.isArray(t)) return { nombre: JSON.stringify(t), cantidad: 1 };
    if (t && typeof t === 'object') {
      return {
        nombre: (t.nombre ? String(t.nombre) : ''),
        cantidad: (t.cantidad !== undefined && t.cantidad !== null ? t.cantidad : ''),
        nota: (t.nota ? String(t.nota) : ''),
        seguridad: (t.seguridad ? String(t.seguridad) : '')
      };
    }
    return { nombre: '', cantidad: '', nota: '', seguridad: '' };
  }

  function renderTools(){
    toolsList.innerHTML = '';
    tools.forEach((t, i) => {
      const li = document.createElement('li');
      li.className = 'tool-item';
      li.setAttribute('data-index', String(i));
      const header = document.createElement('div');
      header.className = 'tool-header';
      header.innerHTML = `
        <span class="tool-title">${escapeHTML(t.nombre || '(sin nombre)')}</span>
        <div class="tool-actions">
          <button type="button" class="btn btn-sm" data-action="up">↑</button>
          <button type="button" class="btn btn-sm" data-action="down">↓</button>
          <button type="button" class="btn btn-sm" data-action="edit">Editar</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete">Eliminar</button>
        </div>`;
      const body = document.createElement('div');
      body.className = 'tool-body';
      const qty = (t.cantidad !== undefined && t.cantidad !== null && String(t.cantidad).trim() !== '') ? `Cantidad: ${escapeHTML(String(t.cantidad))}` : '';
      const nota = (t.nota ? `Nota: ${escapeHTML(t.nota)}` : '');
      const seg = (t.seguridad ? `⚠️ Seguridad: ${escapeHTML(t.seguridad)}` : '');
      const parts = [qty, nota, seg].filter(Boolean);
      body.innerHTML = parts.length ? parts.map(p => `<div>${p}</div>`).join('') : '<div class="muted">(sin detalles)</div>';
      li.appendChild(header);
      li.appendChild(body);
      toolsList.appendChild(li);
    });
    console.log('✅ [ManualsEdit] Renderizadas', tools.length, 'herramientas');
  }

  function openToolModal(initial, mode, index){
    toolModalState = { mode, index };
    ensureToolModal();
    document.getElementById('tool-name').value = initial?.nombre || '';
    document.getElementById('tool-qty').value = (initial?.cantidad !== undefined && initial?.cantidad !== null) ? String(initial.cantidad) : '';
    document.getElementById('tool-note').value = initial?.nota || '';
    document.getElementById('tool-sec').value = initial?.seguridad || '';
    document.getElementById('tool-modal').style.display = 'flex';
  }

  function closeToolModal(){ document.getElementById('tool-modal').style.display = 'none'; }

  function saveToolModal(){
    const nombre = document.getElementById('tool-name').value.trim();
    const cantidadRaw = document.getElementById('tool-qty').value.trim();
    const nota = document.getElementById('tool-note').value.trim();
    const seguridad = document.getElementById('tool-sec').value.trim();
    const cantidad = (cantidadRaw === '' ? '' : (/^\d+$/.test(cantidadRaw) ? parseInt(cantidadRaw,10) : cantidadRaw));
    const obj = { nombre, cantidad, nota, seguridad };
    if (!nombre) { alert('Nombre requerido'); return; }
    if (toolModalState.mode === 'create') {
      tools.push(obj);
      console.log('✅ [ManualsEdit] Herramienta creada');
    } else if (toolModalState.mode === 'edit' && toolModalState.index >= 0) {
      tools[toolModalState.index] = obj;
      console.log('✅ [ManualsEdit] Herramienta actualizada idx', toolModalState.index);
    }
    renderTools();
    closeToolModal();
  }

  toolsList.addEventListener('click', function(ev){
    const btn = ev.target.closest('button');
    if (!btn) return;
    const li = ev.target.closest('.tool-item');
    const idx = parseInt(li.getAttribute('data-index'), 10);
    const action = btn.getAttribute('data-action');
    if (action === 'up' && idx > 0) { const tmp = tools[idx-1]; tools[idx-1] = tools[idx]; tools[idx] = tmp; renderTools(); console.log('🔍 [ManualsEdit] Herramienta arriba idx', idx); }
    else if (action === 'down' && idx < tools.length - 1) { const tmp = tools[idx+1]; tools[idx+1] = tools[idx]; tools[idx] = tmp; renderTools(); console.log('🔍 [ManualsEdit] Herramienta abajo idx', idx); }
    else if (action === 'delete') { if (confirm('¿Eliminar herramienta?')) { tools.splice(idx, 1); renderTools(); console.log('✅ [ManualsEdit] Herramienta eliminada idx', idx); } }
    else if (action === 'edit') { openToolModal(tools[idx], 'edit', idx); }
  });

  addToolBtn.addEventListener('click', function(){ openToolModal({ nombre:'', cantidad:'', nota:'', seguridad:'' }, 'create', -1); });

  // Serialize on submit
  const form = document.querySelector('form');
  form.addEventListener('submit', function(){
    const payload = tools.map(t => ({ nombre: t.nombre, cantidad: t.cantidad, nota: t.nota, seguridad: t.seguridad }));
    toolsTextarea.value = JSON.stringify(payload);
    console.log('📦 [ManualsEdit] Serializado herramientas_json bytes:', toolsTextarea.value.length);
  });

  // Initialize
  tools = safeParseTools(toolsTextarea.value).map(normalizeTool);
  renderTools();

  function ensureToolModal(){
    let modal = document.getElementById('tool-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'tool-modal';
      modal.style.display = 'none';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content">
          <h3>Herramienta</h3>
          <label>Nombre</label>
          <input type="text" id="tool-name" />
          <label>Cantidad (número o texto)</label>
          <input type="text" id="tool-qty" />
          <label>Nota</label>
          <input type="text" id="tool-note" />
          <label>Nota de Seguridad</label>
          <input type="text" id="tool-sec" />
          <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="tool-save-btn">Guardar</button>
            <button type="button" class="btn" id="tool-cancel-btn">Cancelar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      document.getElementById('tool-save-btn').addEventListener('click', saveToolModal);
      document.getElementById('tool-cancel-btn').addEventListener('click', closeToolModal);
      console.log('✅ [ManualsEdit] Tool modal creado');
    }
    return modal;
  }
})();

// Modal se crea bajo demanda por ensureModal()

// CKEditor CDN
(function(){
  const s = document.createElement('script');
  s.src = 'https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js';
  s.onload = function(){ console.log('✅ [ManualsEdit] CKEditor cargado'); };
  s.onerror = function(){ console.log('❌ [ManualsEdit] Error cargando CKEditor CDN'); };
  document.head.appendChild(s);
})();
</script>

<style>
/* Step Builder styles - compact, admin-friendly */
.steps-toolbar { display:flex; gap:8px; margin-bottom:8px; }
.steps-list { list-style:none; padding:0; margin:0; }
.step-item { border:1px solid #ddd; margin-bottom:8px; border-radius:6px; overflow:hidden; }
.step-header { display:flex; align-items:center; justify-content:space-between; background:#f7f7f7; padding:6px 8px; }
.step-order { font-weight:bold; margin-right:8px; }
.step-title { flex:1; }
.step-actions { display:flex; gap:6px; }
.step-body { padding:8px; background:#fff; }
.help-note { color:#666; font-size:12px; margin-top:6px; }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:9999; }
.modal-content { background:#fff; width:min(900px, 92vw); max-height:90vh; overflow:auto; padding:16px; border-radius:8px; }
.modal-actions { display:flex; gap:8px; margin-top:8px; }

/* Tools Builder styles */
.tools-toolbar { display:flex; gap:8px; margin-bottom:8px; }
.tools-list { list-style:none; padding:0; margin:0; }
.tool-item { border:1px solid #ddd; margin-bottom:8px; border-radius:6px; overflow:hidden; }
.tool-header { display:flex; align-items:center; justify-content:space-between; background:#f7f7f7; padding:6px 8px; }
.tool-title { flex:1; }
.tool-actions { display:flex; gap:6px; }
.tool-body { padding:8px; background:#fff; color:#444; }
.mode-toggle { display:flex; gap:16px; align-items:center; }
.disabled-block { opacity:0.5; pointer-events:none; }
.hidden-block { display:none; }
</style>
</script>