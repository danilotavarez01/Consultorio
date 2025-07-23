<?php
/**
 * Script de verificación de sesión robusta - TEMPORALMENTE DESACTIVADO
 * Este script estaba causando deslogueos masivos
 */

// SCRIPT DESACTIVADO PARA EVITAR PROBLEMAS
error_log("VERIFICACION ROBUSTA: Script desactivado temporalmente para evitar deslogueos");

// No hacer ninguna verificación por ahora
return true;

/*
// CÓDIGO ORIGINAL COMENTADO PARA FUTURAS MEJORAS
?>

// Solo ejecutar si no hay salida previa
if (!headers_sent()) {
    
    // Debug de sesión
    error_log("=== VERIFICACION SESION ROBUSTA ===");
    error_log("Archivo: " . ($_SERVER['PHP_SELF'] ?? 'DESCONOCIDO'));
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Variables de sesión: " . print_r($_SESSION, true));
    
    // Verificaciones múltiples
    $checks = [
        'session_active' => session_status() === PHP_SESSION_ACTIVE,
        'session_id_exists' => !empty(session_id()),
        'loggedin_exists' => isset($_SESSION['loggedin']),
        'loggedin_true' => isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true,
        'id_exists' => isset($_SESSION['id']),
        'id_not_empty' => isset($_SESSION['id']) && !empty($_SESSION['id'])
    ];
    
    error_log("Verificaciones de sesión: " . print_r($checks, true));
    
    // Determinar si la sesión es válida
    $sessionValid = $checks['session_active'] && 
                   $checks['session_id_exists'] && 
                   $checks['loggedin_true'] && 
                   $checks['id_not_empty'];
    
    if (!$sessionValid) {
        error_log("SESIÓN INVÁLIDA - Redirigiendo a login");
        error_log("Checks fallidos: " . print_r(array_filter($checks, function($v) { return !$v; }), true));
        
        // Limpiar sesión inválida
        session_unset();
        session_destroy();
        
        // Redirigir al login con mensaje
        $errorMsg = urlencode("Su sesión ha expirado o es inválida. Por favor, inicie sesión nuevamente.");
        header("Location: login.php?error=" . $errorMsg);
        exit();
    }
    
    // Si llegamos aquí, la sesión es válida
    error_log("SESIÓN VÁLIDA - Usuario ID: " . $_SESSION['id']);
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
    
} else {
    error_log("VERIFICACION SESION: Headers ya enviados, no se puede verificar");
}
?>
