<?php
// Incluir configuración de sesiones
require_once __DIR__ . '/session_config.php';

// Desactivar cualquier output de errores
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier buffer de salida
if (ob_get_level()) {
    ob_end_clean();
}

// Iniciar sesión para poder destruirla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Limpiar cualquier cache del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir al login
header("Location: login.php?logout=success");
exit;
?>