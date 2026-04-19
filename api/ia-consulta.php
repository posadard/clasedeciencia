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

function normalize_backend_chat_response(string $text, int $max_content_lines = 10): string {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    // Evitar bloques markdown/codigo en el panel lateral.
    $text = preg_replace('/```[\s\S]*?```/u', '', $text) ?? $text;

    $lines = preg_split('/\n/u', $text) ?: [];
    $out = [];
    $content_lines = 0;
    $prev_empty = false;

    foreach ($lines as $line_raw) {
        $line = trim((string)$line_raw);

        if ($line === '') {
            if (!$prev_empty && !empty($out)) {
                $out[] = '';
                $prev_empty = true;
            }
            continue;
        }
        $prev_empty = false;

        // Quitar encabezados markdown y estilos simples.
        $line = preg_replace('/^#{1,6}\s*/u', '', $line) ?? $line;
        $line = preg_replace('/\*\*(.*?)\*\*/u', '$1', $line) ?? $line;
        $line = preg_replace('/\*(.*?)\*/u', '$1', $line) ?? $line;
        $line = preg_replace('/`([^`]+)`/u', '$1', $line) ?? $line;

        // Eliminar separadores de tabla markdown.
        if (preg_match('/^\s*\|?\s*:?[-]{3,}:?\s*(\|\s*:?[-]{3,}:?\s*)+\|?\s*$/u', $line)) {
            continue;
        }

        // Convertir filas de tabla a texto lineal.
        if (substr_count($line, '|') >= 2) {
            $cells = array_values(array_filter(array_map('trim', explode('|', $line)), static fn($c) => $c !== ''));
            if (count($cells) > 0) {
                $line = implode(' - ', $cells);
            }
        }

        // Si es muy larga, recortar para preservar legibilidad en panel.
        if (mb_strlen($line) > 220) {
            $line = mb_substr($line, 0, 220) . '...';
        }

        $out[] = $line;
        $content_lines++;
        if ($content_lines >= $max_content_lines) {
            break;
        }
    }

    $normalized = trim(implode("\n", $out));
    return $normalized !== '' ? $normalized : 'No tengo suficientes datos para responder con claridad en este momento.';
}

function detect_backend_intent(string $pregunta): ?string {
    $q = mb_strtolower(trim($pregunta));
    if ($q === '') return null;

    $has_manual = preg_match('/\bmanual(?:es)?\b/u', $q) === 1;
    $has_pertenece = preg_match('/\b(pertenece|pertenecen|a que pertenece|de que es|de cual)\b/u', $q) === 1;
    $has_completa = preg_match('/\b(mas completa|completitud|completa)\b/u', $q) === 1;
    $has_clase = preg_match('/\bclase(?:s)?\b/u', $q) === 1;
    $has_kit_word = preg_match('/\bkit(?:s)?\b/u', $q) === 1;
    $has_nombre = preg_match('/\b(nombre|nombres|cual|cuales|dime|listar|lista)\b/u', $q) === 1;
    $has_contrato = preg_match('/\bcontrato(?:s)?\b/u', $q) === 1;
    $has_entrega = preg_match('/\bentrega(?:s)?\b/u', $q) === 1;
    $has_lote = preg_match('/\blote(?:s)?\b/u', $q) === 1;
    $has_riesgo = preg_match('/\b(riesgo|riesgos|alerta|alertas|critico|criticos)\b/u', $q) === 1;
    $has_vencer = preg_match('/\b(vencer|vence|vencen|vencido|vencidos|vencimiento|proximo)\b/u', $q) === 1;
    $has_acta = preg_match('/\bacta(?:s)?\b/u', $q) === 1;
    $has_sin = preg_match('/\b(sin|faltan|falta|faltante|incompleto|incompleta)\b/u', $q) === 1;
    $has_pendiente = preg_match('/\b(pendiente|pendientes|atrasad|reprogramad|abiert)\w*\b/u', $q) === 1;
    $has_quiebre = preg_match('/\b(quiebre|agot|stock|disponible)\w*\b/u', $q) === 1;
    $has_top = preg_match('/\b(mas|más|mayor|top|numero\s*de|n[uú]mero\s*de)\b/u', $q) === 1;

    if ($has_clase && $has_completa) {
        return 'clase_mas_completa';
    }
    if ($has_clase && $has_kit_word && $has_top) {
        return 'clase_mas_kits';
    }
    if ($has_manual && $has_pertenece) {
        return 'manual_pertenencia';
    }
    if ($has_manual && $has_nombre) {
        return 'manuales_nombres';
    }
    if ($has_manual && preg_match('/\b(hay|existe|cuantos|cuantas|tenemos|tienen)\b/u', $q) === 1) {
        return 'manuales_estado';
    }
    if ($has_contrato && $has_vencer) {
        return 'contratos_vencimiento';
    }
    if ($has_entrega && $has_acta && $has_sin) {
        return 'entregas_sin_acta';
    }
    if ($has_entrega && $has_pendiente) {
        return 'entregas_pendientes';
    }
    if ($has_lote && $has_quiebre) {
        return 'lotes_riesgo_quiebre';
    }
    if ($has_riesgo && ($has_contrato || $has_entrega || $has_lote || preg_match('/\boperativo\b/u', $q) === 1)) {
        return 'riesgo_operativo';
    }

    return null;
}

function ia_admin_entity_url(string $tipo, int $id): ?string {
    if ($id <= 0) return null;
    switch ($tipo) {
        case 'clase':
            return '/admin/clases/edit.php?id=' . $id;
        case 'kit':
            return '/admin/kits/edit.php?id=' . $id;
        case 'componente':
            return '/admin/componentes/edit.php?id=' . $id;
        case 'manual':
            return '/admin/kits/manuals/edit.php?id=' . $id;
        case 'contrato':
            return '/admin/contratos/edit.php?id=' . $id;
        case 'entrega':
            return '/admin/entregas/edit.php?id=' . $id;
        case 'lote':
            return '/admin/lotes/edit.php?id=' . $id;
        default:
            return null;
    }
}

function ia_build_link(string $label, string $tipo, int $id): ?array {
    $url = ia_admin_entity_url($tipo, $id);
    if ($url === null) return null;
    return [
        'label' => $label,
        'url' => $url,
        'entity_type' => $tipo,
        'entity_id' => $id,
    ];
}

function ia_unique_links(array $links, int $max = 8): array {
    $out = [];
    $seen = [];
    foreach ($links as $link) {
        if (!is_array($link)) continue;
        $url = (string)($link['url'] ?? '');
        $label = trim((string)($link['label'] ?? ''));
        if ($url === '' || $label === '') continue;
        if (strpos($url, '/') !== 0 || strpos($url, '//') === 0 || stripos($url, 'javascript:') === 0) continue;
        if (isset($seen[$url])) continue;
        $seen[$url] = true;
        $out[] = [
            'label' => $label,
            'url' => $url,
            'entity_type' => (string)($link['entity_type'] ?? ''),
            'entity_id' => (int)($link['entity_id'] ?? 0),
        ];
        if (count($out) >= $max) break;
    }
    return $out;
}

