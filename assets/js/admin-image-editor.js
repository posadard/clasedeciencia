(function initAdminImageEditor() {
  if (window.AdminImageEditor) {
    return;
  }

  const PRESETS = {
    'kit-cover': { width: 800, height: 800, label: 'Kit portada 800x800' },
    'clase-cover': { width: 800, height: 800, label: 'Clase portada 800x800' },
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
    existingUrl: '',
    entity: 'general',
    csrfToken: '',
    backgroundColor: '#ffffff',
    metaTitleInputId: '',
    metaDescriptionInputId: '',
    metaMimeInputId: '',
    metaWidthInputId: '',
    metaHeightInputId: '',
    metaUploadDateInputId: '',
    metaRoleInputId: '',
    metaCreatorInputId: '',
    metaLanguageInputId: '',
    metaTitleSourceId: '',
    metaDescriptionSourceId: ''
  };

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  function isManagedUploadUrl(url) {
    if (!url) return false;
    const v = String(url).trim();
    if (v === '') return false;
    if (v.indexOf('/assets/images/uploads/') === 0) return true;
    try {
      const u = new URL(v, window.location.origin);
      return u.origin === window.location.origin && u.pathname.indexOf('/assets/images/uploads/') === 0;
    } catch (_e) {
      return false;
    }
  }

  function clearLoadedImage() {
    state.sourceImage = null;
    state.sourceName = '';
    state.backgroundColor = '#f3f4f6';
    resetTransform();
    render();
    const meta = qs('#imageEditorMeta');
    if (meta) {
      meta.textContent = 'Sin imagen seleccionada.';
    }
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
      '        <div class="image-editor-actions">' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorLoadCurrent">Usar imagen actual</button>' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorDeleteCurrent">Eliminar actual</button>' +
      '        </div>' +
      '        <label for="imageEditorZoom">Zoom</label>' +
      '        <input id="imageEditorZoom" type="range" min="1" max="3" step="0.01" value="1" />' +
      '        <div class="image-editor-actions">' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorZoomOut">-</button>' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorZoomIn">+</button>' +
      '          <button type="button" class="btn btn-secondary" id="imageEditorReset">Reset</button>' +
      '        </div>' +
        '        <p class="hint" id="imageEditorPresetHint"></p>' +
        '        <div class="image-editor-metadata">' +
        '          <h4>Metadata de la imagen</h4>' +
        '          <label for="imageEditorMetaTitle">Titulo</label>' +
        '          <input id="imageEditorMetaTitle" type="text" maxlength="255" />' +
        '          <label for="imageEditorMetaDescription">Descripcion</label>' +
        '          <input id="imageEditorMetaDescription" type="text" maxlength="255" />' +
        '          <label for="imageEditorMetaRole">Rol schema</label>' +
        '          <select id="imageEditorMetaRole">' +
        '            <option value="primary">Primary</option>' +
        '            <option value="gallery">Gallery</option>' +
        '            <option value="tutorial">Tutorial</option>' +
        '            <option value="download">Download</option>' +
        '            <option value="external">External</option>' +
        '          </select>' +
        '          <div class="image-editor-grid-2">' +
        '            <div>' +
        '              <label for="imageEditorMetaMime">MIME</label>' +
        '              <input id="imageEditorMetaMime" type="text" />' +
        '            </div>' +
        '            <div>' +
        '              <label for="imageEditorMetaLanguage">Idioma</label>' +
        '              <input id="imageEditorMetaLanguage" type="text" />' +
        '            </div>' +
        '            <div>' +
        '              <label for="imageEditorMetaWidth">Ancho</label>' +
        '              <input id="imageEditorMetaWidth" type="number" min="0" />' +
        '            </div>' +
        '            <div>' +
        '              <label for="imageEditorMetaHeight">Alto</label>' +
        '              <input id="imageEditorMetaHeight" type="number" min="0" />' +
        '            </div>' +
        '          </div>' +
        '          <label for="imageEditorMetaUploadDate">Fecha upload</label>' +
        '          <input id="imageEditorMetaUploadDate" type="text" placeholder="YYYY-MM-DD HH:MM:SS o ISO8601" />' +
        '          <label for="imageEditorMetaCreator">Autor/creador</label>' +
        '          <input id="imageEditorMetaCreator" type="text" />' +
        '        </div>' +
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
    state.existingUrl = opts.existingUrl || '';
    state.entity = opts.entity || 'general';
    state.csrfToken = opts.csrfToken || '';
    state.metaTitleInputId = opts.metaTitleInputId || '';
    state.metaDescriptionInputId = opts.metaDescriptionInputId || '';
    state.metaMimeInputId = opts.metaMimeInputId || '';
    state.metaWidthInputId = opts.metaWidthInputId || '';
    state.metaHeightInputId = opts.metaHeightInputId || '';
    state.metaUploadDateInputId = opts.metaUploadDateInputId || '';
    state.metaRoleInputId = opts.metaRoleInputId || '';
    state.metaCreatorInputId = opts.metaCreatorInputId || '';
    state.metaLanguageInputId = opts.metaLanguageInputId || '';
    state.metaTitleSourceId = opts.metaTitleSourceId || '';
    state.metaDescriptionSourceId = opts.metaDescriptionSourceId || '';

    const modal = qs('#adminImageEditorModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    const info = getPresetInfo();
    qs('#imageEditorPresetHint').textContent = 'Preset: ' + info.label;
    qs('#imageEditorFileInput').value = '';
    loadModalMetadata();

    clearLoadedImage();

    if (state.existingUrl) {
      loadImageFromUrl(state.existingUrl).catch(function (err) {
        console.log('⚠️ [ImageEditor] No se pudo precargar imagen actual:', err && err.message);
      });
    }

    console.log('🔍 [ImageEditor] Abierto con preset:', state.preset, 'input:', state.targetInputId);
  }

  function stripHtml(text) {
    const tmp = document.createElement('div');
    tmp.innerHTML = String(text || '');
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
  }

  function setInputValue(inputId, value) {
    if (!inputId) return;
    const input = document.getElementById(inputId);
    if (!input) return;
    input.value = String(value == null ? '' : value);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function applyAutoMetadata(json) {
    const titleSource = state.metaTitleSourceId ? document.getElementById(state.metaTitleSourceId) : null;
    const descSource = state.metaDescriptionSourceId ? document.getElementById(state.metaDescriptionSourceId) : null;
    const autoTitle = titleSource ? String(titleSource.value || '').trim() : '';
    const autoDesc = descSource ? stripHtml(descSource.value || '') : '';

    const titleInput = state.metaTitleInputId ? document.getElementById(state.metaTitleInputId) : null;
    const descInput = state.metaDescriptionInputId ? document.getElementById(state.metaDescriptionInputId) : null;
    if (state.metaTitleInputId && autoTitle && (!titleInput || !String(titleInput.value || '').trim())) setInputValue(state.metaTitleInputId, autoTitle);
    if (state.metaDescriptionInputId && autoDesc && (!descInput || !String(descInput.value || '').trim())) setInputValue(state.metaDescriptionInputId, autoDesc.slice(0, 255));
    if (state.metaMimeInputId) setInputValue(state.metaMimeInputId, json && json.mime_type ? json.mime_type : 'image/webp');
    if (state.metaWidthInputId) setInputValue(state.metaWidthInputId, json && json.width ? json.width : '');
    if (state.metaHeightInputId) setInputValue(state.metaHeightInputId, json && json.height ? json.height : '');
    if (state.metaUploadDateInputId && json && json.upload_date) setInputValue(state.metaUploadDateInputId, json.upload_date);
    if (state.metaRoleInputId) {
      const roleInput = document.getElementById(state.metaRoleInputId);
      if (roleInput && !String(roleInput.value || '').trim()) {
        setInputValue(state.metaRoleInputId, 'primary');
      }
    }
    if (state.metaLanguageInputId) {
      const langInput = document.getElementById(state.metaLanguageInputId);
      if (langInput && !String(langInput.value || '').trim()) {
        setInputValue(state.metaLanguageInputId, 'es-CO');
      }
    }

    console.log('✅ [ImageEditor] Metadata autocompletada');
  }

  function modalMetadataValue(id) {
    const el = qs('#' + id);
    return el ? String(el.value || '').trim() : '';
  }

  function loadModalMetadata() {
    const titleSource = state.metaTitleSourceId ? document.getElementById(state.metaTitleSourceId) : null;
    const descSource = state.metaDescriptionSourceId ? document.getElementById(state.metaDescriptionSourceId) : null;

    const hiddenTitle = state.metaTitleInputId ? document.getElementById(state.metaTitleInputId) : null;
    const hiddenDesc = state.metaDescriptionInputId ? document.getElementById(state.metaDescriptionInputId) : null;
    const hiddenRole = state.metaRoleInputId ? document.getElementById(state.metaRoleInputId) : null;
    const hiddenMime = state.metaMimeInputId ? document.getElementById(state.metaMimeInputId) : null;
    const hiddenWidth = state.metaWidthInputId ? document.getElementById(state.metaWidthInputId) : null;
    const hiddenHeight = state.metaHeightInputId ? document.getElementById(state.metaHeightInputId) : null;
    const hiddenDate = state.metaUploadDateInputId ? document.getElementById(state.metaUploadDateInputId) : null;
    const hiddenCreator = state.metaCreatorInputId ? document.getElementById(state.metaCreatorInputId) : null;
    const hiddenLang = state.metaLanguageInputId ? document.getElementById(state.metaLanguageInputId) : null;

    setInputValue('imageEditorMetaTitle', (hiddenTitle && hiddenTitle.value) ? hiddenTitle.value : (titleSource ? String(titleSource.value || '').trim() : ''));
    setInputValue('imageEditorMetaDescription', (hiddenDesc && hiddenDesc.value) ? hiddenDesc.value : (descSource ? stripHtml(descSource.value || '').slice(0, 255) : ''));
    setInputValue('imageEditorMetaRole', (hiddenRole && hiddenRole.value) ? hiddenRole.value : 'primary');
    setInputValue('imageEditorMetaMime', (hiddenMime && hiddenMime.value) ? hiddenMime.value : 'image/webp');
    setInputValue('imageEditorMetaWidth', hiddenWidth && hiddenWidth.value ? hiddenWidth.value : '');
    setInputValue('imageEditorMetaHeight', hiddenHeight && hiddenHeight.value ? hiddenHeight.value : '');
    setInputValue('imageEditorMetaUploadDate', hiddenDate && hiddenDate.value ? hiddenDate.value : '');
    setInputValue('imageEditorMetaCreator', hiddenCreator && hiddenCreator.value ? hiddenCreator.value : '');
    setInputValue('imageEditorMetaLanguage', (hiddenLang && hiddenLang.value) ? hiddenLang.value : 'es-CO');
  }

  function persistModalMetadataToHidden() {
    if (state.metaTitleInputId) setInputValue(state.metaTitleInputId, modalMetadataValue('imageEditorMetaTitle'));
    if (state.metaDescriptionInputId) setInputValue(state.metaDescriptionInputId, modalMetadataValue('imageEditorMetaDescription'));
    if (state.metaRoleInputId) setInputValue(state.metaRoleInputId, modalMetadataValue('imageEditorMetaRole') || 'primary');
    if (state.metaMimeInputId) setInputValue(state.metaMimeInputId, modalMetadataValue('imageEditorMetaMime') || 'image/webp');
    if (state.metaWidthInputId) setInputValue(state.metaWidthInputId, modalMetadataValue('imageEditorMetaWidth'));
    if (state.metaHeightInputId) setInputValue(state.metaHeightInputId, modalMetadataValue('imageEditorMetaHeight'));
    if (state.metaUploadDateInputId) setInputValue(state.metaUploadDateInputId, modalMetadataValue('imageEditorMetaUploadDate'));
    if (state.metaCreatorInputId) setInputValue(state.metaCreatorInputId, modalMetadataValue('imageEditorMetaCreator'));
    if (state.metaLanguageInputId) setInputValue(state.metaLanguageInputId, modalMetadataValue('imageEditorMetaLanguage') || 'es-CO');
  }

  function closeEditor() {
    const modal = qs('#adminImageEditorModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }

  function getMaskRect(canvasW, canvasH, ratio) {
    let maskW = canvasW * 0.9;
    let maskH = maskW / ratio;
    if (maskH > canvasH * 0.9) {
      maskH = canvasH * 0.9;
      maskW = maskH * ratio;
    }
    return {
      x: (canvasW - maskW) / 2,
      y: (canvasH - maskH) / 2,
      w: maskW,
      h: maskH
    };
  }

  function computeDominantBackgroundColor(img) {
    try {
      const sampleCanvas = document.createElement('canvas');
      const sampleCtx = sampleCanvas.getContext('2d', { willReadFrequently: true });
      const sw = 48;
      const sh = 48;
      sampleCanvas.width = sw;
      sampleCanvas.height = sh;
      sampleCtx.drawImage(img, 0, 0, sw, sh);
      const data = sampleCtx.getImageData(0, 0, sw, sh).data;

      let r = 0;
      let g = 0;
      let b = 0;
      let count = 0;

      for (let y = 0; y < sh; y++) {
        for (let x = 0; x < sw; x++) {
          const isBorder = (x < 6 || x >= sw - 6 || y < 6 || y >= sh - 6);
          if (!isBorder) continue;
          const idx = (y * sw + x) * 4;
          const alpha = data[idx + 3];
          if (alpha < 16) continue;
          r += data[idx];
          g += data[idx + 1];
          b += data[idx + 2];
          count++;
        }
      }

      if (count === 0) {
        return '#f3f4f6';
      }

      // Suavizar ligeramente para evitar fondos muy intensos.
      const rr = Math.round((r / count) * 0.92 + 10);
      const gg = Math.round((g / count) * 0.92 + 10);
      const bb = Math.round((b / count) * 0.92 + 10);

      return 'rgb(' + rr + ', ' + gg + ', ' + bb + ')';
    } catch (_err) {
      return '#f3f4f6';
    }
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

    qs('#imageEditorLoadCurrent').addEventListener('click', function () {
      const input = state.targetInputId ? document.getElementById(state.targetInputId) : null;
      const currentUrl = input ? String(input.value || '').trim() : '';
      if (!currentUrl) {
        alert('No hay imagen actual en el campo.');
        return;
      }
      loadImageFromUrl(currentUrl).catch(function (err) {
        console.log('❌ [ImageEditor] Error al cargar imagen actual:', err && err.message);
        alert('No se pudo cargar la imagen actual.');
      });
    });

    qs('#imageEditorDeleteCurrent').addEventListener('click', async function () {
      const input = state.targetInputId ? document.getElementById(state.targetInputId) : null;
      const currentUrl = input ? String(input.value || '').trim() : '';
      if (!currentUrl) {
        alert('No hay imagen para eliminar.');
        return;
      }

      if (!confirm('¿Eliminar la imagen actual? Esta acción no se puede deshacer.')) {
        return;
      }

      if (!isManagedUploadUrl(currentUrl)) {
        alert('La imagen actual no está en /assets/images/uploads/. Solo se limpiará el campo.');
        if (input) {
          input.value = '';
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        clearLoadedImage();
        return;
      }

      try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('csrf_token', state.csrfToken || '');
        fd.append('image_url', currentUrl);
        const response = await fetch('/admin/media/upload-image.php', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        const json = await response.json();
        if (!json || !json.ok) {
          throw new Error((json && json.error) ? json.error : 'No se pudo eliminar imagen');
        }

        if (input) {
          input.value = '';
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        clearLoadedImage();
        console.log('✅ [ImageEditor] Imagen eliminada del disco:', json.url);
      } catch (err) {
        console.log('❌ [ImageEditor] Error al eliminar imagen:', err && err.message);
        alert('No se pudo eliminar la imagen: ' + (err && err.message ? err.message : 'Error'));
      }
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
          state.backgroundColor = computeDominantBackgroundColor(img);
          fitImageToView();
          render();
          qs('#imageEditorMeta').textContent = 'Archivo: ' + state.sourceName + ' (' + img.width + 'x' + img.height + ')';
          console.log('✅ [ImageEditor] Imagen cargada:', state.sourceName, img.width + 'x' + img.height, 'bg:', state.backgroundColor);
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
        persistModalMetadataToHidden();

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

        applyAutoMetadata(json || {});
        persistModalMetadataToHidden();

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
    // Autoajuste inicial por alto: prioriza que la altura encaje al abrir.
    const fitScale = baseScaleY;
    const containScale = Math.min(baseScaleX, baseScaleY);

    state.minScale = Math.max(containScale * 0.25, 0.02);
    state.scale = fitScale;

    const drawW = img.width * state.scale;
    const drawH = img.height * state.scale;
    state.offsetX = (cw - drawW) / 2;
    state.offsetY = (ch - drawH) / 2;

    const zoom = qs('#imageEditorZoom');
    if (zoom) {
      const max = Math.max(fitScale * 4, fitScale + 2);
      zoom.min = String(state.minScale);
      zoom.max = String(max);
      zoom.step = String((max - state.minScale) / 240);
      zoom.value = String(state.scale);
    }

    console.log('🔍 [ImageEditor] Fit inicial aplicado. Preset ratio:', presetRatio.toFixed(3), 'img ratio:', imgRatio.toFixed(3));
  }

  function loadImageFromUrl(rawUrl) {
    return new Promise(function (resolve, reject) {
      const currentUrl = String(rawUrl || '').trim();
      if (!currentUrl) {
        reject(new Error('URL vacía'));
        return;
      }

      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = function () {
        state.sourceImage = img;
        state.sourceName = 'actual';
        state.backgroundColor = computeDominantBackgroundColor(img);
        fitImageToView();
        render();
        const meta = qs('#imageEditorMeta');
        if (meta) {
          meta.textContent = 'Imagen actual cargada (' + img.width + 'x' + img.height + ')';
        }
        console.log('✅ [ImageEditor] Imagen actual precargada:', currentUrl);
        resolve();
      };
      img.onerror = function () {
        reject(new Error('No se pudo abrir la URL actual'));
      };
      img.src = currentUrl;
    });
  }

  function setScale(nextScale) {
    if (!state.sourceImage) return;
    const canvas = qs('#imageEditorViewport');
    const zoom = qs('#imageEditorZoom');
    const min = parseFloat(zoom.min || String(state.minScale || 1));
    const max = parseFloat(zoom.max || '4');

    const oldScale = state.scale;
    const targetScale = Math.max(min, Math.min(max, nextScale));
    if (!canvas || targetScale === oldScale) {
      state.scale = targetScale;
      if (zoom) {
        zoom.value = String(state.scale);
      }
      clampOffsets();
      render();
      return;
    }

    // Anclar el zoom al centro del encuadre visible.
    const info = getPresetInfo();
    const mask = getMaskRect(canvas.width, canvas.height, info.width / info.height);
    const anchorX = mask.x + (mask.w / 2);
    const anchorY = mask.y + (mask.h / 2);

    const imageXAtAnchor = (anchorX - state.offsetX) / oldScale;
    const imageYAtAnchor = (anchorY - state.offsetY) / oldScale;

    state.scale = targetScale;
    state.offsetX = anchorX - (imageXAtAnchor * state.scale);
    state.offsetY = anchorY - (imageYAtAnchor * state.scale);
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

    // Permitir huecos para zoom out, pero evitar que la imagen se pierda completamente.
    const marginX = Math.max(24, drawW * 0.12);
    const marginY = Math.max(24, drawH * 0.12);
    const minX = -drawW + marginX;
    const maxX = cw - marginX;
    const minY = -drawH + marginY;
    const maxY = ch - marginY;

    state.offsetX = Math.max(minX, Math.min(maxX, state.offsetX));
    state.offsetY = Math.max(minY, Math.min(maxY, state.offsetY));
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
    const mask = getMaskRect(cw, ch, maskRatio);

    if (state.sourceImage) {
      clampOffsets();
      ctx.fillStyle = state.backgroundColor || '#f3f4f6';
      ctx.fillRect(mask.x, mask.y, mask.w, mask.h);
      const drawW = state.sourceImage.width * state.scale;
      const drawH = state.sourceImage.height * state.scale;
      ctx.drawImage(state.sourceImage, state.offsetX, state.offsetY, drawW, drawH);
    }

    ctx.save();
    ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
    ctx.beginPath();
    ctx.rect(0, 0, cw, ch);
    ctx.rect(mask.x, mask.y, mask.w, mask.h);
    ctx.fill('evenodd');
    ctx.restore();

    ctx.strokeStyle = '#22c55e';
    ctx.lineWidth = 2;
    ctx.strokeRect(mask.x, mask.y, mask.w, mask.h);
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
      const mask = getMaskRect(cw, ch, ratio);

      const drawW = state.sourceImage.width * state.scale;
      const drawH = state.sourceImage.height * state.scale;
      const scaleToExport = info.width / mask.w;

      const dx = (state.offsetX - mask.x) * scaleToExport;
      const dy = (state.offsetY - mask.y) * scaleToExport;
      const dw = drawW * scaleToExport;
      const dh = drawH * scaleToExport;

      ctx.fillStyle = state.backgroundColor || '#f3f4f6';
      ctx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
      ctx.drawImage(state.sourceImage, dx, dy, dw, dh);

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
        const metaTitleInputId = btn.getAttribute('data-meta-title-input') || '';
        const metaDescriptionInputId = btn.getAttribute('data-meta-description-input') || '';
        const metaMimeInputId = btn.getAttribute('data-meta-mime-input') || '';
        const metaWidthInputId = btn.getAttribute('data-meta-width-input') || '';
        const metaHeightInputId = btn.getAttribute('data-meta-height-input') || '';
        const metaUploadDateInputId = btn.getAttribute('data-meta-upload-date-input') || '';
        const metaRoleInputId = btn.getAttribute('data-meta-role-input') || '';
        const metaCreatorInputId = btn.getAttribute('data-meta-creator-input') || '';
        const metaLanguageInputId = btn.getAttribute('data-meta-language-input') || '';
        const metaTitleSourceId = btn.getAttribute('data-meta-title-source') || '';
        const metaDescriptionSourceId = btn.getAttribute('data-meta-description-source') || '';
        const csrfToken = (qs('#kit-form input[name="csrf_token"]') || qs('#clase-form input[name="csrf_token"]') || qs('#cmp-form input[name="csrf_token"]') || qs('input[name="csrf_token"]'))?.value || '';
        const targetInput = targetInputId ? document.getElementById(targetInputId) : null;
        const existingUrl = targetInput ? String(targetInput.value || '').trim() : '';

        if (!targetInputId) {
          alert('No se encontró input destino para esta acción.');
          return;
        }
        openEditor({
          targetInputId: targetInputId,
          preset: preset,
          entity: entity,
          csrfToken: csrfToken,
          existingUrl: existingUrl,
          metaTitleInputId: metaTitleInputId,
          metaDescriptionInputId: metaDescriptionInputId,
          metaMimeInputId: metaMimeInputId,
          metaWidthInputId: metaWidthInputId,
          metaHeightInputId: metaHeightInputId,
          metaUploadDateInputId: metaUploadDateInputId,
          metaRoleInputId: metaRoleInputId,
          metaCreatorInputId: metaCreatorInputId,
          metaLanguageInputId: metaLanguageInputId,
          metaTitleSourceId: metaTitleSourceId,
          metaDescriptionSourceId: metaDescriptionSourceId
        });
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
