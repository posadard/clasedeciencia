<?php
/**
 * API: IA Consulta Ã¢â‚¬â€ 2 instancias (frontend + backend)
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
// config.php reactiva display_errors=1 Ã¢â‚¬â€ lo suprimimos de nuevo aquÃƒÂ­
ini_set('display_errors', 0);

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
    $ultimo_error = '';
    foreach ($modelos as $modelo) {
        if (empty($modelo)) continue;
        $r = groq_call($api_key, $modelo, $messages, $temperature, $max_tokens, $top_p);

        if ($r['errno'] !== 0) {
            $ultimo_error = "curl errno={$r['errno']} modelo={$modelo}";
            error_log("IA groq curl error [{$modelo}]: errno={$r['errno']}");
            break; // Error de red: no hay fallback ÃƒÂºtil
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
            $ultimo_error = "HTTP {$r['http_status']} pero sin choices. Raw: " . substr($r['raw'], 0, 300);
            error_log("IA groq sin choices [{$modelo}]: " . $ultimo_error);
            break;
        }

        // Exponer el error de la API si es 4xx/5xx para diagnÃƒÂ³stico
        $groq_error = json_decode($r['raw'], true);
        $groq_msg   = $groq_error['error']['message'] ?? $r['raw'];
        $ultimo_error = "HTTP {$r['http_status']} [{$modelo}]: " . substr($groq_msg, 0, 200);
        error_log("IA groq error [{$modelo}]: HTTP {$r['http_status']} Ã¢â‚¬â€ {$groq_msg}");

        // Solo fallback en rate limit o indisponibilidad
        if (!in_array($r['http_status'], [429, 503])) {
            break;
        }
        error_log("IA groq fallback [{$modelo}] Ã¢â€ â€™ siguiente modelo");
    }

    // Guardar ÃƒÂºltimo error en variable global para poder retornarlo
    $GLOBALS['_ia_groq_ultimo_error'] = $ultimo_error;
    return null;
}

// ---------------------------------------------------------------
// Buscador de sugerencias por palabras clave
// Devuelve hasta $limit recursos relevantes de la BD
// ---------------------------------------------------------------
function buscar_sugerencias(PDO $pdo, string $pregunta, int $limit = 4): array {
    $palabras = array_filter(
        preg_split('/\s+/', mb_strtolower($pregunta, 'UTF-8')),
        fn($p) => mb_strlen($p) >= 4
    );
    if (empty($palabras)) return [];

    $sugerencias = [];
    // Usar solo las primeras 5 palabras significativas para no sobrecargar la consulta
    $palabras = array_slice($palabras, 0, 5);

    $condiciones = implode(' OR ', array_fill(0, count($palabras),
        '(LOWER(c.nombre) LIKE ? OR LOWER(c.resumen) LIKE ?)'));
    $params = [];
    foreach ($palabras as $p) { $params[] = "%$p%"; $params[] = "%$p%"; }

    try {
        // Clases
        $stmt = $pdo->prepare(
            "SELECT 'clase' AS tipo, c.nombre, c.slug, c.resumen AS desc_corta
             FROM clases c
             WHERE c.activo = 1 AND ({$condiciones})
             ORDER BY c.featured DESC LIMIT ?"
        );
        $stmt->execute(array_merge($params, [$limit]));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sugerencias[] = [
                'tipo'  => 'clase',
                'icono' => 'ðŸ”¬',
                'label' => 'Clase',
                'titulo' => $r['nombre'],
                'url'   => '/' . $r['slug'],
                'desc'  => mb_strimwidth($r['desc_corta'] ?? '', 0, 80, 'â€¦'),
            ];
        }

        // Kits
        $cond_kit = implode(' OR ', array_fill(0, count($palabras),
            '(LOWER(k.nombre) LIKE ? OR LOWER(k.descripcion) LIKE ?)'));
        $params_kit = [];
        foreach ($palabras as $p) { $params_kit[] = "%$p%"; $params_kit[] = "%$p%"; }
        $stmt = $pdo->prepare(
            "SELECT 'kit' AS tipo, k.nombre, k.slug, k.descripcion AS desc_corta
             FROM kits k
             WHERE k.activo = 1 AND ({$cond_kit})
             LIMIT ?"
        );
        $stmt->execute(array_merge($params_kit, [$limit]));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sugerencias[] = [
                'tipo'  => 'kit',
                'icono' => 'ðŸ§°',
                'label' => 'Kit',
                'titulo' => $r['nombre'],
                'url'   => '/kit/' . $r['slug'],
                'desc'  => mb_strimwidth($r['desc_corta'] ?? '', 0, 80, 'â€¦'),
            ];
        }

        // Componentes (kit_items)
        $cond_comp = implode(' OR ', array_fill(0, count($palabras),
            '(LOWER(ki.nombre) LIKE ? OR LOWER(ki.descripcion_corta) LIKE ?)'));
        $params_comp = [];
        foreach ($palabras as $p) { $params_comp[] = "%$p%"; $params_comp[] = "%$p%"; }
        $stmt = $pdo->prepare(
            "SELECT ki.nombre, ki.slug, ki.descripcion_corta AS desc_corta
             FROM kit_items ki
             WHERE ki.activo = 1 AND ki.slug IS NOT NULL AND ({$cond_comp})
             LIMIT ?"
        );
        $stmt->execute(array_merge($params_comp, [$limit]));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sugerencias[] = [
                'tipo'  => 'componente',
                'icono' => 'âš—ï¸',
                'label' => 'Componente',
                'titulo' => $r['nombre'],
                'url'   => '/componente/' . $r['slug'],
                'desc'  => mb_strimwidth($r['desc_corta'] ?? '', 0, 80, 'â€¦'),
            ];
        }

        // Manuales
        $cond_man = implode(' OR ', array_fill(0, count($palabras),
            '(LOWER(m.titulo) LIKE ? OR LOWER(m.descripcion) LIKE ?)'));
        $params_man = [];
        foreach ($palabras as $p) { $params_man[] = "%$p%"; $params_man[] = "%$p%"; }
        $stmt = $pdo->prepare(
            "SELECT m.titulo AS nombre, m.slug, m.descripcion AS desc_corta
             FROM kit_manuals m
             WHERE m.status = 'published' AND ({$cond_man})
             LIMIT ?"
        );
        $stmt->execute(array_merge($params_man, [$limit]));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sugerencias[] = [
                'tipo'  => 'manual',
                'icono' => 'ðŸ“–',
                'label' => 'Manual',
                'titulo' => $r['nombre'],
                'url'   => '/manual/' . $r['slug'],
                'desc'  => mb_strimwidth($r['desc_corta'] ?? '', 0, 80, 'â€¦'),
            ];
        }

    } catch (Exception $e) {
        error_log('IA sugerencias error: ' . $e->getMessage());
    }

    // Eliminar duplicados por URL y limitar total
    $vistas = [];
    $result  = [];
    foreach ($sugerencias as $s) {
        if (!isset($vistas[$s['url']])) {
            $vistas[$s['url']] = true;
            $result[] = $s;
        }
        if (count($result) >= $limit) break;
    }
    return $result;
}

// ---------------------------------------------------------------
// Context Builders
// ---------------------------------------------------------------

/**
 * Construye el bloque de contexto para la instancia FRONTEND.
 * Combina: clase, ÃƒÂ¡reas, competencias, kit, manual, guÃƒÂ­a y prompt pedagÃƒÂ³gico.
 */
