<?php
/**
 * Debug temporal para configuracion.php
 */

require_once 'config.php';

echo "<h2>Debug: Carga de Configuración</h2>";

try {
    // 1. Verificar conexión
    echo "<h3>1. Estado de la conexión</h3>";
    if (isset($conn)) {
        echo "✅ Variable \$conn está definida<br>";
        echo "✅ Tipo: " . get_class($conn) . "<br>";
    } else {
        echo "❌ Variable \$conn NO está definida<br>";
    }
    
    // 2. Verificar tabla
    echo "<h3>2. Verificar tabla configuracion</h3>";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'configuracion'");
    if ($tableCheck->rowCount() > 0) {
        echo "✅ Tabla 'configuracion' existe<br>";
    } else {
        echo "❌ Tabla 'configuracion' NO existe<br>";
        exit;
    }
    
    // 3. Verificar datos
    echo "<h3>3. Verificar datos en la tabla</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM configuracion");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "- Total registros: " . $count['count'] . "<br>";
    
    // 4. Cargar configuración
    echo "<h3>4. Cargando configuración con id = 1</h3>";
    $stmt = $conn->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✅ Configuración cargada correctamente<br>";
        echo "<strong>Datos disponibles:</strong><br>";
        
        // Mostrar algunos campos importantes
        $campos_importantes = [
            'nombre_consultorio', 'medico_nombre', 'email_contacto', 
            'telefono', 'duracion_cita', 'multi_medico'
        ];
        
        foreach ($campos_importantes as $campo) {
            $valor = isset($config[$campo]) ? $config[$campo] : 'NULL';
            echo "- {$campo}: " . htmlspecialchars($valor) . "<br>";
        }
        
    } else {
        echo "❌ No se pudo cargar la configuración<br>";
    }
    
    // 5. Probar acceso específico a campos
    echo "<h3>5. Prueba de acceso a campos específicos</h3>";
    echo "- config['nombre_consultorio']: " . htmlspecialchars($config['nombre_consultorio'] ?? 'VACÍO') . "<br>";
    echo "- Usando getConfigValue(): ";
    
    // Simular la función getConfigValue
    function testGetConfigValue($config, $key, $default = '') {
        return isset($config[$key]) && $config[$key] !== null ? $config[$key] : $default;
    }
    
    echo htmlspecialchars(testGetConfigValue($config, 'nombre_consultorio', 'Consultorio Médico')) . "<br>";
    
    // 6. Mostrar cómo se vería en el input
    echo "<h3>6. Cómo se vería en el input</h3>";
    $valor_input = htmlspecialchars($config['nombre_consultorio'] ?? 'Consultorio Médico');
    echo "<input type='text' value='{$valor_input}' readonly style='width: 300px; padding: 5px;'><br>";
    
    echo "<br><div style='background-color: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<strong>✅ Si llegaste hasta aquí, la configuración se está cargando correctamente.</strong><br>";
    echo "El problema puede estar en cómo se usa en el formulario de configuracion.php";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3 { color: #333; }
    input { border: 1px solid #ccc; }
</style>";
?>
