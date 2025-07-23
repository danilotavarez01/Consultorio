<?php
/**
 * Verificador global de sesión
 * Este archivo debe incluirse en todas las páginas que requieren autenticación
 */

// Asegurar que la configuración de sesión esté cargada
if (!defined('SESSION_CONFIG_LOADED')) {
    require_once __DIR__ . '/session_config.php';
    define('SESSION_CONFIG_LOADED', true);
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verificar si el usuario está autenticado
 * @return bool
 */
function verificarSesionActiva() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

/**
 * Redirigir al login si no hay sesión activa
 * @param string $motivo Motivo de la redirección (opcional)
 */
function verificarAutenticacion($motivo = 'no_session') {
    if (!verificarSesionActiva()) {
        // Log para debugging
        error_log("VERIFICAR_AUTH: Redirigiendo a login. Motivo: $motivo. Page: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        
        // Limpiar cualquier sesión residual
        session_unset();
        session_destroy();
        
        // Headers anti-caché
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Determinar parámetro de redirección según el motivo
        $param = '';
        switch ($motivo) {
            case 'expired':
                $param = '?logout=inactive';
                break;
            case 'forced':
                $param = '?logout=forced';
                break;
            case 'no_session':
            default:
                $param = '?message=login_required';
                break;
        }
        
        // Redirigir al login
        header("Location: login.php" . $param);
        exit;
    }
}

/**
 * Verificar tiempo de inactividad
 * @param int $tiempo_limite Tiempo límite en segundos (default: 7200 = 2 horas)
 */
function verificarInactividad($tiempo_limite = 7200) {
    if (isset($_SESSION['last_activity'])) {
        $tiempo_inactivo = time() - $_SESSION['last_activity'];
        
        if ($tiempo_inactivo > $tiempo_limite) {
            error_log("VERIFICAR_INACTIVIDAD: Sesión expirada por inactividad. Inactivo por: $tiempo_inactivo segundos");
            
            // Limpiar sesión
            session_unset();
            session_destroy();
            
            // Redirigir con mensaje de expiración
            header("Location: login.php?logout=inactive");
            exit;
        }
    }
    
    // Actualizar última actividad
    $_SESSION['last_activity'] = time();
}

/**
 * Función principal de verificación de sesión
 * Incluye verificación de autenticación e inactividad
 */
function verificarSesion() {
    verificarAutenticacion();
    verificarInactividad();
}

// Si se llama directamente este archivo, ejecutar verificación
if (basename($_SERVER['PHP_SELF']) === 'verificar_sesion.php') {
    verificarSesion();
    echo json_encode([
        'status' => 'success',
        'message' => 'Sesión válida',
        'user' => $_SESSION['username'] ?? 'unknown',
        'last_activity' => $_SESSION['last_activity'] ?? 0,
        'session_id' => session_id()
    ]);
}
?>