/**
 * Busca en el catalogo real (clases, kits, componentes) por palabras clave
 * y devuelve un bloque de contexto con los resultados para inyectar a la IA.
 */
function buscar_catalogo_por_pregunta(PDO $pdo, string $termino, int $limite = 5): string {
    $termino = trim($termino);
    if (empty($termino)) return '';

    // Stopwords en espanol � no aportan valor como terminos de busqueda
    $stopwords = ['quiero', 'hacer', 'para', 'como', 'sobre', 'algo', 'tener', 'busco',
                  'necesito', 'dame', 'muestra', 'proyecto', 'clase', 'trabajo', 'ayuda',
                  'hola', 'buenas', 'quisiera', 'podria', 'puedo', 'cual', 'cuales', 'que',
                  'una', 'uno', 'los', 'las', 'del', 'con', 'por', 'pero', 'mas'];

    // Normalizar y filtrar palabras utiles (min 4 chars)
    $palabras_raw = preg_split('/\s+/', mb_strtolower($termino));
    $palabras = array_values(array_filter($palabras_raw, function($p) use ($stopwords) {
        return mb_strlen($p) >= 4 && !in_array($p, $stopwords, true);
    }));

    // Si no hay palabras utiles, usar termino completo como ultima opcion
    if (empty($palabras)) {
        $palabras = [mb_substr($termino, 0, 30)];
    }

    $lineas = [];

    try {
        // --- CLASES ---
        $where_parts = array_map(fn($p) => "(c.nombre LIKE ? OR c.resumen LIKE ?)", $palabras);
        $params = [];
        foreach ($palabras as $p) { $params[] = "%$p%"; $params[] = "%$p%"; }
        $sql = "SELECT c.nombre, c.slug, c.resumen, c.ciclo, c.dificultad, c.duracion_minutos
                FROM clases c WHERE c.activo = 1 AND (" . implode(' OR ', $where_parts) . ")
                ORDER BY c.destacado DESC, c.orden_popularidad ASC LIMIT ?";
        $params[] = $limite;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $desc = $c['resumen'] ? mb_substr($c['resumen'], 0, 120) : '';
            $lineas[] = "[Clase] {$c['nombre']} | Ciclo {$c['ciclo']} | {$c['dificultad']} | {$c['duracion_minutos']} min | URL: /{$c['slug']}\n    Descripcion: {$desc}";
        }

        // --- KITS ---
        $where_parts2 = array_map(fn($p) => "(k.nombre LIKE ? OR k.resumen LIKE ?)", $palabras);
        $params2 = [];
        foreach ($palabras as $p) { $params2[] = "%$p%"; $params2[] = "%$p%"; }
        $sql2 = "SELECT k.nombre, k.slug, k.codigo, k.resumen
                 FROM kits k WHERE k.activo = 1 AND (" . implode(' OR ', $where_parts2) . ")
                 ORDER BY k.id ASC LIMIT ?";
        $params2[] = 3;
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute($params2);
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $k) {
            $desc = $k['resumen'] ? mb_substr($k['resumen'], 0, 100) : '';
            $lineas[] = "[Kit] {$k['nombre']} | Codigo: {$k['codigo']} | URL: /{$k['slug']}\n    Descripcion: {$desc}";
        }

        // --- COMPONENTES (solo nombre_comun — descripcion_html es HTML no apto para LIKE) ---
        $where_parts3 = array_map(fn($p) => "ki.nombre_comun LIKE ?", $palabras);
        $params3 = [];
        foreach ($palabras as $p) { $params3[] = "%$p%"; }
        $sql3 = "SELECT ki.nombre_comun, ki.slug, ki.sku
                 FROM kit_items ki WHERE ki.activo = 1 AND (" . implode(' OR ', $where_parts3) . ")
                 ORDER BY ki.id ASC LIMIT ?";
        $params3[] = 3;
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute($params3);
        foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $ki) {
            $lineas[] = "[Componente] {$ki['nombre_comun']} | SKU: {$ki['sku']} | URL: /{$ki['slug']}";
        }
    } catch (Exception $e) {
        error_log('IA buscar_catalogo error: ' . $e->getMessage());
        return '';
    }

    if (empty($lineas)) {
        // Obtener conteo total para dar contexto real de escala del catalogo
        $total_clases = 0;
        $total_kits   = 0;
        try {
            $total_clases = (int)$pdo->query('SELECT COUNT(*) FROM clases WHERE activo = 1')->fetchColumn();
            $total_kits   = (int)$pdo->query('SELECT COUNT(*) FROM kits WHERE activo = 1')->fetchColumn();
        } catch (Exception $e) {}
        $termino_safe = htmlspecialchars($termino, ENT_QUOTES, 'UTF-8');
        return "=== CATALOGO: SIN COINCIDENCIAS ===\nBusqueda realizada: '{$termino_safe}'\nResultado: No existe ninguna clase, kit ni componente sobre este tema en el catalogo actual ({$total_clases} clases y {$total_kits} kits disponibles).\nCOMPORTAMIENTO REQUERIDO: Di al usuario exactamente esto (adaptado naturalmente): \"Aun no tenemos una clase o kit sobre [tema], pero podemos seguir aprendiendo juntos sobre ello.\" Luego continua la conversacion educativa sobre el tema sin inventar productos.";
    }

    return "=== CATALOGO DISPONIBLE (resultados reales — usa SOLO estos) ===\nREGLA: Menciona UNICAMENTE los productos listados aqui. NO inventes nombres de clases, kits, codigos ni materiales que no aparezcan en esta lista.\n" . implode("\n", $lineas);
}

