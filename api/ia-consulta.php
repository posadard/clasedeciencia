<?php
/**
 * API: IA Consulta — 2 instancias (frontend + backend)
 *
 * Frontend POST: { instancia: 'frontend', clase_id: int, pregunta: string }
 * Backend  POST: { instancia: 'backend',  contexto_pagina: string, pregunta: string,
 *                  entidad_tipo?: string, entidad_id?: int }
 *
 * Response: { ok, respuesta, guardrail_activado, cached, modelo_usado, tokens, tiempo_ms }
 */

// Evitar que PHP imprima warnings/notices dentro de la respuesta JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capturar cualquier salida espuria antes del JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function json_fail(string $message, array $extra = []): void {
    ob_end_clean(); // descartar cualquier salida PHP previa
    echo json_encode(array_merge(['ok' => false, 'error' => $message], $extra),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Realiza una llamada a la API de Groq.
 * Retorna array con: raw, errno, http_status, tiempo_ms
 */
function groq_call(string $api_key, string $modelo, array $messages, float $temperature, int $max_tokens, float $top_p): array {
    $payload = [
        'model'                  => $modelo,
        'temperature'            => $temperature,
        'max_completion_tokens'  => $max_tokens,
        'top_p'                  => $top_p,
        'messages'               => $messages,
    ];
    $t0 = microtime(true);
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ],
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 30,
    ]);
    $raw         = curl_exec($ch);
    $errno       = curl_errno($ch);
    $http_status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [
        'raw'         => $raw,
        'errno'       => $errno,
        'http_status' => $http_status,
        'tiempo_ms'   => (int)((microtime(true) - $t0) * 1000),
    ];
}

/**
 * Intenta llamar a Groq con hasta 3 modelos en cascada.
 * Solo hace fallback en 429 (rate limit) y 503 (unavailable).
 * Retorna: { respuesta, modelo_usado, tokens, tiempo_ms } o null si todos fallan.
 */
function groq_con_fallback(string $api_key, array $modelos, array $messages, float $temperature, int $max_tokens, float $top_p): ?array {
    foreach ($modelos as $modelo) {
        if (empty($modelo)) continue;
        $r = groq_call($api_key, $modelo, $messages, $temperature, $max_tokens, $top_p);

        if ($r['errno'] !== 0) {
            error_log("IA groq curl error [{$modelo}]: errno={$r['errno']}");
            break; // Error de red: no hay fallback útil
        }

        if ($r['http_status'] >= 200 && $r['http_status'] < 300) {
            $json      = json_decode($r['raw'], true);
            $respuesta = $json['choices'][0]['message']['content'] ?? null;
            if ($respuesta !== null) {
                return [
                    'respuesta'    => $respuesta,
                    'modelo_usado' => $json['model'] ?? $modelo,
                    'tokens'       => (int)($json['usage']['total_tokens'] ?? 0),
                    'tiempo_ms'    => $r['tiempo_ms'],
                ];
            }
        }

        // Solo fallback en rate limit o indisponibilidad
        if (!in_array($r['http_status'], [429, 503])) {
            error_log("IA groq error [{$modelo}]: HTTP {$r['http_status']} — sin fallback");
            break;
        }
        error_log("IA groq fallback [{$modelo}]: HTTP {$r['http_status']} → siguiente modelo");
    }
    return null;
}

// ---------------------------------------------------------------
// Context Builders
// ---------------------------------------------------------------

/**
 * Construye el bloque de contexto para la instancia FRONTEND.
 * Combina: clase, áreas, competencias, kit, manual, guía y prompt pedagógico.
 */
