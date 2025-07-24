<?php
/**
 * Debug específico para configuracion.php
 */

echo "<h2>Debug Específico: configuracion.php</h2>";

// Simular el mismo entorno que configuracion.php
require_once 'session_config.php';
session_start();
require_once "config.php";

echo "<h3>1. Estado Inicial</h3>";
echo "- Session iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? "✅" : "❌") . "<br>";
echo "- Config.php cargado: " . (isset($conn) ? "✅" : "❌") . "<br>";

if (isset($conn)) {
    echo "- Tipo de conexión: " . get_class($conn) . "<br>";
    
    try {
        $conn->query("SELECT 1");
        echo "- Conexión activa: ✅<br>";
    } catch (Exception $e) {
        echo "- Conexión activa: ❌ (" . $e->getMessage() . ")<br>";
    }
}

echo "<h3>2. Verificación de Tabla</h3>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() > 0) {
        echo "- Tabla existe: ✅<br>";
        
        // Verificar estructura
        $stmt = $conn->query("DESCRIBE configuracion");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "- Columnas encontradas: " . count($columnas) . "<br>";
        echo "- Columnas: " . implode(', ', array_slice($columnas, 0, 10)) . (count($columnas) > 10 ? '...' : '') . "<br>";
        
        // Verificar datos
        $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "- Total registros: " . $count['count'] . "<br>";
        
        if ($count['count'] > 0) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion WHERE id = 1");
            $count1 = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "- Registros con id=1: " . $count1['count'] . "<br>";
        }
        
    } else {
        echo "- Tabla existe: ❌<br>";
        echo "<strong>PROBLEMA ENCONTRADO: La tabla 'configuracion' no existe</strong><br>";
    }
} catch (Exception $e) {
    echo "- Error verificando tabla: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Intentar Cargar Configuración</h3>";
$config = null;
try {
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "- Configuración cargada: ✅<br>";
        echo "- Tipo de resultado: " . gettype($config) . "<br>";
        echo "- Número de campos: " . count($config) . "<br>";
        
        // Mostrar algunos campos clave
        $campos_clave = ['nombre_consultorio', 'medico_nombre', 'email_contacto', 'duracion_cita'];
        foreach ($campos_clave as $campo) {
            $valor = isset($config[$campo]) ? $config[$campo] : 'NO EXISTE';
            echo "- {$campo}: " . htmlspecialchars($valor) . "<br>";
        }
    } else {
        echo "- Configuración cargada: ❌<br>";
        echo "<strong>PROBLEMA: No se pudo obtener el registro con id=1</strong><br>";
    }
} catch (Exception $e) {
    echo "- Error cargando configuración: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Simulación de Carga en Formulario</h3>";
if ($config) {
    // Simular exactamente como se usa en el formulario
    echo "<h4>Método actual (configuracion.php):</h4>";
    $valor1 = htmlspecialchars($config['nombre_consultorio'] ?? 'Consultorio Médico');
    echo "- Valor para input: '{$valor1}'<br>";
    echo "- Input simulado: <input type='text' value='{$valor1}' style='width: 300px; padding: 5px;' readonly><br>";
    
    echo "<h4>Método con getConfigValue():</h4>";
    function getConfigValue($config, $key, $default = '') {
        return isset($config[$key]) && $config[$key] !== null ? $config[$key] : $default;
    }
    $valor2 = htmlspecialchars(getConfigValue($config, 'nombre_consultorio', 'Consultorio Médico'));
    echo "- Valor para input: '{$valor2}'<br>";
    echo "- Input simulado: <input type='text' value='{$valor2}' style='width: 300px; padding: 5px;' readonly><br>";
    
} else {
    echo "❌ No se puede simular porque \$config está vacío<br>";
}

echo "<h3>5. Verificar Permisos y Sesión</h3>";
echo "- Usuario logueado: " . (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ? "✅" : "❌") . "<br>";
if (isset($_SESSION["username"])) {
    echo "- Username: " . htmlspecialchars($_SESSION["username"]) . "<br>";
    echo "- Es admin: " . ($_SESSION["username"] === "admin" ? "✅" : "❌") . "<br>";
}

echo "<h3>6. Test de Acceso Directo a configuracion.php</h3>";
echo "<p>Ahora vamos a probar acceder directamente a configuracion.php:</p>";
echo "<a href='configuracion.php?debug=1' target='_blank' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Abrir configuracion.php con debug</a><br><br>";

echo "<h3>7. Posibles Soluciones</h3>";
echo "<ol>";
echo "<li><a href='reparar_configuracion.php' target='_blank'>Ejecutar script de reparación</a></li>";
echo "<li>Verificar que el usuario 'admin' esté logueado</li>";
echo "<li>Revisar errores de PHP en el navegador (F12 > Console)</li>";
echo "<li>Verificar permisos de archivos</li>";
echo "</ol>";

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h2, h3, h4 { color: #333; }
    input { border: 1px solid #ccc; }
    a { color: #007bff; }
    strong { color: #d63384; }
</style>";
?>
