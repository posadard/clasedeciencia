// Asistente IA - Side Panel Widget (push layout)
// Uso: en clase.php, llama initAsistenteIA({ claseId })

(function () {

  // Ancho del panel — fuente única de verdad
  var PANEL_W     = 360;
  var PANEL_W_EXP = 560;

  var CSS = `
    /* ======== Asistente IA — Push Side Panel ======== */

    /* Transición suave del body al empujar */
    body {
      transition: padding-right 0.3s cubic-bezier(.4,0,.2,1);
    }
    body.ia-panel-open {
      padding-right: ${PANEL_W}px;
    }
    body.ia-panel-open.ia-panel-expanded {
      padding-right: ${PANEL_W_EXP}px;
    }

    /* Tab/trigger pegado al borde derecho (solo cuando el panel está cerrado) */
    .ia-trigger {
      position: fixed;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      z-index: 900;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      padding: 14px 10px;
      background: #1f3c88;
      color: #fff;
      border: none;
      border-radius: 10px 0 0 10px;
      cursor: pointer;
      font-family: inherit;
      box-shadow: -3px 2px 12px rgba(0,0,0,0.22);
      transition: background 0.2s, opacity 0.2s;
    }
    .ia-trigger:hover { background: #3d5ba9; }
    .ia-trigger-icon { font-size: 18px; line-height: 1; }
    .ia-trigger-label {
      writing-mode: vertical-rl;
      text-orientation: mixed;
      transform: rotate(180deg);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 1px;
      opacity: 1;
    }
    /* "IA" con glow dentro del nombre CiencIA */
    .ia-trigger-label .ia-glow {
      color: #7dd3fc;
      text-shadow:
        0 0 6px rgba(125, 211, 252, 0.9),
        0 0 14px rgba(125, 211, 252, 0.55),
        0 0 24px rgba(56, 189, 248, 0.35);
      animation: ia-pulse 2.6s ease-in-out infinite;
    }
    @keyframes ia-pulse {
      0%, 100% { text-shadow: 0 0 6px rgba(125,211,252,0.9), 0 0 14px rgba(125,211,252,0.55), 0 0 24px rgba(56,189,248,0.35); }
      50%       { text-shadow: 0 0 10px rgba(125,211,252,1),   0 0 22px rgba(125,211,252,0.80), 0 0 36px rgba(56,189,248,0.55); }
    }

    /* Panel principal — fijo a la derecha del viewport */
    .ia-side-panel {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: ${PANEL_W}px;
      background: #fff;
      border-left: 1px solid #d4d8dd;
      box-shadow: -4px 0 20px rgba(0,0,0,0.10);
      z-index: 900;
      display: flex;
      flex-direction: column;
      transform: translateX(102%);
      transition: transform 0.3s cubic-bezier(.4,0,.2,1),
                  width 0.3s cubic-bezier(.4,0,.2,1);
    }
    .ia-side-panel.ia-open {
      transform: translateX(0);
    }
    .ia-side-panel.ia-expanded {
      width: ${PANEL_W_EXP}px;
    }

    /* Header del panel */
    .ia-panel-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      background: #1f3c88;
      color: #fff;
      flex-shrink: 0;
    }
    .ia-panel-header-icon { font-size: 20px; line-height: 1; }
    .ia-panel-header-info { flex: 1; min-width: 0; }
    .ia-panel-header-title {
      font-weight: 700;
      font-size: 14px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ia-panel-header-sub {
      font-size: 11px;
      color: rgba(255,255,255,0.6);
      margin-top: 1px;
    }
    .ia-panel-btn {
      background: rgba(255,255,255,0.14);
      border: none;
      color: #fff;
      width: 30px;
      height: 30px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background 0.2s;
      font-family: inherit;
    }
    .ia-panel-btn:hover { background: rgba(255,255,255,0.26); }
    .ia-panel-btn-close { border-radius: 50%; }

    /* Log de conversación */
    .ia-chat-log {
      flex: 1;
      overflow-y: auto;
      padding: 12px 10px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: #f4f6f8;
      scroll-behavior: smooth;
    }

    /* Sugerencias de contenido */
    .ia-sugerencias {
      display: flex;
      flex-direction: column;
      gap: 5px;
      margin-top: 6px;
      align-self: flex-start;
      width: 100%;
      max-width: 88%;
    }
    .ia-sugerencias-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #9ca3af;
      margin-bottom: 2px;
    }
    .ia-sug-card {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 8px 11px;
      background: #fff;
      border: 1px solid #e2e5eb;
      border-radius: 9px;
      text-decoration: none;
      color: #2b2b2b;
      transition: background 0.15s, border-color 0.15s, transform 0.12s;
      cursor: pointer;
    }
    .ia-sug-card:hover {
      background: #eef2ff;
      border-color: #93a8f4;
      transform: translateX(2px);
    }
    .ia-sug-icon { font-size: 18px; flex-shrink: 0; }
    .ia-sug-body { min-width: 0; }
    .ia-sug-label {
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #9ca3af;
      margin-bottom: 1px;
    }
    .ia-sug-titulo {
      font-size: 12.5px;
      font-weight: 600;
      color: #1f3c88;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ia-sug-desc {
      font-size: 11px;
      color: #6b7280;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ia-sug-arrow { margin-left: auto; color: #9ca3af; font-size: 13px; flex-shrink: 0; }

    /* Estado vacío */
    .ia-chat-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #80868b;
      padding: 32px 18px;
      gap: 10px;
    }
    .ia-chat-empty-icon { font-size: 34px; }
    .ia-chat-empty-title { font-size: 14px; font-weight: 600; color: #5f6368; }
    .ia-chat-empty-hint  { font-size: 12.5px; line-height: 1.55; }

    /* Burbujas */
    .ia-bubble {
      max-width: 88%;
      padding: 9px 12px;
      border-radius: 14px;
      font-size: 13px;
      line-height: 1.55;
      word-break: break-word;
      animation: ia-pop 0.16s ease-out;
    }
    @keyframes ia-pop {
      from { opacity: 0; transform: scale(0.96) translateY(4px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .ia-bubble-user {
      align-self: flex-end;
      background: #1f3c88;
      color: #fff;
      border-bottom-right-radius: 3px;
    }
    .ia-bubble-ia {
      align-self: flex-start;
      background: #fff;
      color: #2b2b2b;
      border: 1px solid #e2e5eb;
      border-bottom-left-radius: 3px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.07);
    }
    .ia-bubble-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 3px;
    }
    .ia-bubble-user .ia-bubble-label { color: rgba(255,255,255,0.65); }
    .ia-bubble-ia  .ia-bubble-label  { color: #9ca3af; }
    .ia-bubble-text { white-space: pre-wrap; }

    /* Indicador "escribiendo..." */
    .ia-typing {
      align-self: flex-start;
      display: flex;
      gap: 5px;
      padding: 10px 14px;
      background: #fff;
      border: 1px solid #e2e5eb;
      border-radius: 14px;
      border-bottom-left-radius: 3px;
    }
    .ia-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: #9ca3af;
      animation: ia-bounce 1.3s infinite ease-in-out;
    }
    .ia-dot:nth-child(2) { animation-delay: 0.18s; }
    .ia-dot:nth-child(3) { animation-delay: 0.36s; }
    @keyframes ia-bounce {
      0%, 80%, 100% { transform: translateY(0); }
      40%           { transform: translateY(-7px); }
    }

    /* Footer / input */
    .ia-panel-footer {
      padding: 10px;
      border-top: 1px solid #e8eaf0;
      background: #fff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: 7px;
    }
    .ia-textarea {
      width: 100%;
      box-sizing: border-box;
      resize: none;
      border: 1.5px solid #d4d8dd;
      border-radius: 8px;
      padding: 8px 10px;
      font-size: 13px;
      font-family: inherit;
      line-height: 1.5;
      color: #2b2b2b;
      outline: none;
      transition: border-color 0.2s;
      background: #fafbfc;
    }
    .ia-textarea:focus { border-color: #1f3c88; background: #fff; }
    .ia-footer-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .ia-footer-hint { font-size: 11px; color: #b0b8c1; }
    .ia-send-btn {
      background: #1f3c88;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 7px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background 0.2s;
      white-space: nowrap;
    }
    .ia-send-btn:hover:not(:disabled) { background: #3d5ba9; }
    .ia-send-btn:disabled { background: #d4d8dd; cursor: not-allowed; }

    /* Logo icon con glow — identidad visual potenciada */
    .ia-logo-icon {
      display: block;
      filter: drop-shadow(0 0 4px rgba(125,211,252,0.85)) drop-shadow(0 0 10px rgba(56,189,248,0.5));
      animation: ia-icon-glow 2.6s ease-in-out infinite;
    }
    @keyframes ia-icon-glow {
      0%, 100% { filter: drop-shadow(0 0 4px rgba(125,211,252,0.85)) drop-shadow(0 0 10px rgba(56,189,248,0.5)); }
      50%       { filter: drop-shadow(0 0 8px rgba(125,211,252,1))    drop-shadow(0 0 18px rgba(56,189,248,0.8)); }
    }

    /* Responsive — en móvil el panel ocupa toda la pantalla (no push) */
    @media (max-width: 768px) {
      body.ia-panel-open,
      body.ia-panel-open.ia-panel-expanded { padding-right: 0; }
      .ia-side-panel,
      .ia-side-panel.ia-expanded { width: 100vw; }
      .ia-trigger-label { display: none; }
      .ia-trigger { padding: 12px 9px; }
    }
  `;

  function injectCSS() {
    var style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);
  }

  function createUI() {
    // Trigger tab en el borde derecho
    var trigger = document.createElement('button');
    trigger.className = 'ia-trigger';
    trigger.setAttribute('aria-label', 'Abrir Clase de CiencIA — Asistente de IA');
    var LOGO_SVG_SM = '<svg class="ia-logo-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></circle><line x1="15" y1="15" x2="21" y2="21" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></line><circle cx="10" cy="10" r="4" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.3"></circle><ellipse cx="8" cy="8" rx="2" ry="3" fill="currentColor" opacity="0.15" transform="rotate(-35 8 8)"></ellipse></svg>';
    var LOGO_SVG_MD = '<svg class="ia-logo-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></circle><line x1="15" y1="15" x2="21" y2="21" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></line><circle cx="10" cy="10" r="4" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.3"></circle><ellipse cx="8" cy="8" rx="2" ry="3" fill="currentColor" opacity="0.15" transform="rotate(-35 8 8)"></ellipse></svg>';
    trigger.innerHTML =
      '<span class="ia-trigger-icon">' + LOGO_SVG_SM + '</span>' +
      '<span class="ia-trigger-label">Clase de Cienc<span class="ia-glow">IA</span></span>';

    // Panel lateral
    var panel = document.createElement('div');
    panel.className = 'ia-side-panel';
    panel.setAttribute('role', 'complementary');
    panel.setAttribute('aria-label', 'Clase de CiencIA — Asistente de IA');

    // Header
    var header = document.createElement('div');
    header.className = 'ia-panel-header';
    header.innerHTML =
      '<span class="ia-panel-header-icon">' + LOGO_SVG_MD + '</span>' +
      '<div class="ia-panel-header-info">' +
        '<div class="ia-panel-header-title">Clase de Cienc<span style="color:#7dd3fc;text-shadow:0 0 8px rgba(125,211,252,0.8),0 0 18px rgba(56,189,248,0.5)">IA</span></div>' +
        '<div class="ia-panel-header-sub">Pregunta sobre este experimento</div>' +
      '</div>';
    var expandBtn = document.createElement('button');
    expandBtn.className = 'ia-panel-btn';
    expandBtn.title = 'Expandir / reducir panel';
    expandBtn.textContent = '⇔';
    var closeBtn = document.createElement('button');
    closeBtn.className = 'ia-panel-btn ia-panel-btn-close';
    closeBtn.title = 'Cerrar';
    closeBtn.textContent = '✕';
    header.appendChild(expandBtn);
    header.appendChild(closeBtn);

    // Log
    var log = document.createElement('div');
    log.className = 'ia-chat-log';
    log.innerHTML =
      '<div class="ia-chat-empty" id="ia-empty-state">' +
        '<span class="ia-chat-empty-icon" id="ia-empty-icon">🔬</span>' +
        '<div class="ia-chat-empty-title" id="ia-empty-title">¡Hola! Soy Clase de CiencIA</div>' +
        '<div class="ia-chat-empty-hint" id="ia-empty-hint"></div>' +
      '</div>';

    // Footer / input
    var footer = document.createElement('div');
    footer.className = 'ia-panel-footer';
    var textarea = document.createElement('textarea');
    textarea.className = 'ia-textarea';
    textarea.rows = 3;
    textarea.placeholder = 'Escribe tu pregunta…';
    var footerRow = document.createElement('div');
    footerRow.className = 'ia-footer-row';
    var hint = document.createElement('span');
    hint.className = 'ia-footer-hint';
    hint.textContent = 'Ctrl+Enter para enviar';
    var sendBtn = document.createElement('button');
    sendBtn.className = 'ia-send-btn';
    sendBtn.textContent = '▶ Enviar';
    footerRow.appendChild(hint);
    footerRow.appendChild(sendBtn);
    footer.appendChild(textarea);
    footer.appendChild(footerRow);

    panel.appendChild(header);
    panel.appendChild(log);
    panel.appendChild(footer);

    document.body.appendChild(trigger);
    document.body.appendChild(panel);

    return { trigger, panel, log, textarea, sendBtn, expandBtn, closeBtn };
  }

  function addBubble(log, role, text) {
    var empty = log.querySelector('.ia-chat-empty');
    if (empty) empty.remove();

    var wrap = document.createElement('div');
    wrap.className = role === 'user' ? 'ia-bubble ia-bubble-user' : 'ia-bubble ia-bubble-ia';
    var label = document.createElement('div');
    label.className = 'ia-bubble-label';
    label.textContent = role === 'user' ? 'Tú' : 'Clase de CiencIA';
    var body = document.createElement('div');
    body.className = 'ia-bubble-text';
    body.textContent = text;
    wrap.appendChild(label);
    wrap.appendChild(body);
    log.appendChild(wrap);
    log.scrollTop = log.scrollHeight;
    return wrap;
  }

  function addTyping(log) {
    var t = document.createElement('div');
    t.className = 'ia-typing';
    t.innerHTML = '<div class="ia-dot"></div><div class="ia-dot"></div><div class="ia-dot"></div>';
    log.appendChild(t);
    log.scrollTop = log.scrollHeight;
    return t;
  }

  function addSugerencias(log, sugerencias) {
    if (!sugerencias || sugerencias.length === 0) return;
    var wrap = document.createElement('div');
    wrap.className = 'ia-sugerencias';
    var label = document.createElement('div');
    label.className = 'ia-sugerencias-label';
    label.textContent = '📎 Te puede interesar';
    wrap.appendChild(label);
    sugerencias.forEach(function (s) {
      var a = document.createElement('a');
      a.className = 'ia-sug-card';
      a.href = s.url;
      // Abrir en la misma pestaña — el usuario sigue en el sitio
      a.innerHTML =
        '<span class="ia-sug-icon">' + s.icono + '</span>' +
        '<span class="ia-sug-body">' +
          '<div class="ia-sug-label">' + s.label + '</div>' +
          '<div class="ia-sug-titulo">' + s.titulo + '</div>' +
          (s.desc ? '<div class="ia-sug-desc">' + s.desc + '</div>' : '') +
        '</span>' +
        '<span class="ia-sug-arrow">›</span>';
      wrap.appendChild(a);
    });
    log.appendChild(wrap);
    log.scrollTop = log.scrollHeight;
  }

  var BIENVENIDA = {
    'clase':       { icono: '🔬', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Puedes seguir leyendo el experimento<br>y preguntarme lo que necesites.' },
    'kit':         { icono: '🧰', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Pregúntame sobre los componentes,<br>usos o instrucciones de este kit.' },
    'componente':  { icono: '⚗️', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Pregúntame cómo usar este componente,<br>sus propiedades o medidas de seguridad.' },
    'manual':      { icono: '📖', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Pregúntame sobre los pasos de este manual<br>o cualquier duda que tengas.' },
    'inicio':      { icono: '🚀', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Pregúntame sobre clases, kits,<br>materiales o experimentos científicos.' },
    'catalogo':    { icono: '📚', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Cuéntame qué tema te interesa<br>y te ayudo a encontrar la clase ideal.' },
    'kits':        { icono: '🧰', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Cuéntame qué necesitas y te oriento<br>hacia el kit más adecuado.' },
    'componentes': { icono: '⚗️', titulo: '¡Hola! Soy Clase de CiencIA',      hint: 'Pregúntame sobre materiales,<br>usos, cuidados o alternativas.' },
    'manuales':    { icono: '📖', titulo: '¡Hola! Soy Clase de CiencIA',       hint: 'Pregúntame qué tipo de guía buscas<br>y te ayudo a encontrarla.' },
  };

  var SUBTITULOS = {
    'clase':       'Pregunta sobre este experimento',
    'kit':         'Pregunta sobre este kit',
    'componente':  'Pregunta sobre este componente',
    'manual':      'Pregunta sobre este manual',
    'inicio':      '¿Sobre qué quieres aprender hoy?',
    'catalogo':    '¿Te ayudo a encontrar algo?',
    'kits':        '¿Te ayudo a encontrar un kit?',
    'componentes': '¿Te ayudo con un componente?',
    'manuales':    '¿Te ayudo a encontrar un manual?',
  };

  // ======== Catálogo local para sugerencias (reutiliza mismas APIs del buscador del header) ========
  var _catalogo = null;
  var _catalogoCargando = false;

  function normalizarTexto(text) {
    return (text || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\u00f1/g, 'n'); // ñ
  }

  function cargarCatalogo() {
    if (_catalogo || _catalogoCargando) return;
    _catalogoCargando = true;
    console.log('📡 [asistente-ia] Cargando catálogo para sugerencias...');
    var t = 't=' + Date.now();
    Promise.all([
      fetch('/api/clases-data.php?' + t, { cache: 'no-store' }).then(function(r){ return r.ok ? r.json() : { success: false }; }),
      fetch('/api/kits-data.php?' + t, { cache: 'no-store' }).then(function(r){ return r.ok ? r.json() : { success: false }; }),
      fetch('/api/componentes-data.php?' + t, { cache: 'no-store' }).then(function(r){ return r.ok ? r.json() : { success: false }; })
    ]).then(function(results) {
      _catalogo = {
        clases:      (results[0].success && results[0].proyectos)   ? results[0].proyectos   : [],
        kits:        (results[1].success && results[1].kits)        ? results[1].kits        : [],
        componentes: (results[2].success && results[2].componentes) ? results[2].componentes : []
      };
      _catalogoCargando = false;
      console.log('✅ [asistente-ia] Catálogo cargado:', {
        clases: _catalogo.clases.length,
        kits: _catalogo.kits.length,
        componentes: _catalogo.componentes.length
      });
    }).catch(function(err) {
      _catalogoCargando = false;
      _catalogo = { clases: [], kits: [], componentes: [] };
      console.log('❌ [asistente-ia] Error cargando catálogo:', err.message);
    });
  }

  function buscarSugerencias(pregunta, limit) {
    limit = limit || 5;
    if (!_catalogo) {
      console.log('⚠️ [asistente-ia] buscarSugerencias: catálogo no cargado aún');
      return [];
    }
    // Partir la pregunta en palabras de ≥4 chars para filtrar stopwords (hola, que, me, esto...)
    var palabras = normalizarTexto(pregunta)
      .split(/\s+/)
      .filter(function(p) { return p.length >= 4; })
      .slice(0, 8);
    console.log('🔍 [asistente-ia] buscarSugerencias keywords:', palabras);
    if (palabras.length === 0) return [];

    var ICONOS = { clase: '🔬', kit: '🧰', componente: '⚗️' };
    var LABELS = { clase: 'Clase', kit: 'Kit', componente: 'Componente' };
    var resultados = [];
    ['clases', 'kits', 'componentes'].forEach(function(grupo) {
      var tipo = grupo === 'clases' ? 'clase' : (grupo === 'kits' ? 'kit' : 'componente');
      (_catalogo[grupo] || []).forEach(function(item) {
        if (!item.search_text) return;
        // Coincide si ALGUNA palabra clave aparece en el search_text del ítem
        var match = palabras.some(function(p) { return item.search_text.includes(p); });
        if (match) {
          resultados.push({
            icono:  ICONOS[tipo],
            label:  LABELS[tipo],
            titulo: item.title || '',
            url:    item.url   || '#',
            desc:   item.description ? item.description.substring(0, 80) : ''
          });
        }
      });
    });
    console.log('✅ [asistente-ia] buscarSugerencias encontró:', resultados.length, 'resultados');
    // Deduplicar por URL y limitar
    var seen = {};
    return resultados.filter(function(r) {
      if (seen[r.url]) return false;
      seen[r.url] = true;
      return true;
    }).slice(0, limit);
  }

  window.initAsistenteIA = function (ctx) {
    ctx = ctx || {};
    var claseId      = ctx.claseId      || null;
    var kitId        = ctx.kitId        || null;
    var componenteId = ctx.componenteId || null;
    var manualId     = ctx.manualId     || null;
    var pagina       = ctx.pagina       || (claseId ? 'clase' : 'inicio');
    console.log('🔍 [asistente-ia] init', pagina, { claseId, kitId, componenteId, manualId });

    injectCSS();
    var ui = createUI();
    var isExpanded = false;
    cargarCatalogo(); // carga catálogo en background para sugerencias client-side

    // Subtítulo del header según página
    var subEl = ui.panel.querySelector('.ia-panel-header-sub');
    if (subEl) subEl.textContent = SUBTITULOS[pagina] || SUBTITULOS['inicio'];

    // Mensaje de bienvenida según página
    var bv = BIENVENIDA[pagina] || BIENVENIDA['inicio'];
    var emIcon  = ui.panel.querySelector('#ia-empty-icon');
    var emTitle = ui.panel.querySelector('#ia-empty-title');
    var emHint  = ui.panel.querySelector('#ia-empty-hint');
    if (emIcon)  emIcon.textContent  = bv.icono;
    if (emTitle) emTitle.textContent = bv.titulo;
    if (emHint)  emHint.innerHTML    = bv.hint;

    function openPanel() {
      ui.panel.classList.add('ia-open');
      document.body.classList.add('ia-panel-open');
      ui.trigger.style.display = 'none';
      setTimeout(function () { ui.textarea.focus(); }, 320);
      console.log('✅ [asistente-ia] panel abierto');
    }

    function closePanel() {
      ui.panel.classList.remove('ia-open');
      document.body.classList.remove('ia-panel-open', 'ia-panel-expanded');
      ui.trigger.style.display = '';
      isExpanded = false;
      ui.expandBtn.textContent = '⇔';
      ui.expandBtn.title = 'Expandir panel';
      console.log('✅ [asistente-ia] panel cerrado');
    }

    ui.trigger.addEventListener('click', openPanel);
    ui.closeBtn.addEventListener('click', closePanel);

    ui.expandBtn.addEventListener('click', function () {
      isExpanded = !isExpanded;
      ui.panel.classList.toggle('ia-expanded', isExpanded);
      document.body.classList.toggle('ia-panel-expanded', isExpanded);
      ui.expandBtn.textContent = isExpanded ? '⇤' : '⇔';
      ui.expandBtn.title = isExpanded ? 'Reducir panel' : 'Expandir panel';
    });

    async function enviar() {
      var pregunta = ui.textarea.value.trim();
      console.log('🔍 [asistente-ia] pregunta:', pregunta);
      if (!pregunta) return;

      ui.sendBtn.disabled = true;
      ui.textarea.value = '';
      addBubble(ui.log, 'user', pregunta);
      var typing = addTyping(ui.log);

      try {
        var resp = await fetch('/api/ia-consulta.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            instancia:     'frontend',
            clase_id:      claseId,
            kit_id:        kitId,
            componente_id: componenteId,
            manual_id:     manualId,
            pagina:        pagina,
            pregunta:      pregunta
          })
        });
        console.log('📡 [asistente-ia] status:', resp.status);
        var json = await resp.json();
        console.log('✅ [asistente-ia] respuesta:', json);
        typing.remove();
        if (json && json.ok) {
          addBubble(ui.log, 'ia', json.respuesta);
          addSugerencias(ui.log, buscarSugerencias(pregunta));
        } else {
          addBubble(ui.log, 'ia', '❌ ' + (json && json.error ? json.error : 'Error al procesar la consulta.'));
        }
      } catch (err) {
        console.log('❌ [asistente-ia] error de red:', err.message);
        typing.remove();
        addBubble(ui.log, 'ia', '❌ Error de red. Por favor intenta de nuevo.');
      }

      ui.sendBtn.disabled = false;
      ui.textarea.focus();
    }

    ui.sendBtn.addEventListener('click', enviar);
    ui.textarea.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); enviar(); }
    });
  };
})();
