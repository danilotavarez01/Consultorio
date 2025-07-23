<?php
// security_check.php - Verificación de seguridad del sistema
require_once 'session_config.php';
session_start();

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Verificación de Seguridad del Sistema</title>";
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
echo ".code-snippet { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 11px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🛡️ Verificación de Seguridad del Sistema</h1>";
echo "<p><em>Verificación realizada el: " . date('d/m/Y H:i:s') . "</em></p>";

// 1. Verificar estado de sesión actual
echo "<h2>🔐 Estado de Sesión Actual</h2>";

$session_activa = session_status() === PHP_SESSION_ACTIVE;
echo "<div class='status " . ($session_activa ? 'info' : 'error') . "'>";
echo ($session_activa ? 'ℹ️' : '❌') . " Sesión PHP: " . ($session_activa ? 'ACTIVA' : 'INACTIVA');
echo "</div>";

if (isset($_SESSION['loggedin'])) {
    $is_logged = $_SESSION['loggedin'] === true;
    echo "<div class='status " . ($is_logged ? 'warning' : 'ok') . "'>";
    echo ($is_logged ? '⚠️' : '✅') . " Estado de login: " . ($is_logged ? 'LOGUEADO (REVISAR)' : 'NO LOGUEADO (CORRECTO)');
    echo "</div>";
    
    if ($is_logged) {
        echo "<div class='status info'>";
        echo "👤 Usuario: " . ($_SESSION['username'] ?? 'N/A');
        echo "</div>";
        
        echo "<div class='status info'>";
        echo "🆔 ID: " . ($_SESSION['id'] ?? 'N/A');
        echo "</div>";
    }
} else {
    echo "<div class='status ok'>";
    echo "✅ No hay sesión de login activa (CORRECTO)";
    echo "</div>";
}

// 2. Verificar archivos con auto-login
echo "<h2>🚨 Archivos con Potencial Auto-Login</h2>";

$archivos_sospechosos = [];
$patron_autologin = '/\$_SESSION\s*\[\s*["\']loggedin["\']\s*\]\s*=\s*true/';

// Buscar en archivos PHP
$archivos_php = glob('*.php');
foreach ($archivos_php as $archivo) {
    if (strpos($archivo, 'security_check.php') !== false) continue;
    
    $contenido = file_get_contents($archivo);
    if (preg_match($patron_autologin, $contenido)) {
        $lineas = file($archivo);
        $lineas_problema = [];
        
        foreach ($lineas as $num_linea => $linea) {
            if (preg_match($patron_autologin, $linea)) {
                // Verificar si está comentado
                $linea_limpia = trim($linea);
                $esta_comentado = (strpos($linea_limpia, '//') === 0) || 
                                 (strpos($linea_limpia, '#') === 0) ||
                                 (strpos($linea_limpia, '/*') !== false && strpos($linea_limpia, '*/') !== false);
                
                $lineas_problema[] = [
                    'numero' => $num_linea + 1,
                    'contenido' => trim($linea),
                    'comentado' => $esta_comentado
                ];
            }
        }
        
        if (!empty($lineas_problema)) {
            $archivos_sospechosos[$archivo] = $lineas_problema;
        }
    }
}

if (empty($archivos_sospechosos)) {
    echo "<div class='status ok'>";
    echo "✅ No se encontraron archivos con auto-login activo";
    echo "</div>";
} else {
    foreach ($archivos_sospechosos as $archivo => $lineas) {
        $tiene_activo = false;
        foreach ($lineas as $info_linea) {
            if (!$info_linea['comentado']) {
                $tiene_activo = true;
                break;
            }
        }
        
        echo "<div class='status " . ($tiene_activo ? 'error' : 'warning') . "'>";
        echo ($tiene_activo ? '❌' : '⚠️') . " <strong>$archivo</strong> - " . 
             ($tiene_activo ? 'AUTO-LOGIN ACTIVO' : 'Auto-login comentado');
        echo "</div>";
        
        foreach ($lineas as $info_linea) {
            echo "<div class='code-snippet'>";
            echo "Línea " . $info_linea['numero'] . ": ";
            echo ($info_linea['comentado'] ? '✅ ' : '❌ ');
            echo htmlspecialchars($info_linea['contenido']);
            echo "</div>";
        }
    }
}

