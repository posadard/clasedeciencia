<?php
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$csrf_token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
if ($csrf_token === '' || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No se recibió archivo']);
    exit;
}

$file = $_FILES['image'];
if (!isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Error al subir archivo']);
    exit;
}

$preset = isset($_POST['preset']) ? trim((string)$_POST['preset']) : 'kit-cover';
$entity = isset($_POST['entity']) ? trim((string)$_POST['entity']) : 'general';

$allowed_presets = [
    'kit-cover' => ['width' => 800, 'height' => 800],
    'clase-cover' => ['width' => 800, 'height' => 800],
    'componente-thumb' => ['width' => 800, 'height' => 800],
    'generic-cover' => ['width' => 1200, 'height' => 675],
];

if (!isset($allowed_presets[$preset])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Preset no permitido']);
    exit;
}

$max_size_bytes = 8 * 1024 * 1024;
if (!isset($file['size']) || (int)$file['size'] <= 0 || (int)$file['size'] > $max_size_bytes) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Tamaño de archivo inválido']);
    exit;
}

$tmp_name = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Archivo temporal inválido']);
    exit;
}

$img_info = @getimagesize($tmp_name);
if ($img_info === false || !isset($img_info['mime'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El archivo no es una imagen válida']);
    exit;
}

$mime = (string)$img_info['mime'];
$src = null;
switch ($mime) {
    case 'image/jpeg':
        $src = @imagecreatefromjpeg($tmp_name);
        break;
    case 'image/png':
        $src = @imagecreatefrompng($tmp_name);
        break;
    case 'image/webp':
        if (function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($tmp_name);
        }
        break;
}

if (!$src) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Formato no soportado (use JPG, PNG o WEBP)']);
    exit;
}

$target_w = (int)$allowed_presets[$preset]['width'];
$target_h = (int)$allowed_presets[$preset]['height'];
$src_w = (int)imagesx($src);
$src_h = (int)imagesy($src);
if ($src_w <= 0 || $src_h <= 0) {
    imagedestroy($src);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Dimensiones inválidas']);
    exit;
}

$dst = imagecreatetruecolor($target_w, $target_h);
if (!$dst) {
    imagedestroy($src);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo procesar la imagen']);
    exit;
}

$white = imagecolorallocate($dst, 255, 255, 255);
imagefill($dst, 0, 0, $white);

imagecopyresampled($dst, $src, 0, 0, 0, 0, $target_w, $target_h, $src_w, $src_h);

$entity_safe = strtolower($entity);
$entity_safe = preg_replace('/[^a-z0-9_-]+/', '-', $entity_safe);
$entity_safe = trim((string)$entity_safe, '-');
if ($entity_safe === '') {
    $entity_safe = 'general';
}

$year = date('Y');
$month = date('m');
$base_dir = __DIR__ . '/../../assets/images/uploads/' . $entity_safe . '/' . $year . '/' . $month;
if (!is_dir($base_dir)) {
    if (!mkdir($base_dir, 0755, true) && !is_dir($base_dir)) {
        imagedestroy($src);
        imagedestroy($dst);
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo crear el directorio de destino']);
        exit;
    }
}

$random = bin2hex(random_bytes(6));
$filename = $entity_safe . '-' . date('Ymd-His') . '-' . $random . '.webp';
$absolute_path = $base_dir . '/' . $filename;
$relative_url = '/assets/images/uploads/' . $entity_safe . '/' . $year . '/' . $month . '/' . $filename;

$quality = 84;
$ok_write = false;
if (function_exists('imagewebp')) {
    $ok_write = @imagewebp($dst, $absolute_path, $quality);
}

if (!$ok_write) {
    imagedestroy($src);
    imagedestroy($dst);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar imagen webp']);
    exit;
}

imagedestroy($src);
imagedestroy($dst);

echo json_encode([
    'ok' => true,
    'url' => $relative_url,
    'width' => $target_w,
    'height' => $target_h,
    'preset' => $preset,
], JSON_UNESCAPED_UNICODE);
