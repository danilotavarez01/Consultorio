<?php
// Asegurar configuración de sesión adecuada
require_once 'session_config.php';

// Inicializar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log de debug para diagnóstico
error_log("LOGOUT.PHP: Cerrando sesión para usuario: " . ($_SESSION['username'] ?? 'desconocido'));
error_log("LOGOUT.PHP: Session ID: " . session_id());

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se usan cookies de sesión, eliminarlas también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión completamente
session_destroy();

// Regenerar un nuevo ID de sesión para evitar problemas
session_start();
session_regenerate_id(true);
session_destroy();

// Log de confirmación
error_log("LOGOUT.PHP: Sesión cerrada exitosamente");

// Headers para evitar caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir a la página de login con parámetro de confirmación
header("Location: login.php?logout=success");
exit;
?>