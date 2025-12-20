<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db-functions.php';
header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$termino = trim($input['busqueda'] ?? '');
$resultados = [];
if ($termino !== '' && $pdo) {
    $resultados = buscar_proyectos($pdo, $termino);
}
echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
?>