function build_context_frontend(PDO $pdo, ?int $clase_id): string {
    if (!$clase_id) return '';

    $bloques = [];

    try {
        // 1. Clase base (vista ya existente)
        $stmt = $pdo->prepare('SELECT * FROM v_clase_contexto_ia WHERE clase_id = ? LIMIT 1');
        $stmt->execute([$clase_id]);
        $clase = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($clase) {
            $areas = $clase['areas'] ? implode(', ', json_decode($clase['areas'], true) ?: []) : 'N/A';
            $comp  = $clase['competencias'] ? implode('; ', json_decode($clase['competencias'], true) ?: []) : 'N/A';
            $bloques[] = "=== CLASE ACTUAL ===\n"
                . "Nombre: {$clase['nombre']}\n"
                . "Ciclo: {$clase['ciclo']} | Grados: {$clase['grados']} | Dificultad: {$clase['dificultad']}\n"
                . "Duración: {$clase['duracion_minutos']} min\n"
                . "Resumen: {$clase['resumen']}\n"
                . "Objetivo de aprendizaje: {$clase['objetivo_aprendizaje']}\n"
                . "Áreas del conocimiento: {$areas}\n"
                . "Competencias MEN: {$comp}";
        }

        // 2. Materiales del kit
        $stmt = $pdo->prepare('SELECT kit_nombre, item_nombre, cantidad, notas FROM v_clase_kits_detalle WHERE clase_id = ?');
        $stmt->execute([$clase_id]);
        $mats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($mats) {
            $lineas = array_map(function ($m) {
                $nota = $m['notas'] ? " ({$m['notas']})" : '';
                return "  - {$m['item_nombre']} x{$m['cantidad']}{$nota}";
            }, $mats);
            $bloques[] = "=== MATERIALES DEL KIT ({$mats[0]['kit_nombre']}) ===\n" . implode("\n", $lineas);
        }

        // 3. Manual del kit principal
        $stmt = $pdo->prepare(
            'SELECT km.pasos_json, km.seguridad_json
             FROM kit_manuals km
             JOIN clase_kits ck ON ck.kit_id = km.kit_id
             WHERE ck.clase_id = ? AND ck.es_principal = 1 AND km.status = \'published\'
             ORDER BY km.updated_at DESC LIMIT 1'
        );
        $stmt->execute([$clase_id]);
        $manual = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($manual && $manual['pasos_json']) {
            $pasos  = json_decode($manual['pasos_json'], true) ?: [];
            $lineas = array_map(fn($p, $i) => "  {$p['orden']}. {$p['titulo']}", $pasos, array_keys($pasos));
            $bloques[] = "=== PASOS DEL MANUAL ===\n" . implode("\n", $lineas);
        }

        // 4. Guía de la clase
        $stmt = $pdo->prepare('SELECT pasos, explicacion_cientifica FROM guias WHERE clase_id = ? LIMIT 1');
        $stmt->execute([$clase_id]);
        $guia = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($guia && $guia['pasos']) {
            $pasos  = json_decode($guia['pasos'], true) ?: [];
            $lineas = array_map(fn($p, $i) => "  " . ($i + 1) . ". {$p['titulo']}: {$p['detalle']}", $pasos, array_keys($pasos));
            $bloques[] = "=== GUÍA PASO A PASO ===\n" . implode("\n", $lineas);
            if (!empty($guia['explicacion_cientifica'])) {
                $bloques[] = "=== EXPLICACIÓN CIENTÍFICA ===\n{$guia['explicacion_cientifica']}";
            }
        }

        // 5. Prompt pedagógico (prompts_clase)
        $stmt = $pdo->prepare('SELECT prompt_contexto, enfoque_pedagogico, conocimientos_previos, preguntas_frecuentes FROM prompts_clase WHERE clase_id = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$clase_id]);
        $pc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pc) {
            $cp = $pc['conocimientos_previos'] ? implode(', ', json_decode($pc['conocimientos_previos'], true) ?: []) : '';
            $pf = $pc['preguntas_frecuentes'] ? implode(' / ', json_decode($pc['preguntas_frecuentes'], true) ?: []) : '';
            $bloques[] = "=== ORIENTACIONES PEDAGÓGICAS ===\n"
                . ($pc['prompt_contexto'] ? "Contexto: {$pc['prompt_contexto']}\n" : '')
                . ($pc['enfoque_pedagogico'] ? "Enfoque: {$pc['enfoque_pedagogico']}\n" : '')
                . ($cp ? "Conocimientos previos: {$cp}\n" : '')
                . ($pf ? "Preguntas frecuentes: {$pf}" : '');
        }

    } catch (Exception $e) {
        error_log('IA context frontend error: ' . $e->getMessage());
    }

    return implode("\n\n", array_filter($bloques));
}