function build_context_frontend(PDO $pdo, ?int $clase_id, ?int $kit_id = null, ?int $componente_id = null, ?int $manual_id = null, string $pagina = 'inicio', string $termino_busqueda = ''): string {
    $bloques = [];

    try {
        if ($clase_id) {
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
                . "DuraciÃƒÂ³n: {$clase['duracion_minutos']} min\n"
                . "Resumen: {$clase['resumen']}\n"
                . "Objetivo de aprendizaje: {$clase['objetivo_aprendizaje']}\n"
                . "ÃƒÂreas del conocimiento: {$areas}\n"
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

        // 4. GuÃƒÂ­a de la clase
        $stmt = $pdo->prepare('SELECT pasos, explicacion_cientifica FROM guias WHERE clase_id = ? LIMIT 1');
        $stmt->execute([$clase_id]);
        $guia = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($guia && $guia['pasos']) {
            $pasos  = json_decode($guia['pasos'], true) ?: [];
            $lineas = array_map(fn($p, $i) => "  " . ($i + 1) . ". {$p['titulo']}: {$p['detalle']}", $pasos, array_keys($pasos));
            $bloques[] = "=== GUÃƒÂA PASO A PASO ===\n" . implode("\n", $lineas);
            if (!empty($guia['explicacion_cientifica'])) {
                $bloques[] = "=== EXPLICACIÃƒâ€œN CIENTÃƒÂFICA ===\n{$guia['explicacion_cientifica']}";
            }
        }

        // 5. Prompt pedagÃƒÂ³gico (prompts_clase)
        $stmt = $pdo->prepare('SELECT prompt_contexto, enfoque_pedagogico, conocimientos_previos, preguntas_frecuentes FROM prompts_clase WHERE clase_id = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$clase_id]);
        $pc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pc) {
            $cp = $pc['conocimientos_previos'] ? implode(', ', json_decode($pc['conocimientos_previos'], true) ?: []) : '';
            $pf = $pc['preguntas_frecuentes'] ? implode(' / ', json_decode($pc['preguntas_frecuentes'], true) ?: []) : '';
            $bloques[] = "=== ORIENTACIONES PEDAGÃƒâ€œGICAS ===\n"
                . ($pc['prompt_contexto'] ? "Contexto: {$pc['prompt_contexto']}\n" : '')
                . ($pc['enfoque_pedagogico'] ? "Enfoque: {$pc['enfoque_pedagogico']}\n" : '')
                . ($cp ? "Conocimientos previos: {$cp}\n" : '')
                . ($pf ? "Preguntas frecuentes: {$pf}" : '');
        }

        } elseif ($pagina === 'kit' && $kit_id) {
            // --- KIT PAGE CONTEXT ---
            $stmt = $pdo->prepare('SELECT nombre, codigo, version, resumen, seguridad, time_minutes, dificultad_ensamble FROM kits WHERE id = ? AND activo = 1 LIMIT 1');
            $stmt->execute([$kit_id]);
            $kit = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($kit) {
                $seg      = $kit['seguridad'] ? (json_decode($kit['seguridad'], true) ?: []) : [];
                $seg_nota = $seg['notas'] ?? '';
                $bloques[] = "=== KIT ACTUAL ===\n"
                    . "Nombre: {$kit['nombre']}\n"
                    . "Codigo: {$kit['codigo']} | Version: {$kit['version']}\n"
                    . ($kit['time_minutes']        ? "Tiempo de armado: {$kit['time_minutes']} min\n" : '')
                    . ($kit['dificultad_ensamble'] ? "Dificultad: {$kit['dificultad_ensamble']}\n" : '')
                    . "Resumen: {$kit['resumen']}\n"
                    . ($seg_nota ? "Seguridad: {$seg_nota}" : '');
            }
            $stmt = $pdo->prepare(
                'SELECT ki.nombre_comun, kc.cantidad, ki.unidad, kc.notas
                 FROM kit_componentes kc
                 JOIN kit_items ki ON ki.id = kc.item_id
                 WHERE kc.kit_id = ?
                 ORDER BY kc.sort_order'
            );
            $stmt->execute([$kit_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($items) {
                $lineas = array_map(function ($it) {
                    $nota = $it['notas'] ? " ({$it['notas']})" : '';
                    $u    = $it['unidad'] ? " {$it['unidad']}" : '';
                    return "  - {$it['nombre_comun']} x{$it['cantidad']}{$u}{$nota}";
                }, $items);
                $bloques[] = "=== COMPONENTES DEL KIT ===\n" . implode("\n", $lineas);
            }

        } elseif ($pagina === 'componente' && $componente_id) {
            // --- COMPONENTE PAGE CONTEXT ---
            $stmt = $pdo->prepare(
                'SELECT ki.nombre_comun, ki.sku, ki.descripcion_html, ki.advertencias_seguridad, ki.unidad,
                        cat.nombre AS categoria
                 FROM kit_items ki
                 LEFT JOIN categorias_items cat ON cat.id = ki.categoria_id
                 WHERE ki.id = ? LIMIT 1'
            );
            $stmt->execute([$componente_id]);
            $comp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($comp) {
                $adv      = $comp['advertencias_seguridad'] ? (json_decode($comp['advertencias_seguridad'], true) ?: []) : [];
                $adv_nota = $adv['notas'] ?? '';
                $desc     = $comp['descripcion_html'] ? mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($comp['descripcion_html']))), 0, 500) : '';
                $bloques[] = "=== COMPONENTE ACTUAL ===\n"
                    . "Nombre: {$comp['nombre_comun']}\n"
                    . "SKU: {$comp['sku']} | Categoria: {$comp['categoria']} | Unidad: {$comp['unidad']}\n"
                    . ($adv_nota ? "Seguridad: {$adv_nota}\n" : '')
                    . ($desc     ? "Descripcion: {$desc}" : '');
            }

        } elseif ($pagina === 'manual' && $manual_id) {
            // --- MANUAL PAGE CONTEXT ---
            $stmt = $pdo->prepare(
                'SELECT km.tipo_manual, km.ambito, km.resumen, km.time_minutes, km.dificultad_ensamble,
                        km.pasos_json, km.seguridad_json,
                        k.nombre AS kit_nombre, ki.nombre_comun AS componente_nombre
                 FROM kit_manuals km
                 LEFT JOIN kits k ON k.id = km.kit_id
                 LEFT JOIN kit_items ki ON ki.id = km.item_id
                 WHERE km.id = ? AND km.status = \'published\' LIMIT 1'
            );
            $stmt->execute([$manual_id]);
            $manual_ctx = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($manual_ctx) {
                $entidad = $manual_ctx['kit_nombre'] ?? $manual_ctx['componente_nombre'] ?? 'N/A';
                $bloques[] = "=== MANUAL ACTUAL ===\n"
                    . "Tipo: {$manual_ctx['tipo_manual']} | Ambito: {$manual_ctx['ambito']}\n"
                    . "Para: {$entidad}\n"
                    . ($manual_ctx['resumen']      ? "Resumen: {$manual_ctx['resumen']}\n" : '')
                    . ($manual_ctx['time_minutes'] ? "Tiempo: {$manual_ctx['time_minutes']} min | Dificultad: {$manual_ctx['dificultad_ensamble']}\n" : '');
                if ($manual_ctx['pasos_json']) {
                    $pasos  = json_decode($manual_ctx['pasos_json'], true) ?: [];
                    $lineas = array_map(fn($p) => "  {$p['orden']}. {$p['titulo']}", $pasos);
                    $bloques[] = "=== PASOS DEL MANUAL ===\n" . implode("\n", $lineas);
                }
                if ($manual_ctx['seguridad_json']) {
                    $seg   = json_decode($manual_ctx['seguridad_json'], true) ?: [];
                    $notas = array_map(fn($n) => "  - {$n['nota']}", $seg['notas'] ?? []);
                    if ($notas) $bloques[] = "=== SEGURIDAD ===\n" . implode("\n", $notas);
                }
            }

        } else {
            // --- LISTING PAGE: busqueda real en catalogo + orientacion ---
            $listado_hints = [
                'inicio'      => 'El usuario esta en la pagina de inicio del sitio.',
                'catalogo'    => 'El usuario esta explorando el catalogo de clases.',
                'kits'        => 'El usuario esta viendo el catalogo de kits de ciencia.',
                'componentes' => 'El usuario esta viendo la lista de componentes/materiales.',
                'manuales'    => 'El usuario esta explorando los manuales disponibles.',
            ];
            if (isset($listado_hints[$pagina])) {
                $bloques[] = "=== CONTEXTO DE NAVEGACION ===\n" . $listado_hints[$pagina];
            }
            // Inyectar resultados reales del catalogo si hay termino de busqueda
            if (!empty($termino_busqueda)) {
                $catalogo_ctx = buscar_catalogo_por_pregunta($pdo, $termino_busqueda);
                if ($catalogo_ctx) $bloques[] = $catalogo_ctx;
            }
        }

    } catch (Exception $e) {
        error_log('IA context frontend error: ' . $e->getMessage());
    }

    return implode("\n\n", array_filter($bloques));
}

