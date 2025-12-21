// Asistente IA - Widget Cliente
// Uso: en clase.php, llama initAsistenteIA({ proyectoId })

(function () {
  function createUI() {
    const btn = document.createElement('button');
    btn.className = 'ia-fab';
    btn.textContent = '💬 IA';
    btn.style.position = 'fixed';
    btn.style.right = '16px';
    btn.style.bottom = '16px';
    btn.style.zIndex = '1000';
    btn.style.padding = '10px 14px';
    btn.style.borderRadius = '999px';
    btn.style.border = 'none';
    btn.style.background = '#2c5aa0';
    btn.style.color = '#fff';
    btn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';

    const panel = document.createElement('div');
    panel.className = 'ia-panel';
    panel.style.position = 'fixed';
    panel.style.right = '16px';
    panel.style.bottom = '64px';
    panel.style.width = '360px';
    panel.style.maxWidth = '90%';
    panel.style.background = '#fff';
    panel.style.border = '1px solid #ddd';
    panel.style.borderRadius = '10px';
    panel.style.boxShadow = '0 2px 12px rgba(0,0,0,0.25)';
    panel.style.padding = '12px';
    panel.style.display = 'none';

    const title = document.createElement('div');
    title.textContent = 'Asistente IA';
    title.style.fontWeight = '600';
    title.style.marginBottom = '8px';

    const log = document.createElement('div');
    log.className = 'ia-log';
    log.style.minHeight = '100px';
    log.style.maxHeight = '240px';
    log.style.overflowY = 'auto';
    log.style.border = '1px solid #eee';
    log.style.borderRadius = '6px';
    log.style.padding = '8px';
    log.style.marginBottom = '8px';

    const input = document.createElement('textarea');
    input.placeholder = 'Escribe tu pregunta…';
    input.rows = 3;
    input.style.width = '100%';
    input.style.marginBottom = '8px';

    const send = document.createElement('button');
    send.textContent = 'Enviar';
    send.className = 'ia-send';
    send.style.background = '#2c5aa0';
    send.style.color = '#fff';
    send.style.border = 'none';
    send.style.borderRadius = '6px';
    send.style.padding = '8px 12px';

    panel.appendChild(title);
    panel.appendChild(log);
    panel.appendChild(input);
    panel.appendChild(send);
    document.body.appendChild(btn);
    document.body.appendChild(panel);

    return { btn, panel, log, input, send };
  }

  function appendLog(el, who, text) {
    const row = document.createElement('div');
    row.style.margin = '6px 0';
    const whoEl = document.createElement('strong');
    whoEl.textContent = who + ': ';
    row.appendChild(whoEl);
    row.appendChild(document.createTextNode(text));
    el.appendChild(row);
    el.scrollTop = el.scrollHeight;
  }

  window.initAsistenteIA = function (ctx) {
    const proyectoId = ctx && ctx.proyectoId ? ctx.proyectoId : null;
    const ui = createUI();

    ui.btn.addEventListener('click', function () {
      ui.panel.style.display = ui.panel.style.display === 'none' ? 'block' : 'none';
    });

    ui.send.addEventListener('click', async function () {
      const pregunta = (ui.input.value || '').trim();
      console.log('🔍 [asistente-ia] Pregunta:', pregunta);
      if (!pregunta) return;
      appendLog(ui.log, 'Tú', pregunta);
      ui.input.value = '';

      try {
        const resp = await fetch('/api/ia-consulta.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ proyecto_id: proyectoId, pregunta })
        });
        console.log('📡 [asistente-ia] Status:', resp.status);
        const json = await resp.json();
        console.log('✅ [asistente-ia] Respuesta:', json);
        if (json && json.ok) {
          appendLog(ui.log, 'IA', json.respuesta);
        } else {
          appendLog(ui.log, 'IA', '❌ ' + (json && json.error ? json.error : 'Error'));
        }
      } catch (err) {
        console.log('❌ [asistente-ia] Error:', err.message);
        appendLog(ui.log, 'Sistema', '❌ Error de red.');
      }
    });
  };
})();
