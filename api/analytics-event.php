<?php
/**
 * API: Analytics Event
 * Registra eventos unificados en analytics_eventos
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

function fail_event(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function anonymize_ip_event(string $ip): string {
    if ($ip === '') return '';
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $parts[3] = '0';
            return implode('.', $parts);
        }
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        return implode(':', array_slice($parts, 0, 3)) . '::0';
    }
    return 'unknown';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail_event('Method not allowed', 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}
if (!is_array($data)) {
    fail_event('Payload invalido');
}

$instancia = (($data['instancia'] ?? 'frontend') === 'backend') ? 'backend' : 'frontend';
$evento = trim((string)($data['evento'] ?? ''));
if ($evento === '') {
    fail_event('Evento requerido');
}
$evento = mb_substr($evento, 0, 64);

if ($instancia === 'backend') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        fail_event('No autorizado', 403);
    }
}

$session_hash = trim((string)($data['session_hash'] ?? ''));
if ($session_hash === '' && $instancia === 'frontend') {
    $session_hash = (string)($_COOKIE['cdc_session'] ?? '');
    if ($session_hash === '') {
        try {
            $session_hash = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $session_hash = bin2hex(openssl_random_pseudo_bytes(16));
        }
        setcookie('cdc_session', $session_hash, time() + 3600 * 24 * 365, '/', '', true, true);
    }
}

$fields = [
    'tipo_pagina' => mb_substr(trim((string)($data['tipo_pagina'] ?? '')), 0, 80),
    'modulo' => mb_substr(trim((string)($data['modulo'] ?? '')), 0, 80),
    'entidad_tipo' => mb_substr(trim((string)($data['entidad_tipo'] ?? '')), 0, 40),
    'entidad_id' => isset($data['entidad_id']) ? (int)$data['entidad_id'] : null,
    'clase_id' => isset($data['clase_id']) ? (int)$data['clase_id'] : null,
    'kit_id' => isset($data['kit_id']) ? (int)$data['kit_id'] : null,
    'componente_id' => isset($data['componente_id']) ? (int)$data['componente_id'] : null,
    'manual_id' => isset($data['manual_id']) ? (int)$data['manual_id'] : null,
    'termino_busqueda' => mb_substr(trim((string)($data['termino_busqueda'] ?? '')), 0, 255),
    'resultado_posicion' => isset($data['resultado_posicion']) ? (int)$data['resultado_posicion'] : null,
    'referrer' => mb_substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
    'departamento' => mb_substr(trim((string)($data['departamento'] ?? '')), 0, 120),
    'dispositivo' => mb_substr(trim((string)($data['dispositivo'] ?? '')), 0, 64),
    'ip_anon' => anonymize_ip_event((string)($_SERVER['REMOTE_ADDR'] ?? '')),
    'user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    'duracion_ms' => isset($data['duracion_ms']) ? (int)$data['duracion_ms'] : null,
    'valor_numerico' => isset($data['valor_numerico']) ? (float)$data['valor_numerico'] : null,
    'metadata' => $data['metadata'] ?? null,
];

if ($fields['dispositivo'] === '') {
    $ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $fields['dispositivo'] = (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) ? 'mobile' : 'desktop';
}

$metadata_json = null;
if ($fields['metadata'] !== null && $fields['metadata'] !== '') {
    $metadata_json = json_encode($fields['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

try {
    $stmt = $pdo->prepare("INSERT INTO analytics_eventos
        (session_hash, sesion_ia_id, instancia, evento, tipo_pagina, modulo, entidad_tipo, entidad_id,
         clase_id, kit_id, componente_id, manual_id, termino_busqueda, resultado_posicion, referrer,
         departamento, dispositivo, ip_anon, user_agent, duracion_ms, valor_numerico, metadata)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $session_hash !== '' ? $session_hash : null,
        $instancia,
        $evento,
        $fields['tipo_pagina'] !== '' ? $fields['tipo_pagina'] : null,
        $fields['modulo'] !== '' ? $fields['modulo'] : null,
        $fields['entidad_tipo'] !== '' ? $fields['entidad_tipo'] : null,
        $fields['entidad_id'] ?: null,
        $fields['clase_id'] ?: null,
        $fields['kit_id'] ?: null,
        $fields['componente_id'] ?: null,
        $fields['manual_id'] ?: null,
        $fields['termino_busqueda'] !== '' ? $fields['termino_busqueda'] : null,
        $fields['resultado_posicion'] ?: null,
        $fields['referrer'] !== '' ? $fields['referrer'] : null,
        $fields['departamento'] !== '' ? $fields['departamento'] : null,
        $fields['dispositivo'] !== '' ? $fields['dispositivo'] : null,
        $fields['ip_anon'] !== '' ? $fields['ip_anon'] : null,
        $fields['user_agent'] !== '' ? $fields['user_agent'] : null,
        $fields['duracion_ms'] ?: null,
        $fields['valor_numerico'],
        $metadata_json,
    ]);

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    error_log('analytics-event insert error: ' . $e->getMessage());
    fail_event('No se pudo registrar el evento', 500);
}