/**
 * Construye el bloque de contexto para la instancia BACKEND.
 * Datos varÃƒÂ­an segÃƒÂºn la pÃƒÂ¡gina admin activa.
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
                        "  [{$r['id']}] {$r['nombre_comun']} ({$r['sku']}) | CategorÃƒÂ­a: {$r['categoria']}", $rows));
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
                $bloques[] = "=== ÃƒÅ¡LTIMAS ENTREGAS (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  [{$r['id']}] {$r['institucion_educativa']} | Contrato: {$r['contrato']} | {$r['departamento']} | {$r['fecha']} | acta:" . ($r['acta_pdf'] ? 'sÃƒÂ­' : 'no'), $rows));
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
                $bloques[] = "=== DASHBOARD IA (ÃƒÂºltimos 14 dÃƒÂ­as) ===\n"
                    . implode("\n", array_map(fn($r) =>
                        "  {$r['fecha']}: sesiones={$r['sesiones_unicas']} consultas={$r['total_consultas']} errores={$r['total_errores']} guardrails={$r['alertas_seguridad']} tokens={$r['tokens_totales']} costo_USD={$r['costo_total']}", $rows));
                $estados = $pdo->query("SELECT instancia, valor FROM configuracion_ia WHERE clave = 'ia_activa'")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($estados as $e) {
                    $bloques[] = "IA {$e['instancia']}: " . ($e['valor'] == '1' ? 'Ã¢Å“â€¦ activa' : 'Ã¢ÂÅ’ inactiva');
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

        // Contexto profundo de entidad especÃƒÂ­fica
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
                            . "Nombre: {$k['nombre']} | CÃƒÂ³digo: {$k['codigo']} | Clase: {$k['clase_nombre']}";
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

    // ParÃƒÂ¡metros comunes
    $instancia       = ($data['instancia'] ?? 'frontend') === 'backend' ? 'backend' : 'frontend';
    $pregunta        = trim($data['pregunta'] ?? '');
    // Historial de conversaciÃ³n: array de {role, content} enviado por el cliente
    $historial_raw = isset($data['historial']) && is_array($data['historial']) ? $data['historial'] : [];
    $historial = [];
    $roles_validos = ['user', 'assistant'];
    foreach (array_slice($historial_raw, -12) as $item) {
        if (!is_array($item)) continue;
        $role    = $item['role'] ?? '';
        $content = isset($item['content']) ? mb_substr(trim((string)$item['content']), 0, 800) : '';
        if (!in_array($role, $roles_validos, true) || $content === '') continue;
        $historial[] = ['role' => $role, 'content' => $content];
    }
    if ($pregunta === '') json_fail('Pregunta vacÃƒÂ­a.');
    if (mb_strlen($pregunta) > 2000) json_fail('Pregunta demasiado larga.');

    // Frontend: proteger el endpoint backend de acceso externo
    if ($instancia === 'backend') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            json_fail('No autorizado.', ['code' => 403]);
        }
    }

    // ParÃƒÂ¡metros por instancia
    $clase_id        = $instancia === 'frontend' ? (isset($data['clase_id'])        ? (int)$data['clase_id']        : null) : null;
    $kit_id          = $instancia === 'frontend' ? (isset($data['kit_id'])          ? (int)$data['kit_id']          : null) : null;
    $componente_id   = $instancia === 'frontend' ? (isset($data['componente_id'])   ? (int)$data['componente_id']   : null) : null;
    $manual_id       = $instancia === 'frontend' ? (isset($data['manual_id'])       ? (int)$data['manual_id']       : null) : null;
    $pagina          = $instancia === 'frontend' ? trim($data['pagina'] ?? 'inicio') : '';
    $tema            = $instancia === 'frontend' ? trim($data['tema'] ?? '') : '';
    $contexto_pagina = $instancia === 'backend'  ? trim($data['contexto_pagina'] ?? 'dashboard') : '';
    $entidad_tipo    = $instancia === 'backend'  ? trim($data['entidad_tipo'] ?? '') : null;
    $entidad_id      = $instancia === 'backend'  ? (isset($data['entidad_id']) ? (int)$data['entidad_id'] : null) : null;

    // ---------------------------------------------------------------
    // Cargar configuraciÃƒÂ³n de esta instancia
    // ---------------------------------------------------------------
    $cfg = [];

    // Global (pagina IS NULL) — valores base de la instancia
    $stmt = $pdo->prepare('SELECT clave, valor FROM configuracion_ia WHERE instancia = ? AND pagina IS NULL');
    $stmt->execute([$instancia]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cfg[$row['clave']] = $row['valor'];
    }

    // Override de pagina (solo frontend) — sobreescribe solo las claves definidas para esta pagina
    if ($instancia === 'frontend' && !empty($pagina)) {
        $stmt = $pdo->prepare('SELECT clave, valor FROM configuracion_ia WHERE instancia = ? AND pagina = ?');
        $stmt->execute([$instancia, $pagina]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cfg[$row['clave']] = $row['valor'];
        }
    }

    $ia_activa = (($cfg['ia_activa'] ?? '0') === '1');
    if (!$ia_activa) json_fail('IA desactivada por configuraciÃƒÂ³n.');

    $api_key     = $cfg['groq_api_key'] ?? '';
    if (empty($api_key)) json_fail('Ã¢Å¡Â Ã¯Â¸Â IA no configurada. Falta API Key.');

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
    $mensaje_guardrail   = $cfg['mensaje_guardrail'] ?? 'Ã¢Å¡Â Ã¯Â¸Â Consulta con tu profesor.';

    // ---------------------------------------------------------------
    // SesiÃƒÂ³n anÃƒÂ³nima (solo frontend)
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
            error_log('IA sesiÃƒÂ³n error: ' . $e->getMessage());
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
    // CachÃƒÂ© (solo frontend, solo cuando no hay guardrail)
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
            // Construir contexto segÃƒÂºn instancia
            if ($instancia === 'frontend') {
                // Combinar pregunta + tema del historial para busqueda enriquecida en catalogo
                $termino_busqueda = trim($pregunta . ' ' . $tema);
                $contexto_texto = build_context_frontend($pdo, $clase_id, $kit_id, $componente_id, $manual_id, $pagina, $termino_busqueda);
            } else {
                $contexto_texto = build_context_backend($pdo, $contexto_pagina, $entidad_tipo, $entidad_id);
            }

            $system_content = $prompt_base;
            if (!empty($contexto_texto)) {
                $system_content .= "\n\n" . $contexto_texto;
            }

            // Regla de honestidad sobre el catalogo (frontend) — va SIEMPRE en el prompt del sistema
            // para que tenga maxima autoridad sobre el conocimiento pre-entrenado del modelo
            if ($instancia === 'frontend') {
                $system_content .= "\n\nREGLA DE CATALOGO (obligatoria):\n- Si el contexto incluye '=== CATALOGO DISPONIBLE ===' menciona solo esos productos reales.\n- Si el contexto incluye '=== CATALOGO: SIN COINCIDENCIAS ===' NO inventes clases, kits ni materiales. Di honestamente al usuario que aun no tienes un proyecto o kit sobre ese tema especifico, pero ofrece seguir conversando sobre el tema de forma educativa. Ejemplo: 'Aun no tenemos una clase o kit sobre [tema], pero puedo contarte mas sobre ello si te interesa seguir explorando.'";
            }

            // Chips de respuesta rapida: cuando la IA necesita info del usuario,
            // puede incluir "Opciones: A|B|C" al final para mostrar chips de respuesta.
            if ($instancia === 'frontend') {
                $system_content .= "\n\nCuando necesites informacion del usuario para responder mejor, agrega al FINAL de tu mensaje (en una linea separada) el bloque: Opciones: opcion1|opcion2|opcion3 (max 4 opciones, menos de 40 caracteres cada una, en espanol). Omite este bloque cuando das informacion directa sin necesitar aclaracion del usuario.";
            }

            // system + historial previo (mÃ¡x 12 msgs) + pregunta actual
            $messages = array_merge(
                [['role' => 'system', 'content' => $system_content]],
                $historial,
                [['role' => 'user', 'content' => $pregunta]]
            );

            $resultado = groq_con_fallback($api_key, $modelos, $messages, $temperature, $max_tokens, $top_p);

            if ($resultado) {
                $respuesta    = $resultado['respuesta'];
                $modelo_usado = $resultado['modelo_usado'];
                $tokens       = $resultado['tokens'];
                $tiempo_ms    = $resultado['tiempo_ms'];
            } else {
                $groq_detalle = $GLOBALS['_ia_groq_ultimo_error'] ?? 'sin detalle';
                $respuesta = 'Ã¢ÂÅ’ Error al consultar la IA. Detalle: ' . $groq_detalle;
                error_log('IA groq todos los modelos fallaron: ' . $groq_detalle);
            }
        }

        // Guardar en cachÃƒÂ© (solo frontend, sin guardrail, con respuesta vÃƒÂ¡lida)
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
            $cached ? 'Respuesta desde cachÃƒÂ©' : ($guardrail_activado ? "Guardrail: {$guardrail_palabra}" : 'Consulta Groq'),
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
    exit;

} catch (Throwable $e) {
    error_log('IA consulta fatal: ' . $e->getMessage());
    ob_end_clean();
    json_fail('Error interno del servidor.');
}
