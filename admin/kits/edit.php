<?php
require_once '../auth.php';
/** @var \PDO $pdo */
?>
<?php if ($is_edit): ?>
  <div class="card" style="margin-top:2rem;">
    <h3>Ficha técnica</h3>
    <small class="hint" style="display:block; margin-bottom:6px;">Selecciona atributos y define sus valores. Usa el buscador para filtrar.</small>
    <div class="dual-listbox-container">
      <div class="listbox-panel">
        <div class="listbox-header">
          <strong>Disponibles</strong>
          <span id="attrs-available-count" class="counter"></span>
        </div>
        <input type="text" id="search-attrs" class="listbox-search" placeholder="🔍 Buscar atributos...">
        <div class="listbox-content" id="available-attrs">
          <?php foreach ($attr_defs as $def): 
            $aid = (int)$def['id'];
            $values = $attr_vals[$aid] ?? [];
            if (!empty($values)) { continue; } // sólo disponibles sin valor
            $label = $def['etiqueta'];
            $tipo = $def['tipo_dato'];
            $unitDef = $def['unidad_defecto'] ?? '';
            $unitsJson = $def['unidades_permitidas_json'] ? $def['unidades_permitidas_json'] : '[]';
          ?>
            <div class="competencia-item" 
                 data-id="<?= $aid ?>"
                 data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
                 data-tipo="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>"
                 data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
                 data-unidad_def="<?= htmlspecialchars($unitDef, ENT_QUOTES, 'UTF-8') ?>"
                 data-units='<?= htmlspecialchars($unitsJson, ENT_QUOTES, 'UTF-8') ?>'
                 onclick="selectAttrItem(this)">
              <span class="comp-nombre"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comp-codigo">Tipo: <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?><?= $unitDef ? ' · Unidad: ' . htmlspecialchars($unitDef, ENT_QUOTES, 'UTF-8') : '' ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="listbox-buttons">
        <button type="button" onclick="moveAllAttrs(true)" title="Agregar todas">➡️</button>
        <button type="button" onclick="moveAllAttrs(false)" title="Quitar todas">⬅️</button>
      </div>
      <div class="listbox-panel">
        <div class="listbox-header">
          <strong>Seleccionadas</strong>
          <span id="attrs-selected-count" class="counter"></span>
        </div>
        <div class="listbox-content" id="selected-attrs">
          <?php foreach ($attr_defs as $def): 
            $aid = (int)$def['id'];
            $values = $attr_vals[$aid] ?? [];
            if (empty($values)) { continue; }
            $label = $def['etiqueta'];
            $tipo = $def['tipo_dato'];
            $unitDef = $def['unidad_defecto'] ?? '';
            $unitsJson = $def['unidades_permitidas_json'] ? $def['unidades_permitidas_json'] : '[]';
            $unit = $values[0]['unidad_codigo'] ?? '';
            $display = [];
            foreach ($values as $v) {
              if ($tipo === 'number') {
                if ($v['valor_numero'] !== null) { $s = (string)$v['valor_numero']; $display[] = (strpos($s, '.') !== false) ? rtrim(rtrim($s, '0'), '.') : $s; }
              }
              else if ($tipo === 'integer') { $display[] = (string)$v['valor_entero']; }
              else if ($tipo === 'boolean') { $display[] = ((int)$v['valor_booleano'] === 1 ? 'Sí' : 'No'); }
              else if ($tipo === 'date') { $display[] = $v['valor_fecha']; }
              else if ($tipo === 'datetime') { $display[] = $v['valor_datetime']; }
              else if ($tipo === 'json') { $display[] = $v['valor_json']; }
              else { $display[] = $v['valor_string']; }
            }
            $text = htmlspecialchars(implode(', ', array_filter($display)), ENT_QUOTES, 'UTF-8');
          ?>
            <div class="competencia-item selected" 
                 data-id="<?= $aid ?>"
                 data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
                 data-tipo="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>"
                 data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
                 data-unidad_def="<?= htmlspecialchars($unitDef, ENT_QUOTES, 'UTF-8') ?>"
                 data-units='<?= htmlspecialchars($unitsJson, ENT_QUOTES, 'UTF-8') ?>'
                 onclick="deselectAttrItem(this)">
              <span class="comp-nombre"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comp-codigo">Valor: <?= $text ?><?= $unit ? ' · ' . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') : '' ?></span>
              <button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectAttrItem(this.parentElement)">×</button>
              <button type="button" class="edit-component js-edit-attr" title="Editar"
                data-attr-id="<?= $aid ?>"
                data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
                data-tipo="<?= htmlspecialchars($def['tipo_dato'], ENT_QUOTES, 'UTF-8') ?>"
                data-card="<?= htmlspecialchars($def['cardinalidad'], ENT_QUOTES, 'UTF-8') ?>"
                data-units='<?= htmlspecialchars($def['unidades_permitidas_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>'
                data-unidad_def="<?= htmlspecialchars($def['unidad_defecto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                data-values='<?= htmlspecialchars(json_encode($values), ENT_QUOTES, 'UTF-8') ?>'
              >✏️</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function initAttrsTransfer(){
      const available = document.getElementById('available-attrs');
      const selected = document.getElementById('selected-attrs');
      const search = document.getElementById('search-attrs');
      const availableCount = document.getElementById('attrs-available-count');
      const selectedCount = document.getElementById('attrs-selected-count');
      if (!available || !selected) { console.log('⚠️ [KitsEdit] Transfer de atributos no inicializado'); return; }

      function updateCounts(){
        const availVisible = Array.from(available.querySelectorAll('.competencia-item')).filter(el => el.style.display !== 'none').length;
        const selCount = Array.from(selected.querySelectorAll('.competencia-item')).length;
        if (availableCount) availableCount.textContent = '(' + availVisible + ')';
        if (selectedCount) selectedCount.textContent = '(' + selCount + ')';
        console.log('🔍 [KitsEdit] Atributos: disponibles', availVisible, 'seleccionados', selCount);
      }

      window.selectAttrItem = function(el){
        try {
          const defId = el.dataset.id;
          const label = el.dataset.label;
          const tipo = el.dataset.tipo;
          const unitDef = el.dataset.unidad_def || '';
          const unitsJson = el.dataset.units || '[]';
          document.getElementById('add_def_id').value = String(defId);
          document.getElementById('addAttrInfo').textContent = label;
          const sel = document.getElementById('add_unidad');
          const selGroup = document.getElementById('add_unidad_group');
          const addVal = document.getElementById('add_valor');
          sel.innerHTML = '';
          let units = [];
          try { const parsed = JSON.parse(unitsJson || '[]'); if (Array.isArray(parsed)) units = parsed; } catch(_e){ units = []; }
          const hasUnits = Array.isArray(units) && units.length > 0;
          const hasDefault = !!unitDef;
          if (hasUnits || hasDefault) {
            const opt0 = document.createElement('option'); opt0.value=''; opt0.textContent = unitDef ? `(por defecto: ${unitDef})` : '(sin unidad)'; sel.appendChild(opt0);
            if (hasUnits) units.forEach(u => { const o=document.createElement('option'); o.value=u; o.textContent=u; sel.appendChild(o); });
            if (selGroup) selGroup.style.display = '';
          } else { if (selGroup) selGroup.style.display = 'none'; }
          if (addVal) {
            const card = el.dataset.card;
            if (card === 'many' && (tipo === 'number' || tipo === 'integer')) { addVal.placeholder = 'Para múltiples, separa por saltos de línea'; }
            else { addVal.placeholder = 'Para múltiples, separa por comas'; }
          }
          openModal('#modalAddAttr');
        } catch(e){ console.log('❌ [KitsEdit] Error selectAttrItem:', e && e.message); }
      };

      window.deselectAttrItem = function(el){
        try {
          const defId = el.dataset.id;
          const f = new FormData(); f.append('csrf_token','<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>'); f.append('action','delete_attr'); f.append('def_id', String(defId)); f.append('ajax','1');
          fetch(window.location.href, { method: 'POST', body: f, headers: { 'Accept': 'application/json' }})
            .then(r => r.json())
            .then(data => {
              if (!data || data.ok !== true) throw new Error(data && data.error ? data.error : 'Error desconocido');
              el.remove();
              // Restaurar item en disponibles
              const node = document.createElement('div');
              node.className = 'competencia-item';
              node.dataset.id = defId;
              node.dataset.label = el.dataset.label || '';
              node.dataset.tipo = el.dataset.tipo || 'string';
              node.dataset.card = el.dataset.card || 'one';
              node.dataset.unidad_def = el.dataset.unidad_def || '';
              node.dataset.units = el.dataset.units || '[]';
              node.innerHTML = `<span class="comp-nombre">${el.dataset.label || ''}</span><span class="comp-codigo">Tipo: ${el.dataset.tipo || ''}${(el.dataset.unidad_def ? ' · Unidad: '+el.dataset.unidad_def : '')}</span>`;
              // Restaurar comportamiento: reactivar selección y actualizar conteos
              node.setAttribute('onclick','selectAttrItem(this)');
              available.appendChild(node);
              updateCounts();
            })
            .catch(err => {
              console.log('❌ [KitsEdit] Error al eliminar atributo:', err && err.message);
            });
        } catch(e){ console.log('❌ [KitsEdit] Error deselectAttrItem:', e && e.message); }
      };
      
      // Actualizar conteos iniciales y binder de búsqueda
      updateCounts();
      if (search) {
        search.addEventListener('input', function(){
          const q = (this.value || '').trim().toLowerCase();
          Array.from(available.querySelectorAll('.competencia-item')).forEach(el => {
            const label = (el.dataset.label || '').toLowerCase();
            el.style.display = (!q || label.includes(q)) ? '' : 'none';
          });
          updateCounts();
        });
      }
    })();
  </script>

      // Campos landing/SEO
      $resumen = isset($_POST['resumen']) ? trim((string)$_POST['resumen']) : '';
      $contenido_html = isset($_POST['contenido_html']) ? (string)$_POST['contenido_html'] : '';
      $imagen_portada = isset($_POST['imagen_portada']) ? trim((string)$_POST['imagen_portada']) : '';
      $video_portada = isset($_POST['video_portada']) ? trim((string)$_POST['video_portada']) : '';
      $seo_title = isset($_POST['seo_title']) ? trim((string)$_POST['seo_title']) : '';
      $seo_description = isset($_POST['seo_description']) ? trim((string)$_POST['seo_description']) : '';
      // Seguridad estructurada → JSON
      $seg_edad_min = (isset($_POST['seg_edad_min']) && $_POST['seg_edad_min'] !== '') ? (int)$_POST['seg_edad_min'] : null;
      $seg_edad_max = (isset($_POST['seg_edad_max']) && $_POST['seg_edad_max'] !== '') ? (int)$_POST['seg_edad_max'] : null;
      $seg_notas = isset($_POST['seg_notas']) ? trim((string)$_POST['seg_notas']) : '';
      if ($seg_notas === '') { $seg_notas = null; }
      $seguridad_json = null;
        // Áreas seleccionadas
        $areas_sel = isset($_POST['areas']) && is_array($_POST['areas']) ? array_map('intval', $_POST['areas']) : [];
        if (!$__is_ajax_request) { echo '<script>console.log("🔍 [KitsEdit] Áreas seleccionadas:", ' . json_encode($areas_sel) . ');</script>'; }

      // Tiempo y dificultad por defecto (kit)
      $time_minutes = (isset($_POST['time_minutes']) && $_POST['time_minutes'] !== '') ? (int)$_POST['time_minutes'] : null;
      $dificultad_ensamble = isset($_POST['dificultad_ensamble']) ? trim((string)$_POST['dificultad_ensamble']) : '';
      if ($dificultad_ensamble === '') { $dificultad_ensamble = null; }
      if (!$__is_ajax_request) { echo '<script>console.log("🔍 [KitsEdit] Defaults tiempo/dificultad:", ' . json_encode(['time'=>$time_minutes,'dif'=>$dificultad_ensamble]) . ');</script>'; }

      if ($seg_edad_min !== null || $seg_edad_max !== null || $seg_notas !== null) {
        $seguridad_json = json_encode([
          'edad_min' => $seg_edad_min,
          'edad_max' => $seg_edad_max,
          'notas' => $seg_notas
        ], JSON_UNESCAPED_UNICODE);
      }

      // Generación automática de SEO si vienen vacíos
      // Title: "Kit de Ciencia - [Área]: [Nombre]" o fallback sin área
      if ($seo_title === '') {
        $area_nombre = '';
        if (!empty($areas_sel) && !empty($areas)) {
          foreach ($areas as $area) {
            if (in_array($area['id'], $areas_sel)) { $area_nombre = $area['nombre']; break; }
          }
        }
        $base = 'Kit de Ciencia - ';
        if ($area_nombre !== '') {
          $formato1 = $base . $area_nombre . ': ' . $nombre;
          if (mb_strlen($formato1, 'UTF-8') <= 60) {
            $seo_title = $formato1;
          } else {
            $separador = ' | ' . $area_nombre;
            $max_nombre = 60 - mb_strlen($base, 'UTF-8') - mb_strlen($separador, 'UTF-8');
            $nombre_corto = mb_strlen($nombre, 'UTF-8') > $max_nombre ? mb_substr($nombre, 0, max(0, $max_nombre-3), 'UTF-8') . '...' : $nombre;
            $seo_title = $base . $nombre_corto . $separador;
          }
        } else {
          $max_nombre = 60 - mb_strlen($base, 'UTF-8');
          $nombre_corto = mb_strlen($nombre, 'UTF-8') > $max_nombre ? mb_substr($nombre, 0, max(0, $max_nombre-3), 'UTF-8') . '...' : $nombre;
          $seo_title = $base . $nombre_corto;
        }
      }

      // Description: tomar resumen o texto del contenido HTML, truncado a 160
      if ($seo_description === '') {
        $desc_source = $resumen !== '' ? $resumen : strip_tags($contenido_html);
        $desc_source = preg_replace('/\s+/', ' ', $desc_source);
        $max_desc = 160;
        $seo_description = (mb_strlen($desc_source, 'UTF-8') > $max_desc)
          ? preg_replace('/\s+\S*$/u', '', mb_substr($desc_source, 0, $max_desc, 'UTF-8'))
          : $desc_source;
      }
      if (!$__is_ajax_request) { echo '<script>console.log("🔍 [SEO] auto title:", ' . json_encode($seo_title) . ', "auto desc:", ' . json_encode($seo_description) . ');</script>'; }

      // Normalizar longitudes razonables
      if ($seo_title !== '') { $seo_title = mb_substr($seo_title, 0, 160, 'UTF-8'); }
      if ($seo_description !== '') { $seo_description = mb_substr($seo_description, 0, 255, 'UTF-8'); }
      if ($imagen_portada === '') { $imagen_portada = null; }
      if ($video_portada === '') { $video_portada = null; }

      // Autogenerar/normalizar slug con prefijo kit-
      if ($slug === '' && $nombre !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
        $slug = trim($slug, '-');
      }
      if ($slug !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $slug));
        $slug = trim($slug, '-');
        // Eliminar prefijos repetidos 'kit-' y forzar uno solo al inicio
        $slug = preg_replace('/^(?:kit-)+/i', '', $slug);
        $slug = 'kit-' . ltrim($slug, '-');
      }

      if ($principal_clase_id <= 0 || $nombre === '' || $codigo === '' || $slug === '') {
        $error_msg = 'Selecciona al menos una clase y completa nombre, código y slug.';
      } else {
        try {
          // Enforce unique code
          if ($is_edit) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE codigo = ? AND id <> ?');
            $check->execute([$codigo, $id]);
          } else {
            $check = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE codigo = ?');
            $check->execute([$codigo]);
          }
          $exists = (int)$check->fetchColumn();
          if ($exists > 0) {
            $error_msg = 'El código de kit ya existe. Elige otro.';
          } else {
            // Validar slug único
            if ($is_edit) {
              $checkS = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE slug = ? AND id <> ?');
              $checkS->execute([$slug, $id]);
            } else {
              $checkS = $pdo->prepare('SELECT COUNT(*) FROM kits WHERE slug = ?');
              $checkS->execute([$slug]);
            }
            $slugExists = (int)$checkS->fetchColumn();
            if ($slugExists > 0) {
              $error_msg = 'El slug ya existe. Elige otro.';
            } else {
            $pdo->beginTransaction();
            if ($is_edit) {
              $stmt = $pdo->prepare('UPDATE kits SET clase_id=?, nombre=?, slug=?, codigo=?, version=?, resumen=?, contenido_html=?, imagen_portada=?, video_portada=?, seguridad=?, time_minutes=?, dificultad_ensamble=?, seo_title=?, seo_description=?, activo=?, updated_at=NOW() WHERE id=?');
              $stmt->execute([$principal_clase_id, $nombre, $slug, $codigo, $version, $resumen, $contenido_html, $imagen_portada, $video_portada, $seguridad_json, $time_minutes, $dificultad_ensamble, $seo_title, $seo_description, $activo, $id]);
            } else {
              $stmt = $pdo->prepare('INSERT INTO kits (clase_id, nombre, slug, codigo, version, resumen, contenido_html, imagen_portada, video_portada, seguridad, time_minutes, dificultad_ensamble, seo_title, seo_description, activo, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
              $stmt->execute([$principal_clase_id, $nombre, $slug, $codigo, $version, $resumen, $contenido_html, $imagen_portada, $video_portada, $seguridad_json, $time_minutes, $dificultad_ensamble, $seo_title, $seo_description, $activo]);
              $id = (int)$pdo->lastInsertId();
              $is_edit = true;
            }

            // Actualizar relaciones en clase_kits y kits_areas
            try {
              $pdo->prepare('DELETE FROM clase_kits WHERE kit_id = ?')->execute([$id]);
              if (!empty($clases_sel)) {
                $ins = $pdo->prepare('INSERT INTO clase_kits (clase_id, kit_id, sort_order, es_principal) VALUES (?,?,?,?)');
                $sort = 1;
                foreach ($clases_sel as $cid) {
                  $es_principal = ($sort === 1) ? 1 : 0;
                  $ins->execute([(int)$cid, $id, $sort++, $es_principal]);
                }
              } else if ($principal_clase_id > 0) {
                // Fallback: al menos principal
                $pdo->prepare('INSERT INTO clase_kits (clase_id, kit_id, sort_order, es_principal) VALUES (?,?,?,1)')
                    ->execute([$principal_clase_id, $id, 1]);
              }
              // Guardar áreas del kit (many-to-many)
              $pdo->prepare('DELETE FROM kits_areas WHERE kit_id = ?')->execute([$id]);
              if (!empty($areas_sel)) {
                $insA = $pdo->prepare('INSERT INTO kits_areas (kit_id, area_id) VALUES (?, ?)');
                foreach ($areas_sel as $aid) { if ($aid > 0) { $insA->execute([$id, (int)$aid]); } }
              }
              $pdo->commit();
              if (!$__is_ajax_request) { echo '<script>console.log("✅ [KitsEdit] Kit y relaciones clase_kits + kits_areas guardados");</script>'; }
            } catch (PDOException $e) {
              if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
              throw $e;
            }
            header('Location: /admin/kits/index.php');
            exit;
            }
          }
        } catch (PDOException $e) {
          $error_msg = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    } else if ($action === 'add_item' && $is_edit) {
      $item_id = isset($_POST['item_id']) && ctype_digit($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
      $cantidad = isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) ? (float)$_POST['cantidad'] : 0;
      $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
      $orden = isset($_POST['orden']) && ctype_digit($_POST['orden']) ? (int)$_POST['orden'] : 0;
      if ($item_id <= 0 || $cantidad <= 0) {
        $error_msg = 'Selecciona un componente y cantidad válida.';
      } else {
        try {
          // Ajuste al schema: usar sort_order en vez de orden
          if ($notas !== '') { $notas = mb_substr($notas, 0, 255, 'UTF-8'); } else { $notas = null; }
          $stmt = $pdo->prepare('INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order) VALUES (?,?,?,?,?,?)');
          $stmt->execute([$id, $item_id, $cantidad, 1, $notas, $orden]);
          $action_msg = 'Componente agregado.';
        } catch (PDOException $e) {
          $error_msg = 'Error al agregar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    } else if ($action === 'delete_item' && $is_edit) {
      // El schema no tiene columna id en kit_componentes; borrar por (kit_id, item_id)
      $kc_item_id = isset($_POST['kc_item_id']) && ctype_digit($_POST['kc_item_id']) ? (int)$_POST['kc_item_id'] : 0;
      if ($kc_item_id <= 0) {
        $error_msg = 'Componente inválido.';
      } else {
        try {
          $stmt = $pdo->prepare('DELETE FROM kit_componentes WHERE kit_id = ? AND item_id = ?');
          $stmt->execute([$id, $kc_item_id]);
          $action_msg = 'Componente eliminado.';
        } catch (PDOException $e) {
          $error_msg = 'Error al eliminar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
      }
    } else if ($action === 'update_item' && $is_edit) {
      // Actualizar cantidad, notas y orden (sort_order) para un item existente
      $kc_item_id = isset($_POST['kc_item_id']) && ctype_digit($_POST['kc_item_id']) ? (int)$_POST['kc_item_id'] : 0;
      $cantidad = isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) ? (float)$_POST['cantidad'] : 0;
      $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
      $orden = isset($_POST['orden']) && is_numeric($_POST['orden']) ? (int)$_POST['orden'] : 0;
      $incluido = isset($_POST['es_incluido_kit']) ? 1 : 0;
      if ($kc_item_id <= 0 || $cantidad <= 0) {
        $error_msg = 'Selecciona un componente válido y cantidad positiva.';
        echo '<script>console.log("❌ [KitsEdit] update_item inválido");</script>';
      } else {
        try {
          if ($notas !== '') { $notas = mb_substr($notas, 0, 255, 'UTF-8'); } else { $notas = null; }
          $stmt = $pdo->prepare('UPDATE kit_componentes SET cantidad = ?, notas = ?, sort_order = ?, es_incluido_kit = ? WHERE kit_id = ? AND item_id = ?');
          $stmt->execute([$cantidad, $notas, $orden, $incluido, $id, $kc_item_id]);
          $action_msg = 'Componente actualizado.';
          echo '<script>console.log("✅ [KitsEdit] update_item guardado; incluido=' . ($incluido? '1':'0') . '");</script>';
        } catch (PDOException $e) {
          $error_msg = 'Error al actualizar componente: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
          echo '<script>console.log("❌ [KitsEdit] update_item error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
        }
      }
    }
  }
}

// Cargar lista de componentes del kit
$componentes = [];
if ($is_edit) {
  try {
    // Ajuste al schema: no hay kc.id ni kc.orden; usar sort_order como orden, incluir notas
    $stmt = $pdo->prepare('SELECT kc.item_id, kc.cantidad, kc.sort_order AS orden, kc.notas, kc.es_incluido_kit, ki.nombre_comun, ki.sku, ki.unidad FROM kit_componentes kc JOIN kit_items ki ON ki.id = kc.item_id WHERE kc.kit_id = ? ORDER BY kc.sort_order ASC, ki.nombre_comun ASC');
    $stmt->execute([$id]);
    $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {}
}

// Lista de kit_items para agregar
try {
  $items_stmt = $pdo->query('SELECT id, nombre_comun, sku, unidad FROM kit_items ORDER BY nombre_comun ASC');
  $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $items = [];
}

include '../header.php';
?>
<div class="page-header">
  <h2><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
  <span class="help-text">Completa los campos del kit y gestiona sus componentes.</span>
  <script>
    console.log('✅ [Admin] Kits edit cargado');
    console.log('🔍 [Admin] Edit mode:', <?= $is_edit ? 'true' : 'false' ?>);
    console.log('🔍 [Admin] Kit ID:', <?= $is_edit ? (int)$id : 'null' ?>);
    console.log('🔍 [KitsEdit] Estado activo inicial:', <?= ((int)$kit['activo']) ? 'true' : 'false' ?>);
  </script>
  <script>
    // Fallback binder to ensure the create-attribute button opens the modal
    (function bindCreateAttrButton(){
      const btn = document.getElementById('btn_create_attr');
      if (!btn) { console.log('⚠️ [KitsEdit] Botón crear atributo no encontrado'); return; }
      btn.addEventListener('click', function(){
        try {
          const q = (document.getElementById('attr_search')?.value || '').trim();
          const et = document.getElementById('create_etiqueta');
          const cl = document.getElementById('create_clave');
          const tp = document.getElementById('create_tipo');
          const cd = document.getElementById('create_card');
          const ud = document.getElementById('create_unidad');
          const ups = document.getElementById('create_unidades');
          if (et) et.value = q;
          if (cl) cl.value = '';
          if (tp) tp.value = 'string';
          if (cd) cd.value = 'one';
          if (ud) ud.value = '';
          if (ups) ups.value = '';
          openModal('#modalCreateAttr');
          setTimeout(() => { try { et?.focus(); } catch(_e){} }, 50);
          console.log('✅ [KitsEdit] Modal crear atributo abierto');
        } catch(e) { console.log('❌ [KitsEdit] Error abrir modal crear atributo:', e && e.message); }
      });
    })();
  </script>
</div>

<?php if ($error_msg !== ''): ?>
  <div class="message error"><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($action_msg !== ''): ?>
  <div class="message success"><?= htmlspecialchars($action_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST" id="kit-form">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
  <input type="hidden" name="action" value="save" />
  <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
    <label class="switch-label">
      <input type="checkbox" name="activo" class="switch-input" <?= ((int)$kit['activo']) ? 'checked' : '' ?> />
      <span class="switch-slider"></span>
      <span class="switch-text">✓ Activo</span>
    </label>
  </div>
  <div class="form-group">
    <label for="nombre">Nombre del Kit</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($kit['nombre'], ENT_QUOTES, 'UTF-8') ?>" required />
  </div>
  <div class="form-group">
    <label for="slug">Slug</label>
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($kit['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="se genera automáticamente" style="flex:1;" />
      <button type="button" id="btn_generar_slug" style="padding:8px 16px; background:#0066cc; color:white; border:none; border-radius:4px; cursor:pointer; white-space:nowrap;">⚡ Generar</button>
    </div>
    <small class="hint">URL amigable. Ejemplo: kit-carro-solar</small>
  </div>
  <div class="form-group">
    <label for="codigo">Código</label>
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($kit['codigo'], ENT_QUOTES, 'UTF-8') ?>" placeholder="p.ej. KIT-PLANTA-LUZ-01" required />
      <span id="codigo_status" style="font-size:0.85rem;color:#666;"></span>
    </div>
    <small>Debe ser único.</small>
  </div>
  <div class="form-group">
    <label for="version">Versión</label>
    <input type="text" id="version" name="version" value="<?= htmlspecialchars($kit['version'], ENT_QUOTES, 'UTF-8') ?>" />
  </div>
  
  <?php
    $seg_arr = null;
    if (!empty($kit['seguridad'])) {
      try { $tmp = json_decode((string)$kit['seguridad'], true); if (is_array($tmp)) { $seg_arr = $tmp; } } catch (Exception $e) { $seg_arr = null; }
    }
    $seg_edad_min_val = $seg_arr['edad_min'] ?? '';
    $seg_edad_max_val = $seg_arr['edad_max'] ?? '';
    $seg_notas_val = $seg_arr['notas'] ?? '';
  ?>
  <div class="form-group" style="margin-top:1.25rem;">
    <h3>Ficha pública (Landing)</h3>
  </div>
  <div class="form-group">
    <label for="resumen">Resumen del kit</label>
    <textarea id="resumen" name="resumen" rows="3" placeholder="1-2 frases claras para docentes y estudiantes"><?= htmlspecialchars($kit['resumen'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="hint">Breve descripción para la ficha pública.</small>
  </div>
  <div class="form-group">
    <div class="field-inline">
      <div class="form-group">
        <label for="imagen_portada">Imagen de portada (URL)</label>
        <input type="text" id="imagen_portada" name="imagen_portada" value="<?= htmlspecialchars($kit['imagen_portada'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/img/kits/kit-xyz.webp" />
      </div>
      <div class="form-group">
        <label for="video_portada">Video portada (URL o ID)</label>
        <input type="text" id="video_portada" name="video_portada" value="<?= htmlspecialchars($kit['video_portada'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="p.ej. YouTube ID o URL" />
      </div>
    </div>
  </div>
  <div class="form-group">
    <h4>Seguridad</h4>
    <div class="field-inline">
      <div class="form-group">
        <label for="seg_edad_min">Edad mínima</label>
        <input type="number" id="seg_edad_min" name="seg_edad_min" min="0" step="1" value="<?= htmlspecialchars($seg_edad_min_val, ENT_QUOTES, 'UTF-8') ?>" />
      </div>
      <div class="form-group">
        <label for="seg_edad_max">Edad máxima</label>
        <input type="number" id="seg_edad_max" name="seg_edad_max" min="0" step="1" value="<?= htmlspecialchars($seg_edad_max_val, ENT_QUOTES, 'UTF-8') ?>" />
      </div>
    </div>
    <div class="form-group">
      <label for="seg_notas">Notas de seguridad</label>
      <textarea id="seg_notas" name="seg_notas" rows="3" placeholder="Advertencias y precauciones generales."><?= htmlspecialchars($seg_notas_val, ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
  </div>
  <div class="form-section">
    <h2>Predeterminados de ensamblaje</h2>
    <div class="form-row">
      <div class="form-group">
        <label for="time_minutes">Tiempo de armado (minutos)</label>
        <input type="number" id="time_minutes" name="time_minutes" min="0" step="1" value="<?= htmlspecialchars($kit['time_minutes'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
        <small class="hint">Usado como valor por defecto en el manual si no está definido allí.</small>
      </div>
      <div class="form-group">
        <label for="dificultad_ensamble">Dificultad de ensamble</label>
        <select id="dificultad_ensamble" name="dificultad_ensamble">
          <?php
            $dif_val = $kit['dificultad_ensamble'] ?? '';
            $dif_opts = ['Muy fácil','Fácil','Media','Difícil'];
            echo '<option value="">(sin definir)</option>';
            foreach ($dif_opts as $opt) {
              $sel = ($dif_val === $opt) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '" ' . $sel . '>' . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '</option>';
            }
          ?>
        </select>
        <small class="hint">Referencia general del nivel de dificultad.</small>
      </div>
    </div>
    <script>
      console.log('🔍 [KitsEdit] Defaults iniciales tiempo/dif:', {
        time: <?= json_encode($kit['time_minutes'] ?? null) ?>,
        dif: <?= json_encode($kit['dificultad_ensamble'] ?? null) ?>
      });
    </script>
  </div>
  <div class="form-group">
    <label for="contenido_html">Contenido HTML</label>
    <textarea id="contenido_html" name="contenido_html" rows="8" placeholder="HTML básico para la ficha del kit."><?= htmlspecialchars($kit['contenido_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <small class="hint">Soporta HTML básico. Evita scripts incrustados.</small>
  </div>
  <?php
  // Definiciones y valores actuales de atributos del Kit (para UI tipo chips)
  $attr_defs = [];
  $attr_vals = [];
  if ($is_edit) {
    try {
      $st = $pdo->prepare('SELECT d.* FROM atributos_definiciones d JOIN atributos_mapeo m ON m.atributo_id = d.id WHERE m.tipo_entidad = ? AND m.visible = 1 ORDER BY m.orden ASC, d.id ASC');
      $st->execute(['kit']);
      $attr_defs = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $attr_defs = []; }
    try {
      $sv = $pdo->prepare('SELECT * FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? ORDER BY atributo_id ASC, orden ASC');
      $sv->execute(['kit', $id]);
      $rows = $sv->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        $aid = (int)$r['atributo_id'];
        if (!isset($attr_vals[$aid])) $attr_vals[$aid] = [];
        $attr_vals[$aid][] = $r;
      }
    } catch (PDOException $e) {}
  }
  ?>
  <?php /* chips moved out of main form below */ ?>
  <!-- Taxonomías -->
  <div class="form-section">
    <h2>Taxonomías</h2>
    <h3 style="margin-top:.5rem">Áreas</h3>
    <div class="checkbox-grid">
      <?php foreach ($areas as $a): ?>
        <label class="checkbox-label"><input type="checkbox" name="areas[]" value="<?= (int)$a['id'] ?>" <?= in_array($a['id'], $existing_area_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></label>
      <?php endforeach; ?>
    </div>
    <small class="hint">Selecciona las áreas temáticas del kit.</small>
  </div>
  <!-- SEO -->
  <div class="form-section">
    <h2>SEO</h2>
    <div class="form-row">
      <div class="form-group">
        <label for="seo_title">SEO Title (≤60)</label>
        <div style="display: flex; gap: 8px; align-items: center;">
          <input type="text" id="seo_title" name="seo_title" maxlength="160" value="<?= htmlspecialchars($kit['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="flex: 1;" />
          <button type="button" id="btn_generar_seo" style="padding: 8px 16px; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">⚡ Generar SEO</button>
        </div>
      </div>
      <div class="form-group">
        <label for="seo_description">SEO Description (≤160)</label>
        <input type="text" id="seo_description" name="seo_description" maxlength="255" value="<?= htmlspecialchars($kit['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
      </div>
    </div>
    <!-- SEO Auto Preview + Override Toggle -->
    <div class="form-section">
      <label><input type="checkbox" id="seo_override_toggle"> Editar SEO manualmente</label>
      <div class="seo-preview">
        <p><strong>Preview Title:</strong> <span id="seo_preview_title"></span></p>
        <p><strong>Preview Description:</strong> <span id="seo_preview_desc"></span></p>
        <small class="help-text">Si no defines SEO manualmente, se usarán estos valores.</small>
      </div>
    </div>
    <div id="seo-manual"></div>
  </div>
</form>

  <?php /* Antiguo UI de chips removido; reemplazado por dual listbox de atributos */ ?>

<?php if ($is_edit): ?>
<div class="form-group" style="margin-top:2rem;">
  <h3>Componentes del Kit</h3>

  <!-- estilos de chips y autocompletado se mueven a assets/css/style.css -->

  <div class="form-group">
    <label for="component_search">Buscar Componentes</label>
    <div class="component-selector-container">
      <div class="selected-components" id="selected-components">
        <?php if (!empty($componentes)): foreach ($componentes as $kc): ?>
          <div class="component-chip" data-item-id="<?= (int)$kc['item_id'] ?>" data-orden="<?= (int)$kc['orden'] ?>">
            <span class="name"><?= htmlspecialchars($kc['nombre_comun'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="meta">· <strong><?= htmlspecialchars($kc['cantidad'], ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(($kc['unidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <?php if (isset($kc['es_incluido_kit']) && (int)$kc['es_incluido_kit'] === 0): ?>
              <span class="chip-pill chip-danger" title="No incluido">No incluido</span>
            <?php endif; ?>
            <button type="button" class="edit-component js-edit-item" title="Editar"
              data-item-id="<?= (int)$kc['item_id'] ?>"
              data-cantidad="<?= htmlspecialchars($kc['cantidad'], ENT_QUOTES, 'UTF-8') ?>"
              data-notas="<?= htmlspecialchars(($kc['notas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              data-orden="<?= htmlspecialchars($kc['orden'], ENT_QUOTES, 'UTF-8') ?>"
              data-nombre="<?= htmlspecialchars($kc['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>"
              data-sku="<?= htmlspecialchars($kc['sku'], ENT_QUOTES, 'UTF-8') ?>"
              data-unidad="<?= htmlspecialchars(($kc['unidad'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>"
              data-incluido="<?= isset($kc['es_incluido_kit']) ? (int)$kc['es_incluido_kit'] : 1 ?>"
            >✏️</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar componente del kit?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
              <input type="hidden" name="action" value="delete_item" />
              <input type="hidden" name="kc_item_id" value="<?= (int)$kc['item_id'] ?>" />
              <button type="submit" class="remove-component" title="Remover">×</button>
            </form>
          </div>
        <?php endforeach; endif; ?>
      </div>
      <input type="text" id="component_search" placeholder="Escribir para buscar componente..." autocomplete="off" />
      <datalist id="components_list">
        <?php foreach ($items as $it): ?>
          <option value="<?= (int)$it['id'] ?>" data-name="<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)
          </option>
        <?php endforeach; ?>
      </datalist>
      <div class="autocomplete-dropdown" id="cmp_autocomplete_dropdown"></div>
    </div>
    <small>Escribe para buscar componentes. Al seleccionar, completa cantidad y orden en el modal.</small>
  </div>
</div>
<?php endif; ?>

  <!-- Modal Editar Atributo -->
  <div class="modal-overlay" id="modalEditAttr">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalEditAttrTitle">
      <div class="modal-header">
        <h4 id="modalEditAttrTitle">Editar atributo</h4>
        <button type="button" class="modal-close js-close-modal" data-target="#modalEditAttr">✖</button>
      </div>
      <form method="POST" id="formEditAttr">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="update_attr" />
        <input type="hidden" name="def_id" id="edit_def_id" />
        <div class="modal-body">
          <div class="muted" id="editAttrInfo"></div>
          <div class="form-group">
            <label for="edit_valor">Valor</label>
            <textarea id="edit_valor" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
          </div>
          <div class="form-group" id="edit_unidad_group">
            <label for="edit_unidad">Unidad (si aplica)</label>
            <select id="edit_unidad" name="unidad"></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditAttr">Cancelar</button>
          <button type="submit" class="btn">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Agregar Atributo -->
  <div class="modal-overlay" id="modalAddAttr">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalAddAttrTitle">
      <div class="modal-header">
        <h4 id="modalAddAttrTitle">Agregar atributo</h4>
        <button type="button" class="modal-close js-close-modal" data-target="#modalAddAttr">✖</button>
      </div>
      <form method="POST" id="formAddAttr">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="add_attr" />
        <input type="hidden" name="def_id" id="add_def_id" />
        <div class="modal-body">
          <div class="muted" id="addAttrInfo"></div>
          <div class="form-group">
            <label for="add_valor">Valor</label>
            <textarea id="add_valor" name="valor" rows="3" placeholder="Para múltiples, separa por comas"></textarea>
          </div>
          <div class="form-group" id="add_unidad_group">
            <label for="add_unidad">Unidad (si aplica)</label>
            <select id="add_unidad" name="unidad"></select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddAttr">Cancelar</button>
          <button type="submit" class="btn">Agregar</button>
        </div>
      </form>
    </div>
  </div>

    <!-- Modal Crear Definición de Atributo (Kit) -->
    <div class="modal-overlay" id="modalCreateAttr">
      <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalCreateAttrTitle">
        <div class="modal-header">
          <h4 id="modalCreateAttrTitle">Crear nuevo atributo</h4>
          <button type="button" class="modal-close js-close-modal" data-target="#modalCreateAttr">✖</button>
        </div>
        <form method="POST" id="formCreateAttr">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
          <input type="hidden" name="action" value="create_attr_def" />
          <div class="modal-body">
            <div class="form-group"><label for="create_etiqueta">Etiqueta</label><input type="text" id="create_etiqueta" name="etiqueta" required /></div>
            <div class="form-group"><label for="create_clave">Clave</label><input type="text" id="create_clave" name="clave" placeholder="auto desde etiqueta si se deja vacío" /></div>
            <div class="field-inline">
              <div class="form-group"><label for="create_tipo">Tipo</label>
                <select id="create_tipo" name="tipo_dato">
                  <option value="string">string</option>
                  <option value="number">number</option>
                  <option value="integer">integer</option>
                  <option value="boolean">boolean</option>
                  <option value="date">date</option>
                  <option value="datetime">datetime</option>
                  <option value="json">json</option>
                </select>
              </div>
              <div class="form-group"><label for="create_card">Cardinalidad</label>
                <select id="create_card" name="cardinalidad">
                  <option value="one">one</option>
                  <option value="many">many</option>
                </select>
              </div>
            </div>
            <div class="field-inline">
              <div class="form-group"><label for="create_unidad">Unidad por defecto</label><input type="text" id="create_unidad" name="unidad_defecto" placeholder="opcional" /></div>
              <div class="form-group"><label for="create_unidades">Unidades permitidas</label><input type="text" id="create_unidades" name="unidades_permitidas" placeholder="separa por comas" /></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalCreateAttr">Cancelar</button>
            <button type="submit" class="btn">Crear</button>
          </div>
        </form>
      </div>
     </div>

  <?php /* Legacy autocomplete UI removido en favor del dual listbox */ ?>
  
<script>
  console.log('🔍 [KitsEdit] Clases cargadas:', <?= count($clases) ?>);
  console.log('🔍 [KitsEdit] Items disponibles:', <?= count($items) ?>);
  // Verificación de unicidad en vivo para código de kit
  (function initCodigoCheck(){
    const codigoInput = document.getElementById('codigo');
    const statusEl = document.getElementById('codigo_status');
    const saveBtn = document.querySelector('#kit-form button[type="submit"]');
    const isEdit = <?= $is_edit ? 'true' : 'false' ?>;
    const currentId = <?= $is_edit ? (int)$id : 0 ?>;
    if (!codigoInput || !statusEl || !saveBtn) { console.log('⚠️ [KitsEdit] Código checker no inicializado'); return; }

    let lastVal = '';
    let timer = null;

    async function check(val){
      if (!val) { statusEl.textContent = ''; saveBtn.disabled = false; return; }
      statusEl.textContent = 'Verificando…'; statusEl.style.color = '#666';
      try {
        const resp = await fetch('/api/kits-validate-codigo.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ codigo: val, exclude_id: isEdit ? currentId : 0 })
        });
        console.log('📡 [KitsEdit] Check codigo status:', resp.status);
        const data = await resp.json();
        if (data && data.ok && data.unique !== null) {
          if (data.unique) {
            statusEl.textContent = 'Disponible ✅';
            statusEl.style.color = '#2e7d32';
            saveBtn.disabled = false;
            console.log('✅ [KitsEdit] Código disponible');
          } else {
            statusEl.textContent = 'En uso ❌';
            statusEl.style.color = '#c62828';
            saveBtn.disabled = true;
            console.log('❌ [KitsEdit] Código duplicado');
          }
        } else {
          statusEl.textContent = 'No se pudo verificar ⚠️';
          statusEl.style.color = '#b26a00';
          saveBtn.disabled = false;
          console.log('⚠️ [KitsEdit] Respuesta inválida en verificación');
        }
      } catch (e) {
        statusEl.textContent = 'Error al verificar ⚠️';
        statusEl.style.color = '#b26a00';
        saveBtn.disabled = false;
        console.log('❌ [KitsEdit] Error verificación:', e && e.message);
      }
    }

    function debounced(){
      const val = codigoInput.value.trim();
      if (val === lastVal) return;
      lastVal = val;
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => check(val), 350);
    }

    codigoInput.addEventListener('input', debounced);
    codigoInput.addEventListener('blur', () => check(codigoInput.value.trim()));
    if (codigoInput.value.trim()) {
      // Verificar inicial si existe valor
      check(codigoInput.value.trim());
    }
  })();
  // Generador de slug (similar a clases)
  (function initSlugGenerator(){
    const nombreInput = document.getElementById('nombre');
    const slugInput = document.getElementById('slug');
    const btnGenerar = document.getElementById('btn_generar_slug');
    function normalizeBase(str){ return (str || '').toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, ''); }
    function withKitPrefix(val){
      const base = normalizeBase(val).replace(/^kit-+/,'');
      return base.startsWith('kit-') ? base : ('kit-' + base);
    }
    if (btnGenerar && nombreInput && slugInput) {
      btnGenerar.addEventListener('click', function(){
        const v = nombreInput.value.trim();
        if (!v) { alert('Por favor ingresa el nombre del kit primero'); nombreInput.focus(); return; }
        const s = withKitPrefix(v);
        slugInput.value = s;
        console.log('⚡ [KitsEdit] slug generado con botón:', s);
      });
    }
    if (slugInput) {
      slugInput.addEventListener('blur', function(){
        if (slugInput.value) {
          const fixed = withKitPrefix(slugInput.value);
          if (fixed !== slugInput.value) {
            console.log('⚠️ [KitsEdit] corrigiendo slug con prefijo:', fixed);
            slugInput.value = fixed;
          }
        }
      });
    }
  })();
</script>

<?php if ($is_edit): ?>
<script>
  // AJAX helper for kit attribute actions (mirrors Clases editor)
  function postAjaxKit(formEl, successCb){
    const fd = new FormData(formEl);
    fd.append('ajax','1');
    console.log('📡 [KitsEdit] AJAX', fd.get('action'));
    fetch(window.location.href, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }})
      .then(r => r.json())
      .then(data => {
        if (!data || data.ok !== true) throw new Error(data && data.error ? data.error : 'Error desconocido');
        console.log('✅ [KitsEdit] AJAX ok:', data.action);
        successCb(data);
      })
      .catch(err => {
        console.log('❌ [KitsEdit] AJAX error:', err && err.message);
        alert('Error: ' + (err && err.message ? err.message : 'operación fallida'));
      });
  }

  function displayFromKit(tipo, values){
    if (!Array.isArray(values)) return '';
    const parts = values.map(v => {
      switch (tipo){
        case 'number': {
          let s = (typeof v === 'string' ? v : String(v)).replace(/,/g,'.');
          if (s.includes('.')) {
            s = s.replace(/0+$/,'').replace(/\.$/,'');
          }
          return s;
        }
        case 'integer': return String(parseInt(v,10));
        case 'boolean': return (String(v).toLowerCase()==='1' || String(v).toLowerCase()==='true' || String(v).toLowerCase()==='sí' || String(v).toLowerCase()==='si') ? 'Sí' : 'No';
        default: return String(v);
      }
    }).filter(Boolean);
    return parts.join(', ');
  }

  const formEditAttr = document.getElementById('formEditAttr');
  const formAddAttr = document.getElementById('formAddAttr');
  function upsertAttrChipKit(def, display, unidad, rawValues){
    const wrap = document.getElementById('selected-attrs');
    if (!wrap) return;
    let chip = wrap.querySelector('.component-chip[data-attr-id="' + def.id + '"]');
    const unitText = unidad ? (' ' + unidad) : '';
    if (!chip){
      chip = document.createElement('div');
      chip.className = 'component-chip';
      chip.setAttribute('data-attr-id', String(def.id));
      chip.innerHTML = `
        <span class="name"></span>
        <span class="meta">· <strong class="meta-val"></strong><span class="meta-unit"></span></span>
        <button type="button" class="edit-component js-edit-attr" title="Editar"
          data-attr-id="${def.id}"
          data-label="${def.etiqueta}"
          data-tipo="${def.tipo_dato}"
          data-card="${def.cardinalidad}"
          data-units='${JSON.stringify(def.unidades_permitidas || [])}'
          data-unidad_def="${def.unidad_defecto || ''}"
          data-values='${JSON.stringify((rawValues||[]).map(v=>{
            const s = String(v).replace(/,/g,'.');
            switch(def.tipo_dato){
              case "number":
                return { valor_string: String(v), valor_numero: (isNaN(parseFloat(s)) ? s : parseFloat(s)) };
              case "integer":
                return { valor_string: String(v), valor_entero: (isNaN(parseInt(s,10)) ? s : parseInt(s,10)) };
              case "boolean": {
                const b = (String(v).toLowerCase()==='1' || String(v).toLowerCase()==='true' || String(v).toLowerCase()==='sí' || String(v).toLowerCase()==='si') ? 1 : 0;
                return { valor_string: String(v), valor_booleano: b };
              }
              case "date":
                return { valor_string: String(v), valor_fecha: String(v) };
              case "datetime":
                return { valor_string: String(v), valor_datetime: String(v) };
              case "json":
                return { valor_string: String(v), valor_json: String(v) };
              case "string":
              default:
                return { valor_string: String(v) };
            }
          }))}'
        >✏️</button>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
          <input type="hidden" name="action" value="delete_attr" />
          <input type="hidden" name="def_id" value="${def.id}" />
          <button type="submit" class="remove-component" title="Remover">×</button>
        </form>`;
      wrap.appendChild(chip);
    }
    // Actualiza nombre y meta con seguridad, soportando chips existentes (sin .meta-val)
    const nameEl = chip.querySelector('.name');
    if (nameEl) nameEl.textContent = def.etiqueta;
    const metaEl = chip.querySelector('.meta');
    if (metaEl) {
      while (metaEl.firstChild) metaEl.removeChild(metaEl.firstChild);
      metaEl.appendChild(document.createTextNode('· '));
      const strong = document.createElement('strong');
      strong.textContent = display;
      metaEl.appendChild(strong);
      if (unidad) metaEl.appendChild(document.createTextNode(' ' + unidad));
    }
    // Actualiza datos en el botón de edición para reflejar nuevos valores
    try {
      const editBtnForData = chip.querySelector('.js-edit-attr');
      if (editBtnForData) {
        const valuesForAttr = Array.isArray(rawValues) ? rawValues.map(v=>{
          const s = String(v).replace(/,/g,'.');
          switch(def.tipo_dato){
            case 'number':
              return { valor_string: String(v), valor_numero: (isNaN(parseFloat(s)) ? s : parseFloat(s)) };
            case 'integer':
              return { valor_string: String(v), valor_entero: (isNaN(parseInt(s,10)) ? s : parseInt(s,10)) };
            case 'boolean': {
              const b = (String(v).toLowerCase()==='1' || String(v).toLowerCase()==='true' || String(v).toLowerCase()==='sí' || String(v).toLowerCase()==='si') ? 1 : 0;
              return { valor_string: String(v), valor_booleano: b };
            }
            case 'date':
              return { valor_string: String(v), valor_fecha: String(v) };
            case 'datetime':
              return { valor_string: String(v), valor_datetime: String(v) };
            case 'json':
              return { valor_string: String(v), valor_json: String(v) };
            case 'string':
            default:
              return { valor_string: String(v) };
          }
        }) : [];
        editBtnForData.setAttribute('data-values', JSON.stringify(valuesForAttr));
      }
    } catch(_e){}
    // Rebind edit-open behavior
    try {
      const editBtn = chip.querySelector('.js-edit-attr');
      if (editBtn && !editBtn.dataset.bound) {
        editBtn.dataset.bound = '1';
        editBtn.addEventListener('click', () => {
          try {
            const defId = editBtn.getAttribute('data-attr-id');
            const label = editBtn.getAttribute('data-label');
            const tipo = editBtn.getAttribute('data-tipo');
            const unitsJson = editBtn.getAttribute('data-units');
            const unitDef = editBtn.getAttribute('data-unidad_def') || '';
            const vals = JSON.parse(editBtn.getAttribute('data-values') || '[]');
            document.getElementById('edit_def_id').value = defId;
            document.getElementById('editAttrInfo').textContent = label;
            const inputEl = document.getElementById('edit_valor');
            const unitSel = document.getElementById('edit_unidad');
            const unitGroup = document.getElementById('edit_unidad_group');
            inputEl.value = '';
            unitSel.innerHTML = '';
            if (Array.isArray(vals) && vals.length) {
              const parts = vals.map(v => {
                if (tipo === 'number') return (v.valor_string ?? v.valor_numero);
                if (tipo === 'integer') return (v.valor_string ?? v.valor_entero);
                if (tipo === 'boolean') return (parseInt(v.valor_booleano,10)===1?'1':'0');
                if (tipo === 'date') return v.valor_fecha;
                if (tipo === 'datetime') return v.valor_datetime;
                if (tipo === 'json') return v.valor_json;
                return v.valor_string;
              }).filter(Boolean);
              inputEl.value = parts.join(', ');
            }
            let units = [];
            try { const parsed = JSON.parse(unitsJson || '[]'); if (Array.isArray(parsed)) units = parsed; } catch(_e){ units = []; }
            const hasUnits = Array.isArray(units) && units.length > 0;
            const hasDefault = !!unitDef;
            if (hasUnits || hasDefault) {
              const opt0 = document.createElement('option'); opt0.value=''; opt0.textContent = unitDef ? `(por defecto: ${unitDef})` : '(sin unidad)'; unitSel.appendChild(opt0);
              if (hasUnits) units.forEach(u => { const o=document.createElement('option'); o.value=u; o.textContent=u; unitSel.appendChild(o); });
              if (unitGroup) unitGroup.style.display = '';
              console.log('🔍 [KitsEdit] Unidad visible (aplica)');
            } else {
              if (unitGroup) unitGroup.style.display = 'none';
              console.log('🔍 [KitsEdit] Unidad oculta (no aplica)');
            }
            openModal('#modalEditAttr');
          } catch(e) { console.log('❌ [KitsEdit] Error abrir modal editar (chip nuevo):', e && e.message); }
        });
      }
    } catch(_e){}
    // Intercept delete form submit to use AJAX
    try {
      const delForm = chip.querySelector('form');
      if (delForm && !delForm.dataset.bound){
        delForm.dataset.bound = '1';
        delForm.addEventListener('submit', (e) => {
          e.preventDefault();
          if (!confirm('¿Eliminar este atributo del kit?')) return;
          postAjaxKit(delForm, () => { chip.remove(); });
        });
      }
    } catch(_e){}
  }

  if (formEditAttr){
    formEditAttr.addEventListener('submit', (e) => {
      e.preventDefault();
      postAjaxKit(formEditAttr, (resp) => {
        const valuesRaw = Array.isArray(resp.raw_values) ? resp.raw_values : [];
        const display = resp.display || displayFromKit(resp.def.tipo_dato || 'string', valuesRaw);
        upsertAttrChipKit(resp.def, display, resp.unidad || '', valuesRaw);
        // close modal
        try { document.querySelector('[data-target="#modalEditAttr"]').click(); } catch(_e){}
        try { document.querySelector('#modalEditAttr')?.classList.remove('active'); } catch(_e){}
      });
    });
  }
  if (formAddAttr){
    formAddAttr.addEventListener('submit', (e) => {
      e.preventDefault();
      postAjaxKit(formAddAttr, (resp) => {
        const valuesRaw = Array.isArray(resp.raw_values) ? resp.raw_values : [];
        const display = resp.display || displayFromKit(resp.def.tipo_dato || 'string', valuesRaw);
        upsertAttrChipKit(resp.def, display, resp.unidad || '', valuesRaw);
        document.getElementById('attr_search').value = '';
        try { document.querySelector('[data-target="#modalAddAttr"]').click(); } catch(_e){}
        try { document.querySelector('#modalAddAttr')?.classList.remove('active'); } catch(_e){}
      });
    });
  }

  // Intercept existing delete forms inside chips to avoid page reload
  (function bindChipDeleteForms(){
    const wrap = document.getElementById('selected-attrs');
    if (!wrap) return;
    wrap.querySelectorAll('form').forEach(f => {
      if (f.dataset.bound === '1') return;
      f.dataset.bound = '1';
      f.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!confirm('¿Eliminar este atributo del kit?')) return;
        postAjaxKit(f, () => { try { f.closest('.component-chip')?.remove(); } catch(_e){} });
      });
    });
  })();
</script>
<?php endif; ?>
<!-- Clases vinculadas al Kit (Transfer List) -->
<div class="card" style="margin-top:2rem;">
  <h3>Clases vinculadas al Kit</h3>
  <small class="hint" style="display:block; margin-bottom:6px;">Selecciona una o varias clases. La primera será la principal.</small>
  <div class="dual-listbox-container">
    <div class="listbox-panel">
      <div class="listbox-header">
        <strong>Disponibles</strong>
        <span id="clases-available-count" class="counter">(<?= count($clases) ?>)</span>
      </div>
      <input type="text" id="search-clases" class="listbox-search" placeholder="🔍 Buscar clases...">
      <div class="listbox-content" id="available-clases">
        <?php foreach ($clases as $c): 
          $isSelected = in_array($c['id'], $existing_clase_ids);
        ?>
          <div class="competencia-item <?= $isSelected ? 'hidden' : '' ?>" 
               data-id="<?= (int)$c['id'] ?>"
               data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
               data-ciclo="<?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?>"
               onclick="selectClaseItem(this)">
            <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="comp-codigo">Ciclo <?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="listbox-buttons">
      <button type="button" onclick="moveAllClases(true)" title="Agregar todas">➡️</button>
      <button type="button" onclick="moveAllClases(false)" title="Quitar todas">⬅️</button>
    </div>
    <div class="listbox-panel">
      <div class="listbox-header">
        <strong>Seleccionadas</strong>
        <span id="clases-selected-count" class="counter">(<?= count($existing_clase_ids) ?>)</span>
      </div>
      <div class="listbox-content" id="selected-clases">
        <?php foreach ($clases as $c): if (in_array($c['id'], $existing_clase_ids)): ?>
          <div class="competencia-item selected" 
               data-id="<?= (int)$c['id'] ?>"
               data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
               data-ciclo="<?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?>"
               onclick="deselectClaseItem(this)">
            <span class="comp-nombre"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="comp-codigo">Ciclo <?= htmlspecialchars($c['ciclo'], ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectClaseItem(this.parentElement)">×</button>
          </div>
        <?php endif; endforeach; ?>
      </div>
      <small class="hint" style="margin-top: 10px; display: block;">Haz clic para quitar. Orden superior define el principal.</small>
    </div>
    <!-- Hidden inputs (outside form) must target kit-form -->
    <div id="clases-hidden"></div>
  </div>
</div>
<?php if ($is_edit): ?>
<!-- Modales para editar y agregar componentes -->
<style>
  /* Reusar estilos globales para modales; mantener utilidades locales */
  .muted { color: #666; font-size: 0.9rem; }
  .field-inline { display:flex; gap:12px; }
  .field-inline > div { flex:1; }
  /* Combo box styles */
  .combo { position: relative; }
  .combo-input { width: 100%; padding: 8px 32px 8px 10px; border: 1px solid #ccc; border-radius: 4px; }
  .combo-toggle { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: #f4f4f4; border: 1px solid #ccc; border-radius: 4px; width: 24px; height: 24px; display:flex; align-items:center; justify-content:center; cursor: pointer; }
  .combo-list { position: absolute; z-index: 10; left: 0; right: 0; top: calc(100% + 4px); max-height: 220px; overflow: auto; border: 1px solid #ccc; border-radius: 4px; background: #fff; display: none; }
  .combo.open .combo-list { display: block; }
  .combo-item { padding: 8px 10px; cursor: pointer; display:flex; align-items:center; justify-content: space-between; }
  .combo-item:hover, .combo-item.active { background: #eef6ff; }
  .combo-sku { color: #666; font-size: 0.85rem; }
  /* Ocultar select original pero mantenerlo para submit/validación */
  #add_item_id { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
</style>

<!-- Modal Editar Componente -->
<div class="modal-overlay" id="modalEditCmp">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
    <div class="modal-header">
      <h4 id="modalEditTitle">Editar componente</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalEditCmp">✖</button>
    </div>
    <form method="POST" id="formEditCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="update_item" />
      <input type="hidden" name="kc_item_id" id="edit_kc_item_id" />
      <div class="modal-body">
        <div class="muted" id="editCmpInfo"></div>
        <div class="field-inline">
          <div class="form-group">
            <label for="edit_cantidad">Cantidad</label>
            <input type="number" step="0.01" id="edit_cantidad" name="cantidad" required />
          </div>
          <div class="form-group">
            <label for="edit_orden">Orden</label>
            <input type="number" id="edit_orden" name="orden" />
          </div>
        </div>
        <div class="form-group">
          <label for="edit_notas">Notas</label>
          <input type="text" id="edit_notas" name="notas" maxlength="255" placeholder="p.ej. Indicaciones de uso" />
        </div>
          <div class="form-group">
            <label>
              <input type="checkbox" id="edit_incluido" name="es_incluido_kit" value="1" />
              Incluido en el kit
            </label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalEditCmp">Cancelar</button>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>
 </div>

<!-- Modal Agregar Componente -->
<div class="modal-overlay" id="modalAddCmp">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
    <div class="modal-header">
      <h4 id="modalAddTitle">Agregar componente</h4>
      <button type="button" class="modal-close js-close-modal" data-target="#modalAddCmp">✖</button>
    </div>
    <form method="POST" id="formAddCmp">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="add_item" />
      <div class="modal-body">
        <div class="form-group">
          <label for="combo_item_input">Componente</label>
          <div class="combo" id="combo_item">
            <input type="text" id="combo_item_input" class="combo-input" placeholder="Escribe para buscar y selecciona" autocomplete="off" />
            <button type="button" class="combo-toggle" aria-label="Abrir opciones" title="Abrir opciones">▾</button>
            <ul class="combo-list" id="combo_item_list">
              <?php foreach ($items as $it): ?>
                <li class="combo-item" data-value="<?= (int)$it['id'] ?>" data-name="<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>" data-sku="<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>">
                  <span><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="combo-sku">SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <!-- Select original permanece para envío/validación; se oculta por CSS -->
          <select id="add_item_id" name="item_id" required>
            <option value="">Selecciona componente</option>
            <?php foreach ($items as $it): ?>
              <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?> (SKU <?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-inline">
          <div class="form-group">
            <label for="add_cantidad">Cantidad</label>
            <input type="number" step="0.01" id="add_cantidad" name="cantidad" value="1" required />
          </div>
          <div class="form-group">
            <label for="add_orden">Orden</label>
            <input type="number" id="add_orden" name="orden" value="0" />
          </div>
        </div>
        <div class="form-group">
          <label for="add_notas">Notas (opcional)</label>
          <input type="text" id="add_notas" name="notas" maxlength="255" placeholder="p.ej. Indicaciones de uso" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary js-close-modal" data-target="#modalAddCmp">Cancelar</button>
        <button type="submit" class="btn">Agregar</button>
      </div>
    </form>
  </div>
 </div>

<script>
  // Utilidades de modal
  function openModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.add('active'); console.log('🔍 [KitsEdit] Abre modal', sel); }
  }
  function closeModal(sel) {
    const el = document.querySelector(sel);
    if (el) { el.classList.remove('active'); console.log('🔍 [KitsEdit] Cierra modal', sel); }
  }

  // Abrir modal de agregar
  const btnOpenAdd = document.querySelector('.js-open-add-modal');
  if (btnOpenAdd) {
    btnOpenAdd.addEventListener('click', () => openModal('#modalAddCmp'));
  }

  // Cerrar por botones con data-target
  document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const t = e.currentTarget.getAttribute('data-target');
      if (t) closeModal(t);
    });
  });

  // Cerrar al click en backdrop
  document.querySelectorAll('.modal-overlay').forEach(b => {
    b.addEventListener('click', (e) => { if (e.target === b) closeModal('#' + b.id); });
  });

  // Abrir modal de edición y prellenar
  document.querySelectorAll('.js-edit-item').forEach(btn => {
    btn.addEventListener('click', () => {
      const itemId = btn.getAttribute('data-item-id');
      const cantidad = btn.getAttribute('data-cantidad');
      const notas = btn.getAttribute('data-notas') || '';
      const orden = btn.getAttribute('data-orden') || '0';
      const nombre = btn.getAttribute('data-nombre') || '';
      const sku = btn.getAttribute('data-sku') || '';
      const unidad = btn.getAttribute('data-unidad') || '';
      const incluido = btn.getAttribute('data-incluido') || '1';

      document.getElementById('edit_kc_item_id').value = itemId;
      document.getElementById('edit_cantidad').value = cantidad;
      document.getElementById('edit_notas').value = notas;
      document.getElementById('edit_orden').value = orden;
      document.getElementById('editCmpInfo').textContent = `${nombre} (SKU ${sku}) · Unidad: ${unidad}`;
      try {
        const chk = document.getElementById('edit_incluido');
        if (chk) { chk.checked = (incluido === '1'); }
      } catch(_e){}

      console.log('🔍 [KitsEdit] Editar item', { itemId, cantidad, orden, incluido });
      openModal('#modalEditCmp');
    });
  });

  // Logs de envío de formularios
  const formEdit = document.getElementById('formEditCmp');
  if (formEdit) {
    formEdit.addEventListener('submit', () => console.log('📡 [KitsEdit] Enviando update_item...'));
  }
  const formAdd = document.getElementById('formAddCmp');
  if (formAdd) {
    formAdd.addEventListener('submit', () => console.log('📡 [KitsEdit] Enviando add_item...'));
  }

  
  // Combo Box para seleccionar componente (input + lista)
  (function initComboBox(){
    const combo = document.getElementById('combo_item');
    const input = document.getElementById('combo_item_input');
    const list = document.getElementById('combo_item_list');
    const selectEl = document.getElementById('add_item_id');
    if (!combo || !input || !list || !selectEl) { console.log('⚠️ [KitsEdit] ComboBox no inicializado'); return; }

    let items = Array.from(list.querySelectorAll('.combo-item')).map((li) => ({
      value: li.getAttribute('data-value'),
      name: li.getAttribute('data-name') || li.textContent.trim(),
      sku: li.getAttribute('data-sku') || '',
      text: li.textContent.trim()
    }));
    let activeIndex = -1;

    function normalize(str){
      return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    function renderList(matches){
      list.innerHTML = '';
      matches.forEach((m, idx) => {
        const li = document.createElement('li');
        li.className = 'combo-item' + (idx === 0 ? ' active' : '');
        li.dataset.value = m.value;
        li.dataset.name = m.name;
        li.dataset.sku = m.sku;
        li.innerHTML = `<span>${m.name}</span><span class="combo-sku">SKU ${m.sku}</span>`;
        li.addEventListener('click', () => selectItem(m));
        list.appendChild(li);
      });
      activeIndex = matches.length ? 0 : -1;
    }
    function open(){ combo.classList.add('open'); }
    function close(){ combo.classList.remove('open'); }
    function selectItem(item){
      input.value = `${item.name}`;
      selectEl.value = item.value;
      console.log('✅ [KitsEdit] Combo select', item);
      close();
    }
    function filter(q){
      const nq = normalize(q);
      const matches = nq ? items.filter(i => {
        return normalize(i.name).includes(nq) || normalize(i.sku).includes(nq) || normalize(i.text).includes(nq);
      }) : items.slice();
      renderList(matches);
      open();
      console.log('🔍 [KitsEdit] Combo filtro:', q, '→', matches.length);
    }

    // Eventos
    input.addEventListener('focus', () => { filter(input.value); });
    input.addEventListener('input', () => { filter(input.value); selectEl.value = ''; });
    combo.querySelector('.combo-toggle').addEventListener('click', () => {
      if (combo.classList.contains('open')) { close(); } else { filter(input.value); }
    });
    input.addEventListener('keydown', (e) => {
      const itemsEl = Array.from(list.querySelectorAll('.combo-item'));
      if (!itemsEl.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, itemsEl.length - 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); }
      else if (e.key === 'Enter') { e.preventDefault(); const li = itemsEl[activeIndex]; if (li) selectItem({ value: li.dataset.value, name: li.dataset.name, sku: li.dataset.sku, text: li.textContent.trim() }); }
      else if (e.key === 'Escape') { close(); }
      itemsEl.forEach((el, idx) => el.classList.toggle('active', idx === activeIndex));
    });

    // Reset al abrir modal
    const btnOpenAdd = document.querySelector('.js-open-add-modal');
    if (btnOpenAdd) {
      btnOpenAdd.addEventListener('click', () => { input.value = ''; selectEl.value = ''; renderList(items.slice()); open(); });
    }

    // Cerrar si clic fuera del combo
    document.addEventListener('click', (e) => {
      if (!combo.contains(e.target) && !e.target.closest('.js-open-add-modal')) close();
    });
  })();

  // Selector de componentes: búsqueda + autocompletado + abrir modal de agregar
  (function initComponentSearch(){
    const input = document.getElementById('component_search');
    const dropdown = document.getElementById('cmp_autocomplete_dropdown');
    const selectedWrap = document.getElementById('selected-components');
    const addSelect = document.getElementById('add_item_id');
    const comboInput = document.getElementById('combo_item_input');
    if (!input || !dropdown || !selectedWrap || !addSelect || !comboInput) { console.log('⚠️ [KitsEdit] Selector de componentes no inicializado'); return; }

    // Construir dataset de items disponibles
    const items = [
      <?php foreach ($items as $it): ?>
      { id: <?= (int)$it['id'] ?>, name: '<?= htmlspecialchars($it['nombre_comun'], ENT_QUOTES, 'UTF-8') ?>', sku: '<?= htmlspecialchars($it['sku'], ENT_QUOTES, 'UTF-8') ?>', unidad: '<?= htmlspecialchars($it['unidad'] ?? '', ENT_QUOTES, 'UTF-8') ?>' },
      <?php endforeach; ?>
    ];

    function normalize(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
    function selectedIds(){ return new Set(Array.from(selectedWrap.querySelectorAll('.component-chip')).map(el => parseInt(el.getAttribute('data-item-id'),10)).filter(Boolean)); }
    function nextOrden(){
      const ords = Array.from(selectedWrap.querySelectorAll('.component-chip')).map(el => parseInt(el.getAttribute('data-orden')||'0',10));
      const max = ords.length ? Math.max.apply(null, ords) : 0; return (isFinite(max) ? max : 0) + 1;
    }
    function render(list){
      if (!list.length){ dropdown.innerHTML = '<div class="autocomplete-item"><span class="cmp-code">Sin resultados</span></div>'; dropdown.style.display='block'; return; }
      dropdown.innerHTML = '';
      list.slice(0, 20).forEach(it => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML = `<strong>${it.name}</strong><span class="cmp-code">SKU ${it.sku}${it.unidad? ' · '+it.unidad:''}</span>`;
        div.addEventListener('click', () => onChoose(it));
        dropdown.appendChild(div);
      });
      dropdown.style.display = 'block';
    }
    function filter(q){
      const sel = selectedIds();
      const nq = normalize(q);
      const out = items.filter(it => !sel.has(it.id) && (nq ? (normalize(it.name).includes(nq) || normalize(it.sku).includes(nq)) : true));
      console.log('🔍 [KitsEdit] Buscar componente:', q, '→', out.length);
      render(out);
    }
    function onChoose(it){
      try {
        // Preseleccionar en modal de agregar
        addSelect.value = String(it.id);
        comboInput.value = it.name;
        // Sugerir siguiente orden
        const ordEl = document.getElementById('add_orden');
        if (ordEl) ordEl.value = nextOrden();
        const qtyEl = document.getElementById('add_cantidad');
        if (qtyEl && (!qtyEl.value || Number(qtyEl.value) <= 0)) qtyEl.value = 1;
        console.log('✅ [KitsEdit] Seleccionado para agregar:', it);
        openModal('#modalAddCmp');
        setTimeout(() => { try { document.getElementById('add_cantidad')?.focus(); } catch(_e){} }, 50);
      } catch (e) {
        console.log('❌ [KitsEdit] Error al preparar modal agregar:', e && e.message);
      }
      dropdown.style.display = 'none';
    }

    input.addEventListener('focus', () => filter(input.value));
    input.addEventListener('input', () => filter(input.value));

    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target) && e.target !== input) dropdown.style.display = 'none';
    });
  })();
</script>
<!-- Editor: CKEditor 4 for Kit contenido_html (match Clases) -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  (function initCKEKit(){
    try {
      if (window.CKEDITOR) {
        CKEDITOR.replace('contenido_html', {
          height: 500,
          removePlugins: 'elementspath',
          resize_enabled: true,
          contentsCss: ['/assets/css/style.css', '/assets/css/article-content.css'],
          bodyClass: 'article-body'
        });
        console.log('✅ [KitsEdit] CKEditor 4 cargado');
      } else {
        console.log('⚠️ [KitsEdit] CKEditor no disponible, usando textarea simple');
      }
    } catch(e) {
      console.log('❌ [KitsEdit] Error iniciando CKEditor:', e && e.message);
    }
  })();
  // Oculta avisos de CKEditor sobre versión insegura usando CSS (sin remover nodos)
  (function hideCkeWarningsCss(){
    try {
      const style = document.createElement('style');
      style.setAttribute('data-cke-warn-hide','1');
      style.textContent = `
        .cke_notification.cke_notification_warning,
        .cke_upgrade_notice,
        .cke_browser_warning,
        .cke_panel_warning,
        .cke_warning { display: none !important; }
      `;
      document.head.appendChild(style);
      console.log('✅ [KitsEdit] CKEditor warnings ocultos por CSS');
    } catch(e) {
      console.log('⚠️ [KitsEdit] No se pudo inyectar CSS para warnings:', e && e.message);
    }
  })();
</script>
<script>
  // SEO auto y preview (similar a Clases)
  (function initKitSeo(){
    const nombreInput = document.getElementById('nombre');
    const resumenInput = document.getElementById('resumen');
    const seoTitleInput = document.getElementById('seo_title');
    const seoDescInput = document.getElementById('seo_description');
    const seoPrevTitle = document.getElementById('seo_preview_title');
    const seoPrevDesc = document.getElementById('seo_preview_desc');
    const seoToggle = document.getElementById('seo_override_toggle');
    const btnGenerarSeo = document.getElementById('btn_generar_seo');

    function textFromHtml(html){
      const tmp = document.createElement('div'); tmp.innerHTML = html || ''; const txt = (tmp.textContent || tmp.innerText || '').replace(/\s+/g,' ').trim(); return txt;
    }
    function shortenAtWord(str, maxLen){ if (!str) return ''; if (str.length <= maxLen) return str; const cut = str.slice(0, maxLen); return cut.replace(/\s+\S*$/, '').trim(); }

    function computeSeo(force=false){
      // Área: primera seleccionada
      let areaNombre = '';
      const areasChecked = document.querySelectorAll('input[name="areas[]"]:checked');
      if (areasChecked.length > 0) {
        const label = areasChecked[0].closest('label');
        if (label) areaNombre = label.textContent.trim();
      }
      const base = 'Kit de Ciencia - ';
      const nombreVal = (nombreInput && nombreInput.value ? nombreInput.value.trim() : '');
      let autoTitle = '';
      if (areaNombre) {
        const formato1 = base + areaNombre + ': ' + nombreVal;
        if (formato1.length <= 60) autoTitle = formato1; else {
          const sep = ' | ' + areaNombre;
          const maxNombre = 60 - base.length - sep.length;
          const nombreCorto = nombreVal.length > maxNombre ? (nombreVal.substring(0, Math.max(0, maxNombre-3)) + '...') : nombreVal;
          autoTitle = base + nombreCorto + sep;
        }
      } else {
        const maxNombre = 60 - base.length;
        const nombreCorto = nombreVal.length > maxNombre ? (nombreVal.substring(0, Math.max(0, maxNombre-3)) + '...') : nombreVal;
        autoTitle = base + nombreCorto;
      }

      // Descripción desde resumen o contenido_html
      let descSrc = (resumenInput && resumenInput.value.trim()) ? resumenInput.value.trim() : '';
      if (!descSrc) {
        try {
          if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.contenido_html) {
            descSrc = textFromHtml(CKEDITOR.instances.contenido_html.getData() || '');
          } else {
            const ta = document.getElementById('contenido_html');
            descSrc = textFromHtml(ta ? ta.value : '');
          }
        } catch(e) { descSrc = ''; }
      }
      const autoDesc = shortenAtWord(descSrc, 160);

      if (seoPrevTitle) seoPrevTitle.textContent = autoTitle;
      if (seoPrevDesc) seoPrevDesc.textContent = autoDesc;

      // Autorrellenar si no override
      if (!seoToggle?.checked || force) {
        if (seoTitleInput && (!seoTitleInput.value || force)) seoTitleInput.value = autoTitle;
        if (seoDescInput && (!seoDescInput.value || force)) seoDescInput.value = autoDesc;
        console.log('🔍 [SEO] autogenerados (kit):', { area: areaNombre, title: autoTitle.substring(0,50)+'...', forced: force });
      }
    }

    if (seoToggle) {
      seoToggle.addEventListener('change', () => {
        const manual = document.getElementById('seo-manual');
        if (manual) manual.style.display = seoToggle.checked ? 'block' : 'none';
        console.log(seoToggle.checked ? '✅ [SEO] override manual activado (kit)' : '🔍 [SEO] usando auto (kit)');
      });
    }
    if (btnGenerarSeo) {
      btnGenerarSeo.addEventListener('click', () => {
        if (!nombreInput?.value.trim()) { alert('Por favor ingresa el nombre del kit primero'); nombreInput?.focus(); return; }
        computeSeo(true);
        // feedback visual
        if (seoTitleInput) seoTitleInput.style.background = '#e6f7ff';
        if (seoDescInput) seoDescInput.style.background = '#e6f7ff';
        setTimeout(()=>{ if (seoTitleInput) seoTitleInput.style.background=''; if (seoDescInput) seoDescInput.style.background=''; }, 1000);
        console.log('⚡ [KitsEdit] SEO regenerado manualmente');
      });
    }

    // Recalcular al editar nombre/resumen/áreas
    nombreInput?.addEventListener('input', computeSeo);
    resumenInput?.addEventListener('input', computeSeo);
    document.querySelectorAll('input[name="areas[]"]').forEach(cb => cb.addEventListener('change', () => { console.log('🔍 [SEO] Área cambiada (kit)'); computeSeo(); }));

    // Inicializa preview
    computeSeo();
  })();
  // Validación simple de límites
  (function bindSeoLenChecks(){
    const seoTitle = document.getElementById('seo_title');
    const seoDesc = document.getElementById('seo_description');
    if (seoTitle) seoTitle.addEventListener('input', ()=>{ if (seoTitle.value.length>160) console.log('⚠️ [KitsEdit] SEO title >160'); });
    if (seoDesc) seoDesc.addEventListener('input', ()=>{ if (seoDesc.value.length>255) console.log('⚠️ [KitsEdit] SEO description >255'); });
  })();
</script>
<script>
  // Dual Listbox: Clases vinculadas al kit (siempre activo)
  (function initClasesTransfer(){
    const available = document.getElementById('available-clases');
    const selected = document.getElementById('selected-clases');
    const search = document.getElementById('search-clases');
    const hidden = document.getElementById('clases-hidden');
    const availableCount = document.getElementById('clases-available-count');
    const selectedCount = document.getElementById('clases-selected-count');
    if (!available || !selected || !hidden) { console.log('⚠️ [KitsEdit] Transfer de clases no inicializado'); return; }

    function updateHidden(){
      hidden.innerHTML = '';
      const ids = Array.from(selected.querySelectorAll('.competencia-item')).map(el => parseInt(el.dataset.id, 10)).filter(Boolean);
      ids.forEach((id, idx) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'clases[]';
        input.value = id;
        input.setAttribute('form', 'kit-form'); // ensure submit with main form
        hidden.appendChild(input);
      });
      if (selectedCount) selectedCount.textContent = '(' + ids.length + ')';
      const availVisible = Array.from(available.querySelectorAll('.competencia-item')).filter(el => !el.classList.contains('hidden') && el.style.display !== 'none').length;
      if (availableCount) availableCount.textContent = '(' + availVisible + ')';
      console.log('🔍 [KitsEdit] Clases seleccionadas:', ids);
    }

    window.selectClaseItem = function(el){
      el.classList.add('hidden');
      const id = el.dataset.id;
      const nombre = el.dataset.nombre;
      const ciclo = el.dataset.ciclo;
      const node = document.createElement('div');
      node.className = 'competencia-item selected';
      node.dataset.id = id;
      node.dataset.nombre = nombre;
      node.dataset.ciclo = ciclo;
      node.innerHTML = `<span class="comp-nombre">${nombre}</span><span class="comp-codigo">Ciclo ${ciclo}</span><button type="button" class="remove-btn" onclick="event.stopPropagation(); deselectClaseItem(this.parentElement)">×</button>`;
      node.onclick = function(){ window.deselectClaseItem(node); };
      selected.appendChild(node);
      updateHidden();
    };

    window.deselectClaseItem = function(el){
      const id = el.dataset.id;
      el.remove();
      const avail = available.querySelector(`.competencia-item[data-id="${id}"]`);
      if (avail) avail.classList.remove('hidden');
      updateHidden();
    };

    window.moveAllClases = function(add){
      if (add) {
        const vis = Array.from(available.querySelectorAll('.competencia-item:not(.hidden)')).filter(el => el.style.display !== 'none');
        vis.forEach(el => selectClaseItem(el));
      } else {
        const sel = Array.from(selected.querySelectorAll('.competencia-item'));
        sel.forEach(el => deselectClaseItem(el));
      }
      updateHidden();
    };

    if (search) {
      search.addEventListener('input', () => {
        const q = (search.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        available.querySelectorAll('.competencia-item').forEach(el => {
          const n = (el.dataset.nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
          const c = (el.dataset.ciclo || '').toString();
          const match = n.includes(q) || c.includes(q);
          el.style.display = match ? '' : 'none';
        });
        const visibleCount = Array.from(available.querySelectorAll('.competencia-item')).filter(el => el.style.display !== 'none' && !el.classList.contains('hidden')).length;
        if (availableCount) availableCount.textContent = '(' + visibleCount + ')';
        console.log('🔍 [KitsEdit] Buscar clases:', search.value, '→', visibleCount);
      });
    }

    // Inicializar inputs ocultos con selección actual
    updateHidden();
  })();
</script>
<?php endif; ?>
<div class="form-actions" style="margin-top:2rem;">
  <button type="submit" class="btn" form="kit-form">Guardar</button>
  <a href="/admin/kits/index.php" class="btn btn-secondary">Cancelar</a>
  <?php if ($is_edit && !empty($kit['slug'])): ?>
    <a href="/<?= htmlspecialchars($kit['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn">Ver público</a>
  <?php endif; ?>
</div>
<?php include '../footer.php'; ?>
