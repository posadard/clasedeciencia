<?php
require_once '../auth.php';
$page_title = 'Panel IA';

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------
function ia_get_config(PDO $pdo, string $instancia): array {
    $stmt = $pdo->prepare('SELECT clave, valor, tipo FROM configuracion_ia WHERE instancia = ? ORDER BY clave');
    $stmt->execute([$instancia]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[$row['clave']] = ['valor' => $row['valor'], 'tipo' => $row['tipo']];
    }
    return $out;
}

function ia_mask(string $valor, string $tipo): string {
    if ($tipo !== 'secreto' || strlen($valor) < 8) return $valor;
    return str_repeat('●', max(0, strlen($valor) - 4)) . substr($valor, -4);
}

function ia_field_label(string $clave): string {
    $labels = [
        'ia_activa'          => 'IA Activa',
        'groq_api_key'       => 'Groq API Key',
        'groq_model_1'       => 'Modelo Principal',
        'groq_model_2'       => 'Modelo Fallback 1',
        'groq_model_3'       => 'Modelo Fallback 2',
        'groq_temperature'   => 'Temperatura',
        'groq_max_tokens'    => 'Tokens Máximos',
        'groq_top_p'         => 'Top-P',
        'prompt_sistema'     => 'Prompt del Sistema',
        'guardrails_activos' => 'Guardrails Activos',
        'palabras_peligro'   => 'Palabras Peligrosas (JSON)',
        'palabras_tematicas' => 'Palabras Fuera de Tema (JSON)',
        'mensaje_guardrail'  => 'Mensaje de Guardrail',
        'max_tokens_contexto'=> 'Tokens Máx. Contexto',
    ];
    return $labels[$clave] ?? ucwords(str_replace('_', ' ', $clave));
}

