<?php
/**
 * Test específico para el problema de configuración
 */

require_once 'session_config.php';
session_start();
require_once "config.php";

// Verificar login (simular mismo contexto que configuracion.php)
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo "❌ Usuario no logueado<br>";
    echo "<a href='login.php'>Ir a login</a><br>";
    exit;
}

if($_SESSION["username"] !== "admin"){
    echo "❌ Usuario no es admin<br>";
    exit;
}

echo "<h2>Test: Carga de Datos en Configuración</h2>";

// Simular exactamente la misma lógica que configuracion.php
$config = [];

echo "<h3>Paso 1: Verificar conexión</h3>";
if (isset($conn)) {
    echo "✅ \$conn está definida<br>";
    echo "✅ Tipo: " . get_class($conn) . "<br>";
    
    try {
        $conn->query("SELECT 1");
        echo "✅ Conexión funciona<br>";
    } catch (Exception $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
        exit;
    }
} else {
    echo "❌ \$conn no está definida<br>";
    exit;
}

echo "<h3>Paso 2: Verificar tabla</h3>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabla 'configuracion' no existe<br>";
        echo "<a href='reparar_configuracion.php'>Ejecutar reparación</a><br>";
        exit;
    } else {
        echo "✅ Tabla 'configuracion' existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error verificando tabla: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>Paso 3: Cargar configuración (método actual)</h3>";
try {
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✅ Configuración cargada<br>";
        echo "✅ Número de campos: " . count($config) . "<br>";
        echo "✅ nombre_consultorio: '" . htmlspecialchars($config['nombre_consultorio'] ?? 'NULL') . "'<br>";
        echo "✅ medico_nombre: '" . htmlspecialchars($config['medico_nombre'] ?? 'NULL') . "'<br>";
    } else {
        echo "❌ No se pudo cargar configuración (registro no existe)<br>";
        
        // Intentar crear uno
        echo "Intentando crear registro...<br>";
        $sql_insert = "INSERT INTO configuracion (
            id, nombre_consultorio, medico_nombre, duracion_cita, 
            hora_inicio, hora_fin, dias_laborables, intervalo_citas,
            moneda, zona_horaria, formato_fecha, idioma, tema_color,
            mostrar_alertas_stock, multi_medico, whatsapp_server
        ) VALUES (
            1, 'Mi Consultorio Médico', 'Dr. Juan Pérez', 30, 
            '09:00:00', '18:00:00', '1,2,3,4,5', 30,
            'RD$', 'America/Santo_Domingo', 'Y-m-d', 'es', 'light',
            1, 0, 'https://api.whatsapp.com'
        )";
        
        $conn->exec($sql_insert);
        echo "✅ Registro creado<br>";
        
        // Intentar cargar nuevamente
        $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            echo "✅ Configuración cargada después de crear<br>";
            echo "✅ nombre_consultorio: '" . htmlspecialchars($config['nombre_consultorio']) . "'<br>";
        } else {
            echo "❌ Aún no se puede cargar después de crear<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error cargando configuración: " . $e->getMessage() . "<br>";
}

echo "<h3>Paso 4: Probar función getConfigValue</h3>";
function getConfigValue($config, $key, $default = '') {
    return isset($config[$key]) && $config[$key] !== null && $config[$key] !== '' ? $config[$key] : $default;
}

if ($config) {
    $nombre = getConfigValue($config, 'nombre_consultorio', 'Consultorio Médico');
    echo "✅ getConfigValue result: '" . htmlspecialchars($nombre) . "'<br>";
    
    // Simular cómo se vería en el input
    echo "<h4>Cómo se vería en el formulario:</h4>";
    echo "<input type='text' value='" . htmlspecialchars($nombre) . "' style='width: 300px; padding: 5px;' readonly><br>";
} else {
    echo "❌ No se puede probar getConfigValue porque \$config está vacío<br>";
}

echo "<h3>Paso 5: Mostrar todos los datos cargados</h3>";
if ($config && is_array($config)) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach ($config as $campo => $valor) {
        if ($campo !== 'logo') {
            echo "<tr>";
            echo "<td style='padding: 5px;'>" . htmlspecialchars($campo) . "</td>";
            echo "<td style='padding: 5px;'>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "❌ \$config no es un array válido<br>";
    echo "Tipo de \$config: " . gettype($config) . "<br>";
    echo "Contenido: " . var_export($config, true) . "<br>";
}

echo "<hr>";
echo "<h3>Conclusión:</h3>";
if ($config && isset($config['nombre_consultorio']) && !empty($config['nombre_consultorio'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "✅ <strong>TODO FUNCIONA CORRECTAMENTE</strong><br>";
    echo "Los datos se están cargando bien. El problema puede estar en otro lugar.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ <strong>PROBLEMA IDENTIFICADO</strong><br>";
    echo "Los datos no se están cargando correctamente desde la base de datos.";
    echo "</div>";
}

echo "<br><a href='configuracion.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Ir a Configuración</a>";

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h2, h3, h4 { color: #333; }
    table { margin: 10px 0; }
    th, td { text-align: left; }
    input { border: 1px solid #ccc; }
</style>";
?>
