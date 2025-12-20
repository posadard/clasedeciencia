<?php
// Stub de tracking para replicación TGA sin DB específica
header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents('php://input'), true) ?? [];
error_log('TRACK_CLICK: ' . json_encode($input));
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
?>