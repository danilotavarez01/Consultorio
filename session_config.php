<?php
/**
 * Configuración de sesiones del sistema
 * Este archivo debe incluirse ANTES de cualquier session_start()
 */

// Solo configurar si no hay sesión activa
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    // Configuraciones básicas de sesión (más permisivas para ventanas emergentes)
    ini_set('session.gc_maxlifetime', 7200); // 2 horas
    ini_set('session.cookie_lifetime', 0); // Hasta cerrar navegador
    ini_set('session.use_only_cookies', 1);
    
    // Configuraciones más permisivas para ventanas múltiples y emergentes
    ini_set('session.cookie_samesite', ''); // Más permisivo
    ini_set('session.use_trans_sid', 0);
    ini_set('session.cookie_secure', 0); // Para HTTP local
    ini_set('session.cookie_httponly', 1); // Seguridad básica
    
    // Nombre de sesión único para el sistema
    session_name('CONSULTORIO_SESSION');
    
    // Configurar directorio de sesiones si es necesario
    $session_path = sys_get_temp_dir() . '/consultorio_sessions';
    if (!is_dir($session_path)) {
        @mkdir($session_path, 0700, true);
    }
    if (is_dir($session_path) && is_writable($session_path)) {
        ini_set('session.save_path', $session_path);
    }
}
?>
