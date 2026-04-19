<?php
require_once '../auth.php';
$page_title = 'Panel IA';

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------
function ia_get_config(PDO $pdo, string $instancia): array {
    // Solo filas globales (pagina IS NULL)
    $stmt = $pdo->prepare('SELECT clave, valor, tipo FROM configuracion_ia WHERE instancia = ? AND pagina IS NULL ORDER BY clave');
    $stmt->execute([$instancia]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[$row['clave']] = ['valor' => $row['valor'], 'tipo' => $row['tipo']];
    }
    return $out;
}

// Devuelve overrides agrupados por pagina: ['clase' => ['prompt_sistema' => [...], ...], ...]
function ia_get_config_paginas(PDO $pdo, string $instancia): array {
    $stmt = $pdo->prepare('SELECT pagina, clave, valor, tipo FROM configuracion_ia WHERE instancia = ? AND pagina IS NOT NULL ORDER BY pagina, clave');
    $stmt->execute([$instancia]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[$row['pagina']][$row['clave']] = ['valor' => $row['valor'], 'tipo' => $row['tipo']];
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

function ia_safe_query(PDO $pdo, string $sql, array $params = []): array {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('[IA Admin] ia_safe_query error: ' . $e->getMessage());
        return [];
    }
}

function ia_generate_context_files(PDO $pdo): array {
    $base_dir = realpath(__DIR__ . '/../../marco');
    if ($base_dir === false) {
        return ['ok' => false, 'msg' => 'No se encontró carpeta marco/.'];
    }
    $estado_dir = $base_dir . DIRECTORY_SEPARATOR . 'estado';
    if (!is_dir($estado_dir) && !mkdir($estado_dir, 0775, true)) {
        return ['ok' => false, 'msg' => 'No se pudo crear marco/estado.'];
    }

    $ts = date('Y-m-d H:i:s');

    $resumen = [
        'generado_en' => $ts,
        'clases_activas' => 0,
        'kits_activos' => 0,
        'componentes_activos' => 0,
        'manuales_publicados' => 0,
        'manuales_total' => 0,
        'manuales_borrador' => 0,
        'manuales_archivados' => 0,
        'manuales_publicados_kit' => 0,
        'manuales_publicados_componente' => 0,
        'contratos' => 0,
        'entregas' => 0,
        'lotes' => 0,
    ];

    try {
        $resumen['clases_activas'] = (int)($pdo->query('SELECT COUNT(*) FROM clases WHERE activo = 1')->fetchColumn() ?: 0);
        $resumen['kits_activos'] = (int)($pdo->query('SELECT COUNT(*) FROM kits WHERE activo = 1')->fetchColumn() ?: 0);
        $resumen['componentes_activos'] = (int)($pdo->query('SELECT COUNT(*) FROM kit_items WHERE activo = 1')->fetchColumn() ?: 0);
        $resumen['manuales_total'] = (int)($pdo->query('SELECT COUNT(*) FROM kit_manuals')->fetchColumn() ?: 0);
        $resumen['manuales_publicados'] = (int)($pdo->query("SELECT COUNT(*) FROM kit_manuals WHERE status = 'published'")->fetchColumn() ?: 0);
        $resumen['manuales_borrador'] = (int)($pdo->query("SELECT COUNT(*) FROM kit_manuals WHERE status = 'draft'")->fetchColumn() ?: 0);
        $resumen['manuales_archivados'] = (int)($pdo->query("SELECT COUNT(*) FROM kit_manuals WHERE status IN ('archived','inactive')")->fetchColumn() ?: 0);
        $resumen['manuales_publicados_kit'] = (int)($pdo->query("SELECT COUNT(*) FROM kit_manuals WHERE status = 'published' AND kit_id IS NOT NULL")->fetchColumn() ?: 0);
        $resumen['manuales_publicados_componente'] = (int)($pdo->query("SELECT COUNT(*) FROM kit_manuals WHERE status = 'published' AND item_id IS NOT NULL")->fetchColumn() ?: 0);
        $resumen['contratos'] = (int)($pdo->query('SELECT COUNT(*) FROM contratos')->fetchColumn() ?: 0);
        $resumen['entregas'] = (int)($pdo->query('SELECT COUNT(*) FROM entregas')->fetchColumn() ?: 0);
        $resumen['lotes'] = (int)($pdo->query('SELECT COUNT(*) FROM lotes')->fetchColumn() ?: 0);
    } catch (Exception $e) {
        error_log('[IA Admin] resumen error: ' . $e->getMessage());
    }

    $rows_clases = ia_safe_query(
        $pdo,
        "SELECT c.id, c.nombre, c.ciclo, c.dificultad, c.activo,
                COUNT(DISTINCT ck.kit_id) AS total_kits,
                COUNT(DISTINCT km.id) AS total_manuales_publicados,
                COUNT(DISTINCT kc.item_id) AS total_componentes,
                ROUND(COUNT(DISTINCT ck.kit_id) * 0.45 + COUNT(DISTINCT km.id) * 0.35 + COUNT(DISTINCT kc.item_id) * 0.20, 2) AS score_completitud
         FROM clases c
         LEFT JOIN clase_kits ck ON ck.clase_id = c.id
         LEFT JOIN kits k ON k.id = ck.kit_id
         LEFT JOIN kit_manuals km ON km.kit_id = k.id AND km.status = 'published'
         LEFT JOIN kit_componentes kc ON kc.kit_id = k.id
         GROUP BY c.id, c.nombre, c.ciclo, c.dificultad, c.activo
         ORDER BY score_completitud DESC, total_kits DESC, c.nombre ASC"
    );

    $rows_kits = ia_safe_query(
        $pdo,
        "SELECT k.id, k.nombre, k.codigo, k.activo,
                COUNT(DISTINCT ck.clase_id) AS total_clases_relacionadas,
                COUNT(DISTINCT km.id) AS total_manuales_publicados,
                COUNT(DISTINCT kc.item_id) AS total_componentes
         FROM kits k
         LEFT JOIN clase_kits ck ON ck.kit_id = k.id
         LEFT JOIN kit_manuals km ON km.kit_id = k.id AND km.status = 'published'
         LEFT JOIN kit_componentes kc ON kc.kit_id = k.id
         GROUP BY k.id, k.nombre, k.codigo, k.activo
         ORDER BY total_clases_relacionadas DESC, total_manuales_publicados DESC, total_componentes DESC, k.nombre ASC"
    );

    $rows_componentes = ia_safe_query(
        $pdo,
        "SELECT i.id, i.nombre_comun, i.sku, i.activo, COUNT(DISTINCT kc.kit_id) AS kits_asociados
         FROM kit_items i
         LEFT JOIN kit_componentes kc ON kc.item_id = i.id
         GROUP BY i.id, i.nombre_comun, i.sku, i.activo
         ORDER BY kits_asociados DESC, i.nombre_comun ASC"
    );

    $rows_manuales = ia_safe_query(
        $pdo,
        "SELECT
            km.id,
            km.titulo,
            km.status,
            km.ambito,
            km.tipo_manual,
            km.kit_id,
            k.nombre AS kit_nombre,
            km.item_id,
            i.nombre_comun AS componente_nombre,
            (
                SELECT GROUP_CONCAT(DISTINCT c1.nombre ORDER BY c1.nombre SEPARATOR ', ')
                FROM clase_kits ck1
                JOIN clases c1 ON c1.id = ck1.clase_id
                WHERE ck1.kit_id = km.kit_id
            ) AS clases_por_kit,
            (
                SELECT GROUP_CONCAT(DISTINCT c2.nombre ORDER BY c2.nombre SEPARATOR ', ')
                FROM kit_componentes kcx
                JOIN clase_kits ck2 ON ck2.kit_id = kcx.kit_id
                JOIN clases c2 ON c2.id = ck2.clase_id
                WHERE kcx.item_id = km.item_id
            ) AS clases_por_componente
         FROM kit_manuals km
         LEFT JOIN kits k ON k.id = km.kit_id
         LEFT JOIN kit_items i ON i.id = km.item_id
         ORDER BY (km.status = 'published') DESC, km.id DESC
         LIMIT 500"
    );

    $rows_operacion = ia_safe_query(
        $pdo,
        "SELECT
            (SELECT COUNT(*) FROM contratos WHERE estado_contrato IN ('vigente','suspendido')) AS contratos_vivos,
            (SELECT COUNT(*) FROM entregas WHERE estado_entrega IN ('programada','reprogramada','en_transito')) AS entregas_abiertas,
            (SELECT COUNT(*) FROM lotes WHERE estado_lote = 'activo') AS lotes_activos"
    );

    $md_global = [];
    $md_global[] = '# Contexto Global IA (Backend)';
    $md_global[] = 'Generado: ' . $ts;
    $md_global[] = '';
    $md_global[] = '## Resumen';
    $md_global[] = '- Clases activas: ' . $resumen['clases_activas'];
    $md_global[] = '- Kits activos: ' . $resumen['kits_activos'];
    $md_global[] = '- Componentes activos: ' . $resumen['componentes_activos'];
    $md_global[] = '- Manuales totales: ' . $resumen['manuales_total'];
    $md_global[] = '- Manuales publicados: ' . $resumen['manuales_publicados'];
    $md_global[] = '- Manuales borrador: ' . $resumen['manuales_borrador'];
    $md_global[] = '- Manuales archivados/inactivos: ' . $resumen['manuales_archivados'];
    $md_global[] = '- Manuales publicados (kit): ' . $resumen['manuales_publicados_kit'];
    $md_global[] = '- Manuales publicados (componente): ' . $resumen['manuales_publicados_componente'];
    $md_global[] = '- Contratos: ' . $resumen['contratos'];
    $md_global[] = '- Entregas: ' . $resumen['entregas'];
    $md_global[] = '- Lotes: ' . $resumen['lotes'];
    if (!empty($rows_operacion[0])) {
        $md_global[] = '- Contratos vivos: ' . (int)$rows_operacion[0]['contratos_vivos'];
        $md_global[] = '- Entregas abiertas: ' . (int)$rows_operacion[0]['entregas_abiertas'];
        $md_global[] = '- Lotes activos: ' . (int)$rows_operacion[0]['lotes_activos'];
    }

    $md_clases = [];
    $md_clases[] = '# Contexto IA - Clases';
    $md_clases[] = 'Generado: ' . $ts;
    $md_clases[] = '';
    $md_clases[] = '## Ranking de completitud (kits/manuales/componentes)';
    foreach ($rows_clases as $r) {
        $md_clases[] = '- [' . (int)$r['id'] . '] ' . $r['nombre']
            . ' | ciclo=' . $r['ciclo']
            . ' | kits=' . (int)$r['total_kits']
            . ' | manuales=' . (int)$r['total_manuales_publicados']
            . ' | componentes=' . (int)$r['total_componentes']
            . ' | score=' . (float)$r['score_completitud'];
    }

    $md_kits = [];
    $md_kits[] = '# Contexto IA - Kits';
    $md_kits[] = 'Generado: ' . $ts;
    $md_kits[] = '';
    $md_kits[] = '## Cobertura por kit';
    foreach ($rows_kits as $r) {
        $md_kits[] = '- [' . (int)$r['id'] . '] ' . $r['nombre']
            . ' (' . $r['codigo'] . ')'
            . ' | clases=' . (int)$r['total_clases_relacionadas']
            . ' | manuales=' . (int)$r['total_manuales_publicados']
            . ' | componentes=' . (int)$r['total_componentes']
            . ' | activo=' . (int)$r['activo'];
    }

    $md_componentes = [];
    $md_componentes[] = '# Contexto IA - Componentes';
    $md_componentes[] = 'Generado: ' . $ts;
    $md_componentes[] = '';
    $md_componentes[] = '## Componentes más reutilizados';
    foreach ($rows_componentes as $r) {
        $md_componentes[] = '- [' . (int)$r['id'] . '] ' . $r['nombre_comun']
            . ' (' . $r['sku'] . ')'
            . ' | kits_asociados=' . (int)$r['kits_asociados']
            . ' | activo=' . (int)$r['activo'];
    }

    $md_manuales = [];
    $md_manuales[] = '# Contexto IA - Manuales';
    $md_manuales[] = 'Generado: ' . $ts;
    $md_manuales[] = '';
    $md_manuales[] = '## Estado de manuales';
    $md_manuales[] = '- total=' . $resumen['manuales_total']
        . ' | publicados=' . $resumen['manuales_publicados']
        . ' | borrador=' . $resumen['manuales_borrador']
        . ' | archivados_inactivos=' . $resumen['manuales_archivados'];
    $md_manuales[] = '- publicados_kit=' . $resumen['manuales_publicados_kit']
        . ' | publicados_componente=' . $resumen['manuales_publicados_componente'];
    $md_manuales[] = '';
    $md_manuales[] = '## Trazabilidad manual -> entidad -> clases';
    foreach ($rows_manuales as $r) {
        $destino = '';
        if (!empty($r['kit_id'])) {
            $destino = 'kit:[' . (int)$r['kit_id'] . '] ' . (string)($r['kit_nombre'] ?? 'N/A');
        } elseif (!empty($r['item_id'])) {
            $destino = 'componente:[' . (int)$r['item_id'] . '] ' . (string)($r['componente_nombre'] ?? 'N/A');
        } else {
            $destino = 'sin_destino';
        }
        $clases = trim((string)($r['clases_por_kit'] ?: $r['clases_por_componente'] ?: 'sin_clases_relacionadas'));
        $md_manuales[] = '- [' . (int)$r['id'] . '] ' . (string)$r['titulo']
            . ' | status=' . (string)$r['status']
            . ' | ambito=' . (string)$r['ambito']
            . ' | tipo=' . (string)$r['tipo_manual']
            . ' | destino=' . $destino
            . ' | clases=' . $clases;
    }

    $files = [
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_global.md' => implode("\n", $md_global) . "\n",
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_clases.md' => implode("\n", $md_clases) . "\n",
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_kits.md' => implode("\n", $md_kits) . "\n",
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_componentes.md' => implode("\n", $md_componentes) . "\n",
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_manuales.md' => implode("\n", $md_manuales) . "\n",
        $estado_dir . DIRECTORY_SEPARATOR . 'ia_contexto_resumen.json' => json_encode([
            'generado_en' => $ts,
            'resumen' => $resumen,
            'rows' => [
                'clases' => count($rows_clases),
                'kits' => count($rows_kits),
                'componentes' => count($rows_componentes),
                'manuales' => count($rows_manuales),
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
    ];

    foreach ($files as $path => $content) {
        if (file_put_contents($path, $content) === false) {
            return ['ok' => false, 'msg' => 'Error escribiendo archivo de contexto: ' . basename($path)];
        }
    }

    return ['ok' => true, 'msg' => 'Contexto IA regenerado (' . count($files) . ' archivos).'];
}

function ia_refresh_search_index_fallback(PDO $pdo): array {
    try {
        $pdo->beginTransaction();

        $pdo->exec('DELETE FROM ia_search_index');

        $sql_clases = "INSERT INTO ia_search_index (
                entity_type, entity_id, title, slug, url, status_publicacion, is_active,
                search_text, search_text_normalized, keywords_json, relations_json,
                score_base, source_updated_at, indexed_at
            )
            SELECT
                'clase' AS entity_type,
                c.id AS entity_id,
                c.nombre AS title,
                c.slug,
                CONCAT('/clase.php?slug=', c.slug) AS url,
                CASE WHEN c.status = 'published' THEN 'published' ELSE c.status END AS status_publicacion,
                c.activo AS is_active,
                CONCAT_WS(' ', c.nombre, COALESCE(c.resumen, ''), COALESCE(c.objetivo_aprendizaje, ''), COALESCE(c.dificultad, ''), CONCAT('ciclo ', c.ciclo)) AS search_text,
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    CONCAT_WS(' ', c.nombre, COALESCE(c.resumen, ''), COALESCE(c.objetivo_aprendizaje, ''), COALESCE(c.dificultad, '')),
                    'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')) AS search_text_normalized,
                JSON_OBJECT('ciclo', c.ciclo, 'dificultad', c.dificultad, 'destacado', c.destacado) AS keywords_json,
                JSON_OBJECT('origen', 'fallback_admin') AS relations_json,
                1.00 + CASE WHEN c.destacado = 1 THEN 0.25 ELSE 0 END AS score_base,
                COALESCE(c.updated_at, NOW()) AS source_updated_at,
                NOW() AS indexed_at
            FROM clases c";
        $pdo->exec($sql_clases);

        $sql_kits = "INSERT INTO ia_search_index (
                entity_type, entity_id, title, slug, url, status_publicacion, is_active,
                search_text, search_text_normalized, keywords_json, relations_json,
                score_base, source_updated_at, indexed_at
            )
            SELECT
                'kit' AS entity_type,
                k.id AS entity_id,
                k.nombre AS title,
                k.slug,
                CONCAT('/kit.php?slug=', IFNULL(k.slug, CONCAT('kit-', k.id))) AS url,
                CASE WHEN k.activo = 1 THEN 'published' ELSE 'inactive' END AS status_publicacion,
                k.activo AS is_active,
                CONCAT_WS(' ', k.nombre, COALESCE(k.codigo, ''), COALESCE(k.resumen, '')) AS search_text,
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    CONCAT_WS(' ', k.nombre, COALESCE(k.codigo, ''), COALESCE(k.resumen, '')),
                    'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')) AS search_text_normalized,
                JSON_OBJECT('codigo', k.codigo, 'clase_id', k.clase_id) AS keywords_json,
                JSON_OBJECT('origen', 'fallback_admin') AS relations_json,
                1.00 AS score_base,
                COALESCE(k.updated_at, NOW()) AS source_updated_at,
                NOW() AS indexed_at
            FROM kits k";
        $pdo->exec($sql_kits);

        $sql_componentes = "INSERT INTO ia_search_index (
                entity_type, entity_id, title, slug, url, status_publicacion, is_active,
                search_text, search_text_normalized, keywords_json, relations_json,
                score_base, source_updated_at, indexed_at
            )
            SELECT
                'componente' AS entity_type,
                i.id AS entity_id,
                i.nombre_comun AS title,
                i.slug,
                CONCAT('/componente.php?slug=', IFNULL(i.slug, CONCAT('componente-', i.id))) AS url,
                'published' AS status_publicacion,
                1 AS is_active,
                CONCAT_WS(' ', i.nombre_comun, COALESCE(i.sku, ''), COALESCE(i.unidad, ''), COALESCE(i.descripcion_html, '')) AS search_text,
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    CONCAT_WS(' ', i.nombre_comun, COALESCE(i.sku, ''), COALESCE(i.unidad, ''), COALESCE(i.descripcion_html, '')),
                    'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')) AS search_text_normalized,
                JSON_OBJECT('sku', i.sku, 'unidad', i.unidad) AS keywords_json,
                JSON_OBJECT('origen', 'fallback_admin') AS relations_json,
                0.90 AS score_base,
                NOW() AS source_updated_at,
                NOW() AS indexed_at
            FROM kit_items i";
        $pdo->exec($sql_componentes);

        $sql_manuales = "INSERT INTO ia_search_index (
                entity_type, entity_id, title, slug, url, status_publicacion, is_active,
                search_text, search_text_normalized, keywords_json, relations_json,
                score_base, source_updated_at, indexed_at
            )
            SELECT
                'manual' AS entity_type,
                km.id AS entity_id,
                km.slug AS title,
                km.slug,
                CONCAT('/manual.php?slug=', km.slug) AS url,
                km.status AS status_publicacion,
                CASE WHEN km.status = 'published' THEN 1 ELSE 0 END AS is_active,
                CONCAT_WS(' ', km.slug, COALESCE(km.resumen, ''), COALESCE(k.nombre, ''), COALESCE(i.nombre_comun, '')) AS search_text,
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    CONCAT_WS(' ', km.slug, COALESCE(km.resumen, ''), COALESCE(k.nombre, ''), COALESCE(i.nombre_comun, '')),
                    'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')) AS search_text_normalized,
                JSON_OBJECT('ambito', km.ambito, 'tipo_manual', km.tipo_manual) AS keywords_json,
                JSON_OBJECT('kit_id', km.kit_id, 'item_id', km.item_id, 'origen', 'fallback_admin') AS relations_json,
                0.95 AS score_base,
                COALESCE(km.updated_at, NOW()) AS source_updated_at,
                NOW() AS indexed_at
            FROM kit_manuals km
            LEFT JOIN kits k ON k.id = km.kit_id
            LEFT JOIN kit_items i ON i.id = km.item_id";
        $pdo->exec($sql_manuales);

        $stmtCount = $pdo->query("SELECT entity_type, COUNT(*) AS total FROM ia_search_index GROUP BY entity_type");
        $summary = $stmtCount ? $stmtCount->fetchAll(PDO::FETCH_ASSOC) : [];

        $index_version = 'fallback-' . date('YmdHis');
        $stmtMeta = $pdo->prepare(
            "INSERT INTO ia_search_index_meta (index_version, generated_at, source_summary, notes)
             VALUES (?, NOW(), ?, ?)"
        );
        $stmtMeta->execute([
            $index_version,
            json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Regenerado por fallback admin (sin stored procedure)'
        ]);

        $pdo->commit();
        return ['ok' => true, 'msg' => 'Índice IA regenerado con fallback interno (sin stored procedure).'];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[IA Admin] fallback refresh index error: ' . $e->getMessage());
        return ['ok' => false, 'msg' => 'Falló la regeneración del índice IA: ' . $e->getMessage()];
    }
}