// 3. Verificar archivos de configuración críticos
echo "<h2>📄 Archivos de Configuración</h2>";

$archivos_config = [
    'index.php' => 'Página principal',
    'login.php' => 'Página de login',
    'session_config.php' => 'Configuración de sesión',
    'permissions.php' => 'Sistema de permisos'
];

foreach ($archivos_config as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<div class='status ok file-check'>";
        echo "✅ <strong>$archivo</strong> - $descripcion: EXISTE";
        echo "</div>";
        
        // Verificar contenido crítico en index.php
        if ($archivo === 'index.php') {
            $contenido = file_get_contents($archivo);
            if (strpos($contenido, 'if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)') !== false) {
                echo "<div class='status ok file-check'>";
                echo "  ✅ Verificación de login: PRESENTE";
                echo "</div>";
            } else {
                echo "<div class='status error file-check'>";
                echo "  ❌ Verificación de login: AUSENTE O MODIFICADA";
                echo "</div>";
            }
        }
    } else {
        echo "<div class='status error file-check'>";
        echo "❌ <strong>$archivo</strong> - $descripcion: NO ENCONTRADO";
        echo "</div>";
    }
}

// 4. Verificar redirecciones automáticas
echo "<h2>🔄 Redirecciones y Acceso Directo</h2>";

// Simular acceso a páginas principales sin login
$paginas_protegidas = [
    'index.php' => 'Dashboard principal',
    'pacientes.php' => 'Gestión de pacientes',
    'citas.php' => 'Gestión de citas',
    'facturacion.php' => 'Sistema de facturación'
];

foreach ($paginas_protegidas as $pagina => $descripcion) {
    if (file_exists($pagina)) {
        $contenido = file_get_contents($pagina);
        
        // Buscar verificación de sesión
        $tiene_verificacion = (strpos($contenido, '$_SESSION["loggedin"]') !== false) ||
                             (strpos($contenido, 'session_start()') !== false);
        
        echo "<div class='status " . ($tiene_verificacion ? 'ok' : 'error') . " file-check'>";
        echo ($tiene_verificacion ? '✅' : '❌') . " <strong>$pagina</strong> - $descripcion: " . 
             ($tiene_verificacion ? 'PROTEGIDA' : 'SIN PROTECCIÓN');
        echo "</div>";
    }
}

// 5. Recomendaciones de seguridad
echo "<h2>💡 Recomendaciones de Seguridad</h2>";

echo "<div class='status info'>";
echo "<strong>✅ Para mantener la seguridad:</strong><br>";
echo "• Revisar periódicamente archivos de test<br>";
echo "• No dejar auto-login activo en archivos de desarrollo<br>";
echo "• Verificar que todas las páginas tengan verificación de sesión<br>";
echo "• Limpiar sesiones regularmente con clear_all_sessions.php<br>";
echo "• Revisar logs de acceso para detectar accesos no autorizados";
echo "</div>";

// 6. Enlaces de gestión
echo "<h2>🔗 Herramientas de Gestión</h2>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>";
echo "<a href='clear_all_sessions.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 3px;'>🧹 Limpiar Sesiones</a>";
echo "<a href='login.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>🔑 Ir al Login</a>";
echo "<a href='index.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>🏠 Probar Acceso</a>";
echo "<a href='verificar_sistema_impresion.php' style='display: inline-block; margin: 5px; padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>🖨️ Verificar Impresión</a>";
echo "</div>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Verificación de seguridad v1.0 - " . date('Y-m-d H:i:s') . "<br>";
echo "Ejecute esta verificación regularmente para mantener la seguridad del sistema";
echo "</p>";

echo "</body></html>";
?>