// ---------------------------------------------------------------
// POST: Guardar configuración
// ---------------------------------------------------------------
$save_ok     = false;
$save_msg    = '';
$save_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_config') {
    $instancia_save = in_array($_POST['instancia'] ?? '', ['frontend', 'backend']) ? $_POST['instancia'] : null;
    if (!$instancia_save) {
        $save_error = 'Instancia inválida.';
    } else {
        try {
            $stmt_get  = $pdo->prepare('SELECT clave, tipo FROM configuracion_ia WHERE instancia = ?');
            $stmt_get->execute([$instancia_save]);
            $campos = $stmt_get->fetchAll(PDO::FETCH_KEY_PAIR);     // clave => tipo

            $stmt_upd = $pdo->prepare(
                'UPDATE configuracion_ia SET valor = ?, updated_at = NOW() WHERE instancia = ? AND clave = ?'
            );

            foreach ($campos as $clave => $tipo) {
                $post_key = 'cfg_' . $clave;

                // Los checkboxes booleanos solo aparecen en POST si están marcados.
                // Leemos el valor: 1 si checked, 0 si ausente.
                if ($tipo === 'booleano') {
                    $nuevo_valor = isset($_POST[$post_key]) ? '1' : '0';
                    $stmt_upd->execute([$nuevo_valor, $instancia_save, $clave]);
                    continue;
                }

                if (!array_key_exists($post_key, $_POST)) continue;
                $nuevo_valor = $_POST[$post_key];

                // Para secretos: si vacío o placeholder '●●●●...', no actualizar
                if ($tipo === 'secreto') {
                    $stripped = trim($nuevo_valor);
                    if ($stripped === '' || substr_count($stripped, '●') > 4) continue;
                }

                // Sanitizar según tipo
                if ($tipo === 'booleano') {
                    $nuevo_valor = ($nuevo_valor === '1') ? '1' : '0';
                } elseif ($tipo === 'number') {
                    $nuevo_valor = (string)(float)$nuevo_valor;
                } else {
                    $nuevo_valor = trim($nuevo_valor);
                }

                $stmt_upd->execute([$nuevo_valor, $instancia_save, $clave]);
            }
            $save_ok  = true;
            $save_msg = "Configuración de instancia '{$instancia_save}' guardada.";
        } catch (PDOException $e) {
            error_log('[IA Admin] save error: ' . $e->getMessage());
            $save_error = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// ---------------------------------------------------------------
// Cargar datos para vistas
// ---------------------------------------------------------------
$cfg_frontend = ia_get_config($pdo, 'frontend');
$cfg_backend  = ia_get_config($pdo, 'backend');

// Logs recientes
$logs_recientes = [];
try {
    $logs_recientes = $pdo->query(
        'SELECT l.id, l.instancia, l.tipo_evento, l.descripcion, l.modelo_usado, l.tokens_usados, l.tiempo_respuesta_ms, l.created_at,
                s.sesion_hash, c.nombre as clase_nombre
         FROM ia_logs l
         LEFT JOIN ia_sesiones s ON s.id = l.sesion_id
         LEFT JOIN clases c      ON c.id  = l.clase_id
         ORDER BY l.created_at DESC LIMIT 50'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { error_log('[IA Admin] logs: ' . $e->getMessage()); }

// Dashboard stats
$stats = ['total_consultas' => 0, 'total_errores' => 0, 'alertas_seguridad' => 0, 'tokens_totales' => 0, 'sesiones_unicas' => 0];
try {
    $row = $pdo->query(
        'SELECT COUNT(*) total_consultas,
                SUM(tipo_evento = \'error\') total_errores,
                SUM(tipo_evento = \'guardrail_activado\') alertas_seguridad,
                SUM(tokens_usados) tokens_totales
         FROM ia_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
    )->fetch(PDO::FETCH_ASSOC);
    if ($row) $stats = array_merge($stats, $row);
    $stats['sesiones_unicas'] = (int)$pdo->query(
        'SELECT COUNT(DISTINCT id) FROM ia_sesiones WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
    )->fetchColumn();
} catch (Exception $e) { error_log('[IA Admin] stats: ' . $e->getMessage()); }

// Active tab (URL ?tab=frontend|backend|estado|logs)
$active_tab = in_array($_GET['tab'] ?? '', ['frontend', 'backend', 'estado', 'logs']) ? $_GET['tab'] : 'estado';

// Token budget: derive JS-safe models list for test dropdowns
$modelos_disponibles = [
    'llama-3.3-70b-versatile',
    'llama-3.1-8b-instant',
    'openai/gpt-oss-20b',
    'llama3-groq-70b-8192-tool-use-preview',
    'gemma2-9b-it',
];

include '../header.php';
?>

<style>
/* IA Admin — Panel-scoped styles */
.ia-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.ia-tab-btn { padding: 0.55rem 1.25rem; background: white; border: 2px solid var(--color-border, #ddd); color: var(--color-text, #2b2b2b); font-weight: 600; cursor: pointer; border-radius: 4px; transition: all 0.15s; font-size: 0.9rem; }
.ia-tab-btn:hover { border-color: var(--color-primary, #1f3c88); color: var(--color-primary, #1f3c88); }
.ia-tab-btn.active { background: var(--color-primary, #1f3c88); color: white; border-color: var(--color-primary, #1f3c88); }
.ia-tab-panel { display: none; }
.ia-tab-panel.active { display: block; }

.ia-cfg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 768px) { .ia-cfg-grid { grid-template-columns: 1fr; } }
.ia-cfg-grid .full-col { grid-column: 1 / -1; }

.ia-form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.ia-form-group label { font-weight: 600; font-size: 0.85rem; }
.ia-form-group input[type="text"],
.ia-form-group input[type="number"],
.ia-form-group select,
.ia-form-group textarea { padding: 0.4rem 0.6rem; border: 1px solid var(--color-border, #ddd); border-radius: 4px; font-size: 0.9rem; font-family: inherit; }
.ia-form-group textarea { resize: vertical; min-height: 80px; }
.ia-form-group .hint { font-size: 0.75rem; color: #777; }

.ia-stat-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.ia-stat { flex: 1; min-width: 120px; background: var(--color-bg-alt, #f8f8f8); border: 1px solid var(--color-border, #ddd); border-radius: 6px; padding: 1rem; text-align: center; }
.ia-stat .num { font-size: 2rem; font-weight: 700; color: var(--color-primary, #1f3c88); }
.ia-stat .lbl { font-size: 0.78rem; color: #555; margin-top: 0.25rem; }

.ia-status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 3px; font-size: 0.8rem; font-weight: 700; }
.ia-status-badge.ok { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.ia-status-badge.off { background: #fbe9e7; color: #bf360c; border: 1px solid #ffab91; }

.ia-test-box { background: #f9f9fb; border: 1px solid var(--color-border, #ddd); border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; }
.ia-test-response { background: white; border: 1px solid var(--color-border, #ddd); border-radius: 6px; padding: 0.85rem; min-height: 80px; white-space: pre-wrap; font-size: 0.9rem; line-height: 1.55; }
.ia-test-meta { font-size: 0.78rem; color: #777; margin-top: 0.4rem; }

.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.data-table th { background: var(--color-bg-alt, #f8f8f8); border-bottom: 2px solid var(--color-border, #ddd); padding: 0.55rem 0.75rem; text-align: left; }
.data-table td { border-bottom: 1px solid var(--color-border, #eee); padding: 0.5rem 0.75rem; }
.data-table tr:hover td { background: #fafafa; }

.flash-ok  { padding: 0.75rem 1rem; background: #e8f5e9; border: 1px solid #a5d6a7; color: #1b5e20; border-radius: 4px; margin-bottom: 1rem; }
.flash-err { padding: 0.75rem 1rem; background: #fbe9e7; border: 1px solid #ffab91; color: #bf360c; border-radius: 4px; margin-bottom: 1rem; }

/* Toggle switch */
.ia-toggle-wrap { display: flex; align-items: center; gap: 0.65rem; margin-top: 0.15rem; }
.ia-toggle { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
.ia-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.ia-toggle-slider { position: absolute; inset: 0; background: #ccc; border-radius: 26px; cursor: pointer; transition: background 0.2s; }
.ia-toggle-slider::before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
.ia-toggle input:checked + .ia-toggle-slider { background: #2e7d32; }
.ia-toggle input:checked + .ia-toggle-slider::before { transform: translateX(20px); }
.ia-toggle-label { font-size: 0.88rem; font-weight: 600; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
    <div>
        <h2>Panel de Inteligencia Artificial</h2>
        <span class="help-text">Gestión de las 2 instancias IA: frontend (estudiantes) y backend (administradores).</span>
    </div>
    <div style="font-size:0.85rem;color:#555;">
        Frontend: <span class="ia-status-badge <?= (($cfg_frontend['ia_activa']['valor'] ?? '0') === '1') ? 'ok' : 'off' ?>">
            <?= (($cfg_frontend['ia_activa']['valor'] ?? '0') === '1') ? 'Activa' : 'Inactiva' ?>
        </span>
        &nbsp;
        Backend: <span class="ia-status-badge <?= (($cfg_backend['ia_activa']['valor'] ?? '0') === '1') ? 'ok' : 'off' ?>">
            <?= (($cfg_backend['ia_activa']['valor'] ?? '0') === '1') ? 'Activa' : 'Inactiva' ?>
        </span>
    </div>
</div>

<?php if ($save_ok): ?>
    <div class="flash-ok">✅ <?= htmlspecialchars($save_msg, ENT_QUOTES, 'UTF-8') ?></div>
<?php elseif ($save_error): ?>
    <div class="flash-err">❌ <?= htmlspecialchars($save_error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- Tab navigation -->
<div class="ia-tabs" role="tablist">
    <button class="ia-tab-btn <?= $active_tab === 'estado' ? 'active' : '' ?>" data-tab="estado"  role="tab">📊 Estado</button>
    <button class="ia-tab-btn <?= $active_tab === 'frontend' ? 'active' : '' ?>" data-tab="frontend" role="tab">🎓 Frontend (Estudiantes)</button>
    <button class="ia-tab-btn <?= $active_tab === 'backend' ? 'active' : '' ?>"  data-tab="backend"  role="tab">🔧 Backend (Admin)</button>
    <button class="ia-tab-btn <?= $active_tab === 'logs' ? 'active' : '' ?>"    data-tab="logs"    role="tab">📋 Logs</button>
</div>

<!-- =================================================================
     TAB: ESTADO / TEST
================================================================= -->
<div class="ia-tab-panel <?= $active_tab === 'estado' ? 'active' : '' ?>" id="tab-estado">
    <div class="card">
        <h3>Resumen últimos 30 días</h3>
        <div class="ia-stat-row">
            <div class="ia-stat">
                <div class="num"><?= number_format((int)$stats['total_consultas']) ?></div>
                <div class="lbl">Consultas totales</div>
            </div>
            <div class="ia-stat">
                <div class="num"><?= number_format((int)$stats['sesiones_unicas']) ?></div>
                <div class="lbl">Sesiones únicas</div>
            </div>
            <div class="ia-stat">
                <div class="num"><?= number_format((int)$stats['alertas_seguridad']) ?></div>
                <div class="lbl">Guardrails activados</div>
            </div>
            <div class="ia-stat">
                <div class="num"><?= number_format((int)$stats['total_errores']) ?></div>
                <div class="lbl">Errores</div>
            </div>
            <div class="ia-stat">
                <div class="num"><?= number_format((int)$stats['tokens_totales']) ?></div>
                <div class="lbl">Tokens consumidos</div>
            </div>
        </div>
    </div>

    <!-- Test Frontend -->
    <div class="card">
        <h3>Prueba — IA Frontend (Estudiantes)</h3>
        <div class="ia-test-box">
            <div class="ia-cfg-grid" style="margin-bottom:0.75rem;">
                <div class="ia-form-group">
                    <label>clase_id (opcional)</label>
                    <input type="number" id="test-fe-clase" value="" min="1" placeholder="ej. 1">
                </div>
                <div class="ia-form-group">
                    <label>Pregunta</label>
                    <input type="text" id="test-fe-pregunta" value="¿Qué materiales necesito para este experimento?" style="width:100%;">
                </div>
            </div>
            <button class="btn" id="btn-test-fe" onclick="testIA('frontend')">Enviar pregunta ▶</button>
            <div id="test-fe-result" style="margin-top:0.75rem;display:none;">
                <div class="ia-test-response" id="test-fe-text"></div>
                <div class="ia-test-meta" id="test-fe-meta"></div>
            </div>
        </div>
    </div>

    <!-- Test Backend -->
    <div class="card">
        <h3>Prueba — IA Backend (Admin)</h3>
        <div class="ia-test-box">
            <div class="ia-cfg-grid" style="margin-bottom:0.75rem;">
                <div class="ia-form-group">
                    <label>Contexto de página</label>
                    <select id="test-be-pagina">
                        <option value="dashboard">dashboard</option>
                        <option value="clases">clases</option>
                        <option value="kits">kits</option>
                        <option value="componentes">componentes</option>
                        <option value="contratos">contratos</option>
                        <option value="entregas">entregas</option>
                        <option value="lotes">lotes</option>
                        <option value="ia">ia</option>
                    </select>
                </div>
                <div class="ia-form-group">
                    <label>Pregunta</label>
                    <input type="text" id="test-be-pregunta" value="¿Cuántas clases activas hay?" style="width:100%;">
                </div>
            </div>
            <button class="btn" id="btn-test-be" onclick="testIA('backend')">Enviar pregunta ▶</button>
            <div id="test-be-result" style="margin-top:0.75rem;display:none;">
                <div class="ia-test-response" id="test-be-text"></div>
                <div class="ia-test-meta" id="test-be-meta"></div>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================
     TAB: FRONTEND CONFIG
================================================================= -->
<div class="ia-tab-panel <?= $active_tab === 'frontend' ? 'active' : '' ?>" id="tab-frontend">
    <div class="card">
        <h3>Configuración — Frontend (Estudiantes)</h3>
        <form method="post" action="/admin/ia/index.php?tab=frontend">
            <input type="hidden" name="action" value="save_config">
            <input type="hidden" name="instancia" value="frontend">
            <div class="ia-cfg-grid">
                <?php foreach ($cfg_frontend as $clave => $meta): ?>
                    <?php
                    $valor = $meta['valor'];
                    $tipo  = $meta['tipo'];
                    $label = ia_field_label($clave);
                    $is_full = in_array($clave, ['prompt_sistema', 'palabras_peligro', 'palabras_tematicas', 'mensaje_guardrail']);
                    ?>
                    <div class="ia-form-group <?= $is_full ? 'full-col' : '' ?>">
                        <label for="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
                        <?php if ($tipo === 'booleano'): ?>
                            <div class="ia-toggle-wrap">
                                <label class="ia-toggle">
                                    <input type="checkbox" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="1" <?= $valor === '1' ? 'checked' : '' ?>>
                                    <span class="ia-toggle-slider"></span>
                                </label>
                                <span class="ia-toggle-label" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>_lbl"><?= $valor === '1' ? 'Activo' : 'Inactivo' ?></span>
                            </div>
                        <?php elseif ($tipo === 'number'): ?>
                            <input type="number" step="any" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                        <?php elseif ($tipo === 'secreto'): ?>
                            <input type="text" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="" placeholder="<?= htmlspecialchars(ia_mask($valor, $tipo), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                            <span class="hint">Dejar vacío para mantener el valor actual.</span>
                        <?php elseif ($is_full): ?>
                            <textarea name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" rows="<?= $clave === 'prompt_sistema' ? 8 : 4 ?>"><?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php else: ?>
                            <input type="text" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="fe_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:1.25rem;">
                <button type="submit" class="btn">💾 Guardar configuración frontend</button>
            </div>
        </form>
    </div>
</div>

<!-- =================================================================
     TAB: BACKEND CONFIG
================================================================= -->
<div class="ia-tab-panel <?= $active_tab === 'backend' ? 'active' : '' ?>" id="tab-backend">
    <div class="card">
        <h3>Configuración — Backend (Administradores)</h3>
        <form method="post" action="/admin/ia/index.php?tab=backend">
            <input type="hidden" name="action" value="save_config">
            <input type="hidden" name="instancia" value="backend">
            <div class="ia-cfg-grid">
                <?php foreach ($cfg_backend as $clave => $meta): ?>
                    <?php
                    $valor = $meta['valor'];
                    $tipo  = $meta['tipo'];
                    $label = ia_field_label($clave);
                    $is_full = in_array($clave, ['prompt_sistema', 'palabras_peligro', 'palabras_tematicas', 'mensaje_guardrail']);
                    ?>
                    <div class="ia-form-group <?= $is_full ? 'full-col' : '' ?>">
                        <label for="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
                        <?php if ($tipo === 'booleano'): ?>
                            <div class="ia-toggle-wrap">
                                <label class="ia-toggle">
                                    <input type="checkbox" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="1" <?= $valor === '1' ? 'checked' : '' ?>>
                                    <span class="ia-toggle-slider"></span>
                                </label>
                                <span class="ia-toggle-label" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>_lbl"><?= $valor === '1' ? 'Activo' : 'Inactivo' ?></span>
                            </div>
                        <?php elseif ($tipo === 'number'): ?>
                            <input type="number" step="any" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                        <?php elseif ($tipo === 'secreto'): ?>
                            <input type="text" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="" placeholder="<?= htmlspecialchars(ia_mask($valor, $tipo), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                            <span class="hint">Dejar vacío para mantener el valor actual.</span>
                        <?php elseif ($is_full): ?>
                            <textarea name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" rows="<?= $clave === 'prompt_sistema' ? 8 : 4 ?>"><?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php else: ?>
                            <input type="text" name="cfg_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" id="be_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:1.25rem;">
                <button type="submit" class="btn">💾 Guardar configuración backend</button>
            </div>
        </form>
    </div>
</div>

<!-- =================================================================
     TAB: LOGS
================================================================= -->
<div class="ia-tab-panel <?= $active_tab === 'logs' ? 'active' : '' ?>" id="tab-logs">
    <div class="card">
        <h3>Últimas 50 interacciones</h3>
        <?php if (empty($logs_recientes)): ?>
            <p style="color:#777;">Sin registros aún.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Instancia</th>
                        <th>Evento</th>
                        <th>Descripción</th>
                        <th>Clase</th>
                        <th>Modelo</th>
                        <th>Tokens</th>
                        <th>ms</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs_recientes as $log): ?>
                    <tr>
                        <td><?= (int)$log['id'] ?></td>
                        <td><span class="ia-status-badge <?= $log['instancia'] === 'frontend' ? 'ok' : 'off' ?>"><?= htmlspecialchars($log['instancia'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars($log['tipo_evento'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($log['descripcion'] ?? '', 0, 60, '…'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $log['clase_nombre'] ? htmlspecialchars($log['clase_nombre'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                        <td style="font-size:0.78rem;"><?= htmlspecialchars($log['modelo_usado'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $log['tokens_usados'] ? number_format((int)$log['tokens_usados']) : '—' ?></td>
                        <td><?= $log['tiempo_respuesta_ms'] ? (int)$log['tiempo_respuesta_ms'] : '—' ?></td>
                        <td style="white-space:nowrap;font-size:0.78rem;"><?= htmlspecialchars(substr($log['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
console.log('✅ [Admin/IA] Panel cargado. Tab activo:', '<?= $active_tab ?>');

// Toggle label update
document.querySelectorAll('.ia-toggle input[type="checkbox"]').forEach(function(cb) {
    function updateLabel() {
        var lbl = document.getElementById(cb.id + '_lbl');
        if (lbl) lbl.textContent = cb.checked ? 'Activo' : 'Inactivo';
    }
    cb.addEventListener('change', updateLabel);
});

// ---------------------------------------------------------------
// Tab switching
// ---------------------------------------------------------------
document.querySelectorAll('.ia-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const target = this.getAttribute('data-tab');
        console.log('🔍 [Admin/IA] Cambiando a tab:', target);

        document.querySelectorAll('.ia-tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.ia-tab-panel').forEach(function(p) { p.classList.remove('active'); });

        this.classList.add('active');
        var panel = document.getElementById('tab-' + target);
        if (panel) panel.classList.add('active');

        // Update URL without reload
        try {
            history.replaceState(null, '', '/admin/ia/index.php?tab=' + encodeURIComponent(target));
        } catch(e) {}
    });
});

// ---------------------------------------------------------------
// Test IA
// ---------------------------------------------------------------
async function testIA(instancia) {
    console.log('🔍 [Admin/IA] Probando instancia:', instancia);

    const btnId  = 'btn-test-' + (instancia === 'frontend' ? 'fe' : 'be');
    const resId  = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-result';
    const textId = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-text';
    const metaId = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-meta';

    const btn = document.getElementById(btnId);
    btn.disabled = true;
    btn.textContent = '⏳ Consultando…';

    let body = { instancia };
    if (instancia === 'frontend') {
        const claseId  = parseInt(document.getElementById('test-fe-clase').value) || null;
        const pregunta = document.getElementById('test-fe-pregunta').value.trim();
        body.pregunta  = pregunta;
        if (claseId) body.clase_id = claseId;
    } else {
        body.pregunta          = document.getElementById('test-be-pregunta').value.trim();
        body.contexto_pagina   = document.getElementById('test-be-pagina').value;
    }

    const resDiv  = document.getElementById(resId);
    const textDiv = document.getElementById(textId);
    const metaDiv = document.getElementById(metaId);
    resDiv.style.display = 'block';
    textDiv.textContent  = '…';
    metaDiv.textContent  = '';

    try {
        const resp = await fetch('/api/ia-consulta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
            credentials: 'same-origin',
        });

        console.log('📡 [Admin/IA] Test status:', resp.status);
        const data = await resp.json();
        console.log('✅ [Admin/IA] Test response:', data);

        if (data.ok) {
            textDiv.textContent = data.respuesta;
            metaDiv.textContent = [
                'Modelo: ' + (data.modelo_usado || '—'),
                'Tokens: ' + (data.tokens || 0),
                'Tiempo: ' + (data.tiempo_ms || 0) + ' ms',
                data.cached             ? '📦 Desde caché'      : '',
                data.guardrail_activado ? '🛡️ Guardrail activo' : '',
            ].filter(Boolean).join(' · ');
        } else {
            textDiv.textContent = '❌ Error: ' + (data.error || 'desconocido');
        }
    } catch (err) {
        console.log('❌ [Admin/IA] Test fetch error:', err.message);
        textDiv.textContent = '❌ Error de red: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar pregunta ▶';
    }
}
</script>

<?php include '../footer.php'; ?>