function ia_refresh_search_index(PDO $pdo): array {
    $proc_exists = false;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE' AND ROUTINE_NAME = 'sp_refresh_ia_search_index'");
        $proc_exists = ((int)($stmt ? $stmt->fetchColumn() : 0) > 0);
    } catch (Exception $e) {
        error_log('[IA Admin] routine check warning: ' . $e->getMessage());
    }

    if ($proc_exists) {
        try {
            $pdo->exec('CALL sp_refresh_ia_search_index()');
        } catch (Exception $e) {
            error_log('[IA Admin] refresh index error: ' . $e->getMessage());
            $fallback = ia_refresh_search_index_fallback($pdo);
            if (empty($fallback['ok'])) {
                return ['ok' => false, 'msg' => 'No se pudo regenerar el índice IA ni por procedimiento ni por fallback.'];
            }
        }
    } else {
        $fallback = ia_refresh_search_index_fallback($pdo);
        if (empty($fallback['ok'])) {
            return $fallback;
        }
    }

    try {
        $dir = __DIR__ . '/../../assets/cache';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear directorio de cache.');
        }
        $version = (string)time();
        $ok_write = file_put_contents($dir . '/search-version.txt', $version, LOCK_EX);
        if ($ok_write === false) {
            throw new RuntimeException('No se pudo actualizar search-version.');
        }
    } catch (Exception $e) {
        error_log('[IA Admin] refresh index cache version error: ' . $e->getMessage());
        return ['ok' => true, 'msg' => 'Índice IA regenerado, pero no se pudo actualizar search-version.'];
    }

    try {
        $pdo->prepare("INSERT INTO ia_admin_refresh_log (proceso, estado, detalle_json) VALUES (?, 'ok', JSON_OBJECT('origen', 'admin/ia/index.php'))")
            ->execute(['search_index']);
    } catch (Exception $e) {
        error_log('[IA Admin] refresh log warning: ' . $e->getMessage());
    }

    return ['ok' => true, 'msg' => 'Índice IA regenerado y versión de búsqueda actualizada.'];
}