function ia_extract_terms(string $text, int $max = 6): array {
    $clean = mb_strtolower(trim($text));
    if ($clean === '') return [];
    $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean) ?? $clean;
    $parts = preg_split('/\s+/u', $clean) ?: [];
    $stop = [
        'para', 'pero', 'esta', 'este', 'estos', 'estas', 'donde', 'como', 'cual', 'cuales', 'quiero',
        'tengo', 'tiene', 'tienen', 'sobre', 'desde', 'hasta', 'porque', 'cuando', 'cuanto', 'cuantos',
        'cuantas', 'solo', 'con', 'sin', 'los', 'las', 'del', 'que', 'una', 'uno', 'unos', 'unas',
        'admin', 'chat', 'ia', 'mas', 'más'
    ];
    $stopMap = array_fill_keys($stop, true);
    $terms = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '' || mb_strlen($p) < 3) continue;
        if (isset($stopMap[$p])) continue;
        $terms[$p] = true;
        if (count($terms) >= $max) break;
    }
    return array_keys($terms);
}

function ia_base_module_links(string $contexto_pagina): array {
    $map = [
        'contratos' => [['label' => 'Abrir módulo de contratos', 'url' => '/admin/contratos/index.php', 'entity_type' => 'contrato', 'entity_id' => 0]],
        'entregas' => [['label' => 'Abrir módulo de entregas', 'url' => '/admin/entregas/index.php', 'entity_type' => 'entrega', 'entity_id' => 0]],
        'lotes' => [['label' => 'Abrir módulo de lotes', 'url' => '/admin/lotes/index.php', 'entity_type' => 'lote', 'entity_id' => 0]],
        'clases' => [['label' => 'Abrir módulo de clases', 'url' => '/admin/clases/index.php', 'entity_type' => 'clase', 'entity_id' => 0]],
        'kits' => [['label' => 'Abrir módulo de kits', 'url' => '/admin/kits/index.php', 'entity_type' => 'kit', 'entity_id' => 0]],
        'componentes' => [['label' => 'Abrir módulo de componentes', 'url' => '/admin/componentes/index.php', 'entity_type' => 'componente', 'entity_id' => 0]],
        'manuales' => [['label' => 'Abrir módulo de manuales', 'url' => '/admin/kits/manuals/index.php', 'entity_type' => 'manual', 'entity_id' => 0]],
        'ia' => [
            ['label' => 'Abrir módulo de clases', 'url' => '/admin/clases/index.php', 'entity_type' => 'clase', 'entity_id' => 0],
            ['label' => 'Abrir módulo de kits', 'url' => '/admin/kits/index.php', 'entity_type' => 'kit', 'entity_id' => 0],
            ['label' => 'Abrir panel IA', 'url' => '/admin/ia/index.php?tab=estado', 'entity_type' => 'ia', 'entity_id' => 0],
        ],
    ];

    $default = [
        ['label' => 'Contratos', 'url' => '/admin/contratos/index.php', 'entity_type' => 'contrato', 'entity_id' => 0],
        ['label' => 'Entregas', 'url' => '/admin/entregas/index.php', 'entity_type' => 'entrega', 'entity_id' => 0],
        ['label' => 'Lotes', 'url' => '/admin/lotes/index.php', 'entity_type' => 'lote', 'entity_id' => 0],
    ];

    return $map[$contexto_pagina] ?? $default;
}

function ia_backend_links_from_entities(string $contexto_pagina, ?string $entidad_tipo, ?int $entidad_id): array {
    $links = ia_base_module_links($contexto_pagina);
    if (!empty($entidad_tipo) && !empty($entidad_id)) {
        $link = ia_build_link('Abrir ' . $entidad_tipo . ' #' . (int)$entidad_id, (string)$entidad_tipo, (int)$entidad_id);
        if ($link) array_unshift($links, $link);
    }
    return ia_unique_links($links, 8);
}

