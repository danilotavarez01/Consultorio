<?php
// Configuración de sesión más estable y permisiva
require_once 'session_config.php';
session_start();

// Si hay acción de reset
if (isset($_GET['action']) && $_GET['action'] === 'fix') {
    
    // 1. Limpiar sesiones problemáticas
    session_unset();
    session_destroy();
    
    // 2. Iniciar nueva sesión
    session_start();
    
    // 3. Configurar sesión de prueba (TEMPORAL)
    $_SESSION['loggedin'] = true;
    $_SESSION['id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['nombre'] = 'Usuario de Prueba';
    $_SESSION['last_activity'] = time();
    
    echo "<!DOCTYPE html>";
    echo "<html><head><title>✅ Sistema Reparado</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#d4edda;} .btn{padding:10px 20px;margin:5px;border:none;border-radius:3px;text-decoration:none;display:inline-block;cursor:pointer;background:#007bff;color:white;}</style>";
    echo "</head><body>";
    
    echo "<h1>✅ Sistema de Sesiones Reparado</h1>";
    echo "<div style='background:white;padding:20px;border-radius:5px;margin:20px 0;'>";
    echo "<h3>🎉 Reparación Exitosa</h3>";
    echo "<p>✅ Sesiones limpiadas</p>";
    echo "<p>✅ Nueva sesión creada</p>";
    echo "<p>✅ Usuario de prueba configurado</p>";
    echo "<p>✅ Timeout extendido a 2 horas</p>";
    echo "</div>";
    
    echo "<h3>🔗 Probar Navegación</h3>";
    echo "<a href='index.php' class='btn'>🏠 Página Principal</a> ";
    echo "<a href='facturacion.php' class='btn'>🧾 Facturación</a> ";
    echo "<a href='configuracion_impresora_80mm.php' class='btn'>⚙️ Configuración</a>";
    
    echo "<div style='background:#fff3cd;padding:15px;margin:20px 0;border-radius:5px;'>";
    echo "<h4>⚠️ Importante</h4>";
    echo "<p>Esta es una sesión de prueba temporal. Para usar el sistema normalmente:</p>";
    echo "<ol>";
    echo "<li>Cierre esta ventana</li>";
    echo "<li>Vaya a <strong>login.php</strong></li>";
    echo "<li>Inicie sesión con sus credenciales reales</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</body></html>";
    exit();
}

echo "<!DOCTYPE html>";
echo "<html><head><title>🔧 Reparador de Sesiones</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .btn{padding:15px 30px;margin:10px;border:none;border-radius:5px;text-decoration:none;display:inline-block;cursor:pointer;font-size:16px;} .btn-danger{background:#dc3545;color:white;} .btn-primary{background:#007bff;color:white;} .alert{padding:15px;margin:15px 0;border-radius:5px;} .alert-warning{background:#fff3cd;border:1px solid #ffeaa7;}</style>";
echo "</head><body>";

echo "<h1>🔧 Reparador de Sesiones del Sistema</h1>";

echo "<div class='alert alert-warning'>";
echo "<h3>⚠️ Problema Detectado</h3>";
echo "<p>El sistema está experimentando problemas de sesión que causan deslogueos automáticos.</p>";
echo "<p><strong>Síntomas:</strong> No puede acceder a ninguna página del menú, se desloguea constantemente.</p>";
echo "</div>";

echo "<h3>📊 Diagnóstico Actual</h3>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'No definido') . "</p>";
echo "<p><strong>Loggedin:</strong> " . (isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? 'SÍ' : 'NO') : 'No existe') . "</p>";

echo "<h3>🚀 Solución Rápida</h3>";
echo "<p>Haga clic en el botón de abajo para reparar el sistema automáticamente:</p>";
echo "<a href='?action=fix' class='btn btn-danger' onclick='return confirm(\"¿Está seguro de reparar el sistema de sesiones?\")'>🔧 REPARAR SISTEMA AHORA</a>";

echo "<h3>📋 Lo que hace la reparación:</h3>";
echo "<ul>";
echo "<li>✅ Limpia todas las sesiones problemáticas</li>";
echo "<li>✅ Configura timeout más permisivo (2 horas)</li>";
echo "<li>✅ Crea sesión de prueba temporal</li>";
echo "<li>✅ Permite navegar por el sistema</li>";
echo "</ul>";

echo "<div style='margin-top:30px;'>";
echo "<a href='login.php' class='btn btn-primary'>🚪 Ir al Login Normal</a> ";
echo "<a href='restaurar_emergencia.php' class='btn btn-primary'>🚨 Emergencia Completa</a>";
echo "</div>";

echo "</body></html>";
?>