// ---------------------------------------------------------------
// POST: Guardar configuración
// ---------------------------------------------------------------
$save_ok     = false;
$save_msg    = '';
$save_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'generate_context') {
        $r = ia_generate_context_files($pdo);
        if (!empty($r['ok'])) {
            $save_ok = true;
            $save_msg = (string)$r['msg'];
        } else {
            $save_error = (string)($r['msg'] ?? 'No se pudo regenerar el contexto IA.');
        }
    } elseif ($action === 'refresh_search_index') {
        $r = ia_refresh_search_index($pdo);
        if (!empty($r['ok'])) {
            $save_ok = true;
            $save_msg = (string)$r['msg'];
        } else {
            $save_error = (string)($r['msg'] ?? 'No se pudo regenerar el índice IA.');
        }
    } elseif ($action === 'save_config') {
    $instancia_save = in_array($_POST['instancia'] ?? '', ['frontend', 'backend']) ? $_POST['instancia'] : null;
    // pagina_save: NULL = global, o nombre de página (clase, kit, etc.)
    $paginas_validas = ['clase', 'kit', 'componente', 'manual', 'inicio', 'catalogo'];
    $pagina_raw      = $_POST['pagina'] ?? '';
    $pagina_save     = ($pagina_raw === '' || $pagina_raw === 'global') ? null : $pagina_raw;
    if ($pagina_save !== null && !in_array($pagina_save, $paginas_validas, true)) {
        $pagina_save = null;   // fallback seguro
    }

    if (!$instancia_save) {
        $save_error = 'Instancia inválida.';
    } else {
        try {
            // Obtener tipos de las claves que corresponden a este scope (global o página)
            if ($pagina_save === null) {
                $stmt_get = $pdo->prepare('SELECT clave, tipo FROM configuracion_ia WHERE instancia = ? AND pagina IS NULL');
                $stmt_get->execute([$instancia_save]);
            } else {
                $stmt_get = $pdo->prepare('SELECT clave, tipo FROM configuracion_ia WHERE instancia = ? AND pagina = ?');
                $stmt_get->execute([$instancia_save, $pagina_save]);
            }
            $campos = $stmt_get->fetchAll(PDO::FETCH_KEY_PAIR);   // clave => tipo

            // UPDATE apuntando exactamente al scope correcto
            if ($pagina_save === null) {
                $stmt_upd = $pdo->prepare(
                    'UPDATE configuracion_ia SET valor = ?, updated_at = NOW() WHERE instancia = ? AND pagina IS NULL AND clave = ?'
                );
            } else {
                $stmt_upd = $pdo->prepare(
                    'UPDATE configuracion_ia SET valor = ?, updated_at = NOW() WHERE instancia = ? AND pagina = ? AND clave = ?'
                );
            }

            foreach ($campos as $clave => $tipo) {
                $post_key = 'cfg_' . $clave;

                if ($tipo === 'booleano') {
                    $nuevo_valor = isset($_POST[$post_key]) ? '1' : '0';
                    if ($pagina_save === null) {
                        $stmt_upd->execute([$nuevo_valor, $instancia_save, $clave]);
                    } else {
                        $stmt_upd->execute([$nuevo_valor, $instancia_save, $pagina_save, $clave]);
                    }
                    continue;
                }

                if (!array_key_exists($post_key, $_POST)) continue;
                $nuevo_valor = $_POST[$post_key];

                if ($tipo === 'secreto') {
                    $stripped = trim($nuevo_valor);
                    if ($stripped === '' || substr_count($stripped, '●') > 4) continue;
                }

                if ($tipo === 'numero') {
                    $nuevo_valor = (string)(float)$nuevo_valor;
                } else {
                    $nuevo_valor = trim($nuevo_valor);
                }

                if ($pagina_save === null) {
                    $stmt_upd->execute([$nuevo_valor, $instancia_save, $clave]);
                } else {
                    $stmt_upd->execute([$nuevo_valor, $instancia_save, $pagina_save, $clave]);
                }
            }
            $scope_label = $pagina_save ? "página '{$pagina_save}'" : 'configuración global';
            $save_ok  = true;
            $save_msg = "Guardado: instancia '{$instancia_save}' — {$scope_label}.";
        } catch (PDOException $e) {
            error_log('[IA Admin] save error: ' . $e->getMessage());
            $save_error = 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
    }
}

// ---------------------------------------------------------------
// Cargar datos para vistas
// ---------------------------------------------------------------
$cfg_frontend        = ia_get_config($pdo, 'frontend');
$cfg_backend         = ia_get_config($pdo, 'backend');
$cfg_frontend_paginas = ia_get_config_paginas($pdo, 'frontend');
$paginas_labels = [
    'clase'      => '🔬 Clase (experimento)',
    'kit'        => '🧰 Kit',
    'componente' => '⚗️ Componente',
    'manual'     => '📖 Manual',
    'inicio'     => '🚀 Inicio',
    'catalogo'   => '📚 Catálogo',
];

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

$search_index_meta = null;
$search_index_stats = [];
try {
    $search_index_meta = $pdo->query(
        "SELECT index_version, generated_at, notes FROM ia_search_index_meta ORDER BY id DESC LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) {
    error_log('[IA Admin] search index meta: ' . $e->getMessage());
}

try {
    $search_index_stats = $pdo->query(
        "SELECT entity_type, total, activos, publicados, ultima_indexacion FROM v_ia_search_index_stats ORDER BY entity_type ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[IA Admin] search index stats: ' . $e->getMessage());
}

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

<div class="card" style="margin-bottom:1rem;">
    <h3>Contexto Expandido del Sitio</h3>
    <p class="hint" style="margin-bottom:0.8rem;">Genera snapshots en marco/estado para que la IA backend responda con contexto más amplio y consistente del sitio completo.</p>
    <form method="post" action="/admin/ia/index.php?tab=estado">
        <input type="hidden" name="action" value="generate_context">
        <button type="submit" class="btn">🧠 Regenerar contexto IA del sitio</button>
    </form>
</div>

<div class="card" style="margin-bottom:1rem;">
    <h3>Índice IA de Búsqueda (Base de Datos)</h3>
    <p class="hint" style="margin-bottom:0.8rem;">Regenera el índice transversal usado por la IA y fuerza actualización de caché de búsqueda.</p>
    <form method="post" action="/admin/ia/index.php?tab=estado" style="margin-bottom:1rem;">
        <input type="hidden" name="action" value="refresh_search_index">
        <button type="submit" class="btn">🔄 Regenerar índice IA de búsqueda</button>
    </form>

    <?php if ($search_index_meta): ?>
        <p class="hint" style="margin-bottom:0.5rem;">
            Última versión: <strong><?= htmlspecialchars((string)$search_index_meta['index_version'], ENT_QUOTES, 'UTF-8') ?></strong>
            · Generado: <strong><?= htmlspecialchars((string)$search_index_meta['generated_at'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
    <?php endif; ?>

    <?php if (!empty($search_index_stats)): ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Activos</th>
                        <th>Publicados</th>
                        <th>Última indexación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_index_stats as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$s['entity_type'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((int)$s['total']) ?></td>
                        <td><?= number_format((int)$s['activos']) ?></td>
                        <td><?= number_format((int)$s['publicados']) ?></td>
                        <td><?= htmlspecialchars((string)$s['ultima_indexacion'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="hint">No hay estadísticas del índice disponibles todavía.</p>
    <?php endif; ?>
</div>

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
                <div class="ia-test-meta" id="test-fe-links"></div>
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
                <div class="ia-test-meta" id="test-be-links"></div>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================
     TAB: FRONTEND CONFIG
================================================================= -->
<div class="ia-tab-panel <?= $active_tab === 'frontend' ? 'active' : '' ?>" id="tab-frontend">

    <?php
    // Helper local para renderizar el formulario de un scope (global o página)
    function ia_render_form(string $instancia, ?string $pagina, array $campos, string $form_id): void {
        $scope_val = $pagina ?? 'global';
        $pfx       = $pagina ? 'p_' . $pagina . '_' : 'g_';
    ?>
    <form method="post" action="/admin/ia/index.php?tab=frontend" id="<?= htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action"    value="save_config">
        <input type="hidden" name="instancia" value="<?= htmlspecialchars($instancia, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="pagina"    value="<?= htmlspecialchars($scope_val, ENT_QUOTES, 'UTF-8') ?>">
        <div class="ia-cfg-grid">
            <?php foreach ($campos as $clave => $meta): ?>
                <?php
                $valor   = $meta['valor'];
                $tipo    = $meta['tipo'];
                $label   = ia_field_label($clave);
                $is_full = in_array($clave, ['prompt_sistema', 'palabras_peligro', 'palabras_tematicas', 'mensaje_guardrail']);
                $uid     = htmlspecialchars($pfx . $clave, ENT_QUOTES, 'UTF-8');
                $name    = htmlspecialchars('cfg_' . $clave, ENT_QUOTES, 'UTF-8');
                ?>
                <div class="ia-form-group <?= $is_full ? 'full-col' : '' ?>">
                    <label for="<?= $uid ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
                    <?php if ($tipo === 'booleano'): ?>
                        <div class="ia-toggle-wrap">
                            <label class="ia-toggle">
                                <input type="checkbox" name="<?= $name ?>" id="<?= $uid ?>" value="1" <?= $valor === '1' ? 'checked' : '' ?>>
                                <span class="ia-toggle-slider"></span>
                            </label>
                            <span class="ia-toggle-label" id="<?= $uid ?>_lbl"><?= $valor === '1' ? 'Activo' : 'Inactivo' ?></span>
                        </div>
                    <?php elseif ($tipo === 'numero'): ?>
                        <input type="number" step="any" name="<?= $name ?>" id="<?= $uid ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                    <?php elseif ($tipo === 'secreto'): ?>
                        <input type="text" name="<?= $name ?>" id="<?= $uid ?>" value="" placeholder="<?= htmlspecialchars(ia_mask($valor, $tipo), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                        <span class="hint">Dejar vacío para mantener el valor actual.</span>
                    <?php elseif ($is_full): ?>
                        <textarea name="<?= $name ?>" id="<?= $uid ?>" rows="<?= $clave === 'prompt_sistema' ? 8 : 4 ?>"><?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php else: ?>
                        <input type="text" name="<?= $name ?>" id="<?= $uid ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:1.25rem;">
            <button type="submit" class="btn">💾 Guardar</button>
        </div>
    </form>
    <?php } ?>

    <!-- Config global -->
    <div class="card">
        <h3>⚙️ Configuración Global (Frontend)</h3>
        <p class="hint" style="margin-bottom:1rem;">Valores base para todas las páginas. Las páginas con overrides sobreescriben solo las claves definidas en ellas.</p>
        <?php ia_render_form('frontend', null, $cfg_frontend, 'form-fe-global'); ?>
    </div>

    <!-- Overrides por página -->
    <div class="card" style="margin-top:1.5rem;">
        <h3>📄 Overrides por Página</h3>
        <p class="hint" style="margin-bottom:1rem;">Cada página puede tener su propio prompt y parámetros. Solo se sobreescriben las claves aquí definidas.</p>

        <!-- Sub-pestañas de página -->
        <div class="ia-tabs" style="margin-bottom:1.25rem;" id="pagina-tabs">
            <?php foreach ($paginas_labels as $pg => $lbl): ?>
                <button class="ia-tab-btn<?= $pg === array_key_first($paginas_labels) ? ' active' : '' ?>"
                        data-pagina="<?= htmlspecialchars($pg, ENT_QUOTES, 'UTF-8') ?>"
                        onclick="switchPagina('<?= htmlspecialchars($pg, ENT_QUOTES, 'UTF-8') ?>', this)">
                    <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($paginas_labels as $pg => $lbl): ?>
            <div class="ia-pagina-panel" id="pagina-panel-<?= htmlspecialchars($pg, ENT_QUOTES, 'UTF-8') ?>"
                 style="display:<?= $pg === array_key_first($paginas_labels) ? 'block' : 'none' ?>;">
                <?php if (!empty($cfg_frontend_paginas[$pg])): ?>
                    <?php ia_render_form('frontend', $pg, $cfg_frontend_paginas[$pg], 'form-fe-' . $pg); ?>
                <?php else: ?>
                    <p style="color:#888;font-style:italic;">Sin overrides configurados para esta página.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
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
// Tab switching (main tabs — no afectan sub-pestañas de página)
// ---------------------------------------------------------------
document.querySelectorAll('.ia-tab-btn[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const target = this.getAttribute('data-tab');
        console.log('🔍 [Admin/IA] Cambiando a tab:', target);

        document.querySelectorAll('.ia-tab-btn[data-tab]').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.ia-tab-panel').forEach(function(p) { p.classList.remove('active'); });

        this.classList.add('active');
        var panel = document.getElementById('tab-' + target);
        if (panel) panel.classList.add('active');

        try {
            history.replaceState(null, '', '/admin/ia/index.php?tab=' + encodeURIComponent(target));
        } catch(e) {}
    });
});

// ---------------------------------------------------------------
// Sub-pestañas de página (dentro del tab frontend)
// ---------------------------------------------------------------
function switchPagina(pagina, btn) {
    console.log('🔍 [Admin/IA] Página override:', pagina);
    document.querySelectorAll('#pagina-tabs .ia-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.ia-pagina-panel').forEach(function(p) { p.style.display = 'none'; });
    btn.classList.add('active');
    var panel = document.getElementById('pagina-panel-' + pagina);
    if (panel) panel.style.display = 'block';
}

// ---------------------------------------------------------------
// Test IA
// ---------------------------------------------------------------
async function testIA(instancia) {
    console.log('🔍 [Admin/IA] Probando instancia:', instancia);

    const btnId  = 'btn-test-' + (instancia === 'frontend' ? 'fe' : 'be');
    const resId  = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-result';
    const textId = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-text';
    const metaId = 'test-'     + (instancia === 'frontend' ? 'fe' : 'be') + '-meta';
    const linksId = 'test-'    + (instancia === 'frontend' ? 'fe' : 'be') + '-links';

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
    const linksDiv = document.getElementById(linksId);
    resDiv.style.display = 'block';
    textDiv.textContent  = '…';
    metaDiv.textContent  = '';
    linksDiv.innerHTML = '';

    function isSafeInternalLink(url) {
        return typeof url === 'string'
            && url.indexOf('/') === 0
            && url.indexOf('//') !== 0
            && url.indexOf('javascript:') !== 0;
    }

    function renderLinks(links) {
        linksDiv.innerHTML = '';
        if (!Array.isArray(links) || links.length === 0) return;
        const safe = links.filter(function(l) {
            return l && typeof l.label === 'string' && l.label.trim() !== '' && isSafeInternalLink(String(l.url || ''));
        }).slice(0, 8);
        if (safe.length === 0) return;

        const title = document.createElement('div');
        title.textContent = 'Enlaces clave:';
        title.style.fontWeight = '600';
        title.style.marginTop = '0.45rem';
        linksDiv.appendChild(title);

        safe.forEach(function(l) {
            const row = document.createElement('div');
            const a = document.createElement('a');
            a.href = String(l.url);
            a.textContent = String(l.label);
            a.style.textDecoration = 'underline';
            row.appendChild(a);
            linksDiv.appendChild(row);
        });
    }

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
            renderLinks(data.links || []);
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