function ia_backend_links_from_search(PDO $pdo, string $contexto_pagina, string $pregunta, string $respuesta): array {
    $links = [];
    $text = trim($pregunta . ' ' . $respuesta);
    $terms = ia_extract_terms($text, 4);
    $term = $terms[0] ?? '';

    if ($term === '') {
        return [];
    }

    $like = '%' . $term . '%';

    try {
        $stmt = $pdo->prepare("SELECT id, nombre FROM clases WHERE LOWER(nombre) LIKE ? ORDER BY activo DESC, nombre ASC LIMIT 2");
        $stmt->execute([$like]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $l = ia_build_link('Clase: ' . (string)$r['nombre'] . ' (ID ' . (int)$r['id'] . ')', 'clase', (int)$r['id']);
            if ($l) $links[] = $l;
        }
    } catch (Exception $e) {
        error_log('IA links clases error: ' . $e->getMessage());
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre FROM kits WHERE LOWER(nombre) LIKE ? ORDER BY activo DESC, nombre ASC LIMIT 2");
        $stmt->execute([$like]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $l = ia_build_link('Kit: ' . (string)$r['nombre'] . ' (ID ' . (int)$r['id'] . ')', 'kit', (int)$r['id']);
            if ($l) $links[] = $l;
        }
    } catch (Exception $e) {
        error_log('IA links kits error: ' . $e->getMessage());
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre_comun FROM kit_items WHERE LOWER(nombre_comun) LIKE ? ORDER BY activo DESC, nombre_comun ASC LIMIT 2");
        $stmt->execute([$like]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $l = ia_build_link('Componente: ' . (string)$r['nombre_comun'] . ' (ID ' . (int)$r['id'] . ')', 'componente', (int)$r['id']);
            if ($l) $links[] = $l;
        }
    } catch (Exception $e) {
        error_log('IA links componentes error: ' . $e->getMessage());
    }

    if (in_array($contexto_pagina, ['contratos', 'entregas', 'lotes'], true)) {
        try {
            $stmt = $pdo->prepare("SELECT id, numero FROM contratos WHERE LOWER(numero) LIKE ? OR LOWER(entidad_contratante) LIKE ? ORDER BY id DESC LIMIT 2");
            $stmt->execute([$like, $like]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $l = ia_build_link('Contrato: ' . (string)$r['numero'], 'contrato', (int)$r['id']);
                if ($l) $links[] = $l;
            }
        } catch (Exception $e) {
            error_log('IA links contratos error: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare("SELECT id, codigo_entrega, institucion_educativa FROM entregas WHERE LOWER(COALESCE(codigo_entrega, '')) LIKE ? OR LOWER(institucion_educativa) LIKE ? ORDER BY id DESC LIMIT 2");
            $stmt->execute([$like, $like]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $label = !empty($r['codigo_entrega']) ? ('Entrega: ' . (string)$r['codigo_entrega']) : ('Entrega ID ' . (int)$r['id'] . ' · ' . (string)$r['institucion_educativa']);
                $l = ia_build_link($label, 'entrega', (int)$r['id']);
                if ($l) $links[] = $l;
            }
        } catch (Exception $e) {
            error_log('IA links entregas error: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare("SELECT id, codigo_lote FROM lotes WHERE LOWER(codigo_lote) LIKE ? ORDER BY id DESC LIMIT 2");
            $stmt->execute([$like]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $l = ia_build_link('Lote: ' . (string)$r['codigo_lote'], 'lote', (int)$r['id']);
                if ($l) $links[] = $l;
            }
        } catch (Exception $e) {
            error_log('IA links lotes error: ' . $e->getMessage());
        }
    }

    return ia_unique_links($links, 8);
}

function build_backend_deterministic_answer(PDO $pdo, string $intent, string $pregunta): array|string|null {
    try {
        if ($intent === 'clase_mas_kits') {
            $row = $pdo->query(
                "SELECT
                    c.id,
                    c.nombre,
                    COUNT(DISTINCT ck.kit_id) AS total_kits,
                    ROUND(COUNT(DISTINCT ck.kit_id) * 1.0 + COUNT(DISTINCT km.id) * 0.35 + COUNT(DISTINCT kc.item_id) * 0.20, 2) AS score_ref
                 FROM clases c
                 LEFT JOIN clase_kits ck ON ck.clase_id = c.id
                 LEFT JOIN kits k ON k.id = ck.kit_id
                 LEFT JOIN kit_componentes kc ON kc.kit_id = ck.kit_id
                 LEFT JOIN kit_manuals km ON km.status = 'published' AND (km.kit_id = ck.kit_id OR km.item_id = kc.item_id)
                 GROUP BY c.id, c.nombre
                 ORDER BY total_kits DESC, score_ref DESC, c.nombre ASC
                 LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $kit_rows = [];
            try {
                $stmt = $pdo->prepare(
                    "SELECT k.id, k.nombre
                     FROM clase_kits ck
                     JOIN kits k ON k.id = ck.kit_id
                     WHERE ck.clase_id = ?
                     ORDER BY k.nombre ASC
                     LIMIT 6"
                );
                $stmt->execute([(int)$row['id']]);
                $kit_rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $e) {
                error_log('IA deterministic class top kits links error: ' . $e->getMessage());
            }

            $links = [];
            $class_link = ia_build_link('Clase: ' . (string)$row['nombre'] . ' (ID ' . (int)$row['id'] . ')', 'clase', (int)$row['id']);
            if ($class_link) $links[] = $class_link;
            foreach ($kit_rows as $k) {
                $kit_link = ia_build_link('Kit: ' . (string)$k['nombre'] . ' (ID ' . (int)$k['id'] . ')', 'kit', (int)$k['id']);
                if ($kit_link) $links[] = $kit_link;
            }

            return [
                'text' => "Respuesta corta: La clase " . $row['nombre'] . " (ID " . (int)$row['id'] . ") tiene más kits, con " . (int)$row['total_kits'] . " kits asociados.\n"
                    . "Evidencia:\n"
                    . "- Ranking por número de kits: " . $row['nombre'] . " → kits=" . (int)$row['total_kits'] . "\n"
                    . "- Referencia de score combinado: " . (float)$row['score_ref'] . "\n"
                    . "Siguiente accion: Si quieres, te detallo cada kit y su relación con manuales y componentes.",
                'links' => array_slice($links, 0, 8)
            ];
        }

        if ($intent === 'manuales_nombres') {
            $rows = $pdo->query(
                "SELECT
                    km.id,
                    km.titulo,
                    km.status,
                    km.kit_id,
                    km.item_id,
                    k.nombre AS kit_nombre,
                    i.nombre_comun AS componente_nombre
                 FROM kit_manuals km
                 LEFT JOIN kits k ON k.id = km.kit_id
                 LEFT JOIN kit_items i ON i.id = km.item_id
                 WHERE km.status = 'published'
                 ORDER BY km.id DESC
                 LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return "Respuesta corta: No hay manuales publicados en este momento.\n"
                    . "Evidencia:\n"
                    . "- Coincidencias de manuales publicados: 0\n"
                    . "Siguiente accion: Publica un manual y te confirmo el nombre y su pertenencia.";
            }

            $lineas = [];
            $links = [];
            foreach ($rows as $r) {
                $dest = $r['kit_nombre'] ?: ($r['componente_nombre'] ?: 'sin destino');
                $lineas[] = '- ' . (string)$r['titulo'] . ' (destino: ' . $dest . ')';
                $manual_link = ia_build_link((string)$r['titulo'], 'manual', (int)$r['id']);
                if ($manual_link) $links[] = $manual_link;
                if (!empty($r['kit_nombre']) && !empty($r['kit_id'])) {
                    $kit_link = ia_build_link('Kit: ' . (string)$r['kit_nombre'], 'kit', (int)$r['kit_id']);
                    if ($kit_link) $links[] = $kit_link;
                }
                if (!empty($r['componente_nombre']) && !empty($r['item_id'])) {
                    $item_link = ia_build_link('Componente: ' . (string)$r['componente_nombre'], 'componente', (int)$r['item_id']);
                    if ($item_link) $links[] = $item_link;
                }
            }

            return [
                'text' => "Respuesta corta: Estos son los manuales publicados que tengo registrados.\n"
                    . "Evidencia:\n"
                    . implode("\n", $lineas) . "\n"
                    . "Siguiente accion: Si quieres, te digo a que clases impacta cada manual.",
                'links' => array_slice($links, 0, 8)
            ];
        }

        if ($intent === 'manuales_estado') {
            $row = $pdo->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS publicados,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS borrador,
                    SUM(CASE WHEN status IN ('archived','inactive') THEN 1 ELSE 0 END) AS archivados,
                    SUM(CASE WHEN status = 'published' AND kit_id IS NOT NULL THEN 1 ELSE 0 END) AS publicados_kit,
                    SUM(CASE WHEN status = 'published' AND item_id IS NOT NULL THEN 1 ELSE 0 END) AS publicados_componente
                 FROM kit_manuals"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return "Respuesta corta: Si hay manuales en el sistema.\n"
                . "Evidencia:\n"
                . "- Total: " . (int)$row['total'] . "\n"
                . "- Publicados: " . (int)$row['publicados'] . "\n"
                . "- Borrador: " . (int)$row['borrador'] . "\n"
                . "- Archivados/Inactivos: " . (int)$row['archivados'] . "\n"
                . "- Publicados de kit: " . (int)$row['publicados_kit'] . "\n"
                . "- Publicados de componente: " . (int)$row['publicados_componente'] . "\n"
                . "Siguiente accion: Si quieres, te filtro por componente o por kit especifico.";
        }

        if ($intent === 'clase_mas_completa') {
            $row = $pdo->query(
                "SELECT
                    c.id,
                    c.nombre,
                    COUNT(DISTINCT ck.kit_id) AS total_kits,
                    COUNT(DISTINCT kc.item_id) AS total_componentes,
                    COUNT(DISTINCT km.id) AS total_manuales_publicados,
                    ROUND(COUNT(DISTINCT ck.kit_id) * 0.40 + COUNT(DISTINCT km.id) * 0.35 + COUNT(DISTINCT kc.item_id) * 0.25, 2) AS score_completitud
                 FROM clases c
                 LEFT JOIN clase_kits ck ON ck.clase_id = c.id
                 LEFT JOIN kit_componentes kc ON kc.kit_id = ck.kit_id
                 LEFT JOIN kit_manuals km ON km.status = 'published' AND (km.kit_id = ck.kit_id OR km.item_id = kc.item_id)
                 GROUP BY c.id, c.nombre
                 ORDER BY score_completitud DESC, total_kits DESC, total_manuales_publicados DESC, total_componentes DESC, c.nombre ASC
                 LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $kit_rows = [];
            try {
                $stmt = $pdo->prepare(
                    "SELECT k.id, k.nombre
                     FROM clase_kits ck
                     JOIN kits k ON k.id = ck.kit_id
                     WHERE ck.clase_id = ?
                     ORDER BY k.nombre ASC
                     LIMIT 5"
                );
                $stmt->execute([(int)$row['id']]);
                $kit_rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $e) {
                error_log('IA deterministic class kits links error: ' . $e->getMessage());
            }

            $links = [];
            $class_link = ia_build_link('Clase: ' . (string)$row['nombre'] . ' (ID ' . (int)$row['id'] . ')', 'clase', (int)$row['id']);
            if ($class_link) $links[] = $class_link;
            foreach ($kit_rows as $k) {
                $kit_link = ia_build_link('Kit: ' . (string)$k['nombre'] . ' (ID ' . (int)$k['id'] . ')', 'kit', (int)$k['id']);
                if ($kit_link) $links[] = $kit_link;
            }

            return [
                'text' => "Respuesta corta: La clase mas completa es " . $row['nombre'] . " (ID " . (int)$row['id'] . ").\n"
                    . "Evidencia:\n"
                    . "- Kits: " . (int)$row['total_kits'] . "\n"
                    . "- Manuales publicados: " . (int)$row['total_manuales_publicados'] . "\n"
                    . "- Componentes: " . (int)$row['total_componentes'] . "\n"
                    . "- Score: " . (float)$row['score_completitud'] . "\n"
                    . "Siguiente accion: Si quieres, te doy tambien el top 3 con el mismo criterio.",
                'links' => array_slice($links, 0, 8)
            ];
        }

        if ($intent === 'manual_pertenencia') {
            $q = mb_strtolower(trim($pregunta));
            $terms = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $q) ?? '') ?: [];
            $terms = array_values(array_filter($terms, static fn($t) => mb_strlen($t) >= 4));
            $terms = array_slice($terms, 0, 6);

            $where = [];
            $params = [];
            if (!empty($terms)) {
                foreach ($terms as $t) {
                    $where[] = '(LOWER(km.titulo) LIKE ? OR LOWER(i.nombre_comun) LIKE ? OR LOWER(k.nombre) LIKE ?)';
                    $like = '%' . $t . '%';
                    $params[] = $like;
                    $params[] = $like;
                    $params[] = $like;
                }
            } else {
                $where[] = '1=1';
            }

            $sql = "SELECT
                        km.id,
                        km.titulo,
                        km.status,
                        km.ambito,
                        km.kit_id,
                        k.nombre AS kit_nombre,
                        km.item_id,
                        i.nombre_comun AS componente_nombre,
                        (
                            SELECT GROUP_CONCAT(DISTINCT c1.nombre ORDER BY c1.nombre SEPARATOR ', ')
                            FROM clase_kits ck1
                            JOIN clases c1 ON c1.id = ck1.clase_id
                            WHERE ck1.kit_id = km.kit_id
                        ) AS clases_kit,
                        (
                            SELECT GROUP_CONCAT(DISTINCT c2.nombre ORDER BY c2.nombre SEPARATOR ', ')
                            FROM kit_componentes kcx
                            JOIN clase_kits ck2 ON ck2.kit_id = kcx.kit_id
                            JOIN clases c2 ON c2.id = ck2.clase_id
                            WHERE kcx.item_id = km.item_id
                        ) AS clases_comp
                    FROM kit_manuals km
                    LEFT JOIN kits k ON k.id = km.kit_id
                    LEFT JOIN kit_items i ON i.id = km.item_id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY (km.status = 'published') DESC, km.id DESC
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return "Respuesta corta: No encontre un manual que coincida claramente con tu pregunta.\n"
                    . "Evidencia:\n"
                    . "- No hubo coincidencias por titulo de manual, componente o kit.\n"
                    . "Siguiente accion: Comparte el nombre del manual o del componente para ubicarlo con precision.";
            }

            $destino = 'sin destino definido';
            if (!empty($row['kit_id'])) {
                $destino = 'kit ' . ($row['kit_nombre'] ?: ('#' . (int)$row['kit_id']));
            } elseif (!empty($row['item_id'])) {
                $destino = 'componente ' . ($row['componente_nombre'] ?: ('#' . (int)$row['item_id']));
            }
            $clases = $row['clases_kit'] ?: ($row['clases_comp'] ?: 'sin clases relacionadas');

            $links = [];
            $manual_link = ia_build_link('Manual: ' . (string)$row['titulo'] . ' (ID ' . (int)$row['id'] . ')', 'manual', (int)$row['id']);
            if ($manual_link) $links[] = $manual_link;
            if (!empty($row['kit_id'])) {
                $kit_link = ia_build_link('Kit: ' . (string)$row['kit_nombre'] . ' (ID ' . (int)$row['kit_id'] . ')', 'kit', (int)$row['kit_id']);
                if ($kit_link) $links[] = $kit_link;
            }
            if (!empty($row['item_id'])) {
                $item_link = ia_build_link('Componente: ' . (string)$row['componente_nombre'] . ' (ID ' . (int)$row['item_id'] . ')', 'componente', (int)$row['item_id']);
                if ($item_link) $links[] = $item_link;
            }

            return [
                'text' => "Respuesta corta: El manual encontrado pertenece a " . $destino . ".\n"
                    . "Evidencia:\n"
                    . "- Manual: " . $row['titulo'] . "\n"
                    . "- Estado: " . $row['status'] . "\n"
                    . "- Ambito: " . $row['ambito'] . "\n"
                    . "- Clases relacionadas: " . $clases . "\n"
                    . "Siguiente accion: Si me das otro nombre exacto, te confirmo su pertenencia puntual.",
                'links' => array_slice($links, 0, 5)
            ];
        }

        if ($intent === 'contratos_vencimiento') {
            $row = $pdo->query(
                "SELECT contratos_vigentes, contratos_suspendidos, contratos_vencidos_activos, contratos_por_vencer_30d
                 FROM v_admin_kpis_contratos LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return [
                'text' => "Respuesta corta: Estos son los contratos con foco en vencimientos.
Evidencia:
- Vigentes: " . (int)$row['contratos_vigentes'] . "
- Suspendidos: " . (int)$row['contratos_suspendidos'] . "
- Vencidos con estado activo: " . (int)$row['contratos_vencidos_activos'] . "
- Próximos a vencer (30 días): " . (int)$row['contratos_por_vencer_30d'] . "
Siguiente accion: Si quieres, te muestro el detalle por contrato desde el módulo Contratos.",
                'links' => [
                    [
                        'label' => 'Abrir módulo de contratos',
                        'url' => '/admin/contratos/index.php',
                        'entity_type' => 'contrato',
                        'entity_id' => 0,
                    ]
                ]
            ];
        }

        if ($intent === 'entregas_sin_acta') {
            $row = $pdo->query(
                "SELECT entregas_entregadas, entregas_entregadas_sin_acta
                 FROM v_admin_kpis_entregas LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return [
                'text' => "Respuesta corta: Este es el estado de actas para entregas completadas.
Evidencia:
- Entregas marcadas como entregadas: " . (int)$row['entregas_entregadas'] . "
- Entregas entregadas sin acta: " . (int)$row['entregas_entregadas_sin_acta'] . "
Siguiente accion: Si quieres, te indico las entregas priorizadas para cargar acta.",
                'links' => [
                    [
                        'label' => 'Abrir módulo de entregas',
                        'url' => '/admin/entregas/index.php',
                        'entity_type' => 'entrega',
                        'entity_id' => 0,
                    ]
                ]
            ];
        }

        if ($intent === 'entregas_pendientes') {
            $row = $pdo->query(
                "SELECT entregas_programadas, entregas_en_transito, entregas_reprogramadas, entregas_abiertas, entregas_atrasadas
                 FROM v_admin_kpis_entregas LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return [
                'text' => "Respuesta corta: Este es el panorama de entregas pendientes.
Evidencia:
- Programadas: " . (int)$row['entregas_programadas'] . "
- En tránsito: " . (int)$row['entregas_en_transito'] . "
- Reprogramadas: " . (int)$row['entregas_reprogramadas'] . "
- Abiertas (totales): " . (int)$row['entregas_abiertas'] . "
- Atrasadas: " . (int)$row['entregas_atrasadas'] . "
Siguiente accion: Si quieres, priorizo primero las atrasadas por fecha programada.",
                'links' => [
                    [
                        'label' => 'Abrir módulo de entregas',
                        'url' => '/admin/entregas/index.php',
                        'entity_type' => 'entrega',
                        'entity_id' => 0,
                    ]
                ]
            ];
        }

        if ($intent === 'lotes_riesgo_quiebre') {
            $row = $pdo->query(
                "SELECT lotes_activos, lotes_bloqueados, lotes_riesgo_quiebre, stock_disponible, stock_total
                 FROM v_admin_kpis_lotes LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return [
                'text' => "Respuesta corta: Este es el estado de riesgo de lotes.
Evidencia:
- Lotes activos: " . (int)$row['lotes_activos'] . "
- Lotes bloqueados: " . (int)$row['lotes_bloqueados'] . "
- Lotes con riesgo de quiebre: " . (int)$row['lotes_riesgo_quiebre'] . "
- Stock disponible: " . (int)$row['stock_disponible'] . "
- Stock total: " . (int)$row['stock_total'] . "
Siguiente accion: Si quieres, te doy los lotes con menor disponibilidad para priorizar reposición.",
                'links' => [
                    [
                        'label' => 'Abrir módulo de lotes',
                        'url' => '/admin/lotes/index.php',
                        'entity_type' => 'lote',
                        'entity_id' => 0,
                    ]
                ]
            ];
        }

        if ($intent === 'riesgo_operativo') {
            $rows = $pdo->query(
                "SELECT modulo, nivel_riesgo, COUNT(*) AS total
                 FROM v_admin_riesgo_operativo
                 WHERE nivel_riesgo IN ('alto', 'medio')
                 GROUP BY modulo, nivel_riesgo
                 ORDER BY FIELD(nivel_riesgo, 'alto', 'medio'), modulo"
            )->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return [
                    'text' => "Respuesta corta: No detecté riesgos operativos altos o medios.
Evidencia:
- Registros críticos en v_admin_riesgo_operativo: 0
Siguiente accion: Si quieres, te doy igual un chequeo general de contratos, entregas y lotes.",
                    'links' => [
                        [
                            'label' => 'Ver contratos',
                            'url' => '/admin/contratos/index.php',
                            'entity_type' => 'contrato',
                            'entity_id' => 0,
                        ],
                        [
                            'label' => 'Ver entregas',
                            'url' => '/admin/entregas/index.php',
                            'entity_type' => 'entrega',
                            'entity_id' => 0,
                        ],
                        [
                            'label' => 'Ver lotes',
                            'url' => '/admin/lotes/index.php',
                            'entity_type' => 'lote',
                            'entity_id' => 0,
                        ]
                    ]
                ];
            }

            $lineas = [];
            foreach ($rows as $r) {
                $lineas[] = '- ' . (string)$r['modulo'] . ' | ' . (string)$r['nivel_riesgo'] . ': ' . (int)$r['total'];
            }

            return [
                'text' => "Respuesta corta: Sí hay riesgos operativos que requieren seguimiento.
Evidencia:
" . implode("\n", $lineas) . "
Siguiente accion: Si quieres, te doy el detalle por referencia (contrato, entrega o lote).",
                'links' => [
                    [
                        'label' => 'Abrir riesgo operativo (IA)',
                        'url' => '/admin/ia/index.php?tab=estado',
                        'entity_type' => 'riesgo',
                        'entity_id' => 0,
                    ],
                    [
                        'label' => 'Ver contratos',
                        'url' => '/admin/contratos/index.php',
                        'entity_type' => 'contrato',
                        'entity_id' => 0,
                    ],
                    [
                        'label' => 'Ver entregas',
                        'url' => '/admin/entregas/index.php',
                        'entity_type' => 'entrega',
                        'entity_id' => 0,
                    ],
                    [
                        'label' => 'Ver lotes',
                        'url' => '/admin/lotes/index.php',
                        'entity_type' => 'lote',
                        'entity_id' => 0,
                    ]
                ]
            ];
        }
    } catch (Exception $e) {
        error_log('IA deterministic backend error: ' . $e->getMessage());
    }

    return null;
}

function get_backend_session_id(PDO $pdo, string $admin_user, string $contexto_scope, string $contexto_pagina, ?string $entidad_tipo, ?int $entidad_id): ?int {
    $contexto_scope = $contexto_scope !== '' ? $contexto_scope : 'admin_global';
    $entidad_tipo = $entidad_tipo ?: null;
    $entidad_id = $entidad_id ?: null;

    $sesion_clave = 'backend:' . $admin_user . ':' . $contexto_scope;
    if ($contexto_scope !== 'admin_global') {
        $sesion_clave .= ':' . ($contexto_pagina !== '' ? $contexto_pagina : 'admin');
        if ($entidad_tipo && $entidad_id) {
            $sesion_clave .= ':' . $entidad_tipo . ':' . (int)$entidad_id;
        }
    }
    $sesion_clave = mb_substr($sesion_clave, 0, 160);
    $sesion_hash = hash('sha256', $sesion_clave);

    try {
        $stmt = $pdo->prepare('SELECT sesion_id FROM ia_sesiones_contexto WHERE instancia = ? AND sesion_clave = ? AND activa = 1 LIMIT 1');
        $stmt->execute(['backend', $sesion_clave]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['sesion_id'])) {
            $sesion_id = (int)$row['sesion_id'];
            $pdo->prepare('UPDATE ia_sesiones SET fecha_ultima_interaccion = NOW(), admin_user = ?, contexto_scope = ?, contexto_pagina = ?, entidad_tipo = ?, entidad_id = ? WHERE id = ?')
                ->execute([$admin_user, $contexto_scope, $contexto_pagina ?: null, $entidad_tipo, $entidad_id, $sesion_id]);
            return $sesion_id;
        }

        $pdo->prepare('INSERT INTO ia_sesiones (sesion_hash, clase_id, instancia, admin_user, contexto_scope, contexto_pagina, entidad_tipo, entidad_id)
                       VALUES (?, NULL, ?, ?, ?, ?, ?, ?)')
            ->execute([$sesion_hash, 'backend', $admin_user, $contexto_scope, $contexto_pagina ?: null, $entidad_tipo, $entidad_id]);
        $sesion_id = (int)$pdo->lastInsertId();

        $pdo->prepare('INSERT INTO ia_sesiones_contexto (instancia, sesion_clave, sesion_id, activa) VALUES (?, ?, ?, 1)
                       ON DUPLICATE KEY UPDATE sesion_id = VALUES(sesion_id), activa = 1, updated_at = NOW()')
            ->execute(['backend', $sesion_clave, $sesion_id]);
        return $sesion_id;
    } catch (Exception $e) {
        error_log('IA backend session resolver (new schema) error: ' . $e->getMessage());
    }

    // Fallback para esquemas sin ia_sesiones_contexto o sin columnas nuevas.
    try {
        $stmt = $pdo->prepare('SELECT id FROM ia_sesiones WHERE sesion_hash = ? LIMIT 1');
        $stmt->execute([$sesion_hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sesion_id = (int)$row['id'];
            $pdo->prepare('UPDATE ia_sesiones SET fecha_ultima_interaccion = NOW(), instancia = ? WHERE id = ?')
                ->execute(['backend', $sesion_id]);
            return $sesion_id;
        }

        $pdo->prepare('INSERT INTO ia_sesiones (sesion_hash, clase_id, instancia) VALUES (?, NULL, ?)')
            ->execute([$sesion_hash, 'backend']);
        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('IA backend session resolver (legacy schema) error: ' . $e->getMessage());
        return null;
    }
}

function hydrate_historial_from_db(PDO $pdo, int $sesion_id, int $max_rows = 12): array {
    $out = [];
    if ($sesion_id <= 0) return $out;
    try {
        $stmt = $pdo->prepare('SELECT rol, contenido FROM ia_mensajes WHERE sesion_id = ? AND rol IN (\'user\', \'assistant\') ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $sesion_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $max_rows, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_reverse($rows);
        foreach ($rows as $r) {
            $role = (string)($r['rol'] ?? '');
            $content = mb_substr(trim((string)($r['contenido'] ?? '')), 0, 800);
            if (($role === 'user' || $role === 'assistant') && $content !== '') {
                $out[] = ['role' => $role, 'content' => $content];
            }
        }
    } catch (Exception $e) {
        error_log('IA hydrate historial error: ' . $e->getMessage());
    }
    return $out;
}

function persist_ia_messages(PDO $pdo, int $sesion_id, string $instancia, string $pregunta, string $respuesta, int $tokens, string $modelo): void {
    if ($sesion_id <= 0) return;
    try {
        $pdo->prepare('INSERT INTO ia_mensajes (sesion_id, rol, contenido, tokens, instancia, metadata) VALUES (?, \'user\', ?, 0, ?, JSON_OBJECT(\'timestamp\', NOW()))')
            ->execute([$sesion_id, $pregunta, $instancia]);
        $pdo->prepare('INSERT INTO ia_mensajes (sesion_id, rol, contenido, tokens, instancia, metadata) VALUES (?, \'assistant\', ?, ?, ?, JSON_OBJECT(\'modelo\', ?))')
            ->execute([$sesion_id, $respuesta, $tokens, $instancia, $modelo]);

        $pdo->prepare('UPDATE ia_sesiones
                       SET total_mensajes = COALESCE(total_mensajes, 0) + 2,
                           tokens_usados = COALESCE(tokens_usados, 0) + ?,
                           fecha_ultima_interaccion = NOW()
                       WHERE id = ?')
            ->execute([$tokens, $sesion_id]);
    } catch (Exception $e) {
        error_log('IA persist mensajes error: ' . $e->getMessage());
    }
}

function log_analytics_event(PDO $pdo, array $event): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO analytics_eventos
            (session_hash, sesion_ia_id, instancia, evento, tipo_pagina, modulo, entidad_tipo, entidad_id,
             clase_id, kit_id, componente_id, manual_id, termino_busqueda, resultado_posicion, referrer,
             departamento, dispositivo, ip_anon, user_agent, duracion_ms, valor_numerico, metadata)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $event['session_hash'] ?? null,
            $event['sesion_ia_id'] ?? null,
            $event['instancia'] ?? 'frontend',
            $event['evento'] ?? null,
            $event['tipo_pagina'] ?? null,
            $event['modulo'] ?? null,
            $event['entidad_tipo'] ?? null,
            $event['entidad_id'] ?? null,
            $event['clase_id'] ?? null,
            $event['kit_id'] ?? null,
            $event['componente_id'] ?? null,
            $event['manual_id'] ?? null,
            $event['termino_busqueda'] ?? null,
            $event['resultado_posicion'] ?? null,
            $event['referrer'] ?? null,
            $event['departamento'] ?? null,
            $event['dispositivo'] ?? null,
            $event['ip_anon'] ?? null,
            $event['user_agent'] ?? null,
            $event['duracion_ms'] ?? null,
            $event['valor_numerico'] ?? null,
            isset($event['metadata']) ? json_encode($event['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    } catch (Exception $e) {
        error_log('IA analytics_event insert error: ' . $e->getMessage());
    }
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
            $lineas[] = "[Clase] {$c['nombre']} | Ciclo {$c['ciclo']} | {$c['dificultad']} | {$c['duracion_minutos']} min | URL: /clase.php?slug={$c['slug']}\n    Descripcion: {$desc}";
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
            $lineas[] = "[Kit] {$k['nombre']} | Codigo: {$k['codigo']} | URL: /kit.php?slug={$k['slug']}\n    Descripcion: {$desc}";
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
            $lineas[] = "[Componente] {$ki['nombre_comun']} | SKU: {$ki['sku']} | URL: /componente.php?slug={$ki['slug']}";
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
        return "=== CATALOGO VERDICT ===\n"
            . "CATALOGO_VERDICT: EMPTY\n"
            . "Busqueda realizada: '{$termino_safe}'\n"
            . "Resultado: No existe ninguna clase, kit ni componente sobre este tema en el catalogo actual ({$total_clases} clases y {$total_kits} kits disponibles).\n"
            . "COMPORTAMIENTO REQUERIDO: Di al usuario que aun no tenemos una clase o kit especifico sobre su tema y ofrece continuar la explicacion educativa sin inventar productos.";
    }

    return "=== CATALOGO VERDICT ===\n"
        . "CATALOGO_VERDICT: FOUND\n"
        . "CATALOGO_TOTAL_RESULTADOS: " . count($lineas) . "\n"
        . "REGLA: Debes mencionar al menos 1 resultado real listado abajo. Prohibido decir 'aun no tenemos' cuando el veredicto es FOUND.\n"
        . "=== CATALOGO DISPONIBLE (resultados reales — usa SOLO estos) ===\n"
        . "REGLA: Menciona UNICAMENTE los productos listados aqui. NO inventes nombres de clases, kits, codigos ni materiales que no aparezcan en esta lista.\n"
        . implode("\n", $lineas);
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

function read_backend_context_snapshot_file(string $name, int $max_chars = 12000): ?string {
    $base = realpath(__DIR__ . '/../marco/estado');
    if ($base === false) return null;
    $path = $base . DIRECTORY_SEPARATOR . $name;
    if (!is_file($path) || !is_readable($path)) return null;
    $txt = @file_get_contents($path);
    if ($txt === false || trim($txt) === '') return null;
    return mb_substr(trim($txt), 0, $max_chars);
}

function get_backend_snapshot_age_minutes(string $name): ?int {
    $base = realpath(__DIR__ . '/../marco/estado');
    if ($base === false) return null;
    $path = $base . DIRECTORY_SEPARATOR . $name;
    if (!is_file($path)) return null;
    $mtime = @filemtime($path);
    if ($mtime === false) return null;
    $age = (int) floor((time() - (int)$mtime) / 60);
    return max(0, $age);
}

function build_backend_snapshot_blocks(string $contexto_pagina): array {
    $bloques = [];

    $age_global = get_backend_snapshot_age_minutes('ia_contexto_global.md');
    if ($age_global !== null) {
        $bloques[] = "=== METADATOS SNAPSHOT ===\n"
            . "edad_minutos_global={$age_global}\n"
            . "aviso=" . ($age_global > 240 ? 'snapshot_potencialmente_desactualizado' : 'snapshot_reciente');
    }

    $global = read_backend_context_snapshot_file('ia_contexto_global.md');
    if ($global) {
        $bloques[] = "=== CONTEXTO PERSISTENTE GLOBAL ===\n" . $global;
    }

    $map = [
        'clases' => ['ia_contexto_clases.md'],
        'kits' => ['ia_contexto_kits.md', 'ia_contexto_clases.md', 'ia_contexto_manuales.md'],
        'componentes' => ['ia_contexto_componentes.md', 'ia_contexto_kits.md', 'ia_contexto_manuales.md'],
        'manuales' => ['ia_contexto_manuales.md', 'ia_contexto_kits.md', 'ia_contexto_clases.md'],
        'dashboard' => ['ia_contexto_clases.md', 'ia_contexto_kits.md', 'ia_contexto_componentes.md', 'ia_contexto_manuales.md'],
        'contratos' => ['ia_contexto_global.md'],
        'entregas' => ['ia_contexto_global.md'],
        'lotes' => ['ia_contexto_kits.md', 'ia_contexto_componentes.md', 'ia_contexto_manuales.md'],
        'ia' => ['ia_contexto_clases.md', 'ia_contexto_kits.md', 'ia_contexto_componentes.md', 'ia_contexto_manuales.md'],
    ];

    $targets = $map[$contexto_pagina] ?? ['ia_contexto_clases.md', 'ia_contexto_kits.md', 'ia_contexto_manuales.md'];
    foreach ($targets as $file) {
        $txt = read_backend_context_snapshot_file($file, 14000);
        if ($txt) {
            $bloques[] = "=== CONTEXTO SNAPSHOT: {$file} ===\n" . $txt;
        }
    }

    $json = read_backend_context_snapshot_file('ia_contexto_resumen.json', 3000);
    if ($json) {
        $bloques[] = "=== CONTEXTO SNAPSHOT JSON ===\n" . $json;
    }

    return $bloques;
}

/**
 * Construye el bloque de contexto para la instancia BACKEND.
 * Datos varÃƒÂ­an segÃƒÂºn la pÃƒÂ¡gina admin activa.
 */
function build_context_backend(PDO $pdo, string $contexto_pagina, ?string $entidad_tipo, ?int $entidad_id): string {
    $bloques = [];

    $snapshot_blocks = build_backend_snapshot_blocks($contexto_pagina);
    if (!empty($snapshot_blocks)) {
        $bloques = array_merge($bloques, $snapshot_blocks);
    }

    try {
        // Estado en vivo para evitar errores por snapshots viejos en manuales.
        $manuales_estado = $pdo->query(
            "SELECT
                COUNT(*) AS manuales_total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS manuales_publicados,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS manuales_borrador,
                SUM(CASE WHEN status IN ('archived','inactive') THEN 1 ELSE 0 END) AS manuales_archivados,
                SUM(CASE WHEN status = 'published' AND kit_id IS NOT NULL THEN 1 ELSE 0 END) AS manuales_publicados_kit,
                SUM(CASE WHEN status = 'published' AND item_id IS NOT NULL THEN 1 ELSE 0 END) AS manuales_publicados_componente
             FROM kit_manuals"
        )->fetch(PDO::FETCH_ASSOC);
        if ($manuales_estado) {
            $bloques[] = "=== MANUALES EN VIVO (DB) ===\n"
                . "total=" . (int)$manuales_estado['manuales_total'] . "\n"
                . "publicados=" . (int)$manuales_estado['manuales_publicados'] . "\n"
                . "borrador=" . (int)$manuales_estado['manuales_borrador'] . "\n"
                . "archivados_inactivos=" . (int)$manuales_estado['manuales_archivados'] . "\n"
                . "publicados_kit=" . (int)$manuales_estado['manuales_publicados_kit'] . "\n"
                . "publicados_componente=" . (int)$manuales_estado['manuales_publicados_componente'];
        }

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

            case 'manuales':
                $rows = $pdo->query(
                    "SELECT km.id, km.titulo, km.status, km.ambito, k.nombre AS kit_nombre, i.nombre_comun AS componente_nombre
                     FROM kit_manuals km
                     LEFT JOIN kits k ON k.id = km.kit_id
                     LEFT JOIN kit_items i ON i.id = km.item_id
                     ORDER BY (km.status = 'published') DESC, km.id DESC
                     LIMIT 150"
                )->fetchAll(PDO::FETCH_ASSOC);
                $bloques[] = "=== MANUALES (" . count($rows) . " registros) ===\n"
                    . implode("\n", array_map(function ($r) {
                        $dest = $r['kit_nombre'] ?: ($r['componente_nombre'] ?: 'sin_destino');
                        return "  [{$r['id']}] {$r['titulo']} | status={$r['status']} | ambito={$r['ambito']} | destino={$dest}";
                    }, $rows));
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
    $contexto_scope_in = $instancia === 'backend' ? trim((string)($data['contexto_scope'] ?? 'admin_global')) : '';
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
    $max_pregunta_chars = 2000;
    if ($instancia === 'backend' && in_array($contexto_scope_in, ['admin_clases_builder', 'admin_clases_content_builder', 'admin_kits_builder', 'admin_kits_content_builder', 'admin_componentes_builder', 'admin_componentes_content_builder'], true)) {
        $max_pregunta_chars = 14000;
    }
    if (mb_strlen($pregunta) > $max_pregunta_chars) json_fail('Pregunta demasiado larga.');

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
    $contexto_scope  = $instancia === 'backend'  ? trim($data['contexto_scope'] ?? 'admin_global') : '';
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

    if ($instancia === 'backend' && in_array($contexto_scope, ['admin_clases_builder', 'admin_clases_content_builder', 'admin_kits_builder', 'admin_kits_content_builder', 'admin_componentes_builder', 'admin_componentes_content_builder'], true)) {
        // Los builders requieren respuestas JSON largas (contenido_html completo).
        $max_tokens = max($max_tokens, 2200);
    }

    $guardrails_activos  = (($cfg['guardrails_activos'] ?? '0') === '1');
    $palabras_peligro    = json_decode($cfg['palabras_peligro']   ?? '[]', true) ?: [];
    $palabras_tematicas  = json_decode($cfg['palabras_tematicas'] ?? '[]', true) ?: [];
    $mensaje_guardrail   = $cfg['mensaje_guardrail'] ?? 'Ã¢Å¡Â Ã¯Â¸Â Consulta con tu profesor.';

    // ---------------------------------------------------------------
    // Sesiones por instancia (frontend anÃƒÂ³nima / backend por usuario admin+scope)
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
    } else {
        $admin_user = (string)($_SESSION['admin_username'] ?? 'admin');
        $sesion_id = get_backend_session_id($pdo, $admin_user, $contexto_scope, $contexto_pagina, $entidad_tipo, $entidad_id);
        if (empty($historial)) {
            $historial = hydrate_historial_from_db($pdo, (int)$sesion_id, 12);
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
    $respuesta_links = [];
    $backend_builder_scopes = ['admin_clases_builder', 'admin_clases_content_builder', 'admin_kits_builder', 'admin_kits_content_builder', 'admin_componentes_builder', 'admin_componentes_content_builder'];
    $is_backend_builder_scope = ($instancia === 'backend' && in_array($contexto_scope, $backend_builder_scopes, true));
    $tipo_pagina_analytics = $instancia === 'backend' ? 'admin' : ($pagina !== '' ? $pagina : 'frontend');
    $modulo_analytics = $instancia === 'backend' ? ($contexto_pagina !== '' ? $contexto_pagina : 'admin') : null;
    $session_hash_analytics = null;
    if ($instancia === 'frontend') {
        $session_hash_analytics = $_COOKIE['cdc_session'] ?? null;
    }

    log_analytics_event($pdo, [
        'session_hash' => $session_hash_analytics,
        'sesion_ia_id' => $sesion_id,
        'instancia' => $instancia,
        'evento' => 'ia_question',
        'tipo_pagina' => $tipo_pagina_analytics,
        'modulo' => $modulo_analytics,
        'entidad_tipo' => $entidad_tipo,
        'entidad_id' => $entidad_id,
        'clase_id' => $clase_id,
        'kit_id' => $kit_id,
        'componente_id' => $componente_id,
        'manual_id' => $manual_id,
        'termino_busqueda' => mb_substr($pregunta, 0, 255),
        'referrer' => mb_substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
        'dispositivo' => (strpos(strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 'mobile') !== false ? 'mobile' : 'desktop'),
        'ip_anon' => null,
        'user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        'metadata' => ['source' => 'api/ia-consulta.php']
    ]);

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
            $intent_backend = null;
            if ($instancia === 'backend' && !$is_backend_builder_scope) {
                $intent_backend = detect_backend_intent($pregunta);
                if ($intent_backend !== null) {
                    $det_resp = build_backend_deterministic_answer($pdo, $intent_backend, $pregunta);
                    if (!empty($det_resp)) {
                        if (is_array($det_resp)) {
                            $respuesta = normalize_backend_chat_response((string)($det_resp['text'] ?? ''), 10);
                            $respuesta_links = is_array($det_resp['links'] ?? null) ? array_values($det_resp['links']) : [];
                        } else {
                            $respuesta = normalize_backend_chat_response((string)$det_resp, 10);
                        }
                        $modelo_usado = 'deterministic-backend:' . $intent_backend;
                    }
                }
                if (empty($respuesta_links)) {
                    $respuesta_links = ia_backend_links_from_entities($contexto_pagina, $entidad_tipo, $entidad_id);
                }
            }

            if (!empty($respuesta)) {
                // Respuesta deterministica backend ya resuelta.
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
                $system_content .= "\n\nREGLA DE CATALOGO (obligatoria):\n"
                    . "- Si el contexto incluye 'CATALOGO_VERDICT: FOUND', DEBES mencionar al menos un resultado real del catálogo y NO puedes decir 'aun no tenemos'.\n"
                    . "- Si el contexto incluye 'CATALOGO_VERDICT: EMPTY', NO inventes clases, kits ni materiales. Di honestamente que aun no hay un producto específico y ofrece continuar la explicación educativa.\n"
                    . "- Usa solo URLs y nombres que aparezcan en el bloque de catálogo.";
            }

            if ($instancia === 'backend') {
                if ($is_backend_builder_scope) {
                    $system_content .= "\n\nFORMATO OBLIGATORIO BUILDER (admin builders):\n"
                        . "- Respeta exactamente el formato solicitado por el usuario o por su prompt.\n"
                        . "- Si se solicita JSON, devuelve SOLO JSON valido, sin texto extra.\n"
                        . "- No apliques el formato de panel lateral (Respuesta corta / Evidencia / Siguiente accion).";
                } else {
                    $system_content .= "\n\nFORMATO OBLIGATORIO BACKEND (panel lateral):\n"
                        . "- Responde SIEMPRE en texto simple, corto y legible.\n"
                        . "- Estructura exacta: 'Respuesta corta:', 'Evidencia:', 'Siguiente accion:'.\n"
                        . "- En 'Evidencia' usa solo lista con guiones.\n"
                        . "- Prohibido usar tablas markdown, separadores con '|', HTML o bloques de codigo.\n"
                        . "- Maximo 10 lineas de contenido.\n"
                        . "- Si faltan datos, dilo explicitamente y pide un dato concreto.";
                }
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

                if ($instancia === 'backend' && !$is_backend_builder_scope && !empty($respuesta) && mb_strpos((string)$respuesta, '❌') !== 0) {
                    $respuesta = normalize_backend_chat_response((string)$respuesta, 10);
                }
            } else {
                $groq_detalle = $GLOBALS['_ia_groq_ultimo_error'] ?? 'sin detalle';
                $respuesta = 'Ã¢ÂÅ’ Error al consultar la IA. Detalle: ' . $groq_detalle;
                error_log('IA groq todos los modelos fallaron: ' . $groq_detalle);
            }
            }
        }

        if ($instancia === 'backend' && !$is_backend_builder_scope) {
            $search_links = ia_backend_links_from_search($pdo, $contexto_pagina, $pregunta, (string)$respuesta);
            $respuesta_links = ia_unique_links(array_merge($respuesta_links, $search_links), 8);
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

    log_analytics_event($pdo, [
        'session_hash' => $session_hash_analytics,
        'sesion_ia_id' => $sesion_id,
        'instancia' => $instancia,
        'evento' => ($respuesta && strpos((string)$respuesta, '❌') === 0) ? 'ia_error' : 'ia_answer',
        'tipo_pagina' => $tipo_pagina_analytics,
        'modulo' => $modulo_analytics,
        'entidad_tipo' => $entidad_tipo,
        'entidad_id' => $entidad_id,
        'clase_id' => $clase_id,
        'kit_id' => $kit_id,
        'componente_id' => $componente_id,
        'manual_id' => $manual_id,
        'duracion_ms' => $tiempo_ms,
        'valor_numerico' => $tokens,
        'metadata' => [
            'guardrail_activado' => $guardrail_activado,
            'cached' => $cached,
            'modelo_usado' => $modelo_usado
        ]
    ]);

    persist_ia_messages(
        $pdo,
        (int)$sesion_id,
        $instancia,
        $pregunta,
        (string)$respuesta,
        (int)$tokens,
        (string)$modelo_usado
    );

    ob_end_clean(); // descartar warnings/notices PHP antes de responder
    echo json_encode([
        'ok'                 => true,
        'respuesta'          => $respuesta,
        'links'              => $respuesta_links,
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
