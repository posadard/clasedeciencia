<?php
// API: Async Kit Attribute Actions (add/update/delete) without reloading page
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Accept JSON body or form-encoded
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { $data = $_POST; }

    // CSRF token optional for API; if present, validate
    if (isset($data['csrf_token'])) {
        session_start();
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
            exit;
        }
    }

    $kit_id = isset($data['kit_id']) ? (int)$data['kit_id'] : 0;
    $action = isset($data['action']) ? (string)$data['action'] : '';
    $def_id = isset($data['def_id']) ? (int)$data['def_id'] : 0;
    $valor = isset($data['valor']) ? (string)$data['valor'] : '';
    $unidad = isset($data['unidad']) ? trim((string)$data['unidad']) : '';

    if ($kit_id <= 0) { throw new Exception('kit_id requerido'); }
    if ($def_id <= 0) { throw new Exception('atributo inválido'); }

    // Fetch attribute definition
    $defS = $pdo->prepare('SELECT * FROM atributos_definiciones WHERE id = ?');
    $defS->execute([$def_id]);
    $def = $defS->fetch(PDO::FETCH_ASSOC);
    if (!$def) { throw new Exception('definición no existe'); }

    // Normalize values
    $vals = [];
    $card = $def['cardinalidad'];
    $tipo = $def['tipo_dato'];
    if ($action === 'add_attr' || $action === 'update_attr') {
        $vals = $card === 'many' ? array_filter(array_map('trim', preg_split('/[\n,]+/', $valor))) : [$valor];
    }

    // Delete existing if updating, or on explicit delete
    if ($action === 'update_attr' || $action === 'delete_attr') {
        $pdo->prepare('DELETE FROM atributos_contenidos WHERE tipo_entidad = ? AND entidad_id = ? AND atributo_id = ?')->execute(['kit', $kit_id, $def_id]);
    }

    $inserted = 0;
    if ($action === 'add_attr' || $action === 'update_attr') {
        $ins = $pdo->prepare('INSERT INTO atributos_contenidos (tipo_entidad, entidad_id, atributo_id, valor_string, valor_numero, valor_entero, valor_booleano, valor_fecha, valor_datetime, valor_json, unidad_codigo, lang, orden, fuente, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
        $orden = 1;
        foreach ($vals as $v) {
            if ($v === '' || $v === null) { continue; }
            $val_string = $val_numero = $val_entero = $val_bool = $val_fecha = $val_dt = $val_json = null;
            switch ($tipo) {
                case 'number':
                    $num = is_numeric(str_replace(',', '.', $v)) ? (float)str_replace(',', '.', $v) : null; if ($num === null) continue 2; $val_numero = $num; break;
                case 'integer':
                    $int = is_numeric($v) ? (int)$v : null; if ($int === null) continue 2; $val_entero = $int; break;
                case 'boolean':
                    $val_bool = ($v === '1' || strtolower($v) === 'true' || strtolower($v) === 'sí' || strtolower($v) === 'si') ? 1 : 0; break;
                case 'date':
                    $val_fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; if ($val_fecha === null) continue 2; break;
                case 'datetime':
                    $val_dt = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v) ? (str_replace('T', ' ', $v) . ':00') : null; if ($val_dt === null) continue 2; break;
                case 'json':
                    $decoded = json_decode($v, true); if ($decoded === null && strtolower(trim($v)) !== 'null') continue 2; $val_json = json_encode($decoded); break;
                case 'string':
                default:
                    $val_string = mb_substr((string)$v, 0, 2000, 'UTF-8'); break;
            }
            $unidad_codigo = ($unidad !== '') ? $unidad : ($def['unidad_defecto'] ?? null);
            $ins->execute(['kit', $kit_id, $def_id, $val_string, $val_numero, $val_entero, $val_bool, $val_fecha, $val_dt, $val_json, $unidad_codigo, 'es-CO', $orden++, 'manual']);
            $inserted++;
        }
    }

    echo json_encode(['ok' => true, 'action' => $action, 'inserted' => $inserted]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
