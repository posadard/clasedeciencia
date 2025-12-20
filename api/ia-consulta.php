<?php
/**
 * API: IA Consulta con guardrails y contexto de proyecto
 * Entrada: JSON { proyecto_id, pregunta }
 * Salida: JSON { respuesta, guardrail_activado, cached, modelo, tokens, tiempo_ms }
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

function json_fail($message, $extra = []) {
    echo json_encode(array_merge(['ok' => false, 'error' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    $proyecto_id = isset($data['proyecto_id']) ? (int)$data['proyecto_id'] : null;
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
            $pdo->prepare('UPDATE ia_sesiones SET proyecto_id = COALESCE(?, proyecto_id), fecha_ultima_interaccion = NOW() WHERE id = ?')
                ->execute([$proyecto_id, $sesion_id]);
        } else {
            $pdo->prepare('INSERT INTO ia_sesiones (sesion_hash, proyecto_id) VALUES (?, ?)')->execute([$sesion_hash, $proyecto_id]);
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
    if ($proyecto_id) {
        try {
            $stmtC = $pdo->prepare('SELECT id, respuesta FROM ia_respuestas_cache WHERE proyecto_id = ? AND pregunta_normalizada = ? AND activa = 1 LIMIT 1');
            $stmtC->execute([$proyecto_id, $pregunta_lower]);
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
            if ($proyecto_id) {
                try {
                    $stmtCtx = $pdo->prepare('SELECT * FROM v_proyecto_contexto_ia WHERE proyecto_id = ? LIMIT 1');
                    $stmtCtx->execute([$proyecto_id]);
                    $contexto = $stmtCtx->fetch(PDO::FETCH_ASSOC) ?: [];

                    $stmtMat = $pdo->prepare('SELECT * FROM v_proyecto_materiales_detalle WHERE proyecto_id = ?');
                    $stmtMat->execute([$proyecto_id]);
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
                            'proyecto' => $contexto,
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
        if ($proyecto_id && !$guardrail_activado && !empty($respuesta)) {
            try {
                $pdo->prepare('INSERT INTO ia_respuestas_cache (proyecto_id, pregunta_normalizada, pregunta_original, respuesta) VALUES (?, ?, ?, ?)')
                    ->execute([$proyecto_id, $pregunta_lower, $pregunta, $respuesta]);
            } catch (Exception $e) {
                error_log('IA cache insert error: ' . $e->getMessage());
            }
        }
    }

    // Registrar interacción (SP) si hay sesión
    if ($sesion_id) {
        try {
            $stmtLog = $pdo->prepare('CALL sp_registrar_interaccion_ia(?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $costo = 0.0; // estimado
            $stmtLog->execute([$sesion_id, $proyecto_id, $pregunta, $respuesta, $tokens, $tiempo_ms, $modelo, $costo, $guardrail_activado ? 1 : 0]);
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