/**
 * Construye el bloque de contexto para la instancia BACKEND.
 * Datos varían según la página admin activa.
 */
function build_context_backend(PDO $pdo, string $contexto_pagina, ?string $entidad_tipo, ?int $entidad_id): string {
    $bloques = [];

    try {
        switch ($contexto_pagina) {
            case 'clases':
                $rows = $pdo->query(
                    'SELECT id, nombre, ciclo, dificultad, duracion_minutos, status, activo FROM clases ORDER BY ciclo, nombre'
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== CLASES (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['nombre']} | Ciclo {$r['ciclo']} | {$r['status']} | activo:{$r['activo']}", $rows));
                break;

            case 'kits':
                $rows = $pdo->query(
                    'SELECT k.id, k.nombre, k.codigo, k.activo, c.nombre as clase FROM kits k LEFT JOIN clases c ON c.id = k.clase_id ORDER BY k.nombre'
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== KITS (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['nombre']} ({$r['codigo']}) | Clase: {$r['clase']} | activo:{$r['activo']}", $rows));
                break;

            case 'componentes':
                $rows = $pdo->query(
                    'SELECT i.id, i.nombre_comun, i.sku, cat.nombre as categoria FROM kit_items i LEFT JOIN categorias_items cat ON cat.id = i.categoria_id ORDER BY i.nombre_comun'
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== COMPONENTES (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['nombre_comun']} ({$r['sku']}) | Categoría: {$r['categoria']}", $rows));
                break;

            case 'contratos':
                $rows = $pdo->query(
                    'SELECT id, numero, entidad_contratante, departamento, valor, fecha FROM contratos ORDER BY fecha DESC'
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== CONTRATOS (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['numero']} | {$r['entidad_contratante']} | {$r['departamento']} | $" . number_format($r['valor'], 0, ',', '.') . " | {$r['fecha']}", $rows));
                break;

            case 'entregas':
                $rows = $pdo->query(
                    'SELECT e.id, e.institucion_educativa, e.fecha, e.acta_pdf, c.numero as contrato, c.departamento
                     FROM entregas e JOIN contratos c ON c.id = e.contrato_id ORDER BY e.fecha DESC LIMIT 50'
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== ÚLTIMAS ENTREGAS (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['institucion_educativa']} | Contrato: {$r['contrato']} | {$r['departamento']} | {$r['fecha']} | acta:" . ($r['acta_pdf'] ? 'sí' : 'no'), $rows));
                break;

            case 'lotes':
                $rows = $pdo->query(
                    'SELECT l.*, k.nombre as kit_nombre FROM lotes l LEFT JOIN kits k ON k.id = l.kit_id ORDER BY l.created_at DESC'
                )->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $bloques[] = "=== LOTES (" . count($rows) . " registros) ===\n"
                        . implode("\n", array_map(fn($r) =>
                            "  [{$r['id']}] Kit: {$r['kit_nombre']} | " . json_encode($r, JSON_UNESCAPED_UNICODE), $rows));
                } else {
                    $bloques[] = "=== LOTES ===\n  Sin registros.";
                }
                break;

            case 'ia':
                $rows = $pdo->query('SELECT * FROM v_ia_dashboard ORDER BY fecha DESC LIMIT 14')->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== DASHBOARD IA (últimos 14 días) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  {$r['fecha']}: sesiones={$r['sesiones_unicas']} consultas={$r['total_consultas']} errores={$r['total_errores']} guardrails={$r['alertas_seguridad']} tokens={$r['tokens_totales']} costo_USD={$r['costo_total']}", $rows));
                $estados = $pdo->query("SELECT instancia, valor FROM configuracion_ia WHERE clave = 'ia_activa'")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($estados as $e) {
                    $bloques[] = "IA {$e['instancia']}: " . ($e['valor'] == '1' ? '✅ activa' : '❌ inactiva');
                }
                break;

            default: // dashboard global
                $bloques[] = "=== RESUMEN SISTEMA ===";
                $bloques[] = "Clases: " . $pdo->query('SELECT COUNT(*) FROM clases WHERE activo=1')->fetchColumn()
                    . " activas de " . $pdo->query('SELECT COUNT(*) FROM clases')->fetchColumn() . " total";
                $bloques[] = "Kits: " . $pdo->query('SELECT COUNT(*) FROM kits WHERE activo=1')->fetchColumn() . " activos";
                $bloques[] = "Contratos: " . $pdo->query('SELECT COUNT(*) FROM contratos')->fetchColumn();
                $bloques[] = "Entregas: " . $pdo->query('SELECT COUNT(*) FROM entregas')->fetchColumn();
                $bloques[] = "Componentes: " . $pdo->query('SELECT COUNT(*) FROM kit_items')->fetchColumn();
                break;
        }

        // Contexto profundo de entidad específica
        if ($entidad_tipo && $entidad_id) {
            switch ($entidad_tipo) {
                case 'contrato':
                    $stmt = $pdo->prepare('SELECT * FROM contratos WHERE id = ? LIMIT 1');
                    $stmt->execute([$entidad_id]);
                    $c = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($c) {
                        $bloques[] = "=== CONTRATO #{$entidad_id} (detalle) ===\n" . json_encode($c, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        $stmt = $pdo->prepare('SELECT * FROM entregas WHERE contrato_id = ? ORDER BY fecha DESC');
                        $stmt->execute([$entidad_id]);
                        $ents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $bloques[] = "Entregas de este contrato (" . count($ents) . "):\n"
                            . implode("\n", array_map(fn($e) => "  - {$e['institucion_educativa']} ({$e['fecha']})", $ents));
                    }
                    break;

                case 'kit':
                    $stmt = $pdo->prepare('SELECT k.*, c.nombre as clase_nombre FROM kits k LEFT JOIN clases c ON c.id = k.clase_id WHERE k.id = ? LIMIT 1');
                    $stmt->execute([$entidad_id]);
                    $k = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($k) {
                        $bloques[] = "=== KIT #{$entidad_id} (detalle) ===\n"
                            . "Nombre: {$k['nombre']} | Código: {$k['codigo']} | Clase: {$k['clase_nombre']}";
                        $stmt = $pdo->prepare('SELECT i.nombre_comun, kc.cantidad, kc.notas FROM kit_componentes kc JOIN kit_items i ON i.id = kc.item_id WHERE kc.kit_id = ?');
                        $stmt->execute([$entidad_id]);
                        $comps = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $bloques[] = "Componentes:\n" . implode("\n", array_map(fn($c) => "  - {$c['nombre_comun']} x{$c['cantidad']}", $comps));
                    }
                    break;

                case 'clase':
                    $stmt = $pdo->prepare('SELECT * FROM v_clase_contexto_ia WHERE clase_id = ? LIMIT 1');
                    $stmt->execute([$entidad_id]);
                    $cl = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($cl) {
                        $bloques[] = "=== CLASE #{$entidad_id} (detalle) ===\n" . json_encode($cl, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                    break;

                case 'entrega':
                    $stmt = $pdo->prepare('SELECT e.*, c.numero, c.entidad_contratante FROM entregas e JOIN contratos c ON c.id = e.contrato_id WHERE e.id = ? LIMIT 1');
                    $stmt->execute([$entidad_id]);
                    $e = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($e) {
                        $bloques[] = "=== ENTREGA #{$entidad_id} (detalle) ===\n" . json_encode($e, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                    break;
            }
        }

    } catch (Exception $ex) {
        error_log('IA context backend error: ' . $ex->getMessage());
    }

    return implode("\n\n", array_filter($bloques));
}

// ---------------------------------------------------------------
// Main
// ---------------------------------------------------------------

try {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    // Parámetros comunes
    $instancia       = ($data['instancia'] ?? 'frontend') === 'backend' ? 'backend' : 'frontend';
    $pregunta        = trim($data['pregunta'] ?? '');

    if ($pregunta === '') json_fail('Pregunta vacía.');
    if (mb_strlen($pregunta) > 2000) json_fail('Pregunta demasiado larga.');

    // Frontend: proteger el endpoint backend de acceso externo
    if ($instancia === 'backend') {
        session_start();
        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            json_fail('No autorizado.', ['code' => 403]);
        }
    }

    // Parámetros por instancia
    $clase_id        = $instancia === 'frontend' ? (isset($data['clase_id']) ? (int)$data['clase_id'] : null) : null;
    $contexto_pagina = $instancia === 'backend'  ? trim($data['contexto_pagina'] ?? 'dashboard') : '';
    $entidad_tipo    = $instancia === 'backend'  ? trim($data['entidad_tipo'] ?? '') : null;
    $entidad_id      = $instancia === 'backend'  ? (isset($data['entidad_id']) ? (int)$data['entidad_id'] : null) : null;

    // ---------------------------------------------------------------
    // Cargar configuración de esta instancia
    // ---------------------------------------------------------------
    $stmt = $pdo->prepare('SELECT clave, valor, tipo FROM configuracion_ia WHERE instancia = ?');
    $stmt->execute([$instancia]);
    $cfg = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cfg[$row['clave']] = $row['valor'];
    }

    $ia_activa = (($cfg['ia_activa'] ?? '0') === '1');
    if (!$ia_activa) json_fail('IA desactivada por configuración.');

    $api_key     = $cfg['groq_api_key'] ?? '';
    if (empty($api_key)) json_fail('⚠️ IA no configurada. Falta API Key.');

    $modelos     = array_filter([
        $cfg['groq_model_1'] ?? '',
        $cfg['groq_model_2'] ?? '',
        $cfg['groq_model_3'] ?? '',
    ]);
    $temperature = (float)($cfg['groq_temperature'] ?? '0.5');
    $max_tokens  = (int)($cfg['groq_max_tokens']  ?? '800');
    $top_p       = (float)($cfg['groq_top_p']     ?? '0.9');
    $prompt_base = $cfg['prompt_sistema']          ?? '';

    $guardrails_activos  = (($cfg['guardrails_activos'] ?? '0') === '1');
    $palabras_peligro    = json_decode($cfg['palabras_peligro']   ?? '[]', true) ?: [];
    $palabras_tematicas  = json_decode($cfg['palabras_tematicas'] ?? '[]', true) ?: [];
    $mensaje_guardrail   = $cfg['mensaje_guardrail'] ?? '⚠️ Consulta con tu profesor.';

    // ---------------------------------------------------------------
    // Sesión anónima (solo frontend)
    // ---------------------------------------------------------------
    $sesion_id = null;
    if ($instancia === 'frontend') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $sesion_hash = $_COOKIE['cdc_session'] ?? '';
        if ($sesion_hash === '') {
            $sesion_hash = bin2hex(random_bytes(16));
            setcookie('cdc_session', $sesion_hash, time() + 3600 * 24 * 365, '/', '', true, true);
        }
        try {
            $stmt = $pdo->prepare('SELECT id FROM ia_sesiones WHERE sesion_hash = ?');
            $stmt->execute([$sesion_hash]);
            $ses = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($ses) {
                $sesion_id = (int)$ses['id'];
                $pdo->prepare('UPDATE ia_sesiones SET clase_id = COALESCE(?, clase_id), instancia = ?, fecha_ultima_interaccion = NOW() WHERE id = ?')
                    ->execute([$clase_id, $instancia, $sesion_id]);
            } else {
                $pdo->prepare('INSERT INTO ia_sesiones (sesion_hash, clase_id, instancia) VALUES (?, ?, ?)')
                    ->execute([$sesion_hash, $clase_id, $instancia]);
                $sesion_id = (int)$pdo->lastInsertId();
            }
        } catch (Exception $e) {
            error_log('IA sesión error: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Guardrails
    // ---------------------------------------------------------------
    $pregunta_lower    = mb_strtolower($pregunta, 'UTF-8');
    $guardrail_activado = false;
    $guardrail_palabra  = '';
    if ($guardrails_activos) {
        $todas_palabras = array_merge($palabras_peligro, $palabras_tematicas);
        foreach ($todas_palabras as $pal) {
            if ($pal && strpos($pregunta_lower, mb_strtolower($pal, 'UTF-8')) !== false) {
                $guardrail_activado = true;
                $guardrail_palabra  = $pal;
                break;
            }
        }
    }

    // ---------------------------------------------------------------
    // Caché (solo frontend, solo cuando no hay guardrail)
    // ---------------------------------------------------------------
    $cached   = false;
    $respuesta = null;
    if ($instancia === 'frontend' && $clase_id && !$guardrail_activado) {
        try {
            $stmt = $pdo->prepare('SELECT id, respuesta FROM ia_respuestas_cache WHERE clase_id = ? AND pregunta_normalizada = ? AND activa = 1 LIMIT 1');
            $stmt->execute([$clase_id, $pregunta_lower]);
            $rowC = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowC) {
                $cached    = true;
                $respuesta = $rowC['respuesta'];
                $pdo->prepare('UPDATE ia_respuestas_cache SET veces_usada = veces_usada + 1, ultima_vez_usada = NOW() WHERE id = ?')
                    ->execute([$rowC['id']]);
            }
        } catch (Exception $e) {
            error_log('IA cache read error: ' . $e->getMessage());
        }
    }

    $tokens       = 0;
    $tiempo_ms    = 0;
    $modelo_usado = '';

    if (!$cached) {
        if ($guardrail_activado) {
            $respuesta = $mensaje_guardrail;
            // Registrar guardrail
            if ($sesion_id) {
                try {
                    $pdo->prepare('INSERT INTO ia_guardrails_log (sesion_id, clase_id, instancia, pregunta_usuario, palabra_detectada, tipo_alerta, respuesta_dada) VALUES (?, ?, ?, ?, ?, \'peligro\', ?)')
                        ->execute([$sesion_id, $clase_id, $instancia, $pregunta, $guardrail_palabra, $mensaje_guardrail]);
                } catch (Exception $e) {
                    error_log('IA guardrail log error: ' . $e->getMessage());
                }
            }
        } else {
            // Construir contexto según instancia
            if ($instancia === 'frontend') {
                $contexto_texto = build_context_frontend($pdo, $clase_id);
            } else {
                $contexto_texto = build_context_backend($pdo, $contexto_pagina, $entidad_tipo, $entidad_id);
            }

            $system_content = $prompt_base;
            if (!empty($contexto_texto)) {
                $system_content .= "\n\n" . $contexto_texto;
            }

            $messages = [
                ['role' => 'system', 'content' => $system_content],
                ['role' => 'user',   'content' => $pregunta],
            ];

            $resultado = groq_con_fallback($api_key, $modelos, $messages, $temperature, $max_tokens, $top_p);

            if ($resultado) {
                $respuesta    = $resultado['respuesta'];
                $modelo_usado = $resultado['modelo_usado'];
                $tokens       = $resultado['tokens'];
                $tiempo_ms    = $resultado['tiempo_ms'];
            } else {
                $respuesta = '❌ Error al consultar la IA. Intenta de nuevo.';
            }
        }

        // Guardar en caché (solo frontend, sin guardrail, con respuesta válida)
        if ($instancia === 'frontend' && $clase_id && !$guardrail_activado && !empty($respuesta)) {
            try {
                $pdo->prepare('INSERT IGNORE INTO ia_respuestas_cache (clase_id, pregunta_normalizada, pregunta_original, respuesta) VALUES (?, ?, ?, ?)')
                    ->execute([$clase_id, $pregunta_lower, $pregunta, $respuesta]);
            } catch (Exception $e) {
                error_log('IA cache insert error: ' . $e->getMessage());
            }
        }
    }

    // ---------------------------------------------------------------
    // Logging en ia_logs
    // ---------------------------------------------------------------
    try {
        $tipo_evento = $guardrail_activado ? 'guardrail_activado' : 'consulta';
        $pdo->prepare(
            'INSERT INTO ia_logs (sesion_id, clase_id, instancia, tipo_evento, descripcion, tokens_usados, tiempo_respuesta_ms, modelo_usado, costo_estimado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $sesion_id,
            $clase_id,
            $instancia,
            $tipo_evento,
            $cached ? 'Respuesta desde caché' : ($guardrail_activado ? "Guardrail: {$guardrail_palabra}" : 'Consulta Groq'),
            $tokens,
            $tiempo_ms,
            $modelo_usado,
            0.0,
        ]);
    } catch (Exception $e) {
        error_log('IA log error: ' . $e->getMessage());
    }

    ob_end_clean(); // descartar warnings/notices PHP antes de responder
    echo json_encode([
        'ok'                 => true,
        'respuesta'          => $respuesta,
        'guardrail_activado' => $guardrail_activado,
        'cached'             => $cached,
        'modelo_usado'       => $modelo_usado,
        'tokens'             => $tokens,
        'tiempo_ms'          => $tiempo_ms,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log('IA consulta fatal: ' . $e->getMessage());
    ob_end_clean();
    json_fail('Error interno del servidor.');
}


try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    $clase_id = isset($data['clase_id']) ? (int)$data['clase_id'] : null;
    $pregunta = isset($data['pregunta']) ? trim($data['pregunta']) : '';

    if ($pregunta === '') {
        json_fail('Pregunta vacía.');
    }

    // Configuración IA
    $stmtCfg = $pdo->prepare('SELECT clave, valor, tipo FROM configuracion_ia');
    $stmtCfg->execute();
    $cfgRows = $stmtCfg->fetchAll(PDO::FETCH_ASSOC);
    $cfg = [];
    foreach ($cfgRows as $row) { $cfg[$row['clave']] = $row['valor']; }

    $ia_activa = isset($cfg['ia_activa']) ? (int)$cfg['ia_activa'] === 1 : false;
    if (!$ia_activa) {
        json_fail('IA desactivada por configuración.');
    }

    $guardrails_activos = isset($cfg['guardrails_activos']) ? (int)$cfg['guardrails_activos'] === 1 : true;
    $palabras_peligro = [];
    if (!empty($cfg['palabras_peligro'])) {
        $tmp = json_decode($cfg['palabras_peligro'], true);
        if (is_array($tmp)) $palabras_peligro = $tmp;
    }
    $mensaje_guardrail = $cfg['mensaje_guardrail'] ?? '⚠️ Consulta con tu profesor antes de modificar el experimento.';
    $modelo = $cfg['groq_model'] ?? 'llama-3.3-70b-versatile';
    $temperature = (float)($cfg['groq_temperature'] ?? '0.7');
    $max_tokens = (int)($cfg['groq_max_tokens'] ?? '1000');
    $api_key = $cfg['groq_api_key'] ?? '';
    $contexto_sistema = $cfg['contexto_sistema'] ?? '';

    // Sesión anónima
    $sesion_hash = $_COOKIE['cdc_session'] ?? '';
    if ($sesion_hash === '') {
        $sesion_hash = bin2hex(random_bytes(16));
        setcookie('cdc_session', $sesion_hash, time() + 3600 * 24 * 365, '/');
    }
    $sesion_id = null;
    try {
        $stmtS = $pdo->prepare('SELECT id FROM ia_sesiones WHERE sesion_hash = ?');
        $stmtS->execute([$sesion_hash]);
        $ses = $stmtS->fetch(PDO::FETCH_ASSOC);
        if ($ses) {
            $sesion_id = (int)$ses['id'];
            $pdo->prepare('UPDATE ia_sesiones SET clase_id = COALESCE(?, clase_id), fecha_ultima_interaccion = NOW() WHERE id = ?')
                ->execute([$clase_id, $sesion_id]);
        } else {
            $pdo->prepare('INSERT INTO ia_sesiones (sesion_hash, clase_id) VALUES (?, ?)')->execute([$sesion_hash, $clase_id]);
            $sesion_id = (int)$pdo->lastInsertId();
        }
    } catch (Exception $e) {
        // Continuar sin bloquear si falla creación de sesión
        error_log('IA sesión error: ' . $e->getMessage());
    }

    // Guardrail básico
    $pregunta_lower = mb_strtolower($pregunta, 'UTF-8');
    $guardrail_activado = false;
    if ($guardrails_activos && !empty($palabras_peligro)) {
        foreach ($palabras_peligro as $pal) {
            if ($pal && strpos($pregunta_lower, mb_strtolower($pal, 'UTF-8')) !== false) {
                $guardrail_activado = true;
                break;
            }
        }
    }

    // Intentar caché
    $cached = false;
    $respuesta = null;
    if ($clase_id) {
        try {
            $stmtC = $pdo->prepare('SELECT id, respuesta FROM ia_respuestas_cache WHERE clase_id = ? AND pregunta_normalizada = ? AND activa = 1 LIMIT 1');
            $stmtC->execute([$clase_id, $pregunta_lower]);
            $rowC = $stmtC->fetch(PDO::FETCH_ASSOC);
            if ($rowC) {
                $cached = true;
                $respuesta = $rowC['respuesta'];
                // Actualizar uso
                $pdo->prepare('UPDATE ia_respuestas_cache SET veces_usada = veces_usada + 1, ultima_vez_usada = NOW() WHERE id = ?')->execute([$rowC['id']]);
            }
        } catch (Exception $e) {
            error_log('IA cache error: ' . $e->getMessage());
        }
    }

    $tokens = 0; $tiempo_ms = 0;

    if (!$cached) {
        if ($guardrail_activado) {
            $respuesta = $mensaje_guardrail;
        } else {
            // Contexto del proyecto (si aplica)
            $contexto = [];
            $materiales_ctx = [];
            if ($clase_id) {
                try {
                    $stmtCtx = $pdo->prepare('SELECT * FROM v_clase_contexto_ia WHERE clase_id = ? LIMIT 1');
                    $stmtCtx->execute([$clase_id]);
                    $contexto = $stmtCtx->fetch(PDO::FETCH_ASSOC) ?: [];

                    $stmtMat = $pdo->prepare('SELECT * FROM v_clase_kits_detalle WHERE clase_id = ?');
                    $stmtMat->execute([$clase_id]);
                    $materiales_ctx = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    error_log('IA contexto error: ' . $e->getMessage());
                }
            }

            // Llamada a Groq (OpenAI compatible)
            if (!empty($api_key)) {
                $payload = [
                    'model' => $modelo,
                    'temperature' => $temperature,
                    'max_tokens' => $max_tokens,
                    'messages' => [
                        ['role' => 'system', 'content' => $contexto_sistema],
                        ['role' => 'user', 'content' => json_encode([
                            'pregunta' => $pregunta,
                            'clase' => $contexto,
                            'materiales' => $materiales_ctx
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
                    ]
                ];
                $t0 = microtime(true);
                $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $api_key
                    ],
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload)
                ]);
                $resp = curl_exec($ch);
                $errno = curl_errno($ch);
                $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                curl_close($ch);
                $tiempo_ms = (int)((microtime(true) - $t0) * 1000);

                if ($errno === 0 && $resp && $status >= 200 && $status < 300) {
                    $json = json_decode($resp, true);
                    $respuesta = $json['choices'][0]['message']['content'] ?? 'Sin respuesta';
                    $tokens = isset($json['usage']['total_tokens']) ? (int)$json['usage']['total_tokens'] : 0;
                } else {
                    $respuesta = '❌ Error al consultar la IA.';
                }
            } else {
                $respuesta = '⚠️ IA no configurada. Falta API Key.';
            }
        }

        // Guardar en caché si hay proyecto y no es guardrail ni error
        if ($clase_id && !$guardrail_activado && !empty($respuesta)) {
            try {
                $pdo->prepare('INSERT INTO ia_respuestas_cache (clase_id, pregunta_normalizada, pregunta_original, respuesta) VALUES (?, ?, ?, ?)')
                    ->execute([$clase_id, $pregunta_lower, $pregunta, $respuesta]);
            } catch (Exception $e) {
                error_log('IA cache insert error: ' . $e->getMessage());
            }
        }
    }

    // Registrar interacción (SP) si hay sesión
    if ($sesion_id) {
        try {
            $stmtLog = $pdo->prepare('CALL sp_registrar_interaccion_ia_clase(?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $costo = 0.0; // estimado
            $stmtLog->execute([$sesion_id, $clase_id, $pregunta, $respuesta, $tokens, $tiempo_ms, $modelo, $costo, $guardrail_activado ? 1 : 0]);
        } catch (Exception $e) {
            error_log('IA log error: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'ok' => true,
        'respuesta' => $respuesta,
        'guardrail_activado' => $guardrail_activado,
        'cached' => $cached,
        'modelo' => $modelo,
        'tokens' => $tokens,
        'tiempo_ms' => $tiempo_ms
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    error_log('IA consulta fatal: ' . $e->getMessage());
    json_fail('Error interno.');
}
