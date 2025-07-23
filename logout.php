<?php
// Desactivar cualquier output de errores
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier buffer de salida
if (ob_get_level()) {
    ob_clean();
}

// Iniciar y destruir sesión
session_start();
$_SESSION = array();
session_destroy();

// Redirigir inmediatamente sin parámetros
header("Location: login.php");
exit;
?>