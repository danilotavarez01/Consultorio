<?php
session_start();

// Verificar si hay una solicitud para establecer la variable from_config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_from_config'])) {
    // Establecer la variable de sesión
    $_SESSION['from_config'] = true;
    
    // Devolver una respuesta exitosa
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    // Devolver un error si no se proporcionó el parámetro correcto
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}
?>
