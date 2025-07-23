<?php
// Script de emergencia para restaurar sesiones
require_once 'session_config.php';
session_start();

echo "<!DOCTYPE html>";
echo "<html><head><title>🚨 Restauración de Emergencia</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f8f9fa;} .alert{padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;} .danger{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;} .info{background:#cce7ff;border:1px solid #b3d7ff;color:#004085;} .btn{padding:10px 20px;margin:5px;border:none;border-radius:3px;text-decoration:none;display:inline-block;cursor:pointer;} .btn-primary{background:#007bff;color:white;} .btn-success{background:#28a745;color:white;} .btn-danger{background:#dc3545;color:white;}</style>";
echo "</head><body>";

echo "<h1>🚨 Restauración de Emergencia del Sistema</h1>";

// Paso 1: Limpiar todo
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    session_unset();
    session_destroy();
    
    // Limpiar cookies
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    echo "<div class='alert success'>";
    echo "✅ <strong>Paso 1 completado:</strong> Sesiones limpiadas completamente<br>";
    echo "🔄 Redirigiendo al login en 3 segundos...";
    echo "</div>";
    
    echo "<script>";
    echo "setTimeout(function() { window.location.href = 'login.php?reset=1'; }, 3000);";
    echo "</script>";
    
    echo "<p><a href='login.php?reset=1' class='btn btn-primary'>🚀 Ir al Login Ahora</a></p>";
    
} else {
    // Mostrar diagnóstico
    echo "<div class='alert info'>";
    echo "<h3>📊 Estado Actual del Sistema</h3>";
    echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
    echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'No definido') . "</p>";
    echo "<p><strong>Sesión loggedin:</strong> " . (isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? 'SÍ' : 'NO') : 'No existe') . "</p>";
    echo "<p><strong>Usuario ID:</strong> " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'No definido') . "</p>";
    echo "</div>";
    
    if (!empty($_SESSION)) {
        echo "<div class='alert danger'>";
        echo "<h4>⚠️ Sesión con problemas detectada</h4>";
        echo "<p>El sistema tiene datos de sesión pero no funciona correctamente.</p>";
        echo "<p><strong>Acción recomendada:</strong> Reset completo del sistema de sesiones</p>";
        echo "</div>";
    } else {
        echo "<div class='alert info'>";
        echo "<h4>ℹ️ No hay sesión activa</h4>";
        echo "<p>El sistema no tiene datos de sesión. Esto es normal si no está logueado.</p>";
        echo "</div>";
    }
    
    echo "<h3>🔧 Acciones Disponibles</h3>";
    echo "<div style='margin:20px 0;'>";
    echo "<a href='?action=reset' class='btn btn-danger' onclick='return confirm(\"¿Está seguro de resetear completamente el sistema de sesiones?\")'>🔄 Reset Completo de Sesiones</a> ";
    echo "<a href='login.php' class='btn btn-primary'>🚪 Ir al Login</a> ";
    echo "<a href='index.php' class='btn btn-success'>🏠 Página Principal</a>";
    echo "</div>";
    
    echo "<div class='alert info'>";
    echo "<h4>📋 Instrucciones</h4>";
    echo "<ol>";
    echo "<li><strong>Reset Completo:</strong> Limpia todas las sesiones y cookies del sistema</li>";
    echo "<li><strong>Login Fresh:</strong> Inicia sesión desde cero</li>";
    echo "<li><strong>Verificar Funcionamiento:</strong> Prueba acceder a las páginas del menú</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>
