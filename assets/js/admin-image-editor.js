(function initAdminImageEditor() {
  if (window.AdminImageEditor) {
    return;
  }

  const PRESETS = {
    'kit-cover': { width: 1200, height: 675, label: 'Kit portada 1200x675' },
    'clase-cover': { width: 1200, height: 675, label: 'Clase portada 1200x675' },
    'componente-thumb': { width: 800, height: 800, label: 'Componente 800x800' },
    'generic-cover': { width: 1200, height: 675, label: 'General 1200x675' }
  };

  let state = {
    sourceImage: null,
    sourceName: '',
    scale: 1,
    minScale: 1,
    offsetX: 0,
    offsetY: 0,
    dragging: false,
    dragStartX: 0,
    dragStartY: 0,
    dragBaseX: 0,
    dragBaseY: 0,
    preset: 'generic-cover',
    targetInputId: '',
    entity: 'general',
    csrfToken: ''
  };

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  function escapeHtml(str) {
    return String(str || '').replace(/[&<>'\"]/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[c] || c;
    });
  }

  function buildModal() {
    if (qs('#adminImageEditorModal')) {
      return;
    }

    const html = '' +
      '<div id="adminImageEditorModal" class="image-editor-modal" aria-hidden="true">' +
      '  <div class="image-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="imageEditorTitle">' +
      '    <div class="image-editor-header">' +
      '      <h3 id="imageEditorTitle">Editor de imagen</h3>' +
      '      <button type="button" class="btn btn-secondary" id="imageEditorCloseTop">Cerrar</button>' +
      '    </div>' +
      '    <div class="image-editor-body">' +
      '      <div class="image-editor-canvas-wrap">' +
      '        <canvas id="imageEditorViewport" width="720" height="405"></canvas>' +
      '      </div>' +
      '      <div class="image-editor-side">' +
      '        <label class="btn" for="imageEditorFileInput">📁 Seleccionar imagen</label>' +
      '        <input id="imageEditorFileInput" type="file" accept="image/jpeg,image/png,image/webp" style="display:none;" />' +
      '        <p class="hint" id="imageEditorMeta">Sin imagen seleccionada.</p>' +
      '        <label for="imageEditorZoom">Zoom</label>' +
      '        <input id="imageEditorZoom" type="range" min="1" max="3" step="0.01" value="1" />' +
      '        <div class="image-editor-actions">' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorZoomOut">-</button>' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorZoomIn">+</button>' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorReset">Reset</button>' +
      '        </div>' +
      '        <p class="hint" id="imageEditorPresetHint"></p>' +
      '      </div>' +
      '    </div>' +
      '    <div class="image-editor-footer">' +
      '      <small class="hint">Arrastra para mover el encuadre y usa zoom para ajustar.</small>' +
      '      <div class="image-editor-actions">' +
      '        <button type="button" class="btn btn-secondary" id="imageEditorCancel">Cancelar</button>' +
      '        <button type="button" class="btn" id="imageEditorSave">💾 Guardar imagen</button>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>';

    document.body.insertAdjacentHTML('beforeend', html);

    bindModalEvents();
  }

  function getPresetInfo() {
    return PRESETS[state.preset] || PRESETS['generic-cover'];
  }

  function openEditor(opts) {
    buildModal();
    state.preset = opts.preset || 'generic-cover';
    state.targetInputId = opts.targetInputId || '';
    state.entity = opts.entity || 'general';
    state.csrfToken = opts.csrfToken || '';
    state.sourceImage = null;
    state.sourceName = '';

    const modal = qs('#adminImageEditorModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    const info = getPresetInfo();
    qs('#imageEditorPresetHint').textContent = 'Preset: ' + info.label;
    qs('#imageEditorMeta').textContent = 'Sin imagen seleccionada.';
    qs('#imageEditorFileInput').value = '';

    resetTransform();
    render();
    console.log('🔍 [ImageEditor] Abierto con preset:', state.preset, 'input:', state.targetInputId);
  }

  function closeEditor() {
    const modal = qs('#adminImageEditorModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }

  function resetTransform() {
    state.scale = 1;
    state.offsetX = 0;
    state.offsetY = 0;
    state.minScale = 1;
    const zoom = qs('#imageEditorZoom');
    if (zoom) {
      zoom.value = '1';
    }
  }

  function bindModalEvents() {
    const canvas = qs('#imageEditorViewport');
    const fileInput = qs('#imageEditorFileInput');
    const zoom = qs('#imageEditorZoom');

    qs('#imageEditorCloseTop').addEventListener('click', closeEditor);
    qs('#imageEditorCancel').addEventListener('click', closeEditor);
    qs('#imageEditorZoomIn').addEventListener('click', function () {
      setScale(state.scale + 0.08);
    });
    qs('#imageEditorZoomOut').addEventListener('click', function () {
      setScale(state.scale - 0.08);
    });
    qs('#imageEditorReset').addEventListener('click', function () {
      fitImageToView();
      render();
      console.log('✅ [ImageEditor] Reset de encuadre');
    });

    zoom.addEventListener('input', function () {
      setScale(parseFloat(zoom.value));
    });

    canvas.addEventListener('mousedown', function (ev) {
      if (!state.sourceImage) return;
      state.dragging = true;
      state.dragStartX = ev.clientX;
      state.dragStartY = ev.clientY;
      state.dragBaseX = state.offsetX;
      state.dragBaseY = state.offsetY;
      canvas.classList.add('is-dragging');
    });

    window.addEventListener('mousemove', function (ev) {
      if (!state.dragging) return;
      state.offsetX = state.dragBaseX + (ev.clientX - state.dragStartX);
      state.offsetY = state.dragBaseY + (ev.clientY - state.dragStartY);
      clampOffsets();
      render();
    });

    window.addEventListener('mouseup', function () {
      if (!state.dragging) return;
      state.dragging = false;
      canvas.classList.remove('is-dragging');
    });

    canvas.addEventListener('wheel', function (ev) {
      if (!state.sourceImage) return;
      ev.preventDefault();
      const delta = ev.deltaY < 0 ? 0.06 : -0.06;
      setScale(state.scale + delta);
    }, { passive: false });

    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
      if (!file) return;
      if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
        alert('Formato no permitido. Usa JPG, PNG o WEBP.');
        return;
      }

      const reader = new FileReader();
      reader.onload = function () {
        const img = new Image();
        img.onload = function () {
          state.sourceImage = img;
          state.sourceName = file.name || 'imagen';
          fitImageToView();
          render();
          qs('#imageEditorMeta').textContent = 'Archivo: ' + state.sourceName + ' (' + img.width + 'x' + img.height + ')';
          console.log('✅ [ImageEditor] Imagen cargada:', state.sourceName, img.width + 'x' + img.height);
        };
        img.onerror = function () {
          alert('No se pudo cargar la imagen seleccionada.');
        };
        img.src = reader.result;
      };
      reader.readAsDataURL(file);
    });

    qs('#imageEditorSave').addEventListener('click', async function () {
      if (!state.sourceImage || !state.targetInputId) {
        alert('Selecciona una imagen antes de guardar.');
        return;
      }

      try {
        const blob = await exportBlob();
        const fd = new FormData();
        fd.append('csrf_token', state.csrfToken || '');
        fd.append('preset', state.preset);
        fd.append('entity', state.entity || 'general');
        fd.append('image', blob, 'edited-image.webp');

        console.log('📡 [ImageEditor] Subiendo imagen procesada...');
        const response = await fetch('/admin/media/upload-image.php', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        const json = await response.json();

        if (!json || !json.ok) {
          throw new Error((json && json.error) ? json.error : 'Error desconocido al subir imagen');
        }

        const input = document.getElementById(state.targetInputId);
        if (!input) {
          throw new Error('No se encontró el input destino');
        }

        input.value = json.url;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        const previewId = input.getAttribute('data-preview-target');
        if (previewId) {
          const preview = document.getElementById(previewId);
          if (preview && preview.tagName === 'IMG') {
            preview.src = json.url;
          }
        }

        closeEditor();
        console.log('✅ [ImageEditor] Imagen guardada y URL aplicada:', json.url);
      } catch (err) {
        console.log('❌ [ImageEditor] Error guardando imagen:', err && err.message);
        alert('No se pudo guardar la imagen: ' + (err && err.message ? err.message : 'Error')); 
      }
    });
  }

  function fitImageToView() {
    const canvas = qs('#imageEditorViewport');
    const img = state.sourceImage;
    if (!canvas || !img) return;

    const cw = canvas.width;
    const ch = canvas.height;
    const info = getPresetInfo();

    const imgRatio = img.width / img.height;
    const presetRatio = info.width / info.height;

    const baseScaleX = cw / img.width;
    const baseScaleY = ch / img.height;
    const fitScale = Math.max(baseScaleX, baseScaleY);

    state.minScale = fitScale;
    state.scale = fitScale;

    const drawW = img.width * state.scale;
    const drawH = img.height * state.scale;
    state.offsetX = (cw - drawW) / 2;
    state.offsetY = (ch - drawH) / 2;

    const zoom = qs('#imageEditorZoom');
    if (zoom) {
      const max = Math.max(fitScale * 4, fitScale + 2);
      zoom.min = String(fitScale);
      zoom.max = String(max);
      zoom.step = String((max - fitScale) / 200);
      zoom.value = String(state.scale);
    }

    console.log('🔍 [ImageEditor] Fit inicial aplicado. Preset ratio:', presetRatio.toFixed(3), 'img ratio:', imgRatio.toFixed(3));
  }

  function setScale(nextScale) {
    if (!state.sourceImage) return;
    const zoom = qs('#imageEditorZoom');
    const min = parseFloat(zoom.min || String(state.minScale || 1));
    const max = parseFloat(zoom.max || '4');

    state.scale = Math.max(min, Math.min(max, nextScale));
    if (zoom) {
      zoom.value = String(state.scale);
    }
    clampOffsets();
    render();
  }

  function clampOffsets() {
    if (!state.sourceImage) return;
    const canvas = qs('#imageEditorViewport');
    const cw = canvas.width;
    const ch = canvas.height;

    const drawW = state.sourceImage.width * state.scale;
    const drawH = state.sourceImage.height * state.scale;

    if (drawW <= cw) {
      state.offsetX = (cw - drawW) / 2;
    } else {
      const minX = cw - drawW;
      const maxX = 0;
      state.offsetX = Math.max(minX, Math.min(maxX, state.offsetX));
    }

    if (drawH <= ch) {
      state.offsetY = (ch - drawH) / 2;
    } else {
      const minY = ch - drawH;
      const maxY = 0;
      state.offsetY = Math.max(minY, Math.min(maxY, state.offsetY));
    }
  }

  function render() {
    const canvas = qs('#imageEditorViewport');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const cw = canvas.width;
    const ch = canvas.height;

    ctx.clearRect(0, 0, cw, ch);
    ctx.fillStyle = '#111827';
    ctx.fillRect(0, 0, cw, ch);

    const info = getPresetInfo();
    const maskRatio = info.width / info.height;
    let maskW = cw * 0.9;
    let maskH = maskW / maskRatio;
    if (maskH > ch * 0.9) {
      maskH = ch * 0.9;
      maskW = maskH * maskRatio;
    }
    const maskX = (cw - maskW) / 2;
    const maskY = (ch - maskH) / 2;

    if (state.sourceImage) {
      clampOffsets();
      const drawW = state.sourceImage.width * state.scale;
      const drawH = state.sourceImage.height * state.scale;
      ctx.drawImage(state.sourceImage, state.offsetX, state.offsetY, drawW, drawH);
    }

    ctx.save();
    ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
    ctx.beginPath();
    ctx.rect(0, 0, cw, ch);
    ctx.rect(maskX, maskY, maskW, maskH);
    ctx.fill('evenodd');
    ctx.restore();

    ctx.strokeStyle = '#22c55e';
    ctx.lineWidth = 2;
    ctx.strokeRect(maskX, maskY, maskW, maskH);
  }

  function exportBlob() {
    return new Promise(function (resolve, reject) {
      if (!state.sourceImage) {
        reject(new Error('Sin imagen de origen'));
        return;
      }

      const viewport = qs('#imageEditorViewport');
      const info = getPresetInfo();
      const exportCanvas = document.createElement('canvas');
      exportCanvas.width = info.width;
      exportCanvas.height = info.height;
      const ctx = exportCanvas.getContext('2d');

      const cw = viewport.width;
      const ch = viewport.height;
      const ratio = info.width / info.height;

      let maskW = cw * 0.9;
      let maskH = maskW / ratio;
      if (maskH > ch * 0.9) {
        maskH = ch * 0.9;
        maskW = maskH * ratio;
      }
      const maskX = (cw - maskW) / 2;
      const maskY = (ch - maskH) / 2;

      const sx = (maskX - state.offsetX) / state.scale;
      const sy = (maskY - state.offsetY) / state.scale;
      const sw = maskW / state.scale;
      const sh = maskH / state.scale;

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
      ctx.drawImage(state.sourceImage, sx, sy, sw, sh, 0, 0, exportCanvas.width, exportCanvas.height);

      exportCanvas.toBlob(function (blob) {
        if (!blob) {
          reject(new Error('No se pudo exportar imagen'));
          return;
        }
        resolve(blob);
      }, 'image/webp', 0.9);
    });
  }

  function bindTriggers() {
    qsa('.js-image-picker-trigger').forEach(function (btn) {
      if (btn.dataset.editorBound === '1') {
        return;
      }
      btn.dataset.editorBound = '1';
      btn.addEventListener('click', function () {
        const targetInputId = btn.getAttribute('data-target-input') || '';
        const preset = btn.getAttribute('data-preset') || 'generic-cover';
        const entity = btn.getAttribute('data-entity') || 'general';
        const csrfToken = (qs('#kit-form input[name="csrf_token"]') || qs('#clase-form input[name="csrf_token"]') || qs('#cmp-form input[name="csrf_token"]') || qs('input[name="csrf_token"]'))?.value || '';

        if (!targetInputId) {
          alert('No se encontró input destino para esta acción.');
          return;
        }
        openEditor({ targetInputId: targetInputId, preset: preset, entity: entity, csrfToken: csrfToken });
      });
    });
  }

  window.AdminImageEditor = {
    bind: bindTriggers,
    open: openEditor
  };

  bindTriggers();
  console.log('✅ [ImageEditor] Módulo inicializado');
})();
