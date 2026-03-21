// Asistente IA - Side Panel Widget
// Uso: en clase.php, llama initAsistenteIA({ claseId })

(function () {

  var CSS = `
    /* ======== Asistente IA — Side Panel ======== */

    /* Tab/trigger pegado al borde derecho */
    .ia-trigger {
      position: fixed;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      z-index: 1100;
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
      transition: background 0.2s, padding 0.2s;
    }
    .ia-trigger:hover { background: #3d5ba9; }
    .ia-trigger-icon { font-size: 20px; line-height: 1; }
    .ia-trigger-label {
      writing-mode: vertical-rl;
      text-orientation: mixed;
      transform: rotate(180deg);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      opacity: 0.9;
    }

    /* Backdrop (visible solo en móvil) */
    .ia-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.38);
      z-index: 1150;
    }
    .ia-overlay.ia-open { display: block; }

    /* Panel principal */
    .ia-side-panel {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: 380px;
      max-width: 100vw;
      background: #fff;
      box-shadow: -4px 0 24px rgba(0,0,0,0.18);
      z-index: 1200;
      display: flex;
      flex-direction: column;
      transform: translateX(102%);
      transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    }
    .ia-side-panel.ia-open {
      transform: translateX(0);
    }
    .ia-side-panel.ia-expanded {
      width: 580px;
    }

    /* Header */
    .ia-panel-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 16px;
      background: #1f3c88;
      color: #fff;
      flex-shrink: 0;
    }
    .ia-panel-header-icon { font-size: 22px; line-height: 1; }
    .ia-panel-header-info { flex: 1; min-width: 0; }
    .ia-panel-header-title {
      font-weight: 700;
      font-size: 15px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ia-panel-header-sub {
      font-size: 11px;
      color: rgba(255,255,255,0.65);
      margin-top: 1px;
    }
    .ia-panel-btn {
      background: rgba(255,255,255,0.15);
      border: none;
      color: #fff;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background 0.2s;
    }
    .ia-panel-btn:hover { background: rgba(255,255,255,0.28); }
    .ia-panel-btn-close { border-radius: 50%; }

    /* Log de chat */
    .ia-chat-log {
      flex: 1;
      overflow-y: auto;
      padding: 14px 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: #f4f6f8;
      scroll-behavior: smooth;
    }

    /* Estado vacío */
    .ia-chat-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #80868b;
      padding: 40px 20px;
      gap: 10px;
      flex: 1;
    }
    .ia-chat-empty-icon { font-size: 36px; }
    .ia-chat-empty-title {
      font-size: 14px;
      font-weight: 600;
      color: #5f6368;
    }
    .ia-chat-empty-hint { font-size: 12.5px; line-height: 1.5; }

    /* Burbujas de chat */
    .ia-bubble {
      max-width: 84%;
      padding: 9px 13px;
      border-radius: 14px;
      font-size: 13.5px;
      line-height: 1.55;
      word-break: break-word;
      animation: ia-pop 0.18s ease-out;
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
      box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }
    .ia-bubble-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .ia-bubble-user .ia-bubble-label { color: rgba(255,255,255,0.7); }
    .ia-bubble-ia  .ia-bubble-label  { color: #80868b; }
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
      width: 7px;
      height: 7px;
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

    /* Área de input */
    .ia-panel-footer {
      padding: 12px;
      border-top: 1px solid #e8eaf0;
      background: #fff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .ia-textarea {
      width: 100%;
      box-sizing: border-box;
      resize: none;
      border: 1.5px solid #d4d8dd;
      border-radius: 8px;
      padding: 9px 11px;
      font-size: 13.5px;
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
    .ia-footer-hint { font-size: 11px; color: #9ca3af; }
    .ia-send-btn {
      display: flex;
      align-items: center;
      gap: 5px;
      background: #1f3c88;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background 0.2s;
      white-space: nowrap;
    }
    .ia-send-btn:hover:not(:disabled) { background: #3d5ba9; }
    .ia-send-btn:disabled { background: #d4d8dd; cursor: not-allowed; }

    /* Responsive */
    @media (max-width: 600px) {
      .ia-side-panel, .ia-side-panel.ia-expanded { width: 100vw; }
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
    // Backdrop (móvil)
    var overlay = document.createElement('div');
    overlay.className = 'ia-overlay';

    // Trigger tab en el borde derecho
    var trigger = document.createElement('button');
    trigger.className = 'ia-trigger';
    trigger.setAttribute('aria-label', 'Abrir asistente IA');
    trigger.innerHTML =
      '<span class="ia-trigger-icon">💬</span>' +
      '<span class="ia-trigger-label">Asistente IA</span>';

    // Panel lateral
    var panel = document.createElement('div');
    panel.className = 'ia-side-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.setAttribute('aria-label', 'Asistente IA');

    // Header
    var header = document.createElement('div');
    header.className = 'ia-panel-header';
    header.innerHTML =
      '<span class="ia-panel-header-icon">🤖</span>' +
      '<div class="ia-panel-header-info">' +
        '<div class="ia-panel-header-title">Asistente IA</div>' +
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
      '<div class="ia-chat-empty">' +
        '<span class="ia-chat-empty-icon">🔬</span>' +
        '<div class="ia-chat-empty-title">¡Hola! Soy tu asistente científico</div>' +
        '<div class="ia-chat-empty-hint">Puedo ayudarte a entender el experimento.<br>¿Qué quieres saber?</div>' +
      '</div>';

    // Footer / input
    var footer = document.createElement('div');
    footer.className = 'ia-panel-footer';
    var textarea = document.createElement('textarea');
    textarea.className = 'ia-textarea';
    textarea.rows = 3;
    textarea.placeholder = 'Escribe tu pregunta sobre el experimento…';
    var footerRow = document.createElement('div');
    footerRow.className = 'ia-footer-row';
    var hint = document.createElement('span');
    hint.className = 'ia-footer-hint';
    hint.textContent = 'Ctrl+Enter para enviar';
    var sendBtn = document.createElement('button');
    sendBtn.className = 'ia-send-btn';
    sendBtn.innerHTML = '&#9658; Enviar';
    footerRow.appendChild(hint);
    footerRow.appendChild(sendBtn);
    footer.appendChild(textarea);
    footer.appendChild(footerRow);

    panel.appendChild(header);
    panel.appendChild(log);
    panel.appendChild(footer);

    document.body.appendChild(overlay);
    document.body.appendChild(trigger);
    document.body.appendChild(panel);

    return { trigger, panel, overlay, log, textarea, sendBtn, expandBtn, closeBtn };
  }

  function addBubble(log, role, text) {
    var empty = log.querySelector('.ia-chat-empty');
    if (empty) empty.remove();

    var wrap = document.createElement('div');
    wrap.className = role === 'user' ? 'ia-bubble ia-bubble-user' : 'ia-bubble ia-bubble-ia';
    var label = document.createElement('div');
    label.className = 'ia-bubble-label';
    label.textContent = role === 'user' ? 'Tú' : 'Asistente IA';
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

  window.initAsistenteIA = function (ctx) {
    var claseId = ctx && ctx.claseId ? ctx.claseId : null;
    console.log('🔍 [asistente-ia] init claseId:', claseId);

    injectCSS();
    var ui = createUI();
    var isExpanded = false;

    function openPanel() {
      ui.panel.classList.add('ia-open');
      ui.overlay.classList.add('ia-open');
      ui.trigger.style.display = 'none';
      setTimeout(function () { ui.textarea.focus(); }, 320);
      console.log('✅ [asistente-ia] panel abierto');
    }

    function closePanel() {
      ui.panel.classList.remove('ia-open');
      ui.overlay.classList.remove('ia-open');
      ui.trigger.style.display = '';
      console.log('✅ [asistente-ia] panel cerrado');
    }

    ui.trigger.addEventListener('click', openPanel);
    ui.closeBtn.addEventListener('click', closePanel);
    ui.overlay.addEventListener('click', closePanel);

    ui.expandBtn.addEventListener('click', function () {
      isExpanded = !isExpanded;
      ui.panel.classList.toggle('ia-expanded', isExpanded);
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
          body: JSON.stringify({ instancia: 'frontend', clase_id: claseId, pregunta: pregunta })
        });
        console.log('📡 [asistente-ia] status:', resp.status);
        var json = await resp.json();
        console.log('✅ [asistente-ia] respuesta:', json);
        typing.remove();
        if (json && json.ok) {
          addBubble(ui.log, 'ia', json.respuesta);
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
