<?php
header('Content-Type: application/json; charset=utf-8');
function validar_respuesta($respuesta) {
    $palabras_peligro = ['fuego', 'explosión', 'ácido fuerte'];
    foreach ($palabras_peligro as $p) {
        if (stripos($respuesta, $p) !== false) {
            return "⚠️ Consulta con tu profesor antes de modificar el experimento.";
        }
    }
    return $respuesta;
}
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$pregunta = trim($input['pregunta'] ?? '');
$contexto = $input['contexto'] ?? [];
$reply = $pregunta ? "Respuesta segura basada en contexto local." : "Pregunta vacía.";
echo json_encode(['respuesta' => validar_respuesta($reply), 'eco' => $contexto], JSON_UNESCAPED_UNICODE);
?>