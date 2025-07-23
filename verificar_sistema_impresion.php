<?php
// verificar_sistema_impresion.php - Verificación rápida del sistema
require_once 'session_config.php';
session_start();

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Verificación del Sistema de Impresión</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".status { padding: 10px; margin: 5px 0; border-radius: 5px; }";
echo ".ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }";
echo ".error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }";
echo ".warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }";
echo ".info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }";
echo "h1 { color: #333; }";
echo "h2 { color: #666; margin-top: 25px; }";
echo ".file-check { font-family: monospace; font-size: 12px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Verificación del Sistema de Impresión</h1>";
echo "<p><em>Verificación realizada el: " . date('d/m/Y H:i:s') . "</em></p>";

// Verificar archivos principales
echo "<h2>📁 Archivos del Sistema</h2>";

$archivos_criticos = [
    'imprimir_recibo.php' => 'Recibo original',
    'imprimir_recibo_mejorado.php' => 'Recibo mejorado (NUEVO)',
    'facturacion.php' => 'Módulo de facturación',
    'test_impresion_completa.php' => 'Test integral (NUEVO)',
    'test_impresion_automatica.php' => 'Test de diagnóstico',
    'config.php' => 'Configuración de BD',
    'session_config.php' => 'Configuración de sesión'
];

foreach ($archivos_criticos as $archivo => $descripcion) {
    $existe = file_exists($archivo);
    $clase = $existe ? 'ok' : 'error';
    $icono = $existe ? '✅' : '❌';
    $estado = $existe ? 'EXISTE' : 'NO ENCONTRADO';
    
    echo "<div class='status $clase file-check'>";
    echo "$icono <strong>$archivo</strong> - $descripcion: $estado";
    echo "</div>";
}

// Verificar configuración de sesión
echo "<h2>🔐 Estado de la Sesión</h2>";

$session_activa = session_status() === PHP_SESSION_ACTIVE;
echo "<div class='status " . ($session_activa ? 'ok' : 'error') . "'>";
echo ($session_activa ? '✅' : '❌') . " Sesión PHP: " . ($session_activa ? 'ACTIVA' : 'INACTIVA');
echo "</div>";

echo "<div class='status info'>";
echo "📋 Session ID: " . session_id();
echo "</div>";

if (isset($_SESSION['loggedin'])) {
    echo "<div class='status " . ($_SESSION['loggedin'] ? 'ok' : 'warning') . "'>";
    echo ($_SESSION['loggedin'] ? '✅' : '⚠️') . " Estado de login: " . ($_SESSION['loggedin'] ? 'LOGUEADO' : 'NO LOGUEADO');
    echo "</div>";
} else {
    echo "<div class='status warning'>";
    echo "⚠️ Variable de login no establecida";
    echo "</div>";
}

if (isset($_SESSION['ultimo_pago'])) {
    echo "<div class='status ok'>";
    echo "✅ Datos de último pago: DISPONIBLES";
    echo "</div>";
    
    echo "<div class='status info'>";
    echo "📄 Último pago ID: " . ($_SESSION['ultimo_pago']['pago_id'] ?? 'N/A');
    echo "</div>";
} else {
    echo "<div class='status info'>";
    echo "📄 Datos de último pago: NO DISPONIBLES (normal si no se ha registrado pago)";
    echo "</div>";
}

// Verificar conexión a base de datos
echo "<h2>🗄️ Conexión a Base de Datos</h2>";

try {
    require_once 'config.php';
    
    if (isset($conn) && $conn instanceof PDO) {
        echo "<div class='status ok'>";
        echo "✅ Conexión PDO: ESTABLECIDA";
        echo "</div>";
        
        // Test simple de consulta
        $stmt = $conn->query("SELECT 1");
        if ($stmt) {
            echo "<div class='status ok'>";
            echo "✅ Test de consulta: EXITOSO";
            echo "</div>";
        }
        
        // Verificar tablas importantes
        $tablas = ['usuarios', 'pacientes', 'facturas', 'pagos'];
        foreach ($tablas as $tabla) {
            try {
                $stmt = $conn->query("SHOW TABLES LIKE '$tabla'");
                $existe = $stmt->rowCount() > 0;
                
                echo "<div class='status " . ($existe ? 'ok' : 'error') . "'>";
                echo ($existe ? '✅' : '❌') . " Tabla '$tabla': " . ($existe ? 'EXISTE' : 'NO ENCONTRADA');
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='status error'>";
                echo "❌ Error verificando tabla '$tabla': " . $e->getMessage();
                echo "</div>";
            }
        }
        
    } else {
        echo "<div class='status error'>";
        echo "❌ Conexión PDO: NO ESTABLECIDA";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='status error'>";
    echo "❌ Error de conexión: " . $e->getMessage();
    echo "</div>";
}

// Verificar permisos de archivos (solo en sistemas Unix-like)
if (function_exists('fileperms')) {
    echo "<h2>🔒 Permisos de Archivos</h2>";
    
    $archivos_permisos = ['imprimir_recibo_mejorado.php', 'facturacion.php'];
    
    foreach ($archivos_permisos as $archivo) {
        if (file_exists($archivo)) {
            $permisos = fileperms($archivo);
            $permisos_octal = substr(sprintf('%o', $permisos), -4);
            
            echo "<div class='status info file-check'>";
            echo "🔐 $archivo: $permisos_octal";
            echo "</div>";
        }
    }
}

// Información del servidor
echo "<h2>🖥️ Información del Servidor</h2>";

echo "<div class='status info'>";
echo "🐘 PHP Version: " . phpversion();
echo "</div>";

echo "<div class='status info'>";
echo "🌐 Servidor Web: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido');
echo "</div>";

echo "<div class='status info'>";
echo "📁 Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A');
echo "</div>";

// Enlaces de navegación
echo "<h2>🔗 Enlaces de Prueba</h2>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>";
echo "<a href='test_impresion_completa.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>🧪 Test Completo</a>";
echo "<a href='test_impresion_automatica.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>🔧 Diagnóstico</a>";
echo "<a href='facturacion.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>💰 Facturación</a>";
echo "<a href='imprimir_recibo_mejorado.php?pago_id=999' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>🧾 Recibo Test</a>";
echo "</div>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Sistema de verificación generado automáticamente<br>";
echo "Para soporte técnico, conserve esta información";
echo "</p>";

echo "</body></html>";
?>
